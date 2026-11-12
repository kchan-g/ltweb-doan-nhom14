<?php
/**
 * tests/Unit/CsrfTest.php
 * Kiểm thử đơn vị cho cơ chế phòng chống giả mạo yêu cầu chéo trang (CSRF):
 * - Tạo mã token chuẩn 64 ký tự hex bằng random_bytes(32)
 * - Sinh trường biểu mẫu ẩn <input type="hidden" name="csrf">
 * - Xác thực tính hợp lệ của mã token trong POST và tiêu đề HTTP qua hash_equals
 */

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;

require_once __DIR__ . '/../../inc/config.php';

class CsrfTest extends TestCase
{
    public function setUp(): void
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }

    public function testCsrfTokenSinhMaChuan64KyTuHex(): void
    {
        $token = csrfToken();
        $this->assertEquals(64, strlen($token), 'Mã CSRF từ random_bytes(32) phải có độ dài 64 ký tự hex.');
        $this->assertMatchesRegex('/^[a-f0-9]{64}$/', $token);
    }

    public function testCsrfFieldTaoTheInputAn(): void
    {
        $html = csrfField();
        $this->assertStringContains('<input type="hidden" name="csrf" value="', $html);
        $this->assertStringContains(csrfToken(), $html);
    }

    public function testCsrfHopLeXacThucChuanXac(): void
    {
        $dung = csrfToken();

        // 1. Gửi đúng mã trong $_POST
        $_POST['csrf'] = $dung;
        $this->assertTrue(csrfHopLe(), 'Mã CSRF đúng trong $_POST phải trả về true.');

        // 2. Gửi sai mã
        $_POST['csrf'] = 'ma_gia_mao_khong_hop_le_123456789';
        $this->assertFalse(csrfHopLe(), 'Mã CSRF sai phải bị từ chối.');

        // 3. Thiếu mã hoàn toàn
        unset($_POST['csrf']);
        $this->assertFalse(csrfHopLe(), 'Thiếu mã CSRF phải trả về false.');

        // 4. Gửi qua header HTTP_X_CSRF_TOKEN
        $_SERVER['HTTP_X_CSRF_TOKEN'] = $dung;
        $this->assertTrue(csrfHopLe(), 'Mã CSRF gửi qua HTTP_X_CSRF_TOKEN phải được chấp nhận.');
        unset($_SERVER['HTTP_X_CSRF_TOKEN']);
    }
}
