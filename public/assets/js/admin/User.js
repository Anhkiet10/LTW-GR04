const modal = document.getElementById("deleteModal");
const deleteForm = document.getElementById("deleteForm");

document.querySelectorAll(".delete-btn").forEach((btn) => {
  btn.addEventListener("click", () => {
    document.getElementById("deleteUserId").value = btn.dataset.id;
    document.getElementById("deleteUserName").textContent = btn.dataset.name;
    modal.style.display = "flex";
  });
});

document.getElementById("cancelDelete").addEventListener("click", () => {
  modal.style.display = "none";
});

modal.addEventListener("click", (e) => {
  if (e.target === modal) modal.style.display = "none";
});

//  Xử lý xóa bằng AJAX, không submit form thật (tránh treo overlay đen)
deleteForm.addEventListener("submit", async (e) => {
  e.preventDefault();

  const submitBtn = deleteForm.querySelector('button[type="submit"]');
  const originalText = submitBtn.textContent;
  submitBtn.disabled = true;
  submitBtn.textContent = "Đang xóa...";

  try {
    const formData = new FormData(deleteForm);
    const res = await fetch(deleteForm.action, {
      method: "POST",
      body: formData,
      headers: { "X-Requested-With": "XMLHttpRequest" },
    });

    // Nếu server trả về redirect (theo sau fetch), lấy URL cuối cùng
    if (res.redirected) {
      window.location.href = res.url;
      return;
    }

    // Nếu server trả JSON { success: true/false, message: "..." }
    let data = null;
    try {
      data = await res.json();
    } catch (_) {
      // Không phải JSON, coi như thành công đơn giản -> reload trang hiện tại
      window.location.reload();
      return;
    }

    if (data && data.success) {
      window.location.reload();
    } else {
      alert(data?.message || "Không thể xóa người dùng này.");
      modal.style.display = "none";
    }
  } catch (err) {
    console.error(err);
    alert("Có lỗi xảy ra khi xóa người dùng. Vui lòng thử lại.");
    modal.style.display = "none";
  } finally {
    submitBtn.disabled = false;
    submitBtn.textContent = originalText;
  }
});
