<?php
require_once '../config/config.php';
require_once '../includes/functions.php';

requireAdmin();

// Get all students with enrollment count
global $pdo;
$students = $pdo->query("
    SELECT u.*, COUNT(e.id) as total_enrollments
    FROM users u
    LEFT JOIN enrollments e ON u.id = e.user_id
    WHERE u.role = 'student'
    GROUP BY u.id
    ORDER BY u.created_at DESC
")->fetchAll();

$pageTitle = "Quản lý Học viên";
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
            <h1>🎓 Quản lý Học viên</h1>
        </header>
        
        <div class="admin-content">
            <div class="admin-card">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Họ tên</th>
                            <th>Email</th>
                            <th>Số khóa học</th>
                            <th>Ngày đăng ký</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?= $student['id'] ?></td>
                            <td><?= htmlspecialchars($student['fullname']) ?></td>
                            <td><?= htmlspecialchars($student['email']) ?></td>
                            <td><?= $student['total_enrollments'] ?></td>
                            <td><?= date('d/m/Y', strtotime($student['created_at'])) ?></td>
                            <td>
                                <button class="btn btn-sm btn-outline" onclick="viewStudent(<?= $student['id'] ?>)">Chi tiết</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <script>
    function viewStudent(id) {
        alert('Xem chi tiết học viên #' + id);
    }
    </script>
</body>
</html>
