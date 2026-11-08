<?php
/**
 * tests/Unit/GioHangTest.php
 * Kiểm thử đơn vị cho lớp dịch vụ Giỏ vé / Đơn đăng ký sự kiện (App\Services\GioHang).
 */

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\GioHang;
use Tests\TestCase;

class GioHangTest extends TestCase
{
    private GioHang $gio;

    public function setUp(): void
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
        $_SESSION['gio'] = [];
        $_SESSION['da_dang_ky'] = [];

        $this->gio = new GioHang();
    }

    public function tearDown(): void
    {
        $_SESSION['gio'] = [];
        $_SESSION['da_dang_ky'] = [];
    }

    public function testKhoiTaoGioRong(): void
    {
        $this->assertEquals(0, $this->gio->soMon());
        $this->assertEquals([], $this->gio->tatCa());
    }

    public function testThemVeVaoGioThanhCong(): void
    {
        $ketQua = $this->gio->them(1, 2);

        $this->assertTrue($ketQua);
        $this->assertEquals(2, $this->gio->soMon());
        $this->assertTrue($this->gio->dangChoXacNhan(1));
        $this->assertEquals(['1' => 2], $this->gio->tatCa());
    }

    public function testThemTiepTucCongDonSoLuong(): void
    {
        $this->gio->them(5, 1);
        $this->gio->them(5, 3);

        $this->assertEquals(4, $this->gio->soMon());
        $this->assertEquals(4, $this->gio->tatCa()[5]);
    }

    public function testCapNhatSoLuongVe(): void
    {
        $this->gio->them(2, 1);
        $this->gio->capNhat(2, 5);

        $this->assertEquals(5, $this->gio->soMon());
        $this->assertEquals(5, $this->gio->tatCa()[2]);

        // Cập nhật về 0 thì tự động loại bỏ khỏi giỏ
        $this->gio->capNhat(2, 0);
        $this->assertEquals(0, $this->gio->soMon());
        $this->assertFalse($this->gio->dangChoXacNhan(2));
    }

    public function testXoaSuKienKhoiGio(): void
    {
        $this->gio->them(1, 2);
        $this->gio->them(3, 1);
        $this->assertEquals(3, $this->gio->soMon());

        $this->gio->xoa(1);
        $this->assertEquals(1, $this->gio->soMon());
        $this->assertFalse($this->gio->dangChoXacNhan(1));
        $this->assertTrue($this->gio->dangChoXacNhan(3));
    }

    public function testXoaHetToanBoGio(): void
    {
        $this->gio->them(1, 2);
        $this->gio->them(2, 3);
        $this->gio->them(3, 1);
        $this->assertEquals(6, $this->gio->soMon());

        $this->gio->xoaHet();
        $this->assertEquals(0, $this->gio->soMon());
        $this->assertEquals([], $this->gio->tatCa());
    }

    public function testXacNhanVeDangKy(): void
    {
        $this->gio->them(4, 1);
        $this->assertTrue($this->gio->dangChoXacNhan(4));
        $this->assertFalse($this->gio->daXacNhan(4));

        $ok = $this->gio->xacNhan(4);
        $this->assertTrue($ok);
        $this->assertFalse($this->gio->dangChoXacNhan(4));
        $this->assertTrue($this->gio->daXacNhan(4));
        $this->assertEquals(1, $this->gio->soMonDaXacNhan());
    }
}
