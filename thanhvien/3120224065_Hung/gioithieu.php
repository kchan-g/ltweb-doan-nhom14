<?php
/**
 * thanhvien/3120224065_Hung/gioithieu.php
 * Trang giới thiệu cá nhân sinh viên Đinh Trịnh Ngọc Hưng (MSSV: 3120224065 - Nhóm 14).
 * Chức năng máy chủ: 1. Bộ đếm lượt xem (Session + storage, 1 lần/phiên); 2. Sổ lưu bút (POST -> kiểm tra máy chủ -> lưu storage JSONL -> PRG chuyển hướng -> hiện 5 lời nhắn); 3. Đổi giao diện Sáng/Tối qua Cookie (hung_theme).
 * Cách thử: F5 để thấy lượt xem không tăng lại trong phiên; nhập biểu mẫu Lưu bút và bấm gửi để kiểm tra PRG + lưu JSONL; bấm ?doi_theme=1 để đổi theme qua Cookie.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../inc/config.php';

// Đường dẫn tương đối về thư mục gốc
$goc = '../../';
$tieuDe = 'Profile cá nhân - Đinh Trịnh Ngọc Hưng | UniEvent';
$trang = 'gioi-thieu';

// ============================================================================
// CHỨC NĂNG PHỤ: XỬ LÝ ĐỔI GIAO DIỆN SÁNG / TỐI QUA HTTP COOKIE (PRG)
// ============================================================================
if (isset($_GET['doi_theme'])) {
    $themeHienTai = $_COOKIE['hung_theme'] ?? 'light';
    $themeMoi = ($themeHienTai === 'dark') ? 'light' : 'dark';
    setcookie('hung_theme', $themeMoi, [
        'expires' => time() + (86400 * 30),
        'path' => '/',
        'secure' => false,
        'httponly' => false,
        'samesite' => 'Lax',
    ]);
    header('Location: gioithieu.php');
    exit;
}

$theme = $_COOKIE['hung_theme'] ?? 'light';
$bodyClass = ($theme === 'dark') ? 'dark-theme' : '';

// ============================================================================
// CHỨC NĂNG 1: BỘ ĐẾM LƯỢT XEM TRANG CÁ NHÂN (SESSION + TỆP STORAGE, 1 LẦN/PHIÊN)
// ============================================================================
$storageDir = __DIR__ . '/../../storage';
if (!is_dir($storageDir)) {
    @mkdir($storageDir, 0777, true);
}
$tepViews = $storageDir . '/3120224065_views.txt';
$tepViewsPhu = $storageDir . '/hung_views.txt';

$luotXem = 1;
if (!isset($_SESSION['hung_da_xem_trang'])) {
    $_SESSION['hung_da_xem_trang'] = true;
    if (file_exists($tepViews)) {
        $fp = fopen($tepViews, 'c+');
        if ($fp && flock($fp, LOCK_EX)) {
            $noiDung = trim((string)fread($fp, 64));
            $luotXem = max(1, (int)$noiDung + 1);
            ftruncate($fp, 0);
            rewind($fp);
            fwrite($fp, (string)$luotXem);
            fflush($fp);
            flock($fp, LOCK_UN);
            fclose($fp);
        }
    } else {
        $luotXem = 1;
        @file_put_contents($tepViews, '1', LOCK_EX);
    }
    // Ghi đồng bộ tệp hung_views.txt
    @file_put_contents($tepViewsPhu, (string)$luotXem, LOCK_EX);
} else {
    if (file_exists($tepViews)) {
        $luotXem = max(1, (int)trim((string)@file_get_contents($tepViews)));
    } elseif (file_exists($tepViewsPhu)) {
        $luotXem = max(1, (int)trim((string)@file_get_contents($tepViewsPhu)));
    }
}

// ============================================================================
// CHỨC NĂNG 2: SỔ LƯU BÚT CÁ NHÂN (POST -> KIỂM TRA MÁY CHỦ -> STORAGE -> PRG)
// ============================================================================
$tepLuuBut = $storageDir . '/3120224065_luubut.jsonl';

// Khởi tạo sẵn tệp lưu bút mẫu nếu chưa tồn tại
if (!file_exists($tepLuuBut)) {
    $mauLuuBut = [
        [
            'thoi_gian' => date('d/m/Y H:i', strtotime('-2 days')),
            'ho_ten' => 'Nguyễn Văn Bảo',
            'vai_tro' => 'Bạn cùng nhóm 14',
            'loi_nhan' => 'Chúc Hưng hoàn thành xuất sắc đồ án môn Thiết kế Web nhé! Profile cá nhân thiết kế rất chuyên nghiệp và chỉn chu.',
        ],
        [
            'thoi_gian' => date('d/m/Y H:i', strtotime('-1 days')),
            'ho_ten' => 'Trần Thị Đoan Trang',
            'vai_tro' => 'Bạn cùng lớp',
            'loi_nhan' => 'Giao diện web mượt mà, tối ưu hỗ trợ tiếp cận a11y và đồng hồ đếm ngược thi rất hữu ích!',
        ],
        [
            'thoi_gian' => date('d/m/Y H:i', strtotime('-3 hours')),
            'ho_ten' => 'Hồ Văn Nhuận',
            'vai_tro' => 'Đồng nghiệp / Bạn bè',
            'loi_nhan' => 'Các chức năng máy chủ chạy rất chuẩn, chúc nhóm 14 đạt điểm tối đa môn học!',
        ],
    ];
    $noiDungMau = '';
    foreach ($mauLuuBut as $item) {
        $noiDungMau .= json_encode($item, JSON_UNESCAPED_UNICODE) . "\n";
    }
    @file_put_contents($tepLuuBut, $noiDungMau, LOCK_EX);
}

// Xử lý khi người dùng gửi biểu mẫu POST Sổ lưu bút
$loiLuuBut = $_SESSION['hung_luubut_loi'] ?? [];
$duLieuCu = $_SESSION['hung_luubut_cu'] ?? ['ho_ten' => '', 'vai_tro' => 'Bạn cùng lớp', 'loi_nhan' => ''];
unset($_SESSION['hung_luubut_loi'], $_SESSION['hung_luubut_cu']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gui_luu_but'])) {
    $hoTen = trim((string)($_POST['ho_ten'] ?? ''));
    $vaiTro = trim((string)($_POST['vai_tro'] ?? ''));
    $loiNhan = trim((string)($_POST['loi_nhan'] ?? ''));

    $loi = [];
    $vaiTroHopLe = ['Bạn cùng nhóm 14', 'Bạn cùng lớp', 'Giảng viên', 'Khách ghé thăm', 'Đồng nghiệp / Bạn bè'];

    if ($hoTen === '') {
        $loi['ho_ten'] = 'Vui lòng nhập họ và tên của bạn.';
    } elseif (mb_strlen($hoTen) < 2 || mb_strlen($hoTen) > 50) {
        $loi['ho_ten'] = 'Họ và tên phải từ 2 đến 50 ký tự.';
    }

    if (!in_array($vaiTro, $vaiTroHopLe, true)) {
        $loi['vai_tro'] = 'Vui lòng chọn vai trò hợp lệ trong danh sách.';
    }

    if ($loiNhan === '') {
        $loi['loi_nhan'] = 'Vui lòng nhập nội dung lời nhắn lưu bút.';
    } elseif (mb_strlen($loiNhan) < 5 || mb_strlen($loiNhan) > 500) {
        $loi['loi_nhan'] = 'Lời nhắn phải có độ dài từ 5 đến 500 ký tự.';
    }

    if (!empty($loi)) {
        $_SESSION['hung_luubut_loi'] = $loi;
        $_SESSION['hung_luubut_cu'] = [
            'ho_ten' => $hoTen,
            'vai_tro' => $vaiTro,
            'loi_nhan' => $loiNhan,
        ];
        header('Location: gioithieu.php#luubut');
        exit;
    }

    // Ghi vào tệp JSONL phía máy chủ
    $dongMoi = [
        'thoi_gian' => date('d/m/Y H:i'),
        'ho_ten' => $hoTen,
        'vai_tro' => $vaiTro,
        'loi_nhan' => $loiNhan,
    ];
    $dongJson = json_encode($dongMoi, JSON_UNESCAPED_UNICODE) . "\n";
    file_put_contents($tepLuuBut, $dongJson, FILE_APPEND | LOCK_EX);

    flash('Cảm ơn bạn! Lời nhắn lưu bút của bạn đã được gửi và lưu trữ an toàn trên máy chủ.');
    header('Location: gioithieu.php#luubut');
    exit;
}

// Đọc danh sách lời nhắn từ tệp máy chủ và lấy 5 lời nhắn mới nhất
$danhSachLuuBut = [];
if (file_exists($tepLuuBut)) {
    $lines = file($tepLuuBut, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (is_array($lines)) {
        foreach ($lines as $line) {
            $data = json_decode($line, true);
            if (is_array($data) && isset($data['ho_ten'], $data['loi_nhan'])) {
                $danhSachLuuBut[] = $data;
            }
        }
    }
}
$danhSachLuuBut = array_reverse($danhSachLuuBut);
$namLoiNhanMoiNhat = array_slice($danhSachLuuBut, 0, 5);

$thongBaoThanhCong = flash();

require __DIR__ . '/../../inc/header.php';
?>

<link rel="stylesheet" href="css/style.css">

<!-- HEADER PROFILE -->
<header class="profile-header">
  <div class="container">
    <div class="profile-header__top-row">
      <span class="profile-header__badge">Thành viên Ban Kỹ thuật &bull; UniEvent</span>
      <div class="profile-view-badge" title="Bộ đếm lượt xem phía máy chủ (mỗi phiên tính một lần)">
        <span aria-hidden="true">👁️</span>
        <span>Lượt xem: <strong><?= e($luotXem) ?></strong></span>
      </div>
    </div>
    <h1>Profile cá nhân</h1>
    <p>Chào mừng bạn đến trang giới thiệu của Đinh Trịnh Ngọc Hưng (Bii)</p>
  </div>
</header>

<!-- NAVIGATION BAR -->
<nav class="profile-nav" aria-label="Điều hướng trang cá nhân">
  <div class="container profile-nav__inner">
    <ul>
      <li><a href="#about">Giới thiệu</a></li>
      <li><a href="#projects">Dự án &amp; Sở thích</a></li>
      <li><a href="#skills">Kỹ năng</a></li>
      <li><a href="#countdown">Đếm ngược thi</a></li>
      <li><a href="#schedule">Thời khóa biểu</a></li>
      <li><a href="#luubut">Sổ lưu bút</a></li>
      <li><a href="#contact">Liên hệ</a></li>
    </ul>
    <div class="profile-nav__actions">
      <a
        href="gioithieu.php?doi_theme=1"
        id="theme-toggle-btn"
        class="btn-theme-toggle"
        role="button"
        aria-pressed="<?= $theme === 'dark' ? 'true' : 'false' ?>"
        aria-label="Chuyển sang chế độ giao diện <?= $theme === 'dark' ? 'sáng' : 'tối' ?>"
      >
        <span class="theme-icon" id="theme-icon" aria-hidden="true"><?= $theme === 'dark' ? '☀️' : '🌙' ?></span>
        <span class="theme-text" id="theme-text"><?= $theme === 'dark' ? 'Giao diện sáng' : 'Giao diện tối' ?></span>
      </a>
    </div>
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
          alt="Ảnh chân dung đại diện của Đinh Trịnh Ngọc Hưng"
          width="180"
          height="180"
          loading="lazy"
        />
      </div>
      <div class="about-info">
        <h2 id="about-heading">Giới Thiệu Bản Thân</h2>
        <span class="meta-pill">MSSV: 3120224065 &bull; Nhóm 14</span>
        <p>
          Xin chào! Mình là Hưng (Bii), sinh viên chuyên ngành Sư phạm Tin
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
            <a href="mailto:dinhtrinhngochung1301@gmail.com"
              >dinhtrinhngochung1301@gmail.com</a
            >
          </div>
        </li>
        <li>
          <span>📞</span>
          <div>
            <strong>Số điện thoại:</strong><br />
            <a href="tel:0707174128">0707 174 128</a>
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

  <!-- KHỐI 3: ĐỒNG HỒ ĐẾM NGƯỢC THI CUỐI KỲ (TƯƠNG TÁC JAVASCRIPT THỜI GIAN THỰC) -->
  <section
    id="countdown"
    class="profile-card countdown-section"
    aria-labelledby="countdown-heading"
  >
    <div class="countdown-header">
      <div class="countdown-badge">
        <span aria-hidden="true">⏱️</span>
        <span>Học kỳ 2 &bull; Năm học 2024-2025</span>
      </div>
      <h2 id="countdown-heading">Đồng Hồ Đếm Ngược Thi Cuối Kỳ</h2>
      <p class="countdown-desc">
        Học phần: <strong>Thiết kế &amp; Lập trình Web</strong> &bull; Mã
        HP: <strong>31231016</strong> (24-0103)
      </p>
    </div>

    <!-- Khung số đếm ngược thời gian thực -->
    <div
      class="countdown-grid"
      role="timer"
      aria-live="polite"
      aria-atomic="true"
      aria-label="Thời gian còn lại đến kỳ thi"
    >
      <div class="countdown-box">
        <span id="countdown-days" class="countdown-num">00</span>
        <span class="countdown-label">Ngày</span>
      </div>
      <div class="countdown-box">
        <span id="countdown-hours" class="countdown-num">00</span>
        <span class="countdown-label">Giờ</span>
      </div>
      <div class="countdown-box">
        <span id="countdown-minutes" class="countdown-num">00</span>
        <span class="countdown-label">Phút</span>
      </div>
      <div class="countdown-box">
        <span id="countdown-seconds" class="countdown-num">00</span>
        <span class="countdown-label">Giây</span>
      </div>
    </div>

    <div class="countdown-footer">
      <p id="countdown-target-text" class="countdown-target-text">
        Thời gian thi dự kiến:
        <strong>08:00 - Chủ Nhật, 20/12/2026</strong> &bull; Phòng thi:
        <strong>B3-304</strong>
      </p>
      <div class="countdown-actions">
        <button
          type="button"
          id="btn-exam-reminder"
          class="btn-action"
          aria-expanded="false"
          aria-controls="exam-reminder-panel"
        >
          <span aria-hidden="true">📖</span>
          <span id="btn-exam-reminder-text"
            >Xem Lời Nhắc Ôn Tập &amp; Nội Dung Trọng Tâm</span
          >
        </button>
        <button
          type="button"
          id="btn-countdown-status"
          class="btn-action btn-action--secondary"
        >
          <span aria-hidden="true">🔄</span>
          <span>Kiểm tra trạng thái đếm ngược</span>
        </button>
      </div>

      <!-- Thông báo trạng thái tương tác -->
      <div
        id="countdown-status-msg"
        class="countdown-status-msg"
        aria-live="polite"
      ></div>

      <!-- Khung nội dung lời nhắc được tạo động bằng createElement trong canhan.js -->
      <div
        id="exam-reminder-panel"
        class="exam-reminder-panel"
        hidden
      ></div>
    </div>
  </section>

  <!-- KHỐI 4: THỜI KHÓA BIỂU HỌC TẬP -->
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

  <!-- KHỐI 5: SỔ LƯU BÚT & LỜI NHẮN GỬI (XỬ LÝ MÁY CHỦ POST - PRG - LƯU TỆP JSONL) -->
  <section
    id="luubut"
    class="profile-card luubut-section"
    aria-labelledby="luubut-heading"
  >
    <div class="luubut-header">
      <div class="luubut-badge">
        <span aria-hidden="true">✍️</span>
        <span>Xử lý máy chủ (Server-side) &bull; Mô hình PRG</span>
      </div>
      <h2 id="luubut-heading">Sổ Lưu Bút &amp; Lời Nhắn Gửi</h2>
      <p class="luubut-desc">
        Hãy để lại lời chúc, nhận xét hoặc đóng góp ý kiến của bạn dành cho Hưng. Dữ liệu được kiểm tra an toàn tại máy chủ, lưu trữ bền vững vào tệp <code>storage/3120224065_luubut.jsonl</code> và tự động chuyển hướng <strong>Post/Redirect/Get (PRG)</strong> chống gửi lặp.
      </p>
    </div>

    <!-- Thông báo Flash gửi thành công -->
    <?php if (!empty($thongBaoThanhCong)): ?>
      <div class="hop-thong-bao hop-thong-bao--thanh-cong mb-4" role="alert">
        <span class="hop-thong-bao__icon" aria-hidden="true">✅</span>
        <div>
          <strong>Thành công!</strong>
          <p><?= e($thongBaoThanhCong) ?></p>
        </div>
      </div>
    <?php endif; ?>

    <div class="luubut-grid">
      <!-- Cột trái: Biểu mẫu gửi lời lưu bút -->
      <div class="luubut-form-card">
        <h3 id="form-luubut-title">Viết lời lưu bút mới</h3>
        <form
          action="gioithieu.php#luubut"
          method="post"
          class="luubut-form"
          novalidate
          aria-labelledby="form-luubut-title"
        >
          <div class="form-group mb-3">
            <label for="ho_ten" class="form-label">
              Họ và tên của bạn <span class="bat-buoc" aria-hidden="true">*</span>
            </label>
            <input
              type="text"
              id="ho_ten"
              name="ho_ten"
              class="form-control <?= isset($loiLuuBut['ho_ten']) ? 'is-invalid' : '' ?>"
              placeholder="Ví dụ: Nguyễn Văn A"
              value="<?= e($duLieuCu['ho_ten'] ?? '') ?>"
              required
              maxlength="50"
              <?= isset($loiLuuBut['ho_ten']) ? 'aria-invalid="true" aria-describedby="loi-ho-ten"' : '' ?>
            >
            <?php if (isset($loiLuuBut['ho_ten'])): ?>
              <p class="thong-bao-loi" id="loi-ho-ten" role="alert"><?= e($loiLuuBut['ho_ten']) ?></p>
            <?php endif; ?>
          </div>

          <div class="form-group mb-3">
            <label for="vai_tro" class="form-label">
              Mối quan hệ / Vai trò <span class="bat-buoc" aria-hidden="true">*</span>
            </label>
            <select
              id="vai_tro"
              name="vai_tro"
              class="form-control <?= isset($loiLuuBut['vai_tro']) ? 'is-invalid' : '' ?>"
              required
              <?= isset($loiLuuBut['vai_tro']) ? 'aria-invalid="true" aria-describedby="loi-vai-tro"' : '' ?>
            >
              <?php
              $cacVaiTro = [
                  'Bạn cùng nhóm 14' => 'Bạn cùng nhóm 14',
                  'Bạn cùng lớp' => 'Bạn cùng lớp',
                  'Giảng viên' => 'Giảng viên',
                  'Đồng nghiệp / Bạn bè' => 'Đồng nghiệp / Bạn bè',
                  'Khách ghé thăm' => 'Khách ghé thăm',
              ];
              $vaiTroChon = $duLieuCu['vai_tro'] ?? 'Bạn cùng lớp';
              foreach ($cacVaiTro as $val => $nhan):
              ?>
                <option value="<?= e($val) ?>" <?= $vaiTroChon === $val ? 'selected' : '' ?>>
                  <?= e($nhan) ?>
                </option>
              <?php endforeach; ?>
            </select>
            <?php if (isset($loiLuuBut['vai_tro'])): ?>
              <p class="thong-bao-loi" id="loi-vai-tro" role="alert"><?= e($loiLuuBut['vai_tro']) ?></p>
            <?php endif; ?>
          </div>

          <div class="form-group mb-3">
            <label for="loi_nhan" class="form-label">
              Nội dung lời nhắn <span class="bat-buoc" aria-hidden="true">*</span>
            </label>
            <textarea
              id="loi_nhan"
              name="loi_nhan"
              rows="4"
              class="form-control <?= isset($loiLuuBut['loi_nhan']) ? 'is-invalid' : '' ?>"
              placeholder="Nhập lời chúc hoặc góp ý (từ 5 đến 500 ký tự)..."
              required
              maxlength="500"
              <?= isset($loiLuuBut['loi_nhan']) ? 'aria-invalid="true" aria-describedby="loi-loi-nhan"' : '' ?>
            ><?= e($duLieuCu['loi_nhan'] ?? '') ?></textarea>
            <?php if (isset($loiLuuBut['loi_nhan'])): ?>
              <p class="thong-bao-loi" id="loi-loi-nhan" role="alert"><?= e($loiLuuBut['loi_nhan']) ?></p>
            <?php endif; ?>
          </div>

          <button type="submit" name="gui_luu_but" value="1" class="btn-action">
            <span aria-hidden="true">📨</span>
            <span>Gửi lời lưu bút</span>
          </button>
        </form>
      </div>

      <!-- Cột phải: 5 Lời nhắn mới nhất -->
      <div class="luubut-list-card">
        <div class="luubut-list-card__header">
          <h3>5 Lời nhắn mới nhất</h3>
          <span class="luubut-total-pill">Tổng cộng: <?= e(count($danhSachLuuBut)) ?></span>
        </div>

        <?php if (empty($namLoiNhanMoiNhat)): ?>
          <p class="luubut-empty">Chưa có lời lưu bút nào. Hãy là người đầu tiên để lại lời nhắn nhé!</p>
        <?php else: ?>
          <div class="luubut-items">
            <?php foreach ($namLoiNhanMoiNhat as $item): ?>
              <article class="luubut-card-item">
                <div class="luubut-card-item__top">
                  <div class="luubut-card-item__sender">
                    <strong><?= e($item['ho_ten'] ?? 'Ẩn danh') ?></strong>
                    <span class="luubut-role-tag"><?= e($item['vai_tro'] ?? 'Khách ghé thăm') ?></span>
                  </div>
                  <time class="luubut-time" datetime="<?= e($item['thoi_gian'] ?? '') ?>">
                    <?= e($item['thoi_gian'] ?? '') ?>
                  </time>
                </div>
                <p class="luubut-card-item__content"><?= nl2br(e($item['loi_nhan'] ?? '')) ?></p>
              </article>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>
</main>

<script src="js/canhan.js"></script>

<?php require __DIR__ . '/../../inc/footer.php'; ?>
