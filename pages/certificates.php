<?php
$pageTitle = "Chứng chỉ - UTH Learning";
include '../includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <h1>Chứng chỉ UTH Learning</h1>
        <p>Khẳng định năng lực của bạn với chứng chỉ uy tín</p>
    </div>
</section>

<section class="certificates-info">
    <div class="container">
        <div class="info-grid">
            <div class="info-card">
                <div class="info-icon">🎓</div>
                <h3>Chứng chỉ được công nhận</h3>
                <p>Chứng chỉ của UTH Learning được công nhận bởi các doanh nghiệp hàng đầu</p>
            </div>
            
            <div class="info-card">
                <div class="info-icon">📜</div>
                <h3>Hoàn thành khóa học</h3>
                <p>Nhận chứng chỉ sau khi hoàn thành 100% nội dung khóa học và bài kiểm tra</p>
            </div>
            
            <div class="info-card">
                <div class="info-icon">🔗</div>
                <h3>Chia sẻ trên LinkedIn</h3>
                <p>Thêm chứng chỉ vào profile LinkedIn của bạn để tăng cơ hội việc làm</p>
            </div>
        </div>
        
        <?php if (!isLoggedIn()): ?>
            <div class="cta-box">
                <h2>Bắt đầu học và nhận chứng chỉ ngay hôm nay</h2>
                <a href="<?= BASE_URL ?>/auth/register.php" class="btn btn-primary btn-lg">
                    Đăng ký miễn phí
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
