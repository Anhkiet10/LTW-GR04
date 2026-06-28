/**
 * guest_checkout.js
 * Xử lý luồng đặt hàng 2 bước cho khách vãng lai.
 */

(function () {
  "use strict";

  /* ─────────────────── State ─────────────────── */
  let guestInfo = {};
  let currentMethod = "cod";

  /* ─────────────────── Elements ─────────────────── */
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
  const gcQrNote = document.getElementById("gcQrNote");
  const gcQrImage = document.getElementById("gcQrImage");
  const gcSuccessMsg = document.getElementById("gcSuccessMsg");
  const gcSuccessDetail = document.getElementById("gcSuccessDetail");

  /* ─────────────────── Helpers ─────────────────── */
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

  /* ─────────────────── Bước 1: Validate form ─────────────────── */
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

  /* ─────────────────── Bước 1 → 2 ─────────────────── */
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

  /* ─────────────────── Bước 2 → 1 ─────────────────── */
  btnBackStep.addEventListener("click", function () {
    showPanel(panel1);
    setStepActive(1);
    window.scrollTo({ top: 0, behavior: "smooth" });
  });

  /* ─────────────────── Chuyển tab thanh toán ─────────────────── */
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

  /* ─────────────────── Gửi đơn hàng ─────────────────── */
  function placeOrder(method) {
    const btn = method === "cod" ? btnConfirmCOD : btnConfirmQR;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang xử lý...';

    // Kiểm tra GC_ITEMS và GC_TOTAL được truyền từ PHP
    if (!Array.isArray(GC_ITEMS) || GC_ITEMS.length === 0) {
      alert("Giỏ hàng trống, vui lòng thử lại.");
      resetBtn(btn, method);
      return;
    }

    const payload = {
      guest_name: guestInfo.name,
      guest_phone: guestInfo.phone,
      guest_email: guestInfo.email || "",
      guest_city: guestInfo.city,
      guest_address: guestInfo.address,
      note: guestInfo.note || "",
      payment_method: method,
      items: GC_ITEMS,
      total: GC_TOTAL,
    };

    fetch("/WEB_GR4/orders/guest-place", {
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
        if (data.success) {
          onOrderSuccess(data, method);
        } else {
          alert(data.message || "Có lỗi xảy ra, vui lòng thử lại.");
          resetBtn(btn, method);
        }
      })
      .catch(function (err) {
        console.error("placeOrder error:", err);
        alert("Lỗi kết nối, vui lòng thử lại.");
        resetBtn(btn, method);
      });
  }

  /* ─────────────────── Sau khi đặt hàng thành công ─────────────────── */
  function onOrderSuccess(data, method) {
    const orderId = data.order_id;
    const qrNote = "DATHANG " + orderId;

    // Cập nhật QR với nội dung chuyển khoản chứa mã đơn hàng thật
    if (method === "bank_transfer") {
      if (gcQrNote) gcQrNote.textContent = qrNote;
      if (gcQrImage) {
        // Xây dựng lại URL QR hoàn toàn để tránh lỗi replace chuỗi động
        const amount = parseInt(panel2.dataset.total || GC_TOTAL, 10);
        gcQrImage.src =
          "https://img.vietqr.io/image/MB-0973469734-print.png" +
          "?amount=" +
          amount +
          "&addInfo=" +
          encodeURIComponent(qrNote);
      }
    }

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

  /* ─────────────────── Bind confirm buttons ─────────────────── */
  btnConfirmCOD.addEventListener("click", function () {
    placeOrder("cod");
  });
  btnConfirmQR.addEventListener("click", function () {
    placeOrder("bank_transfer");
  });
})();
