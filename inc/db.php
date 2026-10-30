<?php
/**
 * inc/db.php
 * Khởi tạo và quản lý kết nối Cơ sở dữ liệu MySQL qua PDO cho toàn bộ website UniEvent.
 * Áp dụng mô hình Singleton để chỉ tạo duy nhất một kết nối PDO trong mỗi vòng đời request.
 *
 * CSDL: ltweb_nhom14 | Bảng mã: utf8mb4 | Collation: utf8mb4_unicode_ci
 */

declare(strict_types=1);

/**
 * Trả về đối tượng kết nối PDO dùng chung tới CSDL ltweb_nhom14.
 *
 * @return PDO Đối tượng kết nối PDO đã được cấu hình chuẩn
 */
function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $host = '127.0.0.1';
        $port = '3306';
        $dbname = 'ltweb_nhom14';
        $charset = 'utf8mb4';
        $user = 'root';
        $pass = '';

        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $pdo = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            // Ghi nhận lỗi vào error log của hệ thống để quản trị viên theo dõi
            error_log(sprintf(
                "[%s] Lỗi kết nối CSDL MySQL: %s",
                date('Y-m-d H:i:s'),
                $e->getMessage()
            ));

            http_response_code(500);

            // Kiểm tra nếu là request gọi API (JSON)
            $uri = $_SERVER['REQUEST_URI'] ?? '';
            $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
            $laApi = (strpos($uri, '/api/') !== false) || (strpos($accept, 'application/json') !== false);

            if ($laApi) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'thanh_cong' => false,
                    'loi'        => 'Không thể kết nối đến máy chủ cơ sở dữ liệu. Vui lòng kiểm tra dịch vụ MySQL.',
                    'ma_loi'     => 500,
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }

            // Giao diện web: Hiển thị trang lỗi 500 chuẩn mực, không làm lộ thông tin nhạy cảm
            if (file_exists(__DIR__ . '/../500.php')) {
                require __DIR__ . '/../500.php';
            } else {
                echo "<!DOCTYPE html><html lang='vi'><head><meta charset='utf-8'><title>500 - Sự cố máy chủ</title></head><body><h1>500 - Sự cố kết nối máy chủ</h1><p>Hệ thống tạm thời gián đoạn. Vui lòng thử lại sau.</p></body></html>";
            }
            exit;
        }
    }

    return $pdo;
}
