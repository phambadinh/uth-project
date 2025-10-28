<?php
$pageTitle = "Dashboard - Tổng quan hệ thống";
include 'includes/admin-header.php';

// Get statistics
$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role='student'");
$totalStudents = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role='instructor'");
$totalInstructors = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM courses WHERE status='published'");
$totalCourses = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT SUM(amount) FROM payments WHERE status='completed'");
$totalRevenue = $stmt->fetchColumn() ?? 0;

$stmt = $pdo->query("SELECT COUNT(*) FROM enrollments");
$totalEnrollments = $stmt->fetchColumn();

// Recent enrollments
$stmt = $pdo->query("
    SELECT e.*, u.fullname, c.title as course_title 
    FROM enrollments e
    JOIN users u ON e.user_id = u.id
    JOIN courses c ON e.course_id = c.id
    ORDER BY e.enrolled_at DESC
    LIMIT 10
");
$recentEnrollments = $stmt->fetchAll();

// Top courses
$stmt = $pdo->query("
    SELECT * FROM courses 
    WHERE status='published' 
    ORDER BY students DESC 
    LIMIT 5
");
$topCourses = $stmt->fetchAll();

// Chart data - Enrollments per month
$stmt = $pdo->query("
    SELECT DATE_FORMAT(enrolled_at, '%Y-%m') as month, COUNT(*) as count
    FROM enrollments
    WHERE enrolled_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY month
    ORDER BY month ASC
");
$enrollmentData = $stmt->fetchAll();

$months = array_column($enrollmentData, 'month');
$counts = array_column($enrollmentData, 'count');
?>

<div class="admin-page-header">
    <div>
        <h1>Dashboard</h1>
        <p>Tổng quan hệ thống quản lý khóa học</p>
    </div>
    <div>
        <span class="text-muted">Cập nhật: <?= date('d/m/Y H:i') ?></span>
    </div>
</div>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card bg-primary">
        <div class="stat-icon">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-content">
            <h3><?= number_format($totalStudents) ?></h3>
            <p>Học viên</p>
            <a href="users.php?role=student" class="stat-link">Xem chi tiết →</a>
        </div>
    </div>

    <div class="stat-card bg-success">
        <div class="stat-icon">
            <i class="fas fa-chalkboard-teacher"></i>
        </div>
        <div class="stat-content">
            <h3><?= number_format($totalInstructors) ?></h3>
            <p>Giảng viên</p>
            <a href="users.php?role=instructor" class="stat-link">Xem chi tiết →</a>
        </div>
    </div>

    <div class="stat-card bg-warning">
        <div class="stat-icon">
            <i class="fas fa-book"></i>
        </div>
        <div class="stat-content">
            <h3><?= number_format($totalCourses) ?></h3>
            <p>Khóa học</p>
            <a href="courses.php" class="stat-link">Xem chi tiết →</a>
        </div>
    </div>

    <div class="stat-card bg-info">
        <div class="stat-icon">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <div class="stat-content">
            <h3><?= number_format($totalRevenue) ?>đ</h3>
            <p>Doanh thu</p>
            <a href="payments.php" class="stat-link">Xem chi tiết →</a>
        </div>
    </div>
</div>

<!-- Charts & Recent Activity -->
<div class="dashboard-grid">
    <!-- Chart: Enrollments -->
    <div class="dashboard-card">
        <div class="card-header">
            <h2>Đăng ký 6 tháng gần đây</h2>
        </div>
        <div class="card-body">
            <canvas id="enrollmentChart"></canvas>
        </div>
    </div>

    <!-- Recent Enrollments -->
    <div class="dashboard-card">
        <div class="card-header">
            <h2>Đăng ký gần đây</h2>
            <a href="enrollments.php" class="btn-link">Xem tất cả</a>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Học viên</th>
                        <th>Khóa học</th>
                        <th>Tiến độ</th>
                        <th>Ngày đăng ký</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentEnrollments as $enrollment): ?>
                    <tr>
                        <td><?= htmlspecialchars($enrollment['fullname']) ?></td>
                        <td><?= htmlspecialchars($enrollment['course_title']) ?></td>
                        <td>
                            <div class="progress-bar-mini">
                                <div class="progress-fill-mini" style="width: <?= $enrollment['progress'] ?>%"></div>
                            </div>
                            <?= number_format($enrollment['progress'], 0) ?>%
                        </td>
                        <td><?= date('d/m/Y', strtotime($enrollment['enrolled_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Top Courses -->
<div class="dashboard-card">
    <div class="card-header">
        <h2>Top 5 khóa học phổ biến</h2>
        <a href="courses.php" class="btn-link">Xem tất cả</a>
    </div>
    <div class="top-courses-grid">
        <?php foreach ($topCourses as $course): ?>
        <div class="top-course-item">
            <img src="<?= htmlspecialchars($course['thumbnail']) ?>" alt="<?= htmlspecialchars($course['title']) ?>">
            <div class="top-course-info">
                <h4><?= htmlspecialchars($course['title']) ?></h4>
                <p>
                    <i class="fas fa-users"></i> <?= number_format($course['students']) ?> học viên • 
                    <i class="fas fa-star"></i> <?= number_format($course['rating'], 1) ?>
                </p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
// Enrollment Chart
const ctx = document.getElementById('enrollmentChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($months) ?>,
        datasets: [{
            label: 'Số lượng đăng ký',
            data: <?= json_encode($counts) ?>,
            borderColor: '#0056d2',
            backgroundColor: 'rgba(0, 86, 210, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    precision: 0
                }
            }
        }
    }
});
</script>

<?php include 'includes/admin-footer.php'; ?>
