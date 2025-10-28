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

// Get filter
$filter = $_GET['filter'] ?? 'all';

// Build query
$sql = "
    SELECT c.*, e.progress, e.enrolled_at, e.last_accessed
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    WHERE e.user_id = ?
";

if ($filter === 'in-progress') {
    $sql .= " AND e.progress > 0 AND e.progress < 100";
} elseif ($filter === 'completed') {
    $sql .= " AND e.progress >= 100";
} elseif ($filter === 'not-started') {
    $sql .= " AND e.progress = 0";
}

$sql .= " ORDER BY e.last_accessed DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$userId]);
$courses = $stmt->fetchAll();

// Get counts
$totalCount = $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE user_id = ?");
$totalCount->execute([$userId]);
$total = $totalCount->fetchColumn();

$inProgressCount = $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE user_id = ? AND progress > 0 AND progress < 100");
$inProgressCount->execute([$userId]);
$inProgress = $inProgressCount->fetchColumn();

$completedCount = $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE user_id = ? AND progress >= 100");
$completedCount->execute([$userId]);
$completed = $completedCount->fetchColumn();

$pageTitle = "Khóa học của tôi - UTH Learning";
include '../includes/header.php';
?>

<div class="my-courses-page">
    <div class="container">
        <div class="page-header">
            <h1>Khóa học của tôi</h1>
            <p>Quản lý và theo dõi tiến độ học tập</p>
        </div>

        <!-- Filter Tabs -->
        <div class="filter-tabs">
            <a href="?filter=all" class="tab <?= $filter === 'all' ? 'active' : '' ?>">
                Tất cả (<?= $total ?>)
            </a>
            <a href="?filter=in-progress" class="tab <?= $filter === 'in-progress' ? 'active' : '' ?>">
                Đang học (<?= $inProgress ?>)
            </a>
            <a href="?filter=completed" class="tab <?= $filter === 'completed' ? 'active' : '' ?>">
                Đã hoàn thành (<?= $completed ?>)
            </a>
            <a href="?filter=not-started" class="tab <?= $filter === 'not-started' ? 'active' : '' ?>">
                Chưa bắt đầu
            </a>
        </div>

        <!-- Courses List -->
        <?php if (empty($courses)): ?>
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <h3>Không có khóa học nào</h3>
            <p>Bạn chưa đăng ký khóa học nào trong danh mục này</p>
            <a href="../pages/courses.php" class="btn-primary">Khám phá khóa học</a>
        </div>
        <?php else: ?>
        <div class="courses-list">
            <?php foreach ($courses as $course): ?>
            <div class="my-course-card">
                <div class="course-thumbnail">
                    <img src="<?= htmlspecialchars($course['thumbnail']) ?>" alt="<?= htmlspecialchars($course['title']) ?>">
                    <span class="course-badge" style="background: <?= CATEGORY_COLORS[$course['category']] ?>">
                        <?= CATEGORY_ICONS[$course['category']] ?> <?= $course['category'] ?>
                    </span>
                </div>

                <div class="course-details">
                    <h3><?= htmlspecialchars($course['title']) ?></h3>
                    <p class="course-description"><?= htmlspecialchars(substr($course['description'], 0, 150)) ?>...</p>
                    
                    <div class="course-meta-info">
                        <span><i class="fas fa-calendar"></i> Đăng ký: <?= date('d/m/Y', strtotime($course['enrolled_at'])) ?></span>
                        <span><i class="fas fa-clock"></i> Truy cập: <?= timeAgo($course['last_accessed']) ?></span>
                    </div>

                    <div class="course-progress-section">
                        <div class="progress-info">
                            <span>Tiến độ</span>
                            <span class="progress-percentage"><?= number_format($course['progress'], 0) ?>%</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?= $course['progress'] ?>%"></div>
                        </div>
                    </div>
                </div>

                <div class="course-actions-vertical">
                    <?php if ($course['progress'] >= 100): ?>
                        <a href="../student/certificates.php" class="btn-success btn-sm">
                            <i class="fas fa-certificate"></i> Xem chứng chỉ
                        </a>
                    <?php elseif ($course['progress'] > 0): ?>
                        <a href="../learning/lesson.php?course=<?= $course['id'] ?>" class="btn-primary btn-sm">
                            <i class="fas fa-play"></i> Tiếp tục học
                        </a>
                    <?php else: ?>
                        <a href="../learning/lesson.php?course=<?= $course['id'] ?>" class="btn-primary btn-sm">
                            <i class="fas fa-play"></i> Bắt đầu học
                        </a>
                    <?php endif; ?>
                    
                    <a href="../pages/course-detail.php?id=<?= $course['id'] ?>" class="btn-outline btn-sm">
                        <i class="fas fa-info-circle"></i> Chi tiết
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.my-courses-page { padding: 40px 0; background: #f5f7fa; min-height: calc(100vh - 72px); }
.page-header { margin-bottom: 32px; }
.page-header h1 { font-size: 32px; margin-bottom: 8px; }
.page-header p { color: var(--gray-600); }

.filter-tabs { display: flex; gap: 8px; margin-bottom: 32px; background: #fff; padding: 8px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
.tab { padding: 12px 24px; border-radius: 8px; text-decoration: none; color: var(--gray-700); font-weight: 600; transition: all 0.2s; }
.tab:hover { background: var(--gray-100); }
.tab.active { background: var(--primary); color: #fff; }

.courses-list { display: flex; flex-direction: column; gap: 20px; }
.my-course-card { background: #fff; border-radius: 12px; padding: 24px; display: flex; gap: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); transition: all 0.3s; }
.my-course-card:hover { box-shadow: 0 8px 24px rgba(0,0,0,0.1); }

.course-thumbnail { position: relative; width: 280px; flex-shrink: 0; }
.course-thumbnail img { width: 100%; height: 160px; object-fit: cover; border-radius: 8px; }

.course-details { flex: 1; }
.course-details h3 { font-size: 20px; margin-bottom: 12px; }
.course-description { color: var(--gray-600); margin-bottom: 16px; font-size: 14px; line-height: 1.6; }
.course-meta-info { display: flex; gap: 24px; margin-bottom: 16px; font-size: 13px; color: var(--gray-600); }
.course-meta-info i { margin-right: 6px; }

.course-progress-section { margin-top: 16px; }
.progress-info { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 14px; }
.progress-percentage { font-weight: 700; color: var(--primary); }

.course-actions-vertical { display: flex; flex-direction: column; gap: 12px; justify-content: center; }

@media (max-width: 767px) {
    .my-course-card { flex-direction: column; }
    .course-thumbnail { width: 100%; }
    .filter-tabs { flex-wrap: wrap; }
    .tab { padding: 10px 16px; font-size: 14px; }
}
</style>

<?php include '../includes/footer.php'; ?>
