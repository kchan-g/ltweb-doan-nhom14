<?php
/**
 * api/san-pham.php
 * REST API Endpoint phục vụ tra cứu và tìm kiếm thông tin sự kiện (su_kien):
 * - GET ?q=... : Tìm kiếm sự kiện theo từ khóa (trả JSON 200 kèm danh sách công khai).
 * - GET ?id=...: Lấy thông tin chi tiết một sự kiện (trả JSON 200 hoặc 404 nếu không tìm thấy).
 * - Phương thức khác GET -> Trả về mã 405 Method Not Allowed kèm header Allow: GET.
 * - Triển khai JsonSerializable trên SuKien, ẩn hoàn toàn các trường dữ liệu nội bộ.
 * - Bắt ngoại lệ trả về JSON {"loi": "Lỗi máy chủ"} với mã HTTP 500 khi CSDL gặp sự cố.
 */

declare(strict_types=1);

require_once __DIR__ . '/../inc/config.php';

use App\Data\KhoSuKien;

// Đăng ký lại Exception Handler dành riêng cho API trả về JSON 500 (slide 50)
set_exception_handler(function (Throwable $e): void {
    error_log(sprintf('[API Error san-pham.php] %s at %s:%d', $e->getMessage(), $e->getFile(), $e->getLine()));
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['loi' => 'Lỗi máy chủ'], JSON_UNESCAPED_UNICODE);
    exit;
});

/**
 * Trả về phản hồi JSON chuẩn REST.
 */
function traJson(mixed $duLieu, int $ma = 200, array $headers = []): void
{
    http_response_code($ma);
    header('Content-Type: application/json; charset=utf-8');
    foreach ($headers as $k => $v) {
        header("{$k}: {$v}");
    }
    echo json_encode($duLieu, JSON_UNESCAPED_UNICODE);
    exit;
}

// 1. Kiểm tra phương thức HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    traJson(['loi' => 'Phương thức HTTP không được hỗ trợ.'], 405, ['Allow' => 'GET']);
}

$kho = new KhoSuKien(db());

// 2. Tra cứu chi tiết một sự kiện theo ID (?id=...)
if (isset($_GET['id'])) {
    $idRaw = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if ($idRaw === false || $idRaw <= 0) {
        traJson(['loi' => 'Mã sự kiện không hợp lệ.'], 400);
    }

    $sk = $kho->timTheoId($idRaw);
    if ($sk === null) {
        traJson(['loi' => 'Không tìm thấy sự kiện yêu cầu.'], 404);
    }

    traJson(['du_lieu' => $sk], 200);
}

// 3. Tìm kiếm sự kiện theo từ khóa (?q=...)
$q = trim((string)($_GET['q'] ?? ''));
$danhSach = $kho->timNhanh($q, 20);

traJson([
    'du_lieu' => $danhSach,
    'tong'    => count($danhSach),
], 200);
