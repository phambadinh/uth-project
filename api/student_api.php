<?php
/**
 * Student API
 * Student-specific actions (bookmarks, notes, etc.)
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
        case 'save_note':
            // Save lesson notes
            $lessonId = (int)($_POST['lesson_id'] ?? 0);
            $notes = trim($_POST['notes'] ?? '');
            
            if (!$lessonId) {
                throw new Exception('Lesson ID is required');
            }
            
            $stmt = $pdo->prepare("
                INSERT INTO lesson_notes (user_id, lesson_id, notes, created_at)
                VALUES (?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE notes = ?, updated_at = NOW()
            ");
            $stmt->execute([$userId, $lessonId, $notes, $notes]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Note saved successfully'
            ]);
            break;
            
        case 'get_notes':
            // Get lesson notes
            $lessonId = (int)($_GET['lesson_id'] ?? 0);
            
            $stmt = $pdo->prepare("
                SELECT notes FROM lesson_notes 
                WHERE user_id = ? AND lesson_id = ?
            ");
            $stmt->execute([$userId, $lessonId]);
            $result = $stmt->fetch();
            
            echo json_encode([
                'success' => true,
                'notes' => $result ? $result['notes'] : ''
            ]);
            break;
            
        case 'get_dashboard_stats':
            // Get student dashboard statistics
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE user_id = ?");
            $stmt->execute([$userId]);
            $enrolledCourses = $stmt->fetchColumn();
            
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE user_id = ? AND progress >= 100");
            $stmt->execute([$userId]);
            $completedCourses = $stmt->fetchColumn();
            
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM certificates WHERE user_id = ?");
            $stmt->execute([$userId]);
            $certificates = $stmt->fetchColumn();
            
            $stmt = $pdo->prepare("SELECT AVG(progress) FROM enrollments WHERE user_id = ?");
            $stmt->execute([$userId]);
            $avgProgress = $stmt->fetchColumn();
            
            echo json_encode([
                'success' => true,
                'stats' => [
                    'enrolled_courses' => $enrolledCourses,
                    'completed_courses' => $completedCourses,
                    'certificates' => $certificates,
                    'average_progress' => round($avgProgress, 2)
                ]
            ]);
            break;
            
        case 'get_recent_activity':
            // Get recent learning activity
            $stmt = $pdo->prepare("
                SELECT 
                    l.title as lesson_title,
                    c.title as course_title,
                    c.category,
                    vp.last_watched
                FROM video_progress vp
                JOIN lessons l ON vp.lesson_id = l.id
                JOIN courses c ON l.course_id = c.id
                WHERE vp.user_id = ?
                ORDER BY vp.last_watched DESC
                LIMIT 10
            ");
            $stmt->execute([$userId]);
            $activity = $stmt->fetchAll();
            
            echo json_encode([
                'success' => true,
                'activity' => $activity
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
