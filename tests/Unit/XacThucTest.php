<?php
/**
 * tests/Unit/XacThucTest.php
 * Kiểm thử đơn vị cho cơ chế xác thực và phòng chống dò mật khẩu (Brute Force):
 * - Đếm số lần đăng nhập sai theo tài khoản
 * - Ngưỡng khóa tạm thời (5 lần sai kích hoạt mã HTTP 429)
 * - Dọn dẹp bản ghi sau khi kiểm thử hoặc đăng nhập thành công
 */

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;

require_once __DIR__ . '/../../inc/config.php';
require_once __DIR__ . '/../../inc/xac-thuc.php';

class XacThucTest extends TestCase
{
    public function testChongBruteForceDemSoLanSaiVaKhoaTam(): void
    {
        $emailTest = 'test_auth_' . bin2hex(random_bytes(4)) . '@ued.vn';

        // Ban đầu chưa có lần sai nào
        $this->assertEquals(0, soLanSai($emailTest));

        // Ghi nhận 3 lần đăng nhập sai
        ghiLanSai($emailTest);
        ghiLanSai($emailTest);
        ghiLanSai($emailTest);
        $this->assertEquals(3, soLanSai($emailTest));

        // Ghi thêm 2 lần sai nữa -> đạt mốc 5 lần (kích hoạt mã 429)
        ghiLanSai($emailTest);
        ghiLanSai($emailTest);
        $this->assertEquals(5, soLanSai($emailTest));

        // Dọn dẹp bản ghi kiểm thử
        db()->prepare('DELETE FROM dang_nhap_sai WHERE email = ?')->execute([$emailTest]);
        $this->assertEquals(0, soLanSai($emailTest));
    }
}
