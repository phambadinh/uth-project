<!-- Footer -->
<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <!-- Column 1: About -->
            <div class="footer-column">
                <h3>UTH Learning</h3>
                <p>Nền tảng học lập trình trực tuyến hàng đầu với hơn 1,000 khóa học chất lượng từ các chuyên gia.</p>
                <div class="social-links">
                    <a href="#" aria-label="Facebook"><i class="fab fa-facebook"></i></a>
                    <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                    <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                </div>
            </div>

            <!-- Column 2: Quick Links -->
            <div class="footer-column">
                <h4>Khóa học</h4>
                <ul>
                    <?php foreach (array_slice(CATEGORIES, 0, 4) as $slug => $name): ?>
                    <li><a href="<?= BASE_URL ?>/pages/courses.php?category=<?= $slug ?>"><?= $slug ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Column 3: Support -->
            <div class="footer-column">
                <h4>Hỗ trợ</h4>
                <ul>
                    <li><a href="<?= BASE_URL ?>/pages/about.php">Giới thiệu</a></li>
                    <li><a href="<?= BASE_URL ?>/pages/contact.php">Liên hệ</a></li>
                    <li><a href="#">Điều khoản sử dụng</a></li>
                    <li><a href="#">Chính sách bảo mật</a></li>
                </ul>
            </div>

            <!-- Column 4: Contact -->
            <div class="footer-column">
                <h4>Liên hệ</h4>
                <ul class="contact-info">
                    <li><i class="fas fa-map-marker-alt"></i> TP. Hồ Chí Minh, Việt Nam</li>
                    <li><i class="fas fa-phone"></i> <?= SITE_PHONE ?></li>
                    <li><i class="fas fa-envelope"></i> <?= SITE_EMAIL ?></li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> UTH Learning System. All rights reserved.</p>
            <p>Made with <i class="fas fa-heart" style="color: #e74c3c;"></i> by UTH Development Team</p>
        </div>
    </div>
</footer>

<!-- JavaScript -->
<script src="<?= ASSETS_URL ?>/js/main.js"></script>

</body>
</html>
