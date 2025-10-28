<?php
require_once '../config/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $bio = trim($_POST['bio']);
    
    $stmt = $pdo->prepare("UPDATE users SET fullname=?, email=?, phone=?, bio=? WHERE id=?");
    if ($stmt->execute([$fullname, $email, $phone, $bio, $userId])) {
        $_SESSION['fullname'] = $fullname;
        $message = 'Cập nhật thành công!';
    }
}

// Get user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

$pageTitle = "Thông tin cá nhân - UTH Learning";
include '../includes/header.php';
?>

<div class="profile-page">
    <div class="container">
        <h1>Thông tin cá nhân</h1>
        
        <?php if($message): ?>
        <div class="alert alert-success"><?= $message ?></div>
        <?php endif; ?>
        
        <div class="profile-grid">
            <!-- Avatar Section -->
            <div class="profile-avatar-section">
                <img src="<?= ASSETS_URL ?>/images/avatars/default.png" alt="Avatar" class="profile-avatar-large">
                <button class="btn-outline">Thay đổi ảnh đại diện</button>
            </div>
            
            <!-- Form Section -->
            <div class="profile-form-section">
                <form method="POST">
                    <div class="form-group">
                        <label>Họ và tên</label>
                        <input type="text" name="fullname" class="form-control" value="<?= htmlspecialchars($user['fullname']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" disabled>
                    </div>
                    
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Số điện thoại</label>
                        <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Giới thiệu bản thân</label>
                        <textarea name="bio" class="form-control" rows="4"><?= htmlspecialchars($user['bio']) ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn-primary">Cập nhật thông tin</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
