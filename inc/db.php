<?php
/**
 * inc/db.php
 * Khởi tạo và quản lý kết nối Cơ sở dữ liệu MySQL qua PDO cho website UniEvent.
 * Thông tin kết nối được nạp động từ biến môi trường $_ENV (.env), không viết cứng mật khẩu trong mã nguồn.
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
        $host   = $_ENV['DB_HOST'] ?? '127.0.0.1';
        $dbname = $_ENV['DB_DATABASE'] ?? 'ltweb_nhom14';
        $user   = $_ENV['DB_USERNAME'] ?? 'root';
        $pass   = $_ENV['DB_PASSWORD'] ?? '';

        $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            error_log(sprintf(
                "[%s] Lỗi kết nối CSDL MySQL: %s",
                date('Y-m-d H:i:s'),
                $e->getMessage()
            ));

            http_response_code(500);

            $laApi = (str_contains($_SERVER['REQUEST_URI'] ?? '', '/api/')) 
                  || (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json'))
                  || (str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'json'));

            if ($laApi) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'thanh_cong' => false,
                    'loi'        => 'Không thể kết nối đến máy chủ cơ sở dữ liệu. Vui lòng kiểm tra dịch vụ MySQL.',
                    'ma_loi'     => 500,
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }

            if (defined('DEBUG') && DEBUG) {
                echo "<h1>Lỗi kết nối CSDL MySQL</h1><p>" . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</p>";
            } else {
                if (file_exists(__DIR__ . '/../500.php')) {
                    require __DIR__ . '/../500.php';
                } else {
                    echo "<h1>500 - Lỗi máy chủ nội bộ</h1><p>Đã xảy ra sự cố kết nối. Vui lòng thử lại sau.</p>";
                }
            }
            exit;
        }
    }

    return $pdo;
}
