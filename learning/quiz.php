<?php
require_once '../config/config.php';
require_once '../config/constants.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$quizId = (int)($_GET['id'] ?? 0);
$userId = $_SESSION['user_id'];

// Get quiz details
$stmt = $pdo->prepare("
    SELECT q.*, c.title as course_title, c.category
    FROM quizzes q
    JOIN courses c ON q.course_id = c.id
    WHERE q.id = ?
");
$stmt->execute([$quizId]);
$quiz = $stmt->fetch();

if (!$quiz) {
    header('Location: ../student/dashboard.php');
    exit;
}

// Get questions
$stmt = $pdo->prepare("SELECT * FROM quiz_questions WHERE quiz_id = ? ORDER BY order_num ASC");
$stmt->execute([$quizId]);
$questions = $stmt->fetchAll();

// Get previous attempts
$stmt = $pdo->prepare("SELECT * FROM quiz_attempts WHERE user_id = ? AND quiz_id = ? ORDER BY started_at DESC");
$stmt->execute([$userId, $quizId]);
$attempts = $stmt->fetchAll();

$pageTitle = htmlspecialchars($quiz['title']) . " - UTH Learning";

include '../includes/header.php';
?>

<div class="quiz-page">
    <div class="container">
        <div class="quiz-header">
            <a href="../pages/course-detail.php?id=<?= $quiz['course_id'] ?>" class="back-link">
                <i class="fas fa-arrow-left"></i> Quay lại khóa học
            </a>
            <h1><?= htmlspecialchars($quiz['title']) ?></h1>
            <p><?= htmlspecialchars($quiz['description']) ?></p>
        </div>

        <div class="quiz-info-cards">
            <div class="info-card">
                <i class="fas fa-question-circle"></i>
                <div>
                    <h3><?= count($questions) ?></h3>
                    <p>Câu hỏi</p>
                </div>
            </div>
            <div class="info-card">
                <i class="fas fa-clock"></i>
                <div>
                    <h3><?= $quiz['duration'] ?></h3>
                    <p>Phút</p>
                </div>
            </div>
            <div class="info-card">
                <i class="fas fa-check-circle"></i>
                <div>
                    <h3><?= $quiz['pass_score'] ?>%</h3>
                    <p>Điểm đạt</p>
                </div>
            </div>
            <div class="info-card">
                <i class="fas fa-redo"></i>
                <div>
                    <h3><?= $quiz['max_attempts'] ?></h3>
                    <p>Lần làm bài</p>
                </div>
            </div>
        </div>

        <!-- Previous Attempts -->
        <?php if (!empty($attempts)): ?>
        <div class="attempts-section">
            <h2>Lịch sử làm bài</h2>
            <div class="attempts-list">
                <?php foreach ($attempts as $attempt): ?>
                <div class="attempt-card">
                    <div class="attempt-info">
                        <h4>Lần <?= count($attempts) - array_search($attempt, $attempts) ?></h4>
                        <p><?= date('d/m/Y H:i', strtotime($attempt['started_at'])) ?></p>
                    </div>
                    <div class="attempt-score">
                        <div class="score-circle <?= $attempt['passed'] ? 'passed' : 'failed' ?>">
                            <?= number_format($attempt['score'], 1) ?>%
                        </div>
                        <p><?= $attempt['passed'] ? 'Đạt' : 'Không đạt' ?></p>
                    </div>
                    <a href="quiz-result.php?attempt=<?= $attempt['id'] ?>" class="btn-outline btn-sm">
                        Xem chi tiết
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Start Quiz -->
        <div class="quiz-start-section">
            <?php if (count($attempts) >= $quiz['max_attempts']): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    Bạn đã hết số lần làm bài cho bài kiểm tra này.
                </div>
            <?php else: ?>
                <button onclick="startQuiz()" class="btn-primary-large">
                    <i class="fas fa-play"></i> Bắt đầu làm bài
                </button>
            <?php endif; ?>
        </div>

        <!-- Quiz Form (Hidden initially) -->
        <div id="quizForm" class="quiz-form" style="display: none;">
            <div class="quiz-timer">
                <i class="fas fa-clock"></i> Thời gian còn lại: <span id="timer"><?= $quiz['duration'] ?>:00</span>
            </div>

            <form id="quizAnswerForm">
                <input type="hidden" name="quiz_id" value="<?= $quizId ?>">
                <input type="hidden" name="attempt_id" id="attemptId">

                <?php foreach ($questions as $index => $question): 
                    $stmt = $pdo->prepare("SELECT * FROM quiz_options WHERE question_id = ?");
                    $stmt->execute([$question['id']]);
                    $options = $stmt->fetchAll();
                ?>
                <div class="question-card">
                    <div class="question-header">
                        <span class="question-number">Câu <?= $index + 1 ?></span>
                        <span class="question-points"><?= $question['points'] ?> điểm</span>
                    </div>
                    <h3 class="question-text"><?= htmlspecialchars($question['question']) ?></h3>

                    <?php if ($question['question_type'] === 'multiple_choice'): ?>
                        <div class="options-list">
                            <?php foreach ($options as $option): ?>
                            <label class="option-item">
                                <input type="radio" 
                                       name="answer_<?= $question['id'] ?>" 
                                       value="<?= $option['id'] ?>" 
                                       required>
                                <span class="option-text"><?= htmlspecialchars($option['option_text']) ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    <?php elseif ($question['question_type'] === 'true_false'): ?>
                        <div class="options-list">
                            <label class="option-item">
                                <input type="radio" name="answer_<?= $question['id'] ?>" value="true" required>
                                <span class="option-text">Đúng</span>
                            </label>
                            <label class="option-item">
                                <input type="radio" name="answer_<?= $question['id'] ?>" value="false" required>
                                <span class="option-text">Sai</span>
                            </label>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>

                <div class="quiz-submit-section">
                    <button type="button" onclick="submitQuiz()" class="btn-primary-large">
                        <i class="fas fa-paper-plane"></i> Nộp bài
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let attemptId = null;
let timerInterval = null;
let timeLeft = <?= $quiz['duration'] * 60 ?>; // Convert to seconds

function startQuiz() {
    // Create new attempt via AJAX
    fetch('../api/quiz_api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: 'start_attempt',
            quiz_id: <?= $quizId ?>
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            attemptId = data.attempt_id;
            document.getElementById('attemptId').value = attemptId;
            document.querySelector('.quiz-start-section').style.display = 'none';
            document.getElementById('quizForm').style.display = 'block';
            startTimer();
        } else {
            alert(data.message);
        }
    });
}

function startTimer() {
    timerInterval = setInterval(() => {
        timeLeft--;
        
        let minutes = Math.floor(timeLeft / 60);
        let seconds = timeLeft % 60;
        document.getElementById('timer').textContent = 
            `${minutes}:${seconds.toString().padStart(2, '0')}`;
        
        if (timeLeft <= 0) {
            clearInterval(timerInterval);
            alert('Hết giờ! Bài làm sẽ tự động nộp.');
            submitQuiz();
        }
    }, 1000);
}

function submitQuiz() {
    if (!confirm('Bạn có chắc muốn nộp bài?')) return;
    
    clearInterval(timerInterval);
    
    const formData = new FormData(document.getElementById('quizAnswerForm'));
    const answers = {};
    
    for (let [key, value] of formData.entries()) {
        if (key.startsWith('answer_')) {
            let questionId = key.replace('answer_', '');
            answers[questionId] = value;
        }
    }
    
    fetch('../api/quiz_api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: 'submit_answers',
            attempt_id: attemptId,
            answers: answers,
            time_spent: <?= $quiz['duration'] * 60 ?> - timeLeft
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            window.location.href = 'quiz-result.php?attempt=' + attemptId;
        } else {
            alert(data.message);
        }
    });
}
</script>

<style>
.quiz-page { background: #f5f7fa; min-height: 100vh; padding: 40px 0; }
.quiz-header { text-align: center; margin-bottom: 40px; }
.back-link { display: inline-flex; align-items: center; gap: 8px; margin-bottom: 20px; color: #0056d2; text-decoration: none; }
.quiz-header h1 { font-size: 36px; margin-bottom: 12px; }
.quiz-info-cards { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; margin-bottom: 40px; }
.info-card { background: #fff; padding: 24px; border-radius: 12px; display: flex; align-items: center; gap: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
.info-card i { font-size: 32px; color: #0056d2; }
.info-card h3 { font-size: 28px; margin-bottom: 4px; }
.info-card p { font-size: 14px; color: #545454; }
.attempts-section { background: #fff; padding: 32px; border-radius: 12px; margin-bottom: 32px; }
.attempts-list { display: flex; flex-direction: column; gap: 16px; margin-top: 20px; }
.attempt-card { display: flex; justify-content: space-between; align-items: center; padding: 20px; background: #f5f7fa; border-radius: 8px; }
.score-circle { width: 64px; height: 64px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: 700; }
.score-circle.passed { background: #dcfce7; color: #0cae74; }
.score-circle.failed { background: #fee; color: #c33; }
.quiz-start-section { text-align: center; padding: 60px 0; }
.quiz-timer { background: #fff; padding: 16px 24px; border-radius: 8px; text-align: center; font-size: 18px; font-weight: 600; margin-bottom: 32px; color: #0056d2; position: sticky; top: 90px; z-index: 100; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
.question-card { background: #fff; padding: 32px; border-radius: 12px; margin-bottom: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
.question-header { display: flex; justify-content: space-between; margin-bottom: 16px; }
.question-number { background: #e8f0fe; color: #0056d2; padding: 6px 16px; border-radius: 20px; font-weight: 600; }
.question-points { color: #545454; font-size: 14px; }
.question-text { font-size: 20px; margin-bottom: 24px; }
.options-list { display: flex; flex-direction: column; gap: 12px; }
.option-item { display: flex; align-items: center; gap: 12px; padding: 16px; background: #f5f7fa; border-radius: 8px; cursor: pointer; transition: all 0.2s; }
.option-item:hover { background: #e8f0fe; }
.option-item input[type="radio"] { width: 20px; height: 20px; }
.option-text { font-size: 16px; }
.quiz-submit-section { text-align: center; margin-top: 40px; }
.alert-warning { background: #fef3c7; color: #92400e; padding: 16px; border-radius: 8px; display: flex; align-items: center; gap: 12px; }
@media (max-width: 768px) {
    .quiz-info-cards { grid-template-columns: repeat(2, 1fr); }
}
</style>

<?php include '../includes/footer.php'; ?>
