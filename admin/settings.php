<?php
$pageTitle = "Cài đặt hệ thống";
include 'includes/admin-header.php';

$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // In a real application, save to database or config file
    $_SESSION['flash_message'] = 'Cài đặt đã được cập nhật thành công';
    $_SESSION['flash_type'] = 'success';
    header('Location: settings.php');
    exit;
}
?>

<div class="admin-page-header">
    <h1>Cài đặt hệ thống</h1>
</div>

<?php if (isset($_SESSION['flash_message'])): ?>
<div class="alert alert-<?= $_SESSION['flash_type'] ?>">
    <?= $_SESSION['flash_message'] ?>
</div>
<?php 
    unset($_SESSION['flash_message']);
    unset($_SESSION['flash_type']);
endif; ?>

<form method="POST" class="settings-form">
    <!-- General Settings -->
    <div class="admin-card">
        <div class="card-header">
            <h2><i class="fas fa-cog"></i> Cài đặt chung</h2>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label>Tên website</label>
                <input type="text" name="site_name" class="form-control" value="<?= SITE_NAME ?>">
            </div>
            
            <div class="form-group">
                <label>Email hỗ trợ</label>
                <input type="email" name="site_email" class="form-control" value="<?= SITE_EMAIL ?>">
            </div>
            
            <div class="form-group">
                <label>Số điện thoại</label>
                <input type="tel" name="site_phone" class="form-control" value="<?= SITE_PHONE ?>">
            </div>
        </div>
    </div>

    <!-- Course Settings -->
    <div class="admin-card">
        <div class="card-header">
            <h2><i class="fas fa-book"></i> Cài đặt khóa học</h2>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label>Số khóa học mỗi trang</label>
                <input type="number" name="items_per_page" class="form-control" value="<?= ITEMS_PER_PAGE ?>">
            </div>
            
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="require_approval" value="1">
                    Yêu cầu phê duyệt khóa học mới
                </label>
            </div>
            
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="auto_enroll" value="1">
                    Tự động ghi danh khóa học miễn phí
                </label>
            </div>
        </div>
    </div>

    <!-- Payment Settings -->
    <div class="admin-card">
        <div class="card-header">
            <h2><i class="fas fa-credit-card"></i> Cài đặt thanh toán</h2>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label>VNPay TMN Code</label>
                <input type="text" name="vnpay_tmn_code" class="form-control" value="<?= VNPAY_TMN_CODE ?>">
            </div>
            
            <div class="form-group">
                <label>VNPay Hash Secret</label>
                <input type="password" name="vnpay_hash_secret" class="form-control" value="<?= VNPAY_HASH_SECRET ?>">
            </div>
            
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="enable_vnpay" value="1" checked>
                    Bật thanh toán VNPay
                </label>
            </div>
            
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="enable_momo" value="1" checked>
                    Bật thanh toán MoMo
                </label>
            </div>
        </div>
    </div>

    <!-- Email Settings -->
    <div class="admin-card">
        <div class="card-header">
            <h2><i class="fas fa-envelope"></i> Cài đặt Email</h2>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label>SMTP Host</label>
                <input type="text" name="smtp_host" class="form-control" placeholder="smtp.gmail.com">
            </div>
            
            <div class="form-row-2col">
                <div class="form-group">
                    <label>SMTP Port</label>
                    <input type="number" name="smtp_port" class="form-control" value="587">
                </div>
                
                <div class="form-group">
                    <label>SMTP Encryption</label>
                    <select name="smtp_encryption" class="form-control">
                        <option value="tls">TLS</option>
                        <option value="ssl">SSL</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label>SMTP Username</label>
                <input type="email" name="smtp_username" class="form-control">
            </div>
            
            <div class="form-group">
                <label>SMTP Password</label>
                <input type="password" name="smtp_password" class="form-control">
            </div>
        </div>
    </div>

    <!-- System Info -->
    <div class="admin-card">
        <div class="card-header">
            <h2><i class="fas fa-info-circle"></i> Thông tin hệ thống</h2>
        </div>
        <div class="card-body">
            <table class="info-table">
                <tr>
                    <td><strong>PHP Version:</strong></td>
                    <td><?= phpversion() ?></td>
                </tr>
                <tr>
                    <td><strong>MySQL Version:</strong></td>
                    <td><?= $pdo->query('SELECT VERSION()')->fetchColumn() ?></td>
                </tr>
                <tr>
                    <td><strong>Server Software:</strong></td>
                    <td><?= $_SERVER['SERVER_SOFTWARE'] ?? 'N/A' ?></td>
                </tr>
                <tr>
                    <td><strong>Document Root:</strong></td>
                    <td><?= $_SERVER['DOCUMENT_ROOT'] ?></td>
                </tr>
                <tr>
                    <td><strong>Upload Max Filesize:</strong></td>
                    <td><?= ini_get('upload_max_filesize') ?></td>
                </tr>
            </table>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn-primary">
            <i class="fas fa-save"></i> Lưu cài đặt
        </button>
    </div>
</form>

<?php include 'includes/admin-footer.php'; ?>
