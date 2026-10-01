/**
 * @file trang-lien-he.js
 * @description Kiểm tra dữ liệu biểu mẫu liên hệ phía client (HTML5 validity + custom errors),
 * hiển thị lỗi dưới từng ô khi blur hoặc submit, gửi dữ liệu bằng Fetch (POST)
 * tới API giả lập jsonplaceholder không tải lại trang, khóa nút gửi trong lúc chờ
 * và thông báo trạng thái với aria-live="polite".
 */

const API_LIEN_HE = "https://jsonplaceholder.typicode.com/posts";

const formLienHe = document.querySelector(".form-card form");
const nutGui = formLienHe
  ? formLienHe.querySelector('button[type="submit"]')
  : null;

// Thêm novalidate bằng JavaScript để tắt popup mặc định của trình duyệt
// Nếu người dùng tắt JavaScript, novalidate không được bật, trình duyệt tự kiểm tra
if (formLienHe) {
  formLienHe.setAttribute("novalidate", "");
}

/**
 * Tạo hoặc lấy phần tử hiển thị thông báo lỗi dưới ô nhập liệu.
 * @param {HTMLElement} oNhapLieu - Thẻ input/select/textarea
 * @returns {HTMLSpanElement} Thẻ span chứa thông báo lỗi
 */
function layThongBaoLoi(oNhapLieu) {
  const cha = oNhapLieu.closest(".form-nhom");
  if (!cha) return null;

  let spanLoi = cha.querySelector(".thong-bao-loi");
  if (!spanLoi) {
    spanLoi = document.createElement("span");
    spanLoi.className = "thong-bao-loi text-do fs-meta mt-1 d-block";
    spanLoi.setAttribute("aria-live", "polite");
    cha.appendChild(spanLoi);
  }
  return spanLoi;
}

/**
 * Kiểm tra tính hợp lệ của một trường nhập liệu cụ thể.
 * @param {HTMLElement} oNhapLieu - Thẻ input/select/textarea
 * @returns {boolean} True nếu hợp lệ, False nếu có lỗi
 */
function kiemTraTruong(oNhapLieu) {
  const spanLoi = layThongBaoLoi(oNhapLieu);
  const giaTri = oNhapLieu.value.trim();
  let loi = "";

  // Xóa custom validity cũ để đọc đúng validity chuẩn
  oNhapLieu.setCustomValidity("");

  if (oNhapLieu.required && !giaTri) {
    loi = "Trường thông tin này bắt buộc phải điền.";
  } else if (oNhapLieu.id === "txt-hoten") {
    if (giaTri.length < 3) {
      loi = "Họ và tên sinh viên phải có ít nhất 3 ký tự.";
    } else if (/[0-9]/.test(giaTri)) {
      loi = "Họ và tên không được chứa chữ số.";
    }
  } else if (oNhapLieu.id === "txt-email") {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(giaTri)) {
      loi = "Email không đúng định dạng.";
    }
  } else if (oNhapLieu.id === "txt-sdt") {
    const sdtRegex = /^0[0-9]{9}$/;
    if (!sdtRegex.test(giaTri)) {
      loi = "Số điện thoại phải gồm đúng 10 chữ số và bắt đầu bằng số 0.";
    }
  } else if (oNhapLieu.id === "sl-loai") {
    if (!giaTri) {
      loi = "Vui lòng chọn chủ đề cần được giải đáp.";
    }
  } else if (oNhapLieu.id === "txt-noidung") {
    if (giaTri.length < 10) {
      loi = "Nội dung thắc mắc quá ngắn, vui lòng nhập tối thiểu 10 ký tự.";
    }
  }

  if (loi) {
    oNhapLieu.setCustomValidity(loi);
    oNhapLieu.classList.add("co-loi");
    if (spanLoi) spanLoi.textContent = loi;
    return false;
  } else {
    oNhapLieu.classList.remove("co-loi");
    if (spanLoi) spanLoi.textContent = "";
    return true;
  }
}

/**
 * Hiển thị hộp thông báo kết quả gửi biểu mẫu ở đầu form.
 * @param {boolean} thanhCong - Trạng thái thành công hay thất bại
 * @param {string} thongBao - Nội dung thông báo
 */
function hienThiKetQuaGui(thanhCong, thongBao) {
  let hopKetQua = document.getElementById("thong-bao-ket-qua-form");
  if (!hopKetQua) {
    hopKetQua = document.createElement("div");
    hopKetQua.id = "thong-bao-ket-qua-form";
    hopKetQua.setAttribute("aria-live", "polite");
    formLienHe.parentElement.insertBefore(hopKetQua, formLienHe);
  }

  hopKetQua.textContent = "";
  hopKetQua.className = `hop-thong-bao mb-4 ${
    thanhCong ? "hop-thong-bao--thanh-cong" : "hop-thong-bao--loi"
  }`;

  const icon = document.createElement("span");
  icon.className = "hop-thong-bao__icon";
  icon.textContent = thanhCong ? "✅" : "⚠️";

  const noiDung = document.createElement("div");
  const tieuDe = document.createElement("strong");
  tieuDe.textContent = thanhCong ? "Gửi thành công!" : "Có lỗi xảy ra!";
  const p = document.createElement("p");
  p.className = "mt-1 mb-0";
  p.textContent = thongBao;

  noiDung.appendChild(tieuDe);
  noiDung.appendChild(p);
  hopKetQua.appendChild(icon);
  hopKetQua.appendChild(noiDung);

  // Cuộn tới vùng thông báo
  hopKetQua.scrollIntoView({ behavior: "smooth", block: "nearest" });
}

/**
 * Khởi tạo kiểm tra và gửi biểu mẫu.
 */
function khoiTaoFormLienHe() {
  if (!formLienHe) return;

  const cacTruong = formLienHe.querySelectorAll(
    "input[required], select[required], textarea[required]",
  );

  // Gắn sự kiện blur và input cho từng ô
  cacTruong.forEach((o) => {
    o.addEventListener("blur", () => {
      kiemTraTruong(o);
    });

    o.addEventListener("input", () => {
      // Khi người dùng đang sửa, nếu ô đang có lỗi thì kiểm tra lại để xóa lỗi ngay
      if (o.classList.contains("co-loi")) {
        kiemTraTruong(o);
      }
    });
  });

  // Xử lý gửi biểu mẫu
  formLienHe.addEventListener("submit", async (suKien) => {
    suKien.preventDefault();

    let hopLeToanBo = true;
    let oLoiDauTien = null;

    cacTruong.forEach((o) => {
      const hopLe = kiemTraTruong(o);
      if (!hopLe) {
        hopLeToanBo = false;
        if (!oLoiDauTien) {
          oLoiDauTien = o;
        }
      }
    });

    if (!hopLeToanBo) {
      if (oLoiDauTien) {
        oLoiDauTien.focus();
      }
      return;
    }

    // Thu thập dữ liệu form an toàn
    const duLieu = {
      hoTen: formLienHe.hoten ? formLienHe.hoten.value.trim() : "",
      email: formLienHe.email ? formLienHe.email.value.trim() : "",
      sdt: formLienHe.sdt ? formLienHe.sdt.value.trim() : "",
      ngay: formLienHe.ngay ? formLienHe.ngay.value : "",
      loai: formLienHe.loai ? formLienHe.loai.value : "",
      noiDung: formLienHe.noidung ? formLienHe.noidung.value.trim() : "",
      thoiGianGui: new Date().toISOString(),
    };

    // Khóa nút gửi trong lúc chờ phản hồi từ máy chủ
    const textGoc = nutGui ? nutGui.textContent : "Gửi Phiếu Yêu Cầu Hỗ Trợ";
    if (nutGui) {
      nutGui.disabled = true;
      nutGui.textContent = "⏳ Đang gửi yêu cầu...";
    }

    try {
      const res = await fetch(API_LIEN_HE, {
        method: "POST",
        headers: {
          "Content-Type": "application/json; charset=UTF-8",
        },
        body: JSON.stringify(duLieu),
      });

      if (!res.ok) {
        throw new Error(`Máy chủ phản hồi mã lỗi HTTP ${res.status}`);
      }

      const ketQua = await res.json();
      console.log("Gửi thành công tới API giả lập:", ketQua);

      // Reset biểu mẫu và thông báo thành công
      formLienHe.reset();
      cacTruong.forEach((o) => o.classList.remove("co-loi"));
      hienThiKetQuaGui(
        true,
        `Yêu cầu của bạn (Mã phiếu #${ketQua.id || 101}) đã được tiếp nhận. Đội ngũ UniEvent sẽ phản hồi qua email ${duLieu.email} trong vòng 24 giờ làm việc.`,
      );
    } catch (loi) {
      console.error("Lỗi gửi biểu mẫu liên hệ:", loi);
      hienThiKetQuaGui(
        false,
        "Không thể gửi được yêu cầu do sự cố kết nối mạng hoặc lỗi máy chủ. Vui lòng kiểm tra lại đường truyền và bấm nút gửi lại.",
      );
    } finally {
      // Mở khóa nút gửi
      if (nutGui) {
        nutGui.disabled = false;
        nutGui.textContent = textGoc;
      }
    }
  });
}

khoiTaoFormLienHe();
