<?php
/**
 * inc/tai-khoan.php
 * Danh sách tài khoản thử nghiệm của website UniEvent.
 * Mật khẩu luôn được lưu dưới dạng băm an toàn bằng hàm password_hash().
 */

declare(strict_types=1);

return [
    // 1. TÀI KHOẢN QUẢN TRỊ VIÊN -> Chuyển vào giao diện quản trị (quan-tri.php)
    'admin' => [
        'matKhau' => password_hash('123456', PASSWORD_DEFAULT),
        'tenHienThi' => 'Quản trị viên UniEvent',
        'vaiTro' => 'admin',
    ],
    'hung' => [
        'matKhau' => password_hash('123456', PASSWORD_DEFAULT),
        'tenHienThi' => 'Đinh Trịnh Ngọc Hưng (Admin)',
        'vaiTro' => 'admin',
    ],

    // 2. TÀI KHOẢN SINH VIÊN -> Chuyển vào giao diện người dùng (index.php)
    '3120224065@ued.udn.vn' => [
        'matKhau' => password_hash('123456', PASSWORD_DEFAULT),
        'tenHienThi' => 'Đinh Trịnh Ngọc Hưng',
        'mssv' => '3120224065',
        'khoa' => 'Khoa Toán - Tin',
        'vaiTro' => 'sinhvien',
    ],
    'sinhvien@ued.udn.vn' => [
        'matKhau' => password_hash('123456', PASSWORD_DEFAULT),
        'tenHienThi' => 'Nguyễn Văn A',
        'mssv' => '3120220001',
        'khoa' => 'Khoa Sư phạm KHTN',
        'vaiTro' => 'sinhvien',
    ],
];
