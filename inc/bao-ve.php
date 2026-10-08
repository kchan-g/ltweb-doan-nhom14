<?php
/**
 * inc/bao-ve.php
 * Tệp kiểm tra quyền truy cập bảo vệ các trang quản trị (quan-tri.php).
 * Bắt buộc người dùng phải đăng nhập hợp lệ mới được tiếp tục,
 * nếu chưa sẽ chuyển hướng sang dang-nhap.php kèm tham số quay lại.
 */

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$goc ??= '';

if (empty($_SESSION['user'])) {
    $uriHienTai = $_SERVER['REQUEST_URI'] ?? 'quan-tri.php';
    header('Location: ' . $goc . 'dang-nhap.php?tieptuc=' . urlencode($uriHienTai));
    exit;
}

// Nếu đã đăng nhập nhưng không phải quản trị viên -> chuyển về giao diện người dùng
if (($_SESSION['user_role'] ?? '') !== 'admin') {
    flash('Tài khoản sinh viên không có quyền truy cập vào giao diện Quản trị.');
    header('Location: ' . $goc . 'index.php');
    exit;
}
