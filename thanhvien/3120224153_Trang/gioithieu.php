<?php
/**
 * thanhvien/3120224153_Trang/gioithieu.php
 * Trang giới thiệu cá nhân: Nguyễn Thị Kiều Trang (MSSV: 3120224153 - Nhóm 14).
 * Chức năng máy chủ:
 * 1. Đăng ký nhận bản tin công nghệ & đồ án (POST -> Validation -> Lưu storage/3120224153_bantin.jsonl -> PRG -> Flash Session).
 * 2. Công cụ dự toán chi phí & tiến độ phát triển Website (POST -> Whitelist & Bounds Validation -> Ma trận chi phí & ngày công máy chủ -> PRG).
 * Cách thử:
 * - Điền họ tên, email, chuyên đề và bấm "Đăng ký nhận tin" để kiểm tra lưu file JSONL và cơ chế Flash message qua PRG.
 * - Chọn loại website, số lượng trang, gói thiết kế và tiện ích ở biểu mẫu "Dự toán chi phí & tiến độ", bấm tính toán để xem máy chủ xử lý ma trận chi phí và thời gian bàn giao.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../inc/config.php';

// Đường dẫn tương đối về thư mục gốc
$goc = '../../';
$tieuDe = 'Profile cá nhân - Nguyễn Thị Kiều Trang | UniEvent';
$trang = 'gioi-thieu';

// ============================================================================
// CHỨC NĂNG 1: ĐĂNG KÝ BẢN TIN CÔNG NGHỆ (POST -> VALIDATION -> JSONL -> PRG)
// ============================================================================
$tepBanTinTrang = __DIR__ . '/../../storage/3120224153_bantin.jsonl';
if (!is_dir(dirname($tepBanTinTrang))) {
    mkdir(dirname($tepBanTinTrang), 0777, true);
}

$loiBanTin = $_SESSION['trang_bantin_loi'] ?? [];
$duLieuCu = $_SESSION['trang_bantin_cu'] ?? [
    'ho_ten' => '',
    'email' => '',
    'chuyen_de' => 'Web Dev & UI/UX',
];
$thongBaoThanhCong = $_SESSION['trang_bantin_ok'] ?? null;
unset($_SESSION['trang_bantin_loi'], $_SESSION['trang_bantin_cu'], $_SESSION['trang_bantin_ok']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dang_ky_bantin'])) {
    $hoTen = trim((string)($_POST['ho_ten'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $chuyenDe = trim((string)($_POST['chuyen_de'] ?? ''));

    $loi = [];

    if ($hoTen === '') {
        $loi['ho_ten'] = 'Vui lòng nhập họ và tên của bạn.';
    } elseif (mb_strlen($hoTen) < 2 || mb_strlen($hoTen) > 60) {
        $loi['ho_ten'] = 'Họ tên phải từ 2 đến 60 ký tự.';
    }

    if ($email === '') {
        $loi['email'] = 'Vui lòng cung cấp địa chỉ email nhận tin.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $loi['email'] = 'Email không hợp lệ (ví dụ: student@ued.udn.vn).';
    }

    $dsChuyenDe = ['Web Dev & UI/UX', 'IoT & Hệ thống nhúng', 'AI & Khai phá dữ liệu'];
    if (!in_array($chuyenDe, $dsChuyenDe, true)) {
        $loi['chuyen_de'] = 'Chuyên đề nhận tin không hợp lệ.';
    }

    if (!empty($loi)) {
        $_SESSION['trang_bantin_loi'] = $loi;
        $_SESSION['trang_bantin_cu'] = [
            'ho_ten' => $hoTen,
            'email' => $email,
            'chuyen_de' => $chuyenDe,
        ];
        header('Location: gioithieu.php#ban-tin');
        exit;
    }

    $banGhi = [
        'id' => 'BT' . date('YmdHis') . rand(100, 999),
        'ho_ten' => $hoTen,
        'email' => $email,
        'chuyen_de' => $chuyenDe,
        'ngay_dang_ky' => date('d/m/Y H:i'),
    ];

    file_put_contents($tepBanTinTrang, json_encode($banGhi, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND | LOCK_EX);
    $_SESSION['trang_bantin_ok'] = 'Chúc mừng ' . $hoTen . '! Bạn đã đăng ký nhận bản tin thành công.';
    header('Location: gioithieu.php#ban-tin');
    exit;
}

// Đọc danh sách 5 người đăng ký gần nhất
$dsDangKy = [];
if (file_exists($tepBanTinTrang) && is_readable($tepBanTinTrang)) {
    $dongFile = file($tepBanTinTrang, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($dongFile !== false) {
        $dongFile = array_reverse($dongFile);
        $dongFile = array_slice($dongFile, 0, 5);
        foreach ($dongFile as $d) {
            $item = json_decode($d, true);
            if (is_array($item)) {
                $dsDangKy[] = $item;
            }
        }
    }
}

// ============================================================================
// CHỨC NĂNG 2: DỰ TOÁN CHI PHÍ & TIẾN ĐỘ DỰ ÁN WEB (POST -> VALIDATION -> PRG)
// ============================================================================
$loaiWebMap = [
    'landing' => ['ten' => 'Landing Page sự kiện / giới thiệu sản phẩm', 'gia_goc' => 2000000, 'ngay_goc' => 4],
    'tintuc'  => ['ten' => 'Website Tin tức / Blog / Cổng thông tin', 'gia_goc' => 4500000, 'ngay_goc' => 8],
    'tmdt'    => ['ten' => 'Website Thương mại điện tử / Bán hàng', 'gia_goc' => 8000000, 'ngay_goc' => 15],
    'webapp'  => ['ten' => 'Hệ thống Quản lý Web App chuyên sâu', 'gia_goc' => 12000000, 'ngay_goc' => 22],
];

$goiUiMap = [
    'standard' => ['ten' => 'Giao diện mẫu chuẩn Responsive', 'he_so' => 1.0],
    'custom'   => ['ten' => 'Giao diện tùy biến nhận diện thương hiệu', 'he_so' => 1.25],
    'figma'    => ['ten' => 'Thiết kế độc quyền Figma & Design System', 'he_so' => 1.5],
];

$tienIchMap = [
    'thanh_toan'  => ['ten' => 'Tích hợp cổng thanh toán trực tuyến (VNPay/MoMo)', 'gia' => 1500000, 'ngay' => 3],
    'da_ngon_ngu' => ['ten' => 'Hỗ trợ đa ngôn ngữ quốc tế (i18n)', 'gia' => 1000000, 'ngay' => 2],
    'seo_speed'   => ['ten' => 'Tối ưu chuẩn SEO On-page & Core Web Vitals', 'gia' => 800000, 'ngay' => 2],
    'bao_tri'     => ['ten' => 'Gói bảo hành kỹ thuật & vận hành 6 tháng', 'gia' => 1200000, 'ngay' => 0],
];

$loiDuToan = $_SESSION['trang_dutoan_loi'] ?? [];
$duLieuDuToanCu = $_SESSION['trang_dutoan_cu'] ?? [
    'loai_web' => 'landing',
    'so_trang' => 5,
    'goi_ui'   => 'standard',
    'tien_ich' => ['seo_speed'],
];
$ketQuaDuToan = $_SESSION['trang_dutoan_kq'] ?? null;
unset($_SESSION['trang_dutoan_loi'], $_SESSION['trang_dutoan_cu'], $_SESSION['trang_dutoan_kq']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tinh_du_toan'])) {
    $loaiWeb = trim((string)($_POST['loai_web'] ?? ''));
    $soTrangRaw = trim((string)($_POST['so_trang'] ?? ''));
    $goiUi = trim((string)($_POST['goi_ui'] ?? ''));
    $tienIchChon = isset($_POST['tien_ich']) && is_array($_POST['tien_ich']) ? $_POST['tien_ich'] : [];

    $loi = [];

    if (!isset($loaiWebMap[$loaiWeb])) {
        $loi['loai_web'] = 'Vui lòng chọn loại hình website hợp lệ.';
    }

    if ($soTrangRaw === '' || !ctype_digit($soTrangRaw)) {
        $loi['so_trang'] = 'Số lượng trang phải là số nguyên dương.';
    } else {
        $soTrangInt = (int)$soTrangRaw;
        if ($soTrangInt < 1 || $soTrangInt > 50) {
            $loi['so_trang'] = 'Số lượng trang phải từ 1 đến 50 trang.';
        }
    }

    if (!isset($goiUiMap[$goiUi])) {
        $loi['goi_ui'] = 'Vui lòng chọn gói thiết kế giao diện.';
    }

    $tienIchHopLe = [];
    foreach ($tienIchChon as $ti) {
        $tiKey = trim((string)$ti);
        if (isset($tienIchMap[$tiKey])) {
            $tienIchHopLe[] = $tiKey;
        }
    }

    if (!empty($loi)) {
        $_SESSION['trang_dutoan_loi'] = $loi;
        $_SESSION['trang_dutoan_cu'] = [
            'loai_web' => $loaiWeb,
            'so_trang' => (int)($soTrangRaw ?: 1),
            'goi_ui'   => $goiUi,
            'tien_ich' => $tienIchHopLe,
        ];
        header('Location: gioithieu.php#du-toan');
        exit;
    }

    $soTrangInt = (int)$soTrangRaw;
    $giaGoc = $loaiWebMap[$loaiWeb]['gia_goc'];
    $ngayGoc = $loaiWebMap[$loaiWeb]['ngay_goc'];
    $heSoUi = $goiUiMap[$goiUi]['he_so'];

    // Chi phí số trang: mỗi trang 250.000đ (tính từ trang thứ 2)
    $giaSoTrang = max(0, $soTrangInt - 1) * 250000;
    $ngaySoTrang = (int)ceil(max(0, $soTrangInt - 1) * 0.4);

    // Chi phí nền tảng cốt lõi
    $chiPhiCotLoi = (int)(($giaGoc + $giaSoTrang) * $heSoUi);

    // Tiện ích bổ sung
    $tongTienIch = 0;
    $ngayTienIch = 0;
    $dsTienIchChiTiet = [];
    foreach ($tienIchHopLe as $tiKey) {
        $tongTienIch += $tienIchMap[$tiKey]['gia'];
        $ngayTienIch += $tienIchMap[$tiKey]['ngay'];
        $dsTienIchChiTiet[] = [
            'ten' => $tienIchMap[$tiKey]['ten'],
            'gia' => $tienIchMap[$tiKey]['gia'],
            'ngay' => $tienIchMap[$tiKey]['ngay'],
        ];
    }

    $tongChiPhi = $chiPhiCotLoi + $tongTienIch;
    $tongNgayCong = $ngayGoc + $ngaySoTrang + $ngayTienIch;
    $soTuanLamViec = (int)ceil($tongNgayCong / 5);

    $_SESSION['trang_dutoan_kq'] = [
        'loai_web_ten' => $loaiWebMap[$loaiWeb]['ten'],
        'so_trang' => $soTrangInt,
        'goi_ui_ten' => $goiUiMap[$goiUi]['ten'],
        'he_so_ui' => $heSoUi,
        'gia_goc' => $giaGoc,
        'gia_so_trang' => $giaSoTrang,
        'chi_phi_cot_loi' => $chiPhiCotLoi,
        'tien_ich' => $dsTienIchChiTiet,
        'tong_tien_ich' => $tongTienIch,
        'tong_chi_phi' => $tongChiPhi,
        'tong_ngay_cong' => $tongNgayCong,
        'so_tuan' => $soTuanLamViec,
    ];
    $_SESSION['trang_dutoan_cu'] = [
        'loai_web' => $loaiWeb,
        'so_trang' => $soTrangInt,
        'goi_ui'   => $goiUi,
        'tien_ich' => $tienIchHopLe,
    ];

    header('Location: gioithieu.php#du-toan');
    exit;
}

require __DIR__ . '/../../inc/header.php';
?>

<link rel="stylesheet" href="css/canhan.css">

<!-- HEADER PROFILE -->
<header class="profile-header">
  <div class="container">
    <span class="profile-header__badge">Thành viên Ban Kỹ thuật &bull; UniEvent</span>
    <h1>Profile cá nhân</h1>
    <p>Chào mừng bạn đến trang giới thiệu của Nguyễn Thị Kiều Trang</p>
  </div>
</header>

<!-- NAVIGATION BAR -->
<nav class="profile-nav" aria-label="Điều hướng trang cá nhân">
  <div class="container">
    <ul>
      <li><a href="#about">Giới thiệu</a></li>
      <li><a href="#du-toan">Dự toán dự án Web</a></li>
      <li><a href="#ban-tin">Nhận bản tin</a></li>
      <li><a href="#projects">Dự án &amp; Sở thích</a></li>
      <li><a href="#skills">Kỹ năng</a></li>
      <li><a href="#schedule">Thời khóa biểu</a></li>
      <li><a href="#contact">Liên hệ</a></li>
    </ul>
  </div>
</nav>

<!-- MAIN CONTENT -->
<main class="profile-main container">
  <!-- KHỐI 1: GIỚI THIỆU & DỰ ÁN (BỐ CỤC GRID 2 CỘT TRÊN DESKTOP) -->
  <div class="profile-grid-top">
    <!-- Phân đoạn Giới thiệu bản thân & Ảnh đại diện -->
    <section
      id="about"
      class="profile-card about-section"
      aria-labelledby="about-heading"
    >
      <div class="avatar-wrapper">
        <img
          src="chandung.jpg"
          alt="Ảnh chân dung Nguyễn Thị Kiều Trang"
          width="180"
          height="180"
          loading="lazy"
        />
      </div>
      <div class="about-info">
        <h2 id="about-heading">Giới Thiệu Bản Thân</h2>
        <span class="meta-pill">MSSV: 3120224153 &bull; Nhóm 14</span>
        <p><strong>Họ và tên:</strong> Nguyễn Thị Kiều Trang</p>
        <p>
          <strong>Lớp:</strong> 24CNTT2 - Khoa Toán – Tin, Trường Đại học Sư
          phạm – Đại học Đà Nẵng
        </p>
        <p>
          <strong>Vai trò dự án:</strong> Phụ trách xây dựng trang Chi tiết sự kiện (chi-tiet.php)
        </p>
      </div>
    </section>

    <!-- Phân đoạn Dự án & Sở thích -->
    <article
      id="projects"
      class="profile-card"
      aria-labelledby="projects-heading"
    >
      <h2 id="projects-heading">Dự Án &amp; Sở Thích</h2>

      <div class="projects-list" id="projects-list">
        <div class="project-item" data-category="web">
          <div class="project-item__header">
            <strong>Dự án UniEvent</strong>
            <span class="project-tag">#Web Dev</span>
          </div>
          <p>
            Website quản lý và kết nối sự kiện trong trường đại học, thiết
            kế giao diện responsive chuẩn semantic HTML5/CSS3 và biểu mẫu
            liên hệ tương tác REST API.
          </p>
        </div>

        <div class="project-item" data-category="iot">
          <div class="project-item__header">
            <strong>Khóa cửa tự động RFID RC522</strong>
            <span class="project-tag">#IoT &amp; Nhúng</span>
          </div>
          <p>
            Mạch vi điều khiển Arduino Uno kết nối đầu đọc thẻ RFID RC522 và
            động cơ servo đóng mở chốt cửa an ninh tự động.
          </p>
        </div>

        <div class="project-item" data-category="ai">
          <div class="project-item__header">
            <strong>Phát hiện âm thanh giọng nói (VAD)</strong>
            <span class="project-tag">#AI &amp; Data</span>
          </div>
          <p>
            Ứng dụng mô hình học máy Scikit-Learn kết hợp trích xuất đặc trưng
            MFCC bằng Librosa để nhận dạng khoảng lặng và hoạt động tiếng nói.
          </p>
        </div>
      </div>
    </article>
  </div>

  <!-- KHỐI CHỨC NĂNG MÁY CHỦ 2: DỰ TOÁN CHI PHÍ & TIẾN ĐỘ PHÁT TRIỂN WEBSITE -->
  <section
    id="du-toan"
    class="profile-card trang-card-section"
    aria-labelledby="dutoan-heading"
  >
    <div class="trang-card-header">
      <div class="trang-card-badge">
        <span aria-hidden="true">💡</span>
        <span>Xử lý máy chủ POST &bull; Ma trận định giá &bull; Mô hình PRG</span>
      </div>
      <h2 id="dutoan-heading">Dự Toán Chi Phí &amp; Tiến Độ Phát Triển Website</h2>
      <p class="trang-card-desc">
        Công cụ tính toán chi phí ước tính và tiến độ bàn giao dự án website dựa trên loại hình, số lượng trang, gói thiết kế UI/UX và các tiện ích mở rộng. Toàn bộ logic ma trận định giá được xử lý an toàn tại máy chủ.
      </p>
    </div>

    <div class="trang-estimator-layout">
      <!-- Cột biểu mẫu tính toán -->
      <div class="trang-form-card">
        <h3>Cấu hình yêu cầu dự án</h3>
        <form
          action="gioithieu.php#du-toan"
          method="post"
          class="trang-form"
          novalidate
        >
          <div class="form-group mb-3">
            <label for="loai_web" class="form-label">
              Loại hình website <span class="bat-buoc">*</span>
            </label>
            <select
              id="loai_web"
              name="loai_web"
              class="form-control <?= isset($loiDuToan['loai_web']) ? 'is-invalid' : '' ?>"
              required
            >
              <?php foreach ($loaiWebMap as $maWeb => $thongTinWeb): ?>
                <option
                  value="<?= e($maWeb) ?>"
                  <?= ($duLieuDuToanCu['loai_web'] ?? '') === $maWeb ? 'selected' : '' ?>
                >
                  <?= e($thongTinWeb['ten']) ?> (từ <?= e(number_format($thongTinWeb['gia_goc'], 0, ',', '.')) ?> đ)
                </option>
              <?php endforeach; ?>
            </select>
            <?php if (isset($loaiDuToan['loai_web'])): ?>
              <p class="thong-bao-loi" role="alert"><?= e($loaiDuToan['loai_web']) ?></p>
            <?php endif; ?>
          </div>

          <div class="form-group mb-3">
            <label for="so_trang" class="form-label">
              Số lượng trang con dự kiến (1 - 50) <span class="bat-buoc">*</span>
            </label>
            <input
              type="number"
              id="so_trang"
              name="so_trang"
              min="1"
              max="50"
              class="form-control <?= isset($loaiDuToan['so_trang']) ? 'is-invalid' : '' ?>"
              value="<?= e((string)($duLieuDuToanCu['so_trang'] ?? 5)) ?>"
              required
            >
            <small style="color: var(--chu-phu); font-size: 0.8rem; display: block; margin-top: 3px;">
              Trang đầu tiên đã bao gồm trong gói cơ sở, mỗi trang bổ sung +250.000 VNĐ.
            </small>
            <?php if (isset($loaiDuToan['so_trang'])): ?>
              <p class="thong-bao-loi" role="alert"><?= e($loaiDuToan['so_trang']) ?></p>
            <?php endif; ?>
          </div>

          <div class="form-group mb-3">
            <label class="form-label">
              Gói thiết kế giao diện (UI/UX) <span class="bat-buoc">*</span>
            </label>
            <div class="trang-options-group" role="radiogroup" aria-label="Gói thiết kế giao diện">
              <?php foreach ($goiUiMap as $maUi => $thongTinUi): ?>
                <label class="trang-radio-item">
                  <input
                    type="radio"
                    name="goi_ui"
                    value="<?= e($maUi) ?>"
                    <?= ($duLieuDuToanCu['goi_ui'] ?? 'standard') === $maUi ? 'checked' : '' ?>
                  >
                  <span class="trang-option-label">
                    <strong><?= e($thongTinUi['ten']) ?></strong>
                    <span class="trang-option-sub">Hệ số chi phí x<?= e((string)$thongTinUi['he_so']) ?></span>
                  </span>
                </label>
              <?php endforeach; ?>
            </div>
            <?php if (isset($loaiDuToan['goi_ui'])): ?>
              <p class="thong-bao-loi" role="alert"><?= e($loaiDuToan['goi_ui']) ?></p>
            <?php endif; ?>
          </div>

          <div class="form-group mb-3">
            <label class="form-label">
              Tiện ích &amp; Chức năng nâng cao (Tùy chọn)
            </label>
            <div class="trang-options-group">
              <?php
              $tienIchDaChon = $duLieuDuToanCu['tien_ich'] ?? [];
              foreach ($tienIchMap as $maTi => $thongTinTi):
                $isChecked = in_array($maTi, $tienIchDaChon, true);
              ?>
                <label class="trang-checkbox-item">
                  <input
                    type="checkbox"
                    name="tien_ich[]"
                    value="<?= e($maTi) ?>"
                    <?= $isChecked ? 'checked' : '' ?>
                  >
                  <span class="trang-option-label">
                    <?= e($thongTinTi['ten']) ?>
                    <span class="trang-option-sub">
                      +<?= e(number_format($thongTinTi['gia'], 0, ',', '.')) ?> VNĐ (<?= $thongTinTi['ngay'] > 0 ? '+' . e((string)$thongTinTi['ngay']) . ' ngày công' : 'Bảo hành' ?>)
                    </span>
                  </span>
                </label>
              <?php endforeach; ?>
            </div>
          </div>

          <button
            type="submit"
            name="tinh_du_toan"
            value="1"
            class="btn btn-primary"
            style="width: 100%; padding: 0.75rem; font-weight: bold;"
          >
            Tính toán dự toán ngay
          </button>
        </form>
      </div>

      <!-- Cột kết quả chiết tính -->
      <div class="trang-history-card">
        <h3>Bảng chiết tính dự toán</h3>
        <?php if ($ketQuaDuToan !== null): ?>
          <div class="trang-result-box" role="region" aria-label="Kết quả dự toán">
            <div class="trang-total-banner">
              <span class="total-label">Tổng chi phí ước tính</span>
              <span class="total-amount"><?= e(number_format($ketQuaDuToan['tong_chi_phi'], 0, ',', '.')) ?> VNĐ</span>
            </div>

            <div class="trang-timeline-note">
              <span aria-hidden="true">⏱️</span>
              <div>
                <strong>Tiến độ hoàn thành dự kiến:</strong><br>
                <?= e((string)$ketQuaDuToan['tong_ngay_cong']) ?> ngày công (khoảng <?= e((string)$ketQuaDuToan['so_tuan']) ?> tuần làm việc)
              </div>
            </div>

            <table class="trang-breakdown-table">
              <thead>
                <tr>
                  <th scope="col">Hạng mục</th>
                  <th scope="col" class="text-right">Chi phí</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>
                    <strong>Nền tảng:</strong> <?= e($ketQuaDuToan['loai_web_ten']) ?>
                  </td>
                  <td class="text-right"><?= e(number_format($ketQuaDuToan['gia_goc'], 0, ',', '.')) ?> đ</td>
                </tr>
                <tr>
                  <td>
                    <strong>Số trang:</strong> <?= e((string)$ketQuaDuToan['so_trang']) ?> trang
                  </td>
                  <td class="text-right"><?= e(number_format($ketQuaDuToan['gia_so_trang'], 0, ',', '.')) ?> đ</td>
                </tr>
                <tr>
                  <td>
                    <strong>Giao diện:</strong> <?= e($ketQuaDuToan['goi_ui_ten']) ?> (x<?= e((string)$ketQuaDuToan['he_so_ui']) ?>)
                  </td>
                  <td class="text-right">Đã tính</td>
                </tr>
                <?php if (!empty($ketQuaDuToan['tien_ich'])): ?>
                  <?php foreach ($ketQuaDuToan['tien_ich'] as $tiItem): ?>
                    <tr>
                      <td>
                        &bull; <?= e($tiItem['ten']) ?>
                      </td>
                      <td class="text-right"><?= e(number_format($tiItem['gia'], 0, ',', '.')) ?> đ</td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="trang-empty-box">
            <span aria-hidden="true">📊</span>
            <p>Vui lòng chọn các thông số dự án bên cạnh và nhấn <strong>"Tính toán dự toán ngay"</strong> để nhận kết quả phân tích chi phí và tiến độ từ máy chủ.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- KHỐI CHỨC NĂNG MÁY CHỦ 2: ĐĂNG KÝ BẢN TIN CÔNG NGHỆ -->
  <section
    id="ban-tin"
    class="profile-card trang-card-section"
    aria-labelledby="bantin-heading"
  >
    <div class="trang-card-header">
      <div class="trang-card-badge">
        <span aria-hidden="true">📬</span>
        <span>Xử lý máy chủ POST &bull; Lưu trữ JSONL &bull; Mô hình PRG</span>
      </div>
      <h2 id="bantin-heading">Đăng Ký Nhận Bản Tin Đồ Án &amp; Công Nghệ</h2>
      <p class="trang-card-desc">
        Đăng ký email để nhận thông báo khi có đồ án mới, tài liệu học tập hoặc các cập nhật kỹ thuật từ Kiều Trang. Thông tin được máy chủ xác thực và ghi vào <code>storage/3120224153_bantin.jsonl</code>.
      </p>
    </div>

    <?php if ($thongBaoThanhCong !== null): ?>
      <div class="trang-alert-success" role="alert" aria-live="polite">
        <span class="trang-alert-icon" aria-hidden="true">💌</span>
        <div>
          <strong>Thành công:</strong> <?= e($thongBaoThanhCong) ?>
        </div>
      </div>
    <?php endif; ?>

    <div class="trang-layout-grid">
      <!-- Form đăng ký -->
      <div class="trang-form-card">
        <h3>Điền thông tin nhận bản tin</h3>
        <form
          action="gioithieu.php#ban-tin"
          method="post"
          class="trang-form"
          novalidate
        >
          <div class="form-group mb-3">
            <label for="ho_ten" class="form-label">
              Họ và tên của bạn <span class="bat-buoc">*</span>
            </label>
            <input
              type="text"
              id="ho_ten"
              name="ho_ten"
              class="form-control <?= isset($loiBanTin['ho_ten']) ? 'is-invalid' : '' ?>"
              placeholder="Ví dụ: Lê Thị Mai"
              value="<?= e($duLieuCu['ho_ten'] ?? '') ?>"
              required
            >
            <?php if (isset($loiBanTin['ho_ten'])): ?>
              <p class="thong-bao-loi" role="alert"><?= e($loiBanTin['ho_ten']) ?></p>
            <?php endif; ?>
          </div>

          <div class="form-group mb-3">
            <label for="email" class="form-label">
              Địa chỉ Email <span class="bat-buoc">*</span>
            </label>
            <input
              type="email"
              id="email"
              name="email"
              class="form-control <?= isset($loiBanTin['email']) ? 'is-invalid' : '' ?>"
              placeholder="mai.lt@example.com"
              value="<?= e($duLieuCu['email'] ?? '') ?>"
              required
            >
            <?php if (isset($loiBanTin['email'])): ?>
              <p class="thong-bao-loi" role="alert"><?= e($loiBanTin['email']) ?></p>
            <?php endif; ?>
          </div>

          <div class="form-group mb-3">
            <label for="chuyen_de" class="form-label">
              Chuyên đề quan tâm <span class="bat-buoc">*</span>
            </label>
            <select id="chuyen_de" name="chuyen_de" class="form-control" required>
              <option value="Web Dev & UI/UX" <?= ($duLieuCu['chuyen_de'] ?? '') === 'Web Dev & UI/UX' ? 'selected' : '' ?>>Web Dev &amp; UI/UX (Frontend, Design System)</option>
              <option value="IoT & Hệ thống nhúng" <?= ($duLieuCu['chuyen_de'] ?? '') === 'IoT & Hệ thống nhúng' ? 'selected' : '' ?>>IoT &amp; Hệ thống nhúng (ESP8266, Arduino)</option>
              <option value="AI & Khai phá dữ liệu" <?= ($duLieuCu['chuyen_de'] ?? '') === 'AI & Khai phá dữ liệu' ? 'selected' : '' ?>>AI &amp; Khai phá dữ liệu (Machine Learning, Python)</option>
            </select>
          </div>

          <div class="form-actions">
            <button type="submit" name="dang_ky_bantin" value="1" class="btn-action">
              <span aria-hidden="true">📩</span>
              <span>Đăng ký nhận tin (POST - PRG)</span>
            </button>
          </div>
        </form>
      </div>

      <!-- Lịch sử đăng ký gần nhất -->
      <div class="trang-history-card">
        <h3>Danh sách đăng ký gần đây</h3>
        <p class="trang-history-desc">Lưu trữ tệp máy chủ <code>storage/3120224153_bantin.jsonl</code>:</p>

        <?php if (empty($dsDangKy)): ?>
          <div class="trang-empty-box">
            <span>📭</span>
            <p>Chưa có ai đăng ký. Hãy là người đầu tiên!</p>
          </div>
        <?php else: ?>
          <ul class="trang-subscriber-list">
            <?php foreach ($dsDangKy as $sub): ?>
              <li class="trang-sub-item">
                <div class="trang-sub-header">
                  <strong><?= e($sub['ho_ten'] ?? 'Ẩn danh') ?></strong>
                  <span class="trang-sub-tag"><?= e($sub['chuyen_de'] ?? 'Chuyên đề') ?></span>
                </div>
                <div class="trang-sub-meta">
                  <span>📧 <?= e(substr((string)($sub['email'] ?? ''), 0, 3) . '***@' . (explode('@', (string)($sub['email'] ?? ''))[1] ?? '***')) ?></span>
                  <span>⏱️ <?= e($sub['ngay_dang_ky'] ?? '') ?></span>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- KHỐI 2: KỸ NĂNG & THÔNG TIN BỔ SUNG (BỐ CỤC GRID 2 CỘT TRÊN DESKTOP) -->
  <div class="profile-grid-mid">
    <section
      id="skills"
      class="profile-card"
      aria-labelledby="skills-heading"
    >
      <h2 id="skills-heading">Kỹ Năng Chuyên Môn</h2>
      <ul class="skills-list">
        <li>Ngôn ngữ lập trình: C++, Java, Python, C#</li>
        <li>Công nghệ web: HTML5, CSS3, PHP</li>
        <li>Cơ sở dữ liệu: SQL Server, MySQL</li>
        <li>Kỹ thuật phần cứng &amp; IoT: Arduino Uno, ESP8266 NodeMCU</li>
        <li>Công cụ: Visual Studio Code, Git/GitHub, LaTeX</li>
      </ul>
    </section>

    <aside
      id="contact"
      class="profile-card profile-aside"
      aria-labelledby="contact-heading"
    >
      <h3 id="contact-heading">Thông Tin Bổ Sung</h3>
      <ul>
        <li>
          <span>🎓</span>
          <div><strong>MSSV:</strong><br />3120224153</div>
        </li>
        <li>
          <span>🏫</span>
          <div><strong>Lớp:</strong><br />24CNTT2 - Khoa Toán – Tin</div>
        </li>
        <li>
          <span>💻</span>
          <div>
            <strong>Vai trò:</strong><br />Phụ trách trang Chi tiết sự kiện
          </div>
        </li>
      </ul>
    </aside>
  </div>

  <!-- KHỐI 3: THỜI KHÓA BIỂU HỌC TẬP -->
  <section
    id="schedule"
    class="profile-card schedule-section"
    aria-labelledby="schedule-heading"
  >
    <h2 id="schedule-heading">Thời Khóa Biểu Tuần</h2>
    <div
      class="bang-wrapper"
      tabindex="0"
      role="region"
      aria-label="Bảng thời khóa biểu học tập trong tuần"
    >
      <table class="schedule-table">
        <caption>Thời Khóa Biểu Học Tập Trong Tuần - Học kỳ 1 Năm học 2026-2027</caption>
        <thead>
          <tr>
            <th scope="col">Tiết</th>
            <th scope="col">Thứ 2</th>
            <th scope="col">Thứ 3</th>
            <th scope="col">Thứ 4</th>
            <th scope="col">Thứ 5</th>
            <th scope="col">Thứ 6</th>
            <th scope="col">Thứ 7</th>
            <th scope="col">Chủ nhật</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <th scope="row">Tiết 1</th>
            <td rowspan="6"></td>
            <td rowspan="2"></td>
            <td rowspan="3"><strong>Hệ quản trị CSDL</strong><br><span class="room-badge">B3-402</span></td>
            <td rowspan="3"></td>
            <td rowspan="6"></td>
            <td rowspan="3"><strong>Thiết kế &amp; Lập trình web</strong><br><span class="room-badge">B3-303</span></td>
            <td rowspan="6"></td>
          </tr>
          <tr><th scope="row">Tiết 2</th></tr>
          <tr>
            <th scope="row">Tiết 3</th>
            <td rowspan="3"><strong>Lịch sử Đảng CSVN</strong><br><span class="room-badge">A6-502</span></td>
          </tr>
          <tr>
            <th scope="row">Tiết 4</th>
            <td rowspan="3"></td>
            <td rowspan="3"><strong>Khai phá dữ liệu</strong><br><span class="room-badge">A5-404B</span></td>
            <td rowspan="3"></td>
          </tr>
          <tr><th scope="row">Tiết 5</th></tr>
          <tr><th scope="row">Tiết 6</th><td></td></tr>
          <tr>
            <th scope="row">Tiết 7</th>
            <td rowspan="2"><strong>An toàn thông tin</strong><br><span class="room-badge">A6-401</span></td>
            <td rowspan="6"></td>
            <td rowspan="6"></td>
            <td rowspan="3"></td>
            <td rowspan="6"></td>
            <td rowspan="6"></td>
            <td rowspan="6"></td>
          </tr>
          <tr><th scope="row">Tiết 8</th></tr>
          <tr><th scope="row">Tiết 9</th><td rowspan="4"></td></tr>
          <tr>
            <th scope="row">Tiết 10</th>
            <td rowspan="3"><strong>Công nghệ phần mềm</strong><br><span class="room-badge">B3-304</span></td>
          </tr>
          <tr><th scope="row">Tiết 11</th></tr>
          <tr><th scope="row">Tiết 12</th></tr>
        </tbody>
      </table>
    </div>
  </section>
</main>

<script src="js/canhan.js"></script>

<?php require __DIR__ . '/../../inc/footer.php'; ?>
