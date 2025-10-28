<?php
require_once '../config/config.php';
require_once '../config/constants.php';
require_once '../includes/functions.php';

$courseId = (int)($_GET['id'] ?? 0);

// Get course details
$stmt = $pdo->prepare("
    SELECT c.*, u.fullname as instructor_name, u.avatar as instructor_avatar
    FROM courses c 
    LEFT JOIN users u ON c.instructor_id = u.id 
    WHERE c.id = ?
");
$stmt->execute([$courseId]);
$course = $stmt->fetch();

if (!$course) {
    header('Location: courses.php');
    exit;
}

// Get lessons
$stmt = $pdo->prepare("SELECT * FROM lessons WHERE course_id = ? ORDER BY order_num ASC");
$stmt->execute([$courseId]);
$lessons = $stmt->fetchAll();

// Check if user enrolled
$isEnrolled = false;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM enrollments WHERE user_id = ? AND course_id = ?");
    $stmt->execute([$_SESSION['user_id'], $courseId]);
    $isEnrolled = $stmt->fetch() ? true : false;
}

$pageTitle = htmlspecialchars($course['title']) . " - UTH Learning";

include '../includes/header.php';
?>

<div class="course-detail-page">
    <!-- Course Header -->
    <section class="course-header" style="background: linear-gradient(135deg, <?= CATEGORY_COLORS[$course['category']] ?>20, <?= CATEGORY_COLORS[$course['category']] ?>40)">
        <div class="container">
            <div class="course-header-grid">
                <div class="course-header-left">
                    <div class="breadcrumb">
                        <a href="../index.php">Trang chủ</a> / 
                        <a href="courses.php?category=<?= $course['category'] ?>"><?= $course['category'] ?></a> / 
                        <span><?= htmlspecialchars($course['title']) ?></span>
                    </div>
                    
                    <h1 class="course-title"><?= htmlspecialchars($course['title']) ?></h1>
                    <p class="course-subtitle"><?= htmlspecialchars($course['description']) ?></p>
                    
                    <div class="course-stats">
                        <span class="stat-badge">
                            <i class="fas fa-star"></i> 
                            <?= number_format($course['rating'], 1) ?> 
                            (<?= number_format($course['total_reviews']) ?> đánh giá)
                        </span>
                        <span class="stat-badge">
                            <i class="fas fa-users"></i> 
                            <?= formatNumber($course['students']) ?> học viên
                        </span>
                        <span class="stat-badge">
                            <i class="fas fa-clock"></i> 
                            <?= $course['duration'] ?>
                        </span>
                        <span class="stat-badge">
                            <i class="fas fa-signal"></i> 
                            <?= LEVELS[$course['level']] ?>
                        </span>
                    </div>

                    <div class="course-instructor-info">
                        <img src="<?= ASSETS_URL ?>/images/avatars/default.png" alt="Instructor" class="instructor-thumb">
                        <div>
                            <p class="instructor-label">Giảng viên</p>
                            <p class="instructor-name"><?= htmlspecialchars($course['instructor_name']) ?></p>
                        </div>
                    </div>
                </div>

                <div class="course-header-right">
                    <div class="course-card-sticky">
                        <img src="<?= htmlspecialchars($course['thumbnail']) ?>" 
                             alt="<?= htmlspecialchars($course['title']) ?>" 
                             class="course-preview-img">
                        
                        <div class="course-card-body">
                            <?php if ($course['price'] > 0): ?>
                                <div class="course-price-large"><?= number_format($course['price']) ?>đ</div>
                            <?php else: ?>
                                <div class="course-price-large free">Miễn phí</div>
                            <?php endif; ?>

                            <?php if ($isEnrolled): ?>
                                <a href="../learning/lesson.php?course=<?= $courseId ?>" class="btn-primary-large full-width">
                                    <i class="fas fa-play-circle"></i> Tiếp tục học
                                </a>
                            <?php else: ?>
                                <?php if ($course['price'] > 0): ?>
                                    <a href="../payment/checkout.php?course_id=<?= $courseId ?>" class="btn-primary-large full-width">
                                        <i class="fas fa-shopping-cart"></i> Đăng ký ngay
                                    </a>
                                <?php else: ?>
                                    <form method="POST" action="enroll.php">
                                        <input type="hidden" name="course_id" value="<?= $courseId ?>">
                                        <button type="submit" class="btn-primary-large full-width">
                                            <i class="fas fa-check-circle"></i> Đăng ký miễn phí
                                        </button>
                                    </form>
                                <?php endif; ?>
                            <?php endif; ?>

                            <p class="enroll-note">
                                <i class="fas fa-infinity"></i> Truy cập trọn đời
                            </p>
                            
                            <div class="course-includes">
                                <p class="includes-title">Khóa học bao gồm:</p>
                                <ul class="includes-list">
                                    <li><i class="fas fa-video"></i> <?= count($lessons) ?> bài học video</li>
                                    <li><i class="fas fa-file-alt"></i> Tài liệu tải về</li>
                                    <li><i class="fas fa-tasks"></i> Bài tập thực hành</li>
                                    <li><i class="fas fa-certificate"></i> Chứng chỉ hoàn thành</li>
                                    <li><i class="fas fa-mobile-alt"></i> Học trên mọi thiết bị</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Course Content -->
    <section class="course-content-section">
        <div class="container">
            <div class="course-tabs">
                <button class="tab-btn active" onclick="showTab('overview')">Tổng quan</button>
                <button class="tab-btn" onclick="showTab('curriculum')">Nội dung khóa học</button>
                <button class="tab-btn" onclick="showTab('instructor')">Giảng viên</button>
                <button class="tab-btn" onclick="showTab('reviews')">Đánh giá</button>
            </div>

            <!-- Tab: Overview -->
            <div id="overview" class="tab-content active">
                <h2>Giới thiệu về khóa học</h2>
                <div class="course-description">
                    <?= nl2br(htmlspecialchars($course['description'])) ?>
                </div>

                <h3>Bạn sẽ học được gì</h3>
                <div class="learning-outcomes">
                    <ul>
                        <li>Nắm vững kiến thức cơ bản và nâng cao của <?= $course['category'] ?></li>
                        <li>Xây dựng các dự án thực tế từ đơn giản đến phức tạp</li>
                        <li>Áp dụng best practices và coding standards</li>
                        <li>Chuẩn bị cho các vị trí việc làm trong ngành công nghệ</li>
                    </ul>
                </div>

                <h3>Yêu cầu</h3>
                <div class="requirements">
                    <ul>
                        <li>Máy tính có kết nối internet</li>
                        <li>Không yêu cầu kinh nghiệm lập trình trước đó</li>
                        <li>Đam mê học hỏi và thực hành</li>
                    </ul>
                </div>
            </div>

            <!-- Tab: Curriculum -->
            <div id="curriculum" class="tab-content">
                <h2>Nội dung khóa học</h2>
                <p class="curriculum-summary"><?= count($lessons) ?> bài học • <?= $course['duration'] ?></p>
                
                <div class="curriculum-list">
                    <?php foreach ($lessons as $index => $lesson): ?>
                    <div class="curriculum-item">
                        <div class="curriculum-header">
                            <div class="lesson-info">
                                <span class="lesson-number"><?= $index + 1 ?></span>
                                <div class="lesson-details">
                                    <h4><?= htmlspecialchars($lesson['title']) ?></h4>
                                    <div class="lesson-meta">
                                        <span><i class="fas fa-video"></i> Video</span>
                                        <span><i class="fas fa-clock"></i> <?= $lesson['duration'] ?> phút</span>
                                    </div>
                                </div>
                            </div>
                            <?php if ($lesson['is_free'] || $isEnrolled): ?>
                                <a href="../learning/lesson.php?id=<?= $lesson['id'] ?>" class="btn-preview">
                                    <i class="fas fa-play"></i> <?= $lesson['is_free'] ? 'Xem thử' : 'Xem' ?>
                                </a>
                            <?php else: ?>
                                <i class="fas fa-lock" style="color: #999;"></i>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Tab: Instructor -->
            <div id="instructor" class="tab-content">
                <h2>Giảng viên</h2>
                <div class="instructor-profile">
                    <img src="<?= ASSETS_URL ?>/images/avatars/default.png" alt="Instructor" class="instructor-avatar">
                    <div class="instructor-info">
                        <h3><?= htmlspecialchars($course['instructor_name']) ?></h3>
                        <p class="instructor-title">Chuyên gia <?= $course['category'] ?></p>
                        <div class="instructor-stats">
                            <span><i class="fas fa-users"></i> 5,000+ học viên</span>
                            <span><i class="fas fa-book"></i> 10+ khóa học</span>
                            <span><i class="fas fa-star"></i> 4.8 đánh giá</span>
                        </div>
                        <p class="instructor-bio">
                            Với hơn 10 năm kinh nghiệm trong lĩnh vực lập trình và giảng dạy, 
                            tôi đã giúp hàng nghìn học viên bắt đầu và phát triển sự nghiệp trong ngành công nghệ.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Tab: Reviews -->
            <div id="reviews" class="tab-content">
                <h2>Đánh giá từ học viên</h2>
                <div class="reviews-summary">
                    <div class="rating-overview">
                        <h1 class="rating-score"><?= number_format($course['rating'], 1) ?></h1>
                        <div class="rating-stars">⭐⭐⭐⭐⭐</div>
                        <p><?= number_format($course['total_reviews']) ?> đánh giá</p>
                    </div>
                </div>

                <div class="reviews-list">
                    <!-- Sample reviews -->
                    <div class="review-item">
                        <div class="review-header">
                            <img src="<?= ASSETS_URL ?>/images/avatars/user1.jpg" alt="User" class="review-avatar">
                            <div>
                                <p class="review-author">Nguyễn Văn A</p>
                                <div class="review-rating">⭐⭐⭐⭐⭐</div>
                                <p class="review-date">2 ngày trước</p>
                            </div>
                        </div>
                        <p class="review-text">
                            Khóa học rất chi tiết và dễ hiểu. Giảng viên giải thích rất rõ ràng. 
                            Đây là khóa học <?= $course['category'] ?> hay nhất mà tôi từng học!
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
function showTab(tabName) {
    document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.getElementById(tabName).classList.add('active');
    event.target.classList.add('active');
}
</script>

<style>
/* Course Detail Styles */
.course-header { padding: 60px 0; }
.course-header-grid { display: grid; grid-template-columns: 1fr 400px; gap: 64px; }
.breadcrumb { font-size: 14px; margin-bottom: 20px; color: #545454; }
.breadcrumb a { color: #0056d2; text-decoration: none; }
.course-title { font-size: 40px; margin-bottom: 16px; color: #1f1f1f; }
.course-subtitle { font-size: 18px; color: #545454; margin-bottom: 24px; }
.course-stats { display: flex; flex-wrap: wrap; gap: 16px; margin-bottom: 24px; }
.stat-badge { background: #fff; padding: 8px 16px; border-radius: 20px; font-size: 14px; }
.course-instructor-info { display: flex; align-items: center; gap: 16px; }
.instructor-thumb { width: 56px; height: 56px; border-radius: 50%; }
.instructor-label { font-size: 12px; color: #545454; }
.instructor-name { font-size: 16px; font-weight: 600; }
.course-card-sticky { background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.15); position: sticky; top: 100px; }
.course-preview-img { width: 100%; height: 225px; object-fit: cover; }
.course-card-body { padding: 24px; }
.course-price-large { font-size: 36px; font-weight: 700; color: #1f1f1f; margin-bottom: 20px; }
.course-price-large.free { color: #0cae74; }
.full-width { width: 100%; text-align: center; display: block; }
.enroll-note { text-align: center; font-size: 14px; color: #545454; margin: 16px 0 24px; }
.includes-title { font-weight: 600; margin-bottom: 12px; }
.includes-list { list-style: none; padding: 0; }
.includes-list li { margin-bottom: 10px; }
.course-tabs { border-bottom: 2px solid #e5e5e5; margin: 48px 0 32px; display: flex; gap: 32px; }
.tab-btn { background: none; border: none; padding: 16px 0; font-size: 16px; font-weight: 600; cursor: pointer; border-bottom: 3px solid transparent; transition: all 0.2s; }
.tab-btn.active { color: #0056d2; border-bottom-color: #0056d2; }
.tab-content { display: none; padding: 32px 0; }
.tab-content.active { display: block; }
.curriculum-item { padding: 20px; border: 1px solid #e5e5e5; border-radius: 8px; margin-bottom: 12px; }
.curriculum-header { display: flex; justify-content: space-between; align-items: center; }
.lesson-info { display: flex; align-items: center; gap: 16px; }
.lesson-number { width: 40px; height: 40px; background: #f5f7fa; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; }
.lesson-meta { display: flex; gap: 16px; font-size: 14px; color: #545454; margin-top: 4px; }
.btn-preview { background: #e8f0fe; color: #0056d2; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 600; }
@media (max-width: 768px) {
    .course-header-grid { grid-template-columns: 1fr; }
    .course-card-sticky { position: static; }
}
</style>

<?php include '../includes/footer.php'; ?>
