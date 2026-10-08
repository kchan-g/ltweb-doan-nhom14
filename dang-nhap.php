<?php
/**
 * dang-nhap.php
 * Cổng đăng nhập thống nhất dùng chung cho cả Quản trị viên và Sinh viên:
 * - Nhập tài khoản Quản trị (admin, hung) -> Tự động nhận diện quyền Quản trị và chuyển đến quan-tri.php
 * - Nhập email Sinh viên (@ued.udn.vn) -> Tự động nhận diện tài khoản Sinh viên và chuyển đến giao diện Người dùng (index.php)
 * - Mật khẩu được mã hóa an toàn qua password_verify, cấp Session ID mới chống Session Fixation.
 */

declare(strict_types=1);

require_once __DIR__ . '/inc/config.php';

// Nếu đã đăng nhập rồi -> chuyển hướng theo đúng vai trò
if (daDangNhap()) {
    if (laAdmin()) {
        header('Location: quan-tri.php');
    } else {
        header('Location: index.php');
    }
    exit;
}

$taiKhoanList = require __DIR__ . '/inc/tai-khoan.php';
$loi = null;
$tenDangNhap = '';
$tiepTuc = $_GET['tieptuc'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tenDangNhap = trim($_POST['username'] ?? '');
    $matKhau = (string)($_POST['password'] ?? '');
    $tiepTuc = trim($_POST['tieptuc'] ?? '');

    $hopLe = false;
    $vaiTro = 'sinhvien';
    $tenHienThi = '';
    $mssv = '';
    $khoa = '';

    // 1. Kiểm tra tài khoản trong danh sách hệ thống cấu hình sẵn
    if (isset($taiKhoanList[$tenDangNhap])) {
        $hash = $taiKhoanList[$tenDangNhap]['matKhau'];
        if (password_verify($matKhau, $hash)) {
            $hopLe = true;
            $vaiTro = $taiKhoanList[$tenDangNhap]['vaiTro'] ?? 'admin';
            $tenHienThi = $taiKhoanList[$tenDangNhap]['tenHienThi'] ?? $tenDangNhap;
            $mssv = $taiKhoanList[$tenDangNhap]['mssv'] ?? '';
            $khoa = $taiKhoanList[$tenDangNhap]['khoa'] ?? 'Khoa Toán - Tin';
        }
    }
    // 2. Hoặc đăng nhập bằng email sinh viên trường ĐH Sư Phạm (@ued.udn.vn)
    elseif (preg_match('/^[a-zA-Z0-9._%+-]+@ued\.udn\.vn$/i', $tenDangNhap)) {
        if (strlen($matKhau) >= 6) {
            $hopLe = true;
            $vaiTro = 'sinhvien';
            $phanDau = explode('@', $tenDangNhap)[0];
            if (preg_match('/^\d{8,10}$/', $phanDau, $m)) {
                $mssv = $m[0];
                $tenHienThi = 'Sinh viên ' . $mssv;
            } else {
                $mssv = 'UED-' . strtoupper(substr(md5($tenDangNhap), 0, 8));
                $parts = preg_split('/[._-]/', $phanDau);
                $tenHienThi = implode(' ', array_map('ucfirst', $parts));
            }
            $khoa = 'Khoa Toán - Tin';
        } else {
            $loi = 'Mật khẩu cổng sinh viên phải có tối thiểu 6 ký tự.';
        }
    }

    if ($hopLe) {
        // Cấp mã phiên làm việc mới chống tấn công Session Fixation
        session_regenerate_id(true);
        $_SESSION['user'] = $tenDangNhap;
        $_SESSION['user_name'] = $tenHienThi;
        $_SESSION['user_role'] = $vaiTro;
        $_SESSION['user_mssv'] = $mssv;
        $_SESSION['user_khoa'] = $khoa;

        if ($vaiTro === 'admin') {
            // Quản trị viên -> chuyển thẳng tới giao diện quản trị
            flash("Xin chào {$tenHienThi}! Bạn đã đăng nhập thành công với quyền Quản trị viên.");
            header('Location: quan-tri.php');
            exit;
        } else {
            // Sinh viên -> chuyển tới giao diện người dùng
            flash("Xin chào {$tenHienThi}! Bạn đã đăng nhập thành công vào UniEvent.");

            $dichDen = 'index.php';
            if (!empty($tiepTuc) && $tiepTuc !== 'quan-tri.php' && !str_starts_with($tiepTuc, 'http')) {
                if (str_starts_with($tiepTuc, '/') || preg_match('/^[a-zA-Z0-9_\-\.\/]+\.php(\?.*)?$/', $tiepTuc)) {
                    $dichDen = $tiepTuc;
                }
            }

            // Đồng bộ sang localStorage cho các thành phần client-side của sinh viên rồi chuyển hướng
            ?>
            <!DOCTYPE html>
            <html lang="vi">
            <head>
              <meta charset="UTF-8">
              <title>Đăng nhập thành công - UniEvent</title>
              <script>
                try {
                  localStorage.setItem('unievent_ued_sinhvien', JSON.stringify({
                    email: <?= json_encode($tenDangNhap) ?>,
                    hoTen: <?= json_encode($tenHienThi) ?>,
                    mssv: <?= json_encode($mssv) ?>,
                    khoa: <?= json_encode($khoa) ?>,
                    thoiGianDangNhap: new Date().toISOString()
                  }));
                } catch(e) {}
                window.location.href = <?= json_encode($dichDen) ?>;
              </script>
            </head>
            <body>
              <p style="font-family:sans-serif; text-align:center; padding:50px;">
                Đăng nhập sinh viên thành công! Đang chuyển đến giao diện người dùng...
                <a href="<?= e($dichDen) ?>">Nhấn vào đây nếu không tự chuyển</a>.
              </p>
            </body>
            </html>
            <?php
            exit;
        }
    } else {
        if ($loi === null) {
            $loi = 'Sai tên đăng nhập/email hoặc mật khẩu. Vui lòng kiểm tra lại.';
        }
        error_log(sprintf(
            "[%s] CẢNH BÁO BẢO MẬT: Đăng nhập thất bại với tài khoản '%s' từ IP %s\n",
            date('Y-m-d H:i:s'),
            $tenDangNhap,
            $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
        ));
    }
}

$tieuDe = 'Đăng nhập hệ thống - UniEvent';
$trang  = 'dang-nhap';

require __DIR__ . '/inc/header.php';
?>

<main>
  <div class="container">
    <div style="max-width: 480px; margin: 40px auto;" class="form-card">
      <div class="text-center mb-4">
        <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">🔐</div>
        <span class="badge badge--xanh mb-2">CỔNG ĐĂNG NHẬP CHUNG</span>
        <h1 class="h3 mb-2" style="color: var(--mau-chinh-toi);">Đăng Nhập UniEvent</h1>
        <p class="fs-meta text-phu mb-0">
          Dành cho Sinh viên UED (<code>@ued.udn.vn</code>) và Quản trị viên hệ thống
        </p>
      </div>

      <?php if ($loi !== null): ?>
        <div class="hop-thong-bao hop-thong-bao--loi mb-4" role="alert">
          <span class="hop-thong-bao__icon" aria-hidden="true">⚠️</span>
          <div>
            <p class="mb-0"><?= e($loi) ?></p>
          </div>
        </div>
      <?php endif; ?>

      <form action="dang-nhap.php" method="POST">
        <input type="hidden" name="tieptuc" value="<?= e($tiepTuc) ?>">

        <div class="form-nhom mb-3">
          <label for="username">
            Tên đăng nhập hoặc Email sinh viên UED <span style="color:red;">*</span>
          </label>
          <input
            type="text"
            id="username"
            name="username"
            value="<?= e($tenDangNhap) ?>"
            placeholder="admin hoặc 3120224065@ued.udn.vn"
            required
            autofocus
          />
        </div>

        <div class="form-nhom mb-4">
          <label for="password">
            Mật khẩu <span style="color:red;">*</span>
          </label>
          <input
            type="password"
            id="password"
            name="password"
            placeholder="Nhập mật khẩu (tối thiểu 6 ký tự)"
            required
            minlength="6"
          />
        </div>

        <button type="submit" class="nut-bam nut-nhan nut-full py-2" style="font-size: 1rem; font-weight: 700;">
          Đăng Nhập Vào Hệ Thống →
        </button>
      </form>

      <!-- Khung hướng dẫn tự động chuyển giao diện theo vai trò -->
      <div class="mt-4 p-3 fs-meta" style="background:var(--nen-surface); border:1px dashed var(--border-nhat); border-radius:var(--radius-vua); color:var(--chu-phu);">
        <strong style="color:var(--chu-chinh);">💡 Hướng dẫn nhận diện tài khoản:</strong>
        <ul class="mb-0 mt-2 ps-3" style="line-height:1.7;">
          <li>
            <strong>Tài khoản Quản trị viên:</strong> Nhập <code>admin</code> hoặc <code>hung</code> (Mật khẩu: <code>123456</code>) → Hệ thống tự động chuyển vào <strong>Giao diện Quản trị</strong> (<code>quan-tri.php</code>).
          </li>
          <li>
            <strong>Tài khoản Sinh viên:</strong> Nhập email sinh viên UED như <code>3120224065@ued.udn.vn</code> hoặc bất kỳ email <code>@ued.udn.vn</code> (Mật khẩu: <code>123456</code>) → Hệ thống tự động chuyển vào <strong>Giao diện Người dùng</strong> (<code>index.php</code>).
          </li>
        </ul>
      </div>
    </div>
  </div>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>
