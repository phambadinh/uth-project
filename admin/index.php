<?php
require_once '../config/config.php';
require_once '../includes/functions.php';

requireAdmin();

// Get statistics
global $pdo;
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalCourses = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
$totalEnrollments = $pdo->query("SELECT COUNT(*) FROM enrollments")->fetchColumn();
$totalSubmissions = $pdo->query("SELECT COUNT(*) FROM submissions")->fetchColumn();

// Get recent users
$recentUsers = $pdo->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5")->fetchAll();

// Get recent enrollments
$recentEnrollments = $pdo->query("
    SELECT e.*, u.fullname as student_name, c.title as course_title
    FROM enrollments e
    JOIN users u ON e.user_id = u.id
    JOIN courses c ON e.course_id = c.id
    ORDER BY e.enrolled_at DESC LIMIT 5
")->fetchAll();

$pageTitle = "Admin Dashboard";
$additionalCSS = "admin.css";
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/style.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/<?= $additionalCSS ?>">
</head>
<body class="admin-body">
    <?php include 'sidebar.php'; ?>
    
    <div class="admin-main">
        <header class="admin-header">
            <h1>🎛️ Admin Dashboard</h1>
            <div class="admin-user">
                <span>Xin chào, <?= htmlspecialchars($_SESSION['fullname']) ?></span>
                <a href="<?= BASE_URL ?>/auth/logout.php" class="btn btn-sm btn-outline">Đăng xuất</a>
            </div>
        </header>
        
        <div class="admin-content">
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-info">
                        <div class="stat-value"><?= number_format($totalUsers) ?></div>
                        <div class="stat-label">Người dùng</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">📚</div>
                    <div class="stat-info">
                        <div class="stat-value"><?= number_format($totalCourses) ?></div>
                        <div class="stat-label">Khóa học</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">✅</div>
                    <div class="stat-info">
                        <div class="stat-value"><?= number_format($totalEnrollments) ?></div>
                        <div class="stat-label">Đăng ký</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">✍️</div>
                    <div class="stat-info">
                        <div class="stat-value"><?= number_format($totalSubmissions) ?></div>
                        <div class="stat-label">Bài nộp</div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Activity -->
            <div class="admin-row">
                <div class="admin-col">
                    <div class="admin-card">
                        <h3>Người dùng mới</h3>
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Họ tên</th>
                                    <th>Email</th>
                                    <th>Vai trò</th>
                                    <th>Ngày tạo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentUsers as $user): ?>
                                <tr>
                                    <td><?= htmlspecialchars($user['fullname']) ?></td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td><span class="badge badge-<?= $user['role'] ?>"><?= $user['role'] ?></span></td>
                                    <td><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="admin-col">
                    <div class="admin-card">
                        <h3>Đăng ký gần đây</h3>
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Học viên</th>
                                    <th>Khóa học</th>
                                    <th>Ngày đăng ký</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentEnrollments as $enroll): ?>
                                <tr>
                                    <td><?= htmlspecialchars($enroll['student_name']) ?></td>
                                    <td><?= htmlspecialchars($enroll['course_title']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($enroll['enrolled_at'])) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
