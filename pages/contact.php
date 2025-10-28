<?php
require_once '../config/config.php';
require_once '../config/constants.php';

$pageTitle = "Liên hệ - UTH Learning";

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $content = trim($_POST['message']);
    
    if (empty($name) || empty($email) || empty($subject) || empty($content)) {
        $message = 'Vui lòng điền đầy đủ thông tin';
        $messageType = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Email không hợp lệ';
        $messageType = 'error';
    } else {
        // Save to database
        $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, subject, message, created_at) VALUES (?, ?, ?, ?, NOW())");
        
        if ($stmt->execute([$name, $email, $subject, $content])) {
            $message = 'Cảm ơn bạn đã liên hệ! Chúng tôi sẽ phản hồi trong vòng 24h.';
            $messageType = 'success';
            
            // Clear form
            $_POST = [];
        } else {
            $message = 'Có lỗi xảy ra. Vui lòng thử lại.';
            $messageType = 'error';
        }
    }
}

include '../includes/header.php';
?>

<div class="contact-page">
    <section class="contact-hero">
        <div class="container">
            <h1>Liên hệ với chúng tôi</h1>
            <p>Chúng tôi luôn sẵn sàng lắng nghe và hỗ trợ bạn</p>
        </div>
    </section>

    <div class="container">
        <div class="contact-grid">
            <!-- Contact Form -->
            <div class="contact-form-section">
                <h2>Gửi tin nhắn</h2>
                
                <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?>">
                    <?= $message ?>
                </div>
                <?php endif; ?>

                <form method="POST" class="contact-form">
                    <div class="form-group">
                        <label for="name">Họ và tên *</label>
                        <input type="text" id="name" name="name" class="form-control" 
                               value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" class="form-control" 
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="subject">Chủ đề *</label>
                        <select id="subject" name="subject" class="form-control" required>
                            <option value="">-- Chọn chủ đề --</option>
                            <option value="Hỗ trợ kỹ thuật">Hỗ trợ kỹ thuật</option>
                            <option value="Khóa học">Thắc mắc về khóa học</option>
                            <option value="Thanh toán">Vấn đề thanh toán</option>
                            <option value="Hợp tác">Đề xuất hợp tác</option>
                            <option value="Khác">Khác</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="message">Nội dung *</label>
                        <textarea id="message" name="message" class="form-control" rows="6" required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                    </div>

                    <button type="submit" class="btn-primary-large full-width">
                        <i class="fas fa-paper-plane"></i> Gửi tin nhắn
                    </button>
                </form>
            </div>

            <!-- Contact Info -->
            <div class="contact-info-section">
                <h2>Thông tin liên hệ</h2>

                <div class="contact-info-card">
                    <i class="fas fa-map-marker-alt"></i>
                    <div>
                        <h4>Địa chỉ</h4>
                        <p>Trường Đại học Giao thông Vận tải TP.HCM<br>
                        Số 2, Đ. Võ Oanh, P.25, Bình Thạnh, TP. Hồ Chí Minh</p>
                    </div>
                </div>

                <div class="contact-info-card">
                    <i class="fas fa-phone"></i>
                    <div>
                        <h4>Điện thoại</h4>
                        <p><?= SITE_PHONE ?></p>
                        <p>Hotline: 1900 1234</p>
                    </div>
                </div>

                <div class="contact-info-card">
                    <i class="fas fa-envelope"></i>
                    <div>
                        <h4>Email</h4>
                        <p><?= SITE_EMAIL ?></p>
                        <p>support@uth.edu.vn</p>
                    </div>
                </div>

                <div class="contact-info-card">
                    <i class="fas fa-clock"></i>
                    <div>
                        <h4>Giờ làm việc</h4>
                        <p>Thứ 2 - Thứ 6: 8:00 - 17:00</p>
                        <p>Thứ 7: 8:00 - 12:00</p>
                    </div>
                </div>

                <div class="social-contact">
                    <h4>Theo dõi chúng tôi</h4>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Map -->
        <div class="map-section">
            <h2>Vị trí</h2>
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.0633644278305!2d106.71292931533407!3d10.812274061463258!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x317528b2747a81a3%3A0x33c1813055acb613!2zVHLGsOG7nW5nIMSQ4bqhaSBo4buNYyBHaWFvIHRow7RuZyBW4bqtbiB04bqjaSBUUC5IQ00!5e0!3m2!1svi!2s!4v1234567890123!5m2!1svi!2s" 
                    width="100%" 
                    height="450" 
                    style="border:0; border-radius: 12px;" 
                    allowfullscreen="" 
                    loading="lazy">
            </iframe>
        </div>
    </div>
</div>

<style>
.contact-hero { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; padding: 80px 0; text-align: center; margin-bottom: 60px; }
.contact-hero h1 { font-size: 48px; margin-bottom: 16px; }
.contact-grid { display: grid; grid-template-columns: 1.5fr 1fr; gap: 60px; margin-bottom: 60px; }
.contact-form-section, .contact-info-section { background: #fff; padding: 40px; border-radius: 12px; box-shadow: 0 4px 16px rgba(0,0,0,0.08); }
.contact-form-section h2, .contact-info-section h2 { margin-bottom: 32px; }
.contact-info-card { display: flex; gap: 20px; margin-bottom: 32px; }
.contact-info-card i { font-size: 32px; color: #0056d2; margin-top: 4px; }
.contact-info-card h4 { margin-bottom: 8px; }
.contact-info-card p { color: #545454; margin-bottom: 4px; }
.social-contact { text-align: center; margin-top: 40px; padding-top: 32px; border-top: 1px solid #e5e5e5; }
.social-contact h4 { margin-bottom: 20px; }
.map-section { margin-top: 60px; }
.map-section h2 { margin-bottom: 24px; }
@media (max-width: 768px) {
    .contact-grid { grid-template-columns: 1fr; gap: 32px; }
}
</style>

<?php include '../includes/footer.php'; ?>
