<?php
/**
 * src/Services/GioHang.php
 * Lớp dịch vụ quản lý Giỏ vé / Đơn đăng ký sự kiện của người dùng.
 * Phân chia 2 trạng thái:
 * 1. Chờ xác nhận đăng ký: $_SESSION['gio'] = [id => soLuong]
 * 2. Đã xác nhận đăng ký:  $_SESSION['da_dang_ky'] = [id => ['thoi_gian' => ..., 'ma_ve' => ...]]
 *
 * TỐI ƯU HÓA HIỆU NĂNG CSDL:
 * Phương thức chiTiet() sử dụng layTheoIds() để tải toàn bộ thông tin sự kiện
 * chỉ trong DUY NHẤT MỘT câu truy vấn SQL WHERE id IN (...), loại trừ hoàn toàn lỗi N+1.
 */

declare(strict_types=1);

namespace App\Services;

use App\Data\KhoSuKien;

class GioHang
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['gio']) || !is_array($_SESSION['gio'])) {
            $_SESSION['gio'] = [];
        }

        if (!isset($_SESSION['da_dang_ky']) || !is_array($_SESSION['da_dang_ky'])) {
            $_SESSION['da_dang_ky'] = [];
        }
    }

    /**
     * Kiểm tra xem sự kiện có đang ở trạng thái Chờ xác nhận đăng ký hay không.
     */
    public function dangChoXacNhan(int $id): bool
    {
        return isset($_SESSION['gio'][$id]) && $_SESSION['gio'][$id] >= 1;
    }

    /**
     * Kiểm tra xem sự kiện đã được Xác nhận đăng ký chính thức hay chưa.
     */
    public function daXacNhan(int $id): bool
    {
        return isset($_SESSION['da_dang_ky'][$id]);
    }

    /**
     * Kiểm tra xem sinh viên đã đăng ký sự kiện này.
     */
    public function daDangKy(int $id): bool
    {
        return $this->dangChoXacNhan($id) || $this->daXacNhan($id);
    }

    /**
     * Trả về chuỗi trạng thái đăng ký của sự kiện.
     */
    public function trangThaiDangKy(int $id): string
    {
        if ($this->daXacNhan($id)) {
            return 'da_xac_nhan';
        }
        if ($this->dangChoXacNhan($id)) {
            return 'cho_xac_nhan';
        }
        return 'chua_dang_ky';
    }

    /**
     * Thêm sự kiện vào danh sách Chờ xác nhận đăng ký.
     *
     * @param int $id Mã sự kiện
     * @param int $sl Số lượng vé (tối thiểu 1)
     * @return bool True nếu thêm mới thành công
     */
    public function them(int $id, int $sl = 1): bool
    {
        if ($id <= 0) {
            return false;
        }

        $sl = max(1, min(50, $sl));
        $_SESSION['gio'][$id] = ($_SESSION['gio'][$id] ?? 0) + $sl;

        return true;
    }

    /**
     * Xác nhận toàn bộ sự kiện đang ở trạng thái Chờ xác nhận sang Đã đăng ký thành công.
     */
    public function xacNhanTatCa(): int
    {
        $soLuong = 0;
        foreach ($_SESSION['gio'] as $id => $sl) {
            if ($sl > 0) {
                $_SESSION['da_dang_ky'][$id] = [
                    'thoi_gian' => date('d/m/Y H:i'),
                    'ma_ve' => 'UE-' . str_pad((string)$id, 4, '0', STR_PAD_LEFT) . '-' . rand(1000, 9999),
                ];
                $soLuong++;
            }
        }
        $_SESSION['gio'] = [];
        return $soLuong;
    }

    /**
     * Xác nhận 1 sự kiện cụ thể từ Chờ xác nhận sang Đã đăng ký.
     */
    public function xacNhan(int $id): bool
    {
        if (!$this->dangChoXacNhan($id)) {
            return false;
        }

        $_SESSION['da_dang_ky'][$id] = [
            'thoi_gian' => date('d/m/Y H:i'),
            'ma_ve' => 'UE-' . str_pad((string)$id, 4, '0', STR_PAD_LEFT) . '-' . rand(1000, 9999),
        ];
        unset($_SESSION['gio'][$id]);
        return true;
    }

    /**
     * Hủy vé đã xác nhận của một sự kiện.
     */
    public function huyXacNhan(int $id): void
    {
        unset($_SESSION['da_dang_ky'][$id]);
    }

    /**
     * Cập nhật số lượng của một mục trong giỏ.
     */
    public function capNhat(int $id, int $sl): void
    {
        if ($id <= 0) {
            return;
        }

        if ($sl <= 0) {
            $this->xoa($id);
        } else {
            $_SESSION['gio'][$id] = min(50, $sl);
        }
    }

    /**
     * Xóa một sự kiện khỏi danh sách chờ xác nhận.
     */
    public function xoa(int $id): void
    {
        unset($_SESSION['gio'][$id]);
    }

    /**
     * Xóa toàn bộ các mục trong danh sách chờ xác nhận.
     */
    public function xoaHet(): void
    {
        $_SESSION['gio'] = [];
    }

    /**
     * Đếm tổng số lượng vé chờ xác nhận.
     */
    public function soMonChoXacNhan(): int
    {
        return array_sum($_SESSION['gio']);
    }

    /**
     * Đếm tổng số lượng vé đã xác nhận đăng ký thành công.
     */
    public function soMonDaXacNhan(): int
    {
        return count($_SESSION['da_dang_ky']);
    }

    /**
     * Đếm tổng số món trong giỏ.
     */
    public function soMon(): int
    {
        return $this->soMonChoXacNhan();
    }

    /**
     * Lấy danh sách thô các ID và số lượng: [id => soLuong].
     *
     * @return array<int, int>
     */
    public function tatCa(): array
    {
        return $_SESSION['gio'];
    }

    /**
     * Lấy danh sách chi tiết các sự kiện đang chờ xác nhận.
     * ÁP DỤNG TRUY VẤN MỘT LẦN layTheoIds() ĐỂ TRÁNH LỖI N+1.
     *
     * @param KhoSuKien $kho
     * @return array<int, array{suKien: \App\Models\SuKien, soLuong: int, thanhTien: int}>
     */
    public function chiTiet(KhoSuKien $kho): array
    {
        $ids = array_keys($_SESSION['gio']);
        if (empty($ids)) {
            return [];
        }

        // Tải toàn bộ sự kiện trong 1 câu SQL duy nhất
        $danhSachSK = $kho->layTheoIds($ids);

        $ketQua = [];
        foreach ($danhSachSK as $sk) {
            $soLuong = (int)($_SESSION['gio'][$sk->id] ?? 1);
            if ($soLuong > 0) {
                $ketQua[] = [
                    'suKien'    => $sk,
                    'soLuong'   => $soLuong,
                    'thanhTien' => $sk->gia * $soLuong,
                ];
            }
        }

        return $ketQua;
    }

    /**
     * Lấy danh sách chi tiết các sự kiện đã xác nhận đăng ký thành công.
     */
    public function danhSachDaXacNhan(KhoSuKien $kho): array
    {
        $ids = array_keys($_SESSION['da_dang_ky']);
        if (empty($ids)) {
            return [];
        }

        $danhSachSK = $kho->layTheoIds($ids);

        $ketQua = [];
        foreach ($danhSachSK as $sk) {
            $info = $_SESSION['da_dang_ky'][$sk->id] ?? [];
            $ketQua[] = [
                'suKien'          => $sk,
                'thoiGianXacNhan' => $info['thoi_gian'] ?? date('d/m/Y H:i'),
                'maVe'            => $info['ma_ve'] ?? ('UE-' . $sk->id),
            ];
        }

        return $ketQua;
    }

    /**
     * Lấy mã vé đã xác nhận của sự kiện nếu có.
     */
    public function layMaVe(int $id): ?string
    {
        return $_SESSION['da_dang_ky'][$id]['ma_ve'] ?? null;
    }

    /**
     * Tính tổng số tiền vé trong giỏ hàng.
     */
    public function tongTien(KhoSuKien $kho): int
    {
        $chiTiet = $this->chiTiet($kho);
        $tong = 0;
        foreach ($chiTiet as $item) {
            $tong += $item['thanhTien'];
        }
        return $tong;
    }
}
