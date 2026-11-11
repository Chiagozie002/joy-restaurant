/* ===== TRASH PAGE: restore or permanently delete (with confirmation) ===== */
const box = document.getElementById("trashRows"); let purgeId = null, items = [];
async function load() {
  box.innerHTML = '<tr><td colspan="4"><div class="skeleton" style="height:140px"></div></td></tr>';
  try { items = await AdminAPI.getTrash(); } catch { box.innerHTML = '<tr><td colspan="4">Could not load Trash.</td></tr>'; return; }
  box.innerHTML = items.map(p => `<tr><td class="fw-bold">${p.name}</td><td>${p.category}</td><td class="price">${naira(p.price)}</td>
    <td class="text-nowrap"><button class="btn btn-sm btn-outline-success" data-restore="${p.id}"><i class="fa-solid fa-rotate-left"></i> Restore</button>
    <button class="btn btn-sm btn-outline-danger" data-purge="${p.id}"><i class="fa-solid fa-xmark"></i> Delete forever</button></td></tr>`).join("") ||
    '<tr><td colspan="4" class="text-center sec-sub py-4">Trash is empty. Deleted products appear here.</td></tr>';
}
box.onclick = async e => {
  const r = e.target.closest("[data-restore]"), p = e.target.closest("[data-purge]");
  if (r) { const res = await AdminAPI.setDeleted(+r.dataset.restore, false); showToast(res.message, res.success ? "success" : "error"); load(); }
  if (p) { purgeId = +p.dataset.purge; bootstrap.Modal.getOrCreateInstance("#purgeModal").show(); }
};
document.getElementById("confirmPurge").onclick = async e => {
  e.target.disabled = true; const res = await AdminAPI.purgeProduct(purgeId); e.target.disabled = false;
  showToast(res.message, res.success ? "success" : "error"); bootstrap.Modal.getInstance("#purgeModal").hide(); load();
};
load();
