<?php
declare(strict_types=1);

require_once __DIR__ . '/inc/config.php';

if (daDangNhap()) {
    if (laQuanTri()) {
        header('Location: quan-tri.php');
    } else {
        header('Location: index.php');
    }
    exit;
}

$loi = null;
$email = '';
$tiepTuc = trim((string)($_GET['tieptuc'] ?? ''));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string)($_POST['email'] ?? ''));
    $matKhau = (string)($_POST['password'] ?? '');
    $tiepTuc = trim((string)($_POST['tieptuc'] ?? ''));

    if ($email === '' || $matKhau === '') {
        $loi = 'Vui lòng nhập đầy đủ địa chỉ email và mật khẩu.';
    } else {
        try {
            $st = db()->prepare('SELECT id, email, ho_ten, mat_khau, vai_tro FROM nguoi_dung WHERE email = ?');
            $st->execute([$email]);
            $nd = $st->fetch();

            if ($nd === false || !password_verify($matKhau, (string)$nd['mat_khau'])) {
                error_log(sprintf('[%s] Cảnh báo: Đăng nhập thất bại cho email "%s"', date('Y-m-d H:i:s'), $email));
                $loi = 'Email hoặc mật khẩu không chính xác.';
            } else {
                if (password_needs_rehash((string)$nd['mat_khau'], PASSWORD_DEFAULT)) {
                    $hashMoi = password_hash($matKhau, PASSWORD_DEFAULT);
                    $stRehash = db()->prepare('UPDATE nguoi_dung SET mat_khau = ? WHERE id = ?');
                    $stRehash->execute([$hashMoi, $nd['id']]);
                }

                session_regenerate_id(true);
                $_SESSION['uid'] = (int)$nd['id'];

                flash("Đăng nhập thành công! Chào mừng " . $nd['ho_ten'] . ".");

                if (!empty($tiepTuc) && !str_starts_with($tiepTuc, 'http')) {
                    header('Location: ' . $tiepTuc);
                } elseif (($nd['vai_tro'] ?? '') === 'quan_tri') {
                    header('Location: quan-tri.php');
                } else {
                    header('Location: index.php');
                }
                exit;
            }
        } catch (Throwable $e) {
            error_log('Lỗi truy vấn đăng nhập: ' . $e->getMessage());
            $loi = 'Đã xảy ra sự cố kết nối máy chủ. Vui lòng thử lại sau.';
        }
    }
}

$thongBaoFlash = flash();
$tieuDe = 'Đăng nhập hệ thống - UniEvent';
$trang  = 'dang-nhap';

require __DIR__ . '/inc/header.php';
?>

<main class="vung-chinh noi-dung-chinh container py-5">
  <div style="max-width: 460px; margin: 40px auto; background: var(--nen-surface); border: 1px solid var(--border-nhat); border-radius: 20px; padding: 40px; box-shadow: var(--shadow-card);">
    
    <div class="text-center mb-4">
      <div style="font-size: 2.8rem; line-height: 1; margin-bottom: 0.75rem;">🔐</div>
      <h1 class="h3 mb-2 font-hien-thi fw-800" style="color: var(--mau-chinh-toi);">Đăng Nhập Hệ Thống</h1>
      <p class="text-phu fs-meta" style="font-size: 0.9rem;">
        Cổng đăng nhập tài khoản Sinh viên &amp; Quản trị viên UniEvent
      </p>
    </div>

    <?php if (!empty($thongBaoFlash)): ?>
      <div class="hop-thong-bao mb-3" role="alert" style="background:rgba(59,130,246,0.12); border:1px solid rgba(59,130,246,0.3); color:var(--mau-chinh); padding:12px 16px; border-radius:10px; font-size:0.875rem;">
        <?= e($thongBaoFlash) ?>
      </div>
    <?php endif; ?>

    <?php if ($loi !== null): ?>
      <div class="hop-thong-bao mb-3" role="alert" style="background:rgba(239,68,68,0.12); border:1px solid rgba(239,68,68,0.3); color:#ef4444; padding:12px 16px; border-radius:10px; font-size:0.875rem;">
        <strong>Lỗi:</strong> <?= e($loi) ?>
      </div>
    <?php endif; ?>

    <form action="dang-nhap.php" method="POST" novalidate>
      <?php if (!empty($tiepTuc)): ?>
        <input type="hidden" name="tieptuc" value="<?= e($tiepTuc) ?>">
      <?php endif; ?>

      <div class="form-group mb-3">
        <label for="email" class="form-label fw-bold mb-2" style="font-size: 0.875rem; color:var(--chu-chinh);">
          Địa chỉ Email <span class="bat-buoc" style="color:#ef4444;">*</span>
        </label>
        <input
          type="email"
          id="email"
          name="email"
          class="form-control"
          placeholder="admin@ued.vn hoặc lan@ued.vn"
          value="<?= e($email) ?>"
          style="width:100%; height:46px; padding:0.5rem 1rem; border:1.5px solid var(--border-nhat); border-radius:10px; font-size:0.9rem;"
          required
          autocomplete="email"
        />
      </div>

      <div class="form-group mb-4">
        <label for="password" class="form-label fw-bold mb-2" style="font-size: 0.875rem; color:var(--chu-chinh);">
          Mật khẩu <span class="bat-buoc" style="color:#ef4444;">*</span>
        </label>
        <input
          type="password"
          id="password"
          name="password"
          class="form-control"
          placeholder="Nhập mật khẩu tài khoản"
          style="width:100%; height:46px; padding:0.5rem 1rem; border:1.5px solid var(--border-nhat); border-radius:10px; font-size:0.9rem;"
          required
          autocomplete="current-password"
        />
      </div>

      <button type="submit" class="nut-bam nut-nhan nut-full py-3" style="font-size: 1rem; font-weight: 700; width:100%;">
        Đăng nhập
      </button>

      <div class="mt-4 pt-3 text-center" style="border-top: 1px dashed var(--border-nhat); font-size: 0.875rem; color: #64748b;">
        Chưa có tài khoản sinh viên?
        <a href="dang-ky.php" class="fw-bold" style="color: var(--mau-chinh); text-decoration: none;">Đăng ký ngay</a>
      </div>

      <div class="mt-3 p-3" style="background:var(--nen-section); border-radius:10px; font-size:0.8rem; color:var(--chu-phu); border:1px solid var(--border-nhat); line-height:1.6;">
        <strong style="color:var(--chu-chinh);">Tài khoản thử nghiệm (Mật khẩu: <code>matkhau123</code>):</strong>
        <ul style="margin:6px 0 0 18px; padding:0;">
          <li>Quản trị: <code>admin@ued.vn</code></li>
          <li>Khách hàng: <code>lan@ued.vn</code> hoặc <code>an@ued.vn</code></li>
        </ul>
      </div>
    </form>
  </div>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>
