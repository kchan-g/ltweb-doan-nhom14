<?php
/**
 * src/Data/KhoSuKien.php
 * Lớp truy cập dữ liệu Sự kiện.
 * Đây là NƠI DUY NHẤT đọc tệp data/su-kien.json trong toàn bộ dự án
 * (chuẩn bị để sang Chương 6 chỉ cần thay thế lớp này bằng PDO kết nối CSDL MySQL).
 */

declare(strict_types=1);

namespace App\Data;

use App\Models\SuKien;
use RuntimeException;

class KhoSuKien
{
    /** @var SuKien[]|null Bộ nhớ đệm danh sách sự kiện trong cùng một request */
    private ?array $ds = null;

    /**
     * @param string $tepJson Đường dẫn tuyệt đối tới tệp JSON lưu dữ liệu sự kiện
     */
    public function __construct(private string $tepJson)
    {
    }

    /**
     * Lấy toàn bộ danh sách sự kiện dưới dạng mảng các đối tượng SuKien.
     *
     * @return SuKien[] Danh sách đối tượng sự kiện
     * @throws RuntimeException Khi không tìm thấy tệp JSON hoặc định dạng không hợp lệ
     */
    public function tatCa(): array
    {
        if ($this->ds === null) {
            if (!is_file($this->tepJson)) {
                throw new RuntimeException("Không tìm thấy tệp dữ liệu {$this->tepJson}");
            }

            $noiDung = file_get_contents($this->tepJson);
            if ($noiDung === false) {
                throw new RuntimeException("Không thể đọc tệp dữ liệu {$this->tepJson}");
            }

            $mang = json_decode($noiDung, true, 512, JSON_THROW_ON_ERROR);
            if (!is_array($mang)) {
                throw new RuntimeException("Định dạng dữ liệu sự kiện không phải là mảng hợp lệ");
            }

            $this->ds = array_map(fn(array $d) => SuKien::tuMang($d), $mang);
        }

        return $this->ds;
    }

    /**
     * Tìm kiếm một sự kiện theo ID.
     *
     * @param int $id Mã sự kiện cần tìm
     * @return SuKien|null Đối tượng sự kiện hoặc null nếu không tồn tại
     */
    public function timTheoId(int $id): ?SuKien
    {
        if ($id <= 0) {
            return null;
        }

        foreach ($this->tatCa() as $sk) {
            if ($sk->id === $id) {
                return $sk;
            }
        }

        return null;
    }

    /**
     * Lọc và sắp xếp sự kiện cho trang danh-sach.php theo các tiêu chí GET an toàn.
     *
     * @param string|null $tuKhoa Từ khóa tìm kiếm
     * @param string|null $danhMuc Danh mục cần lọc
     * @param string|null $sapXep Tiêu chí sắp xếp
     * @return SuKien[] Kết quả lọc và sắp xếp
     */
    public function timKiem(?string $tuKhoa = null, ?string $danhMuc = null, ?string $sapXep = null): array
    {
        $danhSach = $this->tatCa();

        // 1. Lọc theo từ khóa tìm kiếm
        if ($tuKhoa !== null && trim($tuKhoa) !== '') {
            $tuKhoaChuan = mb_strtolower(trim($tuKhoa), 'UTF-8');
            $danhSach = array_filter($danhSach, function (SuKien $sk) use ($tuKhoaChuan) {
                $ten = mb_strtolower($sk->ten, 'UTF-8');
                $moTa = mb_strtolower($sk->moTaNgan, 'UTF-8');
                $diaDiem = mb_strtolower($sk->diaDiem, 'UTF-8');
                $dienGia = mb_strtolower($sk->dienGia, 'UTF-8');

                return str_contains($ten, $tuKhoaChuan)
                    || str_contains($moTa, $tuKhoaChuan)
                    || str_contains($diaDiem, $tuKhoaChuan)
                    || str_contains($dienGia, $tuKhoaChuan);
            });
        }

        // 2. Lọc theo danh mục hợp lệ
        $danhMucHopLe = ['hoi-thao', 'viec-lam', 'van-hoa', 'the-thao', 'hoc-thuat', 'cong-nghe'];
        if ($danhMuc !== null && in_array($danhMuc, $danhMucHopLe, true)) {
            $danhSach = array_filter($danhSach, fn(SuKien $sk) => $sk->danhMuc === $danhMuc);
        }

        // Chuyển lại về mảng đánh chỉ mục liên tục trước khi sắp xếp
        $danhSach = array_values($danhSach);

        // 3. Sắp xếp theo tiêu chí hợp lệ
        $sapXepHopLe = ['moi-nhat', 'sap-dien-ra', 'gia-tang', 'gia-giam', 'ten-az', 'diem-cao'];
        $tieuChi = in_array($sapXep, $sapXepHopLe, true) ? $sapXep : 'moi-nhat';

        usort($danhSach, function (SuKien $a, SuKien $b) use ($tieuChi): int {
            return match ($tieuChi) {
                'gia-tang' => $a->giaVe <=> $b->giaVe,
                'gia-giam' => $b->giaVe <=> $a->giaVe,
                'ten-az' => strcoll($a->ten, $b->ten),
                'diem-cao' => $b->diemRenLuyen <=> $a->diemRenLuyen,
                'sap-dien-ra' => strcmp($a->ngay, $b->ngay),
                default => strcmp($b->ngay, $a->ngay), // 'moi-nhat'
            };
        });

        return $danhSach;
    }
}
