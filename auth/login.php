<?php
require_once '../config/config.php';

$error = '';
$success = '';

// Xử lý đăng nhập
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Vui lòng nhập đầy đủ thông tin';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['role'] = $user['role'];
            
            // Redirect based on role
            if ($user['role'] === 'admin') {
                header('Location: ../admin/dashboard.php');
            } else {
                header('Location: ../student/dashboard.php');
            }
            exit;
        } else {
            $error = 'Tên đăng nhập hoặc mật khẩu không đúng';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - UTH Learning</title>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="auth-page">
    <div class="auth-container">
        <div class="auth-left">
            <div class="auth-brand">
                <a href="<?= BASE_URL ?>">
                    <img src="<?= ASSETS_URL ?>/images/logo.png" alt="UTH Learning" height="40">
                </a>
            </div>
            <h1>Chào mừng trở lại!</h1>
            <p class="auth-subtitle">Đăng nhập để tiếp tục học tập</p>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?= $success ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="username">Tên đăng nhập hoặc Email</label>
                    <input type="text" id="username" name="username" class="form-control" 
                           placeholder="Nhập tên đăng nhập hoặc email" required autofocus>
                </div>

                <div class="form-group">
                    <label for="password">Mật khẩu</label>
                    <div class="password-field">
                        <input type="password" id="password" name="password" class="form-control" 
                               placeholder="Nhập mật khẩu" required>
                        <button type="button" class="toggle-password" onclick="togglePassword()">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="form-actions">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember"> Ghi nhớ đăng nhập
                    </label>
                    <a href="forgot-password.php" class="link-primary">Quên mật khẩu?</a>
                </div>

                <button type="submit" class="btn-primary-large full-width">
                    Đăng nhập
                </button>

                <div class="auth-divider">
                    <span>hoặc</span>
                </div>

                <button type="button" class="btn-social btn-google" onclick="alert('Tính năng đang phát triển')">
                    <i class="fab fa-google"></i> Đăng nhập với Google
                </button>

                <button type="button" class="btn-social btn-facebook" onclick="alert('Tính năng đang phát triển')">
                    <i class="fab fa-facebook-f"></i> Đăng nhập với Facebook
                </button>

                <p class="auth-footer">
                    Chưa có tài khoản? <a href="register.php" class="link-primary">Đăng ký ngay</a>
                </p>
            </form>
        </div>

        <div class="auth-right">
            <div class="auth-illustration">
                <img src="<?= ASSETS_URL ?>/images/login-illustration.svg" alt="Learning">
                <h2>Học lập trình miễn phí</h2>
                <p>Tham gia cùng hàng nghìn học viên đang học tại UTH Learning</p>
                <div class="auth-features">
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i> 1000+ khóa học chất lượng
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i> Học trọn đời, không giới hạn
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i> Chứng chỉ hoàn thành miễn phí
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    const passwordField = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');
    
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordField.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}
</script>

<style>
.auth-page { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.auth-container { display: grid; grid-template-columns: 1fr 1fr; max-width: 1200px; width: 100%; margin: 40px; background: #fff; border-radius: 20px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
.auth-left { padding: 60px; }
.auth-brand { margin-bottom: 40px; }
.auth-left h1 { font-size: 32px; margin-bottom: 8px; }
.auth-subtitle { color: #545454; margin-bottom: 32px; }
.alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
.alert-error { background: #fee; color: #c33; border: 1px solid #fcc; }
.alert-success { background: #efe; color: #3c3; border: 1px solid #cfc; }
.form-group { margin-bottom: 20px; }
.form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #1f1f1f; }
.form-control { width: 100%; padding: 14px 16px; border: 1px solid #d1d7dc; border-radius: 8px; font-size: 16px; transition: border-color 0.2s; }
.form-control:focus { outline: none; border-color: #0056d2; }
.password-field { position: relative; }
.toggle-password { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #545454; cursor: pointer; }
.form-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
.checkbox-label { display: flex; align-items: center; gap: 8px; cursor: pointer; }
.link-primary { color: #0056d2; text-decoration: none; font-weight: 600; }
.link-primary:hover { text-decoration: underline; }
.auth-divider { text-align: center; margin: 24px 0; position: relative; }
.auth-divider::before { content: ''; position: absolute; left: 0; top: 50%; width: 100%; height: 1px; background: #e5e5e5; }
.auth-divider span { background: #fff; padding: 0 16px; position: relative; color: #545454; }
.btn-social { width: 100%; padding: 14px; border: 1px solid #d1d7dc; border-radius: 8px; background: #fff; cursor: pointer; font-size: 16px; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 12px; margin-bottom: 12px; transition: all 0.2s; }
.btn-social:hover { background: #f5f7fa; }
.btn-google { color: #db4437; }
.btn-facebook { color: #4267b2; }
.auth-footer { text-align: center; margin-top: 24px; color: #545454; }
.auth-right { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 60px; color: #fff; display: flex; flex-direction: column; justify-content: center; }
.auth-illustration img { width: 100%; max-width: 400px; margin: 0 auto 32px; }
.auth-illustration h2 { font-size: 28px; margin-bottom: 12px; }
.auth-illustration p { font-size: 16px; margin-bottom: 32px; opacity: 0.9; }
.auth-features { display: flex; flex-direction: column; gap: 16px; }
.feature-item { display: flex; align-items: center; gap: 12px; font-size: 16px; }
@media (max-width: 768px) {
    .auth-container { grid-template-columns: 1fr; margin: 20px; }
    .auth-right { display: none; }
}
</style>

</body>
</html>
