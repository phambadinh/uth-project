<?php
$pageTitle = "Quản lý bài học";
include 'includes/admin-header.php';

// Handle delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $lessonId = (int)$_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM lessons WHERE id = ?");
    if ($stmt->execute([$lessonId])) {
        $_SESSION['flash_message'] = 'Đã xóa bài học thành công';
        $_SESSION['flash_type'] = 'success';
    }
    header('Location: lessons.php' . (isset($_GET['course_id']) ? '?course_id=' . $_GET['course_id'] : ''));
    exit;
}

// Get filters
$courseId = $_GET['course_id'] ?? null;

// Build query
$sql = "SELECT l.*, c.title as course_title, c.category
        FROM lessons l 
        JOIN courses c ON l.course_id = c.id";

if ($courseId) {
    $sql .= " WHERE l.course_id = ?";
    $stmt = $pdo->prepare($sql . " ORDER BY l.order_num ASC");
    $stmt->execute([$courseId]);
} else {
    $stmt = $pdo->query($sql . " ORDER BY l.created_at DESC LIMIT 100");
}

$lessons = $stmt->fetchAll();

// Get course info if filtered
$course = null;
if ($courseId) {
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
    $stmt->execute([$courseId]);
    $course = $stmt->fetch();
}

// Get all courses for filter
$allCourses = $pdo->query("SELECT id, title FROM courses ORDER BY title")->fetchAll();
?>

<div class="admin-page-header">
    <div>
        <h1>Quản lý bài học</h1>
        <?php if ($course): ?>
        <p>Khóa học: <strong><?= htmlspecialchars($course['title']) ?></strong></p>
        <?php endif; ?>
    </div>
    <div>
        <a href="lesson-create.php<?= $courseId ? '?course_id='.$courseId : '' ?>" class="btn-primary">
            <i class="fas fa-plus"></i> Thêm bài học
        </a>
        <?php if ($course): ?>
        <a href="courses.php" class="btn-outline">
            <i class="fas fa-arrow-left"></i> Về danh sách khóa học
        </a>
        <?php endif; ?>
    </div>
</div>

<?php if (isset($_SESSION['flash_message'])): ?>
<div class="alert alert-<?= $_SESSION['flash_type'] ?>">
    <?= $_SESSION['flash_message'] ?>
</div>
<?php 
    unset($_SESSION['flash_message']);
    unset($_SESSION['flash_type']);
endif; ?>

<!-- Filter -->
<?php if (!$courseId): ?>
<div class="admin-card">
    <form method="GET" class="filters-form">
        <div class="filter-group">
            <label>Lọc theo khóa học:</label>
            <select name="course_id" class="form-control" onchange="this.form.submit()">
                <option value="">Tất cả khóa học</option>
                <?php foreach ($allCourses as $c): ?>
                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['title']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>
</div>
<?php endif; ?>

<!-- Lessons Table -->
<div class="admin-card">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th width="60">ID</th>
                    <th>Tiêu đề bài học</th>
                    <th>Khóa học</th>
                    <th width="120">Thứ tự</th>
                    <th width="100">Thời lượng</th>
                    <th width="100">Loại video</th>
                    <th width="100">Trạng thái</th>
                    <th width="150">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($lessons)): ?>
                <tr>
                    <td colspan="8" class="text-center text-muted">Chưa có bài học nào</td>
                </tr>
                <?php else: ?>
                    <?php foreach ($lessons as $lesson): ?>
                    <tr>
                        <td><?= $lesson['id'] ?></td>
                        <td>
                            <strong><?= htmlspecialchars($lesson['title']) ?></strong>
                        </td>
                        <td>
                            <span class="badge" style="background: <?= CATEGORY_COLORS[$lesson['category']] ?>">
                                <?= CATEGORY_ICONS[$lesson['category']] ?>
                            </span>
                            <?= htmlspecialchars($lesson['course_title']) ?>
                        </td>
                        <td class="text-center">
                            <span class="order-badge"><?= $lesson['order_num'] ?></span>
                        </td>
                        <td><?= $lesson['duration'] ?> phút</td>
                        <td>
                            <?php
                            $videoTypes = ['youtube' => 'YouTube', 'vimeo' => 'Vimeo', 'local' => 'Local'];
                            echo $videoTypes[$lesson['video_type']] ?? 'N/A';
                            ?>
                        </td>
                        <td>
                            <?php if ($lesson['is_free']): ?>
                                <span class="badge badge-success">Miễn phí</span>
                            <?php else: ?>
                                <span class="badge badge-warning">Premium</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="../learning/lesson.php?id=<?= $lesson['id'] ?>" 
                                   class="btn-icon" title="Xem" target="_blank">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="lesson-edit.php?id=<?= $lesson['id'] ?>" 
                                   class="btn-icon" title="Sửa">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="lessons.php?action=delete&id=<?= $lesson['id'] ?><?= $courseId ? '&course_id='.$courseId : '' ?>" 
                                   class="btn-icon btn-danger" 
                                   onclick="return confirmDelete('Xóa bài học này?')"
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
