<?php
/**
 * Video Progress Tracking API
 * Lưu và cập nhật tiến độ xem video của học viên
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
        case 'update_progress':
            // Update video watch progress
            $lessonId = (int)($_POST['lesson_id'] ?? 0);
            $progressSeconds = (int)($_POST['progress_seconds'] ?? 0);
            $totalDuration = (int)($_POST['total_duration'] ?? 0);
            
            if (!$lessonId) {
                throw new Exception('Lesson ID is required');
            }
            
            // Calculate completion (>90% = completed)
            $completed = ($progressSeconds / $totalDuration) >= 0.9 ? 1 : 0;
            
            // Check if record exists
            $stmt = $pdo->prepare("SELECT id FROM video_progress WHERE user_id = ? AND lesson_id = ?");
            $stmt->execute([$userId, $lessonId]);
            
            if ($stmt->fetch()) {
                // Update existing
                $stmt = $pdo->prepare("
                    UPDATE video_progress 
                    SET progress_seconds = ?, total_duration = ?, completed = ?, last_watched = NOW()
                    WHERE user_id = ? AND lesson_id = ?
                ");
                $stmt->execute([$progressSeconds, $totalDuration, $completed, $userId, $lessonId]);
            } else {
                // Insert new
                $stmt = $pdo->prepare("
                    INSERT INTO video_progress (user_id, lesson_id, progress_seconds, total_duration, completed, last_watched)
                    VALUES (?, ?, ?, ?, ?, NOW())
                ");
                $stmt->execute([$userId, $lessonId, $progressSeconds, $totalDuration, $completed]);
            }
            
            // Update course progress
            updateCourseProgress($pdo, $userId, $lessonId);
            
            echo json_encode([
                'success' => true,
                'completed' => $completed,
                'message' => 'Progress updated successfully'
            ]);
            break;
            
        case 'get_progress':
            // Get video progress
            $lessonId = (int)($_GET['lesson_id'] ?? 0);
            
            if (!$lessonId) {
                throw new Exception('Lesson ID is required');
            }
            
            $stmt = $pdo->prepare("
                SELECT progress_seconds, total_duration, completed 
                FROM video_progress 
                WHERE user_id = ? AND lesson_id = ?
            ");
            $stmt->execute([$userId, $lessonId]);
            $progress = $stmt->fetch();
            
            echo json_encode([
                'success' => true,
                'progress' => $progress ?: [
                    'progress_seconds' => 0,
                    'total_duration' => 0,
                    'completed' => 0
                ]
            ]);
            break;
            
        case 'mark_complete':
            // Mark lesson as completed
            $lessonId = (int)($_POST['lesson_id'] ?? 0);
            
            if (!$lessonId) {
                throw new Exception('Lesson ID is required');
            }
            
            $stmt = $pdo->prepare("
                INSERT INTO video_progress (user_id, lesson_id, completed, last_watched)
                VALUES (?, ?, 1, NOW())
                ON DUPLICATE KEY UPDATE completed = 1, last_watched = NOW()
            ");
            $stmt->execute([$userId, $lessonId]);
            
            updateCourseProgress($pdo, $userId, $lessonId);
            
            echo json_encode([
                'success' => true,
                'message' => 'Lesson marked as completed'
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

/**
 * Update overall course progress based on completed lessons
 */
function updateCourseProgress($pdo, $userId, $lessonId) {
    // Get course ID from lesson
    $stmt = $pdo->prepare("SELECT course_id FROM lessons WHERE id = ?");
    $stmt->execute([$lessonId]);
    $courseId = $stmt->fetchColumn();
    
    if (!$courseId) return;
    
    // Count total lessons in course
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM lessons WHERE course_id = ?");
    $stmt->execute([$courseId]);
    $totalLessons = $stmt->fetchColumn();
    
    // Count completed lessons
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM video_progress vp
        JOIN lessons l ON vp.lesson_id = l.id
        WHERE vp.user_id = ? AND l.course_id = ? AND vp.completed = 1
    ");
    $stmt->execute([$userId, $courseId]);
    $completedLessons = $stmt->fetchColumn();
    
    // Calculate progress percentage
    $progress = $totalLessons > 0 ? ($completedLessons / $totalLessons) * 100 : 0;
    
    // Update enrollment progress
    $stmt = $pdo->prepare("
        UPDATE enrollments 
        SET progress = ?, last_accessed = NOW()
        WHERE user_id = ? AND course_id = ?
    ");
    $stmt->execute([$progress, $userId, $courseId]);
    
    // If 100% completed, generate certificate
    if ($progress >= 100) {
        generateCertificate($pdo, $userId, $courseId);
    }
}

/**
 * Generate certificate when course is completed
 */
function generateCertificate($pdo, $userId, $courseId) {
    // Check if certificate already exists
    $stmt = $pdo->prepare("SELECT id FROM certificates WHERE user_id = ? AND course_id = ?");
    $stmt->execute([$userId, $courseId]);
    
    if (!$stmt->fetch()) {
        // Generate unique certificate code
        $code = 'UTH-' . strtoupper(substr(md5($userId . $courseId . time()), 0, 10));
        
        $stmt = $pdo->prepare("
            INSERT INTO certificates (user_id, course_id, certificate_code, issued_date)
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$userId, $courseId, $code]);
    }
}
?>
