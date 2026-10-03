/**
 * @file trang-danh-sach.js
 * @description Quản lý trang danh sách sự kiện: tải JSON, tìm kiếm tức thời không dấu,
 * lọc theo danh mục, sắp xếp, hiển thị 3 trạng thái (đang tải, lỗi, rỗng),
 * xử lý thêm/bỏ sự kiện yêu thích qua ủy quyền sự kiện (event delegation),
 * và mở Modal Popup hiển thị chi tiết sự kiện ngay trên trang hiện tại.
 */

import { taiJSON } from "./api.js";
import { kiemTraYeuThich, toggleYeuThich } from "./yeu-thich.js";

// Các phần tử DOM chính trên trang
const dsContainer = document.querySelector(".danh-sach-su-kien");
const selectDanhMuc = document.getElementById("danh-muc-select");
const selectSapXep = document.getElementById("sap-xep-select");
const oTimKiem =
  document.getElementById("tim-kiem-danh-sach") ||
  document.getElementById("tim-kiem-header");
const formBoLoc = document.querySelector(".bo-loc form");

// Các phần tử DOM của Modal chi tiết sự kiện
const modalOverlay = document.getElementById("modal-chi-tiet-su-kien");
const modalNutDong = document.getElementById("modal-nut-dong");
const modalTieuDe = document.getElementById("modal-tieu-de");
const modalBadge = document.getElementById("modal-badge");
const modalAnh = document.getElementById("modal-anh");
const modalThoiGian = document.getElementById("modal-thoi-gian");
const modalDiaDiem = document.getElementById("modal-dia-diem");
const modalDienGia = document.getElementById("modal-dien-gia");
const modalQuyenLoi = document.getElementById("modal-quyen-loi");
const modalQuyMo = document.getElementById("modal-quy-mo");
const modalMoTa = document.getElementById("modal-mo-ta");
const modalKhoiNoiBat = document.getElementById("modal-khoi-noi-bat");
const modalDanhSachNoiBat = document.getElementById("modal-danh-sach-noi-bat");
const modalNutYeuThich = document.getElementById("modal-nut-yeu-thich");

// Biến lưu phần tử vừa kích hoạt để khôi phục focus khi đóng modal
let phanTuTruocDo = null;
let suKienDangXemModal = null;

function capNhatNutYeuThichModal(sk) {
  if (!modalNutYeuThich || !sk) return;
  const daThich = kiemTraYeuThich(sk.id);
  modalNutYeuThich.textContent = daThich
    ? "Đã lưu vào yêu thích"
    : "Lưu vào sự kiện yêu thích";
  modalNutYeuThich.classList.toggle("da-thich", daThich);
  modalNutYeuThich.setAttribute("aria-pressed", String(daThich));
  modalNutYeuThich.setAttribute(
    "aria-label",
    daThich
      ? `Bỏ lưu sự kiện ${sk.ten}`
      : `Lưu sự kiện ${sk.ten} vào danh sách yêu thích`,
  );
}

// Tạo vùng hiển thị trạng thái và thông báo kết quả có thuộc tính aria-live="polite"
let vungThongBao = document.getElementById("vung-thong-bao-danh-sach");
if (!vungThongBao && dsContainer && dsContainer.parentElement) {
  vungThongBao = document.createElement("div");
  vungThongBao.id = "vung-thong-bao-danh-sach";
  vungThongBao.setAttribute("aria-live", "polite");
  vungThongBao.className = "vung-thong-bao-ket-qua mb-3";
  dsContainer.parentElement.insertBefore(vungThongBao, dsContainer);
}

// Biến lưu trữ toàn bộ sự kiện đã tải
let tatCaSuKien = [];
let dangXemYeuThich = window.location.hash === "#yeu-thich";

/**
 * Hàm loại bỏ dấu tiếng Việt để phục vụ tìm kiếm không phân biệt dấu.
 * @param {string} chuoi - Chuỗi đầu vào
 * @returns {string} Chuỗi không dấu, viết thường
 */
function boDauTiengViet(chuoi) {
  if (!chuoi) return "";
  return chuoi
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "")
    .replace(/đ/g, "d")
    .replace(/Đ/g, "D")
    .toLowerCase()
    .trim();
}

/**
 * Hiển thị trạng thái đang tải dữ liệu.
 */
function hienThiDangTai() {
  if (!vungThongBao) return;
  vungThongBao.textContent = "";

  const hop = document.createElement("div");
  hop.className = "trang-thai-hop trang-thai-tai";

  const spinner = document.createElement("span");
  spinner.className = "spinner";
  spinner.setAttribute("aria-hidden", "true");

  const text = document.createElement("span");
  text.textContent = "Đang tải danh sách sự kiện từ hệ thống...";

  hop.appendChild(spinner);
  hop.appendChild(text);
  vungThongBao.appendChild(hop);

  if (dsContainer) {
    dsContainer.textContent = "";
  }
}

/**
 * Hiển thị trạng thái lỗi kèm nút thử lại.
 * @param {string} thongBaoLoi - Nội dung lỗi
 */
function hienThiLoi(thongBaoLoi) {
  if (!vungThongBao) return;
  vungThongBao.textContent = "";

  const hop = document.createElement("div");
  hop.className = "trang-thai-hop trang-thai-loi";

  const p = document.createElement("p");
  p.textContent = thongBaoLoi || "Không thể tải dữ liệu sự kiện.";

  const nutThuLai = document.createElement("button");
  nutThuLai.type = "button";
  nutThuLai.className = "nut-bam nut-nhan nut-nho mt-2";
  nutThuLai.textContent = "Thử lại";
  nutThuLai.addEventListener("click", () => {
    taiVaKhoiTao();
  });

  hop.appendChild(p);
  hop.appendChild(nutThuLai);
  vungThongBao.appendChild(hop);

  if (dsContainer) {
    dsContainer.textContent = "";
  }
}

/**
 * Hiển thị trạng thái rỗng khi không có kết quả phù hợp.
 * @param {string} thongDiep - Thông điệp hiển thị
 */
function hienThiRong(thongDiep) {
  if (!vungThongBao) return;
  vungThongBao.textContent = "";

  const hop = document.createElement("div");
  hop.className = "trang-thai-hop trang-thai-rong";

  const p = document.createElement("p");
  p.textContent =
    thongDiep || "Không tìm thấy sự kiện nào phù hợp với điều kiện tìm kiếm.";

  hop.appendChild(p);
  vungThongBao.appendChild(hop);

  if (dsContainer) {
    dsContainer.textContent = "";
  }
}

/**
 * Mở cửa sổ Modal Popup hiển thị chi tiết sự kiện được chọn.
 * @param {number} id - ID của sự kiện
 */
export async function moModalChiTiet(id) {
  if (!modalOverlay) return;

  if (tatCaSuKien.length === 0) {
    try {
      const duLieu = await taiJSON("data/su-kien.json");
      if (Array.isArray(duLieu)) tatCaSuKien = duLieu;
    } catch (e) {
      console.warn("Lỗi tải dữ liệu cho modal:", e);
    }
  }

  const sk = tatCaSuKien.find((item) => item.id === id);
  if (!sk) {
    console.warn(`Không tìm thấy sự kiện với ID: ${id}`);
    return;
  }

  // Điền dữ liệu an toàn bằng textContent và thuộc tính, không dùng innerHTML
  if (modalTieuDe) modalTieuDe.textContent = sk.ten;

  if (modalBadge) {
    modalBadge.className = sk.badgeClass || "badge";
    modalBadge.textContent = sk.tenDanhMuc || "Sự kiện sinh viên";
  }

  if (modalAnh) {
    modalAnh.src = sk.hinhAnh;
    modalAnh.alt = `Hình ảnh sự kiện ${sk.ten}`;
  }

  if (modalThoiGian) modalThoiGian.textContent = sk.thoiGian;
  if (modalDiaDiem) modalDiaDiem.textContent = sk.diaDiem;

  if (modalDienGia) {
    modalDienGia.textContent = sk.dienGia || "Ban Tổ Chức Đoàn - Hội ĐHSP";
  }

  if (modalQuyenLoi) {
    modalQuyenLoi.textContent = `+${sk.diemRenLuyen} Điểm rèn luyện cấp Trường`;
  }

  if (modalQuyMo) {
    const giaVeStr =
      sk.giaVe === 0
        ? "Miễn phí vé tham gia"
        : `${sk.giaVe.toLocaleString("vi-VN")} đ`;
    modalQuyMo.textContent = `Tối đa ${sk.soLuongVe} chỗ ngồi | ${giaVeStr}`;
  }

  if (modalMoTa) {
    modalMoTa.textContent = sk.moTaChiTiet || sk.moTaNgan;
  }

  // Cập nhật link sang trang chi-tiet.html?id=... trong chân Modal
  const modalLinkChiTiet = document.getElementById("modal-link-chi-tiet");
  if (modalLinkChiTiet) {
    modalLinkChiTiet.href = `chi-tiet.html?id=${sk.id}`;
  }

  // Khối nội dung nổi bật
  if (modalKhoiNoiBat && modalDanhSachNoiBat) {
    modalDanhSachNoiBat.textContent = "";
    if (Array.isArray(sk.noiDungNoiBat) && sk.noiDungNoiBat.length > 0) {
      sk.noiDungNoiBat.forEach((muc) => {
        const li = document.createElement("li");
        li.textContent = muc;
        modalDanhSachNoiBat.appendChild(li);
      });
      modalKhoiNoiBat.style.display = "block";
    } else {
      modalKhoiNoiBat.style.display = "none";
    }
  }

  // Đồng bộ nút yêu thích trong Modal
  suKienDangXemModal = sk;
  capNhatNutYeuThichModal(sk);

  // Lưu phần tử đang active để khôi phục focus khi đóng
  phanTuTruocDo = document.activeElement;

  // Hiển thị modal và khóa cuộn nền (vẫn giữ nguyên vị trí cuộn hiện tại của trang)
  modalOverlay.removeAttribute("hidden");
  document.body.classList.add("khoa-cuon-modal");

  // Đặt focus vào nút đóng để người dùng bàn phím có thể nhấn Tab hoặc Enter/Esc ngay
  if (modalNutDong) {
    setTimeout(() => modalNutDong.focus(), 60);
  }
}

/**
 * Đóng cửa sổ Modal chi tiết sự kiện và khôi phục trạng thái.
 */
export function dongModalChiTiet() {
  if (!modalOverlay || modalOverlay.hasAttribute("hidden")) return;

  modalOverlay.setAttribute("hidden", "");
  modalOverlay.removeAttribute("aria-hidden");
  document.body.classList.remove("khoa-cuon-modal");

  // Khôi phục focus về nút vừa bấm mà không làm thay đổi vị trí cuộn trang
  if (phanTuTruocDo && typeof phanTuTruocDo.focus === "function") {
    phanTuTruocDo.focus({ preventScroll: true });
  }
}

/**
 * Tạo một phần tử thẻ sự kiện DOM an toàn (dùng createElement + textContent).
 * Nút "Xem chi tiết" là thẻ button kích hoạt Modal trực tiếp trên trang.
 * @param {object} sk - Đối tượng thông tin sự kiện
 * @returns {HTMLLIElement} Phần tử li chứa thẻ sự kiện
 */
function taoTheSuKien(sk) {
  const li = document.createElement("li");

  const article = document.createElement("article");
  article.className = "the-tin";

  // Khối media ảnh và badge
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

  // Khối nội dung thẻ
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
  pQuyenLoi.textContent = `Quyền lợi: +${sk.diemRenLuyen} Điểm rèn luyện`;

  // Khung nút hành động: Xem chi tiết (Modal) + Yêu thích
  const actionsDiv = document.createElement("div");
  actionsDiv.className = "the-tin__actions mt-3";

  // Nút bấm mở Modal chi tiết ngay trên trang (không reload, không chuyển trang)
  const btnChiTiet = document.createElement("button");
  btnChiTiet.type = "button";
  btnChiTiet.className = "nut-bam nut-nhan nut-nho nut-xem-chi-tiet";
  btnChiTiet.dataset.id = String(sk.id);
  btnChiTiet.setAttribute("aria-haspopup", "dialog");
  btnChiTiet.setAttribute("aria-label", `Xem chi tiết sự kiện ${sk.ten}`);
  btnChiTiet.textContent = "Xem chi tiết sự kiện";

  const daThich = kiemTraYeuThich(sk.id);
  const nutYeuThich = document.createElement("button");
  nutYeuThich.type = "button";
  nutYeuThich.className = `nut-yeu-thich ${daThich ? "da-thich" : ""}`;
  nutYeuThich.dataset.id = String(sk.id);
  nutYeuThich.setAttribute("aria-pressed", String(daThich));
  nutYeuThich.setAttribute(
    "aria-label",
    daThich
      ? `Đã lưu - Bỏ lưu sự kiện ${sk.ten}`
      : `Lưu tin - Lưu sự kiện ${sk.ten}`,
  );
  nutYeuThich.textContent = daThich ? "Đã lưu" : "Lưu tin";

  actionsDiv.appendChild(btnChiTiet);
  actionsDiv.appendChild(nutYeuThich);

  bodyDiv.appendChild(h3);
  bodyDiv.appendChild(pThoiGian);
  bodyDiv.appendChild(pDiaDiem);
  bodyDiv.appendChild(pQuyenLoi);
  bodyDiv.appendChild(actionsDiv);

  article.appendChild(mediaDiv);
  article.appendChild(bodyDiv);
  li.appendChild(article);

  return li;
}

/**
 * Render mảng sự kiện ra giao diện.
 * @param {object[]} danhSach - Mảng sự kiện cần render
 */
function renderDanhSach(danhSach) {
  if (!dsContainer) return;
  dsContainer.textContent = "";

  if (danhSach.length === 0) {
    if (dangXemYeuThich) {
      hienThiRong(
        'Bạn chưa lưu sự kiện nào vào danh sách yêu thích. Hãy nhấn nút "Lưu tin" ở các sự kiện để lưu lại.',
      );
    } else {
      hienThiRong("Không tìm thấy sự kiện nào phù hợp với bộ lọc hiện tại.");
    }
    return;
  }

  // Cập nhật thông báo số lượng tìm thấy
  if (vungThongBao) {
    vungThongBao.textContent = "";
    const thongBaoSoLuong = document.createElement("p");
    thongBaoSoLuong.className = "text-phu mb-3 fs-meta";
    thongBaoSoLuong.textContent = `Tìm thấy ${danhSach.length} sự kiện phù hợp.`;
    vungThongBao.appendChild(thongBaoSoLuong);
  }

  const fragment = document.createDocumentFragment();
  danhSach.forEach((sk) => {
    fragment.appendChild(taoTheSuKien(sk));
  });
  dsContainer.appendChild(fragment);
}

/**
 * Lọc và sắp xếp sự kiện dựa trên các điều kiện người dùng chọn.
 */
function apDungBoLoc() {
  const tuKhoaRaw = oTimKiem ? oTimKiem.value : "";
  const tuKhoaKhongDau = boDauTiengViet(tuKhoaRaw);
  const danhMuc = selectDanhMuc ? selectDanhMuc.value : "";
  const sapXep = selectSapXep ? selectSapXep.value : "moi-nhat";

  let ketQua = tatCaSuKien.filter((sk) => {
    // Nếu đang ở chế độ xem các mục yêu thích
    if (dangXemYeuThich && !kiemTraYeuThich(sk.id)) {
      return false;
    }

    // Lọc theo danh mục
    if (danhMuc && sk.danhMuc !== danhMuc) {
      return false;
    }

    // Lọc theo từ khóa tìm kiếm (hỗ trợ cả có dấu và không dấu)
    if (tuKhoaKhongDau) {
      const tenKhongDau = boDauTiengViet(sk.ten);
      const diaDiemKhongDau = boDauTiengViet(sk.diaDiem);
      const moTaKhongDau = boDauTiengViet(sk.moTaNgan || "");
      const hopLe =
        tenKhongDau.includes(tuKhoaKhongDau) ||
        diaDiemKhongDau.includes(tuKhoaKhongDau) ||
        moTaKhongDau.includes(tuKhoaKhongDau);
      if (!hopLe) return false;
    }

    return true;
  });

  // Sắp xếp
  ketQua.sort((a, b) => {
    if (sapXep === "moi-nhat") {
      return new Date(b.ngay).getTime() - new Date(a.ngay).getTime();
    } else if (sapXep === "sap-dien-ra") {
      return new Date(a.ngay).getTime() - new Date(b.ngay).getTime();
    } else if (sapXep === "diem-ren-luyen") {
      return b.diemRenLuyen - a.diemRenLuyen;
    } else if (sapXep === "ten-az") {
      return a.ten.localeCompare(b.ten, "vi");
    } else if (sapXep === "gia-thap") {
      return a.giaVe - b.giaVe;
    }
    return 0;
  });

  renderDanhSach(ketQua);
}

/**
 * Tải dữ liệu từ tệp JSON và khởi tạo giao diện.
 */
async function taiVaKhoiTao() {
  hienThiDangTai();
  try {
    const duLieu = await taiJSON("data/su-kien.json");
    if (!Array.isArray(duLieu)) {
      throw new Error("Dữ liệu sự kiện không đúng định dạng mảng.");
    }
    tatCaSuKien = duLieu;

    // Đọc từ khóa tìm kiếm từ tham số URL (?q=... hoặc ?timkiem=...) khi chuyển từ ô tìm kiếm trên header
    const thamSoUrl = new URLSearchParams(window.location.search);
    const tuKhoaUrl = thamSoUrl.get("q") || thamSoUrl.get("timkiem");
    if (tuKhoaUrl && oTimKiem) {
      oTimKiem.value = tuKhoaUrl;
    }

    apDungBoLoc();
  } catch (loi) {
    console.error("Lỗi nạp dữ liệu sự kiện:", loi);
    hienThiLoi(
      "Không thể kết nối đến tệp dữ liệu sự kiện. Vui lòng kiểm tra lại mạng.",
    );
  }
}

/**
 * Thiết lập các sự kiện tương tác trên trang danh sách và Modal.
 */
function thietLapSuKien() {
  // Tìm kiếm tức thời khi người dùng gõ
  if (oTimKiem) {
    oTimKiem.addEventListener("input", () => {
      apDungBoLoc();
    });
  }

  // Khi thay đổi danh mục hoặc tiêu chí sắp xếp
  if (selectDanhMuc) {
    selectDanhMuc.addEventListener("change", () => {
      apDungBoLoc();
    });
  }

  if (selectSapXep) {
    selectSapXep.addEventListener("change", () => {
      apDungBoLoc();
    });
  }

  // Nút Lưu/Bỏ lưu trong Modal Xem nhanh (dùng addEventListener chuẩn DOM Level 2)
  if (modalNutYeuThich) {
    modalNutYeuThich.addEventListener("click", () => {
      if (!suKienDangXemModal) return;
      const sk = suKienDangXemModal;
      toggleYeuThich(sk.id);
      capNhatNutYeuThichModal(sk);

      // Đồng bộ trạng thái với nút trên thẻ sự kiện tương ứng ngoài danh sách
      const nutTrenThe = document.querySelector(
        `.nut-yeu-thich[data-id="${sk.id}"]`,
      );
      if (nutTrenThe) {
        const daLuu = kiemTraYeuThich(sk.id);
        nutTrenThe.classList.toggle("da-thich", daLuu);
        nutTrenThe.setAttribute("aria-pressed", String(daLuu));
        nutTrenThe.textContent = daLuu ? "Đã lưu" : "Lưu tin";
      }

      if (dangXemYeuThich && !kiemTraYeuThich(sk.id)) {
        apDungBoLoc();
      }
    });
  }

  // Chặn tải lại trang khi người dùng nhấn Submit form bộ lọc
  if (formBoLoc) {
    formBoLoc.addEventListener("submit", (suKien) => {
      suKien.preventDefault();
      apDungBoLoc();
    });
  }

  // Ủy quyền sự kiện (Event Delegation) trên toàn danh sách sự kiện
  if (dsContainer) {
    dsContainer.addEventListener("click", (suKien) => {
      // 1. Nhấn nút "Xem chi tiết sự kiện" -> Mở Modal
      const nutChiTiet = suKien.target.closest(".nut-xem-chi-tiet");
      if (nutChiTiet) {
        suKien.preventDefault();
        const id = Number(nutChiTiet.dataset.id);
        if (id) {
          moModalChiTiet(id);
        }
        return;
      }

      // 2. Nhấn nút "Lưu sự kiện" -> Toggle yêu thích
      const nutYeuThich = suKien.target.closest(".nut-yeu-thich");
      if (nutYeuThich) {
        const id = Number(nutYeuThich.dataset.id);
        if (!id) return;

        const daLuu = toggleYeuThich(id);
        nutYeuThich.classList.toggle("da-thich", daLuu);
        nutYeuThich.setAttribute("aria-pressed", String(daLuu));
        nutYeuThich.textContent = daLuu ? "Đã lưu" : "Lưu tin";

        const sk = tatCaSuKien.find((item) => item.id === id);
        const ten = sk ? sk.ten : "sự kiện";
        nutYeuThich.setAttribute(
          "aria-label",
          daLuu
            ? `Đã lưu - Bỏ lưu sự kiện ${ten}`
            : `Lưu tin - Lưu sự kiện ${ten}`,
        );

        if (dangXemYeuThich && !daLuu) {
          apDungBoLoc();
        }
      }
    });
  }

  // ==========================================
  // CÁC SỰ KIỆN ĐIỀU KHIỂN ĐÓNG MODAL
  // ==========================================

  // 1. Đóng khi click nút X ở góc trên bên phải
  if (modalNutDong) {
    modalNutDong.addEventListener("click", () => {
      dongModalChiTiet();
    });
  }

  // 2. Đóng khi nhấn ra ngoài vùng nội dung Modal (click trên nền đen mờ)
  if (modalOverlay) {
    modalOverlay.addEventListener("click", (suKien) => {
      if (suKien.target === modalOverlay) {
        dongModalChiTiet();
      }
    });
  }

  // 3. Đóng khi nhấn phím Escape
  document.addEventListener("keydown", (suKien) => {
    if (
      suKien.key === "Escape" &&
      modalOverlay &&
      !modalOverlay.hasAttribute("hidden")
    ) {
      dongModalChiTiet();
    }
  });

  // Lắng nghe thay đổi hash trên URL (hỗ trợ bấm #yeu-thich từ header)
  window.addEventListener("hashchange", () => {
    dangXemYeuThich = window.location.hash === "#yeu-thich";
    apDungBoLoc();
  });
}

// Bắt đầu khởi chạy
taiVaKhoiTao();
thietLapSuKien();
