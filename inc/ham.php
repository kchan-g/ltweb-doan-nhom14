<?php
/**
 * inc/ham.php
 * Tập hợp các hàm tiện ích dùng chung cho toàn bộ website UniEvent.
 * Mọi dữ liệu in ra màn hình HTML bắt buộc phải qua hàm e() để phòng chống tấn công XSS.
 */

declare(strict_types=1);

/**
 * Mã hóa an toàn chuỗi văn bản trước khi in ra HTML (chống XSS).
 *
 * @param string|int|float|null $chuoi Chuỗi cần in
 * @return string Chuỗi đã được mã hóa an toàn
 */
function e(mixed $chuoi): string
{
    return htmlspecialchars((string)($chuoi ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Định dạng số tiền sang định dạng VNĐ.
 *
 * @param int|float $so Số tiền
 * @return string Chuỗi định dạng ví dụ: "150.000 đ" hoặc "Miễn phí"
 */
function vnd(int|float $so): string
{
    if ($so <= 0) {
        return 'Miễn phí';
    }
    return number_format($so, 0, ',', '.') . ' đ';
}

/**
 * Lấy hoặc thiết lập thông báo Flash (chỉ hiển thị đúng một lần trong phiên).
 *
 * @param string|null $thongBao Thông báo cần lưu, để null nếu muốn đọc và xóa
 * @return string|null Thông báo được lấy ra hoặc null
 */
function flash(?string $thongBao = null): ?string
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if ($thongBao !== null) {
        $_SESSION['flash'] = $thongBao;
        return null;
    }

    $msg = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $msg;
}

/**
 * Kiểm tra xem người dùng hiện tại đã đăng nhập vào hệ thống hay chưa.
 *
 * @return bool True nếu đã đăng nhập
 */
function daDangNhap(): bool
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return !empty($_SESSION['user']);
}

/**
 * Lấy thông tin tài khoản đang đăng nhập.
 *
 * @return string|null Tên người dùng hoặc null
 */
function nguoiDungHienTai(): ?string
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return $_SESSION['user'] ?? null;
}

/**
 * Kiểm tra xem người dùng hiện tại có vai trò Quản trị viên (admin) hay không.
 */
function laAdmin(): bool
{
    if (!daDangNhap()) {
        return false;
    }
    return (($_SESSION['user_role'] ?? '') === 'admin');
}

/**
 * Kiểm tra xem người dùng hiện tại có vai trò Sinh viên (sinhvien) hay không.
 */
function laSinhVien(): bool
{
    if (!daDangNhap()) {
        return false;
    }
    return (($_SESSION['user_role'] ?? '') === 'sinhvien');
}

/**
 * Lấy vai trò của người dùng hiện tại ('admin', 'sinhvien' hoặc null).
 */
function vaiTroNguoiDung(): ?string
{
    if (!daDangNhap()) {
        return null;
    }
    return $_SESSION['user_role'] ?? 'sinhvien';
}

/**
 * Lấy tên hiển thị của người dùng hiện tại.
 */
function tenNguoiDung(): string
{
    if (!daDangNhap()) {
        return '';
    }
    return $_SESSION['user_name'] ?? $_SESSION['user'] ?? 'Người dùng';
}
