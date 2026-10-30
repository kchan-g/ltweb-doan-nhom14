<?php
/**
 * xoa-san-pham.php
 * Xử lý xóa sự kiện trong trang quản trị:
 * - CHỈ CHẤP NHẬN PHƯƠNG THỨC POST: Nếu truy cập bằng GET -> Trả về mã HTTP 405 Method Not Allowed (Bảng 3 Ca 5).
 * - Yêu cầu đặc quyền yeuCauQuyen('quan_tri').
 * - Bắt lỗi vi phạm ràng buộc khóa ngoại MySQL (Mã lỗi 1451 - Cannot delete or update a parent row: a foreign key constraint fails).
 *   Nếu sự kiện đã có trong bảng chi_tiet_don, máy chủ báo lỗi thân thiện thay vì làm sập trang.
 * - Áp dụng mô hình Post/Redirect/Get (PRG) và thông báo Flash.
 */

declare(strict_types=1);

require_once __DIR__ . '/inc/config.php';

use App\Data\KhoSuKien;

// 1. Kiểm tra phương thức: Chỉ nhận POST, chặn GET trả 405 kèm header Allow
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    echo '<!DOCTYPE html><html lang="vi"><head><meta charset="utf-8"><title>405 Method Not Allowed</title></head><body>';
    echo '<h1>405 Method Not Allowed</h1>';
    echo '<p>Thao tác xóa sự kiện chỉ được phép thực hiện thông qua phương thức POST để bảo vệ dữ liệu.</p>';
    echo '<p><a href="quan-tri.php">Quay lại trang quản trị</a></p>';
    echo '</body></html>';
    exit;
}

// 2. Yêu cầu quyền quản trị viên
yeuCauQuyen('quan_tri');

$kho = new KhoSuKien(db());

$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
if (!$id || $id <= 0) {
    flash('Mã sự kiện cần xóa không hợp lệ.');
    header('Location: quan-tri.php');
    exit;
}

// Lấy thông tin tên sự kiện trước khi xóa để báo flash rõ ràng
$sk = $kho->timTheoId($id);
$tenSK = $sk ? $sk->ten : "Sự kiện #{$id}";

try {
    // 3. Thực hiện xóa sự kiện
    $kho->xoa($id);
    flash("🗑️ Đã xóa thành công sự kiện \"{$tenSK}\" (Mã #{$id}) khỏi hệ thống!");
} catch (PDOException $e) {
    // 4. Bắt lỗi ràng buộc toàn vẹn khóa ngoại MySQL (Lỗi 1451 - Bảng 3 Ca 5)
    if ($e->errorInfo[1] === 1451 || (int)$e->getCode() === 23000) {
        flash("⚠️ Không thể xóa sự kiện \"{$tenSK}\" vì sự kiện này đã được lưu trong các đơn đặt vé của sinh viên (Ràng buộc khóa ngoại MySQL lỗi 1451).");
    } else {
        error_log('Lỗi xóa sự kiện: ' . $e->getMessage());
        flash("Không thể xóa sự kiện do lỗi hệ thống: " . $e->getMessage());
    }
}

// 5. Chuyển hướng Post/Redirect/Get về trang quản trị
header('Location: quan-tri.php');
exit;
