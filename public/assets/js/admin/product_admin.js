// ─── State ───────────────────────────────────────────────────────────────────
let currentDeleteId = null;
let allAttributes = window.PRODUCT_ATTRIBUTES || [];
let selectedAttrColumns = [];

// ─── Filter / Search (server-side: điều hướng URL kèm query string) ──────────
const searchInput = document.getElementById("searchInput");
const filterCat = document.getElementById("filterCategory");
const filterStatus = document.getElementById("filterStatus");
const filterForm = document.getElementById("filterForm");

function navigateWithFilters() {
  const params = new URLSearchParams();
  if (searchInput.value.trim()) params.set("search", searchInput.value.trim());
  if (filterCat.value) params.set("category", filterCat.value);
  if (filterStatus.value) params.set("status", filterStatus.value);
  // Đổi filter luôn quay về trang 1
  const qs = params.toString();
  window.location.href = `/WEB_GR4/admin/products${qs ? "?" + qs : ""}`;
}

// Đổi select (danh mục / trạng thái) → điều hướng ngay
[filterCat, filterStatus].forEach((el) =>
  el?.addEventListener("change", navigateWithFilters),
);

// Gõ tìm kiếm: debounce để không reload theo từng phím gõ
let searchDebounce;
searchInput?.addEventListener("input", () => {
  clearTimeout(searchDebounce);
  searchDebounce = setTimeout(navigateWithFilters, 500);
});

// Nhấn Enter trong ô search → submit ngay, không cần chờ debounce
filterForm?.addEventListener("submit", (e) => {
  e.preventDefault();
  clearTimeout(searchDebounce);
  navigateWithFilters();
});

// ─── Modal helpers ────────────────────────────────────────────────────────────
function openModal(id) {
  document.getElementById(id).classList.add("active");
}
function closeOverlay(id) {
  document.getElementById(id).classList.remove("active");
}

// Đóng modal khi click vào overlay
document.querySelectorAll(".modal-overlay").forEach((el) => {
  el.addEventListener("click", (e) => {
    if (e.target === el) el.classList.remove("active");
  });
});

// ─── Open Add ────────────────────────────────────────────────────────────────
document.getElementById("btnAddProduct")?.addEventListener("click", openAdd);

function openAdd() {
  document.getElementById("modalTitle").textContent = "Thêm sản phẩm mới";
  document.getElementById("productForm").reset();
  document.getElementById("fProductId").value = "";
  document.getElementById("btnSubmit").dataset.action = "store";
  document.getElementById("variantRows").innerHTML = "";
  window._currentImagesByVariant = {};
  window._currentProductId = 0;
  resetAttributeColumns();
  resetImagePreview();
  addVariantRow();
  openModal("productModal");
}

// ─── Open Edit ────────────────────────────────────────────────────────────────
async function openEdit(id) {
  document.getElementById("modalTitle").textContent = "Chỉnh sửa sản phẩm";
  document.getElementById("btnSubmit").dataset.action = "update";

  try {
    const res = await fetch(`/WEB_GR4/admin/products/getProduct?id=${id}`);
    const data = await res.json();

    if (!data.success) {
      showToast(data.message, "error");
      return;
    }

    const p = data.product;
    document.getElementById("fProductId").value = p.product_id;
    document.getElementById("fName").value = p.product_name;
    document.getElementById("fCategory").value = p.category_id ?? "";
    document.getElementById("fDescription").value = p.description ?? "";
    document.getElementById("fIsActive").checked = p.is_active == 1;
    updateToggleLabel();

    // Ảnh preview
    const img = data.images.find((i) => i.is_primary == 1) || data.images[0];
    if (img) {
      const src =
        img.image_url.startsWith("http") || img.image_url.startsWith("data:")
          ? img.image_url
          : "/WEB_GR4/public" + img.image_url;
      setImagePreview(src);
    } else {
      resetImagePreview();
    }

    // Lưu imagesByVariant để addVariantRow() dùng
    window._currentImagesByVariant = data.imagesByVariant || {};
    window._currentProductId = p.product_id;

    // Variants — khôi phục cột thuộc tính từ dữ liệu có sẵn
    if (data.attributeTypes?.length) {
      allAttributes = data.attributeTypes;
    }
    const usedAttrIds = new Set();
    (data.variants || []).forEach((v) => {
      (v.attributes || []).forEach((a) =>
        usedAttrIds.add(parseInt(a.attribute_id, 10)),
      );
    });
    selectedAttrColumns = [...usedAttrIds].sort((a, b) => a - b);
    renderAttributeHeaders();
    updateAttrTypePicker();

    document.getElementById("variantRows").innerHTML = "";
    (data.variants.length ? data.variants : [null]).forEach((v) =>
      addVariantRow(v),
    );

    openModal("productModal");
  } catch (e) {
    showToast("Không thể tải dữ liệu sản phẩm.", "error");
  }
}

function closeModal() {
  closeOverlay("productModal");
}

// ─── Toggle label ─────────────────────────────────────────────────────────────
document
  .getElementById("fIsActive")
  ?.addEventListener("change", updateToggleLabel);
function updateToggleLabel() {
  document.getElementById("toggleLabel").textContent = document.getElementById(
    "fIsActive",
  ).checked
    ? "Đang bán"
    : "Đã ẩn";
}

// ─── Image upload preview ─────────────────────────────────────────────────────
document.getElementById("imagePreview")?.addEventListener("click", () => {
  document.getElementById("fImage").click();
});
document.getElementById("fImage")?.addEventListener("change", function () {
  if (!this.files[0]) return;
  const reader = new FileReader();
  reader.onload = (e) => setImagePreview(e.target.result);
  reader.readAsDataURL(this.files[0]);
});

function setImagePreview(src) {
  const imgEl = document.getElementById("modalImgTarget");
  const hint = document.getElementById("uploadHint");
  if (imgEl && hint) {
    imgEl.src = src;
    imgEl.style.display = "block";
    hint.style.display = "none";
  }
}
function resetImagePreview() {
  const imgEl = document.getElementById("modalImgTarget");
  const hint = document.getElementById("uploadHint");
  if (imgEl && hint) {
    imgEl.src = "";
    imgEl.style.display = "none";
    hint.style.display = "flex";
  }
}

// ─── Attribute columns ────────────────────────────────────────────────────────
function getAttributeById(attrId) {
  return allAttributes.find((a) => a.attribute_id == attrId);
}

function resetAttributeColumns() {
  selectedAttrColumns = [];
  renderAttributeHeaders();
  updateAttrTypePicker();
  const picker = document.getElementById("attrTypePicker");
  if (picker) picker.value = "";
}

function renderAttributeHeaders() {
  const headRow = document.getElementById("variantTableHead");
  if (!headRow) return;

  headRow.querySelectorAll(".attr-col-header").forEach((el) => el.remove());

  const priceTh = headRow.querySelector('[data-col="price"]');
  selectedAttrColumns.forEach((attrId) => {
    const attr = getAttributeById(attrId);
    if (!attr) return;

    const th = document.createElement("th");
    th.className = "attr-col-header";
    th.dataset.attrId = attrId;
    th.innerHTML = `
      <span class="attr-col-header__inner">
        ${escapeHtml(attr.attribute_name)}
        <button type="button" class="attr-col-remove" title="Xóa cột"
                onclick="removeAttributeColumn(${attrId})">&times;</button>
      </span>`;
    headRow.insertBefore(th, priceTh);
  });

  // Đảm bảo cột "Ảnh" luôn hiện trước cột xóa
  let imgTh = headRow.querySelector('th[data-col="img"]');
  if (!imgTh) {
    imgTh = document.createElement("th");
    imgTh.dataset.col = "img";
    imgTh.style.cssText = "width:52px;text-align:center;";
    imgTh.textContent = "Ảnh";
    headRow.appendChild(imgTh); // thêm vào cuối, trước nút xóa
  }
}

function updateAttrTypePicker() {
  const picker = document.getElementById("attrTypePicker");
  if (!picker) return;

  picker.innerHTML = '<option value="">— Chọn thể loại —</option>';
  allAttributes.forEach((attr) => {
    if (selectedAttrColumns.includes(attr.attribute_id)) return;
    const opt = document.createElement("option");
    opt.value = attr.attribute_id;
    opt.textContent = attr.attribute_name;
    picker.appendChild(opt);
  });
}

function addAttributeColumn(attrId) {
  const picker = document.getElementById("attrTypePicker");
  const id = attrId ?? (picker ? parseInt(picker.value, 10) : 0);

  if (!id) {
    showToast("Vui lòng chọn thể loại thuộc tính.", "warning");
    return;
  }
  if (selectedAttrColumns.includes(id)) {
    showToast("Thể loại này đã được thêm.", "warning");
    return;
  }

  selectedAttrColumns.push(id);
  renderAttributeHeaders();
  updateAttrTypePicker();
  if (picker) picker.value = "";

  document.querySelectorAll("#variantRows tr").forEach((tr) => {
    insertAttributeCell(tr, id);
  });
}

function removeAttributeColumn(attrId) {
  selectedAttrColumns = selectedAttrColumns.filter((id) => id !== attrId);
  renderAttributeHeaders();
  updateAttrTypePicker();

  document.querySelectorAll("#variantRows tr").forEach((tr) => {
    tr.querySelector(`td[data-attr-id="${attrId}"]`)?.remove();
  });
}

function buildAttributeSelect(attrId, selectedValueId = "") {
  const attr = getAttributeById(attrId);
  if (!attr) return "";

  let opts = '<option value="">— Chọn —</option>';
  (attr.values || []).forEach((v) => {
    const sel = v.value_id == selectedValueId ? " selected" : "";
    opts += `<option value="${v.value_id}"${sel}>${escapeHtml(v.value_name)}</option>`;
  });

  return `<select name="attr_value[${attrId}][]" class="v-input v-select" data-attr-id="${attrId}">${opts}</select>`;
}

function insertAttributeCell(tr, attrId, selectedValueId = "") {
  const priceTd = tr.querySelector('[data-col="price"]');
  if (!priceTd || tr.querySelector(`td[data-attr-id="${attrId}"]`)) return;

  const td = document.createElement("td");
  td.dataset.attrId = attrId;
  td.innerHTML = buildAttributeSelect(attrId, selectedValueId);
  tr.insertBefore(td, priceTd);
}

function escapeHtml(str) {
  return String(str ?? "")
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;");
}

function syncAttributesData(attributeTypes) {
  allAttributes = attributeTypes || [];
  window.PRODUCT_ATTRIBUTES = allAttributes;
  renderAttributeHeaders();
  updateAttrTypePicker();
  refreshVariantAttributeSelects();
}

function refreshVariantAttributeSelects() {
  document.querySelectorAll("#variantRows tr").forEach((tr) => {
    selectedAttrColumns.forEach((attrId) => {
      const td = tr.querySelector(`td[data-attr-id="${attrId}"]`);
      if (!td) return;
      const select = td.querySelector("select");
      const current = select ? select.value : "";
      td.innerHTML = buildAttributeSelect(attrId, current);
    });
  });
}

// ─── Custom confirm (thay thế window.confirm) ────────────────────────────────
function showConfirm(msg, okLabel = "Xác nhận xóa") {
  return new Promise((resolve) => {
    const overlay = document.getElementById("confirmModal");
    const msgEl = document.getElementById("confirmModalMsg");
    const okBtn = document.getElementById("confirmModalOk");
    const cancelBtn = document.getElementById("confirmModalCancel");
    if (!overlay) {
      resolve(window.confirm(msg));
      return;
    }

    msgEl.innerHTML = msg.replace(/\n/g, "<br>");
    okBtn.innerHTML = `<i class="fas fa-trash"></i> ${okLabel}`;
    overlay.classList.add("active");

    const finish = (result) => {
      overlay.classList.remove("active");
      okBtn.removeEventListener("click", onOk);
      cancelBtn.removeEventListener("click", onCancel);
      overlay.removeEventListener("click", onOverlay);
      resolve(result);
    };
    const onOk = () => finish(true);
    const onCancel = () => finish(false);
    const onOverlay = (e) => {
      if (e.target === overlay) finish(false);
    };

    okBtn.addEventListener("click", onOk);
    cancelBtn.addEventListener("click", onCancel);
    overlay.addEventListener("click", onOverlay);
  });
}

// ─── Attribute manage modal ───────────────────────────────────────────────────
function openAttributeManageModal() {
  renderAttributeManageList();
  openModal("attributeManageModal");
  document.getElementById("newAttributeTypeName")?.focus();
}

function closeAttributeManageModal() {
  closeOverlay("attributeManageModal");
}

function renderAttributeManageList() {
  const container = document.getElementById("attributeManageList");
  if (!container) return;

  if (!allAttributes.length) {
    container.innerHTML =
      '<p class="hint">Chưa có thể loại thuộc tính. Thêm thể loại mới ở trên.</p>';
    return;
  }

  container.innerHTML = allAttributes
    .map((attr) => {
      const tags = (attr.values || []).length
        ? (attr.values || [])
            .map(
              (v) =>
                `<span class="attr-manage-tag">
                  ${escapeHtml(v.value_name)}
                  <button type="button" class="attr-tag-delete" data-value-id="${v.value_id}" title="Xóa giá trị này">&times;</button>
                </span>`,
            )
            .join("")
        : '<span class="attr-manage-tag attr-manage-tag--empty">Chưa có giá trị</span>';

      return `
        <div class="attr-manage-item" data-attr-id="${attr.attribute_id}">
          <div class="attr-manage-item__head">
            <span class="attr-manage-item__name">${escapeHtml(attr.attribute_name)}</span>
            <div style="display:flex;align-items:center;gap:10px;">
              <span class="attr-manage-item__id">ID: ${attr.attribute_id}</span>
              <button type="button" class="btn btn--sm btn--danger btn-delete-attr" data-attr-id="${attr.attribute_id}" data-attr-name="${escapeHtml(attr.attribute_name)}">
                <i class="fas fa-trash"></i> Xóa thể loại
              </button>
            </div>
          </div>
          <div class="attr-manage-values">${tags}</div>
          <div class="attr-manage-add-value">
            <input type="text" class="v-input attr-value-input" placeholder="Thêm giá trị mới (VD: Đỏ, XL...)" data-attr-id="${attr.attribute_id}">
            <button type="button" class="btn btn--sm btn--outline btn-add-attr-value" data-attr-id="${attr.attribute_id}">
              <i class="fas fa-plus"></i> Thêm
            </button>
          </div>
        </div>`;
    })
    .join("");

  // Hủy listener cũ rồi gán mới — không dùng cloneNode để tránh zombie element
  container._controller?.abort();
  container._controller = new AbortController();
  const { signal } = container._controller;

  container.addEventListener(
    "click",
    (e) => {
      // Thêm giá trị
      const addBtn = e.target.closest(".btn-add-attr-value");
      if (addBtn) {
        const attrId = parseInt(addBtn.dataset.attrId, 10);
        const input = container.querySelector(
          `.attr-value-input[data-attr-id="${attrId}"]`,
        );
        if (input) submitNewAttributeValue(attrId, input.value, input);
        return;
      }
      // Xóa thể loại
      const delAttrBtn = e.target.closest(".btn-delete-attr");
      if (delAttrBtn) {
        const attrId = parseInt(delAttrBtn.dataset.attrId, 10);
        const attrName = delAttrBtn.dataset.attrName;
        deleteAttribute(attrId, attrName);
        return;
      }
      // Xóa giá trị (nút × trên tag)
      const delTagBtn = e.target.closest(".attr-tag-delete");
      if (delTagBtn) {
        const valueId = parseInt(delTagBtn.dataset.valueId, 10);
        const valueName =
          delTagBtn.closest(".attr-manage-tag")?.textContent?.trim() ?? "";
        deleteAttributeValue(valueId, valueName);
        return;
      }
    },
    { signal },
  );

  container.addEventListener(
    "keydown",
    (e) => {
      if (e.key !== "Enter") return;
      const input = e.target.closest(".attr-value-input");
      if (!input) return;
      e.preventDefault();
      submitNewAttributeValue(
        parseInt(input.dataset.attrId, 10),
        input.value,
        input,
      );
    },
    { signal },
  );
}

async function submitNewAttributeType() {
  const input = document.getElementById("newAttributeTypeName");
  const name = input?.value.trim() ?? "";
  if (!name) {
    showToast("Nhập tên thể loại thuộc tính.", "warning");
    return;
  }

  const btn = document.getElementById("btnCreateAttributeType");
  if (btn) btn.disabled = true;

  try {
    const res = await fetch("/WEB_GR4/admin/products/createAttribute", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `attribute_name=${encodeURIComponent(name)}`,
    });

    let data;
    try {
      data = await res.json();
    } catch {
      showToast("Không thể đọc phản hồi từ máy chủ.", "error");
      return;
    }

    if (data.success) {
      syncAttributesData(data.attributeTypes);
      renderAttributeManageList();
      if (input) input.value = "";
      showToast(data.message || "Đã thêm thể loại thuộc tính.");
    } else {
      showToast(data.message || "Không thể thêm thể loại thuộc tính.", "error");
    }
  } catch {
    showToast("Không thể thêm thể loại thuộc tính.", "error");
  } finally {
    if (btn) btn.disabled = false;
  }
}

async function submitNewAttributeValue(attrId, valueName, inputEl) {
  const name = String(valueName ?? "").trim();
  if (!name) {
    showToast("Nhập tên giá trị thuộc tính.", "warning");
    return;
  }

  // Disable button để tránh double-submit
  const container = document.getElementById("attributeManageList");
  const btn = container?.querySelector(
    `.btn-add-attr-value[data-attr-id="${attrId}"]`,
  );
  if (btn) btn.disabled = true;

  try {
    const res = await fetch("/WEB_GR4/admin/products/createAttributeValue", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `attribute_id=${attrId}&value_name=${encodeURIComponent(name)}`,
    });

    let data;
    try {
      data = await res.json();
    } catch {
      showToast("Không thể đọc phản hồi từ máy chủ.", "error");
      return;
    }

    if (data.success) {
      syncAttributesData(data.attributeTypes);
      renderAttributeManageList(); // re-render, input cũ bị replace — không cần clear
      showToast(data.message || "Đã thêm giá trị thuộc tính.");
    } else {
      showToast(data.message || "Không thể thêm giá trị thuộc tính.", "error");
      // Re-enable button nếu không re-render
      if (btn) btn.disabled = false;
    }
  } catch {
    showToast("Không thể thêm giá trị thuộc tính.", "error");
    if (btn) btn.disabled = false;
  }
}

async function deleteAttribute(attrId, attrName) {
  const ok = await showConfirm(
    `Xóa thể loại thuộc tính "<strong>${escapeHtml(attrName)}</strong>" và toàn bộ giá trị của nó?\nHành động này không thể hoàn tác.`,
    "Xác nhận xóa",
  );
  if (!ok) return;

  try {
    const res = await fetch("/WEB_GR4/admin/products/deleteAttribute", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `attribute_id=${attrId}`,
    });
    const data = await res.json();
    if (data.success) {
      syncAttributesData(data.attributeTypes);
      renderAttributeManageList();
      showToast(data.message || "Đã xóa thể loại thuộc tính.");
    } else {
      showToast(data.message || "Không thể xóa.", "error");
    }
  } catch {
    showToast("Không thể xóa thể loại thuộc tính.", "error");
  }
}

async function deleteAttributeValue(valueId, valueName) {
  const cleanName = valueName.replace(/\s*×\s*$/, "").trim();
  const ok = await showConfirm(
    `Xóa giá trị "<strong>${escapeHtml(cleanName)}</strong>"?`,
    "Xác nhận xóa",
  );
  if (!ok) return;

  try {
    const res = await fetch("/WEB_GR4/admin/products/deleteAttributeValue", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `value_id=${valueId}`,
    });
    const data = await res.json();
    if (data.success) {
      syncAttributesData(data.attributeTypes);
      renderAttributeManageList();
      showToast(data.message || "Đã xóa giá trị thuộc tính.");
    } else {
      showToast(data.message || "Không thể xóa.", "error");
    }
  } catch {
    showToast("Không thể xóa giá trị thuộc tính.", "error");
  }
}

document
  .getElementById("btnCreateAttributeType")
  ?.addEventListener("click", submitNewAttributeType);

document
  .getElementById("newAttributeTypeName")
  ?.addEventListener("keydown", (e) => {
    if (e.key === "Enter") {
      e.preventDefault();
      submitNewAttributeType();
    }
  });

// ─── Variant rows ─────────────────────────────────────────────────────────────
function addVariantRow(v = null) {
  const tbody = document.getElementById("variantRows");
  const tr = document.createElement("tr");
  const vid = v?.variant_id ?? 0;
  const rowIdx = tbody.querySelectorAll("tr").length; // index của row này
  const sku = v?.sku ?? "";
  const price = v?.price ?? "";
  const stock = v?.stock_quantity ?? "";
  const active = v ? v.is_active == 1 : true;

  const attrMap = {};
  (v?.attributes || []).forEach((a) => {
    attrMap[a.attribute_id] = a.value_id;
  });

  let attrCells = "";
  selectedAttrColumns.forEach((attrId) => {
    attrCells += `<td data-attr-id="${attrId}">${buildAttributeSelect(attrId, attrMap[attrId] ?? "")}</td>`;
  });

  // Xác định URL ảnh hiện tại của variant (nếu có)
  let currentImgUrl = null;
  if (vid > 0 && window._currentImagesByVariant) {
    const url = getImageForVariant(window._currentImagesByVariant, String(vid));
    // Chỉ dùng nếu là ảnh riêng của variant (không phải ảnh chung fallback)
    const variantImgs = window._currentImagesByVariant[String(vid)];
    if (variantImgs && variantImgs.length > 0) {
      currentImgUrl = url;
    }
  }

  const imgSrc = currentImgUrl
    ? currentImgUrl.startsWith("http") || currentImgUrl.startsWith("data:")
      ? currentImgUrl
      : "/WEB_GR4/public" + currentImgUrl
    : null;

  const imgCell = imgSrc
    ? `<img src="${imgSrc}" class="variant-thumb"
            style="width:40px;height:40px;object-fit:cover;border-radius:4px;cursor:pointer;border:2px solid #e5e7eb;"
            title="Nhấn để đổi ảnh biến thể"
            onclick="pickVariantImage(this, ${rowIdx}, ${vid})">`
    : `<div class="variant-img-placeholder"
            style="width:40px;height:40px;border:2px dashed #d1d5db;border-radius:4px;display:flex;align-items:center;justify-content:center;cursor:pointer;color:#9ca3af;font-size:16px;"
            title="Nhấn để thêm ảnh biến thể"
            onclick="pickVariantImage(this, ${rowIdx}, ${vid})">
         <i class="fas fa-image"></i>
       </div>`;

  tr.dataset.rowIdx = rowIdx;
  tr.innerHTML = `
        <td>
            <input type="hidden" name="variant_id[]" value="${vid}">
            <input type="text" name="sku[]" value="${escapeHtml(sku)}" placeholder="VD: IP15-256-DEN" class="v-input">
        </td>
        ${attrCells}
        <td data-col="price"><input type="number" name="price[]" value="${price}" placeholder="0" min="0" class="v-input" required></td>
        <td><input type="number" name="stock[]" value="${stock}" placeholder="0" min="0" class="v-input"></td>
        <td class="text-center">
            <input type="hidden" name="is_active[]" value="${active ? 1 : 0}" class="v-active-hidden">
            <input type="checkbox" value="1" ${active ? "checked" : ""} class="v-checkbox"
                   onchange="this.previousElementSibling.value = this.checked ? 1 : 0">
        </td>
        <td class="text-center" style="width:52px;">${imgCell}</td>
        <td>
            <button type="button" class="action-btn action-btn--delete action-btn--sm"
                    onclick="removeVariantRow(this, ${vid})">
                <i class="fas fa-minus"></i>
            </button>
        </td>`;
  tbody.appendChild(tr);

  // Re-index tất cả rows để giữ rowIdx chính xác
  reindexVariantRows();
}

// Cập nhật lại data-row-idx và onclick sau khi thêm/xóa row
function reindexVariantRows() {
  document.querySelectorAll("#variantRows tr").forEach((tr, i) => {
    tr.dataset.rowIdx = i;
    const placeholder = tr.querySelector(".variant-img-placeholder");
    const thumb = tr.querySelector(".variant-thumb");
    const vid = parseInt(
      tr.querySelector('input[name="variant_id[]"]')?.value ?? 0,
      10,
    );
    if (placeholder)
      placeholder.setAttribute(
        "onclick",
        `pickVariantImage(this, ${i}, ${vid})`,
      );
    if (thumb)
      thumb.setAttribute("onclick", `pickVariantImage(this, ${i}, ${vid})`);
  });
}

// File input ảo để pick file, gán vào hidden input theo row index
function pickVariantImage(triggerEl, rowIdx, vid) {
  const input = document.createElement("input");
  input.type = "file";
  input.accept = "image/jpeg,image/png,image/webp";
  input.onchange = function () {
    const file = this.files[0];
    if (!file) return;

    // Preview ngay lập tức
    const reader = new FileReader();
    reader.onload = (e) => {
      const src = e.target.result;
      const td = triggerEl.closest("td");
      td.innerHTML = `<img src="${src}" class="variant-thumb"
          style="width:40px;height:40px;object-fit:cover;border-radius:4px;cursor:pointer;border:2px solid #6366f1;"
          title="Nhấn để đổi ảnh biến thể"
          onclick="pickVariantImage(this, ${rowIdx}, ${vid})">`;

      // Lưu file vào hidden input để submit cùng form
      // Dùng DataTransfer để gán file vào input[type=file]
      const hiddenName = `variant_image[${rowIdx}]`;
      let fileInput = document.querySelector(`input[name="${hiddenName}"]`);
      if (!fileInput) {
        fileInput = document.createElement("input");
        fileInput.type = "file";
        fileInput.name = hiddenName;
        fileInput.style.display = "none";
        document.getElementById("productForm").appendChild(fileInput);
      }
      const dt = new DataTransfer();
      dt.items.add(file);
      fileInput.files = dt.files;
    };
    reader.readAsDataURL(file);
  };
  input.click();
}

function removeVariantRow(btn, vid) {
  if (document.querySelectorAll("#variantRows tr").length === 1) {
    showToast("Cần ít nhất một biến thể.", "warning");
    return;
  }
  const row = btn.closest("tr");
  if (vid > 0) {
    showConfirm("Xóa biến thể này?", "Xác nhận xóa").then((ok) => {
      if (!ok) return;
      fetch("/WEB_GR4/admin/products/deleteVariant", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `variant_id=${vid}`,
      })
        .then((r) => r.json())
        .then((d) => {
          if (d.success) {
            row.remove();
            showToast(d.message);
          } else showToast(d.message, "error");
        });
    });
  } else {
    row.remove();
  }
}

// ─── Validate variants trước khi submit ──────────────────────────────────────
function validateVariants() {
  const rows = document.querySelectorAll("#variantRows tr");
  const keysSeen = new Set();

  for (const tr of rows) {
    // Build key từ các select attribute trong row này (giống logic PHP buildVariantKey)
    const selects = [...tr.querySelectorAll("select[name^='attr_value']")];
    const valueIds = selects
      .map((s) => parseInt(s.value, 10))
      .filter((v) => v > 0)
      .sort((a, b) => a - b);

    const key = valueIds.length ? valueIds.join("_") : "default";

    if (keysSeen.has(key)) {
      const label = valueIds.length
        ? selects
            .filter((s) => parseInt(s.value, 10) > 0)
            .map((s) => s.options[s.selectedIndex]?.text ?? "")
            .join(", ")
        : "không có thuộc tính";
      return `Có 2 biến thể trùng tổ hợp thuộc tính: "${label}". Vui lòng kiểm tra lại.`;
    }
    keysSeen.add(key);
  }
  return null; // OK
}

// ─── Form submit ──────────────────────────────────────────────────────────────
document
  .getElementById("productForm")
  ?.addEventListener("submit", async function (e) {
    e.preventDefault();
    const action =
      document.getElementById("btnSubmit").dataset.action || "store";
    const btn = document.getElementById("btnSubmit");

    // Validate duplicate variant trước khi gửi lên server
    const variantError = validateVariants();
    if (variantError) {
      showToast(variantError, "error");
      return;
    }

    const fd = new FormData(this);

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang lưu...';

    try {
      const res = await fetch(`/WEB_GR4/admin/products/${action}`, {
        method: "POST",
        body: fd,
      });

      let data;
      try {
        data = await res.json();
      } catch {
        // Server có thể trả về HTML (lỗi PHP) thay vì JSON
        // Nhưng nếu HTTP status 200 thì khả năng đã lưu thành công
        if (res.ok) {
          showToast("Đã lưu sản phẩm (không đọc được phản hồi).", "success");
          closeModal();
          setTimeout(() => location.reload(), 800);
        } else {
          showToast("Có lỗi xảy ra, vui lòng thử lại.", "error");
        }
        return;
      }

      if (
        data.success ||
        data.success === 1 ||
        data.success === "1" ||
        data.success === "true"
      ) {
        showToast(data.message || "Đã lưu sản phẩm.");
        closeModal();
        setTimeout(() => location.reload(), 800); // reload để cập nhật bảng
      } else {
        showToast(data.message || "Không thể lưu sản phẩm.", "error");
      }
    } catch {
      showToast("Có lỗi xảy ra, vui lòng thử lại.", "error");
    } finally {
      btn.disabled = false;
      btn.innerHTML = '<i class="fas fa-save"></i> Lưu sản phẩm';
    }
  });

// ─── Delete ───────────────────────────────────────────────────────────────────
function confirmDelete(id, name) {
  currentDeleteId = id;
  document.getElementById("deleteProductName").textContent = name;
  openModal("deleteModal");
}
function closeDeleteModal() {
  closeOverlay("deleteModal");
}

document
  .getElementById("btnConfirmDelete")
  ?.addEventListener("click", async function () {
    if (!currentDeleteId) return;
    this.disabled = true;
    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xóa...';

    try {
      const res = await fetch("/WEB_GR4/admin/products/delete", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `product_id=${currentDeleteId}`,
      });
      const data = await res.json();

      if (data.success) {
        showToast(data.message, "success");
        closeDeleteModal();

        // Xóa dòng khỏi bảng với animation
        const row = document.querySelector(`tr[data-id="${currentDeleteId}"]`);
        if (row) {
          row.style.transition = "opacity .35s, transform .35s";
          row.style.opacity = "0";
          row.style.transform = "translateX(20px)";
          setTimeout(() => row.remove(), 380);
        }
        currentDeleteId = null;
      } else {
        showToast(data.message, "error");
      }
    } catch {
      showToast("Có lỗi xảy ra trong quá trình kết nối hệ thống.", "error");
    } finally {
      this.disabled = false;
      this.innerHTML = '<i class="fas fa-trash"></i> Xác nhận xóa';
    }
  });

// ─── Upload ảnh biến thể ──────────────────────────────────────────────────────
function triggerVariantImageUpload(imgEl, variantId) {
  const productId = window._currentProductId ?? 0;
  if (!productId || !variantId) return;

  const input = document.createElement("input");
  input.type = "file";
  input.accept = "image/jpeg,image/png,image/webp";
  input.onchange = async function () {
    const file = this.files[0];
    if (!file) return;

    const fd = new FormData();
    fd.append("product_id", productId);
    fd.append("variant_id", variantId);
    fd.append("image", file);

    try {
      const res = await fetch("/WEB_GR4/admin/products/uploadVariantImage", {
        method: "POST",
        body: fd,
      });
      const data = await res.json();
      if (data.success) {
        window._currentImagesByVariant = data.images || {};
        // Cập nhật ảnh thumb ngay tại chỗ
        if (imgEl) {
          const url = getImageForVariant(
            window._currentImagesByVariant,
            variantId,
          );
          imgEl.src =
            url.startsWith("http") ||
            url.startsWith("data:") ||
            url.startsWith("/assets/img/no-image")
              ? url
              : "/WEB_GR4/public" + url;
        }
        showToast(data.message || "Đã cập nhật ảnh biến thể.");
      } else {
        showToast(data.message || "Không thể upload ảnh.", "error");
      }
    } catch {
      showToast("Có lỗi xảy ra khi upload ảnh.", "error");
    }
  };
  input.click();
}

// ─── Toast ────────────────────────────────────────────────────────────────────
function showToast(msg, type = "success") {
  const toast = document.getElementById("toast");
  if (!toast) return;
  toast.textContent = msg;
  toast.className = `toast toast--${type} toast--show`;
  toast.style.display = "flex";
  clearTimeout(toast._timer);
  toast._timer = setTimeout(() => {
    toast.classList.remove("toast--show");
    toast.style.display = "none";
  }, 3000);
}
// imagesByVariant có dạng: { '_common': [...], 12: [...], 13: [...] }
function getImageForVariant(imagesByVariant, variantId) {
  // 1. Ưu tiên ảnh riêng của variant — key luôn là string khi từ JSON
  const variantImgs = imagesByVariant[String(variantId)];
  if (variantImgs && variantImgs.length > 0) {
    const primary = variantImgs.find((img) => img.is_primary == 1);
    return primary ? primary.image_url : variantImgs[0].image_url;
  }
  // 2. Fallback về ảnh chung (variant_id IS NULL)
  const common = imagesByVariant["_common"];
  if (common && common.length > 0) {
    const primary = common.find((img) => img.is_primary == 1);
    return primary ? primary.image_url : common[0].image_url;
  }
  return "/assets/img/no-image.png"; // fallback cuối
}
