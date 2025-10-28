<?php
if (!isset($pageTitle)) $pageTitle = "UTH Learning System";
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= $pageDescription ?? 'Hệ thống học lập trình trực tuyến hàng đầu' ?>">
    <title><?= $pageTitle ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= ASSETS_URL ?>/images/logo.png">
    
    <!-- CSS -->
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/main.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/responsive.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
    <div class="container">
        <div class="navbar-brand">
            <a href="<?= BASE_URL ?>">
                <img src="<?= ASSETS_URL ?>/images/logo.png" alt="UTH Learning" height="40">
                <span class="brand-text">UTH Learning</span>
            </a>
        </div>

        <div class="navbar-menu">
            <ul class="navbar-nav">
                <li><a href="<?= BASE_URL ?>">Trang chủ</a></li>
                <li class="dropdown">
                    <a href="<?= BASE_URL ?>/pages/courses.php">
                        Khóa học <i class="fas fa-chevron-down"></i>
                    </a>
                    <div class="dropdown-menu">
                        <div class="mega-menu">
                            <div class="mega-menu-column">
                                <h4>Ngôn ngữ lập trình</h4>
                                <?php foreach (CATEGORIES as $slug => $name): ?>
                                <a href="<?= BASE_URL ?>/pages/courses.php?category=<?= $slug ?>">
                                    <?= CATEGORY_ICONS[$slug] ?> <?= $slug ?>
                                </a>
                                <?php endforeach; ?>
                            </div>
                            <div class="mega-menu-column">
                                <h4>Theo cấp độ</h4>
                                <?php foreach (LEVELS as $key => $value): ?>
                                <a href="<?= BASE_URL ?>/pages/courses.php?level=<?= $key ?>">
                                    <i class="fas fa-signal"></i> <?= $value ?>
                                </a>
                                <?php endforeach; ?>
                            </div>
                            <div class="mega-menu-column">
                                <h4>Khóa học miễn phí</h4>
                                <a href="<?= BASE_URL ?>/pages/courses.php?price=free">
                                    <i class="fas fa-gift"></i> Tất cả khóa học miễn phí
                                </a>
                                <a href="<?= BASE_URL ?>/pages/courses.php?sort=newest">
                                    <i class="fas fa-star"></i> Khóa học mới nhất
                                </a>
                                <a href="<?= BASE_URL ?>/pages/courses.php?sort=popular">
                                    <i class="fas fa-fire"></i> Khóa học phổ biến
                                </a>
                            </div>
                        </div>
                    </div>
                </li>
                <li><a href="<?= BASE_URL ?>/pages/about.php">Giới thiệu</a></li>
                <li><a href="<?= BASE_URL ?>/pages/contact.php">Liên hệ</a></li>
            </ul>
        </div>

        <div class="navbar-actions">
            <div class="search-box">
                <input type="text" placeholder="Tìm kiếm khóa học..." id="searchInput">
                <button><i class="fas fa-search"></i></button>
            </div>

            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="user-menu dropdown">
                    <button class="user-avatar">
                        <img src="<?= ASSETS_URL ?>/images/avatars/default.png" alt="Avatar">
                        <span><?= htmlspecialchars($_SESSION['fullname']) ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <a href="<?= BASE_URL ?>/admin/dashboard.php">
                                <i class="fas fa-tachometer-alt"></i> Admin Dashboard
                            </a>
                        <?php else: ?>
                            <a href="<?= BASE_URL ?>/student/dashboard.php">
                                <i class="fas fa-home"></i> Dashboard
                            </a>
                            <a href="<?= BASE_URL ?>/student/my-courses.php">
                                <i class="fas fa-book"></i> Khóa học của tôi
                            </a>
                            <a href="<?= BASE_URL ?>/student/certificates.php">
                                <i class="fas fa-certificate"></i> Chứng chỉ
                            </a>
                            <a href="<?= BASE_URL ?>/student/chat.php">
                                <i class="fas fa-comments"></i> Tin nhắn
                            </a>
                        <?php endif; ?>
                        <div class="dropdown-divider"></div>
                        <a href="<?= BASE_URL ?>/student/profile.php">
                            <i class="fas fa-user"></i> Tài khoản
                        </a>
                        <a href="<?= BASE_URL ?>/auth/logout.php">
                            <i class="fas fa-sign-out-alt"></i> Đăng xuất
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/auth/login.php" class="btn-outline">Đăng nhập</a>
                <a href="<?= BASE_URL ?>/auth/register.php" class="btn-primary">Đăng ký</a>
            <?php endif; ?>
        </div>

        <button class="navbar-toggle" onclick="toggleMobileMenu()">
            <i class="fas fa-bars"></i>
        </button>
    </div>
</nav>

<script>
function toggleMobileMenu() {
    document.querySelector('.navbar-menu').classList.toggle('active');
}

// Search functionality
document.getElementById('searchInput')?.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        const query = this.value.trim();
        if (query) {
            window.location.href = '<?= BASE_URL ?>/pages/courses.php?q=' + encodeURIComponent(query);
        }
    }
});
</script>
