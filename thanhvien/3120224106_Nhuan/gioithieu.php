<?php
/**
 * thanhvien/3120224106_Nhuan/gioithieu.php
 * Trang giới thiệu cá nhân: Phan Nhuận (MSSV: 3120224106 - Nhóm 14).
 * Chức năng máy chủ:
 * 1. Khảo sát & Đánh giá kỹ năng chuyên môn (POST -> Validation -> Lưu storage/3120224106_danhgia.jsonl -> PRG -> Flash Session).
 * 2. Trắc nghiệm nhanh kiến thức Web & Nhận kết quả tự động (POST -> Chấm điểm máy chủ -> PRG -> Flash Session).
 * Cách thử:
 * - Điền họ tên, chọn kỹ năng, chọn số sao (1-5) và bấm "Gửi đánh giá" kiểm tra PRG và dữ liệu lưu file.
 * - Làm trắc nghiệm 3 câu hỏi kiến thức Web bên dưới, bấm "Nộp bài & Chấm điểm" xem máy chủ chấm điểm và phản hồi tức thì.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../inc/config.php';

// Đường dẫn tương đối về thư mục gốc
$goc = '../../';
$tieuDe = 'Profile cá nhân - Phan Nhuận | UniEvent';
$trang = 'gioi-thieu';

// ============================================================================
// CHỨC NĂNG 1: BIỂU MẪU ĐÁNH GIÁ KỸ NĂNG (POST -> VALIDATION -> JSONL -> PRG)
// ============================================================================
$tepDanhGiaNhuan = __DIR__ . '/../../storage/3120224106_danhgia.jsonl';
if (!is_dir(dirname($tepDanhGiaNhuan))) {
    mkdir(dirname($tepDanhGiaNhuan), 0777, true);
}

$loiDanhGia = $_SESSION['nhuan_dg_loi'] ?? [];
$duLieuCu = $_SESSION['nhuan_dg_cu'] ?? [
    'nguoi_gui' => '',
    'ky_nang' => 'HTML5 / Semantic & a11y',
    'so_sao' => '5',
    'gop_y' => '',
];
$thongBaoThanhCong = $_SESSION['nhuan_dg_ok'] ?? null;
unset($_SESSION['nhuan_dg_loi'], $_SESSION['nhuan_dg_cu'], $_SESSION['nhuan_dg_ok']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gui_danh_gia'])) {
    $nguoiGui = trim((string)($_POST['nguoi_gui'] ?? ''));
    $kyNang = trim((string)($_POST['ky_nang'] ?? ''));
    $strSao = trim((string)($_POST['so_sao'] ?? '5'));
    $gopY = trim((string)($_POST['gop_y'] ?? ''));

    $loi = [];

    if ($nguoiGui === '') {
        $loi['nguoi_gui'] = 'Vui lòng nhập tên của bạn.';
    } elseif (mb_strlen($nguoiGui) < 2 || mb_strlen($nguoiGui) > 50) {
        $loi['nguoi_gui'] = 'Tên phải từ 2 đến 50 ký tự.';
    }

    $dsKyNang = [
        'HTML5 / Semantic & a11y',
        'Modern CSS & Responsive',
        'PHP Server-Side & MVC',
        'Lập trình JavaScript ES6+',
        'Làm việc nhóm & Git/GitHub',
    ];
    if (!in_array($kyNang, $dsKyNang, true)) {
        $loi['ky_nang'] = 'Kỹ năng đánh giá không hợp lệ.';
    }

    $soSao = filter_var($strSao, FILTER_VALIDATE_INT);
    if ($soSao === false || $soSao < 1 || $soSao > 5) {
        $loi['so_sao'] = 'Mức điểm đánh giá phải từ 1 đến 5 sao.';
    }

    if ($gopY !== '' && (mb_strlen($gopY) < 5 || mb_strlen($gopY) > 500)) {
        $loi['gop_y'] = 'Lời góp ý nếu có phải từ 5 đến 500 ký tự.';
    }

    if (!empty($loi)) {
        $_SESSION['nhuan_dg_loi'] = $loi;
        $_SESSION['nhuan_dg_cu'] = [
            'nguoi_gui' => $nguoiGui,
            'ky_nang' => $kyNang,
            'so_sao' => $strSao,
            'gop_y' => $gopY,
        ];
        header('Location: gioithieu.php#danh-gia');
        exit;
    }

    $banGhi = [
        'id' => 'DG' . date('YmdHis') . rand(100, 999),
        'nguoi_gui' => $nguoiGui,
        'ky_nang' => $kyNang,
        'so_sao' => $soSao,
        'gop_y' => $gopY !== '' ? $gopY : 'Rất tốt!',
        'ngay_gui' => date('d/m/Y H:i'),
    ];

    file_put_contents($tepDanhGiaNhuan, json_encode($banGhi, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND | LOCK_EX);
    $_SESSION['nhuan_dg_ok'] = 'Cảm ơn bạn ' . $nguoiGui . ' đã gửi đánh giá kỹ năng cho Phan Nhuận!';
    header('Location: gioithieu.php#danh-gia');
    exit;
}

// Đọc danh sách 5 đánh giá gần nhất
$dsDanhGia = [];
if (file_exists($tepDanhGiaNhuan) && is_readable($tepDanhGiaNhuan)) {
    $dongFile = file($tepDanhGiaNhuan, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($dongFile !== false) {
        $dongFile = array_reverse($dongFile);
        $dongFile = array_slice($dongFile, 0, 5);
        foreach ($dongFile as $d) {
            $item = json_decode($d, true);
            if (is_array($item)) {
                $dsDanhGia[] = $item;
            }
        }
    }
}

// ============================================================================
// CHỨC NĂNG 2: TRẮC NGHIỆM KIẾN THỨC WEB & CHẤM ĐIỂM SERVER-SIDE (POST -> PRG)
// ============================================================================
$ketQuaQuiz = $_SESSION['nhuan_quiz_kq'] ?? null;
$loiQuiz = $_SESSION['nhuan_quiz_loi'] ?? null;
unset($_SESSION['nhuan_quiz_kq'], $_SESSION['nhuan_quiz_loi']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nop_quiz'])) {
    $tenHocVien = trim((string)($_POST['ten_hoc_vien'] ?? ''));
    $cau1 = trim((string)($_POST['cau1'] ?? ''));
    $cau2 = trim((string)($_POST['cau2'] ?? ''));
    $cau3 = trim((string)($_POST['cau3'] ?? ''));

    if ($tenHocVien === '') {
        $_SESSION['nhuan_quiz_loi'] = 'Vui lòng nhập tên của bạn trước khi nộp bài trắc nghiệm.';
        header('Location: gioithieu.php#trac-nghiem');
        exit;
    }

    if ($cau1 === '' || $cau2 === '' || $cau3 === '') {
        $_SESSION['nhuan_quiz_loi'] = 'Vui lòng trả lời đầy đủ cả 3 câu hỏi trắc nghiệm.';
        header('Location: gioithieu.php#trac-nghiem');
        exit;
    }

    // Đáp án chuẩn
    // Câu 1: HTML5 semantic cho thanh điều hướng -> 'nav'
    // Câu 2: Biến siêu toàn cục phương thức GET trong PHP -> '_GET'
    // Câu 3: Mã trạng thái HTTP chuyển hướng PRG -> '302'
    $diem = 0;
    $chiTiet = [];

    if ($cau1 === 'nav') {
        $diem++;
        $chiTiet[] = 'Câu 1: Chính xác (Thẻ <nav> dùng cho thanh điều hướng).';
    } else {
        $chiTiet[] = 'Câu 1: Chưa đúng (Đáp án đúng là thẻ <nav>).';
    }

    if ($cau2 === '_GET') {
        $diem++;
        $chiTiet[] = 'Câu 2: Chính xác ($_GET chứa tham số query string).';
    } else {
        $chiTiet[] = 'Câu 2: Chưa đúng (Đáp án đúng là $_GET).';
    }

    if ($cau3 === '302') {
        $diem++;
        $chiTiet[] = 'Câu 3: Chính xác (Mã 302 Found dùng cho PRG Pattern).';
    } else {
        $chiTiet[] = 'Câu 3: Chưa đúng (Đáp án đúng là HTTP 302 Found).';
    }

    $xepHang = ($diem === 3) ? 'Xuất sắc! Bạn nắm rất vững kiến thức Web.' : (($diem >= 2) ? 'Khá tốt! Bạn đã vượt qua bài test.' : 'Cần ôn tập thêm kiến thức cơ bản.');

    $_SESSION['nhuan_quiz_kq'] = [
        'ten' => $tenHocVien,
        'diem' => $diem,
        'tong' => 3,
        'xep_hang' => $xepHang,
        'chi_tiet' => $chiTiet,
        'thoi_gian' => date('d/m/Y H:i:s'),
    ];

    header('Location: gioithieu.php#trac-nghiem');
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
    <p>Chào mừng bạn đến trang giới thiệu của Phan Nhuận (Nhuận)</p>
  </div>
</header>

<!-- NAVIGATION BAR -->
<nav class="profile-nav" aria-label="Điều hướng trang cá nhân">
  <div class="container">
    <ul>
      <li><a href="#about">Giới thiệu</a></li>
      <li><a href="#danh-gia">Đánh giá kỹ năng</a></li>
      <li><a href="#trac-nghiem">Trắc nghiệm Web</a></li>
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
          src="avatar.jpg"
          alt="Ảnh chân dung đại diện của Phan Nhuận"
          width="180"
          height="180"
          loading="lazy"
        />
      </div>
      <div class="about-info">
        <h2 id="about-heading">Giới Thiệu Bản Thân</h2>
        <span class="meta-pill">MSSV: 3120224106 &bull; Nhóm 14</span>
        <p>
          Xin chào! Mình là Phan Nhuận, sinh viên chuyên ngành Sư phạm Tin
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

  <!-- KHỐI CHỨC NĂNG MÁY CHỦ 1: ĐÁNH GIÁ NĂNG LỰC & KỸ NĂNG -->
  <section
    id="danh-gia"
    class="profile-card nhuan-card-section"
    aria-labelledby="danh-gia-heading"
  >
    <div class="nhuan-card-header">
      <div class="nhuan-card-badge">
        <span aria-hidden="true">⭐</span>
        <span>Xử lý máy chủ POST &bull; Lưu trữ JSONL &bull; Mô hình PRG</span>
      </div>
      <h2 id="danh-gia-heading">Khảo Sát &amp; Đánh Giá Kỹ Năng</h2>
      <p class="nhuan-card-desc">
        Gửi nhận xét và chấm điểm sao cho các kỹ năng chuyên môn của Phan Nhuận. Dữ liệu được lưu trữ trực tiếp vào tệp <code>storage/3120224106_danhgia.jsonl</code> trên máy chủ.
      </p>
    </div>

    <?php if ($thongBaoThanhCong !== null): ?>
      <div class="nhuan-alert-success" role="alert" aria-live="polite">
        <span class="nhuan-alert-icon" aria-hidden="true">🎉</span>
        <div>
          <strong>Thành công:</strong> <?= e($thongBaoThanhCong) ?>
        </div>
      </div>
    <?php endif; ?>

    <div class="nhuan-grid-layout">
      <!-- Biểu mẫu gửi đánh giá -->
      <div class="nhuan-form-card">
        <h3>Gửi phiếu đánh giá kỹ năng</h3>
        <form
          action="gioithieu.php#danh-gia"
          method="post"
          class="nhuan-form"
          novalidate
        >
          <div class="form-group mb-3">
            <label for="nguoi_gui" class="form-label">
              Họ và tên của bạn <span class="bat-buoc">*</span>
            </label>
            <input
              type="text"
              id="nguoi_gui"
              name="nguoi_gui"
              class="form-control <?= isset($loiDanhGia['nguoi_gui']) ? 'is-invalid' : '' ?>"
              placeholder="Ví dụ: Lê Minh Trí"
              value="<?= e($duLieuCu['nguoi_gui'] ?? '') ?>"
              required
            >
            <?php if (isset($loiDanhGia['nguoi_gui'])): ?>
              <p class="thong-bao-loi" role="alert"><?= e($loiDanhGia['nguoi_gui']) ?></p>
            <?php endif; ?>
          </div>

          <div class="form-group mb-3">
            <label for="ky_nang" class="form-label">
              Kỹ năng bạn muốn đánh giá <span class="bat-buoc">*</span>
            </label>
            <select id="ky_nang" name="ky_nang" class="form-control" required>
              <?php
              $dsKn = [
                  'HTML5 / Semantic & a11y',
                  'Modern CSS & Responsive',
                  'PHP Server-Side & MVC',
                  'Lập trình JavaScript ES6+',
                  'Làm việc nhóm & Git/GitHub',
              ];
              $knChon = $duLieuCu['ky_nang'] ?? 'HTML5 / Semantic & a11y';
              foreach ($dsKn as $k):
              ?>
                <option value="<?= e($k) ?>" <?= $knChon === $k ? 'selected' : '' ?>>
                  <?= e($k) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group mb-3">
            <label for="so_sao" class="form-label">
              Mức độ thành thạo (Số sao) <span class="bat-buoc">*</span>
            </label>
            <select id="so_sao" name="so_sao" class="form-control" required>
              <option value="5" <?= ($duLieuCu['so_sao'] ?? '5') === '5' ? 'selected' : '' ?>>⭐⭐⭐⭐⭐ (5 sao - Xuất sắc)</option>
              <option value="4" <?= ($duLieuCu['so_sao'] ?? '5') === '4' ? 'selected' : '' ?>>⭐⭐⭐⭐ (4 sao - Tốt)</option>
              <option value="3" <?= ($duLieuCu['so_sao'] ?? '5') === '3' ? 'selected' : '' ?>>⭐⭐⭐ (3 sao - Khá)</option>
              <option value="2" <?= ($duLieuCu['so_sao'] ?? '5') === '2' ? 'selected' : '' ?>>⭐⭐ (2 sao - Trung bình)</option>
              <option value="1" <?= ($duLieuCu['so_sao'] ?? '5') === '1' ? 'selected' : '' ?>>⭐ (1 sao - Cần cải thiện)</option>
            </select>
          </div>

          <div class="form-group mb-3">
            <label for="gop_y" class="form-label">Góp ý / Nhận xét thêm (tùy chọn):</label>
            <textarea
              id="gop_y"
              name="gop_y"
              rows="3"
              class="form-control <?= isset($loiDanhGia['gop_y']) ? 'is-invalid' : '' ?>"
              placeholder="Nhận xét chi tiết về kỹ năng hoặc thái độ làm việc..."
            ><?= e($duLieuCu['gop_y'] ?? '') ?></textarea>
            <?php if (isset($loiDanhGia['gop_y'])): ?>
              <p class="thong-bao-loi" role="alert"><?= e($loiDanhGia['gop_y']) ?></p>
            <?php endif; ?>
          </div>

          <div class="form-actions">
            <button type="submit" name="gui_danh_gia" value="1" class="btn-action">
              <span aria-hidden="true">📨</span>
              <span>Gửi đánh giá (POST - PRG)</span>
            </button>
          </div>
        </form>
      </div>

      <!-- Danh sách đánh giá gần đây -->
      <div class="nhuan-history-card">
        <h3>Đánh giá gần đây từ cộng đồng</h3>
        <p class="nhuan-history-desc">Lưu trữ minh bạch tại <code>storage/3120224106_danhgia.jsonl</code>:</p>

        <?php if (empty($dsDanhGia)): ?>
          <div class="nhuan-empty-box">
            <span>🗳️</span>
            <p>Chưa có đánh giá nào. Hãy là người đầu tiên để lại nhận xét!</p>
          </div>
        <?php else: ?>
          <ul class="nhuan-review-list">
            <?php foreach ($dsDanhGia as $dg): ?>
              <li class="nhuan-review-item">
                <div class="nhuan-review-header">
                  <strong><?= e($dg['nguoi_gui'] ?? 'Ẩn danh') ?></strong>
                  <span class="nhuan-stars"><?= str_repeat('⭐', (int)($dg['so_sao'] ?? 5)) ?></span>
                </div>
                <div class="nhuan-review-skill"><?= e($dg['ky_nang'] ?? '') ?></div>
                <p class="nhuan-review-text">&ldquo;<?= e($dg['gop_y'] ?? '') ?>&rdquo;</p>
                <small class="nhuan-review-date">⏱️ <?= e($dg['ngay_gui'] ?? '') ?></small>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- KHỐI CHỨC NĂNG MÁY CHỦ 2: TRẮC NGHIỆM KIẾN THỨC WEB (POST - CHẤM ĐIỂM SERVER - PRG) -->
  <section
    id="trac-nghiem"
    class="profile-card nhuan-card-section"
    aria-labelledby="quiz-heading"
  >
    <div class="nhuan-card-header">
      <div class="nhuan-card-badge">
        <span aria-hidden="true">🧠</span>
        <span>Xử lý máy chủ POST &bull; Chấm điểm tự động &bull; Mô hình PRG</span>
      </div>
      <h2 id="quiz-heading">Trắc Nghiệm Kiến Thức Lập Trình Web</h2>
      <p class="nhuan-card-desc">
        Thử tài kiến thức HTML5 Semantic, PHP và mô hình PRG. Máy chủ tiếp nhận các câu trả lời, tính điểm tự động và trả kết quả tức thì.
      </p>
    </div>

    <?php if ($loiQuiz !== null): ?>
      <div class="nhuan-alert-error" role="alert" aria-live="polite">
        <span class="nhuan-alert-icon" aria-hidden="true">⚠️</span>
        <div><strong>Lỗi:</strong> <?= e($loiQuiz) ?></div>
      </div>
    <?php endif; ?>

    <?php if ($ketQuaQuiz !== null): ?>
      <div class="nhuan-quiz-result" role="region" aria-label="Kết quả trắc nghiệm">
        <div class="nhuan-quiz-result-header">
          <h3>Kết quả bài làm của: <?= e($ketQuaQuiz['ten']) ?></h3>
          <span class="nhuan-quiz-score"><?= e($ketQuaQuiz['diem']) ?> / <?= e($ketQuaQuiz['tong']) ?> Điểm</span>
        </div>
        <p class="nhuan-quiz-feedback"><strong>Đánh giá:</strong> <?= e($ketQuaQuiz['xep_hang']) ?></p>
        <ul class="nhuan-quiz-details">
          <?php foreach ($ketQuaQuiz['chi_tiet'] as $item): ?>
            <li><?= e($item) ?></li>
          <?php endforeach; ?>
        </ul>
        <small class="text-muted">⏱️ Đã chấm lúc: <?= e($ketQuaQuiz['thoi_gian']) ?></small>
      </div>
    <?php endif; ?>

    <div class="nhuan-form-card">
      <form action="gioithieu.php#trac-nghiem" method="post" class="nhuan-form" novalidate>
        <div class="form-group mb-3">
          <label for="ten_hoc_vien" class="form-label">Tên của bạn: <span class="bat-buoc">*</span></label>
          <input type="text" id="ten_hoc_vien" name="ten_hoc_vien" class="form-control" placeholder="Nhập họ tên để nhận kết quả..." required>
        </div>

        <div class="quiz-question-box mb-3">
          <p class="quiz-q-title"><strong>Câu 1:</strong> Thẻ HTML5 ngữ nghĩa nào chuẩn nhất cho thanh điều hướng trang web?</p>
          <label class="quiz-radio-label"><input type="radio" name="cau1" value="div"> &lt;div class="nav"&gt;</label>
          <label class="quiz-radio-label"><input type="radio" name="cau1" value="nav"> &lt;nav&gt;</label>
          <label class="quiz-radio-label"><input type="radio" name="cau1" value="menu"> &lt;menu type="bar"&gt;</label>
        </div>

        <div class="quiz-question-box mb-3">
          <p class="quiz-q-title"><strong>Câu 2:</strong> Biến siêu toàn cục nào trong PHP chứa các tham số được gửi qua URL Query String?</p>
          <label class="quiz-radio-label"><input type="radio" name="cau2" value="_POST"> $_POST</label>
          <label class="quiz-radio-label"><input type="radio" name="cau2" value="_GET"> $_GET</label>
          <label class="quiz-radio-label"><input type="radio" name="cau2" value="_REQUEST_BODY"> $_REQUEST_BODY</label>
        </div>

        <div class="quiz-question-box mb-3">
          <p class="quiz-q-title"><strong>Câu 3:</strong> Mã trạng thái HTTP phản hồi nào thường được máy chủ dùng trong mô hình Post-Redirect-Get (PRG)?</p>
          <label class="quiz-radio-label"><input type="radio" name="cau3" value="200"> 200 OK</label>
          <label class="quiz-radio-label"><input type="radio" name="cau3" value="302"> 302 Found</label>
          <label class="quiz-radio-label"><input type="radio" name="cau3" value="404"> 404 Not Found</label>
        </div>

        <div class="form-actions">
          <button type="submit" name="nop_quiz" value="1" class="btn-action">
            <span aria-hidden="true">📝</span>
            <span>Nộp bài &amp; Chấm điểm (POST - PRG)</span>
          </button>
        </div>
      </form>
    </div>
  </section>

  <!-- KHỐI 2: KỸ NĂNG & THÔNG TIN BỔ SUNG (BỐ CỤC GRID 2 CỘT TRÊN DESKTOP) -->
  <div class="profile-grid-mid">
    <section
      id="skills"
      class="profile-card"
      aria-labelledby="skills-heading"
    >
      <h2 id="skills-heading">Kỹ Năng</h2>
      <ul class="skills-list">
        <li data-group="html">HTML5 Semantic Tags &amp; a11y</li>
        <li data-group="css">Modern CSS (CSS Grid &amp; Flexbox)</li>
        <li data-group="css">Responsive Web Design (Mobile-First)</li>
        <li data-group="css">Tùy biến CSS Variables &amp; Tokens</li>
        <li data-group="tools">Git &amp; GitHub Version Control</li>
        <li data-group="tools">Bootstrap 5 Framework &amp; Utility</li>
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
          <span>📧</span>
          <div>
            <strong>Email:</strong><br />
            <a href="mailto:nhuanphan514@gmail.com">nhuanphan514@gmail.com</a>
          </div>
        </li>
        <li>
          <span>📞</span>
          <div>
            <strong>Số điện thoại:</strong><br />
            <a href="tel:0335549477">0335549477</a>
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
    <div
      class="bang-wrapper"
      tabindex="0"
      role="region"
      aria-label="Bảng thời khóa biểu học tập trong tuần"
    >
      <table class="schedule-table">
        <caption>Thời Khóa Biểu Học Tập Trong Tuần - Học kỳ 2 Năm học 2024-2025</caption>
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
            <th scope="row">1</th>
            <td></td>
            <td></td>
            <td rowspan="3"><strong>Hệ quản trị CSDL</strong><br><span class="room-badge">A5-404B</span></td>
            <td></td>
            <td></td>
            <td rowspan="3"><strong>Thiết kế &amp; Lập trình Web</strong><br><span class="room-badge">B3-303</span></td>
            <td></td>
          </tr>
          <tr><th scope="row">2</th><td></td><td></td><td></td><td></td><td></td><td></td></tr>
          <tr><th scope="row">3</th><td></td><td rowspan="2"><strong>Lịch sử Đảng CSVN</strong><br><span class="room-badge">A6-502</span></td><td></td><td></td><td></td><td></td></tr>
          <tr><th scope="row">4</th><td></td><td></td><td rowspan="3"><strong>Khai phá dữ liệu</strong><br><span class="room-badge">A5-404B</span></td><td></td><td></td><td></td></tr>
          <tr><th scope="row">5</th><td></td><td></td><td></td><td></td><td></td><td></td></tr>
          <tr><th scope="row">6</th><td></td><td></td><td></td><td></td><td></td><td></td></tr>
          <tr><th scope="row">7</th><td rowspan="3"><strong>An toàn thông tin</strong><br><span class="room-badge">A6-401</span></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
          <tr><th scope="row">8</th><td></td><td></td><td></td><td></td><td></td><td></td></tr>
          <tr><th scope="row">9</th><td></td><td></td><td></td><td></td><td></td><td></td></tr>
          <tr><th scope="row">10</th><td></td><td></td><td></td><td rowspan="3"><strong>Công nghệ phần mềm</strong><br><span class="room-badge">B3-304</span></td><td></td><td></td><td></td></tr>
          <tr><th scope="row">11</th><td></td><td></td><td></td><td></td><td></td><td></td></tr>
          <tr><th scope="row">12</th><td></td><td></td><td></td><td></td><td></td><td></td></tr>
        </tbody>
      </table>
    </div>
  </section>
</main>

<script src="js/canhan.js"></script>

<?php require __DIR__ . '/../../inc/footer.php'; ?>
