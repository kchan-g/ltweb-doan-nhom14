/**
 * canhan.js - Kịch bản tương tác trang cá nhân Lê Phú Đạt
 * - Tương tác 1: Bộ đếm lượt xem trang cá nhân (Lưu bằng localStorage)
 * - Tương tác 2: Modal popup xem chi tiết chứng chỉ (Tối ưu a11y & Mobile)
 */

document.addEventListener("DOMContentLoaded", () => {
  /* ==========================================================================
     TƯƠNG TÁC 1: BỘ ĐẾM SỐ LƯỢT XEM CÁ NHÂN (PROFILE VIEW COUNTER)
     ========================================================================== */
  function initViewCounter() {
    const STORAGE_KEY = "unievent_profile_views";

    // 1. Lấy lượt xem từ localStorage hoặc gán mặc định là 0
    let currentViews = parseInt(localStorage.getItem(STORAGE_KEY), 10) || 0;

    // 2. Tăng số lượt xem lên 1
    currentViews += 1;
    localStorage.setItem(STORAGE_KEY, currentViews.toString());

    // 3. Hiển thị UI: Thêm widget bộ đếm vào thẻ .meta-pill ở phần Giới thiệu
    const metaPill = document.querySelector(".about-info .meta-pill");
    if (metaPill) {
      const viewBadge = document.createElement("span");
      viewBadge.className = "profile-views-badge";
      viewBadge.setAttribute("aria-live", "polite");
      viewBadge.style.marginLeft = "8px";
      viewBadge.innerHTML = ` &bull; 👁️ Lượt xem: <strong>${currentViews}</strong>`;
      metaPill.appendChild(viewBadge);
    }
  }

  /* ==========================================================================
     TƯƠNG TÁC 2: MODAL POPUP XEM CHI TIẾT CHỨNG CHỈ (A11Y & MOBILE FRIENDLY)
     ========================================================================== */
  function initCertificatesModal() {
    // Dữ liệu danh sách chứng chỉ
    const certData = [
      {
        id: "cert-1",
        title:
          "Chứng chỉ Figma cho Giáo dục & Thiết kế Web (Figma for Education)",
        issuer: "Figma Academy",
        year: "2026",
        desc: "Hoàn thành khóa huấn luyện thiết kế giao diện UI/UX, xây dựng Design System và tạo sơ đồ tư duy với FigJam.",
      },
      {
        id: "cert-2",
        title: "Chứng nhận Thiết kế & Lập trình Web Responsive",
        issuer: "Khoa Toán - Tin học (ĐHSP - ĐHĐN)",
        year: "2025",
        desc: "Thành thạo xây dựng giao diện HTML5 Semantic, Modern CSS Grid/Flexbox và tối ưu hóa khả năng truy cập (a11y).",
      },
    ];

    // 1. Tạo HTML cho khối Danh sách Chứng chỉ & Modal
    const skillsSection = document.getElementById("skills");
    if (!skillsSection) return;

    // Khối danh sách nút bấm xem chứng chỉ
    const certWrapper = document.createElement("div");
    certWrapper.className = "cert-section-wrapper";
    certWrapper.style.marginTop = "1.5rem";
    certWrapper.innerHTML = `
      <h3 style="font-size: 1.1rem; margin-bottom: 0.75rem;">📜 Chứng chỉ & Bằng cấp</h3>
      <div class="cert-buttons-grid" style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
        ${certData
          .map(
            (c) => `
          <button type="button" class="btn-cert-open" data-cert-id="${c.id}" aria-haspopup="dialog">
            🔍 Xem: ${c.title}
          </button>
        `,
          )
          .join("")}
      </div>
    `;
    skillsSection.appendChild(certWrapper);

    // Dựng sẵn cấu trúc Modal trong DOM với thuộc tính WAI-ARIA
    const modalDOM = document.createElement("div");
    modalDOM.id = "cert-modal";
    modalDOM.className = "cert-modal-backdrop";
    modalDOM.setAttribute("aria-hidden", "true");
    modalDOM.setAttribute("role", "dialog");
    modalDOM.setAttribute("aria-modal", "true");
    modalDOM.setAttribute("aria-labelledby", "modal-cert-title");

    modalDOM.innerHTML = `
      <div class="cert-modal-content" tabindex="-1">
        <button type="button" class="cert-modal-close" aria-label="Đóng cửa sổ thông tin">&times;</button>
        <h3 id="modal-cert-title" class="cert-modal-title"></h3>
        <p class="cert-modal-meta"><strong>Đơn vị cấp:</strong> <span id="modal-cert-issuer"></span> | <strong>Năm:</strong> <span id="modal-cert-year"></span></p>
        <div class="cert-modal-body" id="modal-cert-desc"></div>
        <div class="cert-modal-footer">
          <button type="button" class="btn-modal-dismiss">Đóng</button>
        </div>
      </div>
    `;
    document.body.appendChild(modalDOM);

    // Quản lý biến điều khiển Modal
    const modalContainer = document.getElementById("cert-modal");
    const modalContent = modalContainer.querySelector(".cert-modal-content");
    const closeBtn = modalContainer.querySelector(".cert-modal-close");
    const dismissBtn = modalContainer.querySelector(".btn-modal-dismiss");
    let lastActiveElement = null;

    // Hàm mở Modal
    function openModal(certId) {
      const data = certData.find((item) => item.id === certId);
      if (!data) return;

      // Lưu phần tử active trước khi mở để khôi phục focus khi đóng (a11y)
      lastActiveElement = document.activeElement;

      // Đổ dữ liệu vào Modal
      document.getElementById("modal-cert-title").textContent = data.title;
      document.getElementById("modal-cert-issuer").textContent = data.issuer;
      document.getElementById("modal-cert-year").textContent = data.year;
      document.getElementById("modal-cert-desc").textContent = data.desc;

      // Hiển thị Modal & khóa cuộn trang background (Tối ưu Mobile)
      modalContainer.classList.add("is-open");
      modalContainer.setAttribute("aria-hidden", "false");
      document.body.style.overflow = "hidden";

      // Chuyển Focus vào trong Modal (a11y)
      modalContent.focus();
    }

    // Hàm đóng Modal
    function closeModal() {
      if (!modalContainer.classList.contains("is-open")) return;

      modalContainer.classList.remove("is-open");
      modalContainer.setAttribute("aria-hidden", "true");
      document.body.style.overflow = "";

      // Trả lại focus cho nút đã bấm trước đó (a11y)
      if (lastActiveElement && typeof lastActiveElement.focus === "function") {
        lastActiveElement.focus();
      }
    }

    // Xử lý Sự kiện Mở
    document.querySelectorAll(".btn-cert-open").forEach((btn) => {
      btn.addEventListener("click", (e) => {
        const certId = e.currentTarget.getAttribute("data-cert-id");
        openModal(certId);
      });
    });

    // Xử lý Sự kiện Đóng
    closeBtn.addEventListener("click", closeModal);
    dismissBtn.addEventListener("click", closeModal);

    // Đóng khi nhấp bên ngoài Modal (Backdrop Click)
    modalContainer.addEventListener("click", (e) => {
      if (e.target === modalContainer) {
        closeModal();
      }
    });

    // Bắt phím bấm ESC & Bẫy Focus (Focus Trap - a11y)
    document.addEventListener("keydown", (e) => {
      if (!modalContainer.classList.contains("is-open")) return;

      // Nhấn ESC để đóng
      if (e.key === "Escape" || e.key === "Esc") {
        closeModal();
        return;
      }

      // Xử lý bẫy phím TAB
      if (e.key === "Tab") {
        const focusables = modalContainer.querySelectorAll(
          'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])',
        );
        const firstFocusable = focusables[0];
        const lastFocusable = focusables[focusables.length - 1];

        if (e.shiftKey) {
          // Shift + Tab
          if (
            document.activeElement === firstFocusable ||
            document.activeElement === modalContent
          ) {
            e.preventDefault();
            lastFocusable.focus();
          }
        } else {
          // Tab
          if (document.activeElement === lastFocusable) {
            e.preventDefault();
            firstFocusable.focus();
          }
        }
      }
    });
  }

  // Khởi chạy cả 2 tính năng
  initViewCounter();
  initCertificatesModal();
});
