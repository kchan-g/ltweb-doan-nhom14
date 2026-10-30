<?php
declare(strict_types=1);

require_once __DIR__ . '/inc/config.php';

$tieuDe = 'Giới Thiệu Về Hệ Thống UniEvent';
$trang  = 'gioi-thieu';

require __DIR__ . '/inc/header.php';
?>

<main>
  <div class="container">
    <nav class="breadcrumb mb-4" aria-label="Đường dẫn phân cấp">
      <a href="index.php">Trang chủ</a>
      <span aria-hidden="true">/</span>
      <span class="hien-tai">Giới thiệu hệ thống</span>
    </nav>

    <section class="gioi-thieu mb-5">
      <div class="noi-dung">
        <h1 class="mb-3 font-hien-thi fw-800 text-chinh">Giới Thiệu Về Hệ Thống UniEvent</h1>
        <p class="do-dai-chuan text-phu mb-3" style="font-size: 1.05rem; line-height: 1.7;">
          UniEvent là nền tảng số hỗ trợ quản lý, kết nối và tổ chức các
          hoạt động sự kiện học thuật, văn hóa, thể thao dành riêng cho cộng
          đồng sinh viên và cán bộ giảng viên Trường Đại học Sư phạm – Đại
          học Đà Nẵng.
        </p>
        <p class="do-dai-chuan text-phu mb-4" style="line-height: 1.7;">
          Mục tiêu của dự án là xây dựng môi trường gắn kết năng động, giúp
          sinh viên chủ động cập nhật các hoạt động phong trào, đăng ký vé
          trực tuyến thuận tiện và tự động hóa quy trình ghi nhận điểm rèn
          luyện thông qua mã QR cá nhân hóa.
        </p>

        <div class="hero__cta d-flex gap-3 flex-wrap">
          <a href="danh-sach.php" class="nut-bam nut-nhan py-2 px-4" style="font-weight:700;">
            Khám phá sự kiện ngay →
          </a>
          <a href="lien-he.php" class="nut-bam nut-phu py-2 px-4">
            Liên hệ hợp tác
          </a>
        </div>
      </div>

      <div class="hinh-anh">
        <img
          src="images/anhsukien.jpg"
          alt="Toàn cảnh không khí sự kiện sinh viên sôi động tại ĐH Sư Phạm Đà Nẵng"
          width="480"
          height="300"
          loading="lazy"
          class="img-cover"
          style="border-radius: 16px; box-shadow: var(--shadow-card);"
        />
        <img
          src="images/anhnhom.jpg"
          alt="Các thành viên nhóm sinh viên phát triển dự án công nghệ"
          width="240"
          height="180"
          loading="lazy"
          class="img-cover"
          style="border-radius: 14px; box-shadow: var(--shadow-sm);"
        />
        <img
          src="images/anhsinhvien.jpg"
          alt="Sinh viên tham gia các hoạt động ngoại khóa hào hứng"
          width="240"
          height="180"
          loading="lazy"
          class="img-cover"
          style="border-radius: 14px; box-shadow: var(--shadow-sm);"
        />
      </div>
    </section>

    <section class="thanh-vien-nhom mb-5" aria-labelledby="tieu-de-nhom">
      <div class="section-head justify-center text-center mb-5">
        <div>
          <h2 id="tieu-de-nhom" class="tieu-de-muc font-hien-thi fw-800 text-chinh">
            Nhóm Sinh Viên Phát Triển Dự Án
          </h2>
          <p class="section-head__mota do-dai-chuan mx-auto text-phu mt-2" style="font-size: 1.05rem;">
            Đội ngũ sinh viên thực hiện đồ án môn Thiết kế &amp; Lập trình
            Web — Khoa Toán - Tin, Trường Đại học Sư phạm – Đại học Đà Nẵng.
          </p>
        </div>
      </div>

      <div class="danh-sach-thanh-vien">
        <article class="thanh-vien" style="background:var(--nen-surface); border:1px solid var(--border-nhat); border-radius:16px; padding:28px 20px; text-align:center; box-shadow:var(--shadow-card); transition:transform var(--transition-chuan), box-shadow var(--transition-chuan);">
          <img
            src="images/anhthanhvien1.jpg"
            alt="Chân dung thành viên Đinh Trịnh Ngọc Hưng"
            width="100"
            height="100"
            loading="lazy"
            style="border-radius:50%; object-fit:cover; border:3px solid var(--navy-50); box-shadow:var(--shadow-sm); margin:0 auto 16px;"
          />
          <h3 class="font-hien-thi fw-700 text-chinh mb-1" style="font-size:1.15rem;">Đinh Trịnh Ngọc Hưng</h3>
          <p class="mssv fs-meta text-phu mb-2" style="font-weight:600;">MSSV: 3120224065</p>
          <span class="badge mb-3" style="background:var(--navy-50); color:var(--mau-chinh); border:1px solid var(--border-vua); font-weight:600; font-size:0.8rem; border-radius:var(--radius-pill); display:inline-block;">Khung chung &amp; Trang chủ</span>
          <div>
            <a
              href="thanhvien/3120224065_Hung/gioithieu.php"
              class="nut-bam nut-nhan nut-nho"
              style="padding: 6px 16px;"
            >
              Xem trang cá nhân →
            </a>
          </div>
        </article>

        <article class="thanh-vien" style="background:var(--nen-surface); border:1px solid var(--border-nhat); border-radius:16px; padding:28px 20px; text-align:center; box-shadow:var(--shadow-card); transition:transform var(--transition-chuan), box-shadow var(--transition-chuan);">
          <img
            src="images/anhthanhvien2.jpg"
            alt="Chân dung thành viên Nguyễn Hoài Bảo"
            width="100"
            height="100"
            loading="lazy"
            style="border-radius:50%; object-fit:cover; border:3px solid var(--navy-50); box-shadow:var(--shadow-sm); margin:0 auto 16px;"
          />
          <h3 class="font-hien-thi fw-700 text-chinh mb-1" style="font-size:1.15rem;">Nguyễn Hoài Bảo</h3>
          <p class="mssv fs-meta text-phu mb-2" style="font-weight:600;">MSSV: 3120224011</p>
          <span class="badge mb-3" style="background:var(--navy-50); color:var(--mau-chinh); border:1px solid var(--border-vua); font-weight:600; font-size:0.8rem; border-radius:var(--radius-pill); display:inline-block;">Code trang Giới thiệu</span>
          <div>
            <a
              href="thanhvien/3120224011_Bao/gioithieu.php"
              class="nut-bam nut-nhan nut-nho"
              style="padding: 6px 16px;"
            >
              Xem trang cá nhân →
            </a>
          </div>
        </article>

        <article class="thanh-vien" style="background:var(--nen-surface); border:1px solid var(--border-nhat); border-radius:16px; padding:28px 20px; text-align:center; box-shadow:var(--shadow-card); transition:transform var(--transition-chuan), box-shadow var(--transition-chuan);">
          <img
            src="images/anhthanhvien3.jpg"
            alt="Chân dung thành viên Lê Phú Đạt"
            width="100"
            height="100"
            loading="lazy"
            style="border-radius:50%; object-fit:cover; border:3px solid var(--navy-50); box-shadow:var(--shadow-sm); margin:0 auto 16px;"
          />
          <h3 class="font-hien-thi fw-700 text-chinh mb-1" style="font-size:1.15rem;">Lê Phú Đạt</h3>
          <p class="mssv fs-meta text-phu mb-2" style="font-weight:600;">MSSV: 3120224024</p>
          <span class="badge mb-3" style="background:var(--navy-50); color:var(--mau-chinh); border:1px solid var(--border-vua); font-weight:600; font-size:0.8rem; border-radius:var(--radius-pill); display:inline-block;">Code trang Chi tiết</span>
          <div>
            <a
              href="thanhvien/3120224024_Dat/gioithieu.php"
              class="nut-bam nut-nhan nut-nho"
              style="padding: 6px 16px;"
            >
              Xem trang cá nhân →
            </a>
          </div>
        </article>

        <article class="thanh-vien" style="background:var(--nen-surface); border:1px solid var(--border-nhat); border-radius:16px; padding:28px 20px; text-align:center; box-shadow:var(--shadow-card); transition:transform var(--transition-chuan), box-shadow var(--transition-chuan);">
          <img
            src="images/anhthanhvien4.jpg"
            alt="Chân dung thành viên Nguyễn Thị Kiều Trang"
            width="100"
            height="100"
            loading="lazy"
            style="border-radius:50%; object-fit:cover; border:3px solid var(--navy-50); box-shadow:var(--shadow-sm); margin:0 auto 16px;"
          />
          <h3 class="font-hien-thi fw-700 text-chinh mb-1" style="font-size:1.15rem;">Nguyễn Thị Kiều Trang</h3>
          <p class="mssv fs-meta text-phu mb-2" style="font-weight:600;">MSSV: 3120224153</p>
          <span class="badge mb-3" style="background:var(--navy-50); color:var(--mau-chinh); border:1px solid var(--border-vua); font-weight:600; font-size:0.8rem; border-radius:var(--radius-pill); display:inline-block;">Thiết kế UX / UI</span>
          <div>
            <a
              href="thanhvien/3120224153_Trang/gioithieu.php"
              class="nut-bam nut-nhan nut-nho"
              style="padding: 6px 16px;"
            >
              Xem trang cá nhân →
            </a>
          </div>
        </article>

        <article class="thanh-vien" style="background:var(--nen-surface); border:1px solid var(--border-nhat); border-radius:16px; padding:28px 20px; text-align:center; box-shadow:var(--shadow-card); transition:transform var(--transition-chuan), box-shadow var(--transition-chuan);">
          <img
            src="images/anhthanhvien5.jpg"
            alt="Chân dung thành viên Phan Nhuận"
            width="100"
            height="100"
            loading="lazy"
            style="border-radius:50%; object-fit:cover; border:3px solid var(--navy-50); box-shadow:var(--shadow-sm); margin:0 auto 16px;"
          />
          <h3 class="font-hien-thi fw-700 text-chinh mb-1" style="font-size:1.15rem;">Phan Nhuận</h3>
          <p class="mssv fs-meta text-phu mb-2" style="font-weight:600;">MSSV: 3120224106</p>
          <span class="badge mb-3" style="background:var(--navy-50); color:var(--mau-chinh); border:1px solid var(--border-vua); font-weight:600; font-size:0.8rem; border-radius:var(--radius-pill); display:inline-block;">Code trang Danh sách</span>
          <div>
            <a
              href="thanhvien/3120224106_Nhuan/gioithieu.php"
              class="nut-bam nut-nhan nut-nho"
              style="padding: 6px 16px;"
            >
              Xem trang cá nhân →
            </a>
          </div>
        </article>
      </div>
    </section>
  </div>
</main>

<?php require __DIR__ . '/inc/footer.php'; ?>
