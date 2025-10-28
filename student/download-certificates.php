<?php
require_once '../config/config.php';

// Composer autoload (optional) for TCPDF
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}


// Ensure TCPDF class is available
if (!class_exists('TCPDF')) {
    die('TCPDF library is not installed. Run `composer require tecnickcom/tcpdf` or install TCPDF in vendor folder.');
}

// Check authentication
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../auth/login.php');
    exit;
}

$code = $_GET['code'] ?? '';
$userId = $_SESSION['user_id'];

// Get certificate
$stmt = $pdo->prepare("
    SELECT cert.*, c.title as course_title, c.category, u.fullname, u.email
    FROM certificates cert
    JOIN courses c ON cert.course_id = c.id
    JOIN users u ON cert.user_id = u.id
    WHERE cert.certificate_code = ? AND cert.user_id = ?
");
$stmt->execute([$code, $userId]);
$certificate = $stmt->fetch();

if (!$certificate) {
    die('Chứng chỉ không tồn tại');
}

// Create PDF (instantiate via variable class name to avoid static analyzer warnings)
$tcpdfClass = 'TCPDF';
$pdf = new $tcpdfClass('L', 'mm', 'A4', true, 'UTF-8', false);

// Set document information
$pdf->SetCreator('UTH Learning');
$pdf->SetAuthor('UTH Learning');
$pdf->SetTitle('Certificate - ' . $certificate['fullname']);
$pdf->SetSubject('Certificate of Completion');

// Remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Add a page
$pdf->AddPage();

// Set font
$pdf->SetFont('dejavusans', '', 12);

// Background color
$pdf->Rect(0, 0, 297, 210, 'F', array(), array(102, 126, 234));

// White content area
$pdf->RoundedRect(20, 20, 257, 170, 5, '1111', 'F', array(), array(255, 255, 255));

// Logo (if exists)
$logoPath = '../assets/images/logo.png';
if (file_exists($logoPath)) {
    $pdf->Image($logoPath, 125, 30, 40, 0, 'PNG');
}

// Title
$pdf->SetY(55);
$pdf->SetFont('dejavusans', 'B', 24);
$pdf->Cell(0, 10, 'CHỨNG CHỈ HOÀN THÀNH', 0, 1, 'C');

// Subtitle
$pdf->SetY(70);
$pdf->SetFont('dejavusans', '', 14);
$pdf->Cell(0, 8, 'Certificate of Completion', 0, 1, 'C');

// Separator line
$pdf->SetY(82);
$pdf->SetDrawColor(102, 126, 234);
$pdf->SetLineWidth(0.5);
$pdf->Line(80, 82, 217, 82);

// Text
$pdf->SetY(95);
$pdf->SetFont('dejavusans', '', 12);
$pdf->Cell(0, 8, 'Chứng nhận rằng', 0, 1, 'C');

// Student name
$pdf->SetY(105);
$pdf->SetFont('dejavusans', 'B', 20);
$pdf->SetTextColor(102, 126, 234);
$pdf->Cell(0, 10, strtoupper($certificate['fullname']), 0, 1, 'C');

// Reset color
$pdf->SetTextColor(0, 0, 0);

// Text
$pdf->SetY(118);
$pdf->SetFont('dejavusans', '', 12);
$pdf->Cell(0, 8, 'đã hoàn thành xuất sắc khóa học', 0, 1, 'C');

// Course title
$pdf->SetY(128);
$pdf->SetFont('dejavusans', 'B', 16);
$pdf->MultiCell(0, 8, $certificate['course_title'], 0, 'C');

// Footer info
$pdf->SetY(155);
$pdf->SetFont('dejavusans', '', 10);

// Date
$pdf->SetX(50);
$pdf->Cell(60, 6, 'Ngày cấp:', 0, 0, 'L');
$pdf->SetFont('dejavusans', 'B', 10);
$pdf->Cell(0, 6, date('d/m/Y', strtotime($certificate['issued_date'])), 0, 1);

// Certificate code
$pdf->SetFont('dejavusans', '', 10);
$pdf->SetX(50);
$pdf->Cell(60, 6, 'Mã chứng chỉ:', 0, 0, 'L');
$pdf->SetFont('dejavusans', 'B', 10);
$pdf->Cell(0, 6, $certificate['certificate_code'], 0, 1);

// QR Code (if QR code library available)
$qrUrl = 'https://uth-learning.com/verify?code=' . $certificate['certificate_code'];
$pdf->write2DBarcode($qrUrl, 'QRCODE,H', 240, 155, 30, 30);

// Signature line
$pdf->SetY(175);
$pdf->SetDrawColor(200, 200, 200);
$pdf->SetLineWidth(0.3);
$pdf->Line(60, 175, 110, 175);
$pdf->Line(187, 175, 237, 175);

$pdf->SetY(176);
$pdf->SetFont('dejavusans', 'I', 9);
$pdf->SetX(60);
$pdf->Cell(50, 5, 'Giảng viên', 0, 0, 'C');
$pdf->SetX(187);
$pdf->Cell(50, 5, 'Ban Giám đốc', 0, 1, 'C');

// Output PDF
$pdf->Output('Certificate_' . $certificate['certificate_code'] . '.pdf', 'D');
exit;
?>
