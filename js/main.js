/**
 * @file main.js
 * @description Tệp JavaScript nạp ở mọi trang trên website UniEvent.
 * Đánh dấu chế độ JavaScript (.js), khởi tạo menu thu gọn cho thiết bị di động,
 * và đồng bộ số lượng sự kiện yêu thích trên header.
 */

// Đánh dấu tài liệu đang kích hoạt JavaScript (hỗ trợ Progressive Enhancement)
document.documentElement.classList.add("js");

import { capNhatSoDemHeader } from "./yeu-thich.js";
import { khoiTaoAuth } from "./xac-thuc.js";

/**
 * Khởi tạo xử lý menu di động (hamburger toggle, aria-expanded, ESC to close).
 */
function khoiTaoMenu() {
  const nutMenu = document.getElementById("nut-menu");
  const menuChinh = document.getElementById("menu-chinh");

  if (!nutMenu || !menuChinh) return;

  // Toggle trạng thái mở/đóng menu khi nhấn nút
  nutMenu.addEventListener("click", (suKien) => {
    suKien.stopPropagation();
    const dangMo = menuChinh.classList.toggle("mo");
    nutMenu.setAttribute("aria-expanded", String(dangMo));
    nutMenu.setAttribute(
      "aria-label",
      dangMo ? "Đóng menu điều hướng" : "Mở menu điều hướng",
    );
  });

  // Đóng menu khi nhấn phím Escape
  document.addEventListener("keydown", (suKien) => {
    if (suKien.key === "Escape" && menuChinh.classList.contains("mo")) {
      menuChinh.classList.remove("mo");
      nutMenu.setAttribute("aria-expanded", "false");
      nutMenu.setAttribute("aria-label", "Mở menu điều hướng");
      nutMenu.focus();
    }
  });

  // Đóng menu khi bấm ra ngoài vùng menu trên màn hình nhỏ
  document.addEventListener("click", (suKien) => {
    if (
      menuChinh.classList.contains("mo") &&
      !menuChinh.contains(suKien.target) &&
      !nutMenu.contains(suKien.target)
    ) {
      menuChinh.classList.remove("mo");
      nutMenu.setAttribute("aria-expanded", "false");
      nutMenu.setAttribute("aria-label", "Mở menu điều hướng");
    }
  });
}

/**
 * Khởi tạo ô tìm kiếm trên thanh tiêu đề (header) của website.
 * Khi người dùng nhập từ khóa và nhấn Enter hoặc nhấn nút tìm kiếm:
 * - Nếu đang ở trang danh-sach.html: tự động lọc danh sách trực tiếp.
 * - Nếu ở các trang khác: điều hướng sang danh-sach.html?q=[từ khóa].
 */
function khoiTaoTimKiemHeader() {
  const oTimKiemHeader = document.getElementById("tim-kiem-header");
  if (!oTimKiemHeader) return;

  const hopTimKiem = oTimKiemHeader.closest(".search-box");
  const nutTimKiem = hopTimKiem ? hopTimKiem.querySelector("button") : null;

  const thucHienTimKiem = () => {
    const tuKhoa = oTimKiemHeader.value.trim();
    if (!tuKhoa) return;

    const duongDan = window.location.pathname;
    const laTrangDanhSach = duongDan.endsWith("danh-sach.html");

    if (laTrangDanhSach) {
      const oTimKiemDs = document.getElementById("tim-kiem-danh-sach");
      if (oTimKiemDs) {
        oTimKiemDs.value = tuKhoa;
        oTimKiemDs.dispatchEvent(new Event("input", { bubbles: true }));
        oTimKiemDs.focus();
      }
    } else {
      window.location.href = `danh-sach.html?q=${encodeURIComponent(tuKhoa)}`;
    }
  };

  oTimKiemHeader.addEventListener("keydown", (e) => {
    if (e.key === "Enter") {
      e.preventDefault();
      thucHienTimKiem();
    }
  });

  if (nutTimKiem) {
    nutTimKiem.addEventListener("click", (e) => {
      e.preventDefault();
      thucHienTimKiem();
    });
  }
}

// Khởi chạy khi DOM sẵn sàng
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", () => {
    khoiTaoMenu();
    khoiTaoTimKiemHeader();
    capNhatSoDemHeader();
    khoiTaoAuth();
  });
} else {
  khoiTaoMenu();
  khoiTaoTimKiemHeader();
  capNhatSoDemHeader();
  khoiTaoAuth();
}

// Lắng nghe sự kiện đồng bộ khi có thay đổi danh sách yêu thích
window.addEventListener("yeuThichThayDoi", () => {
  capNhatSoDemHeader();
});

// Lắng nghe sự kiện thay đổi từ tab/cửa sổ trình duyệt khác
window.addEventListener("storage", (suKien) => {
  if (suKien.key === "unievent_yeu_thich") {
    capNhatSoDemHeader();
  }
});
