<?php
$pageTitle = "Quản lý khóa học";
include 'includes/admin-header.php';

// Handle delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $courseId = (int)$_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
    if ($stmt->execute([$courseId])) {
        $_SESSION['flash_message'] = 'Đã xóa khóa học thành công';
        $_SESSION['flash_type'] = 'success';
    }
    header('Location: courses.php');
    exit;
}

// Get filters
$category = $_GET['category'] ?? '';
$status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

// Build query
$sql = "SELECT c.*, u.fullname as instructor_name FROM courses c 
        LEFT JOIN users u ON c.instructor_id = u.id WHERE 1=1";
$params = [];

if ($category) {
    $sql .= " AND c.category = ?";
    $params[] = $category;
}

if ($status) {
    $sql .= " AND c.status = ?";
    $params[] = $status;
}

if ($search) {
    $sql .= " AND (c.title LIKE ? OR c.description LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

$sql .= " ORDER BY c.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$courses = $stmt->fetchAll();

// Get counts
$totalCourses = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
$publishedCourses = $pdo->query("SELECT COUNT(*) FROM courses WHERE status='published'")->fetchColumn();
$draftCourses = $pdo->query("SELECT COUNT(*) FROM courses WHERE status='draft'")->fetchColumn();
?>

<div class="admin-page-header">
    <div>
        <h1>Quản lý khóa học</h1>
        <p>Tổng số: <?= number_format($totalCourses) ?> khóa học</p>
    </div>
    <a href="course-create.php" class="btn-primary">
        <i class="fas fa-plus"></i> Tạo khóa học mới
    </a>
</div>

<!-- Stats -->
<div class="stats-row">
    <div class="stat-box-small">
        <h4><?= number_format($publishedCourses) ?></h4>
        <p>Đã xuất bản</p>
    </div>
    <div class="stat-box-small">
        <h4><?= number_format($draftCourses) ?></h4>
        <p>Bản nháp</p>
    </div>
    <div class="stat-box-small">
        <h4><?= $totalCourses > 0 ? number_format(($publishedCourses / $totalCourses) * 100, 0) : 0 ?>%</h4>
        <p>Tỷ lệ xuất bản</p>
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

<!-- Filters -->
<div class="admin-card">
    <form method="GET" class="filters-form">
        <div class="filter-group">
            <input type="text" name="search" placeholder="Tìm kiếm khóa học..." 
                   value="<?= htmlspecialchars($search) ?>" class="form-control">
        </div>
        
        <div class="filter-group">
            <select name="category" class="form-control">
                <option value="">Tất cả danh mục</option>
                <?php foreach (CATEGORIES as $key => $value): ?>
                <option value="<?= $key ?>" <?= $category === $key ? 'selected' : '' ?>>
                    <?= $key ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="filter-group">
            <select name="status" class="form-control">
                <option value="">Tất cả trạng thái</option>
                <option value="published" <?= $status === 'published' ? 'selected' : '' ?>>Đã xuất bản</option>
                <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Bản nháp</option>
                <option value="archived" <?= $status === 'archived' ? 'selected' : '' ?>>Lưu trữ</option>
            </select>
        </div>
        
        <button type="submit" class="btn-primary">
            <i class="fas fa-search"></i> Tìm kiếm
        </button>
        
        <a href="courses.php" class="btn-outline">
            <i class="fas fa-redo"></i> Reset
        </a>
    </form>
</div>

<!-- Courses Table -->
<div class="admin-card">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Khóa học</th>
                    <th>Danh mục</th>
                    <th>Giảng viên</th>
                    <th>Giá</th>
                    <th>Học viên</th>
                    <th>Rating</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($courses as $course): ?>
                <tr>
                    <td><?= $course['id'] ?></td>
                    <td>
                        <div class="course-info">
                            <img src="<?= htmlspecialchars($course['thumbnail']) ?>" alt="Thumbnail" class="table-thumbnail">
                            <div>
                                <strong><?= htmlspecialchars($course['title']) ?></strong>
                                <p class="text-muted"><?= $course['level'] ?> • <?= $course['duration'] ?></p>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge" style="background: <?= CATEGORY_COLORS[$course['category']] ?>">
                            <?= CATEGORY_ICONS[$course['category']] ?> <?= $course['category'] ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars($course['instructor_name'] ?? 'N/A') ?></td>
                    <td>
                        <?php if ($course['price'] > 0): ?>
                            <strong><?= number_format($course['price']) ?>đ</strong>
                        <?php else: ?>
                            <span class="badge badge-success">Miễn phí</span>
                        <?php endif; ?>
                    </td>
                    <td><?= number_format($course['students']) ?></td>
                    <td>
                        <i class="fas fa-star text-warning"></i> 
                        <?= number_format($course['rating'], 1) ?>
                    </td>
                    <td>
                        <?php
                        $statusColors = ['draft' => 'secondary', 'published' => 'success', 'archived' => 'warning'];
                        $statusLabels = ['draft' => 'Nháp', 'published' => 'Đã xuất bản', 'archived' => 'Lưu trữ'];
                        ?>
                        <span class="badge badge-<?= $statusColors[$course['status']] ?>">
                            <?= $statusLabels[$course['status']] ?>
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <a href="../pages/course-detail.php?id=<?= $course['id'] ?>" 
                               class="btn-icon" title="Xem" target="_blank">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="course-edit.php?id=<?= $course['id'] ?>" 
                               class="btn-icon" title="Sửa">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="lessons.php?course_id=<?= $course['id'] ?>" 
                               class="btn-icon" title="Bài học">
                                <i class="fas fa-video"></i>
                            </a>
                            <a href="courses.php?action=delete&id=<?= $course['id'] ?>" 
                               class="btn-icon btn-danger" 
                               onclick="return confirmDelete('Xóa khóa học <?= htmlspecialchars($course['title']) ?>?')"
                               title="Xóa">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/admin-footer.php'; ?>
