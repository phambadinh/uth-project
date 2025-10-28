<?php
$pageTitle = "Quản lý Quiz";
include 'includes/admin-header.php';

// Handle delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $quizId = (int)$_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM quizzes WHERE id = ?");
    if ($stmt->execute([$quizId])) {
        $_SESSION['flash_message'] = 'Đã xóa quiz thành công';
        $_SESSION['flash_type'] = 'success';
    }
    header('Location: quizzes.php');
    exit;
}

// Get quizzes
$stmt = $pdo->query("
    SELECT q.*, c.title as course_title, c.category,
           COUNT(DISTINCT qq.id) as total_questions
    FROM quizzes q
    JOIN courses c ON q.course_id = c.id
    LEFT JOIN quiz_questions qq ON q.id = qq.quiz_id
    GROUP BY q.id
    ORDER BY q.created_at DESC
");
$quizzes = $stmt->fetchAll();

$totalQuizzes = count($quizzes);
?>

<div class="admin-page-header">
    <div>
        <h1>Quản lý Quiz</h1>
        <p>Tổng số: <?= number_format($totalQuizzes) ?> quiz</p>
    </div>
    <a href="quiz-create.php" class="btn-primary">
        <i class="fas fa-plus"></i> Tạo quiz mới
    </a>
</div>

<?php if (isset($_SESSION['flash_message'])): ?>
<div class="alert alert-<?= $_SESSION['flash_type'] ?>">
    <?= $_SESSION['flash_message'] ?>
</div>
<?php 
    unset($_SESSION['flash_message']);
    unset($_SESSION['flash_type']);
endif; ?>

<!-- Quizzes Table -->
<div class="admin-card">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên Quiz</th>
                    <th>Khóa học</th>
                    <th>Số câu hỏi</th>
                    <th>Thời gian</th>
                    <th>Điểm đạt</th>
                    <th>Số lần làm</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($quizzes)): ?>
                <tr>
                    <td colspan="8" class="text-center text-muted">Chưa có quiz nào</td>
                </tr>
                <?php else: ?>
                    <?php foreach ($quizzes as $quiz): ?>
                    <tr>
                        <td><?= $quiz['id'] ?></td>
                        <td><strong><?= htmlspecialchars($quiz['title']) ?></strong></td>
                        <td>
                            <span class="badge" style="background: <?= CATEGORY_COLORS[$quiz['category']] ?>">
                                <?= CATEGORY_ICONS[$quiz['category']] ?>
                            </span>
                            <?= htmlspecialchars($quiz['course_title']) ?>
                        </td>
                        <td class="text-center"><?= $quiz['total_questions'] ?> câu</td>
                        <td><?= $quiz['duration'] ?> phút</td>
                        <td><?= $quiz['pass_score'] ?>%</td>
                        <td><?= $quiz['max_attempts'] ?> lần</td>
                        <td>
                            <div class="action-buttons">
                                <a href="quiz-questions.php?quiz_id=<?= $quiz['id'] ?>" 
                                   class="btn-icon" title="Câu hỏi">
                                    <i class="fas fa-list"></i>
                                </a>
                                <a href="quiz-edit.php?id=<?= $quiz['id'] ?>" 
                                   class="btn-icon" title="Sửa">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="quizzes.php?action=delete&id=<?= $quiz['id'] ?>" 
                                   class="btn-icon btn-danger" 
                                   onclick="return confirmDelete('Xóa quiz này?')"
                                   title="Xóa">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/admin-footer.php'; ?>
