<?php
/**
 * inc/config.php
 * Tệp cấu hình trung tâm nạp đầu tiên ở mọi trang trên website UniEvent (Bản Chương 7 - Bảo mật).
 * Nạp biến môi trường .env (phpdotenv), thiết lập cờ DEBUG, bảo mật phiên,
 * cookie an toàn, Content Security Policy, kiểm tra mã CSRF tập trung và xử lý ngoại lệ.
 */

declare(strict_types=1);

// 1. Nạp Autoload của Composer
require_once __DIR__ . '/../vendor/autoload.php';

// 2. Nạp và kiểm tra tệp cấu hình bảo mật .env
$env = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$env->safeLoad();
$env->required(['DB_HOST', 'DB_DATABASE', 'DB_USERNAME']);

// Hằng số DEBUG đọc từ APP_DEBUG trong .env (mặc định tắt nếu thiếu)
if (!defined('DEBUG')) {
    define('DEBUG', ($_ENV['APP_DEBUG'] ?? '') === 'true');
}

// 3. Cấu hình báo lỗi và nhật ký lỗi
error_reporting(E_ALL);
ini_set('display_errors', DEBUG ? '1' : '0');
ini_set('display_startup_errors', DEBUG ? '1' : '0');
ini_set('log_errors', '1');

$thuMucLog = __DIR__ . '/../logs';
if (!is_dir($thuMucLog)) {
    @mkdir($thuMucLog, 0755, true);
}
ini_set('error_log', $thuMucLog . '/php-error.log');

// 4. Nạp các thư viện dùng chung
require_once __DIR__ . '/ham.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/xac-thuc.php';
require_once __DIR__ . '/csrf.php';

// 5. Header bảo mật gửi kèm mọi phản hồi HTTP
header("Content-Security-Policy: default-src 'self'; "
     . "connect-src 'self' https://api.open-meteo.com; "
     . "img-src 'self' data: https://img.vietqr.io https://images.unsplash.com; "
     . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; "
     . "font-src 'self' https://fonts.gstatic.com; "
     . "object-src 'none'; base-uri 'self'; "
     . "form-action 'self'; frame-ancestors 'none'");
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header_remove('X-Powered-By');

if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    header('Strict-Transport-Security: max-age=31536000');
}

// 6. Cấu hình Cookie phiên an toàn — PHẢI đặt TRƯỚC session_start()
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1'); // Từ chối ID phiên lạ do client tự tạo
    ini_set('session.use_only_cookies', '1');

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,                  // JavaScript không thể truy cập qua document.cookie
        'samesite' => 'Lax',                 // Ngăn chặn rò rỉ cookie trong request Cross-Site
    ]);

    session_start();
}

// Kiểm tra hết hạn phiên sau 30 phút không hoạt động
if (time() - ($_SESSION['hoat_dong'] ?? time()) > 30 * 60) {
    $_SESSION = [];
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
}
$_SESSION['hoat_dong'] = time();

// 7. Middleware kiểm tra mã CSRF tập trung: Mọi request POST (form hoặc fetch) bắt buộc phải có mã CSRF hợp lệ
$phuongThuc = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($phuongThuc === 'POST' && !csrfHopLe()) {
    ghiLogBaoMat('csrf_sai', ['trang' => $_SERVER['REQUEST_URI'] ?? '']);
    $loi = 'Trang đã hết hạn, hãy tải lại trang rồi gửi lại';

    $laApi = (str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'json'))
          || (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json'))
          || (str_contains($_SERVER['REQUEST_URI'] ?? '', '/api/'));

    if ($laApi) {
        traJson(['loi' => $loi], 403);
    }

    http_response_code(403);
    exit($loi);
}

// 8. Xử lý ngoại lệ toàn cục an toàn
set_exception_handler(function (Throwable $e): void {
    error_log((string) $e);
    http_response_code(500);

    $laApi = (str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'json'))
          || (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json'))
          || (str_contains($_SERVER['REQUEST_URI'] ?? '', '/api/'));

    if ($laApi) {
        traJson([
            'thanh_cong' => false,
            'loi'        => 'Đã xảy ra sự cố máy chủ nội bộ.',
            'ma_loi'     => 500,
        ], 500);
    }

    if (defined('DEBUG') && DEBUG) {
        echo '<pre>' . e((string)$e) . '</pre>';
    } else {
        if (file_exists(__DIR__ . '/../500.php')) {
            require __DIR__ . '/../500.php';
        } else {
            echo "<h1>500 - Lỗi máy chủ nội bộ</h1><p>Đã xảy ra sự cố. Vui lòng quay lại sau.</p>";
        }
    }
    exit;
});
