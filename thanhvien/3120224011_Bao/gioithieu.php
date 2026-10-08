<?php
/**
 * Trang cá nhân Nguyễn Hoài Bảo, dùng chung header/footer UniEvent.
 * Có sổ lưu bút JSONL và bộ đếm lượt mở trang trong phiên hiện tại.
 * Thử tại /thanhvien/3120224011_Bao/gioithieu.php; gửi lời nhắn rồi tải lại.
 */
declare(strict_types=1);

require_once __DIR__ . '/../../inc/config.php';

$goc = '../../';
$tieuDe = 'Profile cá nhân - Nguyễn Hoài Bảo';
$trang = '';
$cssThem = [$goc . 'thanhvien/3120224011_Bao/css/style.css'];
$tepLuuBut = __DIR__ . '/../../storage/bao_luubut.jsonl';
$tepDem = __DIR__ . '/../../storage/bao_luot_xem.txt';
$khoaDem = 'bao_da_tinh_luot_xem_profile';
$tongLuotXem = null;
if (empty($_SESSION[$khoaDem])) {
  $tepDemMo = @fopen($tepDem, 'c+');
  if ($tepDemMo !== false) {
    if (flock($tepDemMo, LOCK_EX)) {
      $tongLuotXem = (int) trim(stream_get_contents($tepDemMo));
      $tongLuotXem++;
      rewind($tepDemMo);
      ftruncate($tepDemMo, 0);
      fwrite($tepDemMo, (string) $tongLuotXem);
      fflush($tepDemMo);
      $_SESSION[$khoaDem] = true;
      flock($tepDemMo, LOCK_UN);
    }
    fclose($tepDemMo);
  }
}
$tepDemDoc = @file_get_contents($tepDem);
if ($tepDemDoc !== false) {
  $tongLuotXem = (int) trim($tepDemDoc);
}

if (empty($_SESSION['bao_luubut_csrf'])) {
  $_SESSION['bao_luubut_csrf'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['bao_luubut_csrf'];
$loiLuuBut = '';
$tenNhap = '';
$loiNhan = '';
$thongBao = (string) ($_SESSION['bao_luubut_thong_bao'] ?? '');
unset($_SESSION['bao_luubut_thong_bao']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $tenNhap = isset($_POST['ten']) && is_string($_POST['ten']) ? trim($_POST['ten']) : '';
  $loiNhan = isset($_POST['loi_nhan']) && is_string($_POST['loi_nhan']) ? trim($_POST['loi_nhan']) : '';
  $tokenGui = isset($_POST['csrf_token']) && is_string($_POST['csrf_token']) ? $_POST['csrf_token'] : '';

  if (!hash_equals($csrfToken, $tokenGui)) {
    $loiLuuBut = 'Yêu cầu không hợp lệ. Vui lòng tải lại trang và thử lại.';
  } elseif (preg_match('/\A.{2,60}\z/us', $tenNhap) !== 1) {
    $loiLuuBut = 'Tên cần có từ 2 đến 60 ký tự hợp lệ.';
  } elseif (preg_match('/\A.{1,500}\z/us', $loiNhan) !== 1) {
    $loiLuuBut = 'Lời nhắn không được để trống và tối đa 500 ký tự.';
  } else {
    $dongLuu = json_encode(
      ['ten' => $tenNhap, 'loi_nhan' => $loiNhan, 'thoi_gian' => date(DATE_ATOM)],
      JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );

    if ($dongLuu === false || @file_put_contents($tepLuuBut, $dongLuu . PHP_EOL, FILE_APPEND | LOCK_EX) === false) {
      $loiLuuBut = 'Chưa thể lưu lời nhắn. Vui lòng thử lại sau.';
    } else {
      $_SESSION['bao_luubut_thong_bao'] = 'Lời nhắn của bạn đã được lưu.';
      header('Location: gioithieu.php#guestbook', true, 303);
      exit;
    }
  }
}

$danhSachLuuBut = [];
if (is_file($tepLuuBut) && is_readable($tepLuuBut)) {
  $tep = @fopen($tepLuuBut, 'rb');
  if ($tep !== false) {
    if (flock($tep, LOCK_SH)) {
      while (($dong = fgets($tep)) !== false) {
        $muc = json_decode($dong, true);
        if (
          is_array($muc)
          && isset($muc['ten'], $muc['loi_nhan'], $muc['thoi_gian'])
          && is_string($muc['ten'])
          && is_string($muc['loi_nhan'])
          && is_string($muc['thoi_gian'])
        ) {
          $danhSachLuuBut[] = $muc;
        }
      }
      flock($tep, LOCK_UN);
    }
    fclose($tep);
  }
}
$danhSachLuuBut = array_slice(array_reverse($danhSachLuuBut), 0, 5);

require __DIR__ . '/../../inc/header.php';
?>
    <!-- HEADER PROFILE -->
    <header class="profile-header">
      <div class="container">
        <span class="profile-header__badge"
          >Thành viên Ban Nội dung &bull; UniEvent</span
        >
        <h1>Profile cá nhân</h1>
        <p>Chào mừng bạn đến trang giới thiệu của Nguyễn Hoài Bảo</p>
        <p>Tổng lượt xem: <?= e($tongLuotXem ?? 'Chưa khả dụng') ?> (mỗi phiên chỉ tính một lần)</p>
      </div>
    </header>

    <!-- NAVIGATION BAR -->
    <nav class="profile-nav" aria-label="Điều hướng trang cá nhân">
      <div class="container">
        <ul>
          <li><a href="#about">Giới thiệu</a></li>
          <li><a href="#projects">Dự án &amp; Sở thích</a></li>
          <li><a href="#education">Học vấn</a></li>
          <li><a href="#skills">Kỹ năng</a></li>
          <li><a href="#schedule">Thời khóa biểu</a></li>
          <li><a href="#contact">Liên hệ</a></li>
          <li><a href="#guestbook">Sổ lưu bút</a></li>
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
              alt="Ảnh chân dung đại diện của Nguyễn Hoài Bảo"
              width="180"
              height="180"
              loading="lazy"
            />
          </div>
          <div class="about-info">
            <h2 id="about-heading">Giới Thiệu Bản Thân</h2>
            <span class="meta-pill">MSSV: 3120224011 &bull; Nhóm 14</span>
            <p>
              Xin chào! Mình là Nguyễn Hoài Bảo, sinh viên chuyên ngành Sư phạm
              Tin học / CNTT tại Trường Đại học Sư phạm - Đại học Đà Nẵng. Đam
              mê công nghệ, phát triển phần mềm, xây dựng các ứng dụng web và di
              động tối ưu trải nghiệm người dùng.
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
                Quản lý Sự kiện ĐHSP Đà Nẵng" (Phụ trách xây dựng và hoàn thiện
                trang Giới thiệu nền tảng).
              </p>
            </div>
            <div class="project-item">
              <p>
                <strong>Sở thích:</strong> Lập trình, tìm hiểu phần cứng máy
                tính, nghiên cứu kiến trúc hệ thống và chơi thể thao.
              </p>
            </div>
            <div class="project-item">
              <p>
                <strong>Mục tiêu:</strong> Trở thành Fullstack / Frontend
                Developer chuyên nghiệp, phát triển các giải pháp số ứng dụng
                trong giáo dục.
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
            <li>Làm việc nhóm &amp; Giao tiếp hiệu quả</li>
            <li>Quản lý thời gian &amp; Giải quyết vấn đề</li>
            <li>Sử dụng Git &amp; GitHub Version Control</li>
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
                <a href="mailto:nguyenhoaibao282006@gmail.com"
                  >nguyenhoaibao282006@gmail.com</a
                >
              </div>
            </li>
            <li>
              <span>📞</span>
              <div>
                <strong>Số điện thoại:</strong><br />
                <a href="tel:0799860146">0799 860 146</a>
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

      <!-- KHỐI 3: HỌC VẤN & MỤC TIÊU (TƯƠNG TÁC ACCORDION HỌC VẤN) -->
      <section
        id="education"
        class="profile-card education-section"
        aria-labelledby="education-heading"
      >
        <h2 id="education-heading">Quá Trình Học Tập &amp; Bằng Cấp</h2>
        <p class="education-lead mb-4">
          Nhấp vào từng mục bên dưới để xem chi tiết quá trình đào tạo và mục tiêu phát triển:
        </p>

        <div class="education-accordion">
          <!-- Mục 1: Đại học -->
          <div class="education-accordion__item">
            <button
              type="button"
              class="education-accordion__btn"
              id="btn-edu-1"
              aria-expanded="false"
              aria-controls="panel-edu-1"
            >
              <span class="education-accordion__title">
                🎓 Trường Đại học Sư phạm – Đại học Đà Nẵng (2022 – Hiện tại)
              </span>
              <span class="education-accordion__meta">
                <span class="education-accordion__hint">Bấm để mở rộng</span>
                <span class="education-accordion__icon" aria-hidden="true">▾</span>
              </span>
            </button>
            <div
              id="panel-edu-1"
              class="education-accordion__panel"
              role="region"
              aria-labelledby="btn-edu-1"
              hidden
            >
              <div class="education-accordion__content">
                <p>
                  <strong>Chuyên ngành đào tạo:</strong> Sư phạm Tin học / Công nghệ thông tin (Khoa Toán - Tin học).
                </p>
                <p>
                  <strong>Quá trình tích lũy:</strong> Đạt kết quả học tập tốt, hoàn thành các học phần nền tảng vững chắc: Thiết kế &amp; Lập trình Web, Cơ sở dữ liệu, Công nghệ phần mềm, An toàn thông tin.
                </p>
                <p>
                  <strong>Hoạt động:</strong> Tham gia tích cực các hoạt động phong trào Đoàn - Hội và dự án công nghệ sinh viên UniEvent.
                </p>
              </div>
            </div>
          </div>

          <!-- Mục 2: Chứng chỉ & Kỹ năng -->
          <div class="education-accordion__item">
            <button
              type="button"
              class="education-accordion__btn"
              id="btn-edu-2"
              aria-expanded="false"
              aria-controls="panel-edu-2"
            >
              <span class="education-accordion__title">
                💻 Khóa Huấn Luyện &amp; Kỹ Năng Số Chuyên Sâu
              </span>
              <span class="education-accordion__meta">
                <span class="education-accordion__hint">Bấm để mở rộng</span>
                <span class="education-accordion__icon" aria-hidden="true">▾</span>
              </span>
            </button>
            <div
              id="panel-edu-2"
              class="education-accordion__panel"
              role="region"
              aria-labelledby="btn-edu-2"
              hidden
            >
              <div class="education-accordion__content">
                <p>
                  <strong>Phát triển Giao diện Web:</strong> Thành thạo chuẩn ngữ nghĩa HTML5 Semantic, Modern CSS (Flexbox, CSS Grid), JavaScript ES6+ và tiêu chuẩn trợ năng Accessibility (a11y WCAG 2.1).
                </p>
                <p>
                  <strong>Quản trị Dữ liệu:</strong> Thiết kế CSDL quan hệ, tối ưu truy vấn SQL trên MySQL / SQL Server.
                </p>
                <p>
                  <strong>Quy trình &amp; Công cụ:</strong> Sử dụng thành thạo Git/GitHub cho làm việc nhóm, Figma để thiết kế giao diện UI/UX.
                </p>
              </div>
            </div>
          </div>

          <!-- Mục 3: Mục tiêu nghề nghiệp -->
          <div class="education-accordion__item">
            <button
              type="button"
              class="education-accordion__btn"
              id="btn-edu-3"
              aria-expanded="false"
              aria-controls="panel-edu-3"
            >
              <span class="education-accordion__title">
                🎯 Kế Hoạch &amp; Mục Tiêu Nghề Nghiệp Tương Lai
              </span>
              <span class="education-accordion__meta">
                <span class="education-accordion__hint">Bấm để mở rộng</span>
                <span class="education-accordion__icon" aria-hidden="true">▾</span>
              </span>
            </button>
            <div
              id="panel-edu-3"
              class="education-accordion__panel"
              role="region"
              aria-labelledby="btn-edu-3"
              hidden
            >
              <div class="education-accordion__content">
                <p>
                  <strong>Mục tiêu ngắn hạn:</strong> Hoàn thành xuất sắc đồ án học phần Thiết kế &amp; Lập trình Web với điểm số tối đa, làm chủ các kỹ thuật lập trình web hiện đại.
                </p>
                <p>
                  <strong>Mục tiêu dài hạn:</strong> Tốt nghiệp loại Giỏi chuyên ngành Sư phạm Tin học, trở thành Kỹ sư phần mềm Full-stack Web/Mobile và tham gia giảng dạy, phát triển các giải pháp phần mềm chuyển đổi số trong giáo dục.
                </p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- KHỐI 4: THỜI KHÓA BIỂU HỌC TẬP (KHUNG CUỘN NGANG RIÊNG BIỆT TRÊN MOBILE) -->
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
                <!-- Thứ 4: CSDL 3 tiết (chiếm tiết 1, 2, 3) -->
                <td rowspan="3">
                  <strong>Hệ quản trị CSDL</strong>
                  31241283 - 24-0102<br />
                  <span class="room-badge">Phòng: A5-404B</span>
                </td>
                <td></td>
                <td></td>
                <!-- Thứ 7: Web 3 tiết (chiếm tiết 1, 2, 3) -->
                <td rowspan="3">
                  <strong>Thiết kế &amp; Lập trình Web</strong>
                  31231755 - 24-0102<br />
                  <span class="room-badge">Phòng: B3-303</span>
                </td>
                <td></td>
              </tr>

              <!-- Tiết 2 (Thứ 4 và Thứ 7 đang bận) -->
              <tr>
                <th scope="row">2</th>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
              </tr>

              <!-- Tiết 3 (Bắt đầu môn học Thứ 3) -->
              <tr>
                <th scope="row">3</th>
                <td></td>
                <!-- Thứ 3: MÔN HỌC MỚI (chiếm tiết 3 và 4) -->
                <td rowspan="2">
                  <strong>Lịch sử Đảng CSVN</strong>
                  21221904 - 24-0311<br />
                  <span class="room-badge">Phòng: A6-502</span>
                </td>
                <!-- Thứ 4 và Thứ 7 vẫn đang bận bởi Tiết 1 -->
                <td></td>
                <td></td>
                <td></td>
              </tr>

              <!-- Tiết 4 (Thứ 3 đang bận bởi Tiết 3 nên không có <td>) -->
              <tr>
                <th scope="row">4</th>
                <td></td>
                <td></td>
                <!-- Thứ 5: Khai phá dữ liệu (chiếm tiết 4, 5, 6) -->
                <td rowspan="3">
                  <strong>Khai phá dữ liệu</strong>
                  31231330 - 24-0102<br />
                  <span class="room-badge">Phòng: A5-404B</span>
                </td>
                <td></td>
                <td></td>
                <td></td>
              </tr>

              <!-- Tiết 5 (Thứ 5 đang bận nên không có <td>) -->
              <tr>
                <th scope="row">5</th>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
              </tr>

              <!-- Tiết 6 (Thứ 5 đang bận nên không có <td>) -->
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
                <!-- Thứ 2  -->
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
                <!-- Thứ 5 học Công nghệ phần mềm -->
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

      <section id="guestbook" class="profile-card" aria-labelledby="guestbook-heading">
        <h2 id="guestbook-heading">Sổ lưu bút</h2>
        <p>Gửi lời nhắn đến Nguyễn Hoài Bảo.</p>
        <?php if ($thongBao !== ''): ?>
          <p role="status"><?= e($thongBao) ?></p>
        <?php endif; ?>
        <?php if ($loiLuuBut !== ''): ?>
          <p role="alert"><?= e($loiLuuBut) ?></p>
        <?php endif; ?>
        <form method="post" action="gioithieu.php#guestbook">
          <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
          <p>
            <label for="ten">Tên của bạn</label><br>
            <input id="ten" name="ten" type="text" minlength="2" maxlength="60" required value="<?= e($tenNhap) ?>">
          </p>
          <p>
            <label for="loi_nhan">Lời nhắn</label><br>
            <textarea id="loi_nhan" name="loi_nhan" rows="4" maxlength="500" required><?= e($loiNhan) ?></textarea>
          </p>
          <button type="submit">Gửi lời nhắn</button>
        </form>

        <h3>Năm lời nhắn mới nhất</h3>
        <?php if ($danhSachLuuBut === []): ?>
          <p>Chưa có lời nhắn nào.</p>
        <?php else: ?>
          <ol>
            <?php foreach ($danhSachLuuBut as $muc): ?>
              <li>
                <p><strong><?= e($muc['ten']) ?></strong> · <?= e($muc['thoi_gian']) ?></p>
                <p><?= nl2br(e($muc['loi_nhan'])) ?></p>
              </li>
            <?php endforeach; ?>
          </ol>
        <?php endif; ?>
      </section>
    </main>

    <footer class="profile-footer">
      <div class="container">
        <a href="<?= e($goc) ?>index.php" class="btn-back"
          >&larr; Quay lại Trang chủ UniEvent</a
        >
        <p>
          &copy; 2026 Bản quyền thuộc về Nguyễn Hoài Bảo &bull; Nhóm 14 -
          UniEvent (ĐH Sư phạm - ĐHĐN)
        </p>
      </div>
    </footer>

    <!-- THANH TIẾN ĐỘ CUỘN TRANG (SCROLL INDICATOR) -->
    <aside class="scroll-tracker" aria-label="Tiến độ cuộn trang">
      <div class="scroll-tracker__track">
        <div
          id="scrollProgressBar"
          class="scroll-tracker__bar"
          role="progressbar"
          aria-label="Tiến độ cuộn trang cá nhân"
          aria-valuemin="0"
          aria-valuemax="100"
          aria-valuenow="0"
        ></div>
      </div>
      <span id="scrollPercentText" class="scroll-tracker__text" aria-live="polite">0%</span>
    </aside>

    <!-- Kịch bản JavaScript tương tác cá nhân -->
    <script src="js/canhan.js"></script>
<?php require __DIR__ . '/../../inc/footer.php'; ?>
