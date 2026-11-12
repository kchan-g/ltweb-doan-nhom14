<?php
/**
 * dang-xuat.php
 * Xử lý đăng xuất an toàn: CHỈ CHẤP NHẬN PHƯƠNG THỨC POST có kèm mã CSRF hợp lệ.
 * Nếu truy cập bằng GET (ví dụ người dùng gõ URL lên thanh địa chỉ): Trả về mã HTTP 405 Method Not Allowed.
 */

declare(strict_types=1);

require __DIR__ . '/inc/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    echo '<!DOCTYPE html><html lang="vi"><head><meta charset="utf-8"><title>405 Method Not Allowed</title></head><body>';
    echo '<h1>405 Method Not Allowed</h1>';
    echo '<p>Thao tác đăng xuất chỉ được phép thực hiện thông qua phương thức POST để bảo vệ phiên làm việc.</p>';
    echo '<p><a href="index.php">Quay lại trang chủ</a></p>';
    echo '</body></html>';
    exit;
}

dangXuat();
header('Location: index.php');
exit;
