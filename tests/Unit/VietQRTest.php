<?php
/**
 * tests/Unit/VietQRTest.php
 * Kiểm thử đơn vị cho tính năng tạo mã VietQR tự động (inc/ham.php: taoLinkVietQR).
 */

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;

require_once __DIR__ . '/../../inc/ham.php';

class VietQRTest extends TestCase
{
    public function testThongTinNganHangMacDinhChuanGiaoDuc(): void
    {
        $nh = thongTinNganHang();
        $this->assertEquals('TPB', $nh['ma_ngan_hang']);
        $this->assertEquals('13012062204', $nh['so_tai_khoan']);
        $this->assertEquals('UNIEVENT', $nh['chu_tai_khoan']);
    }

    public function testSinhLinkVietQRChuanXac(): void
    {
        $maDon = 'DH-20261108-001';
        $tongTien = 75000;

        $link = taoLinkVietQR($maDon, $tongTien);

        // Kiểm tra domain và cấu trúc link
        $this->assertStringContains('https://img.vietqr.io/image/TPB-13012062204-compact2.png', $link);

        // Phân tích tham số query string
        $queryString = parse_url($link, PHP_URL_QUERY);
        $this->assertNotNull($queryString, 'Link phải có query string.');

        parse_str((string)$queryString, $params);

        $this->assertEquals('75000', $params['amount']);
        $this->assertEquals('UE DH-20261108-001', $params['addInfo']);
        $this->assertEquals('UNIEVENT', $params['accountName']);
    }

    public function testLocKyTuDacBietTrongNoiDungChuyenKhoan(): void
    {
        $maDonCoKyTuLa = 'DH/2026-11-08 #002 <script>';
        $link = taoLinkVietQR($maDonCoKyTuLa, 50000);

        $queryString = parse_url($link, PHP_URL_QUERY);
        parse_str((string)$queryString, $params);

        // Ký tự lạ ngoài A-Za-z0-9_- phải bị loại bỏ
        $this->assertEquals('UE DH2026-11-08002script', $params['addInfo']);
    }

    public function testXuLySoTienKhongAm(): void
    {
        $link = taoLinkVietQR('DH-000', -20000);
        $queryString = parse_url($link, PHP_URL_QUERY);
        parse_str((string)$queryString, $params);

        $this->assertEquals('0', $params['amount']);
    }

    public function testTuyChonNganHangVaTaiKhoanKhac(): void
    {
        $link = taoLinkVietQR('DH-999', 100000, 'MB', '99998888', 'CLB TIN HOC UED');
        $this->assertStringContains('https://img.vietqr.io/image/MB-99998888-compact2.png', $link);

        $queryString = parse_url($link, PHP_URL_QUERY);
        parse_str((string)$queryString, $params);

        $this->assertEquals('100000', $params['amount']);
        $this->assertEquals('CLB TIN HOC UED', $params['accountName']);
    }
}
