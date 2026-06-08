let currentOrderId = null;

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
    alert("Vui lòng chọn trạng thái");
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
        alert("Cập nhật trạng thái thành công");
        location.reload();
      } else {
        alert("Lỗi: " + data.message);
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("Có lỗi xảy ra");
    });
}

// Close modal when clicking outside
document.getElementById("statusModal").addEventListener("click", function (e) {
  if (e.target === this) {
    closeStatusModal();
  }
});
