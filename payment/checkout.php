<?php
require_once '../config/config.php';
require_once '../config/constants.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$courseId = (int)($_GET['course_id'] ?? 0);

// Get course details
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->execute([$courseId]);
$course = $stmt->fetch();

if (!$course || $course['price'] <= 0) {
    header('Location: ../pages/courses.php');
    exit;
}

$pageTitle = "Thanh toán - " . htmlspecialchars($course['title']);

include '../includes/header.php';
?>

<div class="checkout-page">
    <div class="container">
        <h1>Thanh toán khóa học</h1>
        
        <div class="checkout-grid">
            <!-- Order Summary -->
            <div class="order-summary">
                <h2>Thông tin khóa học</h2>
                <div class="course-checkout-card">
                    <img src="<?= htmlspecialchars($course['thumbnail']) ?>" alt="<?= htmlspecialchars($course['title']) ?>">
                    <div class="course-checkout-info">
                        <h3><?= htmlspecialchars($course['title']) ?></h3>
                        <p class="course-category">
                            <span class="badge" style="background: <?= CATEGORY_COLORS[$course['category']] ?>">
                                <?= CATEGORY_ICONS[$course['category']] ?> <?= $course['category'] ?>
                            </span>
                        </p>
                        <div class="course-checkout-meta">
                            <span><i class="fas fa-signal"></i> <?= LEVELS[$course['level']] ?></span>
                            <span><i class="fas fa-clock"></i> <?= $course['duration'] ?></span>
                            <span><i class="fas fa-star"></i> <?= number_format($course['rating'], 1) ?></span>
                        </div>
                    </div>
                </div>

                <div class="order-details">
                    <div class="order-row">
                        <span>Giá khóa học:</span>
                        <span class="price"><?= number_format($course['price']) ?>đ</span>
                    </div>
                    <div class="order-row">
                        <span>Giảm giá:</span>
                        <span class="discount">0đ</span>
                    </div>
                    <div class="order-row total">
                        <span>Tổng cộng:</span>
                        <span class="price-total"><?= number_format($course['price']) ?>đ</span>
                    </div>
                </div>
            </div>

            <!-- Payment Methods -->
            <div class="payment-methods">
                <h2>Chọn phương thức thanh toán</h2>
                
                <form id="paymentForm" method="POST" action="process-payment.php">
                    <input type="hidden" name="course_id" value="<?= $courseId ?>">
                    <input type="hidden" name="amount" value="<?= $course['price'] ?>">
                    
                    <div class="payment-options">
                        <label class="payment-option">
                            <input type="radio" name="payment_method" value="vnpay" required>
                            <div class="payment-option-content">
                                <img src="<?= ASSETS_URL ?>/images/payments/vnpay.png" alt="VNPay" height="32">
                                <div>
                                    <h4>VNPay QR Code</h4>
                                    <p>Quét mã QR để thanh toán nhanh</p>
                                </div>
                            </div>
                        </label>

                        <label class="payment-option">
                            <input type="radio" name="payment_method" value="momo" required>
                            <div class="payment-option-content">
                                <img src="<?= ASSETS_URL ?>/images/payments/momo.png" alt="Momo" height="32">
                                <div>
                                    <h4>Ví MoMo</h4>
                                    <p>Thanh toán qua ví điện tử MoMo</p>
                                </div>
                            </div>
                        </label>

                        <label class="payment-option">
                            <input type="radio" name="payment_method" value="bank_transfer" required>
                            <div class="payment-option-content">
                                <i class="fas fa-university" style="font-size: 32px; color: #0056d2;"></i>
                                <div>
                                    <h4>Chuyển khoản ngân hàng</h4>
                                    <p>Chuyển khoản trực tiếp qua ngân hàng</p>
                                </div>
                            </div>
                        </label>
                    </div>

                    <div class="payment-info">
                        <div class="info-item">
                            <i class="fas fa-shield-alt"></i>
                            <span>Thanh toán an toàn và bảo mật</span>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-infinity"></i>
                            <span>Truy cập khóa học trọn đời</span>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-undo"></i>
                            <span>Hoàn tiền trong 30 ngày</span>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary-large full-width">
                        <i class="fas fa-lock"></i> Thanh toán an toàn
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.checkout-page { background: #f5f7fa; min-height: 100vh; padding: 60px 0; }
.checkout-page h1 { text-align: center; margin-bottom: 48px; }
.checkout-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; max-width: 1200px; margin: 0 auto; }
.order-summary, .payment-methods { background: #fff; padding: 32px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
.order-summary h2, .payment-methods h2 { font-size: 24px; margin-bottom: 24px; }
.course-checkout-card { display: flex; gap: 20px; padding: 20px; background: #f5f7fa; border-radius: 8px; margin-bottom: 24px; }
.course-checkout-card img { width: 140px; height: 105px; object-fit: cover; border-radius: 6px; }
.course-checkout-info h3 { font-size: 18px; margin-bottom: 12px; }
.course-checkout-meta { display: flex; gap: 16px; font-size: 14px; color: #545454; margin-top: 12px; }
.order-details { border-top: 1px solid #e5e5e5; padding-top: 20px; }
.order-row { display: flex; justify-content: space-between; margin-bottom: 16px; }
.order-row.total { font-size: 20px; font-weight: 700; padding-top: 16px; border-top: 2px solid #e5e5e5; margin-top: 16px; }
.price-total { color: #0056d2; }
.payment-options { display: flex; flex-direction: column; gap: 16px; margin-bottom: 32px; }
.payment-option { display: block; cursor: pointer; }
.payment-option input[type="radio"] { display: none; }
.payment-option-content { display: flex; align-items: center; gap: 16px; padding: 20px; border: 2px solid #e5e5e5; border-radius: 8px; transition: all 0.2s; }
.payment-option input[type="radio"]:checked + .payment-option-content { border-color: #0056d2; background: #e8f0fe; }
.payment-option-content h4 { margin-bottom: 4px; }
.payment-option-content p { font-size: 14px; color: #545454; }
.payment-info { display: flex; flex-direction: column; gap: 12px; margin-bottom: 24px; padding: 20px; background: #f5f7fa; border-radius: 8px; }
.info-item { display: flex; align-items: center; gap: 12px; color: #545454; }
.info-item i { color: #0cae74; }
@media (max-width: 768px) {
    .checkout-grid { grid-template-columns: 1fr; }
}
</style>

<?php include '../includes/footer.php'; ?>
