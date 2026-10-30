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
 * Lấy thông tin tài khoản đang đăng nhập (tên hoặc email).
 *
 * @return string|null Tên người dùng hoặc null
 */
function nguoiDungHienTai(): ?string
{
    $nd = nguoiDung();
    return $nd ? ($nd['ho_ten'] ?? $nd['email']) : null;
}

/**
 * Kiểm tra xem người dùng hiện tại có vai trò Quản trị viên (quan_tri) hay không.
 */
function laAdmin(): bool
{
    return laQuanTri();
}

/**
 * Kiểm tra xem người dùng hiện tại có vai trò Sinh viên / Khách hàng hay không.
 */
function laSinhVien(): bool
{
    $nd = nguoiDung();
    return $nd !== null && ($nd['vai_tro'] ?? '') === 'khach_hang';
}

/**
 * Lấy vai trò của người dùng hiện tại ('quan_tri', 'khach_hang' hoặc null).
 */
function vaiTroNguoiDung(): ?string
{
    $nd = nguoiDung();
    return $nd ? ($nd['vai_tro'] ?? null) : null;
}

/**
 * Lấy tên hiển thị của người dùng hiện tại.
 */
function tenNguoiDung(): string
{
    $nd = nguoiDung();
    if ($nd !== null) {
        return (string)($nd['ho_ten'] ?? $nd['email'] ?? 'Người dùng');
    }
    return '';
}


/**
 * Trả về mã SVG Icon chuyên nghiệp, sắc nét, đồng bộ cho toàn bộ giao diện (thay thế emoji OS).
 *
 * @param string $ten Tên biểu tượng
 * @param string $lop Lớp CSS bổ sung (mặc định: '')
 * @param int $co Kích thước icon pixel (mặc định: 16)
 * @return string Chuỗi thẻ <svg> an toàn
 */
function uiIcon(string $ten, string $lop = '', int $co = 16): string
{
    $cacIcon = [
        'building'   => '<path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/><path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/><path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/><path d="M10 6h4"/><path d="M10 10h4"/><path d="M10 14h4"/><path d="M10 18h4"/>',
        'pin'        => '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>',
        'phone'      => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>',
        'mail'       => '<rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>',
        'calendar'   => '<rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/>',
        'clock'      => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
        'star'       => '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',
        'ticket'     => '<path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"/><path d="M13 5v2"/><path d="M13 17v2"/><path d="M13 11v2"/>',
        'users'      => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        'user'       => '<circle cx="12" cy="7" r="4"/><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>',
        'shield'     => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
        'sun'        => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/>',
        'moon'       => '<path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/>',
        'check'      => '<polyline points="20 6 9 17 4 12"/>',
        'cap'        => '<path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/>',
        'trophy'     => '<path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/><path d="M4 22h16"/><path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"/><path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/><path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/>',
        'art'        => '<circle cx="8" cy="18" r="4"/><path d="M12 18V2l7 4"/>',
        'heart'      => '<path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>',
        'grid'       => '<rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/>',
        'box'        => '<path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>',
        'arrow-right'=> '<path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>',
        'facebook'   => '<path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>',
        'youtube'    => '<path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z"/><polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02"/>',
        'twitter'    => '<path d="M22 4s-.7 2.1-2 3.4c1.6 10-9.4 17.3-18 11.6 2.2.1 4.4-.6 6-2C3 15.5.5 9.6 3 5c2.2 2.6 5.6 4.1 9 4-.9-4.2 4-6.6 7-3.8 1.1 0 3-1.2 3-1.2z"/>',
        'external'   => '<path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" x2="21" y1="14" y2="3"/>',
        'bookmark'   => '<path d="m19 21-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16z"/>',
        'menu'       => '<line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/>',
        'logout'     => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/>',
        'dashboard'  => '<rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/>',
    ];

    $path = $cacIcon[$ten] ?? '';
    if ($path === '') {
        return '';
    }

    $lopClass = trim('ui-icon ' . $lop);
    return sprintf(
        '<svg class="%s" width="%d" height="%d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">%s</svg>',
        htmlspecialchars($lopClass, ENT_QUOTES, 'UTF-8'),
        $co,
        $co,
        $path
    );
}
