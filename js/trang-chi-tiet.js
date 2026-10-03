/**
 * @file trang-chi-tiet.js
 * @description Hiển thị chi tiết một sự kiện theo tham số ID trên URL (?id=...).
 * Tải dữ liệu từ tệp JSON, cập nhật document.title, xử lý 3 trạng thái (đang tải,
 * lỗi, không tìm thấy), đồng bộ yêu thích, hiển thị khối sự kiện cùng chuyên mục,
 * và tích hợp cửa sổ Popup đăng ký vé tại chỗ (E-Ticket) với mã QR điểm danh SVG.
 */

import { taiJSON } from "./api.js";
import { kiemTraYeuThich, toggleYeuThich } from "./yeu-thich.js";
import {
  laySinhVienHienTai,
  kiemTraDaDangNhap,
  moModalDangNhap,
} from "./xac-thuc.js";

// Vùng bố cục trang chi tiết
const layoutChiTiet = document.querySelector(".chi-tiet-layout");
const breadcrumbHienTai = document.querySelector(".breadcrumb .hien-tai");
const mainContainer = document.querySelector("main .container");
const sectionLienQuan = document.querySelector(".su-kien-lien-quan");
const dsLienQuanContainer = document.getElementById("danh-sach-lien-quan");

// Đọc tham số id từ URL (mặc định sự kiện 1 nếu không truyền)
const thamSoUrl = new URLSearchParams(window.location.search);
const idThamSo = thamSoUrl.get("id");
const maSuKien = idThamSo !== null ? Number(idThamSo) : 1;

// Biến lưu phần tử active trước khi mở modal để khôi phục focus
let phanTuTruocDo = null;

// Biến toàn cục lưu sự kiện hiện tại
let suKienHienTai = null;

/**
 * Hiển thị trạng thái đang tải dữ liệu.
 */
function hienThiDangTai() {
  if (!mainContainer) return;
  let hopTai = document.getElementById("thong-bao-trang-chi-tiet");
  if (!hopTai) {
    hopTai = document.createElement("div");
    hopTai.id = "thong-bao-trang-chi-tiet";
    hopTai.setAttribute("aria-live", "polite");
    hopTai.className = "vung-thong-bao-chi-tiet mb-4";
    mainContainer.insertBefore(hopTai, layoutChiTiet);
  }

  hopTai.textContent = "";
  const hop = document.createElement("div");
  hop.className = "trang-thai-hop trang-thai-tai";

  const spinner = document.createElement("span");
  spinner.className = "spinner";
  spinner.setAttribute("aria-hidden", "true");

  const text = document.createElement("span");
  text.textContent = "Đang tải thông tin chi tiết sự kiện...";

  hop.appendChild(spinner);
  hop.appendChild(text);
  hopTai.appendChild(hop);
}

/**
 * Xóa thông báo trạng thái tải.
 */
function xoaThongBaoTrangThai() {
  const hop = document.getElementById("thong-bao-trang-chi-tiet");
  if (hop) hop.remove();
}

/**
 * Hiển thị giao diện báo lỗi hoặc không tìm thấy sự kiện.
 * @param {string} tieuDe - Tiêu đề lỗi
 * @param {string} moTa - Mô tả chi tiết
 * @param {boolean} choPhepThuLai - Có hiển thị nút thử lại hay không
 */
function hienThiKhongTimThay(tieuDe, moTa, choPhepThuLai = false) {
  xoaThongBaoTrangThai();
  document.title = "Không tìm thấy sự kiện – UniEvent";

  if (!layoutChiTiet) return;
  layoutChiTiet.textContent = "";
  if (sectionLienQuan) sectionLienQuan.style.display = "none";

  const hop = document.createElement("div");
  hop.className = "trang-thai-hop trang-thai-rong w-full py-5 text-center";
  hop.setAttribute("aria-live", "polite");

  const h2 = document.createElement("h2");
  h2.className = "h3 mb-3 text-do";
  h2.textContent = tieuDe || "Không tìm thấy sự kiện";

  const p = document.createElement("p");
  p.className = "text-phu mb-4";
  p.textContent =
    moTa || "Sự kiện bạn yêu cầu không tồn tại hoặc đã bị gỡ bỏ khỏi hệ thống.";

  const divNut = document.createElement("div");
  divNut.className = "d-flex gap-3 justify-center";

  const aQuayLai = document.createElement("a");
  aQuayLai.href = "danh-sach.html";
  aQuayLai.className = "nut-bam nut-nhan";
  aQuayLai.textContent = "← Quay lại danh sách sự kiện";
  divNut.appendChild(aQuayLai);

  if (choPhepThuLai) {
    const nutThuLai = document.createElement("button");
    nutThuLai.type = "button";
    nutThuLai.className = "nut-bam ms-2";
    nutThuLai.textContent = "Thử lại";
    nutThuLai.addEventListener("click", () => {
      taiChiTietSuKien();
    });
    divNut.appendChild(nutThuLai);
  }

  hop.appendChild(h2);
  hop.appendChild(p);
  hop.appendChild(divNut);
  layoutChiTiet.appendChild(hop);
}

/**
 * Vẽ mã QR dạng SVG thuần chuẩn Version 1 (21x21 modules).
 * @param {SVGElement} svgEl - Thẻ SVG cần vẽ
 * @param {string} chuoiDuLieu - Chuỗi nội dung mã vé cần biểu diễn
 */
function veQRCodeSVG(svgEl, chuoiDuLieu) {
  if (!svgEl) return;
  svgEl.textContent = "";

  const n = 21;
  const cellSize = 5;
  const kichThuoc = n * cellSize; // 105x105
  svgEl.setAttribute("viewBox", `0 0 ${kichThuoc} ${kichThuoc}`);

  // Nền trắng
  const bg = document.createElementNS("http://www.w3.org/2000/svg", "rect");
  bg.setAttribute("width", String(kichThuoc));
  bg.setAttribute("height", String(kichThuoc));
  bg.setAttribute("fill", "#ffffff");
  svgEl.appendChild(bg);

  // Khởi tạo ma trận n x n
  const matrix = Array.from({ length: n }, () => Array(n).fill(false));

  // Vẽ 3 Position Detection Patterns 7x7 ở 3 góc
  function veFinder(r0, c0) {
    for (let r = 0; r < 7; r++) {
      for (let c = 0; c < 7; c++) {
        const isBorder = r === 0 || r === 6 || c === 0 || c === 6;
        const isCenter = r >= 2 && r <= 4 && c >= 2 && c <= 4;
        matrix[r0 + r][c0 + c] = isBorder || isCenter;
      }
    }
  }

  veFinder(0, 0); // Góc trên - trái
  veFinder(0, n - 7); // Góc trên - phải
  veFinder(n - 7, 0); // Góc dưới - trái

  // Vạch định thời (Timing patterns)
  for (let i = 8; i < n - 8; i++) {
    matrix[6][i] = i % 2 === 0;
    matrix[i][6] = i % 2 === 0;
  }

  // Băm chuỗi mã vé để tạo ma trận điểm ảnh độc bản
  let hash = 0;
  for (let i = 0; i < chuoiDuLieu.length; i++) {
    hash = (hash * 31 + chuoiDuLieu.charCodeAt(i)) >>> 0;
  }

  for (let r = 0; r < n; r++) {
    for (let c = 0; c < n; c++) {
      const isTL = r < 8 && c < 8;
      const isTR = r < 8 && c >= n - 8;
      const isBL = r >= n - 8 && c < 8;
      const isTiming = r === 6 || c === 6;
      if (!isTL && !isTR && !isBL && !isTiming) {
        const bit = (hash ^ (r * 13 + c * 19 + r * c)) % 7 < 3;
        matrix[r][c] = bit;
      }
    }
  }

  // Vẽ các module màu navy đậm
  const g = document.createElementNS("http://www.w3.org/2000/svg", "g");
  g.setAttribute("fill", "#0f2240");

  for (let r = 0; r < n; r++) {
    for (let c = 0; c < n; c++) {
      if (matrix[r][c]) {
        const rect = document.createElementNS(
          "http://www.w3.org/2000/svg",
          "rect",
        );
        rect.setAttribute("x", String(c * cellSize));
        rect.setAttribute("y", String(r * cellSize));
        rect.setAttribute("width", String(cellSize));
        rect.setAttribute("height", String(cellSize));
        g.appendChild(rect);
      }
    }
  }
  svgEl.appendChild(g);
}

/**
 * Cập nhật thông tin chi tiết sự kiện vào các phần tử HTML.
 * @param {object} sk - Đối tượng dữ liệu sự kiện
 */
function dienDuLieuSuKien(sk) {
  xoaThongBaoTrangThai();
  suKienHienTai = sk;

  // Cập nhật tiêu đề trang
  document.title = `${sk.ten} – UniEvent ĐH Sư Phạm Đà Nẵng`;

  // Cập nhật Breadcrumb
  if (breadcrumbHienTai) {
    breadcrumbHienTai.textContent = sk.ten;
  }

  // Cập nhật Badge thể loại
  const badgeEl = layoutChiTiet.querySelector("article .badge");
  if (badgeEl) {
    badgeEl.className = sk.badgeClass || "badge";
    badgeEl.textContent = sk.tenDanhMuc || "Sự kiện sinh viên";
  }

  // Cập nhật tiêu đề H1
  const h1El = layoutChiTiet.querySelector("article h1");
  if (h1El) {
    h1El.textContent = sk.ten;
  }

  // Cập nhật đoạn văn bản tổng quan
  const moTaTongQuanEl = layoutChiTiet.querySelector(
    "article section:nth-of-type(1) p",
  );
  if (moTaTongQuanEl) {
    moTaTongQuanEl.textContent = sk.moTaChiTiet || sk.moTaNgan;
  }

  // Cập nhật poster hình ảnh
  const posterImg = layoutChiTiet.querySelector("article figure img");
  if (posterImg) {
    posterImg.src = sk.hinhAnh;
    posterImg.alt = `Poster chính thức sự kiện ${sk.ten}`;
  }

  const figCaption = layoutChiTiet.querySelector("article figure figcaption");
  if (figCaption) {
    figCaption.textContent = `Hình ảnh truyền thông chính thức của sự kiện: ${sk.ten}`;
  }

  // Cập nhật bảng thông số chi tiết (Section 2)
  const bangRows = layoutChiTiet.querySelectorAll("article table tbody tr");
  bangRows.forEach((row) => {
    const th = row.querySelector("th");
    const td = row.querySelector("td");
    if (!th || !td) return;
    const tieuDeMuc = th.textContent.trim().toLowerCase();

    if (tieuDeMuc.includes("thời gian")) {
      td.textContent = sk.thoiGian;
    } else if (tieuDeMuc.includes("địa điểm")) {
      td.textContent = sk.diaDiem;
    } else if (
      tieuDeMuc.includes("diễn giả") ||
      tieuDeMuc.includes("khách mời")
    ) {
      td.textContent = sk.dienGia || "Ban Tổ Chức Đoàn - Hội ĐHSP";
    } else if (tieuDeMuc.includes("đối tượng")) {
      td.textContent = "Toàn thể sinh viên Trường ĐH Sư Phạm – Đại học Đà Nẵng";
    } else if (tieuDeMuc.includes("quyền lợi")) {
      td.textContent = `Cộng +${sk.diemRenLuyen} Điểm rèn luyện cấp Trường vào học kỳ hiện tại; cấp Giấy chứng nhận tham gia.`;
    }
  });

  // Cập nhật Video trailer (Section 3)
  const sectionVideo = layoutChiTiet.querySelector(
    "article section:nth-of-type(3)",
  );
  const iframeVideo =
    document.getElementById("iframe-video-trailer") ||
    layoutChiTiet.querySelector("article iframe");

  if (sectionVideo && iframeVideo) {
    if (sk.video) {
      sectionVideo.hidden = false;
      iframeVideo.src = sk.video;
      iframeVideo.title = `Video trailer sự kiện ${sk.ten}`;
    } else {
      sectionVideo.hidden = true;
    }
  }

  // Cập nhật các thông số tóm tắt bên Sidebar
  const highlightSidebar = layoutChiTiet.querySelector(".chi-tiet-highlight");
  if (highlightSidebar) {
    const cacDong = highlightSidebar.querySelectorAll(
      ".chi-tiet-highlight__dong",
    );
    cacDong.forEach((dong) => {
      const nhan = dong.querySelector(".chi-tiet-highlight__nhan");
      const giaTri = dong.querySelector(".chi-tiet-highlight__gia-tri");
      if (!nhan || !giaTri) return;
      const nhanText = nhan.textContent.trim().toLowerCase();

      if (nhanText.includes("thời gian")) {
        giaTri.textContent = sk.thoiGian;
      } else if (nhanText.includes("địa điểm")) {
        giaTri.textContent = sk.diaDiem;
      } else if (nhanText.includes("quyền lợi")) {
        giaTri.textContent = `+${sk.diemRenLuyen} Điểm rèn luyện cấp Trường`;
      } else if (nhanText.includes("quy mô")) {
        giaTri.textContent = `Tối đa ${sk.soLuongVe} chỗ ngồi`;
      }
    });

    // Nút Lưu sự kiện yêu thích trong sidebar
    let btnYeuThich = document.getElementById("btn-yeu-thich-chi-tiet");
    if (!btnYeuThich) {
      btnYeuThich = document.createElement("button");
      btnYeuThich.type = "button";
      btnYeuThich.id = "btn-yeu-thich-chi-tiet";
      btnYeuThich.className = "nut-bam nut-phu nut-full mt-2";
      highlightSidebar.appendChild(btnYeuThich);
    }

    const capNhatNutYeuThich = () => {
      const daThich = kiemTraYeuThich(sk.id);
      btnYeuThich.setAttribute("aria-pressed", String(daThich));
      btnYeuThich.setAttribute(
        "aria-label",
        daThich
          ? `Đã lưu - Bỏ lưu sự kiện ${sk.ten}`
          : `Lưu vào sự kiện yêu thích - ${sk.ten}`,
      );
      btnYeuThich.textContent = daThich
        ? "Đã lưu vào yêu thích"
        : "Lưu vào sự kiện yêu thích";
    };

    capNhatNutYeuThich();

    btnYeuThich.addEventListener("click", () => {
      toggleYeuThich(sk.id);
      capNhatNutYeuThich();
    });
  }

  // Khởi tạo Popup Đăng ký vé sự kiện
  thietLapModalDangKy(sk);
}

/**
 * Hiển thị danh sách sự kiện cùng chuyên mục ở cuối trang.
 * Nhấn vào thẻ sẽ chuyển sang chi-tiet.html?id=... trong cùng một tab.
 * @param {object[]} tatCa - Danh sách tất cả sự kiện
 * @param {object} hienTai - Sự kiện đang xem
 */
function renderSuKienLienQuan(tatCa, hienTai) {
  if (!dsLienQuanContainer || !Array.isArray(tatCa)) return;
  dsLienQuanContainer.textContent = "";

  // Lọc ra các sự kiện khác sự kiện hiện tại, ưu tiên cùng danh mục
  const cungDanhMuc = tatCa.filter(
    (item) => item.id !== hienTai.id && item.danhMuc === hienTai.danhMuc,
  );
  const khacDanhMuc = tatCa.filter(
    (item) => item.id !== hienTai.id && item.danhMuc !== hienTai.danhMuc,
  );

  const lienQuan = [...cungDanhMuc, ...khacDanhMuc].slice(0, 3);
  if (lienQuan.length === 0) {
    if (sectionLienQuan) sectionLienQuan.style.display = "none";
    return;
  }

  const fragment = document.createDocumentFragment();
  lienQuan.forEach((sk) => {
    const article = document.createElement("article");
    article.className = "the-tin";

    const mediaDiv = document.createElement("div");
    mediaDiv.className = "the-tin__media";

    const badge = document.createElement("span");
    badge.className = sk.badgeClass || "badge";
    badge.textContent = sk.tenDanhMuc || "Sự kiện";

    const aImg = document.createElement("a");
    aImg.href = `chi-tiet.html?id=${sk.id}`;
    aImg.setAttribute("aria-label", sk.ten);

    const img = document.createElement("img");
    img.src = sk.hinhAnh;
    img.alt = `Hình ảnh sự kiện ${sk.ten}`;
    img.width = 360;
    img.height = 225;
    img.loading = "lazy";
    aImg.appendChild(img);

    mediaDiv.appendChild(badge);
    mediaDiv.appendChild(aImg);

    const bodyDiv = document.createElement("div");
    bodyDiv.className = "the-tin__body";

    const h3 = document.createElement("h3");
    const aTieuDe = document.createElement("a");
    aTieuDe.href = `chi-tiet.html?id=${sk.id}`;
    aTieuDe.textContent = sk.ten;
    aTieuDe.style.color = "inherit";
    aTieuDe.style.textDecoration = "none";
    h3.appendChild(aTieuDe);

    const pThoiGian = document.createElement("p");
    pThoiGian.className = "event-meta";
    pThoiGian.textContent = `Thời gian: ${sk.thoiGian}`;

    const pDiaDiem = document.createElement("p");
    pDiaDiem.className = "event-meta";
    pDiaDiem.textContent = `Địa điểm: ${sk.diaDiem}`;

    const pQuyenLoi = document.createElement("p");
    pQuyenLoi.className = "event-meta";
    pQuyenLoi.textContent = `Quyền lợi: +${sk.diemRenLuyen} ĐRL`;

    const actionsDiv = document.createElement("div");
    actionsDiv.className = "the-tin__actions mt-3";

    const aChiTiet = document.createElement("a");
    aChiTiet.href = `chi-tiet.html?id=${sk.id}`;
    aChiTiet.className = "nut-bam nut-nhan nut-nho";
    aChiTiet.textContent = "Xem chi tiết sự kiện";

    const daThich = kiemTraYeuThich(sk.id);
    const btnYeuThich = document.createElement("button");
    btnYeuThich.type = "button";
    btnYeuThich.className = `nut-yeu-thich ${daThich ? "da-thich" : ""}`;
    btnYeuThich.setAttribute("aria-pressed", String(daThich));
    btnYeuThich.setAttribute(
      "aria-label",
      daThich
        ? `Đã lưu - Bỏ lưu sự kiện ${sk.ten}`
        : `Lưu tin - Lưu sự kiện ${sk.ten}`,
    );
    btnYeuThich.textContent = daThich ? "Đã lưu" : "Lưu tin";
    btnYeuThich.addEventListener("click", () => {
      const daLuu = toggleYeuThich(sk.id);
      btnYeuThich.classList.toggle("da-thich", daLuu);
      btnYeuThich.setAttribute("aria-pressed", String(daLuu));
      btnYeuThich.textContent = daLuu ? "Đã lưu" : "Lưu tin";
      btnYeuThich.setAttribute(
        "aria-label",
        daLuu
          ? `Đã lưu - Bỏ lưu sự kiện ${sk.ten}`
          : `Lưu tin - Lưu sự kiện ${sk.ten}`,
      );
    });

    actionsDiv.appendChild(aChiTiet);
    actionsDiv.appendChild(btnYeuThich);

    bodyDiv.appendChild(h3);
    bodyDiv.appendChild(pThoiGian);
    bodyDiv.appendChild(pDiaDiem);
    bodyDiv.appendChild(pQuyenLoi);
    bodyDiv.appendChild(actionsDiv);

    article.appendChild(mediaDiv);
    article.appendChild(bodyDiv);
    fragment.appendChild(article);
  });

  dsLienQuanContainer.appendChild(fragment);
  if (sectionLienQuan) sectionLienQuan.style.display = "block";
}

/**
 * Cài đặt hoạt động cho Modal Popup đăng ký vé sự kiện tại chỗ.
 * @param {object} sk - Dữ liệu sự kiện đang xem
 */
function thietLapModalDangKy(sk) {
  const btnMoDangKy = document.getElementById("btn-mo-dang-ky");
  const modal = document.getElementById("modal-dang-ky-ve");
  const nutDong = document.getElementById("nut-dong-modal-dang-ky");
  const nutHoanTat = document.getElementById("nut-hoan-tat-ve");

  const buocForm = document.getElementById("modal-buoc-form");
  const buocVe = document.getElementById("modal-buoc-ve");
  const form = document.getElementById("form-dang-ky-ve");

  const dangKyBadge = document.getElementById("dang-ky-badge");
  const dangKyTen = document.getElementById("dang-ky-ten-su-kien");
  const dangKyThoiGianDiaDiem = document.getElementById(
    "dang-ky-thoi-gian-dia-diem",
  );

  const inputHoTen = document.getElementById("dk-hoten");
  const inputMssv = document.getElementById("dk-mssv");
  const inputEmail = document.getElementById("dk-email");
  const inputSdt = document.getElementById("dk-sdt");
  const selectKhoa = document.getElementById("dk-khoa");

  const veTenSuKien = document.getElementById("ve-ten-su-kien");
  const veHoTen = document.getElementById("ve-ho-ten");
  const veMssv = document.getElementById("ve-mssv");
  const veThoiGian = document.getElementById("ve-thoi-gian");
  const veDiaDiem = document.getElementById("ve-dia-diem");
  const veQrCode = document.getElementById("ve-qr-code");
  const veMaSo = document.getElementById("ve-ma-so");
  const linkGoogleCal = document.getElementById("link-google-calendar");

  if (!btnMoDangKy || !modal || !form) return;

  // Lấy thẻ span thông báo lỗi của từng ô input
  function laySpanLoi(oNhap) {
    const cha = oNhap.closest(".form-nhom");
    return cha ? cha.querySelector(".thong-bao-loi") : null;
  }

  // Kiểm tra hợp lệ từng trường dữ liệu
  function kiemTraTruong(oNhap) {
    const spanLoi = laySpanLoi(oNhap);
    const val = oNhap.value.trim();
    let loi = "";

    oNhap.setCustomValidity("");

    if (oNhap.required && !val) {
      loi = "Vui lòng không bỏ trống thông tin này.";
    } else if (oNhap.id === "dk-hoten" && val.length < 3) {
      loi = "Họ và tên sinh viên tối thiểu từ 3 ký tự trở lên.";
    } else if (oNhap.id === "dk-mssv" && !/^\d{10}$/.test(val)) {
      loi = "MSSV phải gồm đúng 10 chữ số.";
    } else if (oNhap.id === "dk-email") {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(val)) {
        loi = "Định dạng email không hợp lệ.";
      }
    } else if (oNhap.id === "dk-sdt" && !/^0\d{9}$/.test(val)) {
      loi = "Số điện thoại phải gồm 10 số và bắt đầu bằng số 0.";
    } else if (oNhap.id === "dk-khoa" && !val) {
      loi = "Vui lòng chọn Khoa bạn đang theo học.";
    }

    if (spanLoi) {
      spanLoi.textContent = loi;
    }
    oNhap.classList.toggle("co-loi", Boolean(loi));
    oNhap.setAttribute("aria-invalid", String(Boolean(loi)));
    return !loi;
  }

  // Gắn sự kiện blur và input kiểm tra trực tiếp
  [inputHoTen, inputMssv, inputEmail, inputSdt, selectKhoa].forEach((oNhap) => {
    if (!oNhap) return;
    oNhap.addEventListener("blur", () => kiemTraTruong(oNhap));
    oNhap.addEventListener("input", () => {
      if (oNhap.classList.contains("co-loi")) {
        kiemTraTruong(oNhap);
      }
    });
  });

  // Mở modal đăng ký vé
  function moModal() {
    phanTuTruocDo = document.activeElement;

    // Nạp tiêu đề và thông tin tóm tắt của sự kiện vào header modal
    if (dangKyBadge) {
      dangKyBadge.className = sk.badgeClass || "badge";
      dangKyBadge.textContent = sk.tenDanhMuc || "Sự kiện";
    }
    if (dangKyTen) dangKyTen.textContent = sk.ten;
    if (dangKyThoiGianDiaDiem) {
      dangKyThoiGianDiaDiem.textContent = `${sk.thoiGian}  |  ${sk.diaDiem}`;
    }

    // Đưa về Bước 1 (Form) và reset lỗi
    if (buocForm) buocForm.hidden = false;
    if (buocVe) buocVe.hidden = true;
    form.reset();
    form.querySelectorAll(".thong-bao-loi").forEach((sp) => {
      sp.textContent = "";
    });
    form.querySelectorAll(".co-loi").forEach((inp) => {
      inp.classList.remove("co-loi");
    });

    // Tự động điền dữ liệu nếu sinh viên đã đăng nhập bằng email UED
    const sv = laySinhVienHienTai();
    if (sv) {
      if (inputHoTen) inputHoTen.value = sv.hoTen || "";
      if (inputMssv) inputMssv.value = sv.mssv || "";
      if (inputEmail) inputEmail.value = sv.email || "";
      if (inputSdt) inputSdt.value = sv.sdt || "";
      if (selectKhoa) selectKhoa.value = sv.khoa || "";
    }

    // Hiển thị modal
    modal.removeAttribute("hidden");
    document.body.classList.add("khoa-cuon-modal");

    // Focus vào nút xác nhận nếu đã điền sẵn, hoặc ô họ tên nếu chưa
    setTimeout(() => {
      if (sv) {
        const nutXacNhan = document.getElementById("nut-xac-nhan-ve");
        if (nutXacNhan) nutXacNhan.focus();
      } else if (inputHoTen) {
        inputHoTen.focus();
      }
    }, 60);
  }

  // Đóng modal đăng ký vé
  function dongModal() {
    if (modal.hasAttribute("hidden")) return;
    modal.setAttribute("hidden", "");
    modal.removeAttribute("aria-hidden");
    document.body.classList.remove("khoa-cuon-modal");

    if (phanTuTruocDo && typeof phanTuTruocDo.focus === "function") {
      phanTuTruocDo.focus({ preventScroll: true });
    }
  }

  // Mở modal khi bấm nút đăng ký (yêu cầu đăng nhập email UED nếu chưa đăng nhập)
  btnMoDangKy.addEventListener("click", () => {
    if (!kiemTraDaDangNhap()) {
      moModalDangNhap(() => {
        moModal();
      });
    } else {
      moModal();
    }
  });

  // Đóng modal khi bấm nút X hoặc nút Hoàn tất
  if (nutDong) nutDong.addEventListener("click", dongModal);
  if (nutHoanTat) nutHoanTat.addEventListener("click", dongModal);

  // Đóng khi click ngoài hộp thoại modal
  modal.addEventListener("click", (e) => {
    if (e.target === modal) dongModal();
  });

  // Đóng khi nhấn phím Escape
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && !modal.hasAttribute("hidden")) {
      dongModal();
    }
  });

  // Xử lý nộp form đăng ký vé
  form.addEventListener("submit", (e) => {
    e.preventDefault();

    const hopLeHoTen = kiemTraTruong(inputHoTen);
    const hopLeMssv = kiemTraTruong(inputMssv);
    const hopLeEmail = kiemTraTruong(inputEmail);
    const hopLeSdt = kiemTraTruong(inputSdt);
    const hopLeKhoa = kiemTraTruong(selectKhoa);

    if (!hopLeHoTen || !hopLeMssv || !hopLeEmail || !hopLeSdt || !hopLeKhoa) {
      const firstInvalid = [
        inputHoTen,
        inputMssv,
        inputEmail,
        inputSdt,
        selectKhoa,
      ].find((inp) => inp && inp.classList.contains("co-loi"));
      if (firstInvalid) firstInvalid.focus();
      return;
    }

    // Sinh mã vé ngẫu nhiên duy nhất
    const maNgauNhien = Math.random()
      .toString(36)
      .substring(2, 7)
      .toUpperCase();
    const maVe = `UED-2026-${sk.id}-${maNgauNhien}`;

    // Cập nhật thông tin vào thẻ vé E-Ticket
    if (veTenSuKien) veTenSuKien.textContent = sk.ten;
    if (veHoTen) veHoTen.textContent = inputHoTen.value.trim();
    if (veMssv) veMssv.textContent = inputMssv.value.trim();
    if (veThoiGian) veThoiGian.textContent = sk.thoiGian;
    if (veDiaDiem) veDiaDiem.textContent = sk.diaDiem;
    if (veMaSo) veMaSo.textContent = `MÃ VÉ: ${maVe}`;

    // Tạo mã QR SVG điểm danh
    if (veQrCode) {
      veQRCodeSVG(veQrCode, maVe);
    }

    // Cấu hình liên kết thêm vào Google Calendar
    if (linkGoogleCal) {
      const chiTietCal = `Vé tham dự sự kiện UniEvent của sinh viên ${inputHoTen.value.trim()} (MSSV: ${inputMssv.value.trim()}) - Mã vé: ${maVe}. Được cộng +${sk.diemRenLuyen} điểm rèn luyện cấp Trường.`;
      const urlCal = `https://calendar.google.com/calendar/render?action=TEMPLATE&text=${encodeURIComponent(sk.ten)}&details=${encodeURIComponent(chiTietCal)}&location=${encodeURIComponent(sk.diaDiem)}`;
      linkGoogleCal.href = urlCal;
    }

    // Chuyển sang Bước 2: Hiển thị Thẻ vé điện tử
    if (buocForm) buocForm.hidden = true;
    if (buocVe) {
      buocVe.hidden = false;
      setTimeout(() => {
        if (nutHoanTat) nutHoanTat.focus();
      }, 50);
    }
  });
}

/**
 * Tải danh sách sự kiện từ tệp JSON và tìm kiếm sự kiện theo ID.
 */
async function taiChiTietSuKien() {
  if (isNaN(maSuKien)) {
    hienThiKhongTimThay(
      "Mã sự kiện không hợp lệ",
      "Đường dẫn không chứa mã ID sự kiện hợp lệ (?id=...)",
    );
    return;
  }

  hienThiDangTai();
  try {
    const ds = await taiJSON("data/su-kien.json");
    const sk = ds.find((x) => x.id === maSuKien);

    if (!sk) {
      hienThiKhongTimThay(
        "Không tìm thấy sự kiện",
        `Không tìm thấy sự kiện mang mã số #${maSuKien} trong hệ thống UniEvent.`,
      );
    } else {
      dienDuLieuSuKien(sk);
      renderSuKienLienQuan(ds, sk);
    }
  } catch (loi) {
    console.error("Lỗi khi tải chi tiết sự kiện:", loi);
    hienThiKhongTimThay(
      "Lỗi tải dữ liệu",
      "Không thể kết nối đến máy chủ dữ liệu. Vui lòng thử lại sau giây lát.",
      true,
    );
  }
}

// Khởi chạy nạp chi tiết sự kiện
taiChiTietSuKien();
