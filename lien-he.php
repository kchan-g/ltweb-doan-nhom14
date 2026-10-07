<?php
/**
 * lien-he.php
 * Trang liên hệ & tiếp nhận phản hồi sinh viên UniEvent.
 * Xử lý biểu mẫu an toàn 100% ở phía máy chủ:
 * - Kiểm tra rỗng, độ dài, biểu thức chính quy (Regex cho SĐT, Email chuẩn)
 * - Kiểm tra tệp đính kèm an toàn qua finfo (chống giả mạo đuôi file, dung lượng <= 2MB)
 * - Lưu trữ vào storage/lien-he.jsonl
 * - Chuyển hướng Post/Redirect/Get (PRG) và xóa thông báo Flash sau khi hiển thị
 */

declare(strict_types=1);

require_once __DIR__ . '/inc/config.php';

use App\Data\KhoLienHe;

$khoLienHe = new KhoLienHe(__DIR__ . '/storage/lien-he.jsonl');

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
    // 1. Thu thập và làm sạch dữ liệu chuỗi đầu vào (trim)
    $du['hoten']   = trim($_POST['hoten'] ?? '');
    $du['email']   = trim($_POST['email'] ?? '');
    $du['sdt']     = trim($_POST['sdt'] ?? '');
    $du['khoa']    = trim($_POST['khoa'] ?? '');
    $du['noidung'] = trim($_POST['noidung'] ?? '');

    // 2. Kiểm tra tính hợp lệ từng trường dữ liệu (Validation)
    // a. Họ tên: bắt buộc, 2 - 100 ký tự
    $doDaiHoTen = mb_strlen($du['hoten'], 'UTF-8');
    if ($du['hoten'] === '') {
        $loi['hoten'] = 'Vui lòng nhập họ và tên của bạn.';
    } elseif ($doDaiHoTen < 2 || $doDaiHoTen > 100) {
        $loi['hoten'] = 'Họ tên phải từ 2 đến 100 ký tự.';
    }

    // b. Email: bắt buộc, đúng định dạng và có tên miền hợp lệ (chặn lan@ued)
    if ($du['email'] === '') {
        $loi['email'] = 'Vui lòng nhập địa chỉ email.';
    } elseif (!filter_var($du['email'], FILTER_VALIDATE_EMAIL) || !preg_match('/^[^@\s]+@[^@\s]+\.[a-zA-Z]{2,}$/', $du['email'])) {
        $loi['email'] = 'Địa chỉ email không hợp lệ (ví dụ: hoten@ued.udn.vn).';
    }

    // c. Số điện thoại: bắt buộc, 10 số bắt đầu bằng 0
    if ($du['sdt'] === '') {
        $loi['sdt'] = 'Vui lòng nhập số điện thoại liên hệ.';
    } elseif (!preg_match('/^0[0-9]{9}$/', $du['sdt'])) {
        $loi['sdt'] = 'Số điện thoại phải gồm 10 chữ số và bắt đầu bằng số 0.';
    }

    // d. Nội dung liên hệ: bắt buộc, tối thiểu 10 ký tự
    $doDaiNoiDung = mb_strlen($du['noidung'], 'UTF-8');
    if ($du['noidung'] === '') {
        $loi['noidung'] = 'Vui lòng nhập nội dung liên hệ hoặc câu hỏi.';
    } elseif ($doDaiNoiDung < 10) {
        $loi['noidung'] = 'Nội dung liên hệ quá ngắn (tối thiểu 10 ký tự).';
    } elseif ($doDaiNoiDung > 3000) {
        $loi['noidung'] = 'Nội dung liên hệ không được vượt quá 3000 ký tự.';
    }

    // 3. Xử lý tệp ảnh đính kèm an toàn (Upload File)
    if (isset($_FILES['anh']) && $_FILES['anh']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['anh'];

        // Kiểm tra lỗi hệ thống khi tải tệp
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $loi['anh'] = 'Có lỗi xảy ra trong quá trình tải tệp lên máy chủ.';
        } elseif ($file['size'] > 2 * 1024 * 1024) {
            // Ca kiểm thử 4: Tệp vượt quá 2 MB
            $loi['anh'] = 'Dung lượng ảnh vượt quá giới hạn cho phép (tối đa 2 MB).';
        } else {
            // Ca kiểm thử 3: Kiểm tra định dạng thật bằng finfo (phòng chống tệp giả mạo .jpg chứa PHP)
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
                // Tạo tên tệp ngẫu nhiên không trùng lặp và lưu vào uploads/
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

    // 4. Nếu toàn bộ dữ liệu hợp lệ -> Lưu trữ và Chuyển hướng Post/Redirect/Get (PRG)
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

// Đọc thông báo Flash (hiển thị đúng 1 lần sau PRG)
$thongBaoThanhCong = flash();

$tieuDe = 'Liên hệ Ban Tổ Chức - UniEvent';
$trang  = 'lien-he';

require __DIR__ . '/inc/header.php';
?>

<main>
  <div class="container">
    <!-- Breadcrumb -->
    <nav class="breadcrumb mb-3" aria-label="Đường dẫn phân cấp">
      <a href="index.php">Trang chủ</a>
      <span aria-hidden="true">/</span>
      <span class="hien-tai">Liên hệ hỗ trợ</span>
    </nav>

    <!-- Thẻ H1 duy nhất của trang -->
    <div class="trang-tieu-de mb-4">
      <h1>Liên Hệ Ban Tổ Chức &amp; Hỗ Trợ Sinh Viên</h1>
      <p class="do-dai-chuan text-phu">
        Bạn có thắc mắc về sự kiện, đăng ký vé hoặc hợp tác truyền thông?
        Vui lòng điền thông tin vào biểu mẫu bên dưới, chúng tôi sẽ phản hồi trong vòng 24 giờ làm việc.
      </p>
    </div>

    <?php if ($thongBaoThanhCong !== null): ?>
      <!-- Thông báo gửi thành công (Flash message theo chuẩn PRG) -->
      <div class="hop-thong-bao hop-thong-bao--thanh-cong mb-4" role="status">
        <span class="hop-thong-bao__icon" aria-hidden="true">✓</span>
        <div>
          <strong>Gửi thành công!</strong>
          <p class="mb-0"><?= e($thongBaoThanhCong) ?></p>
        </div>
      </div>
    <?php endif; ?>

    <!-- Bố cục 2 cột trang liên hệ: Form bên trái, Thẻ thông tin bên phải -->
    <div class="lien-he-layout">
      <!-- Cột 1: Biểu mẫu gửi phản hồi -->
      <section aria-labelledby="tieu-de-form-lien-he">
        <div class="form-card">
          <h2 id="tieu-de-form-lien-he" class="h3 mb-4">
            Gửi Phiếu Yêu Cầu Hỗ Trợ
          </h2>

          <form action="lien-he.php" method="POST" enctype="multipart/form-data">
            <fieldset>
              <legend>Thông tin người gửi và nội dung yêu cầu</legend>

              <div class="form-luoi">
                <!-- Control 1: Họ và tên -->
                <div class="form-nhom">
                  <label for="txt-hoten">Họ và tên sinh viên *</label>
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
                  />
                  <?php if (isset($loi['hoten'])): ?>
                    <div class="thong-bao-loi" role="alert">⚠️ <?= e($loi['hoten']) ?></div>
                  <?php endif; ?>
                </div>

                <!-- Control 2: Email -->
                <div class="form-nhom">
                  <label for="txt-email">Email sinh viên *</label>
                  <input
                    type="email"
                    id="txt-email"
                    name="email"
                    value="<?= e($du['email']) ?>"
                    required
                    placeholder="Ví dụ: hoten@ued.udn.vn"
                    autocomplete="email"
                    class="<?= isset($loi['email']) ? 'co-loi' : '' ?>"
                  />
                  <?php if (isset($loi['email'])): ?>
                    <div class="thong-bao-loi" role="alert">⚠️ <?= e($loi['email']) ?></div>
                  <?php endif; ?>
                </div>

                <!-- Control 3: Số điện thoại -->
                <div class="form-nhom">
                  <label for="txt-sdt">Số điện thoại liên hệ *</label>
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
                  />
                  <?php if (isset($loi['sdt'])): ?>
                    <div class="thong-bao-loi" role="alert">⚠️ <?= e($loi['sdt']) ?></div>
                  <?php endif; ?>
                </div>

                <!-- Control 4: Khoa / Đơn vị -->
                <div class="form-nhom">
                  <label for="txt-khoa">Khoa / Viện trực thuộc:</label>
                  <input
                    type="text"
                    id="txt-khoa"
                    name="khoa"
                    value="<?= e($du['khoa']) ?>"
                    placeholder="Ví dụ: Khoa Toán - Tin học"
                    class="<?= isset($loi['khoa']) ? 'co-loi' : '' ?>"
                  />
                  <?php if (isset($loi['khoa'])): ?>
                    <div class="thong-bao-loi" role="alert">⚠️ <?= e($loi['khoa']) ?></div>
                  <?php endif; ?>
                </div>

                <!-- Control 5: Đính kèm ảnh minh chứng -->
                <div class="form-nhom form-nhom--full">
                  <label for="file-anh">Ảnh minh chứng (nếu có):</label>
                  <input
                    type="file"
                    id="file-anh"
                    name="anh"
                    accept="image/jpeg,image/png,image/webp"
                    class="<?= isset($loi['anh']) ? 'co-loi' : '' ?>"
                  />
                  <div class="goi-y mt-1">Định dạng cho phép: JPG, PNG, WebP. Dung lượng tối đa: 2 MB.</div>
                  <?php if (isset($loi['anh'])): ?>
                    <div class="thong-bao-loi" role="alert">⚠️ <?= e($loi['anh']) ?></div>
                  <?php endif; ?>
                </div>

                <!-- Control 6: Nội dung phản hồi / câu hỏi -->
                <div class="form-nhom form-nhom--full">
                  <label for="txt-noidung">Nội dung câu hỏi chi tiết *</label>
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
                  ><?= e($du['noidung']) ?></textarea>
                  <?php if (isset($loi['noidung'])): ?>
                    <div class="thong-bao-loi" role="alert">⚠️ <?= e($loi['noidung']) ?></div>
                  <?php endif; ?>
                </div>
              </div>

              <div class="mt-4">
                <button type="submit" class="nut-bam nut-nhan">Gửi Phiếu Yêu Cầu Hỗ Trợ</button>
                <button type="reset" class="nut-bam ms-2">Nhập lại</button>
              </div>
            </fieldset>
          </form>
        </div>
      </section>

      <!-- Cột 2: Thẻ thông tin liên hệ và Bản đồ -->
      <aside aria-label="Thông tin liên hệ trực tiếp">
        <div class="widget mb-4">
          <h3>Thông Tin Văn Phòng</h3>
          <p class="text-phu mb-3 fs-nho lh-chuan">
            <strong>Trường Đại học Sư phạm – Đại học Đà Nẵng</strong><br />
            Khoa Toán - Tin | Văn phòng Đoàn - Hội Sinh viên<br />
            Địa chỉ: 459 Tôn Đức Thắng, Hòa Khánh Nam, Liên Chiểu, TP. Đà Nẵng
          </p>

          <h3>Đường Dây Nóng</h3>
          <ul class="mb-3">
            <li>Hotline hỗ trợ kỹ thuật: <strong>(0236) 3841 323</strong></li>
            <li>
              Email tiếp nhận: <strong>support@unievent.edu.vn</strong>
            </li>
            <li>Thời gian làm việc: Thứ 2 – Thứ 6 (07:30 - 17:00)</li>
          </ul>
        </div>

        <div class="widget">
          <h3>Thời Gian Xử Lý Phiếu</h3>
          <p class="text-phu fs-nho lh-chuan">
            Các yêu cầu gửi trong giờ hành chính sẽ được Ban Quản trị xử lý trong vòng
            <strong>2 - 4 giờ</strong>. Các phản hồi gửi vào cuối tuần hoặc ngày lễ sẽ
            được xử lý vào sáng ngày làm việc tiếp theo.
          </p>
        </div>
      </aside>
    </div>
  </div>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>
