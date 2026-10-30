<?php
/**
 * inc/config.php
 * Tệp cấu hình trung tâm nạp đầu tiên ở mọi trang trên website UniEvent.
 * Quản lý Autoload của Composer, nạp kết nối PDO, thư viện xác thực, báo lỗi,
 * ghi log, khởi tạo phiên làm việc (Session) và thiết lập bộ bắt ngoại lệ.
 */

declare(strict_types=1);

// 1. Nạp Autoload của Composer (tự động nạp mọi lớp thuộc namespace App\)
require_once __DIR__ . '/../vendor/autoload.php';

// 2. Nạp thư viện hàm tiện ích dùng chung
require_once __DIR__ . '/ham.php';

// 3. Nạp kết nối CSDL MySQL qua PDO
require_once __DIR__ . '/db.php';

// 4. Nạp module xác thực và phân quyền người dùng
require_once __DIR__ . '/xac-thuc.php';

// 5. Hằng số môi trường: 'dev' khi phát triển cục bộ, 'prod' khi chạy thực tế
if (!defined('MOI_TRUONG')) {
    define('MOI_TRUONG', 'dev');
}

// 6. Cấu hình báo lỗi và ghi nhật ký lỗi (logs/php-error.log)
error_reporting(E_ALL);

ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/php-error.log');

if (MOI_TRUONG === 'dev') {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
}

// 7. Khởi tạo phiên làm việc (Session) an toàn nếu chưa kích hoạt
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    session_start();
}

// 8. Xử lý ngoại lệ toàn cục (set_exception_handler)
set_exception_handler(function (Throwable $ngoaiLe): void {
    // Luôn ghi log chi tiết lỗi vào tệp nhật ký
    error_log(sprintf(
        "[%s] Lỗi ngoại lệ: %s tại %s:%d\nStack trace:\n%s",
        date('Y-m-d H:i:s'),
        $ngoaiLe->getMessage(),
        $ngoaiLe->getFile(),
        $ngoaiLe->getLine(),
        $ngoaiLe->getTraceAsString()
    ));

    // Thiết lập mã HTTP 500
    http_response_code(500);

    if (MOI_TRUONG === 'prod') {
        // Môi trường sản xuất: Hiển thị trang lỗi 500 thân thiện, không làm lộ đường dẫn nội bộ
        if (file_exists(__DIR__ . '/../500.php')) {
            require __DIR__ . '/../500.php';
        } else {
            echo "<h1>500 - Lỗi máy chủ nội bộ</h1><p>Đã xảy ra sự cố. Vui lòng quay lại sau.</p>";
        }
        exit;
    }

    // Môi trường dev: Hiển thị lỗi rõ ràng để nhóm dễ gỡ lỗi
    echo "<div style='font-family:sans-serif; background:rgba(239,68,68,0.12); color:#ef4444; padding:20px; border-radius:8px; border:1px solid rgba(239,68,68,0.3); margin:20px;'>";
    echo "<h2 style='margin-top:0;'>⚠️ Ngoại lệ chưa bắt (Dev Mode)</h2>";
    echo "<p><strong>Thông điệp:</strong> " . e($ngoaiLe->getMessage()) . "</p>";
    echo "<p><strong>Tệp:</strong> " . e($ngoaiLe->getFile()) . " (Dòng " . $ngoaiLe->getLine() . ")</p>";
    echo "<pre style='background:var(--nen-surface, #ffffff); color:var(--chu-chinh, #333333); padding:12px; border-radius:4px; overflow-x:auto; border:1px solid var(--border-nhat, #e2e8f0);'>" . e($ngoaiLe->getTraceAsString()) . "</pre>";
    echo "</div>";
    exit;
});
