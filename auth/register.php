<?php
require_once '../config/config.php';

$error = '';
$success = '';

// Xử lý đăng ký
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (empty($fullname) || empty($email) || empty($username) || empty($password)) {
        $error = 'Vui lòng nhập đầy đủ thông tin';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không hợp lệ';
    } elseif (strlen($username) < 3) {
        $error = 'Tên đăng nhập phải có ít nhất 3 ký tự';
    } elseif (strlen($password) < 6) {
        $error = 'Mật khẩu phải có ít nhất 6 ký tự';
    } elseif ($password !== $confirm_password) {
        $error = 'Mật khẩu xác nhận không khớp';
    } else {
        // Check username exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $error = 'Tên đăng nhập đã tồn tại';
        } else {
            // Check email exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'Email đã được sử dụng';
            } else {
                // Insert new user
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, password, fullname, email, role) VALUES (?, ?, ?, ?, 'student')");
                
                if ($stmt->execute([$username, $hashedPassword, $fullname, $email])) {
                    $success = 'Đăng ký thành công! Đang chuyển hướng...';
                    header('refresh:2;url=login.php');
                } else {
                    $error = 'Có lỗi xảy ra. Vui lòng thử lại';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - UTH Learning</title>
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
            <h1>Tạo tài khoản miễn phí</h1>
            <p class="auth-subtitle">Bắt đầu hành trình học lập trình của bạn</p>

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
                    <label for="fullname">Họ và tên</label>
                    <input type="text" id="fullname" name="fullname" class="form-control" 
                           placeholder="Nguyễn Văn A" required value="<?= htmlspecialchars($_POST['fullname'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" 
                           placeholder="example@email.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="username">Tên đăng nhập</label>
                    <input type="text" id="username" name="username" class="form-control" 
                           placeholder="username" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Mật khẩu</label>
                        <input type="password" id="password" name="password" class="form-control" 
                               placeholder="Ít nhất 6 ký tự" required>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Xác nhận mật khẩu</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" 
                               placeholder="Nhập lại mật khẩu" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" required> 
                        Tôi đồng ý với <a href="#" class="link-primary">Điều khoản sử dụng</a> 
                        và <a href="#" class="link-primary">Chính sách bảo mật</a>
                    </label>
                </div>

                <button type="submit" class="btn-primary-large full-width">
                    Đăng ký
                </button>

                <p class="auth-footer">
                    Đã có tài khoản? <a href="login.php" class="link-primary">Đăng nhập ngay</a>
                </p>
            </form>
        </div>

        <div class="auth-right">
            <div class="auth-illustration">
                <img src="<?= ASSETS_URL ?>/images/register-illustration.svg" alt="Join Us">
                <h2>Tham gia cộng đồng học viên</h2>
                <p>Hơn 50,000 học viên đã tin tưởng và học tập tại UTH Learning</p>
                <div class="auth-stats">
                    <div class="stat-item">
                        <h3>1000+</h3>
                        <p>Khóa học</p>
                    </div>
                    <div class="stat-item">
                        <h3>50K+</h3>
                        <p>Học viên</p>
                    </div>
                    <div class="stat-item">
                        <h3>100+</h3>
                        <p>Giảng viên</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.auth-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-top: 32px; }
.stat-item { text-align: center; }
.stat-item h3 { font-size: 36px; margin-bottom: 8px; }
.stat-item p { font-size: 14px; opacity: 0.9; }
</style>

</body>
</html>
