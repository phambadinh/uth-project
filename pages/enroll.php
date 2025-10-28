<?php
require_once '../config/config.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$courseId = (int)($_POST['course_id'] ?? 0);
$userId = $_SESSION['user_id'];

if (!$courseId) {
    header('Location: ../pages/courses.php');
    exit;
}

// Get course details
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ? AND status='published'");
$stmt->execute([$courseId]);
$course = $stmt->fetch();

if (!$course) {
    header('Location: ../pages/courses.php');
    exit;
}

// Check if already enrolled
$stmt = $pdo->prepare("SELECT * FROM enrollments WHERE user_id = ? AND course_id = ?");
$stmt->execute([$userId, $courseId]);

if ($stmt->fetch()) {
    // Already enrolled - redirect to learning
    header('Location: ../learning/lesson.php?course=' . $courseId);
    exit;
}

// Check if course is paid
if ($course['price'] > 0) {
    // Redirect to payment
    header('Location: ../payment/checkout.php?course_id=' . $courseId);
    exit;
}

// Enroll student (free course)
try {
    $stmt = $pdo->prepare("
        INSERT INTO enrollments (user_id, course_id, progress, enrolled_at, last_accessed)
        VALUES (?, ?, 0, NOW(), NOW())
    ");
    $stmt->execute([$userId, $courseId]);
    
    // Update course student count
    $stmt = $pdo->prepare("UPDATE courses SET students = students + 1 WHERE id = ?");
    $stmt->execute([$courseId]);
    
    // Redirect to first lesson
    header('Location: ../learning/lesson.php?course=' . $courseId);
    exit;
    
} catch (PDOException $e) {
    die('Enrollment failed: ' . $e->getMessage());
}
?>
