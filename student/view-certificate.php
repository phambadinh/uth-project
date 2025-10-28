<?php
require_once '../config/config.php';
require_once '../config/constants.php';

$code = $_GET['code'] ?? '';

// Get certificate (public access for verification)
$stmt = $pdo->prepare("
    SELECT cert.*, c.title as course_title, c.category, u.fullname, u.email
    FROM certificates cert
    JOIN courses c ON cert.course_id = c.id
    JOIN users u ON cert.user_id = u.id
    WHERE cert.certificate_code = ?
");
$stmt->execute([$code]);
$certificate = $stmt->fetch();

if (!$certificate) {
    die('Chứng chỉ không tồn tại');
}

$pageTitle = "Chứng chỉ - " . $certificate['fullname'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Arial', sans-serif; background: #f5f7fa; padding: 40px 20px; }
        .certificate-container { max-width: 1000px; margin: 0 auto; background: #fff; border-radius: 12px; box-shadow: 0 8px 32px rgba(0,0,0,0.15); overflow: hidden; }
        .certificate-content { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 60px 80px; color: #fff; position: relative; }
        .certificate-content::before { content: ''; position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 600">ircle cx="100" cy="100" r="80" fill="rgba(255,255,255,0,0.03)"/>ircle cx="1100" cy="500" r="100" fill="rgba(255,25555,255,0.03)"/>ircle cx="900" cy="100" r="60" fill="rgba(255,25555,255,0.03)"/></svg>'); opacity: 0.3; }
        .cert-header { text-align: center; margin-bottom: 40px; position: relative; z-index: 1; }
        .cert-logo { height: 50px; margin-bottom: 20px; }
        .cert-title { font-size: 36px; font-weight: 700; letter-spacing: 3px; margin-bottom: 10px; text-transform: uppercase; }
        .cert-subtitle { font-size: 16px; opacity: 0.9; font-style: italic; }
        .cert-body { text-align: center; position: relative; z-index: 1; }
        .cert-label { font-size: 16px; margin-bottom: 12px; opacity: 0.9; }
        .cert-name { font-size: 42px; font-weight: 700; margin: 20px 0; text-transform: uppercase; text-shadow: 2px 2px 4px rgba(0,0,0,0.2); }
        .cert-course { font-size: 24px; font-weight: 600; margin: 24px 0; padding: 20px 0; border-top: 2px solid rgba(255,255,255,0.3); border-bottom: 2px solid rgba(255,255,255,0.3); }
        .cert-footer { display: flex; justify-content: space-around; margin-top: 40px; gap: 40px; }
        .cert-info { text-align: center; }
        .cert-info-label { font-size: 12px; margin-bottom: 8px; opacity: 0.8; text-transform: uppercase; letter-spacing: 1px; }
        .cert-info-value { font-size: 18px; font-weight: 700; }
        .cert-badge { position: absolute; top: 40px; right: 60px; width: 80px; height: 80px; border-radius: 50%; background: <?= CATEGORY_COLORS[$certificate['category']] ?>; display: flex; align-items: center; justify-content: center; font-size: 36px; box-shadow: 0 6px 20px rgba(0,0,0,0.3); z-index: 2; }
        .certificate-actions { padding: 30px; background: #fff; text-align: center; display: flex; gap: 16px; justify-content: center; }
        .btn { padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; transition: all 0.3s; }
        .btn-primary { background: #667eea; color: #fff; }
        .btn-primary:hover { background: #5568d3; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(102,126,234,0.4); }
        .btn-outline { border: 2px solid #667eea; color: #667eea; background: #fff; }
        .btn-outline:hover { background: #667eea; color: #fff; }
        .verification-note { padding: 20px; background: #f0f9ff; border-left: 4px solid #0ea5e9; margin: 20px; border-radius: 4px; }
        .verification-note p { color: #0c4a6e; font-size: 14px; line-height: 1.6; }
        @media print {
            body { background: #fff; padding: 0; }
            .certificate-actions, .verification-note { display: none !important; }
            .certificate-container { box-shadow: none; }
        }
    </style>
</head>
<body>
    <div class="certificate-container">
        <div class="certificate-content">
            <div class="cert-badge">
                <?= CATEGORY_ICONS[$certificate['category']] ?>
            </div>
            
            <div class="cert-header">
                <h1 class="cert-title">Chứng Chỉ Hoàn Thành</h1>
                <p class="cert-subtitle">Certificate of Completion</p>
            </div>

            <div class="cert-body">
                <p class="cert-label">Chứng nhận rằng</p>
                <h2 class="cert-name"><?= htmlspecialchars($certificate['fullname']) ?></h2>
                <p class="cert-label">đã hoàn thành xuất sắc khóa học</p>
                <h3 class="cert-course"><?= htmlspecialchars($certificate['course_title']) ?></h3>

                <div class="cert-footer">
                    <div class="cert-info">
                        <p class="cert-info-label">Ngày cấp</p>
                        <p class="cert-info-value"><?= date('d/m/Y', strtotime($certificate['issued_date'])) ?></p>
                    </div>
                    <div class="cert-info">
                        <p class="cert-info-label">Mã chứng chỉ</p>
                        <p class="cert-info-value"><?= htmlspecialchars($certificate['certificate_code']) ?></p>
                    </div>
                    <div class="cert-info">
                        <p class="cert-info-label">Xác thực</p>
                        <p class="cert-info-value">uth-learning.com/verify</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="certificate-actions">
            <a href="download-certificate.php?code=<?= $code ?>" class="btn btn-primary">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M8 12L3 7h3V2h4v5h3l-5 5z"/><path d="M14 13v1H2v-1h12z"/></svg>
                Tải PDF
            </a>
            <a href="../pages/verify-certificate.php?code=<?= $code ?>" class="btn btn-outline" target="_blank">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M8 0a8 8 0 1 1 0 16A8 8 0 0 1 8 0zM6.5 5L5 6.5 7 8.5l-2 2L6.5 12l2-2 2 2L12 10.5l-2-2 2-2L10.5 5l-2 2-2-2z"/></svg>
                Xác thực
            </a>
            <button onclick="window.print()" class="btn btn-outline">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M4 0h8v4H4V0z"/><path d="M3 5h10a1 1 0 0 1 1 1v6H2V6a1 1 0 0 1 1-1zm0 8h10v3H3v-3z"/></svg>
                In
            </button>
        </div>

        <div class="verification-note">
            <p><strong>💡 Lưu ý:</strong> Chứng chỉ này có thể được xác thực tại <strong>uth-learning.com/verify</strong> bằng mã chứng chỉ: <strong><?= htmlspecialchars($certificate['certificate_code']) ?></strong></p>
        </div>
    </div>
