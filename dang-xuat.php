<?php
/**
 * dang-xuat.php
 * Hủy toàn bộ phiên làm việc (Session) của người dùng và chuyển hướng an toàn về trang chủ.
 */

declare(strict_types=1);

require_once __DIR__ . '/inc/config.php';

// Xóa sạch toàn bộ biến phiên
$_SESSION = [];

// Xóa cookie phiên PHPSESSID trên trình duyệt
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// Hủy phiên làm việc hoàn toàn
session_destroy();

header('Location: index.php');
exit;
