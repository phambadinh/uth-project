<aside class="admin-sidebar" id="adminSidebar">
    <div class="admin-brand">
        <a href="<?= BASE_URL ?>">
            <img src="<?= ASSETS_URL ?>/images/logo.png" alt="UTH Learning" height="32">
            <span>UTH Admin</span>
        </a>
    </div>
    
    <nav class="admin-nav">
        <a href="dashboard.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
            <i class="fas fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
        
        <div class="nav-section-title">QUẢN LÝ NỘI DUNG</div>
        
        <a href="users.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : '' ?>">
            <i class="fas fa-users"></i>
            <span>Người dùng</span>
        </a>
        
        <a href="courses.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'courses.php' || basename($_SERVER['PHP_SELF']) == 'course-create.php' || basename($_SERVER['PHP_SELF']) == 'course-edit.php' ? 'active' : '' ?>">
            <i class="fas fa-book"></i>
            <span>Khóa học</span>
        </a>
        
        <a href="lessons.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'lessons.php' ? 'active' : '' ?>">
            <i class="fas fa-video"></i>
            <span>Bài học</span>
        </a>
        
        <a href="quizzes.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'quizzes.php' ? 'active' : '' ?>">
            <i class="fas fa-question-circle"></i>
            <span>Quiz</span>
        </a>
        
        <div class="nav-section-title">THANH TOÁN & HỌC VIÊN</div>
        
        <a href="enrollments.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'enrollments.php' ? 'active' : '' ?>">
            <i class="fas fa-user-graduate"></i>
            <span>Đăng ký khóa học</span>
        </a>
        
        <a href="payments.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'payments.php' ? 'active' : '' ?>">
            <i class="fas fa-credit-card"></i>
            <span>Thanh toán</span>
        </a>
        
        <a href="certificates.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'certificates.php' ? 'active' : '' ?>">
            <i class="fas fa-certificate"></i>
            <span>Chứng chỉ</span>
        </a>
        
        <div class="nav-section-title">HỆ THỐNG</div>
        
        <a href="settings.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : '' ?>">
            <i class="fas fa-cog"></i>
            <span>Cài đặt</span>
        </a>
    </nav>
    
    <div class="admin-sidebar-footer">
        <a href="<?= BASE_URL ?>" class="btn-outline-white btn-sm">
            <i class="fas fa-home"></i> Về trang chủ
        </a>
    </div>
</aside>
