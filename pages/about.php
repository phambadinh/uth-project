<?php
require_once '../config/config.php';
require_once '../config/constants.php';
require_once '../includes/functions.php';

$pageTitle = "Giới thiệu - UTH Learning";
$pageDescription = "Tìm hiểu về UTH Learning - Nền tảng học lập trình trực tuyến hàng đầu Việt Nam";

include '../includes/header.php';
?>

<div class="about-page">
    <!-- Hero Section -->
    <section class="about-hero">
        <div class="container">
            <div class="hero-content">
                <h1>Về UTH Learning</h1>
                <p class="hero-subtitle">Nền tảng học lập trình trực tuyến hàng đầu với sứ mệnh đào tạo 1 triệu lập trình viên Việt Nam</p>
            </div>
        </div>
    </section>

    <!-- Mission & Vision -->
    <section class="mission-section">
        <div class="container">
            <div class="mission-grid">
                <div class="mission-card">
                    <div class="mission-icon">🎯</div>
                    <h3>Sứ mệnh</h3>
                    <p>Cung cấp giáo dục công nghệ chất lượng cao, giúp mọi người tiếp cận với lập trình và phát triển sự nghiệp trong ngành IT.</p>
                </div>
                <div class="mission-card">
                    <div class="mission-icon">🌟</div>
                    <h3>Tầm nhìn</h3>
                    <p>Trở thành nền tảng học lập trình trực tuyến số 1 Việt Nam, đồng hành cùng hàng triệu học viên trên con đường chinh phục công nghệ.</p>
                </div>
                <div class="mission-card">
                    <div class="mission-icon">💎</div>
                    <h3>Giá trị cốt lõi</h3>
                    <p>Chất lượng - Minh bạch - Đổi mới. Chúng tôi cam kết mang đến trải nghiệm học tập tốt nhất cho mọi học viên.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <h2 class="section-title">UTH Learning với con số</h2>
            <div class="stats-grid">
                <div class="stat-item">
                    <h3 class="stat-number"><?= formatNumber(getTotalStudents()) ?>+</h3>
                    <p class="stat-label">Học viên</p>
                </div>
                <div class="stat-item">
                    <h3 class="stat-number"><?= formatNumber(countCourses()) ?>+</h3>
                    <p class="stat-label">Khóa học</p>
                </div>
                <div class="stat-item">
                    <h3 class="stat-number"><?= formatNumber(getTotalInstructors()) ?>+</h3>
                    <p class="stat-label">Giảng viên</p>
                </div>
                <div class="stat-item">
                    <h3 class="stat-number">95%</h3>
                    <p class="stat-label">Học viên hài lòng</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Choose Us -->
    <section class="features-section">
        <div class="container">
            <h2 class="section-title">Tại sao chọn UTH Learning?</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <i class="fas fa-graduation-cap feature-icon"></i>
                    <h3>Giảng viên chất lượng</h3>
                    <p>Đội ngũ giảng viên giàu kinh nghiệm, đến từ các công ty công nghệ hàng đầu</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-laptop-code feature-icon"></i>
                    <h3>Học thực hành</h3>
                    <p>100% khóa học có bài tập thực hành, dự án thực tế để rèn luyện kỹ năng</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-certificate feature-icon"></i>
                    <h3>Chứng chỉ uy tín</h3>
                    <p>Nhận chứng chỉ hoàn thành có giá trị, được công nhận bởi các doanh nghiệp</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-users feature-icon"></i>
                    <h3>Cộng đồng sôi động</h3>
                    <p>Tham gia cộng đồng 50,000+ lập trình viên, chia sẻ kinh nghiệm và hỗ trợ lẫn nhau</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-infinity feature-icon"></i>
                    <h3>Học trọn đời</h3>
                    <p>Truy cập khóa học mọi lúc mọi nơi, không giới hạn thời gian học</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-headset feature-icon"></i>
                    <h3>Hỗ trợ 24/7</h3>
                    <p>Đội ngũ hỗ trợ luôn sẵn sàng giải đáp mọi thắc mắc của bạn</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="team-section">
        <div class="container">
            <h2 class="section-title">Đội ngũ của chúng tôi</h2>
            <div class="team-grid">
                <div class="team-member">
                    <img src="<?= ASSETS_URL ?>/images/avatars/user1.jpg" alt="Team Member">
                    <h4>Nguyễn Văn A</h4>
                    <p class="team-role">CEO & Founder</p>
                    <p class="team-bio">10+ năm kinh nghiệm trong giáo dục công nghệ</p>
                </div>
                <div class="team-member">
                    <img src="<?= ASSETS_URL ?>/images/avatars/user2.jpg" alt="Team Member">
                    <h4>Trần Thị B</h4>
                    <p class="team-role">CTO</p>
                    <p class="team-bio">Chuyên gia về AI và Machine Learning</p>
                </div>
                <div class="team-member">
                    <img src="<?= ASSETS_URL ?>/images/avatars/user3.jpg" alt="Team Member">
                    <h4>Lê Văn C</h4>
                    <p class="team-role">Lead Instructor</p>
                    <p class="team-bio">Senior Developer tại Google</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <h2>Bắt đầu hành trình học tập của bạn ngay hôm nay!</h2>
            <p>Tham gia cùng hàng nghìn học viên đang học tại UTH Learning</p>
            <div class="cta-buttons">
                <a href="<?= BASE_URL ?>/pages/courses.php" class="btn-primary-large">Khám phá khóa học</a>
                <a href="<?= BASE_URL ?>/auth/register.php" class="btn-outline-large">Đăng ký miễn phí</a>
            </div>
        </div>
    </section>
</div>

<style>
.about-hero { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; padding: 80px 0; text-align: center; }
.about-hero h1 { font-size: 48px; margin-bottom: 16px; }
.hero-subtitle { font-size: 20px; max-width: 700px; margin: 0 auto; opacity: 0.95; }
.mission-section { padding: 80px 0; background: #f5f7fa; }
.mission-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 32px; }
.mission-card { background: #fff; padding: 40px; border-radius: 12px; text-align: center; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
.mission-icon { font-size: 64px; margin-bottom: 24px; }
.stats-section { padding: 80px 0; text-align: center; }
.section-title { font-size: 36px; margin-bottom: 48px; text-align: center; }
.stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 32px; }
.stat-number { font-size: 48px; font-weight: 700; color: #0056d2; margin-bottom: 8px; }
.stat-label { font-size: 18px; color: #545454; }
.features-section { padding: 80px 0; background: #f5f7fa; }
.features-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 32px; }
.feature-card { background: #fff; padding: 32px; border-radius: 12px; text-align: center; transition: transform 0.3s; }
.feature-card:hover { transform: translateY(-8px); box-shadow: 0 8px 24px rgba(0,0,0,0.12); }
.feature-icon { font-size: 48px; color: #0056d2; margin-bottom: 20px; }
.team-section { padding: 80px 0; }
.team-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 32px; }
.team-member { text-align: center; }
.team-member img { width: 150px; height: 150px; border-radius: 50%; margin-bottom: 20px; object-fit: cover; }
.team-role { color: #0056d2; font-weight: 600; margin-bottom: 8px; }
.team-bio { color: #545454; font-size: 14px; }
.cta-section { background: linear-gradient(135deg, #0056d2, #004aad); color: #fff; padding: 80px 0; text-align: center; }
.cta-section h2 { font-size: 36px; margin-bottom: 16px; }
.cta-section p { font-size: 18px; margin-bottom: 32px; opacity: 0.95; }
.cta-buttons { display: flex; gap: 20px; justify-content: center; }
@media (max-width: 768px) {
    .mission-grid, .features-grid, .team-grid, .stats-grid { grid-template-columns: 1fr; }
    .cta-buttons { flex-direction: column; }
}
</style>

<?php include '../includes/footer.php'; ?>
