<?php
/**
 * tests/Unit/BaoMatTest.php
 * Kiểm thử đơn vị cho các tính năng bảo mật: phòng chống XSS (hàm e),
 * phòng chống Open Redirect (chuyển hướng nguy hiểm), và hiển thị tiền tệ (hàm vnd).
 */

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;

require_once __DIR__ . '/../../inc/ham.php';

class BaoMatTest extends TestCase
{
    public function testHamEMaHoaChongXss(): void
    {
        $payload = '<script>alert("XSS")</script>';
        $anToan = e($payload);

        $this->assertFalse(str_contains($anToan, '<script>'));
        $this->assertEquals('&lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;', $anToan);
    }

    public function testHamEXuLyNullVaSo(): void
    {
        $this->assertEquals('', e(null));
        $this->assertEquals('12345', e(12345));
    }

    public function testChanChuyenHuongMoOpenRedirect(): void
    {
        // Danh sách các URL chuyển hướng tấn công độc hại
        $cacUrlDocHai = [
            '//evil.com',
            '//evil.com/phishing',
            'https://attacker.site',
            'http://malicious.org',
            'javascript:alert(1)',
            'data:text/html,<script>alert(1)</script>',
            '\\\\attacker.com\\share',
        ];

        foreach ($cacUrlDocHai as $url) {
            $ketQua = $this->kiemTraChuyenHuongAnToan($url);
            $this->assertEquals(
                'index.php',
                $ketQua,
                "URL độc hại '{$url}' phải bị chặn và chuyển về 'index.php'."
            );
        }
    }

    public function testChoPhepChuyenHuongNoiBoHopLe(): void
    {
        $cacUrlHopLe = [
            'gio-hang.php',
            'chi-tiet.php?id=5',
            'don-hang.php?id=12',
            'quan-tri.php',
        ];

        foreach ($cacUrlHopLe as $url) {
            $ketQua = $this->kiemTraChuyenHuongAnToan($url);
            $this->assertEquals(
                $url,
                $ketQua,
                "Đường dẫn nội bộ '{$url}' phải được chấp nhận."
            );
        }
    }

    public function testDinhDangTienVnd(): void
    {
        $this->assertEquals('Miễn phí', vnd(0));
        $this->assertEquals('Miễn phí', vnd(-5000));
        $this->assertEquals('50.000 đ', vnd(50000));
        $this->assertEquals('1.500.000 đ', vnd(1500000));
    }

    public function testHamChuoiLocThamSoMang(): void
    {
        // Chuỗi bình thường giữ nguyên
        $this->assertEquals('Hội thảo IT', chuoi('Hội thảo IT'));

        // Tham số mảng (như ?q[]=attack hoặc id[]...) phải lọc thành chuỗi rỗng
        $this->assertEquals('', chuoi(['phan_tu_1', 'phan_tu_2']));
        $this->assertEquals('', chuoi(['hack' => 'payload']));

        // Null hoặc kiểu khác không phải chuỗi -> chuỗi rỗng
        $this->assertEquals('', chuoi(null));
        $this->assertEquals('', chuoi(12345));
    }

    /**
     * Logic chuẩn hóa chuyển hướng an toàn được áp dụng trong dang-nhap.php
     */
    private function kiemTraChuyenHuongAnToan(string $url): string
    {
        $tiepTuc = trim($url);
        if (
            $tiepTuc === ''
            || str_starts_with($tiepTuc, '//')
            || str_starts_with($tiepTuc, '\\\\')
            || preg_match('/^(https?|ftp|javascript|data):/i', $tiepTuc) === 1
        ) {
            return 'index.php';
        }

        return $tiepTuc;
    }
}
