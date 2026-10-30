<?php
/**
 * api/gio-hang.php
 * REST API Endpoint phục vụ xem giỏ vé và thêm vé vào giỏ bằng AJAX / fetch:
 * - GET : Trả về thông tin giỏ vé hiện tại (mã 200 {"so_mon": N, "cac_mon": [...]})
 * - POST: Thêm vé vào giỏ qua thân JSON dạng {"id": ..., "sl": ...}
 *   + Mã 400: Thân yêu cầu không phải JSON hợp lệ.
 *   + Mã 422: Số lượng âm / không hợp lệ hoặc vượt quá tồn kho.
 *   + Mã 404: Sự kiện không tồn tại.
 *   + Mã 405: Phương thức khác GET/POST (kèm header Allow: GET, POST).
 *   + Mã 500: Lỗi máy chủ CSDL.
 */

declare(strict_types=1);

require_once __DIR__ . '/../inc/config.php';

use App\Data\KhoSuKien;
use App\Services\GioHang;

// Đăng ký lại Exception Handler dành riêng cho API trả về JSON 500
set_exception_handler(function (Throwable $e): void {
    error_log(sprintf('[API Error gio-hang.php] %s at %s:%d', $e->getMessage(), $e->getFile(), $e->getLine()));
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

$phuongThuc = $_SERVER['REQUEST_METHOD'];

// Kiểm tra phương thức cho phép (chỉ GET và POST)
if (!in_array($phuongThuc, ['GET', 'POST'], true)) {
    traJson(['loi' => 'Phương thức HTTP không được hỗ trợ.'], 405, ['Allow' => 'GET, POST']);
}

$kho = new KhoSuKien(db());
$gio = new GioHang();

// ============================================================================
// 1. GET: XEM THÔNG TIN GIỎ VÉ
// ============================================================================
if ($phuongThuc === 'GET') {
    $chiTiet = $gio->chiTiet($kho);
    $danhSach = [];
    foreach ($chiTiet as $item) {
        $sk = $item['suKien'];
        $danhSach[] = [
            'id'         => $sk->id,
            'ten'        => $sk->ten,
            'gia'        => $sk->gia,
            'so_luong'   => $item['soLuong'],
            'thanh_tien' => $item['thanhTien'],
        ];
    }

    traJson([
        'so_mon'    => $gio->soMon(),
        'tong_tien' => $gio->tongTien($kho),
        'cac_mon'   => $danhSach,
    ], 200);
}

// ============================================================================
// 2. POST: THÊM VÉ VÀO GIỎ QUA JSON BODY
// ============================================================================
if ($phuongThuc === 'POST') {
    $rawInput = file_get_contents('php://input');

    // Kiểm tra định dạng JSON
    $body = json_decode($rawInput, true);
    if (!is_array($body)) {
        // Mã 400: Thân không phải JSON hợp lệ (Bảng 3 Ca 12)
        traJson(['loi' => 'Thân yêu cầu không phải là chuỗi JSON hợp lệ.'], 400);
    }

    // Kiểm tra id
    if (!isset($body['id'])) {
        traJson(['loi' => 'Thiếu mã sự kiện id.'], 400);
    }
    $id = filter_var($body['id'], FILTER_VALIDATE_INT);
    if ($id === false || $id <= 0) {
        traJson(['loi' => 'Mã sự kiện id không hợp lệ.'], 400);
    }

    // Kiểm tra sl (số lượng)
    if (!isset($body['sl'])) {
        $sl = 1;
    } else {
        $sl = filter_var($body['sl'], FILTER_VALIDATE_INT);
        // Mã 422: Số lượng âm hoặc bằng 0 (Bảng 3 Ca 12)
        if ($sl === false || $sl <= 0) {
            traJson(['loi' => 'Số lượng vé (sl) phải là số nguyên dương lớn hơn 0.'], 422);
        }
    }

    // Kiểm tra sự kiện tồn tại trong CSDL
    $sk = $kho->timTheoId($id);
    if ($sk === null) {
        // Mã 404: ID sự kiện không tồn tại (Bảng 3 Ca 12)
        traJson(['loi' => 'Sự kiện không tồn tại trong hệ thống.'], 404);
    }

    // Kiểm tra số lượng tồn kho khả dụng
    $dangCo = $_SESSION['gio'][$id] ?? 0;
    if ($dangCo + $sl > $sk->ton_kho) {
        // Mã 422: Vượt quá tồn kho (Bảng 3 Ca 12)
        traJson(['loi' => "Số lượng vé yêu cầu vượt quá tồn kho khả dụng (Còn lại: {$sk->ton_kho})."], 422);
    }

    // Thêm vào giỏ thành công
    $gio->them($id, $sl);

    traJson([
        'thanh_cong' => true,
        'so_mon'     => $gio->soMon(),
        'thong_bao'  => "Đã thêm vé sự kiện \"{$sk->ten}\" vào giỏ thành công.",
    ], 200);
}
