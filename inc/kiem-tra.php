<?php
/**
 * inc/kiem-tra.php
 * Hàm kiểm tra và chuẩn hóa dữ liệu sự kiện dùng chung cho cả trang thêm và trang sửa.
 * Đảm bảo dữ liệu an toàn trước khi thực hiện câu lệnh INSERT hoặc UPDATE vào CSDL.
 */

declare(strict_types=1);

/**
 * Kiểm tra dữ liệu đầu vào của biểu mẫu sự kiện.
 *
 * @param array $vao Mảng dữ liệu từ $_POST
 * @param array $danhSachIdDanhMuc Mảng danh sách các ID danh mục hợp lệ trong CSDL
 * @return array{0: array<string, mixed>, 1: array<string, string>} Mảng gồm [$du, $loi]
 */
function kiemTraSanPham(array $vao, array $danhSachIdDanhMuc): array
{
    $du = [
        'ten'           => trim((string)($vao['ten'] ?? '')),
        'danh_muc_id'   => filter_var($vao['danh_muc_id'] ?? '', FILTER_VALIDATE_INT),
        'gia'           => filter_var($vao['gia'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]),
        'ton_kho'       => filter_var($vao['ton_kho'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 99999]]),
        'dia_diem'      => trim((string)($vao['dia_diem'] ?? '')),
        'thoi_gian'     => trim((string)($vao['thoi_gian'] ?? '')),
        'diem_ren_luyen'=> filter_var($vao['diem_ren_luyen'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 30]]),
        'dien_gia'      => trim((string)($vao['dien_gia'] ?? '')),
        'mo_ta_ngan'    => trim((string)($vao['mo_ta_ngan'] ?? '')),
        'mo_ta_chi_tiet'=> trim((string)($vao['mo_ta_chi_tiet'] ?? '')),
        'video'         => trim((string)($vao['video'] ?? '')),
    ];

    $loi = [];

    $doDaiTen = mb_strlen($du['ten']);
    if ($doDaiTen < 2 || $doDaiTen > 200) {
        $loi['ten'] = 'Tên sự kiện phải có độ dài từ 2 đến 200 ký tự.';
    }

    if ($du['danh_muc_id'] === false || !in_array($du['danh_muc_id'], $danhSachIdDanhMuc, true)) {
        $loi['danh_muc_id'] = 'Vui lòng chọn một danh mục sự kiện hợp lệ.';
    }

    if ($du['gia'] === false) {
        $loi['gia'] = 'Giá vé phải là số nguyên không âm (>= 0 đ).';
    }

    if ($du['ton_kho'] === false) {
        $loi['ton_kho'] = 'Số lượng vé tồn kho phải từ 0 đến 99.999.';
    }

    if ($du['dia_diem'] === '' || mb_strlen($du['dia_diem']) > 255) {
        $loi['dia_diem'] = 'Địa điểm tổ chức không được để trống và tối đa 255 ký tự.';
    }

    if ($du['thoi_gian'] === '') {
        $loi['thoi_gian'] = 'Vui lòng chọn thời gian diễn ra sự kiện.';
    }

    if ($du['diem_ren_luyen'] === false) {
        $loi['diem_ren_luyen'] = 'Điểm rèn luyện phải từ 0 đến 30.';
    }

    if (mb_strlen($du['mo_ta_ngan']) > 500) {
        $loi['mo_ta_ngan'] = 'Mô tả ngắn không được vượt quá 500 ký tự.';
    }

    if (mb_strlen($du['mo_ta_chi_tiet']) > 50000) {
        $loi['mo_ta_chi_tiet'] = 'Mô tả chi tiết quá dài.';
    }

    return [$du, $loi];
}
