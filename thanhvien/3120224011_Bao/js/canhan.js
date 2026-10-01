/**
 * canhan.js - Kịch bản tương tác trang cá nhân Nguyễn Hoài Bảo (MSSV: 3120224011)
 * Chức năng 1: Thanh tiến độ cuộn trang (Scroll Indicator) hiển thị % đọc trực quan.
 * Chức năng 2: Khối Accordion mở rộng / thu gọn thông tin học vấn (tối ưu a11y & mobile).
 * Cách thử:
 * 1. Cuộn trang lên/xuống để quan sát thanh tiến độ và số % ở góc phải màn hình cập nhật.
 * 2. Nhấp chuột hoặc dùng phím Tab + Enter/Space vào các mục Học vấn để mở rộng/thu gọn nội dung.
 */

(() => {
  const initAllInteractions = () => {
    /* -----------------------------------------------------------
       1. TƯƠNG TÁC 1: THANH TIẾN ĐỘ DỌC MÉP PHẢI (SCROLL INDICATOR)
       ----------------------------------------------------------- */
    const progressBar = document.getElementById("scrollProgressBar");
    const progressText = document.getElementById("scrollPercentText");

    if (progressBar && progressText) {
      const calculateAndRender = () => {
        // 1. Quét tìm vị trí cuộn từ mọi nguồn có thể
        const winScroll = window.scrollY || window.pageYOffset || 0;
        const docScroll = document.documentElement ? document.documentElement.scrollTop : 0;
        const bodyScroll = document.body ? document.body.scrollTop : 0;
        
        const currentScroll = Math.max(winScroll, docScroll, bodyScroll);

        // 2. Tính tổng chiều cao thực của trang
        const totalHeight = Math.max(
          document.body ? document.body.scrollHeight : 0,
          document.documentElement ? document.documentElement.scrollHeight : 0,
          document.body ? document.body.offsetHeight : 0,
          document.documentElement ? document.documentElement.offsetHeight : 0
        );

        // 3. Chiều cao khung nhìn hiển thị
        const clientHeight = window.innerHeight || (document.documentElement ? document.documentElement.clientHeight : 0);

        const maxScrollable = totalHeight - clientHeight;

        let percentage = 0;
        if (maxScrollable > 5) {
          percentage = Math.round((currentScroll / maxScrollable) * 100);
          percentage = Math.max(0, Math.min(100, percentage));
        }

        // 4. Render trực tiếp vào DOM bằng textContent
        progressBar.style.height = percentage + "%";
        progressText.textContent = percentage + "%";
        progressBar.setAttribute("aria-valuenow", String(percentage));
      };

      // Gắn lắng nghe sự kiện cuộn ở cả capture và bubbling
      window.addEventListener("scroll", calculateAndRender, { passive: true, capture: true });
      document.addEventListener("scroll", calculateAndRender, { passive: true, capture: true });
      if (document.body) {
        document.body.addEventListener("scroll", calculateAndRender, { passive: true });
      }

      window.addEventListener("resize", calculateAndRender);
      window.addEventListener("load", calculateAndRender);

      // Chạy ngay lập tức
      calculateAndRender();
      setTimeout(calculateAndRender, 200);
      setTimeout(calculateAndRender, 800);
    }

    /* -----------------------------------------------------------
       2. TƯƠNG TÁC 2: ACCORDION HỌC VẤN (GIỮ NGUYÊN)
       ----------------------------------------------------------- */
    const accordionButtons = document.querySelectorAll(".education-accordion__btn");

    accordionButtons.forEach((btn) => {
      btn.addEventListener("click", () => {
        const isExpanded = btn.getAttribute("aria-expanded") === "true";
        const targetPanel = document.getElementById(btn.getAttribute("aria-controls"));
        const hintText = btn.querySelector(".education-accordion__hint");

        if (!targetPanel) return;

        // Đóng các mục khác
        accordionButtons.forEach((otherBtn) => {
          if (otherBtn !== btn) {
            otherBtn.setAttribute("aria-expanded", "false");
            const otherPanel = document.getElementById(otherBtn.getAttribute("aria-controls"));
            const otherHint = otherBtn.querySelector(".education-accordion__hint");
            if (otherPanel) {
              otherPanel.hidden = true;
              otherPanel.style.maxHeight = null;
            }
            if (otherHint) {
              otherHint.textContent = "Bấm để mở rộng";
            }
          }
        });

        // Bật / tắt mục hiện tại
        if (isExpanded) {
          btn.setAttribute("aria-expanded", "false");
          targetPanel.hidden = true;
          targetPanel.style.maxHeight = null;
          if (hintText) hintText.textContent = "Bấm để mở rộng";
        } else {
          btn.setAttribute("aria-expanded", "true");
          targetPanel.hidden = false;
          targetPanel.style.maxHeight = (targetPanel.scrollHeight + 30) + "px";
          if (hintText) hintText.textContent = "Bấm để thu gọn";
        }
      });
    });
  };

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initAllInteractions);
  } else {
    initAllInteractions();
  }
})();