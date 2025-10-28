<?php
require_once '../config/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Get enrolled courses with progress
$stmt = $pdo->prepare("
    SELECT c.*, e.progress, e.enrolled_at, e.last_accessed
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    WHERE e.user_id = ?
    ORDER BY e.last_accessed DESC
");
$stmt->execute([$userId]);
$courses = $stmt->fetchAll();

$pageTitle = "Tiến độ học tập - UTH Learning";
include '../includes/header.php';
?>

<div class="progress-page">
    <div class="container">
        <h1>Tiến độ học tập</h1>
        
        <div class="progress-stats">
            <div class="stat-card">
                <h3><?= count($courses) ?></h3>
                <p>Khóa học đang học</p>
            </div>
            <div class="stat-card">
                <h3><?= count(array_filter($courses, fn($c) => $c['progress'] >= 100)) ?></h3>
                <p>Khóa học hoàn thành</p>
            </div>
            <div class="stat-card">
                <h3><?= count(array_filter($courses, fn($c) => $c['progress'] > 0 && $c['progress'] < 100)) ?></h3>
                <p>Đang tiếp tục</p>
            </div>
        </div>
        
        <div class="courses-progress-list">
            <?php foreach($courses as $course): ?>
            <div class="course-progress-card">
                <img src="<?= htmlspecialchars($course['thumbnail']) ?>" alt="">
                <div class="course-progress-info">
                    <h3><?= htmlspecialchars($course['title']) ?></h3>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?= $course['progress'] ?>%"></div>
                    </div>
                    <p><?= number_format($course['progress'], 0) ?>% hoàn thành</p>
                </div>
                <a href="../learning/lesson.php?course=<?= $course['id'] ?>" class="btn-primary">Tiếp tục học</a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
