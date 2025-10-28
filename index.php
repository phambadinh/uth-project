<?php
require_once 'config/config.php';
require_once 'config/constants.php';
require_once 'includes/functions.php';

$pageTitle = "Học Lập Trình Miễn Phí - UTH Learning System";
$pageDescription = "Nền tảng học lập trình trực tuyến hàng đầu với HTML, CSS, JavaScript, PHP, Python, C++";

// Lấy thống kê
$totalCourses = countCourses();
$totalStudents = getTotalStudents();
$totalInstructors = getTotalInstructors();
$featuredCourses = getAllCourses(null, null, 6, 0);

include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="hero-container">
        <div class="hero-content">
            <h1 class="hero-title">Học Lập Trình Miễn Phí</h1>
            <p class="hero-subtitle">
                Bắt đầu, chuyển đổi hoặc nâng cao sự nghiệp của bạn với <?= number_format($totalCourses) ?>+ khóa học 
                từ HTML, CSS, JavaScript, PHP, Python đến C++
            </p>
            <div class="hero-actions">
                <a href="<?= BASE_URL ?>/auth/register.php" class="btn-primary-large">
                    Đăng Ký Miễn Phí
                </a>
                <a href="<?= BASE_URL ?>/pages/courses.php" class="btn-outline-large">
                    Khám Phá Khóa Học
                </a>
            </div>
            <p class="hero-note">
                <i class="fas fa-check-circle"></i> Học trọn đời • Chứng chỉ miễn phí
            </p>
        </div>
        <div class="hero-image">
            <img src="<?= ASSETS_URL ?>/images/hero-coding.svg" alt="Học lập trình online">
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="stats-section">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-item">
                <h2 class="stat-number"><?= number_format($totalCourses) ?>+</h2>
                <p class="stat-label">Khóa học</p>
            </div>
            <div class="stat-item">
                <h2 class="stat-number"><?= number_format($totalStudents) ?>+</h2>
                <p class="stat-label">Học viên</p>
            </div>
            <div class="stat-item">
                <h2 class="stat-number"><?= number_format($totalInstructors) ?>+</h2>
                <p class="stat-label">Giảng viên</p>
            </div>
            <div class="stat-item">
                <h2 class="stat-number">4.8/5</h2>
                <p class="stat-label">Đánh giá</p>
            </div>
        </div>
    </div>
</section>

<!-- Learning Paths -->
<section class="learning-paths">
    <div class="container">
        <h2 class="section-title">Bạn muốn học gì hôm nay?</h2>
        <div class="paths-grid">
            <a href="<?= BASE_URL ?>/pages/courses.php?category=HTML" class="path-card">
                <div class="path-icon">🌐</div>
                <h3>HTML - Nền tảng Web</h3>
                <p>Bắt đầu với HTML5 và xây dựng cấu trúc website chuyên nghiệp</p>
                <span class="link-arrow">Học ngay →</span>
            </a>
            <a href="<?= BASE_URL ?>/pages/courses.php?category=CSS" class="path-card">
                <div class="path-icon">🎨</div>
                <h3>CSS - Thiết kế đẹp</h3>
                <p>Thiết kế giao diện responsive với CSS3, Flexbox và Grid</p>
                <span class="link-arrow">Học ngay →</span>
            </a>
            <a href="<?= BASE_URL ?>/pages/courses.php?category=JavaScript" class="path-card">
                <div class="path-icon">⚡</div>
                <h3>JavaScript - Tương tác động</h3>
                <p>Làm chủ JavaScript và xây dựng ứng dụng web hiện đại</p>
                <span class="link-arrow">Học ngay →</span>
            </a>
        </div>
    </div>
</section>

<!-- Featured Courses -->
<section class="featured-section">
    <div class="container">
        <h2 class="section-title">Khóa học nổi bật</h2>
        <p class="section-subtitle">Các khóa học được yêu thích nhất</p>
        
        <div class="courses-grid-3">
            <?php foreach ($featuredCourses as $course): ?>
            <a href="<?= BASE_URL ?>/pages/course-detail.php?id=<?= $course['id'] ?>" class="course-card-featured">
                <img src="<?= htmlspecialchars($course['thumbnail']) ?>" alt="<?= htmlspecialchars($course['title']) ?>">
                <div class="card-badge" style="background: <?= CATEGORY_COLORS[$course['category']] ?? '#0056d2' ?>">
                    <?= CATEGORY_ICONS[$course['category']] ?? '📚' ?> <?= $course['category'] ?>
                </div>
                <div class="card-content">
                    <h4><?= htmlspecialchars($course['title']) ?></h4>
                    <p class="card-instructor">
                        <i class="fas fa-user-circle"></i> <?= htmlspecialchars($course['instructor_name'] ?? 'UTH Team') ?>
                    </p>
                    <div class="card-meta">
                        <span><i class="fas fa-star"></i> <?= number_format($course['rating'], 1) ?></span>
                        <span><i class="fas fa-users"></i> <?= formatNumber($course['students']) ?> học viên</span>
                        <span><i class="fas fa-signal"></i> <?= LEVELS[$course['level']] ?></span>
                    </div>
                    <div class="card-footer">
                        <?php if ($course['price'] > 0): ?>
                            <span class="card-price"><?= number_format($course['price']) ?>đ</span>
                        <?php else: ?>
                            <span class="card-price free">Miễn phí</span>
                        <?php endif; ?>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="categories-section">
    <div class="container">
        <h2 class="section-title">Khám phá theo ngôn ngữ lập trình</h2>
        <div class="categories-grid-6">
            <?php foreach (CATEGORIES as $slug => $name): ?>
            <a href="<?= BASE_URL ?>/pages/courses.php?category=<?= $slug ?>" class="category-card">
                <div class="category-icon" style="background: <?= CATEGORY_COLORS[$slug] ?>20">
                    <span style="font-size: 48px;"><?= CATEGORY_ICONS[$slug] ?></span>
                </div>
                <h3 class="category-name"><?= $slug ?></h3>
                <p class="category-desc"><?= $name ?></p>
                <p class="category-count"><?= countCourses($slug) ?> khóa học</p>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Learning Journey -->
<section class="journey-section">
    <div class="container">
        <h2 class="section-title">Lộ trình học lập trình web</h2>
        <div class="journey-timeline">
            <div class="journey-step">
                <div class="step-number">1</div>
                <div class="step-content">
                    <h3>HTML - Nền tảng</h3>
                    <p>Học cách xây dựng cấu trúc website với HTML5</p>
                    <span class="step-badge">Cơ bản</span>
                </div>
            </div>
            <div class="journey-arrow">→</div>
            <div class="journey-step">
                <div class="step-number">2</div>
                <div class="step-content">
                    <h3>CSS - Thiết kế</h3>
                    <p>Tạo giao diện đẹp và responsive với CSS3</p>
                    <span class="step-badge">Cơ bản</span>
                </div>
            </div>
            <div class="journey-arrow">→</div>
            <div class="journey-step">
                <div class="step-number">3</div>
                <div class="step-content">
                    <h3>JavaScript - Tương tác</h3>
                    <p>Làm website động với JavaScript ES6+</p>
                    <span class="step-badge">Trung cấp</span>
                </div>
            </div>
            <div class="journey-arrow">→</div>
            <div class="journey-step">
                <div class="step-number">4</div>
                <div class="step-content">
                    <h3>PHP - Backend</h3>
                    <p>Xây dựng server và database với PHP</p>
                    <span class="step-badge">Nâng cao</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials -->
<section class="testimonials-section">
    <div class="container">
        <h2 class="section-title">Học viên nói gì về chúng tôi</h2>
        <div class="testimonials-grid">
            <div class="testimonial-card">
                <div class="testimonial-quote">"</div>
                <p class="testimonial-text">
                    UTH Learning đã giúp tôi chuyển đổi sự nghiệp từ kế toán sang lập trình web. 
                    Các khóa học HTML, CSS, JavaScript rất dễ hiểu và thực tế.
                </p>
                <div class="testimonial-author">
                    <img src="<?= ASSETS_URL ?>/images/avatars/user1.jpg" alt="Nguyễn Văn A">
                    <div>
                        <p class="author-name">Nguyễn Văn A</p>
                        <p class="author-role">Web Developer</p>
                    </div>
                </div>
            </div>

            <div class="testimonial-card">
                <div class="testimonial-quote">"</div>
                <p class="testimonial-text">
                    Tôi đã hoàn thành cả lộ trình từ HTML đến PHP chỉ trong 3 tháng. 
                    Giờ tôi tự tin xây dựng website hoàn chỉnh.
                </p>
                <div class="testimonial-author">
                    <img src="<?= ASSETS_URL ?>/images/avatars/user2.jpg" alt="Trần Thị B">
                    <div>
                        <p class="author-name">Trần Thị B</p>
                        <p class="author-role">Fullstack Developer</p>
                    </div>
                </div>
            </div>

            <div class="testimonial-card">
                <div class="testimonial-quote">"</div>
                <p class="testimonial-text">
                    Python của UTH rất hay! Từ cơ bản đến nâng cao, có cả dự án thực tế. 
                    Đã giúp tôi tìm được việc làm Data Analyst.
                </p>
                <div class="testimonial-author">
                    <img src="<?= ASSETS_URL ?>/images/avatars/user3.jpg" alt="Lê Văn C">
                    <div>
                        <p class="author-name">Lê Văn C</p>
                        <p class="author-role">Data Analyst</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section">
    <div class="container">
        <div class="cta-content">
            <h2>Bắt đầu học lập trình ngay hôm nay</h2>
            <p>Tham gia cùng hàng nghìn học viên đang học tại UTH Learning</p>
            <a href="<?= BASE_URL ?>/auth/register.php" class="btn-primary-large">
                Đăng Ký Miễn Phí
            </a>
            <p class="cta-note">Miễn phí trọn đời • Không cần thẻ tín dụng</p>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
