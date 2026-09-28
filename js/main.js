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

// Khởi chạy khi DOM sẵn sàng
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", () => {
    khoiTaoMenu();
    capNhatSoDemHeader();
    khoiTaoAuth();
  });
} else {
  khoiTaoMenu();
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
