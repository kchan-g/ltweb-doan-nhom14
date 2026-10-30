-- ============================================================================
-- database/seed.sql
-- Dữ liệu khởi tạo mẫu cho website UniEvent (Nhóm 14)
-- Học phần: Thiết kế và Lập trình Web - Học kỳ 1, Năm học 2026 - 2027
-- CSDL: ltweb_nhom14 | Bảng mã: utf8mb4 | Collation: utf8mb4_unicode_ci
-- Mật khẩu chung của mọi tài khoản thử: matkhau123
-- ============================================================================

SET NAMES utf8mb4;
USE ltweb_nhom14;

-- ----------------------------------------------------------------------------
-- 1. NẠP DANH MỤC SỰ KIỆN (danh_muc)
-- 5 danh mục: Trong đó danh mục ID 5 (tinh-nguyen) cố ý CHƯA có sự kiện nào
-- để kiểm thử ca lọc danh mục rỗng theo đúng yêu cầu đề bài.
-- ----------------------------------------------------------------------------
INSERT INTO danh_muc (id, ten, slug, mo_ta) VALUES
(1, 'Hội thảo khoa học & Công nghệ', 'hoi-thao', 'Các buổi tọa đàm, hội thảo học thuật, công nghệ thông tin và AI'),
(2, 'Cơ hội việc làm & Tuyển dụng', 'viec-lam', 'Ngày hội việc làm, kết nối doanh nghiệp và định hướng kỹ năng nghề nghiệp'),
(3, 'Văn hóa & Nghệ thuật', 'van-hoa', 'Các đêm nhạc hội, biểu diễn nghệ thuật, giao lưu văn hóa sinh viên'),
(4, 'Thể thao & Rèn luyện', 'the-thao', 'Các giải đấu bóng đá, cầu lông, điền kinh và hội thao cấp trường'),
(5, 'Câu lạc bộ & Tình nguyện', 'tinh-nguyen', 'Các hoạt động phong trào tình nguyện hè, chiến dịch tình nguyện Mùa hè xanh');

-- ----------------------------------------------------------------------------
-- 2. NẠP NGƯỜI DÙNG (nguoi_dung)
-- Mật khẩu băm chuẩn: $2y$10$6RSEuRESwpYOryF5Q9CEBuqDp5cGrPsEz/hD2avXNTvVPqPzV0iQm (matkhau123)
-- 3 tài khoản thử theo đề bài: 1 quan_tri (admin@ued.vn), 2 khach_hang (lan@ued.vn, an@ued.vn)
-- ----------------------------------------------------------------------------
INSERT INTO nguoi_dung (id, email, ho_ten, mat_khau, vai_tro, so_dien_thoai, ngay_tao) VALUES
(1, 'admin@ued.vn', 'Quản trị viên UniEvent', '$2y$10$6RSEuRESwpYOryF5Q9CEBuqDp5cGrPsEz/hD2avXNTvVPqPzV0iQm', 'quan_tri', '0905123456', '2026-09-01 08:00:00'),
(2, 'lan@ued.vn', 'Nguyễn Thị Lan', '$2y$10$6RSEuRESwpYOryF5Q9CEBuqDp5cGrPsEz/hD2avXNTvVPqPzV0iQm', 'khach_hang', '0912345678', '2026-09-02 09:30:00'),
(3, 'an@ued.vn', 'Trần Văn An', '$2y$10$6RSEuRESwpYOryF5Q9CEBuqDp5cGrPsEz/hD2avXNTvVPqPzV0iQm', 'khach_hang', '0934567890', '2026-09-03 14:15:00'),
(4, 'hung@ued.vn', 'Đinh Trịnh Ngọc Hưng', '$2y$10$6RSEuRESwpYOryF5Q9CEBuqDp5cGrPsEz/hD2avXNTvVPqPzV0iQm', 'quan_tri', '0707174128', '2026-09-04 10:00:00'),
(5, '3120224065@ued.udn.vn', 'Đinh Trịnh Ngọc Hưng (Sinh viên)', '$2y$10$6RSEuRESwpYOryF5Q9CEBuqDp5cGrPsEz/hD2avXNTvVPqPzV0iQm', 'khach_hang', '0707174128', '2026-09-05 11:00:00');

-- ----------------------------------------------------------------------------
-- 3. NẠP DỮ LIỆU SỰ KIỆN (su_kien) - ĐỦ 32 DÒNG (>= 30 DÒNG)
-- Phủ 4 danh mục (1, 2, 3, 4).
-- Có sự kiện chứa dấu nháy đơn ' (Sự kiện ID 4: "Workshop IT: Khám phá 'GenAI'...")
-- Có sự kiện hết vé (Sự kiện ID 10: Giải chạy Marathon, ton_kho = 0)
-- ----------------------------------------------------------------------------
INSERT INTO su_kien (id, danh_muc_id, ten, gia, ton_kho, dia_diem, thoi_gian, diem_ren_luyen, dien_gia, anh, mo_ta_ngan, mo_ta_chi_tiet, video, ngay_tao) VALUES
(1, 1, 'Workshop: Ứng Dụng AI Để Nâng Cao Hiệu Quả Công Việc', 0, 150, 'Hội trường A1, ĐH Sư Phạm', '2026-10-25 18:00:00', 5, 'TS. Lê Công Khanh - Trưởng Lab Trí tuệ Nhân tạo ĐHĐN', 'images/AI.jpg', 'Khám phá các công cụ Trí tuệ Nhân tạo thế hệ mới (LLM, Prompt Engineering) ứng dụng thực tế trong học tập.', 'Chương trình hội thảo học thuật chuyên đề lớn nhất học kỳ do Khoa Toán - Tin phối hợp tổ chức.', 'https://www.youtube-nocookie.com/embed/stybt5KMIh8', '2026-10-01 08:00:00'),

(2, 2, 'Hội Thảo: Gen Z Chuyện Chọn Nghề & Phát Triển Kỹ Năng', 0, 200, 'Hội trường A, Khu Hiệu bộ ĐHSP', '2026-10-26 08:30:00', 4, 'Bà Trần Minh Tâm - Giám đốc Nhân sự FPT Đà Nẵng', 'images/nghe.jpg', 'Định hướng lộ trình sự nghiệp vững chắc, cách xây dựng thương hiệu cá nhân và chuẩn bị hồ sơ ứng tuyển.', 'Tọa đàm thân mật mang lại góc nhìn thực tế từ các nhà tuyển dụng hàng đầu miền Trung.', 'https://www.youtube-nocookie.com/embed/ekWsKOK_mo0', '2026-10-02 09:00:00'),

(3, 3, 'Đêm Nhạc Hội "Giai Điệu Sinh Viên" Sắc Màu Tuổi Trẻ', 50000, 300, 'Quảng trường Trung tâm ĐHSP', '2026-10-30 19:00:00', 5, 'Ca sĩ khách mời & Đội Văn nghệ Xung kích UED', 'https://images.unsplash.com/photo-1470225620780-dba8ba36b745?auto=format&fit=crop&w=800&q=80', 'Bữa tiệc âm nhạc và ánh sáng bùng nổ chào đón tân sinh viên với sự góp mặt của nhiều nghệ sĩ trẻ.', 'Sự kiện âm nhạc quy mô lớn quy tụ hàng ngàn sinh viên Đại học Sư phạm và các trường thành viên ĐHĐN.', '', '2026-10-03 10:00:00'),

(4, 1, 'Workshop IT: Khám phá \'GenAI\' và Kỷ Nguyên Lập Trình Mới', 20000, 120, 'Phòng máy thực hành C204, Khoa Toán - Tin', '2026-11-02 14:00:00', 4, 'Kỹ sư cao cấp Nguyễn Văn Bảo - Google Developer Group', 'images/AI.jpg', 'Thực hành tương tác trực tiếp với các mô hình ngôn ngữ lớn, tối ưu hóa code và kiểm thử tự động.', 'Hội thảo chuyên sâu có tên chứa dấu nháy đơn để kiểm tra an toàn xử lý chuỗi SQL prepared statements.', '', '2026-10-04 11:00:00'),

(5, 4, 'Giải Bóng Đá Nam Sinh Viên UED Cup 2026', 0, 80, 'Sân Vận động Trường Đại học Sư phạm', '2026-11-05 07:30:00', 6, 'Ban Thể thao Đoàn trường ĐHSP', 'https://images.unsplash.com/photo-1574629810360-7efbbe195018?auto=format&fit=crop&w=800&q=80', 'Giải bóng đá truyền thống thường niên tranh cúp vô địch giữa các liên chi đoàn toàn trường.', 'Giải đấu quy tụ 16 đội bóng xuất sắc nhất đại diện cho các khoa viện trong toàn trường tranh tài.', '', '2026-10-05 13:00:00'),

(6, 3, 'Lễ Hội Văn Hóa Quốc Tế: Giao Lưu & Kết Nối Không Biên Giới', 30000, 150, 'Sân đa năng khu A, ĐH Sư Phạm', '2026-11-10 15:00:00', 5, 'Lưu học sinh Lào, sinh viên quốc tế & Đoàn trường', 'https://images.unsplash.com/photo-1511578314322-379afb476865?auto=format&fit=crop&w=800&q=80', 'Trải nghiệm không gian văn hóa đa dạng, ẩm thực đường phố và các trang phục truyền thống độc đáo.', 'Sự kiện mang đến cơ hội giao lưu quốc tế sâu sắc giữa sinh viên Việt Nam và sinh viên các nước.', '', '2026-10-06 14:00:00'),

(7, 1, 'Cuộc Thi Olympic Tin Học Sinh Viên UED 2026', 0, 100, 'Trung tâm Khảo thí & Thực hành Tin học', '2026-11-12 08:00:00', 8, 'Hội đồng Khoa học Khoa Toán - Tin', 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?auto=format&fit=crop&w=800&q=80', 'Sân chơi trí tuệ đỉnh cao về thuật toán, lập trình thi đấu và cấu trúc dữ liệu cho sinh viên yêu CNTT.', 'Cuộc thi nhằm tuyển chọn đội tuyển xuất sắc đại diện trường tham dự Olympic Tin học Sinh viên Toàn quốc.', '', '2026-10-07 15:00:00'),

(8, 2, 'Ngày Hội Tuyển Dụng & Phỏng Vấn Thử Việc 2026', 0, 250, 'Hội trường B1, Trường ĐHSP', '2026-11-15 08:00:00', 5, 'Hơn 20 doanh nghiệp và tập đoàn công nghệ giáo dục', 'https://images.unsplash.com/photo-1521737711867-e3b97375f902?auto=format&fit=crop&w=800&q=80', 'Cơ hội tiếp cận hơn 500 vị trí tuyển dụng thực tập sinh và nhân viên chính thức ngay tại trường.', 'Sinh viên được tham gia các buổi phỏng vấn thử việc mô phỏng, nhận phản hồi trực tiếp từ chuyên gia HR.', '', '2026-10-08 16:00:00'),

(9, 4, 'Giải Cầu Lông Đôi Nam Nữ Cán Bộ & Sinh Viên', 0, 60, 'Nhà Thi đấu Thể thao Đa năng', '2026-11-18 08:30:00', 4, 'Công đoàn & Hội Sinh viên ĐHSP', 'https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?auto=format&fit=crop&w=800&q=80', 'Giao lưu thể thao nâng cao sức khỏe và tinh thần đoàn kết giữa thầy cô và các bạn sinh viên.', 'Giải đấu tổ chức chào mừng Ngày Nhà giáo Việt Nam 20/11 với nhiều phần thưởng hấp dẫn.', '', '2026-10-09 09:00:00'),

(10, 4, 'Giải Chạy Marathon Sinh Viên \'Vững Bước Tương Lai\'', 0, 0, 'Cung đường ven biển Nguyễn Tất Thành, Đà Nẵng', '2026-11-20 06:00:00', 6, 'Ban Phong trào Hội Sinh viên UED', 'https://images.unsplash.com/photo-1452626038306-9aae5e071dd3?auto=format&fit=crop&w=800&q=80', 'Cự ly chạy 5km và 10km dọc cung đường biển tuyệt đẹp, rèn luyện ý chí và lối sống lành mạnh.', 'Sự kiện đã hết sạch vé đăng ký (tồn kho = 0) phục vụ kiểm thử tính năng thông báo hết vé và chặn đặt hàng.', '', '2026-10-10 10:00:00'),

(11, 1, 'Tọa Đàm Khoa Học: Xu Hướng Điện Toán Đám Mây & DevOps', 0, 90, 'Phòng Hội thảo Tầng 3 Thư viện', '2026-11-22 09:00:00', 5, 'Chuyên gia giải pháp AWS và Cloud Architect', 'images/AI.jpg', 'Tìm hiểu kiến trúc Cloud Native, quy trình CI/CD hiện đại và lộ trình trở thành kỹ sư DevOps.', 'Chương trình cung cấp kiến thức thực tế về hạ tầng đám mây và chứng chỉ quốc tế cần thiết cho sinh viên.', '', '2026-10-11 11:00:00'),

(12, 3, 'Hội Thi Kịch Nói Sinh Viên: \'Nắng Sân Trường\'', 25000, 180, 'Hội trường lớn Khu A ĐHSP', '2026-11-25 19:30:00', 5, 'Các CLB Nghệ thuật & Đoàn trường', 'https://images.unsplash.com/photo-1460723237483-7a6dc9d0b212?auto=format&fit=crop&w=800&q=80', 'Những vở kịch sân khấu hóa sinh động phản ánh đời sống học đường, tình thầy trò và lý tưởng tuổi trẻ.', 'Hội thi truyền cảm hứng nghệ thuật sâu sắc với sự dàn dựng công phu từ các đội thi sinh viên.', '', '2026-10-12 14:00:00'),

(13, 2, 'Workshop: Xây Dựng Profile LinkedIn & CV Chuẩn Quốc Tế', 0, 110, 'Phòng Hội thảo Chuyên đề B2', '2026-11-28 14:30:00', 4, 'Chuyên gia Headhunter & Career Coach', 'https://images.unsplash.com/photo-1586281380349-632531db7ed4?auto=format&fit=crop&w=800&q=80', 'Kỹ thuật tối ưu hóa hồ sơ trực tuyến, thu hút nhà tuyển dụng toàn cầu và tạo ấn tượng ban đầu.', 'Sinh viên được chỉnh sửa hồ sơ trực tiếp tại chỗ và hướng dẫn cách xây dựng mạng lưới nghề nghiệp.', '', '2026-10-13 15:00:00'),

(14, 1, 'Hội Thảo: An Toàn Thông Tin & Phòng Chống Tấn Công Mạng', 0, 130, 'Hội trường C1 Khoa Toán - Tin', '2026-12-01 08:30:00', 5, 'Kỹ sư An ninh mạng Viettel Cyber Security', 'https://images.unsplash.com/photo-1563986768609-322da13575f3?auto=format&fit=crop&w=800&q=80', 'Nhận diện các hình thức tấn công lừa đảo trực tuyến (Phishing, Ransomware) và cách phòng thủ an toàn.', 'Nâng cao ý thức an ninh số cho cán bộ, sinh viên trong bối cảnh chuyển đổi số toàn diện.', '', '2026-10-14 08:30:00'),

(15, 3, 'Triển Lãm Mỹ Thuật & Nhiếp Ảnh Sinh Viên Sư Phạm', 0, 200, 'Sảnh Nhà Đa năng Tầng 1', '2026-12-05 09:00:00', 4, 'Khoa Sư phạm Nghệ thuật', 'https://images.unsplash.com/photo-1579783900882-c0d3dad7b119?auto=format&fit=crop&w=800&q=80', 'Trưng bày hơn 100 tác phẩm tranh sơn dầu, màu nước và ảnh nghệ thuật của sinh viên các khóa.', 'Không gian thưởng lãm nghệ thuật tao nhã, giới thiệu tài năng sáng tạo của sinh viên ĐHSP.', '', '2026-10-15 09:00:00'),

(16, 4, 'Hội Thao Thể Dục Cổ Động (Cheerleading) Sắc Màu 2026', 15000, 140, 'Nhà Thi đấu Đa năng ĐHSP', '2026-12-08 18:30:00', 5, 'Liên chi đoàn các Khoa', 'https://images.unsplash.com/photo-1518611012118-696072aa579a?auto=format&fit=crop&w=800&q=80', 'Những màn trình diễn sôi động, nhào lộn kỹ thuật cao và vũ điệu cổ động đầy nhiệt huyết.', 'Đem lại bầu không khí trẻ trung, năng động và tinh thần thể thao đồng đội gắn kết.', '', '2026-10-16 10:00:00'),

(17, 1, 'Hội Thảo: Blockchain & Tương Lai Nền Kinh Tế Số', 0, 85, 'Hội trường Tòa nhà Thông tin', '2026-12-10 14:00:00', 4, 'TS. Đặng Thanh Tùng - Viện Nghiên cứu Đổi mới Sáng tạo', 'images/AI.jpg', 'Tìm hiểu cơ chế đồng thuận, hợp đồng thông minh (Smart Contract) và các ứng dụng thực tế của Web3.', 'Cái nhìn khoa học và bài bản về công nghệ chuỗi khối, phân biệt giá trị công nghệ và đầu tư.', '', '2026-10-17 11:00:00'),

(18, 2, 'Talkshow: Kỹ Năng Giao Tiếp & Giải Quyết Xung Đột Nơi Công Sở', 0, 160, 'Hội trường B3 ĐHSP', '2026-12-12 09:00:00', 4, 'ThS. Nguyễn Hoàng Nam - Giám đốc Đào tạo VNG Corporation', 'images/nghe.jpg', 'Bí quyết lắng nghe chủ động, thấu cảm và ứng xử khéo léo trong môi trường làm việc đa văn hóa.', 'Trang bị hành trang tâm lý vững vàng cho sinh viên năm cuối chuẩn bị bước chân vào doanh nghiệp.', '', '2026-10-18 14:00:00'),

(19, 3, 'Giao Lưu Âm Nhạc Acoustic: \'Góc Phố Dịu Êm\'', 10000, 95, 'Không gian Cà phê Sinh viên Nhà Thư viện', '2026-12-15 19:00:00', 3, 'CLB Guitar & Âm nhạc Mộc UED', 'https://images.unsplash.com/photo-1510915361894-db8b60106cb1?auto=format&fit=crop&w=800&q=80', 'Không gian âm nhạc mộc mạc, ấm áp nơi sinh viên cùng sẻ chia những giai điệu yêu thích cuối tuần.', 'Buổi biểu diễn ấm cúng kết nối những tâm hồn yêu âm nhạc acoustic mộc mạc.', '', '2026-10-19 16:00:00'),

(20, 4, 'Giải Bóng Bàn Đơn Nam & Đơn Nữ Toàn Trường', 0, 50, 'Câu lạc bộ Thể thao Nhà H', '2026-12-18 08:00:00', 4, 'Bộ môn Giáo dục Thể chất', 'https://images.unsplash.com/photo-1534158914592-062992fbe900?auto=format&fit=crop&w=800&q=80', 'Các trận so tài bóng bàn kịch tính với tốc độ phản xạ và kỹ thuật xoáy đỉnh cao của sinh viên.', 'Tạo sân chơi lành mạnh, nâng cao thể lực và phong trào rèn luyện thân thể theo gương Bác Hồ.', '', '2026-10-20 08:00:00'),

(21, 1, 'Cuộc Thi Sáng Tạo Robot Mini & Xe Tự Hành 2026', 0, 70, 'Sân trước Trung tâm Công nghệ', '2026-12-20 08:30:00', 6, 'CLB Sáng tạo Trẻ & Khoa Vật lý', 'https://images.unsplash.com/photo-1485827404703-89b55fcc595e?auto=format&fit=crop&w=800&q=80', 'Đường đua xe tự hành tránh vật cản và robot thông minh vượt sa bàn do chính sinh viên chế tạo.', 'Khuyến khích đam mê chế tạo phần cứng, lập trình nhúng và ứng dụng IoT vào đời sống.', '', '2026-10-21 09:00:00'),

(22, 2, 'Diễn Đàn Khởi Nghiệp Sinh Viên: \'Từ Ý Tưởng Đến Thực Tế\'', 0, 140, 'Hội trường Trung tâm ĐH Sư Phạm', '2026-12-22 14:00:00', 5, 'Các Nhà đầu tư Thiên thần & Cựu Sinh viên Thành đạt', 'https://images.unsplash.com/photo-1559136555-9303baea8ebd?auto=format&fit=crop&w=800&q=80', 'Học hỏi kinh nghiệm gọi vốn, xây dựng mô hình kinh doanh tinh gọn và vượt qua thất bại bước đầu.', 'Nơi các bạn trẻ trình bày dự án khởi nghiệp sáng tạo trước hội đồng chuyên gia tư vấn.', '', '2026-10-22 10:00:00'),

(23, 3, 'Đêm Thơ Nhạc: \'Hương Sắc Mùa Thu Đà Nẵng\'', 0, 120, 'Hội trường Văn hóa Nhà D', '2026-12-24 19:30:00', 4, 'Khoa Ngữ văn & Hội Nhà văn TP. Đà Nẵng', 'https://images.unsplash.com/photo-1507676184212-d03ab07a01bf?auto=format&fit=crop&w=800&q=80', 'Lắng đọng cùng những vần thơ trữ tình và ca khúc bất hủ về thành phố biển Đà Nẵng mến yêu.', 'Đêm nghệ thuật tôn vinh vẻ đẹp văn học và bồi dưỡng tâm hồn yêu cái đẹp cho sinh viên sư phạm.', '', '2026-10-23 11:00:00'),

(24, 4, 'Giải Bóng Rổ 3x3 Sinh Viên Các Trường Đại Học', 20000, 90, 'Sân bóng rổ ngoài trời ĐHSP', '2026-12-26 16:00:00', 5, 'CLB Bóng rổ Sư Phạm UED', 'https://images.unsplash.com/photo-1546519638-68e109498ffc?auto=format&fit=crop&w=800&q=80', 'Tranh tài kịch tính thể thức 3 đấu 3 với lối chơi tốc độ, ném 3 điểm chuẩn xác và tinh thần nhiệt huyết.', 'Thu hút đông đảo sinh viên các trường đại học thành viên trên địa bàn thành phố Đà Nẵng tham gia.', '', '2026-10-24 15:00:00'),

(25, 1, 'Hội Thảo: Xử Lý Dữ Liệu Lớn Với Apache Spark & Python', 0, 65, 'Phòng Lab Data Science Tầng 4', '2026-12-28 09:00:00', 5, 'TS. Hoàng Minh Trí - Chuyên gia Big Data', 'images/AI.jpg', 'Thực hành kiến trúc tính toán phân tán, xử lý dữ liệu quy mô lớn và xây dựng đường ống phân tích.', 'Buổi học nâng cao dành cho sinh viên ngành Công nghệ thông tin và Toán ứng dụng.', '', '2026-10-25 16:00:00'),

(26, 2, 'Buổi Gặp Gỡ Doanh Nghiệp Nhật Bản: Cơ Hội Thực Tập Tại Tokyo', 0, 110, 'Hội trường Giao lưu Quốc tế', '2026-12-30 09:30:00', 5, 'Đoàn Hiệp hội Doanh nghiệp IT Fukuoka & Tokyo', 'images/nghe.jpg', 'Chương trình thực tập hưởng lương 6 tháng tại Nhật Bản dành cho sinh viên có tiếng Nhật hoặc kỹ năng IT tốt.', 'Cầu nối đưa sinh viên vươn ra môi trường làm việc quốc tế chuyên nghiệp và văn minh.', '', '2026-10-26 17:00:00'),

(27, 3, 'Lễ Vinh Danh & Trao Giải Cuộc Thi Sáng Tác Video \'UED Trong Tôi\'', 0, 180, 'Hội trường A Khu Hành chính', '2027-01-05 18:30:00', 5, 'Ban Tuyên giáo Đoàn trường', 'https://images.unsplash.com/photo-1485846234645-a62644f84728?auto=format&fit=crop&w=800&q=80', 'Chiếu và trao giải cho các thước phim ngắn, phóng sự ảnh xuất sắc về thầy cô và mái trường Sư phạm.', 'Tôn vinh những góc nhìn nghệ thuật tươi mới và tình cảm gắn bó của sinh viên với nhà trường.', '', '2026-10-27 08:00:00'),

(28, 4, 'Giải Cờ Vua & Cờ Tướng Sinh Viên Tranh Cúp Trí Tuệ', 0, 75, 'Hội trường Câu lạc bộ Trí tuệ Nhà B', '2027-01-08 08:00:00', 4, 'Liên đoàn Cờ TP. Đà Nẵng & ĐHSP', 'https://images.unsplash.com/photo-1529699211952-734e80c4d42b?auto=format&fit=crop&w=800&q=80', 'Những thế cờ cân não, chiến thuật biến ảo và bản lĩnh kiên cường của các kỳ thủ sinh viên tài năng.', 'Góp phần duy trì và phát triển phong trào cờ truyền thống trong cộng đồng học sinh sinh viên.', '', '2026-10-28 09:00:00'),

(29, 1, 'Tập Huấn: Viết Báo Cáo Khoa Học & Trích Dẫn Chuẩn IEEE/APA', 0, 150, 'Hội trường Thư viện Tầng 2', '2027-01-10 14:00:00', 4, 'Phòng Khoa học & Hợp tác Quốc tế', 'https://images.unsplash.com/photo-1455390582262-044cdead277a?auto=format&fit=crop&w=800&q=80', 'Hướng dẫn kỹ năng sử dụng LaTeX, Mendeley, Zotero và cách cấu trúc bài báo khoa học chuẩn quốc tế.', 'Trang bị phương pháp nghiên cứu khoa học bài bản, chuẩn bị cho khóa luận tốt nghiệp và NCKH sinh viên.', '', '2026-10-29 10:00:00'),

(30, 2, 'Workshop Kỹ Năng Quản Lý Thời Gian & Cân Bằng Cuộc Sống', 0, 130, 'Phòng Kỹ năng Mềm Nhà D', '2027-01-12 15:00:00', 4, 'Chuyên gia Tâm lý Giáo dục', 'images/nghe.jpg', 'Phương pháp ma trận Eisenhower, kỹ thuật Pomodoro và cách kiểm soát căng thẳng trong mùa thi cử.', 'Giúp sinh viên vượt qua áp lực đồng trang lứa và quản trị bản thân hiệu quả hơn.', '', '2026-10-30 11:00:00'),

(31, 3, 'Dạ Hội Khiêu Vũ Sinh Viên: \'Vũ Điệu Mùa Xuân 2027\'', 35000, 160, 'Sảnh Tiệc Nhà Đa Năng', '2027-01-15 19:00:00', 5, 'CLB Khiêu vũ Nghệ thuật UED', 'https://images.unsplash.com/photo-1545128485-c400e7702796?auto=format&fit=crop&w=800&q=80', 'Đêm dạ hội khiêu vũ sang trọng, quý phái với điệu Valse, Tango chào đón năm mới rực rỡ.', 'Cơ hội để sinh viên thể hiện phong thái tự tin, nét đẹp thanh lịch và giao lưu văn hóa tao nhã.', '', '2026-10-31 14:00:00'),

(32, 4, 'Ngày Hội Thể Thao Dân Gian: Kéo Co, Nhảy Bao Bố & Kẹp Bóng', 0, 200, 'Sân Vận động Trung tâm ĐHSP', '2027-01-18 08:00:00', 4, 'Hội Sinh viên & Đội Công tác Xã hội', 'https://images.unsplash.com/photo-1526976668912-1a811878dd37?auto=format&fit=crop&w=800&q=80', 'Sân chơi dân gian vui nhộn, tràn ngập tiếng cười và thắt chặt tình đoàn kết giữa các chi đoàn.', 'Tái hiện không khí hội làng truyền thống với những trò chơi vận động bổ ích và sảng khoái.', '', '2026-11-01 15:00:00');

-- ----------------------------------------------------------------------------
-- 4. NẠP ĐƠN HÀNG VÀ CHI TIẾT ĐƠN HÀNG MẪU (don_hang & chi_tiet_don)
-- Hai khách hàng mẫu: lan@ued.vn (id=2) và an@ued.vn (id=3), mỗi người có ít nhất 1 đơn.
-- Đơn hàng lưu chi_tiet_don kèm don_gia tại thời điểm đặt.
-- ----------------------------------------------------------------------------
INSERT INTO don_hang (id, nguoi_dung_id, ma_don, ngay_dat, tong_tien, trang_thai, ghi_chu) VALUES
(1, 2, 'DH-20261015-001', '2026-10-15 09:30:00', 80000, 'da_thanh_toan', 'Đăng ký vé đêm nhạc và workshop IT'),
(2, 3, 'DH-20261016-002', '2026-10-16 14:00:00', 30000, 'cho_xac_nhan', 'Đăng ký vé Lễ hội Văn hóa Quốc tế');

INSERT INTO chi_tiet_don (don_hang_id, su_kien_id, so_luong, don_gia) VALUES
(1, 3, 1, 50000),
(1, 4, 1, 20000),
(2, 6, 1, 30000);

-- ----------------------------------------------------------------------------
-- 5. NẠP DỮ LIỆU LIÊN HỆ MẪU (lien_he)
-- Có các phản hồi có kèm tệp ảnh và không kèm ảnh.
-- ----------------------------------------------------------------------------
INSERT INTO lien_he (id, ho_ten, email, so_dien_thoai, khoa_vien, noi_dung, anh, ngay_tao) VALUES
(1, 'Trần Minh Hoàng', 'hoangtm@ued.vn', '0905111222', 'Khoa Toán - Tin', 'Em muốn đăng ký tham gia đội hỗ trợ kỹ thuật cho Workshop AI được không ạ?', NULL, '2026-10-05 10:15:00'),
(2, 'Phan Thị Thu Hà', 'haptt@ued.vn', '0912333444', 'Khoa Sư phạm Khoa học Tự nhiên', 'Ban tổ chức cho em hỏi sinh viên khoa ngoài có được nhận điểm rèn luyện khi xem nhạc hội không ạ?', NULL, '2026-10-06 14:20:00'),
(3, 'Lê Quốc Bảo', 'baolq@ued.vn', '0935555666', 'Khoa Tâm lý - Giáo dục', 'Em xin gửi ảnh minh chứng đã chuyển khoản giữ chỗ vé VIP Đêm nhạc hội ạ.', 'avatar_1791384540_6667.jpg', '2026-10-07 16:45:00');

-- ----------------------------------------------------------------------------
-- 6. NẠP DANH SÁCH THÀNH VIÊN NHÓM 14 (thanh_vien)
-- 5 thành viên của nhóm 14.
-- ----------------------------------------------------------------------------
INSERT INTO thanh_vien (mssv, ho_ten, nguoi_dung_id) VALUES
('3120224065', 'Đinh Trịnh Ngọc Hưng', 4),
('3120224011', 'Nguyễn Hoài Bảo', NULL),
('3120224024', 'Lê Phú Đạt', NULL),
('3120224106', 'Phan Nhuận', NULL),
('3120224153', 'Nguyễn Thị Kiều Trang', NULL);

-- ----------------------------------------------------------------------------
-- 7. NẠP DỮ LIỆU SỔ LƯU BÚT TRANG CÁ NHÂN (tv_luu_but)
-- ----------------------------------------------------------------------------
INSERT INTO tv_luu_but (id, mssv, ten_khach, vai_tro, loi_nhan, thoi_gian) VALUES
(1, '3120224065', 'Nguyễn Hoài Bảo', 'Bạn cùng nhóm 14', 'Chúc Hưng hoàn thành xuất sắc đồ án môn Thiết kế Web nhé! Profile cá nhân thiết kế rất chuyên nghiệp và chỉn chu.', '2026-10-10 09:00:00'),
(2, '3120224065', 'Trần Thị Đoan Trang', 'Bạn cùng lớp', 'Giao diện web mượt mà, tối ưu hỗ trợ tiếp cận a11y và đồng hồ đếm ngược thi rất hữu ích!', '2026-10-11 10:30:00'),
(3, '3120224065', 'Hồ Văn Nhuận', 'Đồng nghiệp / Bạn bè', 'Các chức năng máy chủ chạy rất chuẩn, chúc nhóm 14 đạt điểm tối đa môn học!', '2026-10-12 14:00:00'),
(4, '3120224065', 'Phan Văn Khang', 'Giảng viên', 'Chúc mừng em Hưng đã hoàn thành xuất sắc chương trình môn học.', '2026-10-13 16:20:00'),
(5, '3120224065', 'Lê Phú Đạt', 'Bạn cùng nhóm 14', 'Hệ thống UniEvent chạy mượt quá bạn ơi, giao diện dark theme cực đỉnh!', '2026-10-14 08:45:00'),
(6, '3120224065', 'Nguyễn Thị Kiều Trang', 'Bạn cùng nhóm 14', 'Cảm ơn trưởng nhóm Hưng đã luôn nhiệt tình hỗ trợ các thành viên hoàn thành tiến độ đồ án!', '2026-10-15 11:15:00'),
(7, '3120224011', 'Đinh Trịnh Ngọc Hưng', 'Bạn cùng nhóm 14', 'Trang cá nhân của Bảo rất đẹp, hệ thống lọc kỹ năng qua GET chạy rất mượt!', '2026-10-12 09:00:00'),
(8, '3120224011', 'Lê Phú Đạt', 'Bạn cùng nhóm 14', 'Form hẹn lịch tư vấn học tập của Bảo xử lý PRG rất chuẩn nhé.', '2026-10-13 15:30:00');

-- ----------------------------------------------------------------------------
-- 8. NẠP DỮ LIỆU BỘ ĐẾM LƯỢT XEM THEO NGÀY (tv_luot_xem)
-- ----------------------------------------------------------------------------
INSERT INTO tv_luot_xem (mssv, ngay, so_luot) VALUES
('3120224065', '2026-10-10', 12),
('3120224065', '2026-10-11', 18),
('3120224065', '2026-10-12', 25),
('3120224065', '2026-10-13', 31),
('3120224065', '2026-10-14', 40),
('3120224065', '2026-10-15', 52),
('3120224065', '2026-10-16', 65),
('3120224011', '2026-10-15', 20),
('3120224011', '2026-10-16', 28);

-- ----------------------------------------------------------------------------
-- 9. NẠP DỮ LIỆU ĐÁNH GIÁ NĂNG LỰC CÁ NHÂN (tv_danh_gia) - Phan Nhuận
-- ----------------------------------------------------------------------------
INSERT INTO tv_danh_gia (id, mssv, ho_ten, ky_nang, so_sao, nhan_xet, ngay_tao) VALUES
(1, '3120224106', 'Nguyễn Văn An', 'HTML5 / CSS3 & a11y', 5, 'Code giao diện rất chuẩn ngữ nghĩa, hỗ trợ tiếp cận cực tốt.', '2026-10-10 10:00:00'),
(2, '3120224106', 'Trần Thị Thảo', 'PHP Server-side & MVC', 5, 'Logic xử lý phân trang và truy vấn dữ liệu viết rất sạch sẽ.', '2026-10-12 15:30:00');

-- ----------------------------------------------------------------------------
-- 10. NẠP DỮ LIỆU ĐIỂM HỌC PHẦN (tv_diem_hoc_phan) - Lê Phú Đạt
-- ----------------------------------------------------------------------------
INSERT INTO tv_diem_hoc_phan (id, mssv, ten_mon, diem_a1, diem_a2, diem_a3, diem_tb, diem_chu, ngay_tinh) VALUES
(1, '3120224024', 'Thiết kế và Lập trình Web', 9.0, 8.5, 9.5, 9.1, 'A', '2026-10-14 09:00:00'),
(2, '3120224024', 'Cơ sở dữ liệu', 8.5, 8.0, 8.5, 8.4, 'B+', '2026-10-14 09:15:00');

-- ----------------------------------------------------------------------------
-- 11. NẠP DỮ LIỆU DỰ TOÁN WEB (tv_du_toan_web) - Nguyễn Thị Kiều Trang
-- ----------------------------------------------------------------------------
INSERT INTO tv_du_toan_web (id, mssv, loai_website, so_trang, goi_ui, tong_chi_phi, tien_do_ngay, ngay_tao) VALUES
(1, '3120224153', 'Landing Page sự kiện', 5, 'Responsive chuẩn', 3800000, 8, '2026-10-15 14:00:00');

-- ----------------------------------------------------------------------------
-- 12. NẠP DỮ LIỆU ĐẶT LỊCH TƯ VẤN (tv_tu_van) - Nguyễn Hoài Bảo
-- ----------------------------------------------------------------------------
INSERT INTO tv_tu_van (id, mssv, ho_ten, email, chu_de, noi_dung, trang_thai, ngay_gui) VALUES
(1, '3120224011', 'Trần Thị Thu Hà', 'thuha.ued@gmail.com', 'Học tập & Đồ án', 'Em muốn nhờ anh tư vấn về hướng phát triển đồ án môn Thiết kế Web.', 'da_xu_ly', '2026-10-14 10:30:00'),
(2, '3120224011', 'Phạm Minh Khôi', 'khoi.pm@gmail.com', 'Dự án UniEvent', 'Rất ấn tượng với giao diện và kiến trúc của UniEvent, muốn trao đổi học hỏi thêm.', 'chua_xu_ly', '2026-10-15 16:45:00');

-- ----------------------------------------------------------------------------
-- 13. NẠP DỮ LIỆU BẢN TIN CÔNG NGHỆ (tv_ban_tin) - Nguyễn Thị Kiều Trang
-- ----------------------------------------------------------------------------
INSERT INTO tv_ban_tin (id, mssv, ho_ten, email, chu_de, ngay_dang_ky) VALUES
(1, '3120224153', 'Lê Thị Mai', 'sinhvien1@ued.vn', 'Web Dev & UI/UX', '2026-10-14 08:30:00'),
(2, '3120224153', 'Trần Văn Bình', 'sinhvien2@ued.vn', 'AI & Khai phá dữ liệu', '2026-10-15 11:20:00');
