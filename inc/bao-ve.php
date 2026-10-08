<?php
// Middleware bảo vệ trang quản trị
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Chưa đăng nhập -> lưu URL hiện tại để chuyển tiếp sau khi đăng nhập thành công
if (empty($_SESSION['nguoi_dung'])) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header('Location: dang-nhap.php');
    exit;
}

// 2. Đã đăng nhập nhưng không phải admin -> từ chối quyền truy cập (HTTP 403)
if (($_SESSION['nguoi_dung']['vai_tro'] ?? '') !== 'admin') {
    http_response_code(403);
    $tieuDe = '403 Forbidden - Từ chối truy cập';
    $trang  = '403';
    if (file_exists(__DIR__ . '/../403.php')) {
        require_once __DIR__ . '/../403.php';
    } else {
        echo '<div style="font-family:sans-serif; text-align:center; padding:50px;">';
        echo '<h1 style="color:#b91c1c;">403 Forbidden</h1>';
        echo '<p>Khu vực này chỉ dành riêng cho Quản trị viên hệ thống UniEvent.</p>';
        echo '<p><a href="index.php">&larr; Quay lại trang chủ</a></p>';
        echo '</div>';
    }
    exit;
}