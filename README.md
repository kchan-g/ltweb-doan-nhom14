# UniEvent - Cổng Quản Lý & Đăng Ký Sự Kiện Sinh Viên

> **Học phần:** Thiết kế và Lập trình Web  
> **Khoa:** Toán – Tin, Trường Đại học Sư phạm – Đại học Đà Nẵng  
> **Bài tập:** Bài thực hành nhóm số 5 – Lập trình PHP phía máy chủ  
> **Nhóm thực hiện:** Nhóm 14  
> **Kho lưu trữ GitHub:** [https://github.com/kchan-g/ltweb-doan-nhom14](https://github.com/kchan-g/ltweb-doan-nhom14)  
> **Thẻ git bản tĩnh Bài 4:** `btn4`

---

## 👥 Danh Sách Thành Viên Nhóm 14

| STT | Họ và tên | Mã sinh viên | Tài khoản GitHub | Vai trò & Nhiệm vụ chính | Đóng góp |
|:---:|---|:---:|:---:|---|:---:|
| 1 | **Đinh Trịnh Ngọc Hưng** | `3120224065` | [@biidicodedao](https://github.com/biidicodedao) | **Nhóm trưởng**; Khung trang dùng chung, Cookie `da_xem`, Báo cáo | 20% |
| 2 | **Nguyễn Hoài Bảo** | `3120224011` | [@nguyenhoaibao282006-creator](https://github.com/nguyenhoaibao282006-creator) | Thành viên; Khung header, footer, 404, 500, Kiểm thử bảo mật | 20% |
| 3 | **Lê Phú Đạt** | `3120224024` | [@phudat1304](https://github.com/phudat1304) | Thành viên; Biểu mẫu liên hệ, Upload ảnh an toàn, Giỏ hàng Session | 20% |
| 4 | **Phan Nhuận** | `3120224106` | [@nhuan14](https://github.com/nhuan14) | Thành viên; Danh sách sự kiện, Tìm kiếm từ khóa, Lọc & Sắp xếp GET | 20% |
| 5 | **Nguyễn Thị Kiều Trang** | `3120224153` | [@kchan-g](https://github.com/kchan-g) | Thành viên; Đăng nhập, Đăng xuất, Phân quyền Trang quản trị Admin | 20% |

---

## 💻 Yêu Cầu Môi Trường

* **PHP:** Phiên bản $\ge$ **8.1** (Bật các tiện ích mở rộng: `ext-fileinfo`, `ext-mbstring`, `ext-json`).
* **Composer:** Công cụ quản lý phụ thuộc và tự động nạp lớp (Autoload PSR-4).
* **Trình duyệt khuyến nghị:** Google Chrome, Mozilla Firefox, Microsoft Edge (hỗ trợ DevTools, Lighthouse).
* **Môi trường chạy web:**
  * Cách khuyến nghị: PHP Built-in Server (`php -S localhost:8000`).
  * Hoặc máy chủ cục bộ: XAMPP, Laragon, WampServer.

---

## 🚀 Hướng Dẫn Cài Đặt & Chạy Website

### Cách 1: Sử dụng PHP Built-in Server (Khuyên dùng)

1. Mở Terminal / PowerShell tại thư mục gốc của dự án (`ltweb-doan-nhom14/`).
2. Cài đặt các gói phụ thuộc và sinh tệp nạp tự động PSR-4:
   ```bash
   composer install
   ```
   *(hoặc nếu đã có sẵn chỉ cần cập nhật bộ nạp: `composer dump-autoload`)*
3. Khởi động máy chủ web tích hợp của PHP tại cổng `8000`:
   ```bash
   php -S localhost:8000
   ```
4. Truy cập website trên trình duyệt:
   ```text
   http://localhost:8000/
   ```

### Cách 2: Triển khai trên XAMPP / Laragon

1. Chép toàn bộ thư mục `ltweb-doan-nhom14` vào thư mục `htdocs` của XAMPP (ví dụ: `C:\xampp\htdocs\ltweb-doan-nhom14`).
2. Chạy lệnh `composer install` bên trong thư mục dự án.
3. Bật máy chủ **Apache** trong bảng điều khiển XAMPP Control Panel.
4. Mở trình duyệt và truy cập theo đường dẫn:
   ```text
   http://localhost/ltweb-doan-nhom14/
   ```

---

## 🔑 Tài Khoản Thử Nghiệm (inc/tai-khoan.php)

Hệ thống lưu mật khẩu dạng băm an toàn bằng `password_hash()` theo thuật toán mặc định `PASSWORD_DEFAULT`.

| Loại tài khoản | Tên đăng nhập (Username / Email) | Mật khẩu | Quyền hạn & Chuyển hướng |
|---|---|:---:|---|
| **Quản trị viên (Admin)** | `admin` *(hoặc `hung`)* | `123456` | Toàn quyền; tự động vào trang Quản trị (`quan-tri.php`) xem danh sách liên hệ & tệp đính kèm |
| **Sinh viên (User)** | `3120224065@ued.udn.vn` | `123456` | Tài khoản sinh viên Khoa Toán - Tin; xem và đăng ký vé sự kiện |
| **Sinh viên thử nghiệm** | `sinhvien@ued.udn.vn` | `123456` | Tài khoản sinh viên Khoa KHTN |

---

## 📋 Bảng Chức Năng – URL – Tệp PHP

### 1. Sáu chức năng bắt buộc đồ án (Phần A)

| STT | Tên chức năng | Đường dẫn URL | Tệp PHP xử lý | Người phụ trách |
|:---:|---|---|---|:---:|
| **1** | Khung trang dùng chung, Menu điều hướng, Trang lỗi thân thiện 404 & 500 | `http://localhost:8000/404.php`<br>`http://localhost:8000/500.php` | `inc/config.php`<br>`inc/header.php`<br>`inc/footer.php`<br>`404.php`, `500.php` | Bảo & Hưng |
| **2** | Danh sách sự kiện động từ JSON, Tìm kiếm từ khóa, Lọc danh mục, Sắp xếp tiêu chí qua GET | `http://localhost:8000/danh-sach.php` | `danh-sach.php`<br>`src/Data/KhoSuKien.php`<br>`src/Models/SuKien.php` | Nhuận |
| **3** | Chi tiết sự kiện (`?id=`), Thêm vào giỏ vé, Ghi nhận Cookie an toàn nhớ 4 mục xem gần nhất | `http://localhost:8000/chi-tiet.php?id=1`<br>`http://localhost:8000/index.php` | `chi-tiet.php`<br>`index.php`<br>`src/Models/SuKien.php` | Trang & Hưng |
| **4** | Biểu mẫu liên hệ: Kiểm tra toàn bộ tại máy chủ, Giữ lại dữ liệu, Upload ảnh an toàn (finfo MIME), Ghi JSON Lines, Mô hình PRG | `http://localhost:8000/lien-he.php` | `lien-he.php`<br>`src/Data/KhoLienHe.php` | Đạt |
| **5** | Quản lý giỏ vé qua Session: Thêm vé, Sửa số lượng, Hủy vé, Xóa toàn bộ, Tính tổng điểm rèn luyện | `http://localhost:8000/gio-hang.php` | `gio-hang.php`<br>`src/Services/GioHang.php` | Đạt |
| **6** | Đăng nhập đơn giản, Quản lý phiên Session, Đăng xuất, Bảo vệ trang quản trị liệt kê liên hệ kèm ảnh | `http://localhost:8000/dang-nhap.php`<br>`http://localhost:8000/quan-tri.php`<br>`http://localhost:8000/dang-xuat.php` | `dang-nhap.php`<br>`quan-tri.php`<br>`dang-xuat.php`<br>`inc/bao-ve.php` | Trang |

### 2. Trang cá nhân và 2 chức năng máy chủ của từng thành viên (Phần B)

| Thành viên | Đường dẫn trang cá nhân | Hai chức năng xử lý phía máy chủ (Server-side) |
|---|---|---|
| **Đinh Trịnh Ngọc Hưng** | `http://localhost:8000/thanhvien/3120224065_Hung/gioithieu.php` | 1. Bộ đếm lượt xem trang cá nhân (mỗi phiên tính 1 lần, lưu `storage/3120224065_views.txt`)<br>2. Sổ lưu bút sinh viên (POST $\rightarrow$ Validate server $\rightarrow$ Lưu JSONL $\rightarrow$ PRG $\rightarrow$ Hiện 5 lời nhắn mới nhất) |
| **Nguyễn Hoài Bảo** | `http://localhost:8000/thanhvien/3120224011_Bao/gioithieu.php` | 1. Lọc danh mục hồ sơ kỹ năng qua GET `?nhom=`<br>2. Đặt lịch tư vấn học tập & kết nối dự án (Validate server $\rightarrow$ Lưu `storage/3120224011_lienhe.jsonl` $\rightarrow$ PRG) |
| **Phan Nhuận** | `http://localhost:8000/thanhvien/3120224106_Nhuan/gioithieu.php` | 1. Khảo sát & Đánh giá năng lực chuyên môn (Lưu `storage/3120224106_danhgia.jsonl` $\rightarrow$ PRG)<br>2. Bài trắc nghiệm kiến thức Web tự động chấm điểm phía máy chủ |
| **Lê Phú Đạt** | `http://localhost:8000/thanhvien/3120224024_Dat/gioithieu.php` | 1. Công cụ tính điểm học phần tích lũy theo công thức tín chỉ ($0.2 \times A1 + 0.3 \times A2 + 0.5 \times A3$)<br>2. Tải lên ảnh đại diện cá nhân an toàn (Kiểm tra MIME qua finfo $\rightarrow$ Đổi tên ngẫu nhiên $\rightarrow$ PRG) |
| **Nguyễn Thị Kiều Trang** | `http://localhost:8000/thanhvien/3120224153_Trang/gioithieu.php` | 1. Đăng ký nhận bản tin công nghệ & đồ án (Validate server $\rightarrow$ Lưu `storage/3120224153_bantin.jsonl` $\rightarrow$ PRG)<br>2. Công cụ dự toán chi phí & tiến độ phát triển website tự động |

---

## 🗂️ Cấu Trúc Thư Mục Dự Án

```text
ltweb-doan-nhom14/
├── index.php                      # Trang chủ (Hiện SP mới & Đã xem gần đây từ Cookie)
├── danh-sach.php                  # Danh sách sự kiện (Lọc danh mục, tìm kiếm, sắp xếp GET)
├── chi-tiet.php                   # Chi tiết sự kiện & Ghi nhận Cookie da_xem
├── gio-hang.php                   # Quản lý giỏ vé Session (Thêm, cập nhật, xóa, tổng điểm)
├── lien-he.php                    # Biểu mẫu liên hệ & Upload ảnh an toàn
├── dang-nhap.php                  # Đăng nhập phân quyền (Admin & Sinh viên)
├── dang-xuat.php                  # Đăng xuất phiên làm việc
├── quan-tri.php                   # Trang quản trị danh sách phản hồi liên hệ (Cần đăng nhập)
├── gioi-thieu.php                 # Trang giới thiệu chung về hệ thống UniEvent
├── 404.php                        # Giao diện thông báo lỗi 404 Not Found thân thiện
├── 500.php                        # Giao diện thông báo lỗi 500 Internal Server Error an toàn
├── composer.json                  # Cấu hình nạp tự động Composer PSR-4 (App\ -> src/)
├── composer.lock                  # Khóa phiên bản phụ thuộc
├── .gitignore                     # Bỏ qua vendor, logs rác, uploads và cache runtime
├── README.md                      # Tài liệu tổng quan dự án và hướng dẫn chạy
│
├── inc/                           # Các thành phần dùng chung phía máy chủ
│   ├── config.php                 # Cấu hình trung tâm: Autoload, Session, Error Log, Chặn Exception
│   ├── ham.php                    # Các hàm tiện ích dùng chung: e() chống XSS, vnd(), flash()
│   ├── header.php                 # Khung đầu trang, thanh điều hướng aria-current, đếm giỏ vé
│   ├── footer.php                 # Khung chân trang và nhúng script dùng chung
│   ├── bao-ve.php                 # Lớp bảo vệ phiên làm việc (Middleware kiểm tra đăng nhập)
│   └── tai-khoan.php              # Khai báo danh sách tài khoản thử nghiệm mật khẩu băm
│
├── src/                           # Các lớp hướng đối tượng (OOP PSR-4: namespace App\)
│   ├── Models/
│   │   ├── SuKien.php             # Lớp thực thể Sự Kiện (thuộc tính readonly, hàm tuMang)
│   │   └── SanPham.php            # Lớp bí danh kế thừa SuKien đảm bảo tương thích 100% đề bài
│   ├── Data/
│   │   ├── KhoSuKien.php          # Tầng Data Access Layer (DAL) đọc và truy vấn data/su-kien.json
│   │   ├── KhoSanPham.php         # Lớp bí danh kế thừa KhoSuKien
│   │   └── KhoLienHe.php          # Lớp đọc và ghi nối tiếp tệp storage/lien-he.jsonl (LOCK_EX)
│   └── Services/
│       └── GioHang.php            # Lớp nghiệp vụ quản lý giỏ vé bao đóng trên $_SESSION['gio']
│
├── data/                          # Cơ sở dữ liệu tĩnh gốc
│   └── su-kien.json               # Dữ liệu nguồn 16 sự kiện sinh viên
│
├── storage/                       # Dữ liệu động phát sinh trong quá trình vận hành
│   ├── .htaccess                  # Chặn truy cập trực tiếp từ trình duyệt (Require all denied)
│   └── lien-he.jsonl              # Tệp lưu trữ danh sách phản hồi liên hệ (JSON Lines)
│
├── logs/                          # Nhật ký ghi lỗi hệ thống
│   ├── .htaccess                  # Chặn truy cập web vào thư mục log
│   └── php-error.log              # Nhật ký ghi lỗi runtime và cảnh báo đăng nhập trái phép
│
├── uploads/                       # Thư mục lưu ảnh người dùng tải lên an toàn (tên băm ngẫu nhiên)
│   └── .gitkeep                   # Giữ thư mục trên kho Git
│
├── css/                           # Hệ thống stylesheet thiết kế phân tầng BEM & CSS Variables
├── js/                            # Mã nguồn JavaScript tương tác client (menu mobile, validation)
├── kiemtra/                       # Ảnh chụp minh chứng kiểm thử chức năng và báo cáo
└── thanhvien/                     # Trang cá nhân của 5 thành viên nhóm 14
    ├── 3120224011_Bao/            # Trang cá nhân bạn Nguyễn Hoài Bảo
    ├── 3120224024_Dat/            # Trang cá nhân bạn Lê Phú Đạt
    ├── 3120224065_Hung/           # Trang cá nhân bạn Đinh Trịnh Ngọc Hưng
    ├── 3120224106_Nhuan/          # Trang cá nhân bạn Phan Nhuận
    └── 3120224153_Trang/          # Trang cá nhân bạn Nguyễn Thị Kiều Trang
```

---

## 🛡️ Tiêu Chí Kỹ Thuật & Bảo Mật Nổi Bật (Rubric R1.2)

1. **Tuân thủ nguyên tắc DRY (Don't Repeat Yourself):**  
   Toàn bộ Header, Navigation và Footer đều được đóng gói thành các mô-đun dùng chung trong `inc/`, nạp qua `require` với đường dẫn tuyệt đối `__DIR__` và biến định tuyến `$goc` linh hoạt.
2. **Kiến trúc Data Access Layer (DAL) chuẩn bị cho MySQL:**  
   Mọi thao tác đọc/truy vấn dữ liệu đều thông qua `KhoSuKien` và `KhoLienHe`. Ở Chương 6 khi chuyển sang MySQL/PDO, nhóm chỉ cần viết lại 2 tệp lớp này mà không cần sửa bất kỳ dòng mã nào trên 8 trang giao diện.
3. **Phòng chống tấn công XSS (Cross-Site Scripting):**  
   Mọi giá trị đầu ra in ra HTML đều bắt buộc đi qua hàm `e()` (sử dụng `htmlspecialchars(..., ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')`).
4. **Mô hình Post/Redirect/Get (PRG) & Flash Session:**  
   Biểu mẫu gửi liên hệ và thao tác giỏ hàng áp dụng 100% mô hình PRG. Khi người dùng bấm F5 (Reload), trình duyệt không bao giờ bị hỏi gửi lại dữ liệu (chống nhân đôi đơn/thư rác).
5. **Tải lên tệp an toàn (Secure File Upload):**  
   Không tin tưởng phần mở rộng client gửi lên; sử dụng `finfo(FILEINFO_MIME_TYPE)` đọc dữ liệu nhị phân thực tế; giới hạn dung lượng $\le 2\text{ MB}$; đổi tên tệp ngẫu nhiên 32 ký tự bằng `bin2hex(random_bytes(16))` chống tấn công thực thi mã độc.
6. **Bảo mật thư mục nội bộ:**  
   Thư mục `storage/` và `logs/` được bảo vệ tuyệt đối bằng tệp `.htaccess` chứa lệnh `Require all denied`, ngăn chặn việc truy cập hoặc tải về tệp nhạy cảm qua thanh địa chỉ URL.
7. **Khả năng hoạt động khi tắt JavaScript:**  
   Tất cả 6 chức năng chính (tìm kiếm, lọc, thêm giỏ, gửi liên hệ, đăng nhập, phân trang) đều vận hành hoàn hảo 100% bằng xử lý máy chủ PHP thuần ngay cả khi tắt JavaScript trên trình duyệt.
8. **Đạt chuẩn W3C & Lighthouse Accessibility:**  
   Mã nguồn HTML do PHP sinh ra đạt **0 lỗi W3C Markup Validator**, không cuộn ngang trên màn hình 360px và đạt điểm kiểm định trợ năng **Lighthouse Accessibility $\ge 96/100$** trên thiết bị di động.

