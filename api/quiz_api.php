<?php
/**
 * Quiz API
 * Handle quiz submissions, scoring, and results
 */

header('Content-Type: application/json');
require_once '../config/config.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'start_quiz':
            // Start a new quiz attempt
            $quizId = (int)($_POST['quiz_id'] ?? 0);
            
            if (!$quizId) {
                throw new Exception('Quiz ID is required');
            }
            
            // Check max attempts
            $stmt = $pdo->prepare("SELECT max_attempts FROM quizzes WHERE id = ?");
            $stmt->execute([$quizId]);
            $maxAttempts = $stmt->fetchColumn();
            
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM quiz_attempts WHERE user_id = ? AND quiz_id = ?");
            $stmt->execute([$userId, $quizId]);
            $attemptCount = $stmt->fetchColumn();
            
            if ($attemptCount >= $maxAttempts) {
                throw new Exception('Maximum attempts reached');
            }
            
            // Create new attempt
            $stmt = $pdo->prepare("
                INSERT INTO quiz_attempts (user_id, quiz_id, started_at)
                VALUES (?, ?, NOW())
            ");
            $stmt->execute([$userId, $quizId]);
            $attemptId = $pdo->lastInsertId();
            
            // Get quiz questions
            $stmt = $pdo->prepare("
                SELECT qq.*, 
                       (SELECT JSON_ARRAYAGG(JSON_OBJECT('id', id, 'text', option_text))
                        FROM quiz_options WHERE question_id = qq.id) as options
                FROM quiz_questions qq
                WHERE qq.quiz_id = ?
                ORDER BY qq.order_num
            ");
            $stmt->execute([$quizId]);
            $questions = $stmt->fetchAll();
            
            echo json_encode([
                'success' => true,
                'attempt_id' => $attemptId,
                'questions' => $questions
            ]);
            break;

        case 'save_progress':
            // Autosave quiz answers for an attempt (upsert per question)
            $attemptId = (int)($_POST['attempt_id'] ?? 0);
            $answersRaw = $_POST['answers'] ?? null;
            $timeSpent = isset($_POST['time_spent']) ? (int)$_POST['time_spent'] : null;

            // Attempt to read JSON body if answers not provided in form-data
            if (empty($answersRaw)) {
                $body = file_get_contents('php://input');
                $data = json_decode($body, true);
                if (isset($data['answers'])) {
                    $answersRaw = $data['answers'];
                }
                if (isset($data['attempt_id']) && !$attemptId) {
                    $attemptId = (int)$data['attempt_id'];
                }
                if (isset($data['time_spent']) && $timeSpent === null) {
                    $timeSpent = (int)$data['time_spent'];
                }
            }

            if (!$attemptId || empty($answersRaw)) {
                throw new Exception('Invalid save payload');
            }

            // Ensure attempt belongs to the user
            $stmt = $pdo->prepare("SELECT id, completed_at FROM quiz_attempts WHERE id = ? AND user_id = ?");
            $stmt->execute([$attemptId, $userId]);
            $attempt = $stmt->fetch();
            if (!$attempt) {
                throw new Exception('Quiz attempt not found');
            }

            // Decode answers if necessary (answers may arrive as JSON string)
            if (is_string($answersRaw)) {
                $answers = json_decode($answersRaw, true);
            } else {
                $answers = $answersRaw;
            }

            if (!is_array($answers)) {
                throw new Exception('Answers must be an object/array');
            }

            // Don't allow saving after completion, but allow if completed_at is null (in-progress)
            if (!is_null($attempt['completed_at'])) {
                throw new Exception('Cannot save progress for completed attempt');
            }

            // Prepare statements
            $selectStmt = $pdo->prepare("SELECT id FROM quiz_answers WHERE attempt_id = ? AND question_id = ? LIMIT 1");
            $updateStmt = $pdo->prepare("UPDATE quiz_answers SET selected_option_id = ?, answer_text = ? WHERE id = ?");
            $insertStmt = $pdo->prepare("INSERT INTO quiz_answers (attempt_id, question_id, selected_option_id, answer_text, is_correct) VALUES (?, ?, ?, ?, 0)");

            $pdo->beginTransaction();
            try {
                foreach ($answers as $questionId => $answerValue) {
                    $questionId = (int)$questionId;
                    // Determine whether this is an option id (numeric) or text answer
                    $selectedOptionId = null;
                    $answerText = null;
                    if (is_numeric($answerValue)) {
                        $selectedOptionId = (int)$answerValue;
                    } else {
                        $answerText = (string)$answerValue;
                    }

                    // Check existing answer
                    $selectStmt->execute([$attemptId, $questionId]);
                    $existing = $selectStmt->fetch();
                    if ($existing) {
                        $updateStmt->execute([$selectedOptionId, $answerText, $existing['id']]);
                    } else {
                        $insertStmt->execute([$attemptId, $questionId, $selectedOptionId, $answerText]);
                    }
                }

                // Optionally update time_spent on attempt
                if (!is_null($timeSpent)) {
                    $u = $pdo->prepare("UPDATE quiz_attempts SET time_spent = ? WHERE id = ?");
                    $u->execute([$timeSpent, $attemptId]);
                }

                $pdo->commit();
            } catch (Exception $ex) {
                $pdo->rollBack();
                throw $ex;
            }

            echo json_encode(['success' => true, 'message' => 'Progress saved']);
            break;
            
        case 'submit_quiz':
            // Submit quiz and calculate score
            $attemptId = (int)($_POST['attempt_id'] ?? 0);
            $answers = json_decode($_POST['answers'] ?? '[]', true);
            $timeSpent = (int)($_POST['time_spent'] ?? 0);
            
            if (!$attemptId || empty($answers)) {
                throw new Exception('Invalid submission');
            }
            
            // Get quiz info
            $stmt = $pdo->prepare("
                SELECT q.pass_score, qa.quiz_id
                FROM quiz_attempts qa
                JOIN quizzes q ON qa.quiz_id = q.id
                WHERE qa.id = ? AND qa.user_id = ?
            ");
            $stmt->execute([$attemptId, $userId]);
            $quiz = $stmt->fetch();
            
            if (!$quiz) {
                throw new Exception('Quiz attempt not found');
            }
            
            // Get all questions with correct answers
            $stmt = $pdo->prepare("
                SELECT qq.id as question_id, qo.id as option_id
                FROM quiz_questions qq
                JOIN quiz_options qo ON qq.id = qo.question_id
                WHERE qq.quiz_id = ? AND qo.is_correct = 1
            ");
            $stmt->execute([$quiz['quiz_id']]);
            $correctAnswers = [];
            while ($row = $stmt->fetch()) {
                $correctAnswers[$row['question_id']] = $row['option_id'];
            }
            
            // Calculate score
            $totalQuestions = count($correctAnswers);
            $correctCount = 0;
            
            foreach ($answers as $questionId => $answerId) {
                if (isset($correctAnswers[$questionId]) && $correctAnswers[$questionId] == $answerId) {
                    $correctCount++;
                }
            }
            
            $score = $totalQuestions > 0 ? ($correctCount / $totalQuestions) * 100 : 0;
            $passed = $score >= $quiz['pass_score'] ? 1 : 0;
            
            // Update attempt
            $stmt = $pdo->prepare("
                UPDATE quiz_attempts 
                SET score = ?, total_questions = ?, correct_answers = ?, time_spent = ?, passed = ?, completed_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$score, $totalQuestions, $correctCount, $timeSpent, $passed, $attemptId]);
            
            echo json_encode([
                'success' => true,
                'score' => round($score, 2),
                'correct_answers' => $correctCount,
                'total_questions' => $totalQuestions,
                'passed' => $passed,
                'pass_score' => $quiz['pass_score']
            ]);
            break;
            
        case 'get_results':
            // Get quiz attempt results
            $attemptId = (int)($_GET['attempt_id'] ?? 0);
            
            $stmt = $pdo->prepare("
                SELECT qa.*, q.title as quiz_title, q.pass_score
                FROM quiz_attempts qa
                JOIN quizzes q ON qa.quiz_id = q.id
                WHERE qa.id = ? AND qa.user_id = ?
            ");
            $stmt->execute([$attemptId, $userId]);
            $result = $stmt->fetch();
            
            if (!$result) {
                throw new Exception('Results not found');
            }
            
            echo json_encode([
                'success' => true,
                'result' => $result
            ]);
            break;
            
        case 'get_attempts':
            // Get all attempts for a quiz
            $quizId = (int)($_GET['quiz_id'] ?? 0);
            
            $stmt = $pdo->prepare("
                SELECT * FROM quiz_attempts 
                WHERE user_id = ? AND quiz_id = ?
                ORDER BY started_at DESC
            ");
            $stmt->execute([$userId, $quizId]);
            $attempts = $stmt->fetchAll();
            
            echo json_encode([
                'success' => true,
                'attempts' => $attempts
            ]);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
