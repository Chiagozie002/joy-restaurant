/* ===== RESERVATIONS PAGE: search, date + status filters, details modal, status update ===== */
let all = [], cur = null; const rows = document.getElementById("rows");
const f = {q:"", date:"", status:""};
async function load() {
  rows.innerHTML = '<tr><td colspan="10"><div class="skeleton" style="height:220px"></div></td></tr>';
  try { all = await AdminAPI.getReservations(); } catch { rows.innerHTML = '<tr><td colspan="10">Could not load reservations.</td></tr>'; return; }
  render();
}
function render() {
  const list = all.filter(r => (!f.status || r.status === f.status) && (!f.date || r.date === f.date) &&
    (r.ref + r.name + r.email + r.phone).toLowerCase().includes(f.q));
  rows.innerHTML = list.map(r => `<tr><td>${r.ref}</td><td>${r.name}</td><td>${r.email}</td><td>${r.phone}</td><td>${r.guests}</td><td>${r.date}</td><td>${r.time}</td>
    <td><span class="badge st-${r.status}">${r.status}</span></td><td><button class="btn btn-sm btn-outline-warning" data-view="${r.ref}">View</button></td></tr>`).join("") ||
    '<tr><td colspan="9" class="text-center sec-sub py-4">No reservations match your filters.</td></tr>';
}
document.getElementById("search").oninput = e => { f.q = e.target.value.trim().toLowerCase(); render(); };
document.getElementById("dateF").onchange = e => { f.date = e.target.value; render(); };
document.getElementById("statusF").onchange = e => { f.status = e.target.value; render(); };
rows.onclick = e => { const b = e.target.closest("[data-view]"); if (!b) return; cur = all.find(r => r.ref === b.dataset.view);
  document.getElementById("detail").innerHTML = `<dl class="row mb-0"><dt class="col-4">Reference</dt><dd class="col-8">${cur.ref}</dd><dt class="col-4">Name</dt><dd class="col-8">${cur.name}</dd>
  <dt class="col-4">Email</dt><dd class="col-8">${cur.email}</dd><dt class="col-4">Phone</dt><dd class="col-8">${cur.phone}</dd><dt class="col-4">Guests</dt><dd class="col-8">${cur.guests}</dd>
  <dt class="col-4">When</dt><dd class="col-8">${cur.date} at ${cur.time}</dd></dl>`;
  document.getElementById("newStatus").value = cur.status; bootstrap.Modal.getOrCreateInstance("#viewModal").show(); };
document.getElementById("saveStatus").onclick = async e => {
  const s = document.getElementById("newStatus").value; if (s === cur.status) return showToast("Status is unchanged.", "info");
  e.target.disabled = true; const r = await AdminAPI.updateReservationStatus(cur.ref, s); e.target.disabled = false;
  showToast(r.message, r.success ? "success" : "error"); if (r.success) { bootstrap.Modal.getInstance("#viewModal").hide(); load(); }
};
load();
