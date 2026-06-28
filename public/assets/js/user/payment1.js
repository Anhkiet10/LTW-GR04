const container = document.getElementById("paymentContainer");
const totalAmount = container ? parseInt(container.dataset.total) || 0 : 0;
const IS_BUYNOW = container ? container.dataset.buynow === "1" : false;

// ── Endpoint tuỳ theo luồng ──────────────────────────────────
const PLACE_ORDER_URL = IS_BUYNOW
  ? "/WEB_GR4/orders/buynow-place"
  : "/WEB_GR4/cart/placeOrder";

// ── Kiểm tra selected_ids (chỉ với luồng giỏ hàng) ──────────
const SELECTED_IDS = IS_BUYNOW
  ? []
  : JSON.parse(sessionStorage.getItem("pendingCartIds") || "[]");

if (!IS_BUYNOW && SELECTED_IDS.length === 0) {
  alert("Không có sản phẩm nào được chọn. Đang quay về giỏ hàng...");
  window.location.href = "/WEB_GR4/cart";
}

// ── Payload tuỳ theo luồng ───────────────────────────────────
function buildPayload() {
  return IS_BUYNOW ? {} : { selected_ids: SELECTED_IDS };
}

// ── Chuyển tab phương thức ───────────────────────────────────
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

// ================================================================
// QR / Chuyển khoản
// ================================================================
document
  .getElementById("btnConfirmQR")
  .addEventListener("click", async function () {
    const btn = this;
    btn.disabled = true;
    btn.innerText = "Đang tạo đơn hàng...";

    try {
      const orderRes = await fetch(PLACE_ORDER_URL, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(buildPayload()),
      });
      const orderData = await orderRes.json();

      if (!orderData.success) {
        alert(orderData.message || "Có lỗi khi tạo đơn hàng!");
        btn.disabled = false;
        btn.innerText = "Xác nhận đặt hàng & Chuyển khoản";
        return;
      }

      const orderId = orderData.order_id;
      if (!IS_BUYNOW) sessionStorage.removeItem("pendingCartIds");

      // Cập nhật QR với mã đơn hàng thực
      const qrNoteText = "WEBGR4 DH" + orderId;
      document.getElementById("qrNote").innerText = qrNoteText;
      document.getElementById("qrImage").src =
        "https://img.vietqr.io/image/MB-0973469734-print.png?amount=" +
        totalAmount +
        "&addInfo=" +
        encodeURIComponent(qrNoteText);

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
            alert(payData.message || "Có lỗi xảy ra!");
            newBtn.disabled = false;
            newBtn.innerText = "✓ Tôi đã chuyển khoản xong";
          }
        } catch (e) {
          alert("Lỗi kết nối hệ thống!");
          newBtn.disabled = false;
          newBtn.innerText = "✓ Tôi đã chuyển khoản xong";
        }
      });
    } catch (error) {
      alert("Lỗi kết nối hệ thống!");
      btn.disabled = false;
      btn.innerText = "Xác nhận đặt hàng & Chuyển khoản";
    }
  });

// ================================================================
// COD
// ================================================================
document
  .getElementById("btnConfirmCOD")
  .addEventListener("click", async function () {
    const btn = this;
    btn.disabled = true;
    btn.innerText = "Đang xử lý...";

    try {
      const orderRes = await fetch(PLACE_ORDER_URL, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(buildPayload()),
      });
      const orderData = await orderRes.json();

      if (!orderData.success) {
        alert(orderData.message || "Có lỗi khi tạo đơn hàng!");
        btn.disabled = false;
        btn.innerText = "Xác nhận đặt hàng COD";
        return;
      }

      const orderId = orderData.order_id;
      if (!IS_BUYNOW) sessionStorage.removeItem("pendingCartIds");

      // Ghi nhận phương thức COD
      const payRes = await fetch("/WEB_GR4/orders/confirmPayment", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ order_id: orderId, method: "cod" }),
      });
      const payData = await payRes.json();

      if (payData.success) {
        window.location.href = "/WEB_GR4/orders/" + orderId;
      } else {
        alert(payData.message || "Có lỗi xảy ra!");
        btn.disabled = false;
        btn.innerText = "Xác nhận đặt hàng COD";
      }
    } catch (e) {
      alert("Lỗi kết nối hệ thống!");
      btn.disabled = false;
      btn.innerText = "Xác nhận đặt hàng COD";
    }
  });

// ── Nút Hủy ─────────────────────────────────────────────────
document.getElementById("btnCancel").addEventListener("click", function (e) {
  e.preventDefault();
  if (!IS_BUYNOW) sessionStorage.removeItem("pendingCartIds");
  window.location.href = IS_BUYNOW ? "/WEB_GR4/products" : "/WEB_GR4/cart";
});
