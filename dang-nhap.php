<?php
// dang-nhap.php — Xử lý xác thực người dùng (Admin & Sinh viên)
declare(strict_types=1);

require_once __DIR__ . '/inc/config.php';

// Nếu đã đăng nhập, điều hướng về đúng trang theo vai trò
if (!empty($_SESSION['nguoi_dung'])) {
    $dichDen = ($_SESSION['nguoi_dung']['vai_tro'] === 'admin') ? 'quan-tri.php' : 'index.php';
    header("Location: $dichDen");
    exit;
}

$loi = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dinhDanh = trim((string)($_POST['tai_khoan'] ?? ''));
    $matKhau  = (string)($_POST['mat_khau'] ?? '');

    $danhSachTK = require __DIR__ . '/inc/tai-khoan.php';
    $taiKhoanHopLe = null;

    // Tìm kiếm tài khoản theo: key (username), mssv hoặc email
    foreach ($danhSachTK as $userKey => $info) {
        $khopKey   = ($userKey === $dinhDanh);
        $khopMssv  = (isset($info['mssv']) && $info['mssv'] === $dinhDanh);
        $khopEmail = (isset($info['email']) && strcasecmp($info['email'], $dinhDanh) === 0);

        if ($khopKey || $khopMssv || $khopEmail) {
            $taiKhoanHopLe = $info;
            $taiKhoanHopLe['tai_khoan'] = $userKey;
            break;
        }
    }

    if ($taiKhoanHopLe && password_verify($matKhau, $taiKhoanHopLe['mat_khau'])) {
        // Cấp lại ID phiên ngăn ngừa tấn công Session Fixation
        session_regenerate_id(true);

        $_SESSION['nguoi_dung'] = [
            'tai_khoan'      => $taiKhoanHopLe['tai_khoan'],
            'ten'            => $taiKhoanHopLe['ten'],
            'vai_tro'        => $taiKhoanHopLe['vai_tro'],
            'email'          => $taiKhoanHopLe['email'] ?? '',
            'mssv'           => $taiKhoanHopLe['mssv'] ?? '',
            'lop'            => $taiKhoanHopLe['lop'] ?? '',
            'diem_ren_luyen' => $taiKhoanHopLe['diem_ren_luyen'] ?? 0,
        ];

        // Ưu tiên quay lại trang trước đó nếu là admin truy cập trang bảo vệ
        if ($taiKhoanHopLe['vai_tro'] === 'admin') {
            $chuyenHuong = $_SESSION['redirect_url'] ?? 'quan-tri.php';
        } else {
            $chuyenHuong = 'index.php';
        }

        unset($_SESSION['redirect_url']);
        header("Location: $chuyenHuong");
        exit;
    } else {
        // Báo một thông báo chung để chống dò tài khoản
        $loi = 'Sai tên đăng nhập, email hoặc mật khẩu.';

        // Ghi nhật ký bảo mật vào logs/php-error.log
        $dongLog = sprintf(
            "[%s] Cảnh báo xác thực: Thất bại với định danh '%s' từ IP %s\n",
            date('Y-m-d H:i:s'),
            $dinhDanh,
            $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
        );
        @file_put_contents(__DIR__ . '/logs/php-error.log', $dongLog, FILE_APPEND | LOCK_EX);
    }
}

$tieuDe = 'Đăng nhập - UniEvent';
$trang  = 'dang-nhap';
require __DIR__ . '/inc/header.php';
?>

<main class="vung-chinh container py-5" style="max-width: 480px; margin: 0 auto;">
    <div class="card p-4 shadow-sm border rounded bg-white">
        <h1 class="h3 fw-bold text-center mb-3" style="color: var(--mau-chinh, #123b6d);">Đăng nhập UniEvent</h1>
        <p class="text-muted small text-center mb-4">Cổng thông tin sự kiện sinh viên Trường ĐH Sư Phạm – ĐH Đà Nẵng</p>

        <?php if ($loi !== ''): ?>
            <div class="alert alert-danger" style="background:#fee2e2; color:#b91c1c; padding:10px 14px; border-radius:6px; margin-bottom:16px; border:1px solid #fca5a5;">
                ⚠️ <?= e($loi) ?>
            </div>
        <?php endif; ?>

        <form action="dang-nhap.php" method="POST">
            <div class="mb-3">
                <label for="tai_khoan" class="form-label fw-bold small">Tài khoản / MSSV / Email sinh viên:</label>
                <input type="text" id="tai_khoan" name="tai_khoan" class="form-control" 
                       value="<?= e($_POST['tai_khoan'] ?? '') ?>" 
                       placeholder="Ví dụ: admin hoặc 3120224153@mssv.ued.udn.vn" required autofocus 
                       style="width:100%; box-sizing:border-box; padding:8px 12px; border:1px solid #cbd5e1; border-radius:6px;">
            </div>

            <div class="mb-4">
                <label for="mat_khau" class="form-label fw-bold small">Mật khẩu:</label>
                <input type="password" id="mat_khau" name="mat_khau" class="form-control" required 
                       style="width:100%; box-sizing:border-box; padding:8px 12px; border:1px solid #cbd5e1; border-radius:6px;">
            </div>

            <button type="submit" class="nut-bam nut-nhan w-100 py-2" style="width:100%; cursor:pointer; font-weight:600;">
                Đăng nhập
            </button>
        </form>

        <div class="mt-4 pt-3 border-top text-muted small">
            <strong>Gợi ý tài khoản thử nghiệm:</strong>
            <ul style="padding-left: 20px; margin-top: 6px; margin-bottom: 0;">
                <li><strong>Quản trị viên:</strong> <code>admin</code> (Mật khẩu: <code>admin123</code>)</li>
                <li><strong>Sinh viên (Trang):</strong> <code>3120224153</code> hoặc <code>3120224153@mssv.ued.udn.vn</code> (Mật khẩu: <code>12345678</code>)</li>
                <li><strong>Sinh viên (Mẫu):</strong> <code>sinhvien</code> (Mật khẩu: <code>sinhvien123</code>)</li>
            </ul>
        </div>
    </div>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>