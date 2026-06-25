function selectAll() {
  document
    .querySelectorAll(".table-checkbox")
    .forEach((cb) => (cb.checked = true));
}

function deselectAll() {
  document
    .querySelectorAll(".table-checkbox")
    .forEach((cb) => (cb.checked = false));
}

async function startBackup() {
  const checked = [...document.querySelectorAll(".table-checkbox:checked")].map(
    (cb) => cb.value,
  );

  if (checked.length === 0) {
    showAlert("Vui lòng chọn ít nhất một bảng để sao lưu.", "error");
    return;
  }

  const btn = document.getElementById("btn-backup");
  const btnText = document.getElementById("btn-backup-text");
  const progress = document.getElementById("backup-progress");

  btn.disabled = true;
  btnText.textContent = "Đang xử lý...";
  progress.style.display = "flex";
  hideAlert();

  try {
    const response = await fetch("/WEB_GR4/admin/backup/download", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ tables: checked }),
    });

    if (!response.ok) {
      const err = await response
        .json()
        .catch(() => ({ message: "Lỗi không xác định" }));
      throw new Error(err.message || `HTTP ${response.status}`);
    }

    // Lấy tên file từ header nếu có
    const disposition = response.headers.get("Content-Disposition") || "";
    const match = disposition.match(/filename="(.+?)"/);
    const filename = match
      ? match[1]
      : "w4shop_backup_" + formatDate() + ".sql";

    const blob = await response.blob();
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);

    showAlert(
      '<i class="fas fa-check-circle"></i> Sao lưu thành công! File đã được tải xuống.',
      "success",
    );
  } catch (err) {
    showAlert(
      '<i class="fas fa-exclamation-circle"></i> Lỗi: ' + err.message,
      "error",
    );
  } finally {
    btn.disabled = false;
    btnText.textContent = "Tải xuống bản sao lưu";
    progress.style.display = "none";
  }
}

function showAlert(html, type) {
  const el = document.getElementById("backup-alert");
  el.innerHTML = html;
  el.className = "backup-alert " + type;
  el.style.display = "block";
  el.scrollIntoView({ behavior: "smooth", block: "nearest" });
}

function hideAlert() {
  document.getElementById("backup-alert").style.display = "none";
}

function formatDate() {
  const d = new Date();
  return (
    d.getFullYear() +
    String(d.getMonth() + 1).padStart(2, "0") +
    String(d.getDate()).padStart(2, "0") +
    "_" +
    String(d.getHours()).padStart(2, "0") +
    String(d.getMinutes()).padStart(2, "0") +
    String(d.getSeconds()).padStart(2, "0")
  );
}
