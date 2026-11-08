<?php
/**
 * tests/run_tests.php
 * Trình chạy kiểm thử tự động (Automated Test Runner) cho hệ thống UniEvent.
 * Chạy trực tiếp từ dòng lệnh: php tests/run_tests.php
 */

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/TestCase.php';

// Nạp các tệp Test trong thư mục tests/Unit
$testFiles = glob(__DIR__ . '/Unit/*Test.php');
if ($testFiles === false || empty($testFiles)) {
    echo "Không tìm thấy tệp kiểm thử nào trong thư mục tests/Unit/\n";
    exit(1);
}

foreach ($testFiles as $file) {
    require_once $file;
}

$startTime = microtime(true);
$totalTests = 0;
$totalAssertions = 0;
$passedCount = 0;
$failedCount = 0;
$failures = [];

// Thiết lập mã màu ANSI cho Terminal
$cReset  = "\033[0m";
$cGreen  = "\033[32m";
$cRed    = "\033[31m";
$cYellow = "\033[33m";
$cBlue   = "\033[34m";
$cCyan   = "\033[36m";
$cBold   = "\033[1m";

// Nếu trên Windows cmd không hỗ trợ ANSI thì bỏ mã màu nếu cần
if (DIRECTORY_SEPARATOR === '\\' && getenv('TERM') === false && getenv('ANSICON') === false && getenv('WT_SESSION') === false) {
    // Windows Terminal / PowerShell hiện đại đều hỗ trợ ANSI
}

echo "\n{$cBold}{$cCyan}========================================================================{$cReset}\n";
echo "{$cBold}{$cCyan}   HỆ THỐNG KIỂM THỬ TỰ ĐỘNG UNIEVENT - ĐH SƯ PHẠM ĐÀ NẴNG (UED)    {$cReset}\n";
echo "{$cCyan}========================================================================{$cReset}\n";
echo "PHP: " . PHP_VERSION . " | HĐH: " . PHP_OS_FAMILY . " | Thời gian: " . date('Y-m-d H:i:s') . "\n\n";

// Danh sách các lớp Test kế thừa từ Tests\TestCase
$declaredClasses = get_declared_classes();
$testClasses = array_filter($declaredClasses, function ($className) {
    return is_subclass_of($className, Tests\TestCase::class) && !(new ReflectionClass($className))->isAbstract();
});

foreach ($testClasses as $testClass) {
    $ref = new ReflectionClass($testClass);
    $methods = $ref->getMethods(ReflectionMethod::IS_PUBLIC);
    $shortName = $ref->getShortName();

    echo "{$cBold}• {$shortName}{$cReset}\n";

    foreach ($methods as $method) {
        $methodName = $method->getName();
        if (!str_starts_with($methodName, 'test')) {
            continue;
        }

        $totalTests++;
        /** @var Tests\TestCase $instance */
        $instance = new $testClass();

        try {
            $instance->setUp();
            $instance->$methodName();
            $instance->tearDown();

            $totalAssertions += $instance->getAssertionCount();
            $passedCount++;
            echo "   {$cGreen}✓ PASS{$cReset} - {$methodName}\n";
        } catch (Throwable $e) {
            $failedCount++;
            $totalAssertions += $instance->getAssertionCount();
            $failures[] = [
                'class'   => $shortName,
                'method'  => $methodName,
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ];
            echo "   {$cRed}✗ FAIL{$cReset} - {$methodName} ({$e->getMessage()})\n";
        }
    }
    echo "\n";
}

$duration = round(microtime(true) - $startTime, 4);

echo "{$cBold}{$cCyan}------------------------------------------------------------------------{$cReset}\n";
if ($failedCount === 0) {
    echo "{$cBold}{$cGreen} KẾT QUẢ: TOÀN BỘ {$passedCount} BÀI KIỂM THỬ ĐẠT CHUẨN (PASS){$cReset}\n";
} else {
    echo "{$cBold}{$cRed} KẾT QUẢ: CÓ {$failedCount} BÀI KIỂM THỬ THẤT BẠI!{$cReset}\n";
}
echo " Tổng số bài test: {$totalTests} | Tổng số assertions: {$totalAssertions} | Thời gian chạy: {$duration}s\n";
echo "{$cCyan}------------------------------------------------------------------------{$cReset}\n";

if (!empty($failures)) {
    echo "\n{$cBold}{$cRed}CHI TIẾT LỖI KIỂM THỬ:{$cReset}\n";
    foreach ($failures as $idx => $fail) {
        $stt = $idx + 1;
        echo "{$stt}) {$fail['class']}::{$fail['method']}\n";
        echo "   Lý do: {$fail['message']}\n";
        echo "   Tệp: {$fail['file']}:{$fail['line']}\n\n";
    }
    exit(1);
}

exit(0);
