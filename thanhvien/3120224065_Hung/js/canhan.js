// ============================================================================
// TỆP JAVASCRIPT TRANG CÁ NHÂN: ĐINH TRỊNH NGỌC HƯNG (MSSV: 3120224065)
// Tương tác: 1. Đổi giao diện Sáng / Tối (lưu localStorage) | 2. Đếm ngược thi cuối kỳ
// Cách thử 1: Bấm nút "Giao diện tối/sáng" trên thanh menu (hoặc phím Tab + Enter); tải lại trang để xem lưu trữ.
// Cách thử 2: Quan sát đồng hồ Ngày-Giờ-Phút-Giây chạy thời gian thực; bấm "Xem Lời Nhắc Ôn Tập" để mở rộng/thu gọn.
// ============================================================================

(function () {
  "use strict";

  // Chờ tài liệu HTML tải xong hoàn toàn trước khi gán sự kiện
  document.addEventListener("DOMContentLoaded", () => {
    initThemeToggle();
    initExamCountdown();
  });

  /* ==========================================================================
     TƯƠNG TÁC 1: CHUYỂN ĐỔI GIAO DIỆN SÁNG / TỐI (DARK/LIGHT MODE)
     - Lưu trạng thái vào localStorage để ghi nhớ qua các lần truy cập.
     - Cập nhật chuẩn a11y: aria-pressed, aria-label, textContent.
     - Hỗ trợ đầy đủ chuột và bàn phím (Tab, Enter, Space).
     ========================================================================== */
  function initThemeToggle() {
    const themeBtn = document.getElementById("theme-toggle-btn");
    const themeIcon = document.getElementById("theme-icon");
    const themeText = document.getElementById("theme-text");
    const STORAGE_KEY = "hung_profile_theme";

    if (!themeBtn) return;

    // Hàm đồng bộ giao diện và các thuộc tính hỗ trợ tiếp cận (a11y)
    function applyTheme(isDark) {
      if (isDark) {
        document.body.classList.add("dark-theme");
        themeBtn.setAttribute("aria-pressed", "true");
        themeBtn.setAttribute(
          "aria-label",
          "Chuyển sang chế độ giao diện sáng",
        );
        if (themeIcon) {
          themeIcon.textContent = "☀️";
        }
        if (themeText) {
          themeText.textContent = "Giao diện sáng";
        }
      } else {
        document.body.classList.remove("dark-theme");
        themeBtn.setAttribute("aria-pressed", "false");
        themeBtn.setAttribute("aria-label", "Chuyển sang chế độ giao diện tối");
        if (themeIcon) {
          themeIcon.textContent = "🌙";
        }
        if (themeText) {
          themeText.textContent = "Giao diện tối";
        }
      }
    }

    // Đọc cài đặt từ cookie hung_theme hoặc class có sẵn từ máy chủ render
    const hasServerDarkClass = document.body.classList.contains("dark-theme");
    let savedTheme = null;
    try {
      savedTheme = localStorage.getItem(STORAGE_KEY);
    } catch {
      // Bảo vệ nếu localStorage bị chặn
    }

    const isInitiallyDark = savedTheme ? savedTheme === "dark" : hasServerDarkClass;
    applyTheme(isInitiallyDark);

    // Gán sự kiện click: ngăn chặn hành vi điều hướng mặc định khi đã có JS
    themeBtn.addEventListener("click", (evt) => {
      evt.preventDefault();
      const willBeDark = !document.body.classList.contains("dark-theme");
      applyTheme(willBeDark);
      try {
        localStorage.setItem(STORAGE_KEY, willBeDark ? "dark" : "light");
      } catch {
        // Dự phòng an toàn
      }
      // Đồng bộ cookie hung_theme phía máy chủ (hạn dùng 30 ngày)
      document.cookie = `hung_theme=${willBeDark ? "dark" : "light"}; path=/; max-age=2592000; SameSite=Lax`;
    });
  }

  /* ==========================================================================
     TƯƠNG TÁC 2: ĐỒNG HỒ ĐẾM NGƯỢC THI CUỐI KỲ & LỜI NHẮC ÔN TẬP
     - Tính toán chênh lệch thời gian thực đến ngày thi môn Lập trình Web.
     - Cập nhật số đếm bằng textContent mỗi 1000ms qua setInterval.
     - Mở rộng / thu gọn lời nhắc ôn tập bằng createElement, textContent, classList.
     ========================================================================== */
  function initExamCountdown() {
    const daysEl = document.getElementById("countdown-days");
    const hoursEl = document.getElementById("countdown-hours");
    const minutesEl = document.getElementById("countdown-minutes");
    const secondsEl = document.getElementById("countdown-seconds");
    const targetTextEl = document.getElementById("countdown-target-text");
    const statusMsgEl = document.getElementById("countdown-status-msg");
    const btnReminder = document.getElementById("btn-exam-reminder");
    const btnStatus = document.getElementById("btn-countdown-status");
    const reminderPanel = document.getElementById("exam-reminder-panel");

    if (!daysEl || !hoursEl || !minutesEl || !secondsEl) return;

    // Thời điểm thi: Môn Thiết kế & Lập trình Web (Kỳ thi cuối kỳ: 20/12/2026 08:00)
    const examDate = new Date("2026-12-20T08:00:00+07:00");

    function updateTimer() {
      const now = new Date();
      const distance = examDate.getTime() - now.getTime();

      if (distance <= 0) {
        daysEl.textContent = "00";
        hoursEl.textContent = "00";
        minutesEl.textContent = "00";
        secondsEl.textContent = "00";
        if (targetTextEl) {
          targetTextEl.textContent =
            "🎉 Đã đến giờ thi! Chúc bạn tự tin hoàn thành bài và đạt điểm A+!";
          targetTextEl.classList.add("badge-success");
        }
        return false;
      }

      const days = Math.floor(distance / (1000 * 60 * 60 * 24));
      const hours = Math.floor(
        (distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60),
      );
      const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
      const seconds = Math.floor((distance % (1000 * 60)) / 1000);

      daysEl.textContent = String(days).padStart(2, "0");
      hoursEl.textContent = String(hours).padStart(2, "0");
      minutesEl.textContent = String(minutes).padStart(2, "0");
      secondsEl.textContent = String(seconds).padStart(2, "0");

      return true;
    }

    // Cập nhật ngay khi tải trang để không bị trễ 1 giây
    const isRunning = updateTimer();
    let timerId = null;
    if (isRunning) {
      timerId = setInterval(() => {
        const stillRunning = updateTimer();
        if (!stillRunning && timerId) {
          clearInterval(timerId);
        }
      }, 1000);
    }

    // Tương tác nút mở rộng: Hiển thị Lời nhắc ôn tập (createElement, textContent, classList)
    if (btnReminder && reminderPanel) {
      btnReminder.addEventListener("click", () => {
        const isExpanded = btnReminder.getAttribute("aria-expanded") === "true";

        if (!isExpanded) {
          // Khởi tạo nội dung động an toàn nếu chưa có
          if (reminderPanel.children.length === 0) {
            const heading = document.createElement("h3");
            heading.textContent = "📋 Trọng Tâm Ôn Tập Học Phần Lập Trình Web";

            const list = document.createElement("ul");

            const topics = [
              "Chuẩn Semantic HTML5: header, nav, main, article, section, aside, footer.",
              "Khả năng tiếp cận WCAG (a11y): Tương phản màu sắc ≥ 4.5:1, aria-label, phím Tab.",
              "Bố cục Modern CSS: Flexbox & CSS Grid, Responsive 360px không vỡ giao diện.",
              "JavaScript DOM chuẩn: addEventListener, textContent, classList, createElement.",
              "Xử lý bất đồng bộ: fetch + async/await, bắt lỗi try/catch, res.ok.",
            ];

            topics.forEach((topic) => {
              const item = document.createElement("li");
              item.textContent = "✔️ " + topic;
              list.appendChild(item);
            });

            const tipBox = document.createElement("div");
            tipBox.classList.add("tip-notice");
            tipBox.textContent =
              "💡 Mẹo phòng thi: Mang thẻ sinh viên, có mặt trước 15 phút, rà soát Console kiểm tra 0 lỗi trước khi nộp bài.";

            reminderPanel.appendChild(heading);
            reminderPanel.appendChild(list);
            reminderPanel.appendChild(tipBox);
          }

          reminderPanel.removeAttribute("hidden");
          btnReminder.setAttribute("aria-expanded", "true");
          const reminderTextEl = document.getElementById(
            "btn-exam-reminder-text",
          );
          if (reminderTextEl) {
            reminderTextEl.textContent = "Thu Gọn Lời Nhắc Ôn Tập";
          }
        } else {
          reminderPanel.setAttribute("hidden", "");
          btnReminder.setAttribute("aria-expanded", "false");
          const reminderTextEl = document.getElementById(
            "btn-exam-reminder-text",
          );
          if (reminderTextEl) {
            reminderTextEl.textContent =
              "Xem Lời Nhắc Ôn Tập & Nội Dung Trọng Tâm";
          }
        }
      });
    }

    // Tương tác nút làm mới / kiểm tra trạng thái đếm ngược
    if (btnStatus && statusMsgEl) {
      btnStatus.addEventListener("click", () => {
        const now = new Date();
        const distance = examDate.getTime() - now.getTime();
        if (distance <= 0) {
          statusMsgEl.textContent =
            "🎉 Đã đến giờ thi! Chúc bạn làm bài đạt kết quả tốt nhất!";
        } else {
          const days = Math.floor(distance / (1000 * 60 * 60 * 24));
          const hours = Math.floor(
            (distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60),
          );
          const minutes = Math.floor(
            (distance % (1000 * 60 * 60)) / (1000 * 60),
          );
          statusMsgEl.textContent = `🟢 Đồng hồ hoạt động chính xác: Còn ${days} ngày ${hours} giờ ${minutes} phút đến kỳ thi!`;
        }
        statusMsgEl.classList.remove("status-highlight");
        // Kích hoạt lại animation qua trigger reflow
        void statusMsgEl.offsetWidth;
        statusMsgEl.classList.add("status-highlight");
      });
    }
  }
})();
