<?php
declare(strict_types=1);

require_once __DIR__ . '/inc/config.php';

if (daDangNhap()) {
    header('Location: index.php');
    exit;
}

$du = [
    'ho_ten'        => '',
    'email'         => '',
    'so_dien_thoai' => '',
];
$loi = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $du['ho_ten']        = trim((string)($_POST['ho_ten'] ?? ''));
    $du['email']         = trim((string)($_POST['email'] ?? ''));
    $du['so_dien_thoai'] = trim((string)($_POST['so_dien_thoai'] ?? ''));
    $matKhau             = (string)($_POST['password'] ?? '');
    $xacNhanMatKhau      = (string)($_POST['repassword'] ?? '');

    if ($du['ho_ten'] === '' || mb_strlen($du['ho_ten']) < 2 || mb_strlen($du['ho_ten']) > 100) {
        $loi['ho_ten'] = 'Họ và tên phải từ 2 đến 100 ký tự.';
    }

    if ($du['email'] === '') {
        $loi['email'] = 'Vui lòng nhập địa chỉ email.';
    } elseif (!filter_var($du['email'], FILTER_VALIDATE_EMAIL)) {
        $loi['email'] = 'Địa chỉ email không đúng định dạng.';
    }

    if ($du['so_dien_thoai'] !== '' && !preg_match('/^0[0-9]{9}$/', $du['so_dien_thoai'])) {
        $loi['so_dien_thoai'] = 'Số điện thoại phải gồm 10 chữ số bắt đầu bằng số 0.';
    }

    if (strlen($matKhau) < 8) {
        $loi['password'] = 'Mật khẩu phải có độ dài tối thiểu 8 ký tự.';
    } elseif ($matKhau !== $xacNhanMatKhau) {
        $loi['repassword'] = 'Xác nhận mật khẩu không khớp.';
    }

    if (empty($loi)) {
        $emailChuan = mb_strtolower($du['email'], 'UTF-8');
        $hashMatKhau = password_hash($matKhau, PASSWORD_DEFAULT);
        $vaiTro = 'khach_hang';

        try {
            $st = db()->prepare(
                'INSERT INTO nguoi_dung (email, ho_ten, mat_khau, vai_tro, so_dien_thoai, ngay_tao)
                 VALUES (?, ?, ?, ?, ?, NOW())'
            );
            $st->execute([
                $emailChuan,
                $du['ho_ten'],
                $hashMatKhau,
                $vaiTro,
                $du['so_dien_thoai'] !== '' ? $du['so_dien_thoai'] : null,
            ]);

            $uid = (int)db()->lastInsertId();

            session_regenerate_id(true);
            $_SESSION['uid'] = $uid;

            flash("🎉 Đăng ký tài khoản thành công! Chào mừng bạn gia nhập UniEvent.");
            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            if ($e->errorInfo[1] === 1062 || (int)$e->getCode() === 23000) {
                $loi['email'] = 'Địa chỉ email này đã được đăng ký trong hệ thống.';
            } else {
                error_log('Lỗi đăng ký tài khoản: ' . $e->getMessage());
                $loi['he_thong'] = 'Không thể tạo tài khoản lúc này. Vui lòng thử lại sau.';
            }
        }
    }
}

$tieuDe = 'Đăng ký tài khoản - UniEvent';
$trang  = 'dang-ky';

require __DIR__ . '/inc/header.php';
?>

<main class="vung-chinh noi-dung-chinh container py-5">
  <div style="max-width: 500px; margin: 30px auto; background: var(--nen-surface); border: 1px solid var(--border-nhat); border-radius: 20px; padding: 40px; box-shadow: var(--shadow-card);">

    <div class="text-center mb-4">
      <div style="font-size: 2.8rem; line-height: 1; margin-bottom: 0.75rem;">📝</div>
      <h1 class="h3 mb-2 font-hien-thi fw-800" style="color: var(--mau-chinh-toi);">Đăng Ký Tài Khoản</h1>
      <p class="text-phu fs-meta" style="font-size: 0.9rem;">
        Tạo tài khoản sinh viên để đăng ký vé sự kiện và tích lũy điểm rèn luyện
      </p>
    </div>

    <?php if (isset($loi['he_thong'])): ?>
      <div class="hop-thong-bao mb-3" role="alert" style="background:rgba(239,68,68,0.12); border:1px solid rgba(239,68,68,0.3); color:#ef4444; padding:12px 16px; border-radius:10px; font-size:0.875rem;">
        <?= e($loi['he_thong']) ?>
      </div>
    <?php endif; ?>

    <form action="dang-ky.php" method="POST" novalidate>
      <div class="form-group mb-3">
        <label for="ho_ten" class="form-label fw-bold mb-2" style="font-size: 0.875rem; color:var(--chu-chinh);">
          Họ và tên của bạn <span class="bat-buoc" style="color:#ef4444;">*</span>
        </label>
        <input
          type="text"
          id="ho_ten"
          name="ho_ten"
          class="form-control <?= isset($loi['ho_ten']) ? 'is-invalid' : '' ?>"
          placeholder="Ví dụ: Nguyễn Văn A"
          value="<?= e($du['ho_ten']) ?>"
          style="width:100%; height:46px; padding:0.5rem 1rem; border:1.5px solid var(--border-nhat); border-radius:10px; font-size:0.9rem;"
          required
        />
        <?php if (isset($loi['ho_ten'])): ?>
          <div class="thong-bao-loi" style="color:#dc2626; font-size:0.8rem; margin-top:5px; font-weight:500;">
            <?= e($loi['ho_ten']) ?>
          </div>
        <?php endif; ?>
      </div>

      <div class="form-group mb-3">
        <label for="email" class="form-label fw-bold mb-2" style="font-size: 0.875rem; color:var(--chu-chinh);">
          Địa chỉ Email <span class="bat-buoc" style="color:#ef4444;">*</span>
        </label>
        <input
          type="email"
          id="email"
          name="email"
          class="form-control <?= isset($loi['email']) ? 'is-invalid' : '' ?>"
          placeholder="Ví dụ: sinhvien@ued.vn"
          value="<?= e($du['email']) ?>"
          style="width:100%; height:46px; padding:0.5rem 1rem; border:1.5px solid var(--border-nhat); border-radius:10px; font-size:0.9rem;"
          required
        />
        <?php if (isset($loi['email'])): ?>
          <div class="thong-bao-loi" style="color:#dc2626; font-size:0.8rem; margin-top:5px; font-weight:500;">
            <?= e($loi['email']) ?>
          </div>
        <?php endif; ?>
      </div>

      <div class="form-group mb-3">
        <label for="so_dien_thoai" class="form-label fw-bold mb-2" style="font-size: 0.875rem; color:var(--chu-chinh);">
          Số điện thoại liên hệ
        </label>
        <input
          type="tel"
          id="so_dien_thoai"
          name="so_dien_thoai"
          class="form-control <?= isset($loi['so_dien_thoai']) ? 'is-invalid' : '' ?>"
          placeholder="Ví dụ: 0905123456"
          value="<?= e($du['so_dien_thoai']) ?>"
          style="width:100%; height:46px; padding:0.5rem 1rem; border:1.5px solid var(--border-nhat); border-radius:10px; font-size:0.9rem;"
        />
        <?php if (isset($loi['so_dien_thoai'])): ?>
          <div class="thong-bao-loi" style="color:#dc2626; font-size:0.8rem; margin-top:5px; font-weight:500;">
            <?= e($loi['so_dien_thoai']) ?>
          </div>
        <?php endif; ?>
      </div>

      <div class="form-group mb-3">
        <label for="password" class="form-label fw-bold mb-2" style="font-size: 0.875rem; color:var(--chu-chinh);">
          Mật khẩu (tối thiểu 8 ký tự) <span class="bat-buoc" style="color:#ef4444;">*</span>
        </label>
        <input
          type="password"
          id="password"
          name="password"
          class="form-control <?= isset($loi['password']) ? 'is-invalid' : '' ?>"
          placeholder="Nhập mật khẩu ít nhất 8 ký tự"
          style="width:100%; height:46px; padding:0.5rem 1rem; border:1.5px solid var(--border-nhat); border-radius:10px; font-size:0.9rem;"
          required
        />
        <?php if (isset($loi['password'])): ?>
          <div class="thong-bao-loi" style="color:#dc2626; font-size:0.8rem; margin-top:5px; font-weight:500;">
            <?= e($loi['password']) ?>
          </div>
        <?php endif; ?>
      </div>

      <div class="form-group mb-4">
        <label for="repassword" class="form-label fw-bold mb-2" style="font-size: 0.875rem; color:var(--chu-chinh);">
          Xác nhận lại mật khẩu <span class="bat-buoc" style="color:#ef4444;">*</span>
        </label>
        <input
          type="password"
          id="repassword"
          name="repassword"
          class="form-control <?= isset($loi['repassword']) ? 'is-invalid' : '' ?>"
          placeholder="Nhập lại mật khẩu phía trên"
          style="width:100%; height:46px; padding:0.5rem 1rem; border:1.5px solid var(--border-nhat); border-radius:10px; font-size:0.9rem;"
          required
        />
        <?php if (isset($loi['repassword'])): ?>
          <div class="thong-bao-loi" style="color:#dc2626; font-size:0.8rem; margin-top:5px; font-weight:500;">
            <?= e($loi['repassword']) ?>
          </div>
        <?php endif; ?>
      </div>

      <button type="submit" class="nut-bam nut-nhan nut-full py-3" style="font-size: 1rem; font-weight: 700; width:100%;">
        Đăng Ký Tài Khoản
      </button>

      <div class="mt-4 pt-3 text-center" style="border-top: 1px dashed var(--border-nhat); font-size: 0.875rem; color: #64748b;">
        Đã có tài khoản trên hệ thống?
        <a href="dang-nhap.php" class="fw-bold" style="color: var(--mau-chinh); text-decoration: none;">Đăng nhập ngay</a>
      </div>
    </form>
  </div>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>
