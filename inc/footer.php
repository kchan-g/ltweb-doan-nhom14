<?php
/**
 * inc/footer.php
 * Khung chân trang (Footer) dùng chung cho toàn bộ website UniEvent.
 */

declare(strict_types=1);

$goc ??= '';
?>
  <!-- Footer (Vùng "chan") -->
  <footer class="vung-chan site-footer">
    <div class="container footer-grid">
      <!-- Cột 1: Thông tin thương hiệu -->
      <div>
        <div class="d-flex align-items-center gap-2 mb-3">
          <img
            src="<?= $goc ?>images/logo.png"
            alt="Logo UniEvent"
            width="32"
            height="32"
          />
          <span class="fs-h3 fw-bold text-chinh">UniEvent</span>
        </div>
        <p class="fs-meta">
          Nền tảng truyền thông và quản lý sự kiện chính thức dành cho sinh viên
          Trường Đại học Sư phạm – Đại học Đà Nẵng.
        </p>
        <p class="fs-meta mt-2">
          📍 Địa chỉ: 459 Tôn Đức Thắng, Phường Hòa Khánh Nam, Quận Liên Chiểu, TP. Đà Nẵng
        </p>
      </div>

      <!-- Cột 2: Điều hướng nhanh -->
      <div>
        <h3>Liên kết nhanh</h3>
        <ul>
          <li><a href="<?= $goc ?>index.php">Trang chủ</a></li>
          <li><a href="<?= $goc ?>danh-sach.php">Danh sách sự kiện</a></li>
          <li><a href="<?= $goc ?>gio-hang.php">Giỏ vé đăng ký</a></li>
          <li><a href="<?= $goc ?>gioi-thieu.php">Về chúng tôi</a></li>
          <li><a href="<?= $goc ?>lien-he.php">Liên hệ ban tổ chức</a></li>
        </ul>
      </div>

      <!-- Cột 3: Hỗ trợ sinh viên -->
      <div>
        <h3>Hỗ trợ sinh viên</h3>
        <ul>
          <li><a href="<?= $goc ?>danh-sach.php">Quy chế đăng ký tham gia sự kiện</a></li>
          <li><a href="<?= $goc ?>danh-sach.php">Quy định tích lũy điểm rèn luyện</a></li>
          <li><a href="<?= $goc ?>lien-he.php">Câu hỏi thường gặp (FAQ)</a></li>
          <li><a href="<?= $goc ?>lien-he.php">Bảo mật thông tin sinh viên</a></li>
        </ul>
      </div>

      <!-- Cột 4: Mạng xã hội & Kết nối -->
      <div>
        <h3>Kết nối mạng xã hội</h3>
        <p>Theo dõi các kênh thông tin chính thức của Đoàn - Hội trường ĐHSP:</p>
        <div class="mang-xa-hoi mt-3">
          <a
            href="https://www.facebook.com"
            target="_blank"
            rel="noopener noreferrer"
            aria-label="Facebook UniEvent"
          >f</a>
          <a
            href="https://www.youtube.com"
            target="_blank"
            rel="noopener noreferrer"
            aria-label="Kênh YouTube UniEvent"
          >▶</a>
          <a
            href="https://twitter.com"
            target="_blank"
            rel="noopener noreferrer"
            aria-label="Twitter UniEvent"
          >t</a>
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

  <!-- JavaScript dùng chung -->
  <script type="module" src="<?= $goc ?>js/main.js"></script>
</body>
</html>
