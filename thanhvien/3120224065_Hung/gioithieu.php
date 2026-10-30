<?php
/**
 * thanhvien/3120224065_Hung/gioithieu.php
 * Trang giới thiệu cá nhân sinh viên Đinh Trịnh Ngọc Hưng (MSSV: 3120224065 - Nhóm 14).
 * Chức năng CSDL (Phần B):
 * 1. Sổ lưu bút dùng bảng tv_luu_but (INSERT qua prepared statement, phân trang 5 lời nhắn/trang, chỉ chủ trang đăng nhập mới được xóa lời nhắn có kiểm tra quyền máy chủ).
 * 2. Bộ đếm lượt xem theo ngày dùng bảng tv_luot_xem (INSERT ... ON DUPLICATE KEY UPDATE) kèm thống kê 7 ngày gần nhất.
 * Cách thử:
 * - Gửi biểu mẫu Lưu bút kiểm tra thêm mới vào MySQL và phân trang (?trang_lb=2).
 * - Đăng nhập tài khoản Hưng (hung@ued.vn / matkhau123) hoặc Admin (admin@ued.vn / matkhau123) để thấy nút "Xóa" từng lời nhắn; thử xóa để kiểm tra DELETE trong CSDL. Đăng nhập tài khoản khác (an@ued.vn) nút xóa sẽ ẩn và nếu cố tình gửi request xóa sẽ nhận mã lỗi 403 Forbidden.
 * - Xem widget "Thống kê lượt xem 7 ngày gần nhất" ở đầu trang để thấy số lượt xem tăng lên theo ngày.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../inc/config.php';

// Đường dẫn tương đối về thư mục gốc
$goc = '../../';
$tieuDe = 'Profile cá nhân - Đinh Trịnh Ngọc Hưng | UniEvent';
$trang = 'gioi-thieu';
$mssvHung = '3120224065';

// Kết nối PDO
$pdo = null;
try {
    $pdo = db();
} catch (Throwable $e) {
    error_log('Lỗi kết nối CSDL tại trang Hưng: ' . $e->getMessage());
}

// Kiểm tra quyền chủ trang (id 4 hung@ued.vn, id 5, hoặc vai trò quan_tri)
$ndHienTai = nguoiDung();
$laChuTrang = $ndHienTai !== null && (
    ($ndHienTai['id'] ?? 0) === 4 ||
    ($ndHienTai['id'] ?? 0) === 5 ||
    ($ndHienTai['email'] ?? '') === 'hung@ued.vn' ||
    ($ndHienTai['email'] ?? '') === '3120224065@ued.udn.vn' ||
    laQuanTri()
);

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
// CHỨC NĂNG 1: BỘ ĐẾM LƯỢT XEM THEO NGÀY (BẢNG tv_luot_xem)
// ============================================================================
$luotXemHomNay = 1;
$tongLuotXem = 1;
$thongKe7Ngay = [];

if ($pdo !== null) {
    if (!isset($_SESSION['hung_da_xem_trang'])) {
        $_SESSION['hung_da_xem_trang'] = true;
        try {
            $stmtInc = $pdo->prepare("INSERT INTO tv_luot_xem (mssv, ngay, so_luot) VALUES (:mssv, CURDATE(), 1) ON DUPLICATE KEY UPDATE so_luot = so_luot + 1");
            $stmtInc->execute([':mssv' => $mssvHung]);
        } catch (Throwable $e) {
            error_log('Lỗi ghi nhận tv_luot_xem: ' . $e->getMessage());
        }
    }

    try {
        $stmtHN = $pdo->prepare("SELECT so_luot FROM tv_luot_xem WHERE mssv = :mssv AND ngay = CURDATE()");
        $stmtHN->execute([':mssv' => $mssvHung]);
        $valHN = $stmtHN->fetchColumn();
        $luotXemHomNay = $valHN !== false ? (int)$valHN : 1;

        $stmtTong = $pdo->prepare("SELECT SUM(so_luot) FROM tv_luot_xem WHERE mssv = :mssv");
        $stmtTong->execute([':mssv' => $mssvHung]);
        $valTong = $stmtTong->fetchColumn();
        $tongLuotXem = $valTong !== false ? (int)$valTong : $luotXemHomNay;

        $stmt7 = $pdo->prepare("SELECT ngay, so_luot FROM tv_luot_xem WHERE mssv = :mssv ORDER BY ngay DESC LIMIT 7");
        $stmt7->execute([':mssv' => $mssvHung]);
        $thongKe7Ngay = $stmt7->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        error_log('Lỗi đọc thống kê tv_luot_xem: ' . $e->getMessage());
    }
} else {
    // Fallback file storage nếu CSDL chưa kết nối
    $storageDir = __DIR__ . '/../../storage';
    if (!is_dir($storageDir)) {
        @mkdir($storageDir, 0777, true);
    }
    $tepViews = $storageDir . '/3120224065_views.txt';
    if (!isset($_SESSION['hung_da_xem_trang'])) {
        $_SESSION['hung_da_xem_trang'] = true;
        $cnt = file_exists($tepViews) ? (int)file_get_contents($tepViews) + 1 : 1;
        @file_put_contents($tepViews, (string)$cnt, LOCK_EX);
        $tongLuotXem = $cnt;
    } else {
        $tongLuotXem = file_exists($tepViews) ? max(1, (int)file_get_contents($tepViews)) : 1;
    }
    $luotXemHomNay = $tongLuotXem;
}

// ============================================================================
// CHỨC NĂNG 2: XÓA LƯU BÚT (CHỈ CHỦ TRANG ĐĂNG NHẬP MỚI ĐƯỢC XÓA - KIỂM TRA MÁY CHỦ)
// ============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['xoa_luu_but'])) {
    if (!$laChuTrang) {
        http_response_code(403);
        if (file_exists(__DIR__ . '/../../403.php')) {
            require __DIR__ . '/../../403.php';
        } else {
            echo "<h1>403 Forbidden</h1><p>Bạn không có quyền xóa lời nhắn lưu bút này.</p>";
        }
        exit;
    }

    $idXoa = filter_var($_POST['id_luu_but'] ?? 0, FILTER_VALIDATE_INT);
    if ($idXoa > 0 && $pdo !== null) {
        try {
            $stmtXoa = $pdo->prepare("DELETE FROM tv_luu_but WHERE id = :id AND mssv = :mssv");
            $stmtXoa->execute([':id' => $idXoa, ':mssv' => $mssvHung]);
            flash('Đã xóa lời nhắn lưu bút khỏi cơ sở dữ liệu thành công.');
        } catch (Throwable $e) {
            flash('Lỗi khi xóa lời nhắn: ' . $e->getMessage());
        }
    }
    header('Location: gioithieu.php#luubut');
    exit;
}

// ============================================================================
// CHỨC NĂNG 3: GỬI LƯU BÚT MỚI (POST -> VALIDATE -> INSERT CSDL -> PRG)
// ============================================================================
$loiLuuBut = $_SESSION['hung_luubut_loi'] ?? [];
$duLieuCu = $_SESSION['hung_luubut_cu'] ?? ['ho_ten' => '', 'vai_tro' => '', 'loi_nhan' => ''];
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

    if ($pdo !== null) {
        try {
            $stmtIns = $pdo->prepare("INSERT INTO tv_luu_but (mssv, ten_khach, vai_tro, loi_nhan, thoi_gian) VALUES (:mssv, :ten, :vai_tro, :loi_nhan, NOW())");
            $stmtIns->execute([
                ':mssv' => $mssvHung,
                ':ten' => $hoTen,
                ':vai_tro' => $vaiTro,
                ':loi_nhan' => $loiNhan,
            ]);
            flash('Cảm ơn bạn! Lời nhắn lưu bút của bạn đã được lưu vào cơ sở dữ liệu MySQL thành công.');
        } catch (Throwable $e) {
            flash('Lỗi khi lưu vào CSDL: ' . $e->getMessage());
        }
    } else {
        // Fallback ghi file JSONL
        $storageDir = __DIR__ . '/../../storage';
        $tepLuuBut = $storageDir . '/3120224065_luubut.jsonl';
        $dongMoi = [
            'thoi_gian' => date('Y-m-d H:i:s'),
            'ho_ten' => $hoTen,
            'vai_tro' => $vaiTro,
            'loi_nhan' => $loiNhan,
        ];
        @file_put_contents($tepLuuBut, json_encode($dongMoi, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND | LOCK_EX);
        flash('Cảm ơn bạn! Lời nhắn lưu bút đã được ghi nhận.');
    }

    header('Location: gioithieu.php#luubut');
    exit;
}

// ============================================================================
// CHỨC NĂNG 4: ĐỌC VÀ PHÂN TRANG SỔ LƯU BÚT (5 LỜI NHẮN / TRANG)
// ============================================================================
$trangLB = max(1, (int)($_GET['trang_lb'] ?? 1));
$soLuongMoiTrang = 5;
$tongSoLuuBut = 0;
$tongSoTrangLB = 1;
$danhSachLuuBut = [];

if ($pdo !== null) {
    try {
        $stmtDem = $pdo->prepare("SELECT COUNT(*) FROM tv_luu_but WHERE mssv = :mssv");
        $stmtDem->execute([':mssv' => $mssvHung]);
        $tongSoLuuBut = (int)$stmtDem->fetchColumn();

        $tongSoTrangLB = max(1, (int)ceil($tongSoLuuBut / $soLuongMoiTrang));
        if ($trangLB > $tongSoTrangLB) {
            $trangLB = $tongSoTrangLB;
        }
        $offset = ($trangLB - 1) * $soLuongMoiTrang;

        $stmtLB = $pdo->prepare("SELECT id, ten_khach, vai_tro, loi_nhan, thoi_gian FROM tv_luu_but WHERE mssv = :mssv ORDER BY thoi_gian DESC, id DESC LIMIT :limit OFFSET :offset");
        $stmtLB->bindValue(':mssv', $mssvHung, PDO::PARAM_STR);
        $stmtLB->bindValue(':limit', $soLuongMoiTrang, PDO::PARAM_INT);
        $stmtLB->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmtLB->execute();
        $danhSachLuuBut = $stmtLB->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        error_log('Lỗi đọc tv_luu_but: ' . $e->getMessage());
    }
} else {
    // Fallback file storage nếu CSDL chưa có
    $storageDir = __DIR__ . '/../../storage';
    $tepLuuBut = $storageDir . '/3120224065_luubut.jsonl';
    $dsTatCa = [];
    if (file_exists($tepLuuBut)) {
        $lines = file($tepLuuBut, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (is_array($lines)) {
            foreach ($lines as $idx => $line) {
                $data = json_decode($line, true);
                if (is_array($data) && isset($data['ho_ten'], $data['loi_nhan'])) {
                    $dsTatCa[] = [
                        'id' => $idx + 1,
                        'ten_khach' => $data['ho_ten'],
                        'vai_tro' => $data['vai_tro'] ?? 'Khách ghé thăm',
                        'loi_nhan' => $data['loi_nhan'],
                        'thoi_gian' => $data['thoi_gian'] ?? date('Y-m-d H:i:s'),
                    ];
                }
            }
        }
    }
    $dsTatCa = array_reverse($dsTatCa);
    $tongSoLuuBut = count($dsTatCa);
    $tongSoTrangLB = max(1, (int)ceil($tongSoLuuBut / $soLuongMoiTrang));
    $danhSachLuuBut = array_slice($dsTatCa, ($trangLB - 1) * $soLuongMoiTrang, $soLuongMoiTrang);
}

$thongBaoThanhCong = flash();

require __DIR__ . '/../../inc/header.php';
?>


<link rel="stylesheet" href="css/style.css">

<!-- HEADER PROFILE -->
<header class="profile-header">
  <div class="container">
    <div class="profile-header__top-row">
      <span class="profile-header__badge">Thành viên Ban Kỹ thuật &bull; UniEvent</span>
      <div class="profile-view-badge" title="Bộ đếm lượt xem CSDL MySQL tv_luot_xem (INSERT ... ON DUPLICATE KEY UPDATE)">
        <span aria-hidden="true">👁️</span>
        <span>Hôm nay: <strong><?= e($luotXemHomNay) ?></strong> | Tổng: <strong><?= e($tongLuotXem) ?></strong> lượt</span>
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

  <!-- KHỐI 5: SỔ LƯU BÚT & LỜI NHẮN GỬI (CSDL MYSQL tv_luu_but & tv_luot_xem - PHẦN B) -->
  <section
    id="luubut"
    class="profile-card luubut-section"
    aria-labelledby="luubut-heading"
  >
    <div class="luubut-header">
      <div class="luubut-badge">
        <span aria-hidden="true">✍️</span>
        <span>CSDL MySQL <code>tv_luu_but</code> &bull; Phân trang &bull; Phân quyền chủ trang</span>
      </div>
      <h2 id="luubut-heading">Sổ Lưu Bút &amp; Lời Nhắn Gửi</h2>
      <p class="luubut-desc">
        Hãy để lại lời chúc, nhận xét hoặc đóng góp ý kiến của bạn dành cho Hưng. Dữ liệu được lưu trữ trong bảng <code>tv_luu_but</code> qua PDO Prepared Statement, hỗ trợ phân trang (5 lời nhắn/trang) và chỉ chủ trang đăng nhập mới có quyền xóa lời nhắn.
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
              <option value="" <?= empty($duLieuCu['vai_tro']) ? 'selected' : '' ?>>-- Chọn mối quan hệ / vai trò --</option>
              <?php
              $cacVaiTro = [
                  'Bạn cùng nhóm 14' => 'Bạn cùng nhóm 14',
                  'Bạn cùng lớp' => 'Bạn cùng lớp',
                  'Giảng viên' => 'Giảng viên',
                  'Đồng nghiệp / Bạn bè' => 'Đồng nghiệp / Bạn bè',
                  'Khách ghé thăm' => 'Khách ghé thăm',
              ];
              $vaiTroChon = $duLieuCu['vai_tro'] ?? '';
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

      <!-- Cột phải: Danh sách lời nhắn phân trang -->
      <div class="luubut-list-card">
        <div class="luubut-list-card__header">
          <h3>Sổ lưu bút (Trang <?= e($trangLB) ?> / <?= e($tongSoTrangLB) ?>)</h3>
          <span class="luubut-total-pill">Tổng cộng: <?= e($tongSoLuuBut) ?> lời nhắn</span>
        </div>

        <?php if (empty($danhSachLuuBut)): ?>
          <p class="luubut-empty">Chưa có lời lưu bút nào. Hãy là người đầu tiên để lại lời nhắn nhé!</p>
        <?php else: ?>
          <div class="luubut-items">
            <?php foreach ($danhSachLuuBut as $item): ?>
              <?php
                $thoiGianRaw = $item['thoi_gian'] ?? '';
                $timestamp = strtotime((string)$thoiGianRaw);
                $isoTime = $timestamp ? date('c', $timestamp) : date('c');
                $dinhDangHienThi = $timestamp ? date('d/m/Y H:i', $timestamp) : (string)$thoiGianRaw;
              ?>
              <article class="luubut-card-item">
                <div class="luubut-card-item__top">
                  <div class="luubut-card-item__sender">
                    <h4 class="luubut-card-item__sender-name"><?= e($item['ten_khach'] ?? 'Ẩn danh') ?></h4>
                    <span class="luubut-role-tag"><?= e($item['vai_tro'] ?? 'Khách ghé thăm') ?></span>
                  </div>
                  <div class="luubut-card-item__meta">
                    <time class="luubut-time" datetime="<?= e($isoTime) ?>">
                      <?= e($dinhDangHienThi) ?>
                    </time>
                    <?php if ($laChuTrang && !empty($item['id'])): ?>
                      <form action="gioithieu.php#luubut" method="post" class="d-inline ms-2" onsubmit="return confirm('Bạn có chắc chắn muốn xóa lời nhắn này khỏi CSDL?');">
                        <input type="hidden" name="xoa_luu_but" value="1">
                        <input type="hidden" name="id_luu_but" value="<?= (int)$item['id'] ?>">
                        <button type="submit" class="btn-xoa-luubut" title="Chủ trang xóa lời nhắn">🗑️ Xóa</button>
                      </form>
                    <?php endif; ?>
                  </div>
                </div>
                <p class="luubut-card-item__content"><?= nl2br(e($item['loi_nhan'] ?? '')) ?></p>
              </article>
            <?php endforeach; ?>
          </div>

          <!-- Phân trang Sổ lưu bút (5 lời nhắn / trang) -->
          <?php if ($tongSoTrangLB > 1): ?>
            <nav class="luubut-pagination mt-3" aria-label="Phân trang sổ lưu bút">
              <ul>
                <?php if ($trangLB > 1): ?>
                  <li class="page-item">
                    <a class="page-link" href="?trang_lb=<?= $trangLB - 1 ?>#luubut" aria-label="Trang trước">&laquo; Trước</a>
                  </li>
                <?php endif; ?>

                <?php for ($p = 1; $p <= $tongSoTrangLB; $p++): ?>
                  <li class="page-item <?= $p === $trangLB ? 'active' : '' ?>">
                    <a class="page-link" href="?trang_lb=<?= $p ?>#luubut"><?= $p ?></a>
                  </li>
                <?php endfor; ?>

                <?php if ($trangLB < $tongSoTrangLB): ?>
                  <li class="page-item">
                    <a class="page-link" href="?trang_lb=<?= $trangLB + 1 ?>#luubut" aria-label="Trang sau">Sau &raquo;</a>
                  </li>
                <?php endif; ?>
              </ul>
            </nav>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- Bảng thống kê 7 ngày gần nhất (Bảng tv_luot_xem) -->
    <?php if (!empty($thongKe7Ngay)): ?>
      <div class="luubut-stats-box mt-4 p-3 rounded border">
        <h3 class="h6 mb-2 font-weight-bold" style="font-size: 1rem; color: var(--mau-chinh);">
          📊 Thống kê lượt truy cập trang 7 ngày gần nhất (Bảng <code>tv_luot_xem</code>)
        </h3>
        <div class="table-responsive">
          <table class="table table-sm table-bordered text-center m-0">
            <thead>
              <tr style="background: rgba(0,0,0,0.03);">
                <?php foreach ($thongKe7Ngay as $tk): ?>
                  <th><?= e(date('d/m', strtotime((string)$tk['ngay']))) ?></th>
                <?php endforeach; ?>
              </tr>
            </thead>
            <tbody>
              <tr>
                <?php foreach ($thongKe7Ngay as $tk): ?>
                  <td><strong><?= e($tk['so_luot']) ?></strong> lượt</td>
                <?php endforeach; ?>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    <?php endif; ?>
  </section>
</main>

<script src="js/canhan.js"></script>

<?php require __DIR__ . '/../../inc/footer.php'; ?>
