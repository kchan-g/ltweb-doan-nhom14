<?php
/**
 * src/Services/GioHang.php
 * Lớp dịch vụ quản lý Giỏ vé / Đơn đăng ký sự kiện của người dùng.
 * Phân chia 2 trạng thái:
 * 1. Chờ xác nhận đăng ký: $_SESSION['gio'] = [id => 1]
 * 2. Đã xác nhận đăng ký:  $_SESSION['da_dang_ky'] = [id => ['thoi_gian' => ..., 'ma_ve' => ...]]
 * Ràng buộc: Mỗi tài khoản sinh viên chỉ được đăng ký tối đa 1 vé cho mỗi sự kiện.
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
     * Kiểm tra xem sinh viên đã đăng ký sự kiện này (đang chờ hoặc đã xác nhận).
     */
    public function daDangKy(int $id): bool
    {
        return $this->dangChoXacNhan($id) || $this->daXacNhan($id);
    }

    /**
     * Trả về chuỗi trạng thái đăng ký của sự kiện:
     * - 'da_xac_nhan': Đã đăng ký thành công
     * - 'cho_xac_nhan': Đang chờ xác nhận
     * - 'chua_dang_ky': Chưa đăng ký
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
     * Ràng buộc: Mỗi tài khoản sinh viên chỉ có thể đăng ký 1 vé cho mỗi sự kiện.
     *
     * @param int $id Mã sự kiện
     * @param int $sl Số lượng vé (luôn là 1 vé duy nhất/sự kiện)
     * @return bool True nếu thêm mới thành công, False nếu ID không hợp lệ hoặc đã đăng ký rồi
     */
    public function them(int $id, int $sl = 1): bool
    {
        if ($id <= 0) {
            return false;
        }

        // Ràng buộc: Mỗi tài khoản sinh viên chỉ đăng ký 1 vé cho mỗi sự kiện
        if ($this->daDangKy($id)) {
            return false;
        }

        $_SESSION['gio'][$id] = 1;

        return true;
    }

    /**
     * Xác nhận toàn bộ sự kiện đang ở trạng thái Chờ xác nhận sang Đã đăng ký thành công.
     *
     * @return int Số lượng sự kiện vừa được xác nhận (+N vào Đã đăng ký)
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
     * Cập nhật số lượng của một mục trong giỏ (luôn giữ 1 vé hoặc xóa).
     */
    public function capNhat(int $id, int $sl): void
    {
        if ($id <= 0) {
            return;
        }

        if ($sl <= 0) {
            $this->xoa($id);
        } else {
            $_SESSION['gio'][$id] = 1;
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
        return count($_SESSION['gio']);
    }

    /**
     * Đếm tổng số lượng vé đã xác nhận đăng ký thành công (+1 vào đây).
     */
    public function soMonDaXacNhan(): int
    {
        return count($_SESSION['da_dang_ky']);
    }

    /**
     * Đếm tổng số lượng vé chờ xác nhận (hỗ trợ tương thích ngược).
     */
    public function soMon(): int
    {
        return $this->soMonChoXacNhan();
    }

    /**
     * Lấy danh sách thô các ID và số lượng chờ xác nhận: [id => soLuong].
     */
    public function tatCa(): array
    {
        return $_SESSION['gio'];
    }

    /**
     * Lấy danh sách chi tiết các sự kiện đang chờ xác nhận.
     */
    public function chiTiet(KhoSuKien $kho): array
    {
        $ketQua = [];
        foreach ($_SESSION['gio'] as $id => $soLuong) {
            $sk = $kho->timTheoId((int)$id);
            if ($sk !== null && $soLuong > 0) {
                $ketQua[] = [
                    'suKien' => $sk,
                    'soLuong' => 1,
                    'thanhTien' => 0,
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
        $ketQua = [];
        foreach ($_SESSION['da_dang_ky'] as $id => $info) {
            $sk = $kho->timTheoId((int)$id);
            if ($sk !== null) {
                $ketQua[] = [
                    'suKien' => $sk,
                    'thoiGianXacNhan' => $info['thoi_gian'] ?? date('d/m/Y H:i'),
                    'maVe' => $info['ma_ve'] ?? ('UE-' . $id),
                ];
            }
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

    public function tongTien(KhoSuKien $kho): int
    {
        return 0;
    }
}
