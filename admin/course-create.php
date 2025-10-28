<?php
$pageTitle = "Tạo khóa học mới";
include 'includes/admin-header.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $slug = strtolower(str_replace(' ', '-', preg_replace('/[^A-Za-z0-9 ]/', '', $title)));
    $description = trim($_POST['description']);
    $category = $_POST['category'];
    $level = $_POST['level'];
    $duration = trim($_POST['duration']);
    $price = (float)$_POST['price'];
    $thumbnail = '/assets/images/courses/default.jpg'; // Default thumbnail
    
    // Handle thumbnail upload
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === 0) {
        $uploadDir = '../uploads/courses/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        
        $fileName = time() . '_' . basename($_FILES['thumbnail']['name']);
        $targetPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $targetPath)) {
            $thumbnail = '/uploads/courses/' . $fileName;
        }
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO courses (title, slug, description, thumbnail, instructor_id, category, level, duration, price, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'draft', NOW())
        ");
        
        if ($stmt->execute([$title, $slug, $description, $thumbnail, $_SESSION['user_id'], $category, $level, $duration, $price])) {
            $_SESSION['flash_message'] = 'Tạo khóa học thành công!';
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
    <h1>Tạo khóa học mới</h1>
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
                       placeholder="VD: HTML Cơ Bản - Xây Dựng Website Từ Đầu" required>
            </div>
            
            <div class="form-group">
                <label for="description">Mô tả ngắn *</label>
                <textarea id="description" name="description" class="form-control" rows="4" 
                          placeholder="Mô tả ngắn gọn về khóa học..." required></textarea>
            </div>
            
            <div class="form-row-2col">
                <div class="form-group">
                    <label for="category">Danh mục *</label>
                    <select id="category" name="category" class="form-control" required>
                        <option value="">-- Chọn danh mục --</option>
                        <?php foreach (CATEGORIES as $key => $value): ?>
                        <option value="<?= $key ?>"><?= CATEGORY_ICONS[$key] ?> <?= $key ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="level">Cấp độ *</label>
                    <select id="level" name="level" class="form-control" required>
                        <option value="">-- Chọn cấp độ --</option>
                        <?php foreach (LEVELS as $key => $value): ?>
                        <option value="<?= $key ?>"><?= $value ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-row-2col">
                <div class="form-group">
                    <label for="duration">Thời lượng</label>
                    <input type="text" id="duration" name="duration" class="form-control" 
                           placeholder="VD: 8 tuần, 40 giờ...">
                </div>
                
                <div class="form-group">
                    <label for="price">Giá (VNĐ)</label>
                    <input type="number" id="price" name="price" class="form-control" 
                           value="0" min="0" step="1000">
                    <small class="form-text">Nhập 0 để khóa học miễn phí</small>
                </div>
            </div>
        </div>
        
        <div class="form-section">
            <h3>Hình ảnh</h3>
            
            <div class="form-group">
                <label for="thumbnail">Ảnh thumbnail</label>
                <input type="file" id="thumbnail" name="thumbnail" class="form-control" accept="image/*">
                <small class="form-text">Kích thước khuyến nghị: 1280x720px (16:9)</small>
            </div>
            
            <div id="thumbnailPreview" class="thumbnail-preview"></div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn-primary">
                <i class="fas fa-save"></i> Tạo khóa học
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
                '<img src="' + e.target.result + '" style="max-width: 400px; border-radius: 8px;">';
        }
        reader.readAsDataURL(file);
    }
});
</script>

<?php include 'includes/admin-footer.php'; ?>
