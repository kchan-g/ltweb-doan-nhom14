<?php
/**
 * src/Models/SuKien.php
 * Lớp thực thể Sự kiện (SuKien / SanPham) của website UniEvent.
 * Toàn bộ các thuộc tính là readonly để đảm bảo tính bất biến của dữ liệu.
 */

declare(strict_types=1);

namespace App\Models;

class SuKien
{
    /**
     * Khởi tạo đối tượng Sự kiện với các trường dữ liệu bắt buộc và tùy chọn.
     */
    public function __construct(
        public readonly int $id,
        public readonly string $ten,
        public readonly string $danhMuc,
        public readonly string $tenDanhMuc,
        public readonly string $badgeClass,
        public readonly string $ngay,
        public readonly string $thoiGian,
        public readonly string $diaDiem,
        public readonly int $diemRenLuyen,
        public readonly int $giaVe,
        public readonly int $soLuongVe,
        public readonly string $dienGia,
        public readonly string $hinhAnh,
        public readonly string $moTaNgan,
        public readonly string $moTaChiTiet,
        public readonly array $noiDungNoiBat,
        public readonly string $video = ''
    ) {
        $this->gia = $this->giaVe;
    }

    /**
     * Thuộc tính bí danh $gia tương thích với đề bài mẫu (SanPham->gia).
     */
    public readonly int $gia;

    /**
     * Phương thức tĩnh tạo đối tượng SuKien từ mảng dữ liệu lấy từ tệp JSON.
     *
     * @param array $d Dữ liệu mảng của một sự kiện
     * @return self Đối tượng SuKien mới
     */
    public static function tuMang(array $d): self
    {
        return new self(
            id: (int)($d['id'] ?? 0),
            ten: (string)($d['ten'] ?? ''),
            danhMuc: (string)($d['danhMuc'] ?? ''),
            tenDanhMuc: (string)($d['tenDanhMuc'] ?? ''),
            badgeClass: (string)($d['badgeClass'] ?? 'badge'),
            ngay: (string)($d['ngay'] ?? ''),
            thoiGian: (string)($d['thoiGian'] ?? ''),
            diaDiem: (string)($d['diaDiem'] ?? ''),
            diemRenLuyen: (int)($d['diemRenLuyen'] ?? 0),
            giaVe: (int)($d['giaVe'] ?? ($d['gia'] ?? 0)),
            soLuongVe: (int)($d['soLuongVe'] ?? 100),
            dienGia: (string)($d['dienGia'] ?? ''),
            hinhAnh: (string)($d['hinhAnh'] ?? 'images/AI.jpg'),
            moTaNgan: (string)($d['moTaNgan'] ?? ''),
            moTaChiTiet: (string)($d['moTaChiTiet'] ?? ''),
            noiDungNoiBat: (array)($d['noiDungNoiBat'] ?? []),
            video: (string)($d['video'] ?? '')
        );
    }
}
