<?php
/**
 * src/Models/SuKien.php
 * Lớp thực thể Sự kiện (SuKien / SanPham) của website UniEvent.
 * Toàn bộ các thuộc tính là readonly để đảm bảo tính bất biến của dữ liệu.
 * Triển khai giao diện JsonSerializable để kiểm soát các trường dữ liệu công khai trả về trong REST API.
 */

declare(strict_types=1);

namespace App\Models;

use JsonSerializable;

class SuKien implements JsonSerializable
{
    /** @var int Bí danh $giaVe tương thích với mã nguồn bài trước */
    public readonly int $giaVe;

    /** @var int Bí danh $soLuongVe tương thích với ton_kho */
    public readonly int $soLuongVe;

    /** @var string Bí danh $hinhAnh tương thích với anh */
    public readonly string $hinhAnh;

    /** @var int Bí danh $danhMucId */
    public readonly int $danhMucId;

    /**
     * Khởi tạo đối tượng Sự kiện.
     */
    public function __construct(
        public readonly int $id,
        public readonly int $danh_muc_id,
        public readonly string $ten,
        public readonly int $gia,
        public readonly int $ton_kho,
        public readonly string $dia_diem,
        public readonly string $thoi_gian,
        public readonly int $diem_ren_luyen = 0,
        public readonly string $dien_gia = '',
        public readonly string $anh = 'images/AI.jpg',
        public readonly string $mo_ta_ngan = '',
        public readonly string $mo_ta_chi_tiet = '',
        public readonly string $video = '',
        public readonly string $ngay_tao = '',
        public readonly string $ten_danh_muc = '',
        public readonly string $slug_danh_muc = ''
    ) {
        $this->danhMucId  = $this->danh_muc_id;
        $this->giaVe      = $this->gia;
        $this->soLuongVe  = $this->ton_kho;
        $this->hinhAnh    = $this->anh !== '' ? $this->anh : 'images/AI.jpg';
    }

    /**
     * Thuộc tính ảo truy cập tương thích ngược với code cũ (camelCase).
     */
    public function __get(string $name): mixed
    {
        return match ($name) {
            'diaDiem'       => $this->dia_diem,
            'thoiGian'      => $this->thoi_gian,
            'diemRenLuyen'  => $this->diem_ren_luyen,
            'dienGia'       => $this->dien_gia,
            'moTaNgan'      => $this->mo_ta_ngan,
            'moTaChiTiet'   => $this->mo_ta_chi_tiet,
            'ngayTao'       => $this->ngay_tao,
            'danhMuc'       => $this->slug_danh_muc !== '' ? $this->slug_danh_muc : (string)$this->danh_muc_id,
            'tenDanhMuc'    => $this->ten_danh_muc,
            'badgeClass'    => 'badge',
            default         => null,
        };
    }

    public function __isset(string $name): bool
    {
        return in_array($name, [
            'diaDiem', 'thoiGian', 'diemRenLuyen', 'dienGia',
            'moTaNgan', 'moTaChiTiet', 'ngayTao', 'danhMuc', 'tenDanhMuc', 'badgeClass'
        ], true);
    }

    /**
     * Phương thức tĩnh tạo đối tượng SuKien từ mảng dữ liệu CSDL (snake_case) hoặc JSON.
     *
     * @param array<string, mixed> $d Mảng dữ liệu sự kiện từ MySQL PDO
     * @return self Đối tượng SuKien mới
     */
    public static function tuMang(array $d): self
    {
        return new self(
            id: (int)($d['id'] ?? 0),
            danh_muc_id: (int)($d['danh_muc_id'] ?? ($d['danhMucId'] ?? 1)),
            ten: (string)($d['ten'] ?? ''),
            gia: (int)($d['gia'] ?? ($d['giaVe'] ?? 0)),
            ton_kho: (int)($d['ton_kho'] ?? ($d['soLuongVe'] ?? 0)),
            dia_diem: (string)($d['dia_diem'] ?? ($d['diaDiem'] ?? '')),
            thoi_gian: (string)($d['thoi_gian'] ?? ($d['thoiGian'] ?? '')),
            diem_ren_luyen: (int)($d['diem_ren_luyen'] ?? ($d['diemRenLuyen'] ?? 0)),
            dien_gia: (string)($d['dien_gia'] ?? ($d['dienGia'] ?? '')),
            anh: (string)($d['anh'] ?? ($d['hinhAnh'] ?? 'images/AI.jpg')),
            mo_ta_ngan: (string)($d['mo_ta_ngan'] ?? ($d['moTaNgan'] ?? '')),
            mo_ta_chi_tiet: (string)($d['mo_ta_chi_tiet'] ?? ($d['moTaChiTiet'] ?? '')),
            video: (string)($d['video'] ?? ''),
            ngay_tao: (string)($d['ngay_tao'] ?? ($d['ngayTao'] ?? '')),
            ten_danh_muc: (string)($d['ten_danh_muc'] ?? ($d['tenDanhMuc'] ?? '')),
            slug_danh_muc: (string)($d['slug_danh_muc'] ?? ($d['danhMuc'] ?? ''))
        );
    }

    /**
     * Dữ liệu công khai chuyển đổi thành JSON an toàn qua REST API (ẩn các cột nội bộ).
     */
    public function jsonSerialize(): array
    {
        return [
            'id'            => $this->id,
            'danh_muc_id'   => $this->danh_muc_id,
            'ten_danh_muc'  => $this->ten_danh_muc,
            'ten'           => $this->ten,
            'gia'           => $this->gia,
            'ton_kho'       => $this->ton_kho,
            'dia_diem'      => $this->dia_diem,
            'thoi_gian'     => $this->thoi_gian,
            'diem_ren_luyen'=> $this->diem_ren_luyen,
            'dien_gia'      => $this->dien_gia,
            'anh'           => $this->hinhAnh,
            'mo_ta_ngan'    => $this->mo_ta_ngan,
        ];
    }
}
