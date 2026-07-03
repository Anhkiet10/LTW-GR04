const container = document.getElementById("paymentContainer");
const totalAmount = container ? parseInt(container.dataset.total) || 0 : 0;
const IS_BUYNOW = container ? container.dataset.buynow === "1" : false;

const PLACE_ORDER_URL = IS_BUYNOW
  ? "/WEB_GR4/orders/buynow-place"
  : "/WEB_GR4/cart/placeOrder";
const SELECTED_IDS = IS_BUYNOW
  ? []
  : JSON.parse(sessionStorage.getItem("pendingCartIds") || "[]");

if (!IS_BUYNOW && SELECTED_IDS.length === 0) {
  showToast(
    "Không có sản phẩm nào được chọn. Đang quay về giỏ hàng...",
    "error",
  );
  setTimeout(function () {
    window.location.href = "/WEB_GR4/cart";
  }, 1200);
}

//  Payload tuỳ theo luồng
function buildPayload() {
  return IS_BUYNOW ? {} : { selected_ids: SELECTED_IDS };
}

// Tạo đơn hàng — CHỈ MỘT LẦN DUY NHẤT, và CHỈ khi người dùng chủ động
// bấm nút xác nhận (QR hoặc COD). Không còn tạo đơn khi trang vừa
// load hay khi chỉ đổi qua tab QR để xem — nếu người dùng rời trang
// mà không bấm xác nhận thì KHÔNG có đơn `pending` nào bị bỏ lại.
// ensureOrderCreated() cache lại promise/order_id để không tạo trùng
// đơn nếu người dùng bấm nút nhiều lần hoặc dùng chung giữa 2 luồng
// QR/COD (vd: lỡ tạo đơn ở QR rồi lại bấm COD).

let orderCreationPromise = null;
let createdOrderId = null;

const qrImageEl = document.getElementById("qrImage");
const qrLoadingEl = document.getElementById("qrLoading");
const qrNoteEl = document.getElementById("qrNote");
const btnConfirmQR = document.getElementById("btnConfirmQR");
const btnConfirmCOD = document.getElementById("btnConfirmCOD");

function updateQrForOrder(orderId) {
  const qrNoteText = "WEBGR4 DH" + orderId;
  if (qrNoteEl) qrNoteEl.innerText = qrNoteText;
  if (qrImageEl) {
    qrImageEl.src =
      "https://img.vietqr.io/image/MB-0973469734-print.png?amount=" +
      totalAmount +
      "&addInfo=" +
      encodeURIComponent(qrNoteText);
    qrImageEl.classList.remove("hidden");
  }
  if (qrLoadingEl) qrLoadingEl.classList.add("hidden");
  if (btnConfirmQR && !btnConfirmQR.dataset.locked) {
    btnConfirmQR.disabled = false;
    btnConfirmQR.innerText = "Xác nhận đặt hàng & Chuyển khoản";
  }
}

function ensureOrderCreated() {
  if (createdOrderId) return Promise.resolve(createdOrderId);
  if (orderCreationPromise) return orderCreationPromise;

  orderCreationPromise = fetch(PLACE_ORDER_URL, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(buildPayload()),
  })
    .then(function (res) {
      return res.json();
    })
    .then(function (data) {
      if (!data.success) {
        orderCreationPromise = null; // cho phép thử lại
        throw new Error(data.message || "Có lỗi khi tạo đơn hàng!");
      }
      createdOrderId = data.order_id;
      if (!IS_BUYNOW) sessionStorage.removeItem("pendingCartIds");
      updateQrForOrder(createdOrderId);
      return createdOrderId;
    })
    .catch(function (err) {
      orderCreationPromise = null;
      if (qrNoteEl) qrNoteEl.innerText = "Lỗi tạo đơn hàng";
      throw err;
    });

  return orderCreationPromise;
}

//  Chuyển tab phương thức
// Chuyển tab CHỈ đổi giao diện, KHÔNG tạo đơn hàng. Đơn hàng chỉ được
// tạo khi người dùng bấm nút xác nhận (QR hoặc COD) ở bước sau.
document.querySelectorAll(".method-tab").forEach(function (tab) {
  tab.addEventListener("click", function () {
    document.querySelectorAll(".method-tab").forEach(function (t) {
      t.classList.remove("active");
    });
    document.querySelectorAll(".method-panel").forEach(function (p) {
      p.classList.add("hidden");
    });
    this.classList.add("active");
    document
      .getElementById("panel-" + this.dataset.method)
      .classList.remove("hidden");
  });
});

// QR / Chuyển khoản — bấm LẦN 1: đây là lúc DUY NHẤT đơn hàng được
// tạo (không còn tạo sẵn lúc load trang hay lúc chuyển tab nữa),
// sau đó hiện mã QR thật gắn với đơn đó. Bấm LẦN 2 (nút đã đổi
// thành "Tôi đã chuyển khoản xong"): gọi confirmPayment.

if (btnConfirmQR) {
  btnConfirmQR.addEventListener("click", async function () {
    const btn = this;
    btn.disabled = true;
    btn.dataset.locked = "1";
    btn.innerText = "Đang tạo đơn hàng...";
    if (qrLoadingEl) qrLoadingEl.classList.remove("hidden");

    try {
      const orderId = await ensureOrderCreated();

      btn.innerText = "✓ Tôi đã chuyển khoản xong";
      btn.style.backgroundColor = "#22c55e";
      btn.disabled = false;

      // Thay sự kiện cũ
      const newBtn = btn.cloneNode(true);
      btn.parentNode.replaceChild(newBtn, btn);

      newBtn.addEventListener("click", async function () {
        newBtn.disabled = true;
        newBtn.innerText = "Đang ghi nhận...";

        try {
          const payRes = await fetch("/WEB_GR4/orders/confirmPayment", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
              order_id: orderId,
              method: "bank_transfer",
            }),
          });
          const payData = await payRes.json();

          if (payData.success) {
            newBtn.style.display = "none";
            const box = document.createElement("div");
            box.className = "qr-success-box";
            box.innerHTML =
              "<div class='qr-success-icon'><i class='fa-solid fa-circle-check'></i></div>" +
              "<h3>Đã ghi nhận yêu cầu thanh toán!</h3>" +
              "<p>Hệ thống đã nhận được thông báo chuyển khoản của bạn.</p>" +
              "<p><strong>Vui lòng chờ admin xác nhận</strong> — thường trong vòng 15–30 phút.</p>" +
              "<a href='/WEB_GR4/orders/" +
              orderId +
              "' class='btn-view-order'>Xem đơn hàng</a>";
            newBtn.parentNode.insertBefore(box, newBtn);
          } else {
            showToast(payData.message || "Có lỗi xảy ra!", "error");
            newBtn.disabled = false;
            newBtn.innerText = " Tôi đã chuyển khoản xong";
          }
        } catch (e) {
          showToast("Lỗi kết nối hệ thống!", "error");
          newBtn.disabled = false;
          newBtn.innerText = " Tôi đã chuyển khoản xong";
        }
      });
    } catch (error) {
      showToast(error.message || "Lỗi kết nối hệ thống!", "error");
      btn.disabled = false;
      btn.dataset.locked = "";
      btn.innerText = "Tạo đơn hàng & lấy mã QR";
      if (qrLoadingEl) qrLoadingEl.classList.add("hidden");
    }
  });
}

// COD — cũng dùng chung ensureOrderCreated(), sẽ KHÔNG tạo đơn lần 2
// nếu người dùng trước đó đã lỡ mở tab QR (đơn đã tồn tại rồi).

if (btnConfirmCOD) {
  btnConfirmCOD.addEventListener("click", async function () {
    const btn = this;
    btn.disabled = true;
    btn.innerText = "Đang xử lý...";

    try {
      const orderId = await ensureOrderCreated();

      const payRes = await fetch("/WEB_GR4/orders/confirmPayment", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ order_id: orderId, method: "cod" }),
      });
      const payData = await payRes.json();

      if (payData.success) {
        window.location.href = "/WEB_GR4/orders/" + orderId;
      } else {
        showToast(payData.message || "Có lỗi xảy ra!", "error");
        btn.disabled = false;
        btn.innerText = "Xác nhận đặt hàng COD";
      }
    } catch (err) {
      showToast(err.message || "Có lỗi khi tạo đơn hàng!", "error");
      btn.disabled = false;
      btn.innerText = "Xác nhận đặt hàng COD";
    }
  });
}

//  Nút Hủy
const btnCancel = document.getElementById("btnCancel");
if (btnCancel) {
  btnCancel.addEventListener("click", function (e) {
    e.preventDefault();
    if (!IS_BUYNOW) sessionStorage.removeItem("pendingCartIds");
    window.location.href = IS_BUYNOW ? "/WEB_GR4/products" : "/WEB_GR4/cart";
  });
}

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
