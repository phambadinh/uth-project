<?php
require_once '../config/config.php';
require_once '../includes/functions.php';

requireAdmin();

// Get all exercises
global $pdo;
$exercises = $pdo->query("
    SELECT e.*, c.title as course_title 
    FROM exercises e
    JOIN courses c ON e.course_id = c.id
    ORDER BY e.created_at DESC
")->fetchAll();

$pageTitle = "Quản lý Bài tập";
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
            <h1>✍️ Quản lý Bài tập</h1>
            <button class="btn btn-primary" onclick="alert('Thêm bài tập')">+ Thêm bài tập</button>
        </header>
        
        <div class="admin-content">
            <div class="admin-card">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tiêu đề</th>
                            <th>Khóa học</th>
                            <th>Loại</th>
                            <th>Điểm</th>
                            <th>Ngày tạo</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($exercises as $exercise): ?>
                        <tr>
                            <td><?= $exercise['id'] ?></td>
                            <td><?= htmlspecialchars($exercise['title']) ?></td>
                            <td><?= htmlspecialchars($exercise['course_title']) ?></td>
                            <td><span class="badge badge-<?= strtolower($exercise['type']) ?>"><?= $exercise['type'] ?></span></td>
                            <td><?= $exercise['points'] ?></td>
                            <td><?= date('d/m/Y', strtotime($exercise['created_at'])) ?></td>
                            <td>
                                <button class="btn btn-sm btn-outline">Sửa</button>
                                <button class="btn btn-sm btn-danger">Xóa</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
