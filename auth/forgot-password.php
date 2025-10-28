<?php
require_once '../config/config.php';

// Logic để gửi email reset password
// Sử dụng PHPMailer hoặc mail() function
// Generate reset token, lưu vào DB
// Gửi link reset: /auth/reset-password.php?token=xxx

$pageTitle = "Quên mật khẩu - UTH Learning";
include '../includes/header.php';
?>

<div class="auth-page">
    <div class="auth-container">
        <h1>Quên mật khẩu</h1>
        <p>Nhập email để nhận link đặt lại mật khẩu</p>
        
        <form method="POST" class="auth-form">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <button type="submit" class="btn-primary-large full-width">
                Gửi link đặt lại mật khẩu
            </button>
        </form>
        
        <p class="auth-footer">
            <a href="login.php">Quay lại đăng nhập</a>
        </p>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
