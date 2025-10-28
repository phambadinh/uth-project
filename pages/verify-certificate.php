<?php
require_once '../config/config.php';
require_once '../config/constants.php';

$code = $_GET['code'] ?? '';
$certificate = null;
$error = '';

if ($code) {
    $stmt = $pdo->prepare("
        SELECT c.*, co.title as course_title, co.category, u.fullname, u.email
        FROM certificates c
        JOIN courses co ON c.course_id = co.id
        JOIN users u ON c.user_id = u.id
        WHERE c.certificate_code = ?
    ");
    $stmt->execute([$code]);
    $certificate = $stmt->fetch();
    
    if (!$certificate) {
        $error = 'Không tìm thấy chứng chỉ với mã này';
    }
}

$pageTitle = "Xác thực chứng chỉ - UTH Learning";
include '../includes/header.php';
?>

<div class="verify-page">
    <div class="container">
        <div class="verify-header">
            <h1>Xác thực chứng chỉ</h1>
            <p>Kiểm tra tính xác thực của chứng chỉ UTH Learning</p>
        </div>

        <?php if (!$code): ?>
        <!-- Search Form -->
        <div class="verify-search">
            <form method="GET" class="search-form">
                <input type="text" 
                       name="code" 
                       class="form-control" 
                       placeholder="Nhập mã chứng chỉ (VD: UTH-ABC123XYZ)" 
                       required>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-search"></i> Xác thực
                </button>
            </form>
        </div>

        <div class="verify-info">
            <div class="info-card">
                <i class="fas fa-qrcode"></i>
                <h3>Quét mã QR</h3>
                <p>Sử dụng camera điện thoại để quét mã QR trên chứng chỉ</p>
            </div>
            <div class="info-card">
                <i class="fas fa-keyboard"></i>
                <h3>Nhập mã</h3>
                <p>Nhập mã chứng chỉ vào ô tìm kiếm phía trên</p>
            </div>
            <div class="info-card">
                <i class="fas fa-check-circle"></i>
                <h3>Xác thực</h3>
                <p>Hệ thống sẽ kiểm tra và hiển thị kết quả ngay lập tức</p>
            </div>
        </div>

        <?php elseif ($error): ?>
        <!-- Error -->
        <div class="verify-result error">
            <i class="fas fa-times-circle"></i>
            <h2>Không tìm thấy</h2>
            <p><?= $error ?></p>
            <a href="verify-certificate.php" class="btn-outline">Thử lại</a>
        </div>

        <?php else: ?>
        <!-- Success - Show Certificate Details -->
        <div class="verify-result success">
            <i class="fas fa-check-circle"></i>
            <h2>Chứng chỉ hợp lệ ✓</h2>
            <p>Chứng chỉ này được cấp bởi UTH Learning System</p>
        </div>

        <div class="certificate-details">
            <div class="detail-row">
                <span class="label">Mã chứng chỉ:</span>
                <span class="value"><strong><?= htmlspecialchars($certificate['certificate_code']) ?></strong></span>
            </div>
            <div class="detail-row">
                <span class="label">Tên học viên:</span>
                <span class="value"><?= htmlspecialchars($certificate['fullname']) ?></span>
            </div>
            <div class="detail-row">
                <span class="label">Email:</span>
                <span class="value"><?= htmlspecialchars($certificate['email']) ?></span>
            </div>
            <div class="detail-row">
                <span class="label">Khóa học:</span>
                <span class="value">
                    <span class="badge" style="background: <?= CATEGORY_COLORS[$certificate['category']] ?>">
                        <?= CATEGORY_ICONS[$certificate['category']] ?> <?= $certificate['category'] ?>
                    </span>
                    <?= htmlspecialchars($certificate['course_title']) ?>
                </span>
            </div>
            <div class="detail-row">
                <span class="label">Ngày cấp:</span>
                <span class="value"><?= date('d/m/Y', strtotime($certificate['issued_date'])) ?></span>
            </div>
            <div class="detail-row">
                <span class="label">Trạng thái:</span>
                <span class="value">
                    <span class="status-badge valid">
                        <i class="fas fa-shield-alt"></i> Hợp lệ
                    </span>
                </span>
            </div>
        </div>

        <div class="verify-actions">
            <a href="verify-certificate.php" class="btn-outline">Xác thực chứng chỉ khác</a>
            <button onclick="window.print()" class="btn-primary">
                <i class="fas fa-print"></i> In trang này
            </button>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.verify-page { min-height: 80vh; padding: 60px 0; background: #f5f7fa; }
.verify-header { text-align: center; margin-bottom: 48px; }
.verify-header h1 { font-size: 40px; margin-bottom: 12px; }
.verify-search { max-width: 600px; margin: 0 auto 60px; background: #fff; padding: 40px; border-radius: 12px; box-shadow: 0 4px 16px rgba(0,0,0,0.08); }
.search-form { display: flex; gap: 12px; }
.search-form input { flex: 1; }
.verify-info { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; max-width: 900px; margin: 0 auto; }
.info-card { background: #fff; padding: 32px; border-radius: 12px; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
.info-card i { font-size: 48px; color: #0056d2; margin-bottom: 16px; }
.info-card h3 { margin-bottom: 8px; }
.info-card p { color: #545454; font-size: 14px; }
.verify-result { max-width: 600px; margin: 0 auto 40px; background: #fff; padding: 60px 40px; border-radius: 12px; text-align: center; box-shadow: 0 4px 16px rgba(0,0,0,0.08); }
.verify-result i { font-size: 80px; margin-bottom: 24px; }
.verify-result.success i { color: #0cae74; }
.verify-result.error i { color: #e74c3c; }
.verify-result h2 { font-size: 32px; margin-bottom: 12px; }
.certificate-details { max-width: 700px; margin: 0 auto 40px; background: #fff; padding: 40px; border-radius: 12px; box-shadow: 0 4px 16px rgba(0,0,0,0.08); }
.detail-row { display: flex; justify-content: space-between; padding: 16px 0; border-bottom: 1px solid #e5e5e5; }
.detail-row:last-child { border-bottom: none; }
.detail-row .label { font-weight: 600; color: #545454; }
.detail-row .value { text-align: right; }
.badge { padding: 4px 12px; border-radius: 20px; color: #fff; font-size: 12px; font-weight: 600; margin-right: 8px; }
.status-badge { padding: 6px 16px; border-radius: 20px; font-weight: 600; }
.status-badge.valid { background: #d1fae5; color: #065f46; }
.verify-actions { display: flex; gap: 16px; justify-content: center; }
@media (max-width: 768px) {
    .verify-info { grid-template-columns: 1fr; }
    .search-form { flex-direction: column; }
    .verify-actions { flex-direction: column; }
}
</style>

<?php include '../includes/footer.php'; ?>
