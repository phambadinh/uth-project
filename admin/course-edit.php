<?php
$pageTitle = "Chỉnh sửa khóa học";
include 'includes/admin-header.php';

$courseId = (int)($_GET['id'] ?? 0);
$message = '';
$messageType = '';

// Get course details
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->execute([$courseId]);
$course = $stmt->fetch();

if (!$course) {
    header('Location: courses.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $slug = strtolower(str_replace(' ', '-', preg_replace('/[^A-Za-z0-9 ]/', '', $title)));
    $description = trim($_POST['description']);
    $category = $_POST['category'];
    $level = $_POST['level'];
    $duration = trim($_POST['duration']);
    $price = (float)$_POST['price'];
    $status = $_POST['status'];
    $thumbnail = $course['thumbnail'];
    
    // Handle thumbnail upload
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === 0) {
        $uploadDir = '../uploads/courses/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        
        $fileName = time() . '_' . basename($_FILES['thumbnail']['name']);
        $targetPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $targetPath)) {
            $thumbnail = '/uploads/courses/' . $fileName;
            
            // Delete old thumbnail if exists
            if ($course['thumbnail'] && file_exists('..' . $course['thumbnail'])) {
                @unlink('..' . $course['thumbnail']);
            }
        }
    }
    
    try {
        $stmt = $pdo->prepare("
            UPDATE courses 
            SET title=?, slug=?, description=?, thumbnail=?, category=?, level=?, duration=?, price=?, status=?, updated_at=NOW()
            WHERE id=?
        ");
        
        if ($stmt->execute([$title, $slug, $description, $thumbnail, $category, $level, $duration, $price, $status, $courseId])) {
            $_SESSION['flash_message'] = 'Cập nhật khóa học thành công!';
            $_SESSION['flash_type'] = 'success';
            header('Location: courses.php');
            exit;
        }
    } catch (PDOException $e) {
        $message = 'Lỗi: ' . $e->getMessage();
        $messageType = 'error';
    }
}
?>

<div class="admin-page-header">
    <h1>Chỉnh sửa khóa học: <?= htmlspecialchars($course['title']) ?></h1>
    <a href="courses.php" class="btn-outline">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div>

<?php if ($message): ?>
<div class="alert alert-<?= $messageType ?>">
    <?= $message ?>
</div>
<?php endif; ?>

<div class="admin-card">
    <form method="POST" enctype="multipart/form-data" class="admin-form">
        <div class="form-section">
            <h3>Thông tin cơ bản</h3>
            
            <div class="form-group">
                <label for="title">Tiêu đề khóa học *</label>
                <input type="text" id="title" name="title" class="form-control" 
                       value="<?= htmlspecialchars($course['title']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="description">Mô tả ngắn *</label>
                <textarea id="description" name="description" class="form-control" rows="4" required><?= htmlspecialchars($course['description']) ?></textarea>
            </div>
            
            <div class="form-row-2col">
                <div class="form-group">
                    <label for="category">Danh mục *</label>
                    <select id="category" name="category" class="form-control" required>
                        <?php foreach (CATEGORIES as $key => $value): ?>
                        <option value="<?= $key ?>" <?= $course['category'] === $key ? 'selected' : '' ?>>
                            <?= CATEGORY_ICONS[$key] ?> <?= $key ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="level">Cấp độ *</label>
                    <select id="level" name="level" class="form-control" required>
                        <?php foreach (LEVELS as $key => $value): ?>
                        <option value="<?= $key ?>" <?= $course['level'] === $key ? 'selected' : '' ?>>
                            <?= $value ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-row-2col">
                <div class="form-group">
                    <label for="duration">Thời lượng</label>
                    <input type="text" id="duration" name="duration" class="form-control" 
                           value="<?= htmlspecialchars($course['duration']) ?>">
                </div>
                
                <div class="form-group">
                    <label for="price">Giá (VNĐ)</label>
                    <input type="number" id="price" name="price" class="form-control" 
                           value="<?= $course['price'] ?>" min="0" step="1000">
                </div>
            </div>
            
            <div class="form-group">
                <label for="status">Trạng thái</label>
                <select id="status" name="status" class="form-control">
                    <option value="draft" <?= $course['status'] === 'draft' ? 'selected' : '' ?>>Bản nháp</option>
                    <option value="published" <?= $course['status'] === 'published' ? 'selected' : '' ?>>Đã xuất bản</option>
                    <option value="archived" <?= $course['status'] === 'archived' ? 'selected' : '' ?>>Lưu trữ</option>
                </select>
            </div>
        </div>
        
        <div class="form-section">
            <h3>Hình ảnh</h3>
            
            <div class="current-thumbnail">
                <p>Ảnh hiện tại:</p>
                <img src="<?= htmlspecialchars($course['thumbnail']) ?>" alt="Thumbnail" style="max-width: 400px; border-radius: 8px;">
            </div>
            
            <div class="form-group">
                <label for="thumbnail">Thay đổi ảnh thumbnail</label>
                <input type="file" id="thumbnail" name="thumbnail" class="form-control" accept="image/*">
                <small class="form-text">Kích thước khuyến nghị: 1280x720px (16:9)</small>
            </div>
            
            <div id="thumbnailPreview" class="thumbnail-preview"></div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn-primary">
                <i class="fas fa-save"></i> Lưu thay đổi
            </button>
            <a href="courses.php" class="btn-outline">Hủy</a>
        </div>
    </form>
</div>

<script>
// Preview thumbnail
document.getElementById('thumbnail').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('thumbnailPreview').innerHTML = 
                '<p>Ảnh mới:</p><img src="' + e.target.result + '" style="max-width: 400px; border-radius: 8px;">';
        }
        reader.readAsDataURL(file);
    }
});
</script>

<?php include 'includes/admin-footer.php'; ?>
