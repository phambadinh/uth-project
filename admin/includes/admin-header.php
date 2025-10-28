<?php
require_once '../config/config.php';
require_once '../config/constants.php';

// Check admin authentication
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

if (!isset($pageTitle)) $pageTitle = "Admin Dashboard";
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - UTH Admin</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= ASSETS_URL ?>/images/logo.png">
    
    <!-- CSS -->
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/main.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/responsive.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body class="admin-body">

<div class="admin-layout">
    <!-- Sidebar -->
    <?php include __DIR__ . '/admin-sidebar.php'; ?>
    
    <!-- Main Content -->
    <main class="admin-main">
        <!-- Topbar -->
        <div class="admin-topbar">
            <button class="btn-toggle-sidebar" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            
            <div class="admin-topbar-right">
                <div class="admin-notifications">
                    <button class="btn-icon">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge">3</span>
                    </button>
                </div>
                
                <div class="admin-user-menu">
                    <img src="<?= ASSETS_URL ?>/images/avatars/default.png" alt="Admin" class="admin-avatar">
                    <span><?= htmlspecialchars($_SESSION['fullname']) ?></span>
                    <i class="fas fa-chevron-down"></i>
                    <div class="dropdown-menu">
                        <a href="../student/profile.php"><i class="fas fa-user"></i> Hồ sơ</a>
                        <a href="settings.php"><i class="fas fa-cog"></i> Cài đặt</a>
                        <div class="dropdown-divider"></div>
                        <a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Content Area -->
        <div class="admin-content">
