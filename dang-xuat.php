<?php
/**
 * dang-xuat.php
 * Hủy toàn bộ phiên làm việc (Session) của người dùng và chuyển hướng về trang đăng nhập.
 */

declare(strict_types=1);

require_once __DIR__ . '/inc/config.php';

// Xóa toàn bộ biến phiên
$_SESSION = [];

// Xóa cookie phiên nếu có
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
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Đang đăng xuất - UniEvent</title>
  <script>
    try {
      localStorage.removeItem('unievent_ued_sinhvien');
    } catch(e) {}
    window.location.href = 'index.php';
  </script>
</head>
<body>
  <p style="font-family:sans-serif; text-align:center; padding:50px;">
    Đã đăng xuất tài khoản thành công. Đang chuyển về trang chủ... 
    <a href="index.php">Nhấn vào đây nếu không tự chuyển</a>.
  </p>
</body>
</html>
