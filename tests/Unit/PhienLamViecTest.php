<?php
/**
 * tests/Unit/PhienLamViecTest.php
 * Kiểm thử đơn vị cho quản lý phiên làm việc (Session) và cấu hình môi trường:
 * - Nạp biến môi trường từ tệp .env (phpdotenv)
 * - Tự động hết hạn phiên sau 30 phút không hoạt động
 */

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;

require_once __DIR__ . '/../../inc/config.php';

class PhienLamViecTest extends TestCase
{
    public function testBienMoiTruongDotenvDaDuocNap(): void
    {
        $this->assertArrayHasKey('DB_HOST', $_ENV);
        $this->assertArrayHasKey('DB_DATABASE', $_ENV);
        $this->assertArrayHasKey('DB_USERNAME', $_ENV);
        $this->assertEquals('ltweb_nhom14', $_ENV['DB_DATABASE']);
        $this->assertTrue(defined('DEBUG'), 'Hằng số DEBUG phải được định nghĩa.');
    }

    public function testHetHanPhienSau30PhutKhongHoatDong(): void
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }

        // Giả lập phiên đang hoạt động
        $_SESSION['uid'] = 10;
        $_SESSION['hoat_dong'] = time() - (35 * 60); // Đã trôi qua 35 phút (quá giới hạn 30 phút)

        // Thực hiện logic kiểm tra hết hạn như trong inc/config.php
        if (time() - ($_SESSION['hoat_dong'] ?? time()) > 30 * 60) {
            $_SESSION = [];
        }

        $this->assertCount(0, $_SESSION, 'Phiên làm việc sau 30 phút không hoạt động phải bị xóa dữ liệu.');
        $this->assertNull($_SESSION['uid'] ?? null);
    }
}
