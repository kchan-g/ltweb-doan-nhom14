<?php
declare(strict_types=1);

require_once __DIR__ . '/inc/config.php';

use App\Data\KhoLienHe;

$khoLienHe = new KhoLienHe(db());

$du = [
    'hoten'   => '',
    'email'   => '',
    'sdt'     => '',
    'khoa'    => '',
    'noidung' => '',
];

$loi = [];
$tenAnhLuu = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $du['hoten']   = trim($_POST['hoten'] ?? '');
    $du['email']   = trim($_POST['email'] ?? '');
    $du['sdt']     = trim($_POST['sdt'] ?? '');
    $du['khoa']    = trim($_POST['khoa'] ?? '');
    $du['noidung'] = trim($_POST['noidung'] ?? '');

    $doDaiHoTen = mb_strlen($du['hoten'], 'UTF-8');
    if ($du['hoten'] === '') {
        $loi['hoten'] = 'Vui lòng nhập họ và tên của bạn.';
    } elseif ($doDaiHoTen < 2 || $doDaiHoTen > 100) {
        $loi['hoten'] = 'Họ tên phải từ 2 đến 100 ký tự.';
    }

    if ($du['email'] === '') {
        $loi['email'] = 'Vui lòng nhập địa chỉ email.';
    } elseif (!filter_var($du['email'], FILTER_VALIDATE_EMAIL) || !preg_match('/^[^@\s]+@[^@\s]+\.[a-zA-Z]{2,}$/', $du['email'])) {
        $loi['email'] = 'Địa chỉ email không hợp lệ (ví dụ: hoten@ued.udn.vn).';
    }

    if ($du['sdt'] === '') {
        $loi['sdt'] = 'Vui lòng nhập số điện thoại liên hệ.';
    } elseif (!preg_match('/^0[0-9]{9}$/', $du['sdt'])) {
        $loi['sdt'] = 'Số điện thoại phải gồm 10 chữ số và bắt đầu bằng số 0.';
    }

    $doDaiNoiDung = mb_strlen($du['noidung'], 'UTF-8');
    if ($du['noidung'] === '') {
        $loi['noidung'] = 'Vui lòng nhập nội dung liên hệ hoặc câu hỏi.';
    } elseif ($doDaiNoiDung < 10) {
        $loi['noidung'] = 'Nội dung liên hệ quá ngắn (tối thiểu 10 ký tự).';
    } elseif ($doDaiNoiDung > 3000) {
        $loi['noidung'] = 'Nội dung liên hệ không được vượt quá 3000 ký tự.';
    }

    if (isset($_FILES['anh']) && $_FILES['anh']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['anh'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $loi['anh'] = 'Có lỗi xảy ra trong quá trình tải tệp lên máy chủ.';
        } elseif ($file['size'] > 2 * 1024 * 1024) {
            $loi['anh'] = 'Dung lượng ảnh vượt quá giới hạn cho phép (tối đa 2 MB).';
        } else {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeThat = $finfo->file($file['tmp_name']);

            $duoiHopLe = [
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/webp' => 'webp',
            ];

            if (!isset($duoiHopLe[$mimeThat])) {
                $loi['anh'] = 'Tệp tải lên không phải ảnh hợp lệ. Hệ thống chỉ chấp nhận định dạng JPG, PNG hoặc WebP.';
            } else {
                $duoi = $duoiHopLe[$mimeThat];
                $tenNgauNhien = bin2hex(random_bytes(16)) . '.' . $duoi;
                $duongDanDich = __DIR__ . '/uploads/' . $tenNgauNhien;

                if (!move_uploaded_file($file['tmp_name'], $duongDanDich)) {
                    $loi['anh'] = 'Không thể lưu tệp ảnh vào thư mục máy chủ.';
                } else {
                    $tenAnhLuu = $tenNgauNhien;
                }
            }
        }
    }

    if (empty($loi)) {
        $khoLienHe->them([
            'thoiGian' => date('Y-m-d H:i:s'),
            'hoTen'    => $du['hoten'],
            'email'    => $du['email'],
            'sdt'      => $du['sdt'],
            'khoa'     => $du['khoa'],
            'noiDung'  => $du['noidung'],
            'anh'      => $tenAnhLuu,
        ]);

        flash('Cảm ơn bạn! Thông tin liên hệ đã được gửi thành công đến Ban Quản trị UniEvent.');
        header('Location: lien-he.php');
        exit;
    }
}

$thongBaoThanhCong = flash();

$tieuDe = 'Liên hệ Ban Tổ Chức - UniEvent';
$trang  = 'lien-he';

require __DIR__ . '/inc/header.php';
?>

<main>
  <div class="container">
    <nav class="breadcrumb mb-4" aria-label="Đường dẫn phân cấp">
      <a href="index.php">Trang chủ</a>
      <span aria-hidden="true">/</span>
      <span class="hien-tai">Liên hệ hỗ trợ</span>
    </nav>

    <div class="trang-tieu-de mb-4">
      <h1 class="font-hien-thi fw-800 text-chinh mb-2">Liên Hệ Ban Tổ Chức &amp; Hỗ Trợ</h1>
      <p class="do-dai-chuan text-phu" style="font-size: 1.05rem;">
        Bạn có thắc mắc về sự kiện, đăng ký vé hoặc hợp tác truyền thông?
        Vui lòng điền thông tin vào biểu mẫu bên dưới, chúng tôi sẽ phản hồi trong vòng 24 giờ làm việc.
      </p>
    </div>

    <?php if ($thongBaoThanhCong !== null): ?>
      <div class="hop-thong-bao mb-4" role="status" style="background:rgba(59,130,246,0.12); border:1px solid rgba(59,130,246,0.3); color:var(--mau-chinh); padding:16px 20px; border-radius:12px; box-shadow:var(--shadow-sm);">
        <strong style="color:var(--mau-chinh); font-size:1.05rem;">✓ Gửi thành công!</strong>
        <p class="mb-0 mt-1"><?= e($thongBaoThanhCong) ?></p>
      </div>
    <?php endif; ?>

    <div class="lien-he-layout">
      <section aria-labelledby="tieu-de-form-lien-he">
        <div style="background:var(--nen-surface); border:1px solid var(--border-nhat); border-radius:16px; padding:32px; box-shadow:var(--shadow-card);">
          <h2 id="tieu-de-form-lien-he" class="h3 mb-4 font-hien-thi fw-700 text-chinh">
            Gửi Phiếu Yêu Cầu Hỗ Trợ
          </h2>

          <form action="lien-he.php" method="POST" enctype="multipart/form-data">
            <fieldset style="border:none; padding:0; margin:0;">
              <legend class="chi-danh-cho-sr">Thông tin người gửi và nội dung yêu cầu</legend>

              <div class="form-luoi">
                <div class="form-nhom">
                  <label for="txt-hoten" style="font-weight:600; font-size:0.875rem; color:var(--chu-chinh);">Họ và tên sinh viên *</label>
                  <input
                    type="text"
                    id="txt-hoten"
                    name="hoten"
                    value="<?= e($du['hoten']) ?>"
                    required
                    minlength="2"
                    maxlength="100"
                    placeholder="Ví dụ: Nguyễn Văn An"
                    autocomplete="name"
                    class="<?= isset($loi['hoten']) ? 'co-loi' : '' ?>"
                    style="height:44px; padding:0.5rem 1rem; border:1.5px solid var(--border-nhat); border-radius:10px; font-size:0.9rem;"
                  />
                  <?php if (isset($loi['hoten'])): ?>
                    <div class="thong-bao-loi" role="alert" style="color:#dc2626; font-size:0.8rem; margin-top:4px;">⚠️ <?= e($loi['hoten']) ?></div>
                  <?php endif; ?>
                </div>

                <div class="form-nhom">
                  <label for="txt-email" style="font-weight:600; font-size:0.875rem; color:var(--chu-chinh);">Email sinh viên *</label>
                  <input
                    type="email"
                    id="txt-email"
                    name="email"
                    value="<?= e($du['email']) ?>"
                    required
                    placeholder="Ví dụ: hoten@ued.udn.vn"
                    autocomplete="email"
                    class="<?= isset($loi['email']) ? 'co-loi' : '' ?>"
                    style="height:44px; padding:0.5rem 1rem; border:1.5px solid var(--border-nhat); border-radius:10px; font-size:0.9rem;"
                  />
                  <?php if (isset($loi['email'])): ?>
                    <div class="thong-bao-loi" role="alert" style="color:#dc2626; font-size:0.8rem; margin-top:4px;">⚠️ <?= e($loi['email']) ?></div>
                  <?php endif; ?>
                </div>

                <div class="form-nhom">
                  <label for="txt-sdt" style="font-weight:600; font-size:0.875rem; color:var(--chu-chinh);">Số điện thoại liên hệ *</label>
                  <input
                    type="tel"
                    id="txt-sdt"
                    name="sdt"
                    value="<?= e($du['sdt']) ?>"
                    required
                    pattern="0[0-9]{9}"
                    title="Vui lòng nhập đúng 10 chữ số bắt đầu bằng số 0"
                    placeholder="Ví dụ: 0905123456"
                    autocomplete="tel"
                    class="<?= isset($loi['sdt']) ? 'co-loi' : '' ?>"
                    style="height:44px; padding:0.5rem 1rem; border:1.5px solid var(--border-nhat); border-radius:10px; font-size:0.9rem;"
                  />
                  <?php if (isset($loi['sdt'])): ?>
                    <div class="thong-bao-loi" role="alert" style="color:#dc2626; font-size:0.8rem; margin-top:4px;">⚠️ <?= e($loi['sdt']) ?></div>
                  <?php endif; ?>
                </div>

                <div class="form-nhom">
                  <label for="txt-khoa" style="font-weight:600; font-size:0.875rem; color:var(--chu-chinh);">Khoa / Viện trực thuộc:</label>
                  <input
                    type="text"
                    id="txt-khoa"
                    name="khoa"
                    value="<?= e($du['khoa']) ?>"
                    placeholder="Ví dụ: Khoa Toán - Tin học"
                    class="<?= isset($loi['khoa']) ? 'co-loi' : '' ?>"
                    style="height:44px; padding:0.5rem 1rem; border:1.5px solid var(--border-nhat); border-radius:10px; font-size:0.9rem;"
                  />
                  <?php if (isset($loi['khoa'])): ?>
                    <div class="thong-bao-loi" role="alert" style="color:#dc2626; font-size:0.8rem; margin-top:4px;">⚠️ <?= e($loi['khoa']) ?></div>
                  <?php endif; ?>
                </div>

                <div class="form-nhom form-nhom--full">
                  <label for="file-anh" style="font-weight:600; font-size:0.875rem; color:var(--chu-chinh);">Ảnh minh chứng (nếu có):</label>
                  <input
                    type="file"
                    id="file-anh"
                    name="anh"
                    accept="image/jpeg,image/png,image/webp"
                    class="<?= isset($loi['anh']) ? 'co-loi' : '' ?>"
                    style="padding:0.4rem; border:1.5px solid var(--border-nhat); border-radius:10px; font-size:0.85rem;"
                  />
                  <div class="goi-y mt-1 text-phu fs-meta" style="font-size:0.8rem;">Định dạng cho phép: JPG, PNG, WebP. Dung lượng tối đa: 2 MB.</div>
                  <?php if (isset($loi['anh'])): ?>
                    <div class="thong-bao-loi" role="alert" style="color:#dc2626; font-size:0.8rem; margin-top:4px;">⚠️ <?= e($loi['anh']) ?></div>
                  <?php endif; ?>
                </div>

                <div class="form-nhom form-nhom--full">
                  <label for="txt-noidung" style="font-weight:600; font-size:0.875rem; color:var(--chu-chinh);">Nội dung câu hỏi chi tiết *</label>
                  <textarea
                    id="txt-noidung"
                    name="noidung"
                    rows="5"
                    cols="40"
                    required
                    minlength="10"
                    maxlength="3000"
                    placeholder="Mô tả cụ thể thắc mắc, mã sự kiện hoặc góp ý của bạn..."
                    class="<?= isset($loi['noidung']) ? 'co-loi' : '' ?>"
                    style="padding:0.75rem 1rem; border:1.5px solid var(--border-nhat); border-radius:10px; font-size:0.9rem; font-family:inherit;"
                  ><?= e($du['noidung']) ?></textarea>
                  <?php if (isset($loi['noidung'])): ?>
                    <div class="thong-bao-loi" role="alert" style="color:#dc2626; font-size:0.8rem; margin-top:4px;">⚠️ <?= e($loi['noidung']) ?></div>
                  <?php endif; ?>
                </div>
              </div>

              <div class="mt-4 d-flex gap-2">
                <button type="submit" class="nut-bam nut-nhan py-2 px-4" style="font-weight:700;">Gửi Phiếu Yêu Cầu Hỗ Trợ</button>
                <button type="reset" class="nut-bam nut-phu py-2 px-4">Nhập lại</button>
              </div>
            </fieldset>
          </form>
        </div>
      </section>

      <aside aria-label="Thông tin liên hệ trực tiếp">
        <div class="widget mb-4">
          <h3 class="h5 mb-3 font-hien-thi fw-700 text-chinh">Thông Tin Văn Phòng</h3>
          <p class="text-phu mb-3 fs-nho lh-chuan" style="line-height:1.7;">
            <strong>Trường Đại học Sư phạm – Đại học Đà Nẵng</strong><br />
            Khoa Toán - Tin | Văn phòng Đoàn - Hội Sinh viên<br />
            Địa chỉ: 459 Tôn Đức Thắng, Hòa Khánh Nam, Liên Chiểu, TP. Đà Nẵng
          </p>

          <h3 class="h5 mb-2 font-hien-thi fw-700 text-chinh">Đường Dây Nóng</h3>
          <ul class="mb-0 text-phu" style="padding-left:1.25rem; line-height:1.8; font-size:0.875rem;">
            <li>Hotline hỗ trợ kỹ thuật: <strong class="text-chinh">(0236) 3841 323</strong></li>
            <li>
              Email tiếp nhận: <strong class="text-chinh">support@unievent.edu.vn</strong>
            </li>
            <li>Thời gian làm việc: Thứ 2 – Thứ 6 (07:30 - 17:00)</li>
          </ul>
        </div>

        <div class="widget">
          <h3 class="h5 mb-2 font-hien-thi fw-700 text-chinh">Thời Gian Xử Lý Phiếu</h3>
          <p class="text-phu fs-nho lh-chuan mb-0" style="line-height:1.7; font-size:0.875rem;">
            Các yêu cầu gửi trong giờ hành chính sẽ được Ban Quản trị xử lý trong vòng
            <strong class="text-chinh">2 - 4 giờ</strong>. Các phản hồi gửi vào cuối tuần hoặc ngày lễ sẽ
            được xử lý vào sáng ngày làm việc tiếp theo.
          </p>
        </div>
      </aside>
    </div>
  </div>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>
