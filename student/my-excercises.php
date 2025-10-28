<?php
require_once '../config/config.php';
require_once '../includes/functions.php';

requireLogin();

$userId = $_SESSION['user_id'];
$submissions = getUserSubmissions($userId);

$pageTitle = "Bài tập của tôi";
include '../includes/header.php';
?>

<section class="my-exercises-page">
    <div class="container">
        <h1>✍️ Bài tập của tôi</h1>
        
        <?php if (empty($submissions)): ?>
            <div class="empty-state">
                <h3>Bạn chưa nộp bài tập nào</h3>
                <p>Hãy tham gia các khóa học và hoàn thành bài tập</p>
                <a href="<?= BASE_URL ?>/pages/courses.php" class="btn btn-primary">
                    Khám phá khóa học
                </a>
            </div>
        <?php else: ?>
            <div class="submissions-table">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Bài tập</th>
                            <th>Khóa học</th>
                            <th>Điểm</th>
                            <th>Trạng thái</th>
                            <th>Thời gian nộp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($submissions as $sub): ?>
                            <tr>
                                <td><?= htmlspecialchars($sub['exercise_title']) ?></td>
                                <td><?= htmlspecialchars($sub['course_title']) ?></td>
                                <td>
                                    <strong><?= $sub['score'] ?>/<?= $sub['max_points'] ?></strong>
                                </td>
                                <td>
                                    <?php if ($sub['score'] >= $sub['max_points'] * 0.7): ?>
                                        <span class="badge badge-success">✓ Đạt</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Chưa đạt</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($sub['submitted_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
