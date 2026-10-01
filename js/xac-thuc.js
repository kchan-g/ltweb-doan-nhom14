/**
 * @file xac-thuc.js
 * @description Quản lý đăng nhập và xác thực tài khoản sinh viên trường ĐH Sư Phạm Đà Nẵng (UED).
 * Kiểm soát chỉ email có đuôi @ued.udn.vn mới được phép đăng nhập vào hệ thống UniEvent.
 * Lưu trữ trạng thái phiên làm việc trong localStorage, cập nhật giao diện Header trên mọi trang,
 * và tự động điền thông tin sinh viên khi đăng ký vé sự kiện.
 */

// Khóa lưu thông tin sinh viên trong localStorage
const KEY_AUTH = "unievent_ued_sinhvien";

// Callback chờ kích hoạt sau khi đăng nhập thành công (nếu được gọi từ luồng đăng ký vé)
let callbackSauDangNhap = null;

// Phần tử active trước khi mở modal để phục hồi focus
let phanTuTruocKhiMoModal = null;

/**
 * Lấy thông tin sinh viên đang đăng nhập.
 * @returns {object|null} Đối tượng sinh viên hoặc null nếu chưa đăng nhập
 */
export function laySinhVienHienTai() {
  try {
    const raw = localStorage.getItem(KEY_AUTH);
    return raw ? JSON.parse(raw) : null;
  } catch (e) {
    console.warn("Lỗi đọc dữ liệu xác thực:", e);
    return null;
  }
}

/**
 * Kiểm tra xem sinh viên đã đăng nhập hay chưa.
 * @returns {boolean} True nếu đã đăng nhập bằng email @ued.udn.vn
 */
export function kiemTraDaDangNhap() {
  return Boolean(laySinhVienHienTai());
}

/**
 * Thực hiện đăng nhập với email sinh viên trường UED.
 * @param {string} email - Email sinh viên (bắt buộc đuôi @ued.udn.vn)
 * @param {string} matKhau - Mật khẩu cổng sinh viên
 * @param {string} [hoTenTuyChon] - Họ và tên (tùy chọn)
 * @param {string} [khoaTuyChon] - Mã khoa (tùy chọn)
 * @returns {{ thanhCong: boolean, loi?: string, sinhVien?: object }}
 */
export function dangNhapUED(email, matKhau, hoTenTuyChon, khoaTuyChon) {
  const emailChuan = (email || "").trim().toLowerCase();
  const matKhauChuan = (matKhau || "").trim();

  // Kiểm tra định dạng bắt buộc đuôi @ued.udn.vn
  const regexUED = /^[a-zA-Z0-9._%+-]+@ued\.udn\.vn$/i;
  if (!regexUED.test(emailChuan)) {
    return {
      thanhCong: false,
      loi: "Chỉ chấp nhận email sinh viên trường ĐH Sư Phạm Đà Nẵng (@ued.udn.vn). Vui lòng kiểm tra lại!",
    };
  }

  // Kiểm tra mật khẩu (tối thiểu 6 ký tự)
  if (!matKhauChuan || matKhauChuan.length < 6) {
    return {
      thanhCong: false,
      loi: "Mật khẩu cổng sinh viên phải có tối thiểu từ 6 ký tự trở lên.",
    };
  }

  // Tự động phân tích MSSV từ email nếu bắt đầu bằng chữ số (VD: 3120224065@ued.udn.vn)
  const phanTruoc = emailChuan.split("@")[0];
  const matchMSSV = phanTruoc.match(/^\d{8,10}$/);
  const mssv = matchMSSV ? matchMSSV[0] : "3120224065";

  // Danh mục khoa mặc định
  const khoaMap = {
    "toan-tin": "Khoa Toán - Tin",
    khtn: "Khoa Sư phạm Khoa học Tự nhiên",
    "ngu-van": "Khoa Ngữ Văn",
    "lich-su": "Khoa Lịch sử",
    "dia-ly": "Khoa Địa lý",
    "gdth-mn": "Khoa GD Tiểu học & Mầm non",
    "tam-ly": "Khoa Tâm lý - Giáo dục",
    gdtc: "Khoa Giáo dục Thể chất",
  };

  const maKhoa = khoaTuyChon || "toan-tin";
  const tenKhoa = khoaMap[maKhoa] || "Khoa Toán - Tin";

  let hoTen = "Sinh viên UED";
  if (hoTenTuyChon && hoTenTuyChon.trim()) {
    hoTen = hoTenTuyChon.trim();
  } else if (matchMSSV) {
    hoTen = `Sinh viên ${mssv}`;
  } else if (phanTruoc) {
    const parts = phanTruoc.split(/[._-]/).filter(Boolean);
    hoTen = parts.map((p) => p.charAt(0).toUpperCase() + p.slice(1)).join(" ");
  }

  // Tạo đối tượng sinh viên đăng nhập
  const sinhVien = {
    email: emailChuan,
    mssv: mssv,
    hoTen: hoTen,
    khoa: maKhoa,
    tenKhoa: tenKhoa,
    sdt: "0905123456",
    thoiGianDangNhap: new Date().toISOString(),
  };

  localStorage.setItem(KEY_AUTH, JSON.stringify(sinhVien));

  // Phát sự kiện toàn cục để cập nhật giao diện ở các component khác
  window.dispatchEvent(new CustomEvent("authThayDoi", { detail: sinhVien }));

  return { thanhCong: true, sinhVien };
}

/**
 * Đăng xuất khỏi hệ thống UniEvent.
 */
export function dangXuatUED() {
  localStorage.removeItem(KEY_AUTH);
  window.dispatchEvent(new CustomEvent("authThayDoi", { detail: null }));
  capNhatGiaoDienHeader();
}

/**
 * Cập nhật khối tài khoản trên thanh Header ở mọi trang.
 */
export function capNhatGiaoDienHeader() {
  const khuVucTaiKhoan =
    document.getElementById("khu-vuc-tai-khoan") ||
    document.querySelector(".header-phai .tai-khoan");

  if (!khuVucTaiKhoan) return;

  const sv = laySinhVienHienTai();
  khuVucTaiKhoan.textContent = "";

  if (sv) {
    // Đã đăng nhập: Hiển thị Avatar + Tên + MSSV + Nút Đăng xuất
    const wrap = document.createElement("div");
    wrap.className = "user-badge-ued";

    const avatar = document.createElement("span");
    avatar.className = "user-badge-ued__avatar";
    avatar.setAttribute("aria-hidden", "true");
    avatar.textContent = (sv.hoTen || "SV").trim().charAt(0).toUpperCase();

    const info = document.createElement("div");
    info.className = "user-badge-ued__info";

    const name = document.createElement("span");
    name.className = "user-badge-ued__name";
    name.textContent = sv.hoTen;

    const mssv = document.createElement("span");
    mssv.className = "user-badge-ued__mssv";
    mssv.textContent = `MSSV: ${sv.mssv}`;

    info.appendChild(name);
    info.appendChild(mssv);

    const btnLogout = document.createElement("button");
    btnLogout.type = "button";
    btnLogout.id = "btn-dang-xuat-ued";
    btnLogout.className = "user-badge-ued__logout";
    btnLogout.setAttribute("title", "Đăng xuất tài khoản UED");
    btnLogout.setAttribute("aria-label", "Đăng xuất tài khoản sinh viên");
    btnLogout.textContent = "Đăng xuất";
    btnLogout.addEventListener("click", () => {
      dangXuatUED();
    });

    wrap.appendChild(avatar);
    wrap.appendChild(info);
    wrap.appendChild(btnLogout);
    khuVucTaiKhoan.appendChild(wrap);
  } else {
    // Chưa đăng nhập: Hiển thị nút Đăng nhập UED
    const btnLogin = document.createElement("button");
    btnLogin.type = "button";
    btnLogin.id = "btn-mo-dang-nhap-ued";
    btnLogin.className = "nut-login-ued";
    btnLogin.setAttribute("aria-haspopup", "dialog");
    btnLogin.setAttribute(
      "aria-label",
      "Đăng nhập UED - Tài khoản email sinh viên trường",
    );
    btnLogin.textContent = "Đăng nhập UED";
    btnLogin.addEventListener("click", () => {
      moModalDangNhap();
    });

    khuVucTaiKhoan.appendChild(btnLogin);
  }
}

/**
 * Tạo và chèn Modal Đăng nhập UED vào trang nếu chưa có.
 */
function khoiTaoModalDangNhap() {
  if (document.getElementById("modal-dang-nhap-ued")) return;

  const modal = document.createElement("div");
  modal.id = "modal-dang-nhap-ued";
  modal.className = "modal-overlay";
  modal.setAttribute("role", "dialog");
  modal.setAttribute("aria-modal", "true");
  modal.setAttribute("aria-labelledby", "tieu-de-modal-login");
  modal.setAttribute("tabindex", "-1");
  modal.hidden = true;

  modal.innerHTML = `
    <div class="modal-hop-thoai modal-hop-thoai--dang-nhap" role="document">
      <button
        type="button"
        class="modal-nut-dong"
        id="nut-dong-modal-login"
        aria-label="Đóng cửa sổ đăng nhập"
      >
        <span aria-hidden="true">&times;</span>
      </button>

      <div class="modal-login-wrapper">
        <div class="modal-login-header">
          <span class="modal-login-badge">CỔNG XÁC THỰC SINH VIÊN</span>
          <h2 id="tieu-de-modal-login" class="modal-login-title">
            Đăng Nhập Sinh Viên
          </h2>
          <p class="modal-login-desc">
            Sử dụng email trường cấp (<code>@ued.udn.vn</code>) để đăng nhập
          </p>
        </div>

        <div id="login-thong-bao-loi" class="thong-bao-loi-login mb-3" aria-live="polite" hidden></div>

        <form id="form-dang-nhap-ued" novalidate>
          <div class="form-nhom mb-3">
            <label for="login-email">Email sinh viên UED</label>
            <input
              type="email"
              id="login-email"
              name="email"
              required
              placeholder="mssv@ued.udn.vn"
              autocomplete="email"
            />
          </div>

          <div class="form-nhom mb-4">
            <label for="login-matkhau">Mật khẩu</label>
            <input
              type="password"
              id="login-matkhau"
              name="matkhau"
              required
              minlength="6"
              placeholder="Nhập mật khẩu"
              autocomplete="current-password"
            />
          </div>

          <button type="submit" class="nut-bam nut-nhan nut-full py-2" id="nut-xac-nhan-login">
            Đăng Nhập
          </button>
        </form>
      </div>
    </div>
  `;

  document.body.appendChild(modal);

  // Gắn sự kiện đóng modal
  const nutDong = modal.querySelector("#nut-dong-modal-login");
  if (nutDong) {
    nutDong.addEventListener("click", dongModalDangNhap);
  }

  modal.addEventListener("click", (e) => {
    if (e.target === modal) dongModalDangNhap();
  });

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && !modal.hasAttribute("hidden")) {
      dongModalDangNhap();
    }
  });

  // Gắn sự kiện submit form đăng nhập
  const form = modal.querySelector("#form-dang-nhap-ued");
  const inputEmail = modal.querySelector("#login-email");
  const inputMatKhau = modal.querySelector("#login-matkhau");
  const hopLoi = modal.querySelector("#login-thong-bao-loi");

  function hienThiLoiLogin(msg) {
    if (!hopLoi) return;
    hopLoi.textContent = msg;
    hopLoi.removeAttribute("hidden");
  }

  function xoaLoiLogin() {
    if (!hopLoi) return;
    hopLoi.textContent = "";
    hopLoi.setAttribute("hidden", "");
  }

  if (form) {
    form.addEventListener("submit", (e) => {
      e.preventDefault();
      xoaLoiLogin();

      const kq = dangNhapUED(inputEmail.value, inputMatKhau.value);
      if (!kq.thanhCong) {
        hienThiLoiLogin(kq.loi);
        if (inputEmail && !/@ued\.udn\.vn$/i.test(inputEmail.value.trim())) {
          inputEmail.focus();
        } else if (inputMatKhau) {
          inputMatKhau.focus();
        }
      } else {
        dongModalDangNhap();
        capNhatGiaoDienHeader();

        // Kích hoạt callback nếu có
        if (typeof callbackSauDangNhap === "function") {
          const fn = callbackSauDangNhap;
          callbackSauDangNhap = null;
          fn(kq.sinhVien);
        }
      }
    });
  }
}

/**
 * Mở cửa sổ Modal Đăng nhập UED.
 * @param {Function} [onThanhCong] - Hàm callback chạy khi đăng nhập thành công
 */
export function moModalDangNhap(onThanhCong = null) {
  khoiTaoModalDangNhap();

  const modal = document.getElementById("modal-dang-nhap-ued");
  if (!modal) return;

  callbackSauDangNhap = onThanhCong;
  phanTuTruocKhiMoModal = document.activeElement;

  const form = modal.querySelector("#form-dang-nhap-ued");
  if (form) form.reset();

  const hopLoi = modal.querySelector("#login-thong-bao-loi");
  if (hopLoi) {
    hopLoi.textContent = "";
    hopLoi.setAttribute("hidden", "");
  }

  modal.removeAttribute("hidden");
  document.body.classList.add("khoa-cuon-modal");

  const inputEmail = modal.querySelector("#login-email");
  setTimeout(() => {
    if (inputEmail) inputEmail.focus();
  }, 60);
}

/**
 * Đóng cửa sổ Modal Đăng nhập UED.
 */
export function dongModalDangNhap() {
  const modal = document.getElementById("modal-dang-nhap-ued");
  if (!modal || modal.hasAttribute("hidden")) return;

  modal.setAttribute("hidden", "");
  modal.removeAttribute("aria-hidden");
  document.body.classList.remove("khoa-cuon-modal");

  if (
    phanTuTruocKhiMoModal &&
    typeof phanTuTruocKhiMoModal.focus === "function"
  ) {
    phanTuTruocKhiMoModal.focus({ preventScroll: true });
  }
}

/**
 * Khởi tạo hệ thống xác thực khi tải trang.
 */
export function khoiTaoAuth() {
  capNhatGiaoDienHeader();
  khoiTaoModalDangNhap();

  // Lắng nghe sự kiện storage thay đổi giữa các tab
  window.addEventListener("storage", (e) => {
    if (e.key === KEY_AUTH) {
      capNhatGiaoDienHeader();
    }
  });

  // Lắng nghe sự kiện auth thay đổi trong cùng tab
  window.addEventListener("authThayDoi", () => {
    capNhatGiaoDienHeader();
  });
}
