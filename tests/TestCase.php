<?php
/**
 * tests/TestCase.php
 * Lớp kiểm thử cơ sở (Base Test Case) cho bộ kiểm thử tự động của UniEvent.
 * Cung cấp hệ thống các phương thức khẳng định (Assertions) chuẩn mực.
 */

declare(strict_types=1);

namespace Tests;

use RuntimeException;
use Throwable;

abstract class TestCase
{
    protected int $assertionCount = 0;

    public function getAssertionCount(): int
    {
        return $this->assertionCount;
    }

    public function setUp(): void
    {
    }

    public function tearDown(): void
    {
    }

    protected function assertTrue(bool $condition, string $message = ''): void
    {
        $this->assertionCount++;
        if (!$condition) {
            throw new RuntimeException($message !== '' ? $message : 'Khẳng định thất bại: Giá trị mong đợi là TRUE nhưng nhận được FALSE.');
        }
    }

    protected function assertFalse(bool $condition, string $message = ''): void
    {
        $this->assertionCount++;
        if ($condition) {
            throw new RuntimeException($message !== '' ? $message : 'Khẳng định thất bại: Giá trị mong đợi là FALSE nhưng nhận được TRUE.');
        }
    }

    protected function assertEquals(mixed $expected, mixed $actual, string $message = ''): void
    {
        $this->assertionCount++;
        if ($expected !== $actual) {
            $msg = $message !== '' 
                ? $message 
                : sprintf("Khẳng định thất bại:\nMong đợi: %s\nNhận được: %s", var_export($expected, true), var_export($actual, true));
            throw new RuntimeException($msg);
        }
    }

    protected function assertNotEquals(mixed $expected, mixed $actual, string $message = ''): void
    {
        $this->assertionCount++;
        if ($expected === $actual) {
            $msg = $message !== '' 
                ? $message 
                : sprintf("Khẳng định thất bại: Hai giá trị bằng nhau khi mong đợi khác nhau: %s", var_export($actual, true));
            throw new RuntimeException($msg);
        }
    }

    protected function assertNull(mixed $actual, string $message = ''): void
    {
        $this->assertionCount++;
        if ($actual !== null) {
            $msg = $message !== '' 
                ? $message 
                : sprintf("Khẳng định thất bại: Mong đợi NULL nhưng nhận được %s", var_export($actual, true));
            throw new RuntimeException($msg);
        }
    }

    protected function assertNotNull(mixed $actual, string $message = ''): void
    {
        $this->assertionCount++;
        if ($actual === null) {
            $msg = $message !== '' ? $message : 'Khẳng định thất bại: Giá trị không được là NULL.';
            throw new RuntimeException($msg);
        }
    }

    protected function assertCount(int $expectedCount, array|\Countable $array, string $message = ''): void
    {
        $this->assertionCount++;
        $actualCount = count($array);
        if ($expectedCount !== $actualCount) {
            $msg = $message !== '' 
                ? $message 
                : sprintf("Khẳng định thất bại: Số lượng phần tử mong đợi %d, nhận được %d", $expectedCount, $actualCount);
            throw new RuntimeException($msg);
        }
    }

    protected function assertArrayHasKey(string|int $key, array $array, string $message = ''): void
    {
        $this->assertionCount++;
        if (!array_key_exists($key, $array)) {
            $msg = $message !== '' ? $message : sprintf("Khẳng định thất bại: Mảng không chứa khóa '%s'.", (string)$key);
            throw new RuntimeException($msg);
        }
    }

    protected function assertArrayNotHasKey(string|int $key, array $array, string $message = ''): void
    {
        $this->assertionCount++;
        if (array_key_exists($key, $array)) {
            $msg = $message !== '' ? $message : sprintf("Khẳng định thất bại: Mảng chứa khóa '%s' nhưng mong đợi không có.", (string)$key);
            throw new RuntimeException($msg);
        }
    }

    protected function assertStringContains(string $needle, string $haystack, string $message = ''): void
    {
        $this->assertionCount++;
        if (!str_contains($haystack, $needle)) {
            $msg = $message !== '' 
                ? $message 
                : sprintf("Khẳng định thất bại: Chuỗi '%s' không chứa đoạn con '%s'.", $haystack, $needle);
            throw new RuntimeException($msg);
        }
    }

    protected function assertMatchesRegex(string $pattern, string $string, string $message = ''): void
    {
        $this->assertionCount++;
        if (preg_match($pattern, $string) !== 1) {
            $msg = $message !== '' 
                ? $message 
                : sprintf("Khẳng định thất bại: Chuỗi '%s' không khớp biểu thức chính quy '%s'.", $string, $pattern);
            throw new RuntimeException($msg);
        }
    }

    protected function assertThrows(string $expectedException, callable $callback, string $message = ''): void
    {
        $this->assertionCount++;
        $threw = false;
        try {
            $callback();
        } catch (Throwable $e) {
            if ($e instanceof $expectedException) {
                $threw = true;
            } else {
                throw new RuntimeException(
                    sprintf("Mong đợi ngoại lệ %s nhưng nhận được %s: %s", $expectedException, get_class($e), $e->getMessage())
                );
            }
        }

        if (!$threw) {
            $msg = $message !== '' ? $message : sprintf("Khẳng định thất bại: Không có ngoại lệ %s nào được ném ra.", $expectedException);
            throw new RuntimeException($msg);
        }
    }
}
