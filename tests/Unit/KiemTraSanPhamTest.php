<?php
/**
 * tests/Unit/KiemTraSanPhamTest.php
 * Kiểm thử đơn vị cho hàm kiểm tra dữ liệu sự kiện (inc/kiem-tra.php),
 * đặc biệt là tính năng chuẩn hóa trường ngày giờ datetime-local sang MySQL DATETIME.
 */

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;

require_once __DIR__ . '/../../inc/kiem-tra.php';

class KiemTraSanPhamTest extends TestCase
{
    private array $danhSachIdDanhMucHopLe = [1, 2, 3, 4, 5];

    /**
     * Dữ liệu mẫu hợp lệ ban đầu
     */
    private function layDuLieuMauHopLe(): array
    {
        return [
            'ten'            => 'Hội thảo Trí tuệ Nhân tạo trong Giáo dục 2026',
            'danh_muc_id'    => '1',
            'gia'            => '25000',
            'ton_kho'        => '120',
            'dia_diem'       => 'Hội trường A - Trường Đại học Sư phạm, ĐHĐN',
            'thoi_gian'      => '2026-11-20T08:30', // Định dạng HTML5 datetime-local
            'diem_ren_luyen' => '5',
            'dien_gia'       => 'TS. Nguyễn Văn A',
            'mo_ta_ngan'     => 'Hội thảo chia sẻ ứng dụng AI trong giảng dạy và nghiên cứu học đường.',
            'mo_ta_chi_tiet' => 'Nội dung chi tiết chương trình hội thảo...',
            'video'          => 'https://www.youtube.com/watch?v=sample',
        ];
    }

    public function testDuLieuHopLeChuanHoaNgayGioThanhCong(): void
    {
        $input = $this->layDuLieuMauHopLe();
        [$du, $loi] = kiemTraSanPham($input, $this->danhSachIdDanhMucHopLe);

        $this->assertCount(0, $loi, 'Dữ liệu chuẩn không được có lỗi.');
        $this->assertEquals('Hội thảo Trí tuệ Nhân tạo trong Giáo dục 2026', $du['ten']);
        $this->assertEquals(1, $du['danh_muc_id']);
        $this->assertEquals(25000, $du['gia']);
        $this->assertEquals(120, $du['ton_kho']);
        $this->assertEquals(5, $du['diem_ren_luyen']);

        // Chuẩn hóa định dạng datetime-local sang MySQL DATETIME Y-m-d H:i:s
        $this->assertEquals('2026-11-20 08:30:00', $du['thoi_gian']);
    }

    public function testChapNhanDinhDangMySqlDateTimeSanCo(): void
    {
        $input = $this->layDuLieuMauHopLe();
        $input['thoi_gian'] = '2026-12-15 14:00:00';

        [$du, $loi] = kiemTraSanPham($input, $this->danhSachIdDanhMucHopLe);
        $this->assertArrayNotHasKey('thoi_gian', $loi);
        $this->assertEquals('2026-12-15 14:00:00', $du['thoi_gian']);
    }

    public function testBaoLoiKhiThoiGianDeTrong(): void
    {
        $input = $this->layDuLieuMauHopLe();
        $input['thoi_gian'] = '';

        [$du, $loi] = kiemTraSanPham($input, $this->danhSachIdDanhMucHopLe);
        $this->assertArrayHasKey('thoi_gian', $loi);
        $this->assertStringContains('Vui lòng chọn thời gian', $loi['thoi_gian']);
    }

    public function testBaoLoiKhiThoiGianKhongHopLe(): void
    {
        $input = $this->layDuLieuMauHopLe();
        $input['thoi_gian'] = 'ngay-mai-chieu-toi';

        [$du, $loi] = kiemTraSanPham($input, $this->danhSachIdDanhMucHopLe);
        $this->assertArrayHasKey('thoi_gian', $loi);
        $this->assertStringContains('hợp lệ', $loi['thoi_gian']);
    }

    public function testBaoLoiKhiTenSuKienQuaNgan(): void
    {
        $input = $this->layDuLieuMauHopLe();
        $input['ten'] = 'A';

        [$du, $loi] = kiemTraSanPham($input, $this->danhSachIdDanhMucHopLe);
        $this->assertArrayHasKey('ten', $loi);
    }

    public function testBaoLoiKhiDanhMucKhongTonTai(): void
    {
        $input = $this->layDuLieuMauHopLe();
        $input['danh_muc_id'] = '999';

        [$du, $loi] = kiemTraSanPham($input, $this->danhSachIdDanhMucHopLe);
        $this->assertArrayHasKey('danh_muc_id', $loi);
    }

    public function testBaoLoiKhiGiaVeAm(): void
    {
        $input = $this->layDuLieuMauHopLe();
        $input['gia'] = '-10000';

        [$du, $loi] = kiemTraSanPham($input, $this->danhSachIdDanhMucHopLe);
        $this->assertArrayHasKey('gia', $loi);
    }

    public function testBaoLoiKhiTonKhoAm(): void
    {
        $input = $this->layDuLieuMauHopLe();
        $input['ton_kho'] = '-5';

        [$du, $loi] = kiemTraSanPham($input, $this->danhSachIdDanhMucHopLe);
        $this->assertArrayHasKey('ton_kho', $loi);
    }

    public function testBaoLoiKhiDiemRenLuyenVuotKhung(): void
    {
        $input = $this->layDuLieuMauHopLe();
        $input['diem_ren_luyen'] = '40'; // Khung chuẩn ĐHĐN là 0 - 30

        [$du, $loi] = kiemTraSanPham($input, $this->danhSachIdDanhMucHopLe);
        $this->assertArrayHasKey('diem_ren_luyen', $loi);
    }
}
