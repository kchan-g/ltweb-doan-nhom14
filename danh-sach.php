<!doctype html>
<html lang="vi">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta
      name="description"
      content="Danh sách các sự kiện dành cho sinh viên trên hệ thống UniEvent - Trường Đại học Sư phạm Đà Nẵng."
    />
    <title>Danh Sách Sự Kiện | UniEvent</title>
    <meta name="theme-color" content="#0284c7" />
    <link rel="icon" type="image/svg+xml" href="images/favicon.svg" />

    <!-- Nạp 5 tệp CSS theo module chuẩn đề bài -->
    <link rel="stylesheet" href="css/01-bien.css" />
    <link rel="stylesheet" href="css/02-chuan-hoa.css" />
    <link rel="stylesheet" href="css/03-bo-cuc.css" />
    <link rel="stylesheet" href="css/04-thanh-phan.css" />
    <link rel="stylesheet" href="css/05-tien-ich.css" />
  </head>

  <body class="trang">
    <!-- Header chung -->
    <header class="vung-dau">
      <div class="container site-header__inner">
        <a href="index.html" class="thuong-hieu">
          <img
            src="images/logo.png"
            alt="Logo UniEvent - Cổng sự kiện Đại học Sư phạm Đà Nẵng"
            class="logo-img"
            width="44"
            height="44"
          />
          <div>
            <div class="thuong-hieu__ten">UniEvent</div>
            <div class="thuong-hieu__mota">
              Hệ thống Quản lý Sự kiện ĐHSP Đà Nẵng
            </div>
          </div>
        </a>

        <div class="header-phai">
          <button
            type="button"
            class="nut-menu"
            id="nut-menu"
            aria-label="Mở menu điều hướng"
            aria-expanded="false"
            aria-controls="menu-chinh"
          >
            <span aria-hidden="true">☰</span>
          </button>
          <div class="search-box" role="search">
            <label for="tim-kiem-header" class="chi-danh-cho-sr"
              >Tìm kiếm sự kiện</label
            >
            <input
              type="text"
              id="tim-kiem-header"
              placeholder="Tìm kiếm sự kiện, hội thảo..."
            />
            <button type="button" aria-label="Tìm kiếm">
              <svg
                width="15"
                height="15"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2.5"
                stroke-linecap="round"
                stroke-linejoin="round"
                aria-hidden="true"
              >
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
              </svg>
            </button>
          </div>
          <a href="danh-sach.html#yeu-thich" class="nut-yeu-thich-header">
            <span>Đã lưu</span>
            <span class="huy-hieu-dem" id="dem-yeu-thich" aria-live="polite"
              >0</span
            >
          </a>
          <div class="tai-khoan" role="group" aria-label="Tài khoản người dùng">
            <span>Admin</span>
          </div>
        </div>
      </div>
    </header>

    <!-- Navigation chung -->
    <nav
      class="vung-menu site-nav"
      id="menu-chinh"
      aria-label="Điều hướng chính"
    >
      <div class="container">
        <ul>
          <li>
            <a href="index.html">Trang chủ</a>
          </li>
          <li>
            <a href="danh-sach.html" class="active" aria-current="page"
              >Danh sách sự kiện</a
            >
          </li>
          <li>
            <a href="gioi-thieu.html">Giới thiệu</a>
          </li>
          <li>
            <a href="lien-he.html">Liên hệ</a>
          </li>
        </ul>
      </div>
    </nav>

    <!-- Main Content -->
    <main>
      <div class="container">
        <!-- Breadcrumb -->
        <nav class="breadcrumb mb-3" aria-label="Đường dẫn phân cấp">
          <a href="index.html">Trang chủ</a>
          <span aria-hidden="true">/</span>
          <span class="hien-tai">Danh sách sự kiện</span>
        </nav>

        <!-- Tiêu đề trang duy nhất (H1) -->
        <div class="trang-tieu-de mb-4">
          <h1>Danh Sách Các Sự Kiện Đang Diễn Ra</h1>
          <p class="do-dai-chuan text-phu">
            Khám phá và đăng ký các hoạt động học thuật, văn hóa, thể thao đang
            diễn ra tại Trường Đại học Sư phạm – Đại học Đà Nẵng.
          </p>
        </div>

        <!-- Thanh lọc theo danh mục -->
        <section class="bo-loc mb-5" aria-labelledby="tieu-de-bo-loc">
          <h2 id="tieu-de-bo-loc" class="chi-danh-cho-sr">
            Bộ lọc tìm kiếm sự kiện
          </h2>

          <form action="#" method="get">
            <div class="form-nhom">
              <label for="tim-kiem-danh-sach">Tìm kiếm sự kiện:</label>
              <input
                type="search"
                id="tim-kiem-danh-sach"
                name="tu-khoa"
                placeholder="Tìm kiếm sự kiện..."
                autocomplete="off"
              />
            </div>

            <div class="form-nhom">
              <label for="danh-muc-select">Chọn phân loại danh mục:</label>
              <select id="danh-muc-select" name="danh-muc">
                <option value="">Tất cả các danh mục sự kiện</option>
                <option value="hoi-thao">
                  Hội thảo khoa học &amp; Công nghệ
                </option>
                <option value="van-hoa">Văn hóa &amp; Nghệ thuật</option>
                <option value="the-thao">Thể dục - Thể thao sinh viên</option>
                <option value="hoc-thuat">Học thuật &amp; Kỹ năng mềm</option>
                <option value="viec-lam">
                  Cơ hội việc làm &amp; Tuyển dụng
                </option>
              </select>
            </div>

            <div class="form-nhom">
              <label for="sap-xep-select">Sắp xếp theo tiêu chí:</label>
              <select id="sap-xep-select" name="sap-xep">
                <option value="moi-nhat">Mới nhất tuần này</option>
                <option value="sap-dien-ra">Sắp diễn ra gần nhất</option>
                <option value="diem-ren-luyen">Điểm rèn luyện cao nhất</option>
                <option value="ten-az">Tên sự kiện (A - Z)</option>
              </select>
            </div>

            <button type="submit" class="nut-bam nut-nhan">Lọc sự kiện</button>
          </form>
        </section>

        <!-- Danh sách thẻ sự kiện: Lưới repeat(auto-fit, minmax) -->
        <section aria-labelledby="tieu-de-tat-ca-su-kien">
          <div class="section-head mb-4">
            <h2 id="tieu-de-tat-ca-su-kien" class="tieu-de-muc">
              Tất Cả Sự Kiện Mới Nhất
            </h2>
          </div>

          <div
            id="vung-thong-bao-danh-sach"
            class="vung-thong-bao-ket-qua mb-3"
            aria-live="polite"
          ></div>

          <ul class="danh-sach-su-kien">
            <!-- Item 1: Workshop AI -->
            <li>
              <article class="the-tin">
                <div class="the-tin__media">
                  <span class="badge">Hội thảo</span>
                  <img
                    src="images/AI.jpg"
                    alt="Poster Workshop Trí tuệ Nhân tạo trong thời đại số"
                    width="360"
                    height="225"
                    loading="lazy"
                  />
                </div>
                <div class="the-tin__body">
                  <h3>Workshop: Ứng Dụng AI Để Nâng Cao Hiệu Quả Công Việc</h3>
                  <p class="event-meta">Thời gian: 25/08/2026, 18:00 - 20:30</p>
                  <p class="event-meta">Địa điểm: Hội trường A1, ĐH Sư Phạm</p>
                  <p class="event-meta">Quyền lợi: +5 Điểm rèn luyện</p>
                  <div class="the-tin__actions mt-3">
                    <button
                      type="button"
                      class="nut-bam nut-nhan nut-nho nut-xem-chi-tiet"
                      data-id="1"
                      aria-haspopup="dialog"
                    >
                      Xem chi tiết sự kiện
                    </button>
                    <button
                      type="button"
                      class="nut-yeu-thich"
                      data-id="1"
                      aria-pressed="false"
                      aria-label="Lưu tin - Lưu sự kiện"
                    >
                      Lưu tin
                    </button>
                  </div>
                </div>
              </article>
            </li>

            <!-- Item 2: Định hướng nghề nghiệp -->
            <li>
              <article class="the-tin">
                <div class="the-tin__media">
                  <span class="badge badge--vang">Kỹ năng</span>
                  <img
                    src="images/nghe.jpg"
                    alt="Hình ảnh Hội thảo Định hướng nghề nghiệp Gen Z"
                    width="360"
                    height="225"
                    loading="lazy"
                  />
                </div>
                <div class="the-tin__body">
                  <h3>
                    Hội Thảo: Gen Z Chuyện Chọn Nghề &amp; Phát Triển Kỹ Năng
                  </h3>
                  <p class="event-meta">Thời gian: 26/08/2026, 08:30 - 11:30</p>
                  <p class="event-meta">Địa điểm: Hội trường A, Khu Hiệu bộ</p>
                  <p class="event-meta">Quyền lợi: +4 Điểm rèn luyện</p>
                  <div class="the-tin__actions mt-3">
                    <button
                      type="button"
                      class="nut-bam nut-nhan nut-nho nut-xem-chi-tiet"
                      data-id="2"
                      aria-haspopup="dialog"
                    >
                      Xem chi tiết sự kiện
                    </button>
                    <button
                      type="button"
                      class="nut-yeu-thich"
                      data-id="2"
                      aria-pressed="false"
                      aria-label="Lưu tin - Lưu sự kiện"
                    >
                      Lưu tin
                    </button>
                  </div>
                </div>
              </article>
            </li>

            <!-- Item 3: Đêm nhạc sinh viên -->
            <li>
              <article class="the-tin">
                <div class="the-tin__media">
                  <span class="badge badge--info">Văn hóa</span>
                  <img
                    src="images/giaidieu.jpg"
                    alt="Ban nhạc trình diễn tại Đêm nhạc Giai điệu sinh viên"
                    width="360"
                    height="225"
                    loading="lazy"
                  />
                </div>
                <div class="the-tin__body">
                  <h3>Đêm Nhạc Hội "Giai Điệu Sinh Viên" Sắc Màu Tuổi Trẻ</h3>
                  <p class="event-meta">Thời gian: 30/08/2026, 19:00 - 22:00</p>
                  <p class="event-meta">
                    Địa điểm: Quảng trường Trung tâm ĐHSP
                  </p>
                  <p class="event-meta">Quyền lợi: +5 Điểm rèn luyện</p>
                  <div class="the-tin__actions mt-3">
                    <button
                      type="button"
                      class="nut-bam nut-nhan nut-nho nut-xem-chi-tiet"
                      data-id="3"
                      aria-haspopup="dialog"
                    >
                      Xem chi tiết sự kiện
                    </button>
                    <button
                      type="button"
                      class="nut-yeu-thich"
                      data-id="3"
                      aria-pressed="false"
                      aria-label="Lưu tin - Lưu sự kiện"
                    >
                      Lưu tin
                    </button>
                  </div>
                </div>
              </article>
            </li>

            <!-- Item 4: Giải bóng đá sinh viên -->
            <li>
              <article class="the-tin">
                <div class="the-tin__media">
                  <span class="badge badge--success">Thể thao</span>
                  <img
                    src="images/bongda.jpg"
                    alt="Trận đấu trong Giải bóng đá sinh viên tranh cúp UniEvent"
                    width="360"
                    height="225"
                    loading="lazy"
                  />
                </div>
                <div class="the-tin__body">
                  <h3>
                    Giải Bóng Đá Sinh Viên Tranh Cúp Vô Địch UniEvent 2026
                  </h3>
                  <p class="event-meta">Thời gian: 05/09/2026, 15:30 - 18:00</p>
                  <p class="event-meta">
                    Địa điểm: Sân vận động cỏ nhân tạo trường
                  </p>
                  <p class="event-meta">Quyền lợi: +4 Điểm rèn luyện</p>
                  <div class="the-tin__actions mt-3">
                    <button
                      type="button"
                      class="nut-bam nut-nhan nut-nho nut-xem-chi-tiet"
                      data-id="4"
                      aria-haspopup="dialog"
                    >
                      Xem chi tiết sự kiện
                    </button>
                    <button
                      type="button"
                      class="nut-yeu-thich"
                      data-id="4"
                      aria-pressed="false"
                      aria-label="Lưu tin - Lưu sự kiện"
                    >
                      Lưu tin
                    </button>
                  </div>
                </div>
              </article>
            </li>

            <!-- Item 5: Hội thảo Công nghệ 4.0 -->
            <li>
              <article class="the-tin">
                <div class="the-tin__media">
                  <span class="badge">Công nghệ</span>
                  <img
                    src="images/event1.jpg"
                    alt="Hội thảo Công nghệ 4.0 và chuyển đổi số sinh viên"
                    width="360"
                    height="225"
                    loading="lazy"
                  />
                </div>
                <div class="the-tin__body">
                  <h3>
                    Hội Thảo Công Nghệ 4.0: Cơ Hội Việc Làm Lập Trình &amp; IT
                  </h3>
                  <p class="event-meta">Thời gian: 28/10/2026, 08:30 - 11:30</p>
                  <p class="event-meta">Địa điểm: Hội trường A1 - ĐH Sư Phạm</p>
                  <p class="event-meta">Quyền lợi: +5 Điểm rèn luyện</p>
                  <div class="the-tin__actions mt-3">
                    <button
                      type="button"
                      class="nut-bam nut-nhan nut-nho nut-xem-chi-tiet"
                      data-id="5"
                      aria-haspopup="dialog"
                    >
                      Xem chi tiết sự kiện
                    </button>
                    <button
                      type="button"
                      class="nut-yeu-thich"
                      data-id="5"
                      aria-pressed="false"
                      aria-label="Lưu tin - Lưu sự kiện"
                    >
                      Lưu tin
                    </button>
                  </div>
                </div>
              </article>
            </li>

            <!-- Item 6: Ngày hội Câu lạc bộ -->
            <li>
              <article class="the-tin">
                <div class="the-tin__media">
                  <span class="badge badge--vang">CLB Đội nhóm</span>
                  <img
                    src="images/hero-image.jpg"
                    alt="Sinh viên tham gia Ngày hội Câu Lạc Bộ UniEvent"
                    width="360"
                    height="225"
                    loading="lazy"
                  />
                </div>
                <div class="the-tin__body">
                  <h3>
                    Ngày Hội Tuyển Thành Viên Các CLB &amp; Đội Nhóm Sinh Viên
                  </h3>
                  <p class="event-meta">Thời gian: 12/09/2026, 07:30 - 17:00</p>
                  <p class="event-meta">Địa điểm: Sân trường ĐH Sư Phạm</p>
                  <p class="event-meta">Quyền lợi: +3 Điểm rèn luyện</p>
                  <div class="the-tin__actions mt-3">
                    <button
                      type="button"
                      class="nut-bam nut-nhan nut-nho nut-xem-chi-tiet"
                      data-id="7"
                      aria-haspopup="dialog"
                    >
                      Xem chi tiết sự kiện
                    </button>
                    <button
                      type="button"
                      class="nut-yeu-thich"
                      data-id="7"
                      aria-pressed="false"
                      aria-label="Lưu tin - Lưu sự kiện"
                    >
                      Lưu tin
                    </button>
                  </div>
                </div>
              </article>
            </li>
          </ul>
        </section>

        <!-- Phân trang ngữ nghĩa -->
        <nav aria-label="Phân trang danh sách sự kiện" class="mt-5">
          <div class="phan-trang">
            <a href="danh-sach.html" class="dieu-huong"
              ><span aria-hidden="true">&laquo;</span> Trước</a
            >
            <a href="danh-sach.html" class="dang-active" aria-current="page"
              >1</a
            >
            <a href="danh-sach.html">2</a>
            <a href="danh-sach.html">3</a>
            <a href="danh-sach.html" class="dieu-huong"
              >Sau <span aria-hidden="true">&raquo;</span></a
            >
          </div>
        </nav>
      </div>
    </main>

    <!-- =========================================================
         CỬA SỔ CHI TIẾT SỰ KIỆN (MODAL / POPUP)
         ========================================================= -->
    <div
      id="modal-chi-tiet-su-kien"
      class="modal-overlay"
      role="dialog"
      aria-modal="true"
      aria-labelledby="modal-tieu-de"
      tabindex="-1"
      hidden
    >
      <div class="modal-hop-thoai" role="document">
        <!-- Nút đóng ở góc trên bên phải -->
        <button
          type="button"
          class="modal-nut-dong"
          id="modal-nut-dong"
          aria-label="Đóng cửa sổ chi tiết sự kiện"
        >
          <span aria-hidden="true">&times;</span>
        </button>

        <!-- Khung nội dung cuộn bên trong nếu thông tin dài -->
        <div class="modal-noi-dung" id="modal-noi-dung">
          <!-- Ảnh banner / poster sự kiện -->
          <div class="modal-banner-wrapper">
            <span class="badge" id="modal-badge">Hội thảo</span>
            <img
              id="modal-anh"
              src="images/AI.jpg"
              alt="Hình ảnh sự kiện"
              width="720"
              height="360"
              loading="lazy"
            />
          </div>

          <!-- Phần thân thông tin sự kiện -->
          <div class="modal-than">
            <h2 id="modal-tieu-de" class="modal-tieu-de">Tên sự kiện</h2>

            <!-- Thông số nhanh 2 cột -->
            <div class="modal-thong-tin-nhanh">
              <div class="modal-dong-meta">
                <div>
                  <strong>Thời gian:</strong>
                  <span id="modal-thoi-gian"></span>
                </div>
              </div>

              <div class="modal-dong-meta">
                <div>
                  <strong>Địa điểm:</strong>
                  <span id="modal-dia-diem"></span>
                </div>
              </div>

              <div class="modal-dong-meta">
                <div>
                  <strong>Diễn giả / Đơn vị:</strong>
                  <span id="modal-dien-gia"></span>
                </div>
              </div>

              <div class="modal-dong-meta">
                <div>
                  <strong>Quyền lợi:</strong>
                  <span id="modal-quyen-loi"></span>
                </div>
              </div>

              <div class="modal-dong-meta">
                <div>
                  <strong>Quy mô / Giá vé:</strong>
                  <span id="modal-quy-mo"></span>
                </div>
              </div>
            </div>

            <!-- Mô tả chi tiết sự kiện -->
            <div class="modal-phan-mo-ta">
              <h3 class="h4 mb-2">Giới thiệu chi tiết sự kiện</h3>
              <p id="modal-mo-ta" class="modal-mo-ta-text"></p>

              <div id="modal-khoi-noi-bat" class="modal-khoi-noi-bat">
                <h4 class="h5 mb-2">Các nội dung nổi bật:</h4>
                <ul
                  id="modal-danh-sach-noi-bat"
                  class="modal-danh-sach-noi-bat"
                ></ul>
              </div>
            </div>

            <!-- Chân modal chứa các nút hành động -->
            <div class="modal-chan">
              <button
                type="button"
                class="nut-bam nut-phu"
                id="modal-nut-yeu-thich"
              >
                Lưu vào sự kiện yêu thích
              </button>
              <a
                href="chi-tiet.html?id=1"
                class="nut-bam nut-nhan"
                id="modal-link-chi-tiet"
              >
                Xem chi tiết &amp; Đăng ký vé →
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer 4 cột chung -->
    <footer class="vung-chan">
      <div class="container footer-grid">
        <!-- Cột 1: Về UniEvent -->
        <div>
          <div class="footer-brand">
            <img
              src="images/logo.png"
              alt="Logo UniEvent Footer"
              class="logo-img"
              width="32"
              height="32"
            />
            <span>UniEvent</span>
          </div>
          <p>
            Nền tảng quản lý và kết nối sự kiện đại học toàn diện dành cho sinh
            viên và giảng viên Trường Đại học Sư phạm – Đại học Đà Nẵng.
          </p>
          <p class="mt-2">
            Địa chỉ: 459 Tôn Đức Thắng, Hòa Khánh Nam, Liên Chiểu, Đà Nẵng
          </p>
          <p>Email: support@unievent.edu.vn | Hotline: 0236 3841 323</p>
        </div>

        <!-- Cột 2: Điều hướng nhanh -->
        <div>
          <h3>Điều hướng nhanh</h3>
          <ul>
            <li><a href="index.html">Trang chủ</a></li>
            <li><a href="danh-sach.html">Danh sách sự kiện</a></li>
            <li><a href="gioi-thieu.html">Giới thiệu nhóm phát triển</a></li>
            <li><a href="lien-he.html">Liên hệ hỗ trợ</a></li>
          </ul>
        </div>

        <!-- Cột 3: Hỗ trợ sinh viên -->
        <div>
          <h3>Hỗ trợ sinh viên</h3>
          <ul>
            <li><a href="lien-he.html">Hướng dẫn đăng ký vé</a></li>
            <li>
              <a href="danh-sach.html">Quy định tích lũy điểm rèn luyện</a>
            </li>
            <li><a href="lien-he.html">Câu hỏi thường gặp (FAQ)</a></li>
            <li><a href="lien-he.html">Bảo mật thông tin sinh viên</a></li>
          </ul>
        </div>

        <!-- Cột 4: Mạng xã hội & Bản quyền -->
        <div>
          <h3>Kết nối mạng xã hội</h3>
          <p>
            Theo dõi các kênh thông tin chính thức của Đoàn - Hội trường ĐHSP:
          </p>
          <div class="mang-xa-hoi mt-3">
            <a
              href="https://www.facebook.com"
              target="_blank"
              rel="noopener noreferrer"
              aria-label="Facebook UniEvent"
              >f</a
            >
            <a
              href="https://www.youtube.com"
              target="_blank"
              rel="noopener noreferrer"
              aria-label="Kênh YouTube UniEvent"
              >▶</a
            >
            <a
              href="https://twitter.com"
              target="_blank"
              rel="noopener noreferrer"
              aria-label="Twitter UniEvent"
              >t</a
            >
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

    <!-- JavaScript ES6 Modules -->
    <script type="module" src="js/main.js"></script>
    <script type="module" src="js/trang-danh-sach.js"></script>
  </body>
</html>
