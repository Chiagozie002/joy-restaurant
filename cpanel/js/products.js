/* ===== PRODUCTS PAGE: search, dynamic categories, pagination, edit modal, soft delete ===== */
const PER = 8; let all = [], q = "", cat = "all", page = 1, delId = null;
const body = document.getElementById("rows"), ef = document.getElementById("editForm");
const imgSrc = p => p.image.startsWith("data:") ? p.image : "../" + p.image;      // uploaded demo images are data URLs
async function load() {
  body.innerHTML = '<tr><td colspan="6"><div class="skeleton" style="height:200px"></div></td></tr>';   // table skeleton
  try { all = await AdminAPI.getProducts(); } catch { body.innerHTML = '<tr><td colspan="6">Could not load products.</td></tr>'; return; }
  const cats = [...(() => { const m = new Map(); all.forEach(p => { const k = p.category.toLowerCase(); if (!m.has(k)) m.set(k, p.category); }); return m.entries(); })()];         // unique, case-insensitive
  document.getElementById("filters").innerHTML = `<button class="filter-btn ${cat==="all"?"active":""}" data-c="all">All</button>` + cats.map(([k, l]) => `<button class="filter-btn ${cat===k?"active":""}" data-c="${k}">${l}</button>`).join("");
  render();
}
function render() {
  const list = all.filter(p => (cat === "all" || p.category.toLowerCase() === cat) && p.name.toLowerCase().includes(q));
  const pages = Math.max(1, Math.ceil(list.length / PER)); page = Math.min(page, pages);
  body.innerHTML = list.slice((page-1)*PER, page*PER).map(p => `<tr><td><img class="em-thumb" style="width:56px;height:42px;object-fit:cover;border-radius:8px;background:var(--grad)" src="${imgSrc(p)}" alt="" onerror="this.style.opacity='0'"></td>
    <td class="fw-bold">${p.name}</td><td>${p.category}</td><td class="price">${naira(p.price)}</td>
    <td><span class="badge ${p.available ? "badge-ok" : "badge-no"}">${p.available ? "Available" : "Sold out"}</span></td>
    <td class="text-nowrap"><button class="btn btn-sm btn-outline-warning" data-edit="${p.id}"><i class="fa-solid fa-pen"></i></button>
    <button class="btn btn-sm btn-outline-danger" data-del="${p.id}"><i class="fa-solid fa-trash"></i></button></td></tr>`).join("") || '<tr><td colspan="6" class="text-center sec-sub py-4">No products found.</td></tr>';
  document.getElementById("pager").innerHTML = pages < 2 ? "" : Array.from({length:pages}, (_, i) => `<li class="page-item ${i+1===page?"active":""}"><button class="page-link" data-p="${i+1}">${i+1}</button></li>`).join("");
}
document.getElementById("search").oninput = e => { q = e.target.value.trim().toLowerCase(); page = 1; render(); };
document.getElementById("filters").onclick = e => { const b = e.target.closest("[data-c]"); if (b) { cat = b.dataset.c; page = 1; document.querySelectorAll(".filter-btn").forEach(x => x.classList.toggle("active", x === b)); render(); } };
document.getElementById("pager").onclick = e => { const b = e.target.closest("[data-p]"); if (b) { page = +b.dataset.p; render(); } };
/* Row buttons: open edit modal (filled with the product) or delete confirmation */
body.onclick = e => {
  const ed = e.target.closest("[data-edit]"), de = e.target.closest("[data-del]");
  if (ed) { const p = all.find(x => x.id == ed.dataset.edit); ["id","name","description","price","category"].forEach(k => ef.elements[k].value = p[k]);
    ef.elements.available.value = p.available ? "1" : "0"; ef.elements.image.value = ""; showErrors(ef, {});
    const pv = document.getElementById("editPreview"); pv.src = imgSrc(p); pv.hidden = false; bootstrap.Modal.getOrCreateInstance("#editModal").show(); }
  if (de) { delId = +de.dataset.del; document.getElementById("delName").textContent = all.find(x => x.id == delId).name; bootstrap.Modal.getOrCreateInstance("#delModal").show(); }
};
bindPreview(ef.elements.image, document.getElementById("editPreview"));   // new image preview before saving
ef.addEventListener("submit", async e => {
  e.preventDefault();
  const d = Object.fromEntries(new FormData(ef)), file = ef.elements.image.files[0];
  const err = validateProduct(d, file, false); showErrors(ef, err); if (Object.keys(err).length) return;
  const btn = ef.querySelector("[type=submit]"); if (btn.disabled) return; btn.disabled = true; btn.textContent = "Saving…";
  const p = {id:+d.id, name:d.name.trim(), description:d.description.trim(), price:Number(d.price), category:d.category.trim(), available:d.available === "1"};
  if (file) p.image = await readFile(file);                                  // only replace the image when a new one is chosen
  const r = await AdminAPI.updateProduct(p); showToast(r.message, r.success ? "success" : "error");
  btn.disabled = false; btn.textContent = "Save changes";
  if (r.success) { bootstrap.Modal.getInstance("#editModal").hide(); load(); }
});
document.getElementById("confirmDel").onclick = async e => {                 // soft delete => Trash
  e.target.disabled = true; const r = await AdminAPI.setDeleted(delId, true); e.target.disabled = false;
  showToast(r.message, r.success ? "success" : "error"); bootstrap.Modal.getInstance("#delModal").hide(); load();
};
load();
