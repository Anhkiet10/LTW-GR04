/* ── Toast ── */
function showToast(msg, type = "success") {
  const icon = type === "success" ? "fa-circle-check" : "fa-circle-xmark";
  const t = document.createElement("div");
  t.className = "toast " + type;
  t.innerHTML = `<i class="fas ${icon}"></i> ${msg}`;
  document.body.appendChild(t);
  setTimeout(() => t.remove(), 2800);
}

/* ── Confirm modal ── */
function showConfirm({
  title,
  message,
  confirmText = "Xác nhận",
  confirmClass = "btn-primary",
  onConfirm,
}) {
  const old = document.getElementById("confirmModal");
  if (old) old.remove();

  const overlay = document.createElement("div");
  overlay.id = "confirmModal";
  overlay.className = "modal-overlay active";
  overlay.innerHTML = `
    <div class="modal" style="max-width:420px;">
      <div class="modal-header">
        <h3>${title}</h3>
        <button class="modal-close" id="confirmClose"><i class="fas fa-times"></i></button>
      </div>
      <div class="modal-body" style="font-size:14px;color:#374151;line-height:1.6;">${message}</div>
      <div class="modal-footer">
        <button class="btn-secondary" id="confirmCancel">Huỷ</button>
        <button class="${confirmClass}" id="confirmOk">${confirmText}</button>
      </div>
    </div>`;

  document.body.appendChild(overlay);

  const close = () => overlay.remove();
  document.getElementById("confirmClose").onclick = close;
  document.getElementById("confirmCancel").onclick = close;
  overlay.addEventListener("click", (e) => {
    if (e.target === overlay) close();
  });
  document.getElementById("confirmOk").onclick = () => {
    close();
    onConfirm();
  };
}

/* ── Payment actions ── */
function approvePayment(orderId) {
  showConfirm({
    title:
      '<i class="fas fa-circle-check" style="color:#16a34a;margin-right:6px;"></i>Duyệt thanh toán',
    message: `Xác nhận đã nhận tiền và duyệt thanh toán cho đơn <strong>#${orderId}</strong>?`,
    confirmText: "Duyệt",
    confirmClass: "btn-approve",
    onConfirm: async () => {
      const res = await fetch("/WEB_GR4/orders/updatePaymentStatus", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ order_id: orderId, payment_status: "paid" }),
      });
      const data = await res.json();
      if (data.success) {
        showToast("Đã duyệt. Đơn hàng chuyển sang Đã xác nhận.", "success");
        setTimeout(() => location.reload(), 1000);
      } else {
        showToast("Lỗi cập nhật!", "error");
      }
    },
  });
}

function rejectPayment(orderId) {
  showConfirm({
    title:
      '<i class="fas fa-circle-xmark" style="color:#dc2626;margin-right:6px;"></i>Từ chối thanh toán',
    message: `Xác nhận từ chối thanh toán cho đơn <strong>#${orderId}</strong>?<br><span style="color:#dc2626;font-size:13px;">⚠️ Đơn hàng sẽ bị huỷ.</span>`,
    confirmText: "Từ chối",
    confirmClass: "btn-reject",
    onConfirm: async () => {
      const res = await fetch("/WEB_GR4/orders/updatePaymentStatus", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ order_id: orderId, payment_status: "failed" }),
      });
      const data = await res.json();
      if (data.success) {
        showToast("Đã từ chối. Đơn hàng đã bị huỷ.", "error");
        setTimeout(() => location.reload(), 1000);
      } else {
        showToast("Lỗi cập nhật!", "error");
      }
    },
  });
}
