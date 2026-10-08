<?php
// inc/tai-khoan.php — Danh sách tài khoản người dùng hệ thống UniEvent
declare(strict_types=1);

return [
    // ---------------------------------------------------------
    // 1. TÀI KHOẢN QUẢN TRỊ VIÊN (ADMIN)
    // ---------------------------------------------------------
    'admin' => [
        'ten'           => 'Quản trị viên UniEvent',
        'mat_khau'      => password_hash('admin123', PASSWORD_BCRYPT),
        'vai_tro'       => 'admin',
        'email'         => 'admin@ued.udn.vn',
    ],
    'trang' => [
        'ten'           => 'Nguyễn Thị Kiều Trang (Admin)',
        'mat_khau'      => password_hash('trang123', PASSWORD_BCRYPT),
        'vai_tro'       => 'admin',
        'email'         => '3120224153@mssv.ued.udn.vn',
        'mssv'          => '3120224153',
    ],

    // ---------------------------------------------------------
    // 2. TÀI KHOẢN SINH VIÊN (SINH_VIEN) — Email chuẩn: mssv@mssv.ued.udn.vn
    // ---------------------------------------------------------
    'sinhvien' => [
        'ten'           => 'Nguyễn Văn A',
        'mat_khau'      => password_hash('sinhvien123', PASSWORD_BCRYPT),
        'vai_tro'       => 'sinh_vien',
        'email'         => '1234567899@mssv.ued.udn.vn',
        'mssv'          => '1234567899',
        'lop'           => '24CNTT2',
        'diem_ren_luyen'=> 90,
    ],
    '3120224153' => [
        'ten'           => 'Nguyễn Thị Kiều Trang',
        'mat_khau'      => password_hash('12345678', PASSWORD_BCRYPT),
        'vai_tro'       => 'sinh_vien',
        'email'         => '3120224153@mssv.ued.udn.vn',
        'mssv'          => '3120224153',
        'lop'           => '24CNTT2',
        'diem_ren_luyen'=> 90,
    ],
    '3120224065' => [
        'ten'           => 'Đinh Trịnh Ngọc Hưng',
        'mat_khau'      => password_hash('12345678', PASSWORD_BCRYPT),
        'vai_tro'       => 'sinh_vien',
        'email'         => '3120224065@mssv.ued.udn.vn',
        'mssv'          => '3120224065',
        'lop'           => '24CNTT2',
        'diem_ren_luyen'=> 92,
    ],
    '3120224011' => [
        'ten'           => 'Nguyễn Hoài Bảo',
        'mat_khau'      => password_hash('12345678', PASSWORD_BCRYPT),
        'vai_tro'       => 'sinh_vien',
        'email'         => '3120224011@mssv.ued.udn.vn',
        'mssv'          => '3120224011',
        'lop'           => '24CNTT2',
        'diem_ren_luyen'=> 88,
    ],
    '3120224106' => [
        'ten'           => 'Phan Nhuận',
        'mat_khau'      => password_hash('12345678', PASSWORD_BCRYPT),
        'vai_tro'       => 'sinh_vien',
        'email'         => '3120224106@mssv.ued.udn.vn',
        'mssv'          => '3120224106',
        'lop'           => '24CNTT2',
        'diem_ren_luyen'=> 89,
    ],
    '3120224024' => [
        'ten'           => 'Lê Phú Đạt',
        'mat_khau'      => password_hash('12345678', PASSWORD_BCRYPT),
        'vai_tro'       => 'sinh_vien',
        'email'         => '3120224024@mssv.ued.udn.vn',
        'mssv'          => '3120224024',
        'lop'           => '24CNTT2',
        'diem_ren_luyen'=> 87,
    ]
];