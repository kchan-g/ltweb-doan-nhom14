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
 * @throws PDOException Nếu không thể kết nối tới máy chủ MySQL
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

        $pdo = new PDO($dsn, $user, $pass, $options);
    }

    return $pdo;
}
