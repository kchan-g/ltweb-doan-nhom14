<?php
/**
 * gioi-thieu.php
 * Trang giới thiệu hệ thống UniEvent và đội ngũ phát triển Nhóm 14.
 * Sử dụng khung trang dùng chung (header.php, footer.php) và giữ nguyên 100% cấu trúc CSS Design System gốc.
 */

declare(strict_types=1);

require_once __DIR__ . '/inc/config.php';

$tieuDe = 'Giới Thiệu Về Hệ Thống UniEvent';
$trang  = 'gioi-thieu';

require __DIR__ . '/inc/header.php';
?>

<main>
  <div class="container">
    <!-- Breadcrumb -->
    <nav class="breadcrumb mb-3" aria-label="Đường dẫn phân cấp">
      <a href="index.php">Trang chủ</a>
      <span aria-hidden="true">/</span>
      <span class="hien-tai">Giới thiệu hệ thống</span>
    </nav>

    <!-- Section 1: Giới thiệu dự án UniEvent -->
    <section class="gioi-thieu mb-5">
      <div class="noi-dung">
        <!-- Thẻ H1 duy nhất của trang -->
        <h1 class="mb-3">Giới Thiệu Về Hệ Thống UniEvent</h1>
        <p class="do-dai-chuan text-phu mb-3">
          UniEvent là nền tảng số hỗ trợ quản lý, kết nối và tổ chức các
          hoạt động sự kiện học thuật, văn hóa, thể thao dành riêng cho cộng
          đồng sinh viên và cán bộ giảng viên Trường Đại học Sư phạm – Đại
          học Đà Nẵng.
        </p>
        <p class="do-dai-chuan text-phu mb-4">
          Mục tiêu của dự án là xây dựng môi trường gắn kết năng động, giúp
          sinh viên chủ động cập nhật các hoạt động phong trào, đăng ký vé
          trực tuyến thuận tiện và tự động hóa quy trình ghi nhận điểm rèn
          luyện thông qua mã QR cá nhân hóa.
        </p>

        <div class="hero__cta">
          <a href="danh-sach.php" class="nut-bam nut-nhan">
            Khám phá sự kiện ngay →
          </a>
          <a href="lien-he.php" class="nut-bam"> Liên hệ hợp tác </a>
        </div>
      </div>

      <!-- Hình ảnh hoạt động -->
      <div class="hinh-anh">
        <img
          src="images/anhsukien.jpg"
          alt="Toàn cảnh không khí sự kiện sinh viên sôi động tại ĐH Sư Phạm Đà Nẵng"
          width="480"
          height="300"
          loading="lazy"
          class="rounded-vua img-cover"
        />
        <img
          src="images/anhnhom.jpg"
          alt="Các thành viên nhóm sinh viên phát triển dự án công nghệ"
          width="240"
          height="180"
          loading="lazy"
          class="rounded-vua img-cover"
        />
        <img
          src="images/anhsinhvien.jpg"
          alt="Sinh viên tham gia các hoạt động ngoại khóa hào hứng"
          width="240"
          height="180"
          loading="lazy"
          class="rounded-vua img-cover"
        />
      </div>
    </section>

    <!-- Section 2: Danh sách thành viên nhóm phát triển (Bố cục bằng Flexbox) -->
    <section class="thanh-vien-nhom mb-5" aria-labelledby="tieu-de-nhom">
      <div class="section-head justify-center text-center">
        <div>
          <h2 id="tieu-de-nhom" class="tieu-de-muc">
            Nhóm Sinh Viên Phát Triển Dự Án
          </h2>
          <p class="section-head__mota do-dai-chuan mx-auto text-phu">
            Đội ngũ sinh viên thực hiện đồ án môn Thiết kế &amp; Lập trình
            Web — Khoa Toán - Tin, Trường Đại học Sư phạm – Đại học Đà Nẵng.
          </p>
        </div>
      </div>

      <!-- Bố cục bằng Flexbox -->
      <div class="danh-sach-thanh-vien">
        <!-- Thành viên 1: Đinh Trịnh Ngọc Hưng -->
        <article class="thanh-vien">
          <img
            src="images/anhthanhvien1.jpg"
            alt="Chân dung thành viên Đinh Trịnh Ngọc Hưng"
            width="96"
            height="96"
            loading="lazy"
          />
          <h3>Đinh Trịnh Ngọc Hưng</h3>
          <p class="mssv">MSSV: 3120224065</p>
          <p class="vai-tro">Khung chung &amp; Trang chủ</p>
          <a
            href="thanhvien/3120224065_Hung/gioithieu.php"
            class="nut-bam nut-nho"
          >
            Xem trang cá nhân →
          </a>
        </article>

        <!-- Thành viên 2: Nguyễn Hoài Bảo -->
        <article class="thanh-vien">
          <img
            src="images/anhthanhvien2.jpg"
            alt="Chân dung thành viên Nguyễn Hoài Bảo"
            width="96"
            height="96"
            loading="lazy"
          />
          <h3>Nguyễn Hoài Bảo</h3>
          <p class="mssv">MSSV: 3120224011</p>
          <p class="vai-tro">Code trang Giới thiệu</p>
          <a
            href="thanhvien/3120224011_Bao/gioithieu.html"
            class="nut-bam nut-nho"
          >
            Xem trang cá nhân →
          </a>
        </article>

        <!-- Thành viên 3: Lê Phú Đạt -->
        <article class="thanh-vien">
          <img
            src="images/anhthanhvien3.jpg"
            alt="Chân dung thành viên Lê Phú Đạt"
            width="96"
            height="96"
            loading="lazy"
          />
          <h3>Lê Phú Đạt</h3>
          <p class="mssv">MSSV: 3120224024</p>
          <p class="vai-tro">Code trang Chi tiết</p>
          <a
            href="thanhvien/3120224024_Dat/gioithieu.html"
            class="nut-bam nut-nho"
          >
            Xem trang cá nhân →
          </a>
        </article>

        <!-- Thành viên 4: Nguyễn Thị Kiểu Trang -->
        <article class="thanh-vien">
          <img
            src="images/anhthanhvien4.jpg"
            alt="Chân dung thành viên Nguyễn Thị Kiểu Trang"
            width="96"
            height="96"
            loading="lazy"
          />
          <h3>Nguyễn Thị Kiểu Trang</h3>
          <p class="mssv">MSSV: 3120224153</p>
          <p class="vai-tro">Thiết kế UX / UI</p>
          <a
            href="thanhvien/3120224153_Trang/gioithieu.html"
            class="nut-bam nut-nho"
          >
            Xem trang cá nhân →
          </a>
        </article>

        <!-- Thành viên 5: Phan Nhuận -->
        <article class="thanh-vien">
          <img
            src="images/anhthanhvien5.jpg"
            alt="Chân dung thành viên Phan Nhuận"
            width="96"
            height="96"
            loading="lazy"
          />
          <h3>Phan Nhuận</h3>
          <p class="mssv">MSSV: 3120224106</p>
          <p class="vai-tro">Code trang Danh sách</p>
          <a
            href="thanhvien/3120224106_Nhuan/gioithieu.html"
            class="nut-bam nut-nho"
          >
            Xem trang cá nhân →
          </a>
        </article>
      </div>
    </section>
  </div>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>
