<?php
$pageTitle = "Quản lý chứng chỉ";
include 'includes/admin-header.php';

// Get certificates
$stmt = $pdo->query("
    SELECT cert.*, u.fullname, u.email, c.title as course_title, c.category
    FROM certificates cert
    JOIN users u ON cert.user_id = u.id
    JOIN courses c ON cert.course_id = c.id
    ORDER BY cert.issued_date DESC
    LIMIT 100
");
$certificates = $stmt->fetchAll();

$totalCertificates = $pdo->query("SELECT COUNT(*) FROM certificates")->fetchColumn();
$thisMonth = $pdo->query("SELECT COUNT(*) FROM certificates WHERE MONTH(issued_date) = MONTH(NOW())")->fetchColumn();
?>

<div class="admin-page-header">
    <div>
        <h1>Quản lý chứng chỉ</h1>
        <p>Tổng số: <?= number_format($totalCertificates) ?> chứng chỉ</p>
    </div>
</div>

<!-- Stats -->
<div class="stats-row">
    <div class="stat-box-small">
        <h4><?= number_format($totalCertificates) ?></h4>
        <p>Tổng chứng chỉ</p>
    </div>
    <div class="stat-box-small">
        <h4><?= number_format($thisMonth) ?></h4>
        <p>Tháng này</p>
    </div>
    <div class="stat-box-small">
        <h4><?= $totalCertificates > 0 ? number_format(($thisMonth / $totalCertificates) * 100, 1) : 0 ?>%</h4>
        <p>Tỷ lệ tháng này</p>
    </div>
</div>

<!-- Certificates Table -->
<div class="admin-card">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Mã chứng chỉ</th>
                    <th>Học viên</th>
                    <th>Khóa học</th>
                    <th>Ngày cấp</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($certificates as $cert): ?>
                <tr>
                    <td><?= $cert['id'] ?></td>
                    <td><code><?= htmlspecialchars($cert['certificate_code']) ?></code></td>
                    <td>
                        <strong><?= htmlspecialchars($cert['fullname']) ?></strong><br>
                        <small class="text-muted"><?= htmlspecialchars($cert['email']) ?></small>
                    </td>
                    <td>
                        <span class="badge" style="background: <?= CATEGORY_COLORS[$cert['category']] ?>">
                            <?= CATEGORY_ICONS[$cert['category']] ?>
                        </span>
                        <?= htmlspecialchars($cert['course_title']) ?>
                    </td>
                    <td><?= date('d/m/Y H:i', strtotime($cert['issued_date'])) ?></td>
                    <td>
                        <div class="action-buttons">
                            <a href="../student/view-certificate.php?code=<?= $cert['certificate_code'] ?>" 
                               class="btn-icon" title="Xem" target="_blank">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="../pages/verify-certificate.php?code=<?= $cert['certificate_code'] ?>" 
                               class="btn-icon" title="Xác thực" target="_blank">
                                <i class="fas fa-check-circle"></i>
                            </a>
                            <a href="../student/download-certificate.php?code=<?= $cert['certificate_code'] ?>" 
                               class="btn-icon" title="Tải PDF" target="_blank">
                                <i class="fas fa-download"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/admin-footer.php'; ?>
