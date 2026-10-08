<?php
/**
 * thanhvien/3120224024_Dat/gioithieu.php
 * Trang giới thiệu cá nhân: Lê Phú Đạt (MSSV: 3120224024 - Nhóm 14).
 * Chức năng máy chủ:
 * 1. Máy tính điểm học phần (0.2*A1 + 0.3*A2 + 0.5*A3, kiểm tra 0-10, quy đổi hệ 4 & chữ, PRG pattern).
 * 2. Tải lên ảnh đại diện an toàn (kiểm tra MIME thật finfo, kích thước <= 2MB, lưu storage/avatars/, đổi tên ngẫu nhiên).
 * Cách thử:
 * - Nhập 3 cột điểm A1, A2, A3 và bấm "Tính điểm" xem kết quả quy đổi (thử nhập điểm âm hoặc >10 để xem lỗi máy chủ).
 * - Chọn file ảnh (JPG/PNG/WEBP <= 2MB) bấm "Tải lên ảnh mới" kiểm tra MIME thật và cập nhật avatar tức thì.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../inc/config.php';

// Đường dẫn tương đối về thư mục gốc
$goc = '../../';
$tieuDe = 'Profile cá nhân - Lê Phú Đạt | UniEvent';
$trang = 'gioi-thieu';

// ============================================================================
// CHỨC NĂNG 1: MÁY TÍNH ĐIỂM HỌC PHẦN (POST -> VALIDATION -> PRG)
// ============================================================================
$loiDiem = $_SESSION['dat_diem_loi'] ?? [];
$duLieuDiemCu = $_SESSION['dat_diem_cu'] ?? [
    'hoc_phan' => 'Thiết kế & Lập trình Web',
    'diem_a1' => '',
    'diem_a2' => '',
    'diem_a3' => '',
];
$ketQuaDiem = $_SESSION['dat_diem_ketqua'] ?? null;
unset($_SESSION['dat_diem_loi'], $_SESSION['dat_diem_cu'], $_SESSION['dat_diem_ketqua']);

if (isset($_GET['demo_diem']) && $ketQuaDiem === null) {
    $ketQuaDiem = [
        'hoc_phan' => 'Thiết kế & Lập trình Web',
        'diem_a1' => 8.5,
        'diem_a2' => 8.0,
        'diem_a3' => 9.0,
        'dtb10' => 8.60,
        'he4' => 3.8,
        'diem_chu' => 'A',
        'xep_loai' => 'Giỏi / Xuất sắc',
        'ket_luan' => 'Đạt (Qua môn)',
        'badge' => 'thanh-cong',
        'thoi_gian' => date('d/m/Y H:i'),
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tinh_diem'])) {
    $hocPhan = trim((string)($_POST['hoc_phan'] ?? ''));
    $strA1 = trim((string)($_POST['diem_a1'] ?? ''));
    $strA2 = trim((string)($_POST['diem_a2'] ?? ''));
    $strA3 = trim((string)($_POST['diem_a3'] ?? ''));

    $loi = [];

    if ($hocPhan === '') {
        $loi['hoc_phan'] = 'Vui lòng chọn học phần cần tính điểm.';
    }

    if ($strA1 === '') {
        $loi['diem_a1'] = 'Vui lòng nhập điểm chuyên cần A1.';
    } elseif (!is_numeric($strA1)) {
        $loi['diem_a1'] = 'Điểm A1 phải là số thực hợp lệ.';
    } else {
        $a1 = (float)$strA1;
        if ($a1 < 0.0 || $a1 > 10.0) {
            $loi['diem_a1'] = 'Điểm A1 phải từ 0.0 đến 10.0.';
        }
    }

    if ($strA2 === '') {
        $loi['diem_a2'] = 'Vui lòng nhập điểm giữa kỳ A2.';
    } elseif (!is_numeric($strA2)) {
        $loi['diem_a2'] = 'Điểm A2 phải là số thực hợp lệ.';
    } else {
        $a2 = (float)$strA2;
        if ($a2 < 0.0 || $a2 > 10.0) {
            $loi['diem_a2'] = 'Điểm A2 phải từ 0.0 đến 10.0.';
        }
    }

    if ($strA3 === '') {
        $loi['diem_a3'] = 'Vui lòng nhập điểm thi cuối kỳ A3.';
    } elseif (!is_numeric($strA3)) {
        $loi['diem_a3'] = 'Điểm A3 phải là số thực hợp lệ.';
    } else {
        $a3 = (float)$strA3;
        if ($a3 < 0.0 || $a3 > 10.0) {
            $loi['diem_a3'] = 'Điểm A3 phải từ 0.0 đến 10.0.';
        }
    }

    if (!empty($loi)) {
        $_SESSION['dat_diem_loi'] = $loi;
        $_SESSION['dat_diem_cu'] = [
            'hoc_phan' => $hocPhan,
            'diem_a1' => $strA1,
            'diem_a2' => $strA2,
            'diem_a3' => $strA3,
        ];
        header('Location: gioithieu.php#tinh-diem');
        exit;
    }

    $a1Val = (float)$strA1;
    $a2Val = (float)$strA2;
    $a3Val = (float)$strA3;
    $dtb10 = round(0.2 * $a1Val + 0.3 * $a2Val + 0.5 * $a3Val, 2);

    if ($dtb10 >= 8.5) {
        $diemChu = 'A';
        $he4 = 4.0;
        $xepLoai = 'Giỏi / Xuất sắc';
        $ketLuan = 'Đạt (Qua môn)';
        $loaiBadge = 'thanh-cong';
    } elseif ($dtb10 >= 7.0) {
        $diemChu = 'B';
        $he4 = 3.0;
        $xepLoai = 'Khá';
        $ketLuan = 'Đạt (Qua môn)';
        $loaiBadge = 'thanh-cong';
    } elseif ($dtb10 >= 5.5) {
        $diemChu = 'C';
        $he4 = 2.0;
        $xepLoai = 'Trung bình';
        $ketLuan = 'Đạt (Qua môn)';
        $loaiBadge = 'canh-bao';
    } elseif ($dtb10 >= 4.0) {
        $diemChu = 'D';
        $he4 = 1.0;
        $xepLoai = 'Trung bình yếu';
        $ketLuan = 'Đạt (Qua môn)';
        $loaiBadge = 'canh-bao';
    } else {
        $diemChu = 'F';
        $he4 = 0.0;
        $xepLoai = 'Kém';
        $ketLuan = 'Không đạt (Học lại)';
        $loaiBadge = 'that-bai';
    }

    $_SESSION['dat_diem_ketqua'] = [
        'hoc_phan' => $hocPhan,
        'diem_a1' => $a1Val,
        'diem_a2' => $a2Val,
        'diem_a3' => $a3Val,
        'dtb10' => $dtb10,
        'he4' => $he4,
        'diem_chu' => $diemChu,
        'xep_loai' => $xepLoai,
        'ket_luan' => $ketLuan,
        'badge' => $loaiBadge,
        'thoi_gian' => date('d/m/Y H:i'),
    ];

    header('Location: gioithieu.php#tinh-diem');
    exit;
}

// ============================================================================
// CHỨC NĂNG 2: TẢI LÊN ẢNH ĐẠI DIỆN AN TOÀN (POST FILE -> VALIDATION -> PRG)
// ============================================================================
$loiUpload = $_SESSION['dat_upload_loi'] ?? null;
$thanhCongUpload = $_SESSION['dat_upload_ok'] ?? null;
unset($_SESSION['dat_upload_loi'], $_SESSION['dat_upload_ok']);

$avatarHienTai = $_SESSION['dat_avatar_file'] ?? 'avatar.jpg';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tai_avatar'])) {
    if (!isset($_FILES['anh_dai_dien']) || $_FILES['anh_dai_dien']['error'] === UPLOAD_ERR_NO_FILE) {
        $_SESSION['dat_upload_loi'] = 'Vui lòng chọn một tệp hình ảnh để tải lên.';
        header('Location: gioithieu.php#upload-avatar');
        exit;
    }

    $file = $_FILES['anh_dai_dien'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['dat_upload_loi'] = 'Đã xảy ra lỗi trong quá trình tải tệp lên máy chủ.';
        header('Location: gioithieu.php#upload-avatar');
        exit;
    }

    // Kiểm tra dung lượng (tối đa 2MB = 2 * 1024 * 1024 bytes)
    $maxBytes = 2 * 1024 * 1024;
    if ($file['size'] > $maxBytes) {
        $_SESSION['dat_upload_loi'] = 'Kích thước tệp quá lớn (tối đa 2MB). Dung lượng tệp hiện tại: ' . round($file['size'] / 1024 / 1024, 2) . 'MB.';
        header('Location: gioithieu.php#upload-avatar');
        exit;
    }

    // Kiểm tra MIME type thật thông qua finfo_file
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeThat = $finfo ? finfo_file($finfo, $file['tmp_name']) : '';
    if ($finfo) {
        finfo_close($finfo);
    }

    $mimeHopLe = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    if (!isset($mimeHopLe[$mimeThat])) {
        $_SESSION['dat_upload_loi'] = 'Định dạng tệp không được hỗ trợ. Chỉ chấp nhận ảnh JPG, PNG hoặc WEBP hợp lệ (Phát hiện: ' . htmlspecialchars((string)$mimeThat) . ').';
        header('Location: gioithieu.php#upload-avatar');
        exit;
    }

    $duoiMoRong = $mimeHopLe[$mimeThat];
    $tenMoi = 'avatar_dat_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $duoiMoRong;
    $thuMucLuu = __DIR__ . '/../../storage/avatars';

    if (!is_dir($thuMucLuu)) {
        mkdir($thuMucLuu, 0777, true);
    }

    $duongDanDich = $thuMucLuu . '/' . $tenMoi;

    if (move_uploaded_file($file['tmp_name'], $duongDanDich)) {
        $_SESSION['dat_avatar_file'] = '../../storage/avatars/' . $tenMoi;
        $_SESSION['dat_upload_ok'] = 'Tải ảnh đại diện thành công! Tệp an toàn đã được lưu vào hệ thống máy chủ.';
    } else {
        $_SESSION['dat_upload_loi'] = 'Không thể lưu tệp ảnh lên máy chủ. Vui lòng thử lại sau.';
    }

    header('Location: gioithieu.php#upload-avatar');
    exit;
}

require __DIR__ . '/../../inc/header.php';
?>

<link rel="stylesheet" href="css/style.css">

<!-- HEADER PROFILE -->
<header class="profile-header">
  <div class="container">
    <span class="profile-header__badge">Thành viên Ban Kỹ thuật &bull; UniEvent</span>
    <h1>Profile cá nhân</h1>
    <p>Chào mừng bạn đến trang giới thiệu của Lê Phú Đạt (Đạt)</p>
  </div>
</header>

<!-- NAVIGATION BAR -->
<nav class="profile-nav" aria-label="Điều hướng trang cá nhân">
  <div class="container">
    <ul>
      <li><a href="#about">Giới thiệu</a></li>
      <li><a href="#upload-avatar">Đổi ảnh đại diện</a></li>
      <li><a href="#tinh-diem">Máy tính điểm</a></li>
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
          src="<?= e($avatarHienTai) ?>"
          alt="Ảnh chân dung đại diện của Lê Phú Đạt"
          width="180"
          height="180"
          loading="lazy"
        />
      </div>
      <div class="about-info">
        <h2 id="about-heading">Giới Thiệu Bản Thân</h2>
        <span class="meta-pill">MSSV: 3120224024 &bull; Nhóm 14</span>
        <p>
          Xin chào! Mình là Lê Phú Đạt, sinh viên chuyên ngành Sư phạm Tin
          học / CNTT tại Trường Đại học Sư phạm - Đại học Đà Nẵng. Đam mê
          xây dựng các giao diện web hiện đại, tối ưu trải nghiệm và khả
          năng tiếp cận (a11y).
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
      <div class="projects-list">
        <div class="project-item">
          <p>
            <strong>Dự án tiêu biểu:</strong> Hệ thống website "UniEvent -
            Quản lý Sự kiện ĐHSP Đà Nẵng" (HTML5 Semantic, Modern CSS Grid
            &amp; Flexbox, Responsive Mobile-First).
          </p>
        </div>
        <div class="project-item">
          <p>
            <strong>Sở thích:</strong> Lập trình web, nghiên cứu kiến trúc
            máy tính &amp; phần cứng, chơi thể thao và tham gia hoạt động
            Đoàn - Hội.
          </p>
        </div>
        <div class="project-item">
          <p>
            <strong>Mục tiêu:</strong> Trở thành Frontend Developer chuyên
            nghiệp, thành thạo xây dựng Design System và Progressive Web
            Apps.
          </p>
        </div>
      </div>
    </article>
  </div>

  <!-- KHỐI CHỨC NĂNG MÁY CHỦ 1: TẢI LÊN ẢNH ĐẠI DIỆN AN TOÀN -->
  <section
    id="upload-avatar"
    class="profile-card dat-card-section"
    aria-labelledby="upload-avatar-heading"
  >
    <div class="dat-card-header">
      <div class="dat-card-badge">
        <span aria-hidden="true">🛡️</span>
        <span>Xử lý máy chủ &bull; finfo MIME check &bull; PRG Pattern</span>
      </div>
      <h2 id="upload-avatar-heading">Tải Lên Ảnh Đại Diện An Toàn</h2>
      <p class="dat-card-desc">
        Hệ thống kiểm tra loại MIME thực tế bằng <code>finfo_file</code>, giới hạn dung lượng &le; 2MB, đổi tên ngẫu nhiên chống ghi đè và lưu trữ trong thư mục <code>storage/avatars/</code>.
      </p>
    </div>

    <?php if ($thanhCongUpload !== null): ?>
      <div class="dat-alert-success" role="alert" aria-live="polite">
        <span class="dat-alert-icon" aria-hidden="true">✅</span>
        <div>
          <strong>Thành công:</strong> <?= e($thanhCongUpload) ?>
        </div>
      </div>
    <?php endif; ?>

    <?php if ($loiUpload !== null): ?>
      <div class="dat-alert-error" role="alert" aria-live="polite">
        <span class="dat-alert-icon" aria-hidden="true">⚠️</span>
        <div>
          <strong>Lỗi máy chủ:</strong> <?= e($loiUpload) ?>
        </div>
      </div>
    <?php endif; ?>

    <form
      action="gioithieu.php#upload-avatar"
      method="post"
      enctype="multipart/form-data"
      class="dat-form"
      novalidate
    >
      <div class="form-group mb-3">
        <label for="anh_dai_dien" class="form-label">
          Chọn tệp hình ảnh mới (JPG, PNG, WEBP &le; 2MB):
        </label>
        <input
          type="file"
          id="anh_dai_dien"
          name="anh_dai_dien"
          accept="image/jpeg,image/png,image/webp"
          class="form-control"
          required
        >
        <small class="form-hint">Máy chủ không tin cậy phần mở rộng do trình duyệt gửi lên mà kiểm tra trực tiếp byte nhị phân của tệp.</small>
      </div>

      <div class="form-actions">
        <button type="submit" name="tai_avatar" value="1" class="btn-action">
          <span aria-hidden="true">📤</span>
          <span>Tải lên ảnh mới (POST - PRG)</span>
        </button>
      </div>
    </form>
  </section>

  <!-- KHỐI CHỨC NĂNG MÁY CHỦ 2: MÁY TÍNH ĐIỂM HỌC PHẦN -->
  <section
    id="tinh-diem"
    class="profile-card dat-card-section"
    aria-labelledby="tinh-diem-heading"
  >
    <div class="dat-card-header">
      <div class="dat-card-badge">
        <span aria-hidden="true">🧮</span>
        <span>Xử lý máy chủ &bull; Công thức 0.2*A1 + 0.3*A2 + 0.5*A3 &bull; PRG</span>
      </div>
      <h2 id="tinh-diem-heading">Máy Tính Điểm Học Phần</h2>
      <p class="dat-card-desc">
        Công thức tính điểm trung bình học phần theo quy chế tín chỉ:
        <strong>ĐTB = 0,2 &times; A1 + 0,3 &times; A2 + 0,5 &times; A3</strong>.
        Máy chủ kiểm tra điểm số thực trong khoảng từ 0.0 đến 10.0 và quy đổi sang thang 4 kèm điểm chữ.
      </p>
    </div>

    <?php if ($ketQuaDiem !== null): ?>
      <div class="dat-grade-result dat-grade-result--<?= e($ketQuaDiem['badge']) ?>" role="region" aria-label="Kết quả tính điểm">
        <div class="dat-grade-result-top">
          <div>
            <span class="dat-time-badge">⏱️ Đã tính lúc: <?= e($ketQuaDiem['thoi_gian']) ?></span>
            <h3 class="dat-course-name">Môn học: <?= e($ketQuaDiem['hoc_phan']) ?></h3>
          </div>
          <span class="dat-status-pill dat-status-pill--<?= e($ketQuaDiem['badge']) ?>">
            <?= e($ketQuaDiem['ket_luan']) ?>
          </span>
        </div>

        <div class="dat-grade-grid">
          <div class="dat-grade-box">
            <span>A1 (Chuyên cần 20%)</span>
            <strong><?= e($ketQuaDiem['diem_a1']) ?></strong>
          </div>
          <div class="dat-grade-box">
            <span>A2 (Giữa kỳ 30%)</span>
            <strong><?= e($ketQuaDiem['diem_a2']) ?></strong>
          </div>
          <div class="dat-grade-box">
            <span>A3 (Cuối kỳ 50%)</span>
            <strong><?= e($ketQuaDiem['diem_a3']) ?></strong>
          </div>
          <div class="dat-grade-box highlight-10">
            <span>ĐTB Hệ 10</span>
            <strong><?= e($ketQuaDiem['dtb10']) ?></strong>
          </div>
          <div class="dat-grade-box highlight-4">
            <span>ĐTB Hệ 4</span>
            <strong><?= e($ketQuaDiem['he4']) ?></strong>
          </div>
          <div class="dat-grade-box highlight-letter">
            <span>Điểm Chữ / Xếp Loại</span>
            <strong><?= e($ketQuaDiem['diem_chu']) ?> (<?= e($ketQuaDiem['xep_loai']) ?>)</strong>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <div class="dat-form-card">
      <h3 id="form-diem-title">Nhập điểm các cột thành phần:</h3>
      <form
        action="gioithieu.php#tinh-diem"
        method="post"
        class="dat-form"
        novalidate
        aria-labelledby="form-diem-title"
      >
        <div class="form-group mb-3">
          <label for="hoc_phan" class="form-label">
            Học phần / Môn học <span class="bat-buoc" aria-hidden="true">*</span>
          </label>
          <select
            id="hoc_phan"
            name="hoc_phan"
            class="form-control <?= isset($loiDiem['hoc_phan']) ? 'is-invalid' : '' ?>"
            required
            <?= isset($loiDiem['hoc_phan']) ? 'aria-invalid="true" aria-describedby="loi-hp"' : '' ?>
          >
            <?php
            $dsMon = [
                'Thiết kế & Lập trình Web' => 'Thiết kế & Lập trình Web (31231755)',
                'Hệ quản trị CSDL' => 'Hệ quản trị CSDL (31241283)',
                'Công nghệ phần mềm' => 'Công nghệ phần mềm (31231016)',
                'Khai phá dữ liệu' => 'Khai phá dữ liệu (31231330)',
                'An toàn thông tin' => 'An toàn thông tin (31221010)',
            ];
            $monChon = $duLieuDiemCu['hoc_phan'] ?? 'Thiết kế & Lập trình Web';
            foreach ($dsMon as $val => $nhan):
            ?>
              <option value="<?= e($val) ?>" <?= $monChon === $val ? 'selected' : '' ?>>
                <?= e($nhan) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <?php if (isset($loiDiem['hoc_phan'])): ?>
            <p class="thong-bao-loi" id="loi-hp" role="alert"><?= e($loiDiem['hoc_phan']) ?></p>
          <?php endif; ?>
        </div>

        <div class="dat-inputs-row">
          <div class="form-group mb-3">
            <label for="diem_a1" class="form-label">
              Điểm A1: Chuyên cần (20%) <span class="bat-buoc" aria-hidden="true">*</span>
            </label>
            <input
              type="number"
              step="0.1"
              min="0"
              max="10"
              id="diem_a1"
              name="diem_a1"
              class="form-control <?= isset($loiDiem['diem_a1']) ? 'is-invalid' : '' ?>"
              placeholder="0.0 - 10.0"
              value="<?= e($duLieuDiemCu['diem_a1'] ?? '') ?>"
              required
              <?= isset($loiDiem['diem_a1']) ? 'aria-invalid="true" aria-describedby="loi-a1"' : '' ?>
            >
            <?php if (isset($loiDiem['diem_a1'])): ?>
              <p class="thong-bao-loi" id="loi-a1" role="alert"><?= e($loiDiem['diem_a1']) ?></p>
            <?php endif; ?>
          </div>

          <div class="form-group mb-3">
            <label for="diem_a2" class="form-label">
              Điểm A2: Giữa kỳ (30%) <span class="bat-buoc" aria-hidden="true">*</span>
            </label>
            <input
              type="number"
              step="0.1"
              min="0"
              max="10"
              id="diem_a2"
              name="diem_a2"
              class="form-control <?= isset($loiDiem['diem_a2']) ? 'is-invalid' : '' ?>"
              placeholder="0.0 - 10.0"
              value="<?= e($duLieuDiemCu['diem_a2'] ?? '') ?>"
              required
              <?= isset($loiDiem['diem_a2']) ? 'aria-invalid="true" aria-describedby="loi-a2"' : '' ?>
            >
            <?php if (isset($loiDiem['diem_a2'])): ?>
              <p class="thong-bao-loi" id="loi-a2" role="alert"><?= e($loiDiem['diem_a2']) ?></p>
            <?php endif; ?>
          </div>

          <div class="form-group mb-3">
            <label for="diem_a3" class="form-label">
              Điểm A3: Thi cuối kỳ (50%) <span class="bat-buoc" aria-hidden="true">*</span>
            </label>
            <input
              type="number"
              step="0.1"
              min="0"
              max="10"
              id="diem_a3"
              name="diem_a3"
              class="form-control <?= isset($loiDiem['diem_a3']) ? 'is-invalid' : '' ?>"
              placeholder="0.0 - 10.0"
              value="<?= e($duLieuDiemCu['diem_a3'] ?? '') ?>"
              required
              <?= isset($loiDiem['diem_a3']) ? 'aria-invalid="true" aria-describedby="loi-a3"' : '' ?>
            >
            <?php if (isset($loiDiem['diem_a3'])): ?>
              <p class="thong-bao-loi" id="loi-a3" role="alert"><?= e($loiDiem['diem_a3']) ?></p>
            <?php endif; ?>
          </div>
        </div>

        <div class="form-actions mt-2">
          <button type="submit" name="tinh_diem" value="1" class="btn-action">
            <span aria-hidden="true">📊</span>
            <span>Tính điểm học phần (POST - PRG)</span>
          </button>
        </div>
      </form>
    </div>
  </section>

  <!-- KHỐI 2: KỸ NĂNG & THÔNG TIN BỔ SUNG (BỐ CỤC GRID 2 CỘT TRÊN DESKTOP) -->
  <div class="profile-grid-mid">
    <!-- Danh sách Kỹ năng (Flexbox / Grid dạng Chip Badge) -->
    <section
      id="skills"
      class="profile-card"
      aria-labelledby="skills-heading"
    >
      <h2 id="skills-heading">Kỹ Năng</h2>
      <ul class="skills-list">
        <li>HTML5 Semantic Tags &amp; a11y</li>
        <li>Modern CSS (CSS Grid &amp; Flexbox)</li>
        <li>Responsive Web Design (Mobile-First)</li>
        <li>Tùy biến CSS Variables &amp; Tokens</li>
        <li>Git &amp; GitHub Version Control</li>
        <li>Bootstrap 5 Framework &amp; Utility</li>
      </ul>
    </section>

    <!-- Thông tin liên hệ bổ sung -->
    <aside
      id="contact"
      class="profile-card profile-aside"
      aria-labelledby="contact-heading"
    >
      <h3 id="contact-heading">Thông Tin Bổ Sung</h3>
      <ul>
        <li>
          <span>📧</span>
          <div>
            <strong>Email:</strong><br />
            <a href="mailto:lephudat1304@gmail.com">lephudat1304@gmail.com</a>
          </div>
        </li>
        <li>
          <span>📞</span>
          <div>
            <strong>Số điện thoại:</strong><br />
            <a href="tel:0905494761">0905494761</a>
          </div>
        </li>
        <li>
          <span>🏫</span>
          <div>
            <strong>Đơn vị:</strong><br />
            <span>Khoa Toán - Tin học, Trường ĐHSP - ĐHĐN</span>
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
    <p class="schedule-note">
      <span>💡</span>
      <em
        >Mẹo: Trên điện thoại, bạn có thể vuốt sang trái/phải khung bảng bên
        dưới để xem toàn bộ các ngày trong tuần.</em
      >
    </p>

    <!-- Khung cuộn ngang độc lập chống tràn trang -->
    <div
      class="bang-wrapper"
      tabindex="0"
      role="region"
      aria-label="Bảng thời khóa biểu học tập trong tuần"
    >
      <table class="schedule-table">
        <caption>
          Thời Khóa Biểu Học Tập Trong Tuần - Học kỳ 2 Năm học 2024-2025
        </caption>
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
          <!-- Tiết 1 -->
          <tr>
            <th scope="row">1</th>
            <td></td>
            <td></td>
            <td rowspan="3">
              <strong>Hệ quản trị CSDL</strong>
              31241283 - 24-0102<br />
              <span class="room-badge">Phòng: A5-404B</span>
            </td>
            <td></td>
            <td></td>
            <td rowspan="3">
              <strong>Thiết kế &amp; Lập trình Web</strong>
              31231755 - 24-0102<br />
              <span class="room-badge">Phòng: B3-303</span>
            </td>
            <td></td>
          </tr>

          <!-- Tiết 2 -->
          <tr>
            <th scope="row">2</th>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
          </tr>

          <!-- Tiết 3 -->
          <tr>
            <th scope="row">3</th>
            <td></td>
            <td rowspan="2">
              <strong>Lịch sử Đảng CSVN</strong>
              21221904 - 24-0311<br />
              <span class="room-badge">Phòng: A6-502</span>
            </td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
          </tr>

          <!-- Tiết 4 -->
          <tr>
            <th scope="row">4</th>
            <td></td>
            <td></td>
            <td rowspan="3">
              <strong>Khai phá dữ liệu</strong>
              31231330 - 24-0102<br />
              <span class="room-badge">Phòng: A5-404B</span>
            </td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
          </tr>

          <!-- Tiết 5 -->
          <tr>
            <th scope="row">5</th>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
          </tr>

          <!-- Tiết 6 -->
          <tr>
            <th scope="row">6</th>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
          </tr>

          <!-- Tiết 7 -->
          <tr>
            <th scope="row">7</th>
            <td rowspan="3">
              <strong>An toàn thông tin</strong>
              31221010 - 24-0102<br />
              <span class="room-badge">Phòng: A6-401</span>
            </td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
          </tr>

          <!-- Tiết 8 -->
          <tr>
            <th scope="row">8</th>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
          </tr>

          <!-- Tiết 9 -->
          <tr>
            <th scope="row">9</th>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
          </tr>

          <!-- Tiết 10 -->
          <tr>
            <th scope="row">10</th>
            <td></td>
            <td></td>
            <td></td>
            <td rowspan="3">
              <strong>Công nghệ phần mềm</strong>
              31231016 - 24-0103<br />
              <span class="room-badge">Phòng: B3-304</span>
            </td>
            <td></td>
            <td></td>
            <td></td>
          </tr>

          <!-- Tiết 11 -->
          <tr>
            <th scope="row">11</th>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
          </tr>

          <!-- Tiết 12 -->
          <tr>
            <th scope="row">12</th>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
          </tr>
        </tbody>
      </table>
    </div>
  </section>
</main>

<script src="js/canhan.js"></script>

<?php require __DIR__ . '/../../inc/footer.php'; ?>

