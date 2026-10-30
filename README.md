# UniEvent - Cổng Quản Lý & Đăng Ký Sự Kiện Sinh Viên

> **Học phần:** Thiết kế và Lập trình Web (Web Design and Programming)  
> **Khoa:** Toán – Tin, Trường Đại học Sư phạm – Đại học Đà Nẵng  
> **Học kỳ:** Học kỳ 1, Năm học 2026 – 2027  
> **BÀI TẬP THỰC HÀNH NHÓM SỐ 6:** Chương 6 – PHP và cơ sở dữ liệu MySQL  
> **Nhóm thực hiện:** Nhóm 14  
> **Kho lưu trữ GitHub:** [https://github.com/kchan-g/ltweb-doan-nhom14](https://github.com/kchan-g/ltweb-doan-nhom14)  
> **Thẻ git bản Bài 5:** `btn5`

---

## 👥 Danh Sách Thành Viên Nhóm 14 & Phân Công Nhiệm Vụ

| STT | Họ và tên | Mã sinh viên | Tài khoản GitHub | Nhiệm vụ chính Bài 6 | Bảng riêng Phần B | Đóng góp |
| :-: | --------- | :----------: | :--------------: | -------------------- | ----------------- | :------: |
| 1 | **Đinh Trịnh Ngọc Hưng** | `3120224065` | [@biidicodedao](https://github.com/biidicodedao) | **Nhóm trưởng**; Thiết kế CSDL, PDO Singleton `inc/db.php`, Sổ lưu bút phân trang & Xóa (kiểm tra quyền), ERD | `tv_luu_but`, `tv_luot_xem` | 20% |
| 2 | **Nguyễn Hoài Bảo** | `3120224011` | [@nguyenhoaibao282006-creator](https://github.com/nguyenhoaibao282006-creator) | RBAC `inc/xac-thuc.php`, Bảo vệ IDOR `don-hang.php`, Trang 403, 500 độc lập | `tv_tu_van` | 20% |
| 3 | **Lê Phú Đạt** | `3120224024` | [@phudat1304](https://github.com/phudat1304) | Giao dịch ACID `inc/don-hang.php`, Giỏ hàng Batch SQL, Máy tính điểm CSDL | `tv_diem_hoc_phan` | 20% |
| 4 | **Phan Nhuận** | `3120224106` | [@nhuan14](https://github.com/nhuan14) | Phân trang `danh-sach.php`, Lọc GET, Tìm kiếm an toàn SQL, Đánh giá CSDL | `tv_danh_gia` | 20% |
| 5 | **Nguyễn Thị Kiều Trang** | `3120224153` | [@kchan-g](https://github.com/kchan-g) | REST API `api/`, AJAX `js/`, Quản trị CRUD sự kiện, Dự toán web CSDL | `tv_du_toan_web`, `tv_ban_tin` | 20% |

---

## 💻 Yêu Cầu Môi Trường

- **PHP:** Phiên bản $\ge$ **8.1** (Bật các tiện ích mở rộng: `pdo_mysql`, `fileinfo`, `mbstring`, `json`, `gd`).
- **MySQL / MariaDB:** Phiên bản $\ge$ **8.0** hoặc MariaDB $\ge$ **10.4** (Hỗ trợ InnoDB, UTF-8 `utf8mb4`).
- **Composer:** Công cụ nạp tự động PSR-4 (`vendor/autoload.php`).
- **Môi trường chạy:**
  - Khuyến nghị: XAMPP / Laragon (Apache + MySQL cổng 3306).
  - Hoặc PHP Built-in Server (`php -S localhost:8000`) kết hợp máy chủ MySQL cục bộ.

---

## 🗄️ Hướng Dẫn Cài Đặt Cơ Sở Dữ Liệu (`ltweb_nhom14`)

CSDL của nhóm được thiết kế chuẩn 3NF, lưu trữ tại thư mục `database/`:
- `database/schema.sql`: DDL xóa và tạo lại CSDL `ltweb_nhom14`, cấu trúc 12 bảng, khóa chính, khóa ngoại, ràng buộc CHECK và chỉ mục.
- `database/seed.sql`: DML nạp dữ liệu mẫu gồm 32 sự kiện (phủ 4 danh mục + 1 danh mục rỗng để kiểm thử), 5 tài khoản mẫu, đơn hàng, phản hồi liên hệ và dữ liệu thành viên.
- `database/erd.png`: Sơ đồ quan hệ thực thể độ phân giải cao trực quan hóa toàn bộ 12 bảng.

### Cách 1: Sử dụng phpMyAdmin (XAMPP / Laragon)

1. Mở trình duyệt truy cập: `http://localhost/phpmyadmin/`.
2. Vào thẻ **Import** (Nhập).
3. Chọn tệp `database/schema.sql` và nhấn **Import** (tệp đã chứa lệnh `DROP DATABASE IF EXISTS ltweb_nhom14; CREATE DATABASE ...`).
4. Chọn CSDL `ltweb_nhom14` vừa tạo, tiếp tục chọn thẻ **Import**, nạp tệp `database/seed.sql` và nhấn **Import**.

### Cách 2: Sử dụng dòng lệnh (CLI / Terminal)

```bash
# 1. Nhập cấu trúc CSDL (schema)
mysql -u root -p < database/schema.sql

# 2. Nhập dữ liệu mẫu ban đầu (seed)
mysql -u root -p ltweb_nhom14 < database/seed.sql
```

---

## 🚀 Hướng Dẫn Khởi Động Website

### Chạy bằng PHP Built-in Server
1. Mở PowerShell / Terminal tại thư mục `ltweb-doan-nhom14/`.
2. Khởi tạo nạp tự động Composer:
   ```bash
   composer dump-autoload
   ```
3. Khởi chạy máy chủ PHP:
   ```bash
   php -S localhost:8000
   ```
4. Truy cập website: `http://localhost:8000/`.

---

## 🔑 Tài Khoản Thử Nghiệm

Mật khẩu chung cho tất cả các tài khoản thử nghiệm là: `matkhau123` (băm an toàn bằng `password_hash()` với thuật toán `PASSWORD_BCRYPT`).

| Email đăng nhập | Họ và tên | Vai trò | Mục đích kiểm thử theo kịch bản |
| --------------- | --------- | :-----: | -------------------------------- |
| `admin@ued.vn` | Quản trị viên UniEvent | `quan_tri` | Toàn quyền quản trị; thêm/sửa/xóa sự kiện, xem danh sách đơn hàng, quản lý liên hệ |
| `lan@ued.vn` | Nguyễn Thị Lan | `khach_hang` | Tài khoản sinh viên đã có đơn hàng mẫu `DH2026101401` để kiểm tra lịch sử đặt vé |
| `an@ued.vn` | Trần Văn An | `khach_hang` | Tài khoản sinh viên mới; kiểm tra đặt vé trong giao dịch ACID, giỏ hàng |
| `hung@ued.vn` | Đinh Trịnh Ngọc Hưng | `quan_tri` | Tài khoản quản trị của chủ trang Hưng (MSSV: `3120224065`); thấy nút xóa lưu bút |
| `3120224065@ued.udn.vn` | Đinh Trịnh Ngọc Hưng | `khach_hang` | Tài khoản sinh viên liên kết chủ trang `thanh_vien`; có quyền xóa lời nhắn lưu bút |

---

## 📋 Danh Mục URL & Các Tệp Xử Lý

### 1. Website chính của đồ án (Phần A)

| Tên trang / Chức năng | Đường dẫn URL | Tệp xử lý chính | Đặc điểm kỹ thuật nổi bật |
| --------------------- | ------------- | --------------- | ------------------------- |
| **Trang chủ** | `/index.php` | `index.php` | Sự kiện mới nhất, Đã xem gần đây (Cookie `da_xem` truy vấn batch `layTheoIds()` tránh N+1) |
| **Danh sách sự kiện** | `/danh-sach.php` | `danh-sach.php`<br>`src/Data/KhoSuKien.php` | Phân trang qua `COUNT(*)`, `LIMIT ? OFFSET ?`, tìm kiếm an toàn từ khóa, lọc danh mục, sắp xếp whitelist |
| **Chi tiết sự kiện** | `/chi-tiet.php?id=1` | `chi-tiet.php` | Prepared statement `layTheoId()`, sự kiện cùng danh mục `layCungDanhMuc()`, nhãn "Hết vé" khi `ton_kho = 0` |
| **Giỏ hàng** | `/gio-hang.php` | `gio-hang.php`<br>`src/Services/GioHang.php` | Đọc dữ liệu sự kiện theo batch SQL `WHERE id IN (...)` thay vì lặp từng ID; tính tổng tiền |
| **Đặt hàng (Giao dịch)**| `/dat-hang.php` | `dat-hang.php`<br>`inc/don-hang.php` | Giao dịch ACID (`beginTransaction`, `commit`, `rollBack`), trừ tồn kho nguyên tử `WHERE ton_kho >= ?` |
| **Lịch sử đơn hàng** | `/don-hang.php`<br>`/don-hang.php?id=1` | `don-hang.php` | Chống IDOR: người dùng chỉ xem đơn của mình (truy cập đơn người khác trả 404); Admin xem toàn bộ |
| **Biểu mẫu liên hệ** | `/lien-he.php` | `lien-he.php`<br>`src/Data/KhoLienHe.php` | Lưu dữ liệu vào bảng `lien_he`, kiểm tra MIME thật `finfo`, tải lên ảnh an toàn, PRG pattern |
| **Đăng ký tài khoản** | `/dang-ky.php` | `dang-ky.php` | Băm mật khẩu `password_hash()`, bắt lỗi trùng email MySQL (mã 1062), gán vai trò mặc định `khach_hang` |
| **Đăng nhập hệ thống** | `/dang-nhap.php` | `dang-nhap.php`<br>`inc/xac-thuc.php` | `password_verify()`, chống Session Fixation (`session_regenerate_id(true)`), chỉ lưu `$_SESSION['uid']` |
| **Đăng xuất** | `/dang-xuat.php` | `dang-xuat.php` | Hủy session và cookie phiên an toàn, chuyển hướng PRG |
| **Trang quản trị** | `/quan-tri.php` | `quan-tri.php` | Phân quyền `yeuCauQuyen('quan_tri')`, quản lý danh sách sự kiện phân trang và phản hồi liên hệ |
| **Thêm sự kiện** | `/them-san-pham.php` | `them-san-pham.php`<br>`inc/kiem-tra.php` | Hàm kiểm tra hợp lệ dùng chung `kiemTraSanPham()`, upload ảnh, INSERT CSDL lấy `lastInsertId` |
| **Sửa sự kiện** | `/sua-san-pham.php?id=1` | `sua-san-pham.php` | Điền sẵn dữ liệu cũ, kiểm tra hợp lệ, UPDATE CSDL an toàn |
| **Xóa sự kiện** | `/xoa-san-pham.php` | `xoa-san-pham.php` | Chỉ nhận POST (GET trả 405), bắt lỗi ràng buộc khóa ngoại (mã 1451) khi sự kiện đã có trong đơn hàng |
| **Trang lỗi 403** | `/403.php` | `403.php` | Trả mã HTTP 403 Forbidden thân thiện |
| **Trang lỗi 404** | `/404.php` | `404.php` | Trả mã HTTP 404 Not Found thân thiện |
| **Trang lỗi 500** | `/500.php` | `500.php` | Độc lập 100%, không truy vấn CSDL, không gọi session khi máy chủ gặp sự cố |

---

### 2. REST API & Tương Tác JavaScript (Chức năng 6)

Hệ thống cung cấp 2 REST API trả JSON đúng chuẩn RESTful, có xử lý lỗi 400, 404, 405, 422, 500:

| Endpoint API | Phương thức | Tham số / Body | Mã trạng thái HTTP | Mô tả chức năng & Client JS |
| ------------ | :---------: | -------------- | :----------------: | --------------------------- |
| `/api/san-pham.php` | `GET` | `?q=tu-khoa` hoặc `?id=1` | 200, 400, 404, 405, 500 | Tìm kiếm sự kiện theo từ khóa hoặc lấy chi tiết 1 sự kiện. Client: `js/tim-kiem.js` (Debounce 300ms, gợi ý tức thì, render DOM bằng `textContent` chống XSS) |
| `/api/gio-hang.php` | `GET` | _Không có_ | 200, 500 | Lấy danh sách giỏ hàng và tổng số lượng vé hiện tại |
| `/api/gio-hang.php` | `POST` | `{"id": 1, "sl": 2}` | 200, 400, 404, 422, 405 | Thêm vé vào giỏ hàng qua AJAX. Client: `js/gio-hang.js` (cập nhật badge `#dem-gio-hang` không tải lại trang + Toast thông báo) |

> **Kiểm thử API:** Mở tệp `api.http` trong VS Code (REST Client Extension) hoặc chạy `curl` theo các kịch bản mẫu từ ca 1 đến ca 12 đã soạn sẵn.

---

### 3. Trang Cá Nhân Của Từng Thành Viên & Bảng CSDL Riêng (Phần B)

| Thành viên | MSSV | Đường dẫn trang cá nhân | Bảng CSDL riêng | Hai chức năng CSDL máy chủ |
| ---------- | :--: | ----------------------- | :-------------: | -------------------------- |
| **Đinh Trịnh Ngọc Hưng** | `3120224065` | `thanhvien/3120224065_Hung/gioithieu.php` | `tv_luu_but`<br>`tv_luot_xem` | 1. Sổ lưu bút CSDL `tv_luu_but`: Prepared statement, phân trang 5 lời nhắn/trang, chỉ chủ trang đăng nhập mới thấy nút xóa lời nhắn (kiểm tra quyền máy chủ).<br>2. Bộ đếm lượt xem theo ngày `tv_luot_xem` (`INSERT ... ON DUPLICATE KEY UPDATE`) kèm bảng thống kê 7 ngày gần nhất. |
| **Lê Phú Đạt** | `3120224024` | `thanhvien/3120224024_Dat/gioithieu.php` | `tv_diem_hoc_phan` | 1. Máy tính điểm học phần theo tín chỉ: lưu lịch sử tính vào bảng `tv_diem_hoc_phan`.<br>2. Hiển thị bảng lịch sử 5 lần tính điểm gần nhất từ MySQL. |
| **Phan Nhuận** | `3120224106` | `thanhvien/3120224106_Nhuan/gioithieu.php` | `tv_danh_gia` | 1. Biểu mẫu đánh giá năng lực chuyên môn lưu vào bảng `tv_danh_gia`.<br>2. Hiển thị danh sách phản hồi đánh giá gần nhất từ CSDL. |
| **Nguyễn Thị Kiều Trang** | `3120224153` | `thanhvien/3120224153_Trang/gioithieu.php` | `tv_du_toan_web` | 1. Công cụ dự toán chi phí & tiến độ website: lưu bảng `tv_du_toan_web`.<br>2. Bảng thống kê các dự toán chi phí website gần đây từ CSDL. |
| **Nguyễn Hoài Bảo** | `3120224011` | `thanhvien/3120224011_Bao/gioithieu.php` | `tv_luot_xem` | 1. Bộ đếm lượt truy cập trang cá nhân lưu trữ bền vững trong bảng `tv_luot_xem`.<br>2. Lọc danh mục hồ sơ kỹ năng và kết nối lịch hẹn tư vấn. |

---

## 🛡️ Điểm Sáng Kỹ Thuật & Bảo Mật (Rubric Bài 6)

1. **Phòng chống SQL Injection tuyệt đối:**  
   100% truy vấn có tham số người dùng đều sử dụng PDO Prepared Statement (`prepare()` + `execute()` / `bindValue()`). Sắp xếp và phân trang dùng Whitelist an toàn.
2. **Giao dịch ACID trong đặt hàng (`luuDonHang`):**  
   Bao bọc trong `beginTransaction()`, `commit()`, `rollBack()`. Trừ tồn kho nguyên tử `UPDATE su_kien SET ton_kho = ton_kho - :sl WHERE id = :id AND ton_kho >= :sl` kết hợp kiểm tra `rowCount() === 1` chống bán vượt tồn kho (race condition).
3. **Phòng chống tấn công IDOR (Insecure Direct Object Reference):**  
   Tại `don-hang.php?id=X`, khách hàng chỉ xem được đơn hàng của chính tài khoản mình (`AND nguoi_dung_id = ?`). Truy cập đơn của người khác nhận mã lỗi 404 (thay vì 403 để tránh rò rỉ sự tồn tại của đơn hàng).
4. **Phòng chống N+1 Query:**  
   Khối "Đã xem gần đây" ở trang chủ và giỏ hàng gom tất cả ID vào một câu truy vấn duy nhất `layTheoIds()` dạng `WHERE id IN (?, ?, ...)` thay vì vòng lặp truy vấn.
5. **Phiên làm việc & Mật khẩu chuẩn:**  
   Chỉ lưu `$_SESSION['uid']`, nạp thông tin người dùng qua hàm `nguoiDung()` có cơ chế cache trong cùng một request. Gọi `session_regenerate_id(true)` khi đăng nhập để chống Session Fixation.
6. **0 lỗi W3C Markup Validator & Thân thiện tiếp cận (a11y):**  
   Toàn bộ mã HTML sinh ra tuân thủ chuẩn W3C, hỗ trợ nhãn `aria-*`, cấu trúc ngữ nghĩa rõ ràng và tương thích thiết bị di động.
