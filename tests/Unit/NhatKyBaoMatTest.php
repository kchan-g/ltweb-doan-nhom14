<?php
/**
 * tests/Unit/NhatKyBaoMatTest.php
 * Kiểm thử đơn vị cho hệ thống ghi nhật ký an ninh (logs/bao-mat.log):
 * - Ghi nhận các sự kiện bảo mật
 * - Tự động loại bỏ/che giấu mật khẩu và mã token bí mật khỏi tệp log
 */

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;

require_once __DIR__ . '/../../inc/config.php';
require_once __DIR__ . '/../../inc/ham.php';

class NhatKyBaoMatTest extends TestCase
{
    public function testGhiLogBaoMatKhongChuaMatKhauVaMaCsrf(): void
    {
        $logPath = __DIR__ . '/../../logs/bao-mat.log';

        // Ghi log thử nghiệm sự kiện csrf_sai kèm dữ liệu nhạy cảm
        ghiLogBaoMat('csrf_sai', [
            'trang'    => '/test-security.php',
            'mat_khau' => 'MatKhauBiMat123!', // Phải bị hàm ghiLogBaoMat tự động loại bỏ
            'csrf'     => 'csrf_token_secret', // Phải bị loại bỏ
        ]);

        $this->assertTrue(file_exists($logPath), 'Tệp logs/bao-mat.log phải tồn tại.');

        $noiDungLog = (string)file_get_contents($logPath);
        $this->assertStringContains('csrf_sai', $noiDungLog);
        $this->assertStringContains('/test-security.php', $noiDungLog);

        // Đảm bảo không chứa mật khẩu hay token bí mật
        $this->assertFalse(str_contains($noiDungLog, 'MatKhauBiMat123!'), 'Nhật ký bảo mật tuyệt đối không được ghi mật khẩu!');
        $this->assertFalse(str_contains($noiDungLog, 'csrf_token_secret'), 'Nhật ký bảo mật tuyệt đối không được ghi token CSRF!');
    }
}
