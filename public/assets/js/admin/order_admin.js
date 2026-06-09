let currentOrderId = null;

function showToast(msg, type = "success") {
  const t = document.createElement("div");
  t.className = "toast " + type;
  t.textContent = msg;
  document.body.appendChild(t);
  setTimeout(() => t.remove(), 2800);
}

function openStatusModal(orderId, currentStatus) {
  currentOrderId = orderId;
  document.getElementById("statusSelect").value = currentStatus;
  document.getElementById("statusModal").classList.add("active");
}

function closeStatusModal() {
  document.getElementById("statusModal").classList.remove("active");
  currentOrderId = null;
}

function updateOrderStatus() {
  const status = document.getElementById("statusSelect").value;

  if (!status) {
    showToast("Vui lòng chọn trạng thái", "error");
    return;
  }

  fetch("/WEB_GR4/admin/update-order-status", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      orderId: currentOrderId,
      status: status,
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showToast("Cập nhật trạng thái thành công", "success");
        setTimeout(() => location.reload(), 1000);
      } else {
        showToast("Lỗi: " + data.message, "error");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showToast("Có lỗi xảy ra", "error");
    });
}

document.getElementById("statusModal").addEventListener("click", function (e) {
  if (e.target === this) {
    closeStatusModal();
  }
});
