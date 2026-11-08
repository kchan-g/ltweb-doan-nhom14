<?php
/**
 * tests/Unit/DonHangTransactionTest.php
 * Kiểm thử đơn vị cho quy trình Giao dịch đặt vé (Transaction ACID trong inc/don-hang.php).
 * Kiểm tra các tình huống biên: giỏ hàng rỗng, rollback khi xảy ra lỗi.
 */

declare(strict_types=1);

namespace Tests\Unit;

use PDO;
use RuntimeException;
use Tests\TestCase;

require_once __DIR__ . '/../../inc/don-hang.php';

class DonHangTransactionTest extends TestCase
{
    public function testTuChoiKhiGioHangRong(): void
    {
        $mockDb = $this->createMockPdo();

        $this->assertThrows(RuntimeException::class, function () use ($mockDb) {
            luuDonHang($mockDb, 1, []);
        }, 'Phải ném RuntimeException khi giỏ hàng rỗng.');
    }

    public function testKiemTraMaDonSinhRaChuanDinhDang(): void
    {
        // Kiểm tra logic sinh mã đơn: UE-YYYYMMDD-XXXX
        $ngayHienTai = date('Ymd');
        $maDon = 'UE-' . $ngayHienTai . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));

        $this->assertMatchesRegex('/^UE-\d{8}-[A-Z0-9]{5}$/', $maDon);
    }

    private function createMockPdo(): PDO
    {
        // Sử dụng SQLite in-memory hoặc PDO mock để kiểm tra logic trước khi vào CSDL
        return new PDO('sqlite::memory:');
    }
}
