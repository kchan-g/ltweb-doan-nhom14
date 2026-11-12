<?php
/**
 * inc/csrf.php
 * Module phòng chống tấn công CSRF (Cross-Site Request Forgery).
 * Sinh mã ngẫu nhiên an toàn bằng random_bytes(32) và so sánh an toàn thời gian bằng hash_equals.
 */

declare(strict_types=1);

/**
 * Lấy hoặc sinh mới mã CSRF token cho phiên làm việc hiện tại.
 */
function csrfToken(): string
{
    return $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
}

/**
 * Tạo thẻ input ẩn chứa mã CSRF để chèn vào mọi form method="POST".
 */
function csrfField(): string
{
    return '<input type="hidden" name="csrf" value="' . csrfToken() . '">';
}

/**
 * Kiểm tra tính hợp lệ của mã CSRF gửi lên từ form POST, header X-CSRF-Token hoặc thân JSON.
 */
function csrfHopLe(): bool
{
    // 1. Kiểm tra trong $_POST hoặc Header HTTP_X_CSRF_TOKEN
    $gui = $_POST['csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

    // 2. Nếu request dạng JSON chưa parse vào $_POST
    if ($gui === '' && str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'json')) {
        $raw = file_get_contents('php://input');
        if ($raw !== false && $raw !== '') {
            $data = json_decode($raw, true);
            if (is_array($data) && isset($data['csrf']) && is_string($data['csrf'])) {
                $gui = $data['csrf'];
            }
        }
    }

    return is_string($gui) && $gui !== '' && hash_equals(csrfToken(), $gui);
}
