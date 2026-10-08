<!doctype html>
<html lang="vi">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Profile cá nhân - Nguyễn Thị Kiều Trang | UniEvent</title>
    <link rel="stylesheet" href="../../css/01-bien.css" />
    <link rel="stylesheet" href="css/canhan.css" />
  </head>
  <body>
    <header class="profile-header">
      <div class="container">
        <span class="profile-header__badge"
          >Thành viên Nhóm 14 &bull; UniEvent</span
        >
        <h1>Profile cá nhân</h1>
        <p>Chào mừng bạn đến trang giới thiệu của Nguyễn Thị Kiều Trang</p>
      </div>
    </header>

    <nav class="profile-nav" aria-label="Điều hướng trang cá nhân">
      <div class="container">
        <ul>
          <li><a href="#about">Giới thiệu</a></li>
          <li><a href="#projects">Dự án &amp; Sở thích</a></li>
          <li><a href="#skills">Kỹ năng</a></li>
          <li><a href="#schedule">Thời khóa biểu</a></li>
          <li><a href="#contact">Liên hệ</a></li>
        </ul>
      </div>
    </nav>

    <main class="profile-main container">
      <div class="profile-grid-top">
        <section
          id="about"
          class="profile-card about-section"
          aria-labelledby="about-heading"
        >
          <div class="avatar-wrapper">
            <img
              src="chandung.jpg"
              alt="Ảnh chân dung Nguyễn Thị Kiều Trang"
              width="180"
              height="180"
              loading="lazy"
            />
          </div>
          <div class="about-info">
            <h2 id="about-heading">Giới Thiệu Bản Thân</h2>
            <span class="meta-pill">MSSV: 3120224153 &bull; Nhóm 14</span>
            <p><strong>Họ và tên:</strong> Nguyễn Thị Kiều Trang</p>
            <p>
              <strong>Lớp:</strong> 24CNTT2 - Khoa Toán – Tin, Trường Đại học Sư
              phạm – Đại học Đà Nẵng
            </p>
            <p>
              <strong>Vai trò dự án:</strong> Phụ trách xây dựng trang Chi tiết (chi-tiet.php)
            </p>
          </div>
        </section>

        <article
          id="projects"
          class="profile-card"
          aria-labelledby="projects-heading"
        >
          <h2 id="projects-heading">Dự Án &amp; Sở Thích</h2>

          <!-- Bộ lọc dự án cá nhân theo tag (Tương tác Phần C) -->
          <div
            class="project-filter-group"
            role="group"
            aria-label="Bộ lọc dự án theo chuyên môn"
          >
            <button
              type="button"
              class="btn-tag active"
              data-tag="all"
              aria-pressed="true"
            >
              Tất cả
            </button>
            <button
              type="button"
              class="btn-tag"
              data-tag="web"
              aria-pressed="false"
            >
              Web Dev
            </button>
            <button
              type="button"
              class="btn-tag"
              data-tag="iot"
              aria-pressed="false"
            >
              IoT &amp; Nhúng
            </button>
            <button
              type="button"
              class="btn-tag"
              data-tag="ai"
              aria-pressed="false"
            >
              AI &amp; Data
            </button>
          </div>

          <!-- Khu vực aria-live để thông báo số lượng dự án sau lọc cho Accessibility -->
          <div id="filter-status" class="sr-only" aria-live="polite"></div>

          <div class="projects-list" id="projects-list">
            <div class="project-item" data-category="web">
              <div class="project-item__header">
                <strong>Dự án UniEvent</strong>
                <span class="project-tag">#Web Dev</span>
              </div>
              <p>
                Website quản lý và kết nối sự kiện trong trường đại học, thiết
                kế giao diện responsive chuẩn semantic HTML5/CSS3 và biểu mẫu
                liên hệ tương tác REST API.
              </p>
            </div>

            <div class="project-item" data-category="iot">
              <div class="project-item__header">
                <strong>Khóa cửa tự động RFID RC522</strong>
                <span class="project-tag">#IoT &amp; Nhúng</span>
              </div>
              <p>
                Mạch vi điều khiển Arduino Uno kết nối đầu đọc thẻ RFID RC522 và
                động cơ servo đóng mở chốt cửa an ninh tự động.
              </p>
            </div>

            <div class="project-item" data-category="iot">
              <div class="project-item__header">
                <strong>Hệ thống quan trắc Smart Home ESP8266</strong>
                <span class="project-tag">#IoT &amp; Nhúng</span>
              </div>
              <p>
                Thu thập nhiệt độ và độ ẩm qua cảm biến DHT11, đồng bộ dữ liệu
                và điều khiển thiết bị từ xa qua nền tảng đám mây Blynk IoT.
              </p>
            </div>

            <div class="project-item" data-category="ai">
              <div class="project-item__header">
                <strong>Phát hiện âm thanh giọng nói (VAD)</strong>
                <span class="project-tag">#AI &amp; Data</span>
              </div>
              <p>
                Ứng dụng mô hình học máy Scikit-Learn kết hợp trích xuất đặc trưng
                MFCC bằng Librosa để nhận dạng khoảng lặng và hoạt động tiếng nói.
              </p>
            </div>

            <div class="project-item" data-category="web">
              <div class="project-item__header">
                <strong>Quản lý điểm sinh viên Java Swing &amp; SQL Server</strong>
                <span class="project-tag">#Web Dev</span>
              </div>
              <p>
                Ứng dụng phần mềm quản lý học tập kết nối CSDL quan hệ chuẩn hóa
                qua JDBC, truy vấn thống kê dữ liệu trực quan.
              </p>
            </div>
          </div>
        </article>
      </div>

      <div class="profile-grid-mid">
        <section
          id="skills"
          class="profile-card"
          aria-labelledby="skills-heading"
        >
          <h2 id="skills-heading">Kỹ Năng Chuyên Môn</h2>
          <ul class="skills-list">
            <li>Ngôn ngữ lập trình: C++, Java, Python, C#</li>
            <li>Công nghệ web: HTML5, CSS3, PHP</li>
            <li>Cơ sở dữ liệu: SQL Server, MySQL</li>
            <li>Kỹ thuật phần cứng &amp; IoT: Arduino Uno, ESP8266 NodeMCU</li>
            <li>Công cụ: Visual Studio Code, Git/GitHub, LaTeX</li>
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
              <span>🎓</span>
              <div><strong>MSSV:</strong><br />3120224153</div>
            </li>
            <li>
              <span>🏫</span>
              <div><strong>Lớp:</strong><br />24CNTT2 - Khoa Toán – Tin</div>
            </li>
            <li>
              <span>💻</span>
              <div>
                <strong>Vai trò:</strong><br />Phụ trách trang Biểu mẫu liên hệ
              </div>
            </li>
          </ul>
        </aside>
      </div>

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

        <div
          class="bang-wrapper"
          tabindex="0"
          role="region"
          aria-label="Bảng thời khóa biểu học tập trong tuần"
        >
          <table class="schedule-table">
            <caption>
              Thời Khóa Biểu Học Tập Trong Tuần - Học kỳ 1 Năm học 2026-2027
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
              <tr>
                <th scope="row">Tiết 1</th>
                <td rowspan="6"></td>
                <td rowspan="2"></td>
                <td rowspan="3">
                  <strong>Hệ quản trị cơ sở dữ liệu</strong>31241283 -
                  24-0102<br /><span class="room-badge">Phòng: B3-402</span>
                </td>
                <td rowspan="3"></td>
                <td rowspan="6"></td>
                <td rowspan="3">
                  <strong>Thiết kế và lập trình web</strong>31231755 -
                  24-0102<br /><span class="room-badge">Phòng: B3-303</span>
                </td>
                <td rowspan="6"></td>
              </tr>
              <tr>
                <th scope="row">Tiết 2</th>
              </tr>
              <tr>
                <th scope="row">Tiết 3</th>
                <td rowspan="3">
                  <strong>Lịch sử Đảng Cộng sản Việt Nam</strong>21221904 -
                  24-0311<br /><span class="room-badge">Phòng: A6-502</span>
                </td>
              </tr>
              <tr>
                <th scope="row">Tiết 4</th>
                <td rowspan="3"></td>
                <td rowspan="3">
                  <strong>Khai phá dữ liệu</strong>31231330 - 24-0102<br /><span
                    class="room-badge"
                    >Phòng: A5-404B</span
                  >
                </td>
                <td rowspan="3"></td>
              </tr>
              <tr>
                <th scope="row">Tiết 5</th>
              </tr>
              <tr>
                <th scope="row">Tiết 6</th>
                <td></td>
              </tr>
              <tr>
                <th scope="row">Tiết 7</th>
                <td rowspan="2">
                  <strong>An toàn thông tin</strong>31221010 - 24-0102<br /><span
                    class="room-badge"
                    >Phòng: A6-401</span
                  >
                </td>
                <td rowspan="6"></td>
                <td rowspan="6"></td>
                <td rowspan="3"></td>
                <td rowspan="6"></td>
                <td rowspan="6"></td>
                <td rowspan="6"></td>
              </tr>
              <tr>
                <th scope="row">Tiết 8</th>
              </tr>
              <tr>
                <th scope="row">Tiết 9</th>
                <td rowspan="4"></td>
              </tr>
              <tr>
                <th scope="row">Tiết 10</th>
                <td rowspan="3">
                  <strong>Công nghệ phần mềm</strong>31231016 - 24-0103<br /><span
                    class="room-badge"
                    >Phòng: B3-304</span
                  >
                </td>
              </tr>
              <tr>
                <th scope="row">Tiết 11</th>
              </tr>
              <tr>
                <th scope="row">Tiết 12</th>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </main>

    <footer class="profile-footer">
      <div class="container">
        <a href="../../index.php" class="btn-back"
          >&larr; Quay lại Trang chủ UniEvent</a
        >
        <p>
          &copy; 2026 Nguyễn Thị Kiều Trang &bull; Nhóm 14 - UniEvent (ĐH Sư
          phạm - ĐHĐN)
        </p>
      </div>
    </footer>

    <!-- Nút Back-to-top (Tương tác Phần C) -->
    <button
      type="button"
      id="btn-back-to-top"
      class="btn-back-to-top"
      aria-label="Cuộn lên đầu trang"
    >
      <span aria-hidden="true">&uarr;</span>
    </button>

    <!-- Nạp mã JavaScript tương tác riêng cho trang cá nhân -->
    <script src="js/canhan.js"></script>
  </body>
</html>
