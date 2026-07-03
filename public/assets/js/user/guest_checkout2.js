/**
 * guest_checkout.js
 * Xử lý luồng đặt hàng 2 bước cho khách vãng lai.
 */

(function () {
  "use strict";

  let guestInfo = {};
  let currentMethod = "cod";

  // Đơn hàng CHỈ được tạo khi người dùng bấm nút xác nhận (ở bước 2),
  // không tạo sẵn lúc load trang hay lúc chuyển tab phương thức.
  // orderCreationPromise/createdOrderId dùng để cache, tránh tạo trùng
  // đơn nếu người dùng bấm nút nhiều lần hoặc đổi qua lại giữa 2 tab.
  let orderCreationPromise = null;
  let createdOrderId = null;

  const panel1 = document.getElementById("panel-step-1");
  const panel2 = document.getElementById("panel-step-2");
  const panelSuccess = document.getElementById("panel-success");

  const step1Ind = document.getElementById("step-indicator-1");
  const step2Ind = document.getElementById("step-indicator-2");

  const infoForm = document.getElementById("guestInfoForm");
  const btnBackStep = document.getElementById("btnBackStep");

  const btnConfirmCOD = document.getElementById("btnConfirmCOD");
  const btnConfirmQR = document.getElementById("btnConfirmQR");

  const methodTabs = document.querySelectorAll(".gc-method-tab");
  const methodPanels = document.querySelectorAll(".gc-method-panel");

  const gcAddressConfirm = document.getElementById("gcAddressConfirm");
  const gcQrLoading = document.getElementById("gcQrLoading");
  const gcQrNote = document.getElementById("gcQrNote");
  const gcQrImage = document.getElementById("gcQrImage");
  const gcSuccessMsg = document.getElementById("gcSuccessMsg");
  const gcSuccessDetail = document.getElementById("gcSuccessDetail");

  function showPanel(panel) {
    [panel1, panel2, panelSuccess].forEach(function (p) {
      p.classList.add("hidden");
    });
    panel.classList.remove("hidden");
  }

  function setStepActive(n) {
    step1Ind.classList.toggle("active", n >= 1);
    step2Ind.classList.toggle("active", n >= 2);
  }

  function fieldErr(id, msg) {
    const el = document.getElementById(id);
    if (el) el.textContent = msg;
  }

  function clearErrors() {
    document.querySelectorAll(".gc-field-error").forEach(function (el) {
      el.textContent = "";
    });
    document
      .querySelectorAll(".gc-field input, .gc-field textarea")
      .forEach(function (el) {
        el.classList.remove("is-error");
      });
  }

  function escHtml(str) {
    if (!str) return "";
    return str
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  function resetBtn(btn, method) {
    btn.disabled = false;
    btn.innerHTML =
      method === "cod"
        ? '<i class="fa-solid fa-check"></i> Xác nhận đặt hàng COD'
        : '<i class="fa-solid fa-qrcode"></i> Xác nhận đặt hàng & Chuyển khoản';
  }

  function validateForm() {
    clearErrors();
    let ok = true;

    const name = document.getElementById("gc_name").value.trim();
    const phone = document.getElementById("gc_phone").value.trim();
    const email = document.getElementById("gc_email").value.trim();
    const city = document.getElementById("gc_city").value.trim();
    const address = document.getElementById("gc_address").value.trim();
    const note = document.getElementById("gc_note").value.trim();

    if (!name) {
      fieldErr("err-name", "Vui lòng nhập họ và tên");
      document.getElementById("gc_name").classList.add("is-error");
      ok = false;
    }

    const phoneReg = /^(0|\+84)[0-9]{8,10}$/;
    if (!phone) {
      fieldErr("err-phone", "Vui lòng nhập số điện thoại");
      document.getElementById("gc_phone").classList.add("is-error");
      ok = false;
    } else if (!phoneReg.test(phone.replace(/\s/g, ""))) {
      fieldErr("err-phone", "Số điện thoại không hợp lệ");
      document.getElementById("gc_phone").classList.add("is-error");
      ok = false;
    }

    if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      fieldErr("err-email", "Email không đúng định dạng");
      document.getElementById("gc_email").classList.add("is-error");
      ok = false;
    }

    if (!city) {
      fieldErr("err-city", "Vui lòng nhập tỉnh / thành phố");
      document.getElementById("gc_city").classList.add("is-error");
      ok = false;
    }

    if (!address) {
      fieldErr("err-address", "Vui lòng nhập địa chỉ cụ thể");
      document.getElementById("gc_address").classList.add("is-error");
      ok = false;
    }

    if (ok) {
      guestInfo = { name, phone, email, city, address, note };
    }

    return ok;
  }
  infoForm.addEventListener("submit", function (e) {
    e.preventDefault();
    if (!validateForm()) return;

    // Cập nhật ô xác nhận địa chỉ ở bước 2
    gcAddressConfirm.innerHTML = [
      "<strong><i class='fa-solid fa-user'></i> " +
        escHtml(guestInfo.name) +
        "</strong>",
      "<span><i class='fa-solid fa-phone'></i> " +
        escHtml(guestInfo.phone) +
        "</span>",
      guestInfo.email
        ? "<span><i class='fa-solid fa-envelope'></i> " +
          escHtml(guestInfo.email) +
          "</span>"
        : "",
      "<span><i class='fa-solid fa-location-dot'></i> " +
        escHtml(guestInfo.address) +
        ", " +
        escHtml(guestInfo.city) +
        "</span>",
      guestInfo.note
        ? "<span><i class='fa-solid fa-pen'></i> " +
          escHtml(guestInfo.note) +
          "</span>"
        : "",
    ].join("");

    showPanel(panel2);
    setStepActive(2);
    window.scrollTo({ top: 0, behavior: "smooth" });
  });
  btnBackStep.addEventListener("click", function () {
    showPanel(panel1);
    setStepActive(1);
    window.scrollTo({ top: 0, behavior: "smooth" });
  });
  methodTabs.forEach(function (tab) {
    tab.addEventListener("click", function () {
      const method = this.dataset.method;
      currentMethod = method;

      methodTabs.forEach(function (t) {
        t.classList.remove("active");
      });
      this.classList.add("active");

      methodPanels.forEach(function (p) {
        p.classList.add("hidden");
      });
      const target = document.getElementById("gc-panel-" + method);
      if (target) target.classList.remove("hidden");
    });
  });

  function ensureOrderCreated() {
    if (createdOrderId) return Promise.resolve(createdOrderId);
    if (orderCreationPromise) return orderCreationPromise;

    // Kiểm tra GC_ITEMS và GC_TOTAL được truyền từ PHP
    if (!Array.isArray(GC_ITEMS) || GC_ITEMS.length === 0) {
      return Promise.reject(new Error("Giỏ hàng trống, vui lòng thử lại."));
    }

    const payload = {
      guest_name: guestInfo.name,
      guest_phone: guestInfo.phone,
      guest_email: guestInfo.email || "",
      guest_city: guestInfo.city,
      guest_address: guestInfo.address,
      note: guestInfo.note || "",
      items: GC_ITEMS,
      total: GC_TOTAL,
    };

    orderCreationPromise = fetch("/WEB_GR4/orders/guest-place", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-Requested-With": "XMLHttpRequest",
      },
      body: JSON.stringify(payload),
    })
      .then(function (res) {
        if (!res.ok) throw new Error("HTTP " + res.status);
        return res.json();
      })
      .then(function (data) {
        if (!data.success) {
          orderCreationPromise = null; // cho phép thử lại
          throw new Error(data.message || "Có lỗi khi tạo đơn hàng!");
        }
        createdOrderId = data.order_id;
        return createdOrderId;
      })
      .catch(function (err) {
        orderCreationPromise = null;
        throw err;
      });

    return orderCreationPromise;
  }

  function updateQrForOrder(orderId) {
    const qrNote = "DATHANG " + orderId;
    if (gcQrNote) gcQrNote.textContent = qrNote;
    if (gcQrImage) {
      gcQrImage.src =
        "https://img.vietqr.io/image/MB-0973469734-print.png" +
        "?amount=" +
        parseInt(GC_TOTAL, 10) +
        "&addInfo=" +
        encodeURIComponent(qrNote);
      gcQrImage.classList.remove("hidden");
    }
    if (gcQrLoading) gcQrLoading.classList.add("hidden");
  }

  function confirmPayment(orderId, method) {
    return fetch("/WEB_GR4/orders/confirmPayment", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ order_id: orderId, method: method }),
    })
      .then(function (res) {
        return res.json();
      })
      .then(function (data) {
        if (!data.success) {
          throw new Error(data.message || "Có lỗi khi xác nhận thanh toán!");
        }
        return data;
      });
  }

  function onOrderSuccess(data, method) {
    const orderId = data.order_id;
    // Mã QR thật đã được vẽ trước đó bởi updateQrForOrder() ngay khi tạo
    // đơn; ở đây chỉ cần dùng lại cùng nội dung để hiển thị trong tóm tắt.
    const qrNote = "DATHANG " + orderId;

    // Nội dung panel thành công
    gcSuccessMsg.textContent =
      method === "bank_transfer"
        ? "Đơn hàng đã được tạo! Vui lòng chờ chúng tôi xác minh và liên hệ sau."
        : "Cảm ơn bạn đã đặt hàng. Đơn sẽ được giao sớm nhất!";

    gcSuccessDetail.innerHTML = [
      "<div><i class='fa-solid fa-hashtag'></i> Mã đơn hàng: <strong>#" +
        orderId +
        "</strong></div>",
      "<div><i class='fa-solid fa-user'></i> Tên: " +
        escHtml(guestInfo.name) +
        "</div>",
      "<div><i class='fa-solid fa-phone'></i> SĐT: " +
        escHtml(guestInfo.phone) +
        "</div>",
      "<div><i class='fa-solid fa-location-dot'></i> Địa chỉ: " +
        escHtml(guestInfo.address) +
        ", " +
        escHtml(guestInfo.city) +
        "</div>",
      "<div><i class='fa-solid fa-wallet'></i> Thanh toán: " +
        (method === "cod" ? "COD (khi nhận hàng)" : "Chuyển khoản ngân hàng") +
        "</div>",
      // Hiển thị thêm thông tin QR nếu chuyển khoản
      method === "bank_transfer"
        ? "<div class='gc-qr-reminder'>" +
          "<i class='fa-solid fa-circle-info'></i> Nội dung chuyển khoản: " +
          "<strong>" +
          escHtml(qrNote) +
          "</strong>" +
          "</div>"
        : "",
    ].join("");

    showPanel(panelSuccess);
    window.scrollTo({ top: 0, behavior: "smooth" });
  }

  btnConfirmCOD.addEventListener("click", async function () {
    const btn = btnConfirmCOD;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang xử lý...';

    try {
      const orderId = await ensureOrderCreated();
      await confirmPayment(orderId, "cod");
      onOrderSuccess({ order_id: orderId }, "cod");
    } catch (err) {
      console.error("COD error:", err);
      showToast(err.message || "Có lỗi xảy ra, vui lòng thử lại.", "error");
      resetBtn(btn, "cod");
    }
  });

  btnConfirmQR.addEventListener("click", async function () {
    const btn = this;

    // Lần bấm thứ 2 trở đi (đã khoá) -> không xử lý lại ở đây,
    // logic đã được gắn lại vào nút mới bên dưới.
    if (btn.dataset.locked) return;

    btn.disabled = true;
    btn.dataset.locked = "1";
    btn.innerHTML =
      '<i class="fa-solid fa-spinner fa-spin"></i> Đang tạo đơn hàng...';
    if (gcQrLoading) gcQrLoading.classList.remove("hidden");

    try {
      const orderId = await ensureOrderCreated();
      updateQrForOrder(orderId);

      btn.disabled = false;
      btn.innerHTML =
        '<i class="fa-solid fa-check"></i> Tôi đã chuyển khoản xong';

      // Thay sự kiện cũ để tránh double-submit
      const newBtn = btn.cloneNode(true);
      btn.parentNode.replaceChild(newBtn, btn);

      newBtn.addEventListener("click", async function () {
        newBtn.disabled = true;
        newBtn.innerHTML =
          '<i class="fa-solid fa-spinner fa-spin"></i> Đang ghi nhận...';

        try {
          await confirmPayment(orderId, "bank_transfer");
          onOrderSuccess({ order_id: orderId }, "bank_transfer");
        } catch (e) {
          showToast(e.message || "Lỗi kết nối hệ thống!", "error");
          newBtn.disabled = false;
          newBtn.innerHTML =
            '<i class="fa-solid fa-check"></i> Tôi đã chuyển khoản xong';
        }
      });
    } catch (error) {
      showToast(error.message || "Có lỗi khi tạo đơn hàng!", "error");
      btn.disabled = false;
      btn.dataset.locked = "";
      btn.innerHTML =
        '<i class="fa-solid fa-qrcode"></i> Tạo đơn hàng & lấy mã QR';
      if (gcQrLoading) gcQrLoading.classList.add("hidden");
    }
  });
})();
/**
 * toast.js
 * Toast notification nhẹ, dùng chung cho các trang thanh toán,
 * thay cho alert() (tránh hộp thoại "localhost says" xấu xí).
 * Cách dùng: showToast("Nội dung", "error" | "success" | "info");
 */
(function () {
  "use strict";

  if (window.showToast) return; // đã có sẵn, tránh nạp trùng

  const STYLE_ID = "appToastStyle";
  const CONTAINER_ID = "appToastContainer";

  function ensureStyle() {
    if (document.getElementById(STYLE_ID)) return;
    const style = document.createElement("style");
    style.id = STYLE_ID;
    style.textContent = [
      "#" + CONTAINER_ID + "{position:fixed;top:20px;right:20px;z-index:99999;",
      "display:flex;flex-direction:column;gap:10px;max-width:340px;}",
      ".app-toast{display:flex;align-items:flex-start;gap:10px;padding:14px 16px;",
      "border-radius:10px;font-size:14px;line-height:1.45;color:#fff;",
      "box-shadow:0 6px 20px rgba(0,0,0,.18);opacity:0;transform:translateX(24px);",
      "transition:opacity .25s ease,transform .25s ease;}",
      ".app-toast.show{opacity:1;transform:translateX(0);}",
      ".app-toast i{margin-top:2px;flex-shrink:0;}",
      ".app-toast span{flex:1;}",
      ".app-toast.error{background:#e53935;}",
      ".app-toast.success{background:#16a34a;}",
      ".app-toast.info{background:#1976d2;}",
      "@media (max-width:480px){#" +
        CONTAINER_ID +
        "{left:16px;right:16px;max-width:none;}}",
    ].join("");
    document.head.appendChild(style);
  }

  function ensureContainer() {
    let el = document.getElementById(CONTAINER_ID);
    if (!el) {
      el = document.createElement("div");
      el.id = CONTAINER_ID;
      document.body.appendChild(el);
    }
    return el;
  }

  const ICONS = {
    error: "fa-solid fa-circle-exclamation",
    success: "fa-solid fa-circle-check",
    info: "fa-solid fa-circle-info",
  };

  /**
   * @param {string} message
   * @param {"error"|"success"|"info"} [type="error"]
   * @param {number} [duration=3500] thời gian hiện (ms)
   */
  window.showToast = function (message, type, duration) {
    type = ICONS[type] ? type : "error";
    duration = duration || 3500;

    ensureStyle();
    const container = ensureContainer();

    const toast = document.createElement("div");
    toast.className = "app-toast " + type;

    const icon = document.createElement("i");
    icon.className = ICONS[type];

    const text = document.createElement("span");
    text.textContent = message;

    toast.appendChild(icon);
    toast.appendChild(text);
    container.appendChild(toast);

    requestAnimationFrame(function () {
      toast.classList.add("show");
    });

    setTimeout(function () {
      toast.classList.remove("show");
      setTimeout(function () {
        toast.remove();
      }, 250);
    }, duration);
  };
})();
