<?php
require_once '../config/config.php';
require_once '../config/constants.php';

// Check authentication
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../auth/login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Get all certificates
$stmt = $pdo->prepare("
    SELECT cert.*, c.title as course_title, c.category, c.thumbnail
    FROM certificates cert
    JOIN courses c ON cert.course_id = c.id
    WHERE cert.user_id = ?
    ORDER BY cert.issued_date DESC
");
$stmt->execute([$userId]);
$certificates = $stmt->fetchAll();

$pageTitle = "Chứng chỉ của tôi - UTH Learning";
include '../includes/header.php';
?>

<div class="certificates-page">
    <div class="container">
        <div class="page-header">
            <div>
                <h1>Chứng chỉ của tôi</h1>
                <p>Quản lý và tải xuống các chứng chỉ đã đạt được</p>
            </div>
            <div class="header-stats">
                <div class="stat-item">
                    <i class="fas fa-certificate"></i>
                    <span><?= count($certificates) ?> chứng chỉ</span>
                </div>
            </div>
        </div>

        <?php if (empty($certificates)): ?>
        <div class="empty-state">
            <i class="fas fa-certificate"></i>
            <h3>Chưa có chứng chỉ nào</h3>
            <p>Hoàn thành khóa học để nhận chứng chỉ hoàn thành</p>
            <a href="my-courses.php" class="btn-primary">Xem khóa học của tôi</a>
        </div>
        <?php else: ?>
        <div class="certificates-grid">
            <?php foreach ($certificates as $cert): ?>
            <div class="certificate-card">
                <div class="certificate-preview">
                    <div class="certificate-template">
                        <div class="cert-header">
                            <img src="<?= ASSETS_URL ?>/images/logo.png" alt="UTH Learning" class="cert-logo">
                            <h3>CHỨNG CHỈ HOÀN THÀNH</h3>
                        </div>
                        <div class="cert-body">
                            <p class="cert-label">Chứng nhận rằng</p>
                            <h2 class="cert-name"><?= htmlspecialchars($_SESSION['fullname']) ?></h2>
                            <p class="cert-label">đã hoàn thành xuất sắc khóa học</p>
                            <h3 class="cert-course"><?= htmlspecialchars($cert['course_title']) ?></h3>
                            <div class="cert-footer">
                                <div class="cert-date">
                                    <p>Ngày cấp</p>
                                    <p><strong><?= date('d/m/Y', strtotime($cert['issued_date'])) ?></strong></p>
                                </div>
                                <div class="cert-code">
                                    <p>Mã chứng chỉ</p>
                                    <p><strong><?= htmlspecialchars($cert['certificate_code']) ?></strong></p>
                                </div>
                            </div>
                        </div>
                        <div class="cert-badge" style="background: <?= CATEGORY_COLORS[$cert['category']] ?>">
                            <?= CATEGORY_ICONS[$cert['category']] ?>
                        </div>
                    </div>
                </div>

                <div class="certificate-info">
                    <h4><?= htmlspecialchars($cert['course_title']) ?></h4>
                    <p class="cert-meta">
                        <span><i class="fas fa-calendar"></i> <?= date('d/m/Y', strtotime($cert['issued_date'])) ?></span>
                        <span><i class="fas fa-qrcode"></i> <?= htmlspecialchars($cert['certificate_code']) ?></span>
                    </p>
                </div>

                <div class="certificate-actions">
                    <a href="view-certificate.php?code=<?= $cert['certificate_code'] ?>" 
                       class="btn-outline btn-sm" target="_blank">
                        <i class="fas fa-eye"></i> Xem
                    </a>
                    <a href="download-certificate.php?code=<?= $cert['certificate_code'] ?>" 
                       class="btn-primary btn-sm">
                        <i class="fas fa-download"></i> Tải PDF
                    </a>
                    <a href="../pages/verify-certificate.php?code=<?= $cert['certificate_code'] ?>" 
                       class="btn-outline btn-sm" target="_blank">
                        <i class="fas fa-check-circle"></i> Xác thực
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Instructions -->
        <div class="certificate-instructions">
            <h3><i class="fas fa-info-circle"></i> Hướng dẫn sử dụng chứng chỉ</h3>
            <div class="instructions-grid">
                <div class="instruction-item">
                    <i class="fas fa-download"></i>
                    <h4>Tải xuống</h4>
                    <p>Tải chứng chỉ dạng PDF để in ấn hoặc lưu trữ</p>
                </div>
                <div class="instruction-item">
                    <i class="fas fa-share-alt"></i>
                    <h4>Chia sẻ</h4>
                    <p>Chia sẻ chứng chỉ trên LinkedIn, Facebook hoặc CV</p>
                </div>
                <div class="instruction-item">
                    <i class="fas fa-qrcode"></i>
                    <h4>Xác thực</h4>
                    <p>Sử dụng mã QR hoặc mã chứng chỉ để xác thực tính hợp lệ</p>
                </div>
                <div class="instruction-item">
                    <i class="fas fa-shield-alt"></i>
                    <h4>Bảo mật</h4>
                    <p>Mỗi chứng chỉ có mã duy nhất, không thể làm giả</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.certificates-page { padding: 40px 0; background: #f5f7fa; min-height: calc(100vh - 72px); }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }
.page-header h1 { font-size: 32px; margin-bottom: 8px; }
.page-header p { color: var(--gray-600); }
.header-stats { display: flex; gap: 24px; }
.stat-item { display: flex; align-items: center; gap: 8px; background: #fff; padding: 12px 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
.stat-item i { font-size: 20px; color: var(--warning); }
.stat-item span { font-weight: 600; }

.certificates-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 32px; margin-bottom: 48px; }

.certificate-card { background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); transition: all 0.3s; }
.certificate-card:hover { box-shadow: 0 8px 24px rgba(0,0,0,0.15); transform: translateY(-4px); }

.certificate-preview { margin-bottom: 20px; }
.certificate-template { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 8px; padding: 32px; color: #fff; position: relative; overflow: hidden; }
.certificate-template::before { content: ''; position: absolute; top: -50%; right: -50%; width: 200%; height: 200%; background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%); }

.cert-header { text-align: center; margin-bottom: 24px; position: relative; z-index: 1; }
.cert-logo { height: 40px; margin-bottom: 12px; }
.cert-header h3 { font-size: 18px; font-weight: 700; letter-spacing: 2px; }

.cert-body { text-align: center; position: relative; z-index: 1; }
.cert-label { font-size: 14px; margin-bottom: 8px; opacity: 0.9; }
.cert-name { font-size: 28px; font-weight: 700; margin: 12px 0; }
.cert-course { font-size: 18px; font-weight: 600; margin: 16px 0; border-top: 2px solid rgba(255,255,255,0.3); border-bottom: 2px solid rgba(255,255,255,0.3); padding: 12px 0; }

.cert-footer { display: flex; justify-content: space-around; margin-top: 24px; gap: 20px; }
.cert-date, .cert-code { text-align: center; }
.cert-footer p { font-size: 12px; margin: 4px 0; }

.cert-badge { position: absolute; top: 20px; right: 20px; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 28px; box-shadow: 0 4px 12px rgba(0,0,0,0.2); }

.certificate-info h4 { font-size: 18px; margin-bottom: 12px; }
.cert-meta { display: flex; gap: 20px; font-size: 13px; color: var(--gray-600); margin-bottom: 16px; }
.cert-meta i { margin-right: 6px; }

.certificate-actions { display: flex; gap: 8px; }

.certificate-instructions { background: #fff; border-radius: 12px; padding: 32px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
.certificate-instructions h3 { font-size: 20px; margin-bottom: 24px; display: flex; align-items: center; gap: 12px; }
.certificate-instructions h3 i { color: var(--info); }

.instructions-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; }
.instruction-item { text-align: center; }
.instruction-item i { font-size: 40px; color: var(--primary); margin-bottom: 12px; }
.instruction-item h4 { font-size: 16px; margin-bottom: 8px; }
.instruction-item p { font-size: 13px; color: var(--gray-600); line-height: 1.6; }

@media (max-width: 991px) {
    .certificates-grid { grid-template-columns: 1fr; }
    .instructions-grid { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 767px) {
    .page-header { flex-direction: column; align-items: flex-start; gap: 16px; }
    .instructions-grid { grid-template-columns: 1fr; }
    .certificate-actions { flex-wrap: wrap; }
}
</style>

<?php include '../includes/footer.php'; ?>
