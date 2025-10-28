<?php
require_once '../config/config.php';
require_once '../config/constants.php';
require_once '../includes/functions.php';

// Check authentication
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../auth/login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Get statistics
$stmt = $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE user_id = ?");
$stmt->execute([$userId]);
$totalCourses = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE user_id = ? AND progress >= 100");
$stmt->execute([$userId]);
$completedCourses = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM certificates WHERE user_id = ?");
$stmt->execute([$userId]);
$totalCertificates = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT AVG(progress) FROM enrollments WHERE user_id = ?");
$stmt->execute([$userId]);
$avgProgress = $stmt->fetchColumn() ?? 0;

// Get enrolled courses
$stmt = $pdo->prepare("
    SELECT c.*, e.progress, e.last_accessed
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    WHERE e.user_id = ?
    ORDER BY e.last_accessed DESC
    LIMIT 6
");
$stmt->execute([$userId]);
$courses = $stmt->fetchAll();

// Get recent activity
$stmt = $pdo->prepare("
    SELECT 
        l.title as lesson_title,
        c.title as course_title,
        c.category,
        c.id as course_id,
        vp.last_watched,
        vp.completed
    FROM video_progress vp
    JOIN lessons l ON vp.lesson_id = l.id
    JOIN courses c ON l.course_id = c.id
    WHERE vp.user_id = ?
    ORDER BY vp.last_watched DESC
    LIMIT 8
");
$stmt->execute([$userId]);
$activities = $stmt->fetchAll();

$pageTitle = "Dashboard - UTH Learning";
include '../includes/header.php';
?>

<div class="student-dashboard">
    <div class="container">
        <div class="dashboard-header">
            <div>
                <h1>Chào mừng trở lại, <?= htmlspecialchars($_SESSION['fullname']) ?>! 👋</h1>
                <p>Tiếp tục hành trình học tập của bạn</p>
            </div>
            <a href="../pages/courses.php" class="btn-primary">
                <i class="fas fa-plus"></i> Khám phá khóa học mới
            </a>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid-student">
            <div class="stat-card-student">
                <div class="stat-icon-student bg-primary">
                    <i class="fas fa-book"></i>
                </div>
                <div class="stat-content-student">
                    <h3><?= $totalCourses ?></h3>
                    <p>Khóa học đang học</p>
                </div>
            </div>

            <div class="stat-card-student">
                <div class="stat-icon-student bg-success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content-student">
                    <h3><?= $completedCourses ?></h3>
                    <p>Đã hoàn thành</p>
                </div>
            </div>

            <div class="stat-card-student">
                <div class="stat-icon-student bg-warning">
                    <i class="fas fa-certificate"></i>
                </div>
                <div class="stat-content-student">
                    <h3><?= $totalCertificates ?></h3>
                    <p>Chứng chỉ</p>
                </div>
            </div>

            <div class="stat-card-student">
                <div class="stat-icon-student bg-info">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-content-student">
                    <h3><?= number_format($avgProgress, 0) ?>%</h3>
                    <p>Tiến độ trung bình</p>
                </div>
            </div>
        </div>

        <!-- My Courses -->
        <section class="dashboard-section">
            <div class="section-header">
                <h2>Khóa học của tôi</h2>
                <a href="my-courses.php" class="btn-link">Xem tất cả →</a>
            </div>

            <?php if (empty($courses)): ?>
            <div class="empty-state">
                <i class="fas fa-book-open"></i>
                <h3>Chưa có khóa học nào</h3>
                <p>Bắt đầu hành trình học tập của bạn ngay hôm nay</p>
                <a href="../pages/courses.php" class="btn-primary">Khám phá khóa học</a>
            </div>
            <?php else: ?>
            <div class="courses-grid-dashboard">
                <?php foreach ($courses as $course): ?>
                <div class="course-card-dashboard">
                    <img src="<?= htmlspecialchars($course['thumbnail']) ?>" alt="<?= htmlspecialchars($course['title']) ?>">
                    <div class="course-info-dashboard">
                        <span class="course-category" style="background: <?= CATEGORY_COLORS[$course['category']] ?>">
                            <?= CATEGORY_ICONS[$course['category']] ?> <?= $course['category'] ?>
                        </span>
                        <h3><?= htmlspecialchars($course['title']) ?></h3>
                        
                        <div class="progress-info">
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?= $course['progress'] ?>%"></div>
                            </div>
                            <span class="progress-text"><?= number_format($course['progress'], 0) ?>%</span>
                        </div>
                        
                        <div class="course-actions">
                            <?php if ($course['progress'] > 0): ?>
                            <a href="../learning/lesson.php?course=<?= $course['id'] ?>" class="btn-primary btn-sm full-width">
                                <i class="fas fa-play"></i> Tiếp tục học
                            </a>
                            <?php else: ?>
                            <a href="../learning/lesson.php?course=<?= $course['id'] ?>" class="btn-primary btn-sm full-width">
                                <i class="fas fa-play"></i> Bắt đầu học
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </section>

        <!-- Recent Activity -->
        <?php if (!empty($activities)): ?>
        <section class="dashboard-section">
            <div class="section-header">
                <h2>Hoạt động gần đây</h2>
            </div>

            <div class="activity-list">
                <?php foreach ($activities as $activity): ?>
                <div class="activity-item">
                    <div class="activity-icon" style="background: <?= CATEGORY_COLORS[$activity['category']] ?>">
                        <?= CATEGORY_ICONS[$activity['category']] ?>
                    </div>
                    <div class="activity-content">
                        <h4><?= htmlspecialchars($activity['lesson_title']) ?></h4>
                        <p><?= htmlspecialchars($activity['course_title']) ?></p>
                        <span class="activity-time"><?= timeAgo($activity['last_watched']) ?></span>
                    </div>
                    <div class="activity-status">
                        <?php if ($activity['completed']): ?>
                            <span class="status-badge completed">
                                <i class="fas fa-check-circle"></i> Đã hoàn thành
                            </span>
                        <?php else: ?>
                            <a href="../learning/lesson.php?course=<?= $activity['course_id'] ?>" class="btn-outline btn-sm">
                                Tiếp tục
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Quick Actions -->
        <section class="dashboard-section">
            <div class="section-header">
                <h2>Liên kết nhanh</h2>
            </div>

            <div class="quick-actions-grid">
                <a href="my-courses.php" class="quick-action-card">
                    <i class="fas fa-book"></i>
                    <h3>Khóa học của tôi</h3>
                    <p>Xem tất cả khóa học đã đăng ký</p>
                </a>

                <a href="certificates.php" class="quick-action-card">
                    <i class="fas fa-certificate"></i>
                    <h3>Chứng chỉ</h3>
                    <p>Xem và tải xuống chứng chỉ</p>
                </a>

                <a href="progress.php" class="quick-action-card">
                    <i class="fas fa-chart-line"></i>
                    <h3>Tiến độ học tập</h3>
                    <p>Theo dõi quá trình học của bạn</p>
                </a>

                <a href="profile.php" class="quick-action-card">
                    <i class="fas fa-user"></i>
                    <h3>Hồ sơ</h3>
                    <p>Cập nhật thông tin cá nhân</p>
                </a>
            </div>
        </section>
    </div>
</div>

<style>
.student-dashboard { padding: 40px 0; background: #f5f7fa; min-height: calc(100vh - 72px); }
.dashboard-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; }
.dashboard-header h1 { font-size: 32px; margin-bottom: 8px; }
.dashboard-header p { color: var(--gray-600); }

.stats-grid-student { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; margin-bottom: 40px; }
.stat-card-student { background: #fff; border-radius: 12px; padding: 24px; display: flex; gap: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
.stat-icon-student { width: 56px; height: 56px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; color: #fff; }
.stat-icon-student.bg-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.stat-icon-student.bg-success { background: linear-gradient(135deg, #0cae74 0%, #059669 100%); }
.stat-icon-student.bg-warning { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
.stat-icon-student.bg-info { background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); }
.stat-content-student h3 { font-size: 32px; font-weight: 700; margin-bottom: 4px; }
.stat-content-student p { color: var(--gray-600); font-size: 14px; }

.dashboard-section { background: #fff; border-radius: 12px; padding: 32px; margin-bottom: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
.section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
.section-header h2 { font-size: 24px; font-weight: 600; }
.btn-link { color: var(--primary); font-weight: 600; }

.courses-grid-dashboard { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
.course-card-dashboard { background: #fff; border: 1px solid var(--gray-200); border-radius: 12px; overflow: hidden; transition: all 0.3s; }
.course-card-dashboard:hover { box-shadow: 0 8px 24px rgba(0,0,0,0.1); transform: translateY(-4px); }
.course-card-dashboard img { width: 100%; height: 150px; object-fit: cover; }
.course-info-dashboard { padding: 20px; }
.course-category { display: inline-block; padding: 4px 10px; border-radius: 20px; color: #fff; font-size: 12px; font-weight: 600; margin-bottom: 12px; }
.course-info-dashboard h3 { font-size: 16px; margin-bottom: 16px; line-height: 1.4; }
.progress-info { margin-bottom: 16px; }
.progress-text { font-size: 14px; font-weight: 600; color: var(--primary); }

.activity-list { display: flex; flex-direction: column; gap: 16px; }
.activity-item { display: flex; gap: 16px; align-items: center; padding: 16px; background: var(--gray-50); border-radius: 8px; }
.activity-icon { width: 48px; height: 48px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 20px; color: #fff; flex-shrink: 0; }
.activity-content { flex: 1; }
.activity-content h4 { font-size: 15px; margin-bottom: 4px; }
.activity-content p { font-size: 13px; color: var(--gray-600); margin-bottom: 4px; }
.activity-time { font-size: 12px; color: var(--gray-500); }
.status-badge { padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
.status-badge.completed { background: #d1fae5; color: #065f46; }

.quick-actions-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; }
.quick-action-card { background: var(--gray-50); border-radius: 12px; padding: 24px; text-align: center; transition: all 0.3s; text-decoration: none; color: inherit; }
.quick-action-card:hover { background: var(--primary); color: #fff; transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0,86,210,0.2); }
.quick-action-card i { font-size: 36px; color: var(--primary); margin-bottom: 16px; }
.quick-action-card:hover i { color: #fff; }
.quick-action-card h3 { font-size: 16px; margin-bottom: 8px; }
.quick-action-card p { font-size: 13px; color: var(--gray-600); }
.quick-action-card:hover p { color: rgba(255,255,255,0.9); }

.empty-state { text-align: center; padding: 60px 20px; }
.empty-state i { font-size: 64px; color: var(--gray-400); margin-bottom: 20px; }
.empty-state h3 { font-size: 24px; margin-bottom: 12px; }
.empty-state p { color: var(--gray-600); margin-bottom: 24px; }

@media (max-width: 991px) {
    .stats-grid-student { grid-template-columns: repeat(2, 1fr); }
    .courses-grid-dashboard { grid-template-columns: repeat(2, 1fr); }
    .quick-actions-grid { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 767px) {
    .dashboard-header { flex-direction: column; align-items: flex-start; gap: 16px; }
    .stats-grid-student, .courses-grid-dashboard, .quick-actions-grid { grid-template-columns: 1fr; }
    .activity-item { flex-direction: column; align-items: flex-start; }
}
</style>

<?php include '../includes/footer.php'; ?>
