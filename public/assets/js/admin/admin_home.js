// ── PHP data injected ──
const INIT_CATEGORIES = window.W4ShopData.INIT_CATEGORIES;
const INIT_PRODUCTS = window.W4ShopData.INIT_PRODUCTS;
const AVAIL_PRODUCTS = window.W4ShopData.AVAIL_PRODUCTS;
const AVAIL_CATEGORIES = window.W4ShopData.AVAIL_CATEGORIES;

// ── State ──
// state: [ { id, category_id, category_name, sort_order, products: [ {id, product_id, product_name, price, image_url, sort_order} ] } ]
let state = [];
let changes = 0;
let currentCatIndexForProd = null; // which category we're adding a product to

// ── Build initial state from PHP data ──
function initState() {
  // Sort categories by sort_order
  const cats = [...INIT_CATEGORIES].sort((a, b) => a.sort_order - b.sort_order);
  state = cats.map((cat) => ({
    id: cat.id,
    category_id: cat.category_id,
    category_name: cat.category_name,
    sort_order: cat.sort_order,
    isNew: false,
    products: INIT_PRODUCTS.filter(
      (p) => p.category_id == cat.category_id,
    ).sort((a, b) => a.sort_order - b.sort_order),
  }));
}

initState();

// ── Render ──
function render() {
  const canvas = document.getElementById("builderCanvas");
  canvas.innerHTML = "";

  state.forEach((cat, catIdx) => {
    const block = document.createElement("div");
    block.className = "cat-block";
    block.dataset.catIdx = catIdx;

    // Drag handle for category reordering
    block.draggable = true;
    block.addEventListener("dragstart", (e) => onCatDragStart(e, catIdx));
    block.addEventListener("dragover", (e) => onCatDragOver(e, catIdx));
    block.addEventListener("drop", (e) => onCatDrop(e, catIdx));
    block.addEventListener("dragleave", onCatDragLeave);

    block.innerHTML = `
            <div class="cat-block-header">
                <i class="fas fa-grip-vertical drag-handle"></i>
                <div class="cat-icon"><i class="fas fa-tag"></i></div>
                <span class="cat-name">${esc(cat.category_name)}</span>
                <button class="btn-remove-cat" onclick="removeCategory(${catIdx})">
                    <i class="fas fa-times"></i> Xóa danh mục
                </button>
            </div>
            <div class="cat-products">
                <div class="products-row" id="prodRow-${catIdx}" 
                     ondragover="onProdDragOver(event, ${catIdx})" 
                     ondrop="onProdDrop(event, ${catIdx})"
                     ondragleave="onProdDragLeave(event)">
                    ${cat.products.map((p, pIdx) => renderProdChip(p, catIdx, pIdx)).join("")}
                    ${cat.products.length === 0 ? `<div class="empty-products"><i class="fas fa-info-circle" style="margin-right:6px;"></i>Chưa có sản phẩm được ghim</div>` : ""}
                </div>
                <div style="padding-bottom:14px;">
                    <button class="btn-add-product" onclick="openProdModal(${catIdx})">
                        <i class="fas fa-plus"></i> Thêm sản phẩm
                    </button>
                </div>
            </div>
        `;
    canvas.appendChild(block);
  });

  updateChangeCount();
}

function renderProdChip(p, catIdx, pIdx) {
  const imgHtml = p.image_url
    ? `<img src="/WEB_GR4/public${esc(p.image_url)}" alt="${esc(p.product_name)}">`
    : `<div class="prod-noimg"><i class="fas fa-box-open"></i></div>`;
  const price = Number(p.price).toLocaleString("vi-VN") + "₫";
  return `
        <div class="prod-chip" draggable="true" data-cat="${catIdx}" data-prod="${pIdx}"
             ondragstart="onProdDragStart(event, ${catIdx}, ${pIdx})">
            ${imgHtml}
            <div class="prod-chip-info">
                <div class="prod-chip-name">${esc(p.product_name)}</div>
                <div class="prod-chip-price">${price}</div>
            </div>
            <button class="prod-chip-remove" onclick="removeProduct(${catIdx}, ${pIdx})" title="Xóa">
                <i class="fas fa-times"></i>
            </button>
        </div>`;
}

function esc(str) {
  if (!str) return "";
  return String(str)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;");
}

// ── Category drag-drop ──
let draggedCatIdx = null;
function onCatDragStart(e, idx) {
  draggedCatIdx = idx;
  e.dataTransfer.effectAllowed = "move";
}
function onCatDragOver(e, idx) {
  e.preventDefault();
  if (draggedCatIdx !== idx)
    e.currentTarget.style.outline = "2px solid #a78bfa";
}
function onCatDragLeave(e) {
  e.currentTarget.style.outline = "";
}
function onCatDrop(e, idx) {
  e.currentTarget.style.outline = "";
  if (draggedCatIdx === null || draggedCatIdx === idx) return;
  const moved = state.splice(draggedCatIdx, 1)[0];
  state.splice(idx, 0, moved);
  draggedCatIdx = null;
  changes++;
  render();
}

// ── Product drag-drop within row ──
let draggedProd = null; // {catIdx, pIdx}
function onProdDragStart(e, catIdx, pIdx) {
  draggedProd = { catIdx, pIdx };
  e.stopPropagation();
  e.dataTransfer.effectAllowed = "move";
}
function onProdDragOver(e, catIdx) {
  e.preventDefault();
  e.stopPropagation();
  document.getElementById("prodRow-" + catIdx).classList.add("drag-over");
}
function onProdDragLeave(e) {
  e.currentTarget.classList.remove("drag-over");
}
function onProdDrop(e, catIdx) {
  e.stopPropagation();
  e.currentTarget.classList.remove("drag-over");
  if (!draggedProd) return;
  // Move product to this category
  const moved = state[draggedProd.catIdx].products.splice(
    draggedProd.pIdx,
    1,
  )[0];
  state[catIdx].products.push(moved);
  draggedProd = null;
  changes++;
  render();
}

// ── Add / remove category ──
function openCatModal() {
  document.getElementById("catModal").classList.add("open");
  document.getElementById("catSearch").value = "";
  renderCatList("");
}
function closeCatModal() {
  document.getElementById("catModal").classList.remove("open");
}

function renderCatList(filter) {
  const existing = state.map((c) => c.category_id);
  const items = AVAIL_CATEGORIES.filter(
    (c) =>
      !existing.includes(c.category_id) &&
      c.category_name.toLowerCase().includes(filter.toLowerCase()),
  );
  const el = document.getElementById("catList");
  if (items.length === 0) {
    el.innerHTML =
      '<div style="text-align:center;color:#9ca3af;padding:20px;font-size:13px;">Không còn danh mục nào.</div>';
    return;
  }
  el.innerHTML = items
    .map(
      (c) => `
        <div class="modal-item" onclick="addCategory(${c.category_id}, '${esc(c.category_name)}')">
            <div class="modal-noimg"><i class="fas fa-folder"></i></div>
            <div class="modal-item-info">
                <div class="modal-item-name">${esc(c.category_name)}</div>
            </div>
            <button class="modal-item-add">Thêm</button>
        </div>
    `,
    )
    .join("");
}

function filterCats() {
  renderCatList(document.getElementById("catSearch").value);
}

function addCategory(catId, catName) {
  state.push({
    id: null,
    category_id: catId,
    category_name: catName,
    sort_order: 999,
    isNew: true,
    products: [],
  });
  changes++;
  closeCatModal();
  render();
  showToast('Đã thêm danh mục "' + catName + '"', "success");
}

function removeCategory(catIdx) {
  const name = state[catIdx].category_name;
  state.splice(catIdx, 1);
  changes++;
  render();
  showToast('Đã xóa danh mục "' + name + '"', "success");
}

// ── Add / remove product ──
function openProdModal(catIdx) {
  currentCatIndexForProd = catIdx;
  document.getElementById("prodModal").classList.add("open");
  document.getElementById("prodSearch").value = "";
  renderProdList("");
}
function closeProdModal() {
  document.getElementById("prodModal").classList.remove("open");
  currentCatIndexForProd = null;
}

function renderProdList(filter) {
  // Collect all pinned product_ids
  const pinned = state.flatMap((c) => c.products.map((p) => p.product_id));
  const items = AVAIL_PRODUCTS.filter(
    (p) =>
      !pinned.includes(p.product_id) &&
      p.product_name.toLowerCase().includes(filter.toLowerCase()),
  );
  const el = document.getElementById("prodList");
  if (items.length === 0) {
    el.innerHTML =
      '<div style="text-align:center;color:#9ca3af;padding:20px;font-size:13px;">Không còn sản phẩm nào.</div>';
    return;
  }
  const price = (p) => Number(p.price).toLocaleString("vi-VN") + "₫";
  el.innerHTML = items
    .map(
      (p) => `
        <div class="modal-item" onclick="addProduct(${p.product_id})">
            ${
              p.image_url
                ? `<img src="/WEB_GR4/public${esc(p.image_url)}" style="width:40px;height:40px;border-radius:8px;object-fit:cover;">`
                : `<div class="modal-noimg"><i class="fas fa-box-open"></i></div>`
            }
            <div class="modal-item-info">
                <div class="modal-item-name">${esc(p.product_name)}</div>
                <div class="modal-item-sub">${price(p)}</div>
            </div>
            <button class="modal-item-add">Thêm</button>
        </div>
    `,
    )
    .join("");
}

function filterProds() {
  renderProdList(document.getElementById("prodSearch").value);
}

function addProduct(productId) {
  if (currentCatIndexForProd === null) return;
  const prod = AVAIL_PRODUCTS.find((p) => p.product_id == productId);
  if (!prod) return;
  state[currentCatIndexForProd].products.push({
    id: null,
    product_id: prod.product_id,
    product_name: prod.product_name,
    price: prod.price,
    image_url: prod.image_url,
    sort_order: 999,
    isNew: true,
  });
  changes++;
  closeProdModal();
  render();
  showToast("Đã thêm sản phẩm", "success");
}

function removeProduct(catIdx, pIdx) {
  state[catIdx].products.splice(pIdx, 1);
  changes++;
  render();
}

function updateChangeCount() {
  document.getElementById("changeCount").textContent = changes;
}

// ── Save all ──
async function saveAll() {
  const btn = document.getElementById("saveBtn");
  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang lưu...';

  // Gom cấu trúc dữ liệu chính xác từ state hiện tại
  const payload = {
    // Ánh xạ danh mục kèm theo vị trí (sort_order) dựa vào chỉ số mảng i
    categories: state.map((cat, i) => ({
      category_id: cat.category_id,
      sort_order: i,
    })),
    // Ánh xạ toàn bộ sản phẩm ghim kèm theo vị trí dựa vào chỉ số mảng i
    products: state.flatMap((cat, catIdx) =>
      cat.products.map((prod, pIdx) => ({
        product_id: prod.product_id,
        category_id: cat.category_id,
        sort_order: catIdx * 1000 + pIdx,
      })),
    ),
  };

  try {
    // Gửi duy nhất 1 request mang toàn bộ trạng thái mới lên Backend
    const response = await fetch("/WEB_GR4/admin/save-homepage", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(payload),
    });

    const result = await response.json();

    if (response.ok && result.success) {
      changes = 0;
      showToast("Đã lưu cấu hình trang chủ thành công!", "success");
      setTimeout(() => location.reload(), 1000);
    } else {
      throw new Error(result.message || "Lỗi xử lý phía server");
    }
  } catch (err) {
    showToast("Có lỗi xảy ra: " + err.message, "error");
    console.error(err);
  } finally {
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-save"></i> Lưu thay đổi';
  }
}

// ── Toast ──
function showToast(msg, type = "success") {
  const t = document.createElement("div");
  t.className = "toast " + type;
  t.textContent = msg;
  document.body.appendChild(t);
  setTimeout(() => t.remove(), 2800);
}

// Close modals on overlay click
document.getElementById("catModal").addEventListener("click", function (e) {
  if (e.target === this) closeCatModal();
});
document.getElementById("prodModal").addEventListener("click", function (e) {
  if (e.target === this) closeProdModal();
});

// ── Init ──
render();
