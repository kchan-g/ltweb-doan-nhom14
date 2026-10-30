<?php
/**
 * inc/don-hang.php
 * Module xử lý nghiệp vụ đặt vé sự kiện trong Giao dịch CSDL (Transaction).
 * Đảm bảo tính toàn vẹn dữ liệu (ACID): tự động trừ tồn kho an toàn, lưu chi tiết đơn
 * kèm đơn giá tại thời điểm đặt, rollback hoàn toàn nếu bất kỳ sự kiện nào không đủ tồn kho.
 */

declare(strict_types=1);

/**
 * Thực hiện đặt hàng/đăng ký vé trong giao dịch MySQL.
 *
 * @param PDO $db Kết nối CSDL
 * @param int $nguoiDungId Mã tài khoản người đặt
 * @param array<int, int> $gio Mảng giỏ vé dạng [$suKienId => $soLuong]
 * @param string|null $ghiChu Ghi chú thêm của người đặt
 * @return int Mã ID đơn hàng vừa tạo thành công
 * @throws RuntimeException Khi giỏ hàng rỗng hoặc không đủ tồn kho
 */
function luuDonHang(PDO $db, int $nguoiDungId, array $gio, ?string $ghiChu = null): int
{
    if (empty($gio)) {
        throw new RuntimeException('Giỏ đăng ký vé của bạn đang rỗng.');
    }

    $db->beginTransaction();

    try {
        // 1. Tạo mã đơn hàng duy nhất dạng: UE-YYYYMMDD-XXXX
        $maDon = 'UE-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));

        // 2. Chèn bản ghi ban đầu vào bảng don_hang
        $stDon = $db->prepare(
            'INSERT INTO don_hang (nguoi_dung_id, ma_don, ngay_dat, tong_tien, trang_thai, ghi_chu)
             VALUES (?, ?, NOW(), 0, "cho_xac_nhan", ?)'
        );
        $stDon->execute([$nguoiDungId, $maDon, $ghiChu]);
        $donHangId = (int)$db->lastInsertId();

        $tongTien = 0;

        // Chuẩn bị sẵn các câu lệnh SQL
        $stTruKho = $db->prepare(
            'UPDATE su_kien SET ton_kho = ton_kho - ? WHERE id = ? AND ton_kho >= ?'
        );
        $stLayGia = $db->prepare('SELECT ten, gia, ton_kho FROM su_kien WHERE id = ?');
        $stChiTiet = $db->prepare(
            'INSERT INTO chi_tiet_don (don_hang_id, su_kien_id, so_luong, don_gia)
             VALUES (?, ?, ?, ?)'
        );

        // 3. Duyệt từng mục trong giỏ và trừ tồn kho trực tiếp trong câu UPDATE
        foreach ($gio as $skId => $soLuong) {
            $skId = (int)$skId;
            $soLuong = (int)$soLuong;

            if ($soLuong <= 0) {
                continue;
            }

            // Thực thi trừ tồn kho với điều kiện an toàn: ton_kho >= ?
            $stTruKho->execute([$soLuong, $skId, $soLuong]);

            // Nếu không có dòng nào được cập nhật -> Hết vé hoặc không đủ tồn kho
            if ($stTruKho->rowCount() !== 1) {
                $stLayGia->execute([$skId]);
                $info = $stLayGia->fetch();
                $tenSK = $info ? $info['ten'] : "Sự kiện #{$skId}";
                $tonHienTai = $info ? (int)$info['ton_kho'] : 0;

                throw new RuntimeException(
                    "Sự kiện \"{$tenSK}\" không đủ vé (Yêu cầu: {$soLuong}, Còn lại: {$tonHienTai}). Vui lòng điều chỉnh lại số lượng."
                );
            }

            // Lấy giá vé chính thức từ CSDL để lưu vào chi tiết đơn
            $stLayGia->execute([$skId]);
            $sk = $stLayGia->fetch();
            $donGia = (int)($sk['gia'] ?? 0);
            $thanhTien = $donGia * $soLuong;
            $tongTien += $thanhTien;

            // Chèn vào chi tiết đơn
            $stChiTiet->execute([$donHangId, $skId, $soLuong, $donGia]);
        }

        // 4. Cập nhật tổng tiền chính thức vào bảng don_hang
        $stCapNhatTong = $db->prepare('UPDATE don_hang SET tong_tien = ? WHERE id = ?');
        $stCapNhatTong->execute([$tongTien, $donHangId]);

        // 5. Xác nhận giao dịch thành công
        $db->commit();

        return $donHangId;
    } catch (Throwable $e) {
        // Rollback hoàn toàn dữ liệu nếu xảy ra bất kỳ lỗi nào
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        throw $e;
    }
}
