<?php
declare(strict_types=1);

$goc ??= '';
?>
  <footer class="vung-chan site-footer">
    <div class="container footer-grid">
      <div>
        <div class="d-flex align-items-center gap-2 mb-3">
          <img
            src="<?= $goc ?>images/logo.png"
            alt="Logo UniEvent"
            width="36"
            height="36"
            style="border-radius: var(--radius-nho);"
          />
          <span class="fs-h3 fw-bold text-chinh" style="letter-spacing: -0.02em;">UniEvent</span>
        </div>
        <p class="fs-meta" style="line-height: 1.6; color: var(--chu-phu);">
          Nền tảng truyền thông và quản lý sự kiện chính thức dành cho sinh viên
          Trường Đại học Sư phạm – Đại học Đà Nẵng.
        </p>
        <p class="fs-meta mt-2" style="color: var(--chu-nhat);">
          <?= uiIcon('pin', 'icon-muted', 14) ?> Địa chỉ: 459 Tôn Đức Thắng, Phường Hòa Khánh Nam, Quận Liên Chiểu, TP. Đà Nẵng
        </p>
      </div>

      <div>
        <h2 class="footer-tieu-de">Liên kết nhanh</h2>
        <ul>
          <li><a href="<?= $goc ?>index.php">Trang chủ</a></li>
          <li><a href="<?= $goc ?>danh-sach.php">Danh sách sự kiện</a></li>
          <li><a href="<?= $goc ?>gio-hang.php">Giỏ vé đăng ký</a></li>
          <li><a href="<?= $goc ?>gioi-thieu.php">Về chúng tôi</a></li>
          <li><a href="<?= $goc ?>lien-he.php">Liên hệ ban tổ chức</a></li>
        </ul>
      </div>

      <div>
        <h2 class="footer-tieu-de">Hỗ trợ sinh viên</h2>
        <ul>
          <li><a href="<?= $goc ?>danh-sach.php">Đăng ký tham gia sự kiện</a></li>
          <li><a href="<?= $goc ?>danh-sach.php">Tích lũy điểm rèn luyện</a></li>
          <li><a href="<?= $goc ?>lien-he.php">Câu hỏi thường gặp (FAQ)</a></li>
          <li><a href="<?= $goc ?>lien-he.php">Bảo mật thông tin sinh viên</a></li>
        </ul>
      </div>

      <div>
        <h2 class="footer-tieu-de">Kết nối mạng xã hội</h2>
        <p style="color: var(--chu-phu); font-size: var(--co-chu-nho);">Theo dõi các kênh thông tin chính thức của Đoàn - Hội trường ĐHSP:</p>
        <div class="mang-xa-hoi mt-3">
          <a
            href="https://www.facebook.com"
            target="_blank"
            rel="noopener noreferrer"
            aria-label="Facebook UniEvent"
          ><?= uiIcon('facebook', '', 18) ?></a>
          <a
            href="https://www.youtube.com"
            target="_blank"
            rel="noopener noreferrer"
            aria-label="Kênh YouTube UniEvent"
          ><?= uiIcon('youtube', '', 18) ?></a>
          <a
            href="https://twitter.com"
            target="_blank"
            rel="noopener noreferrer"
            aria-label="Twitter UniEvent"
          ><?= uiIcon('twitter', '', 18) ?></a>
        </div>
      </div>
    </div>

    <div class="footer-bottom">
      <div class="container">
        <p>
          © 2026 UniEvent — Hệ thống Quản lý Sự kiện | Khoa Toán - Tin, Trường
          Đại học Sư phạm – Đại học Đà Nẵng.
        </p>
      </div>
    </div>
  </footer>

  <script type="module" src="<?= $goc ?>js/main.js"></script>
  <script src="<?= $goc ?>js/gio-hang.js"></script>
</body>
</html>
