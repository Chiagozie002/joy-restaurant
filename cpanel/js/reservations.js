/* ===== RESERVATIONS: premium table with status chips, search, date filter, pagination and quick actions ===== */
const PER = 10; let all = [], f = {q: "", date: "", status: ""}, page = 1, cur = null, pendingCancel = null;
const rows = document.getElementById("rows");
const initials = n => n.trim().split(/\s+/).slice(0, 2).map(w => w[0]).join("").toUpperCase();
const fmtDate = d => new Date(d + "T00:00:00").toLocaleDateString("en-GB", {weekday: "short", day: "numeric", month: "short"});
const fmtTime = t => { const [h, m] = t.split(":").map(Number); return `${h % 12 || 12}:${String(m).padStart(2, "0")} ${h < 12 ? "AM" : "PM"}`; };

async function load() {
  rows.innerHTML = '<tr><td colspan="6"><div class="skeleton" style="height:240px"></div></td></tr>';            // table skeleton
  try { all = await AdminAPI.getReservations(); } catch { rows.innerHTML = '<tr><td colspan="6" class="text-center py-4">Could not load reservations.</td></tr>'; return; }
  render();
}
function render() {
  /* Chip numbers always count everything; the table below applies the filters */
  ["Pending", "Confirmed", "Cancelled"].forEach(s => document.getElementById("c" + s).textContent = all.filter(r => r.status === s).length);
  document.getElementById("cAll").textContent = all.length;
  document.querySelectorAll(".sc-chip").forEach(c => c.classList.toggle("active", c.dataset.st === f.status));
  const list = all.filter(r => (!f.status || r.status === f.status) && (!f.date || r.date === f.date) && (r.ref + r.name + r.email + r.phone).toLowerCase().includes(f.q));
  const pages = Math.max(1, Math.ceil(list.length / PER)); page = Math.min(page, pages);
  const slice = list.slice((page - 1) * PER, page * PER);
  rows.innerHTML = slice.map(r => `<tr>
    <td data-label="Guest"><div class="guest"><span class="avatar-i">${esc(initials(r.name))}</span><div><b>${esc(r.name)}</b><small>${esc(r.email)}<br>${esc(r.phone)}</small></div></div></td>
    <td data-label="When"><div class="when"><span class="pill-date"><i class="fa-regular fa-calendar"></i>${fmtDate(r.date)}</span><span class="pill-time"><i class="fa-regular fa-clock"></i>${fmtTime(r.time)}</span></div></td>
    <td data-label="Party"><span class="party"><i class="fa-solid fa-user-group"></i>${r.guests}</span></td>
    <td data-label="Status"><span class="st-pill st-${esc(r.status)}">${esc(r.status)}</span></td>
    <td data-label="Reference"><code class="ref">${esc(r.ref)}</code></td>
    <td class="actions"><button class="act view" data-act="view" data-ref="${esc(r.ref)}" title="View details" aria-label="View details"><i class="fa-regular fa-eye"></i></button>
      ${r.status !== "Confirmed" ? `<button class="act ok" data-act="Confirmed" data-ref="${esc(r.ref)}" title="Confirm" aria-label="Confirm"><i class="fa-solid fa-check"></i></button>` : ""}
      ${r.status !== "Cancelled" ? `<button class="act no" data-act="Cancelled" data-ref="${esc(r.ref)}" title="Cancel" aria-label="Cancel"><i class="fa-solid fa-xmark"></i></button>` : ""}</td></tr>`).join("") ||
    '<tr><td colspan="6" class="text-center sec-sub py-5"><i class="fa-regular fa-calendar-xmark fs-2 d-block mb-2"></i>No reservations match your filters.</td></tr>';
  document.getElementById("showing").textContent = list.length ? `Showing ${(page - 1) * PER + 1}–${(page - 1) * PER + slice.length} of ${list.length}` : "";
  document.getElementById("pager").innerHTML = pages < 2 ? "" : Array.from({length: pages}, (_, i) => `<li class="page-item ${i + 1 === page ? "active" : ""}"><button class="page-link" data-p="${i + 1}">${i + 1}</button></li>`).join("");
}
/* Filters */
document.getElementById("search").oninput = e => { f.q = e.target.value.trim().toLowerCase(); page = 1; render(); };
document.getElementById("dateF").onchange = e => { f.date = e.target.value; page = 1; render(); };
document.getElementById("clearF").onclick = () => { f = {q: "", date: "", status: ""}; document.getElementById("search").value = ""; document.getElementById("dateF").value = ""; page = 1; render(); };
document.querySelectorAll(".sc-chip").forEach(c => c.onclick = () => { f.status = c.dataset.st; page = 1; render(); });
document.getElementById("pager").onclick = e => { const b = e.target.closest("[data-p]"); if (b) { page = +b.dataset.p; render(); } };

/* Change a status. Cancelling asks first because the customer is emailed. */
async function setStatus(ref, status) {
  const r = await AdminAPI.updateReservationStatus(ref, status);
  showToast(r.message, r.success ? "success" : "error"); if (r.success) load();
}
rows.onclick = e => {
  const b = e.target.closest("[data-act]"); if (!b) return; const r = all.find(x => x.ref === b.dataset.ref), act = b.dataset.act;
  if (act === "view") {
    cur = r;
    document.getElementById("detail").innerHTML = `<dl class="row mb-0"><dt class="col-4">Reference</dt><dd class="col-8">${esc(r.ref)}</dd><dt class="col-4">Name</dt><dd class="col-8">${esc(r.name)}</dd>
      <dt class="col-4">Email</dt><dd class="col-8">${esc(r.email)}</dd><dt class="col-4">Phone</dt><dd class="col-8">${esc(r.phone)}</dd><dt class="col-4">Guests</dt><dd class="col-8">${r.guests}</dd>
      <dt class="col-4">When</dt><dd class="col-8">${fmtDate(r.date)} at ${fmtTime(r.time)}</dd><dt class="col-4">Requests</dt><dd class="col-8">${esc(r.requests || "None")}</dd></dl>`;
    document.getElementById("newStatus").value = r.status; bootstrap.Modal.getOrCreateInstance("#viewModal").show();
  } else if (act === "Cancelled") {
    pendingCancel = r; document.getElementById("confirmText").textContent = `${r.name} (${r.ref}) will be told by email that the reservation is cancelled.`;
    bootstrap.Modal.getOrCreateInstance("#confirmModal").show();
  } else setStatus(r.ref, act);
};
document.getElementById("confirmOk").onclick = async e => {
  e.target.disabled = true; await setStatus(pendingCancel.ref, "Cancelled"); e.target.disabled = false; bootstrap.Modal.getInstance("#confirmModal").hide();
};
document.getElementById("saveStatus").onclick = async e => {
  const s = document.getElementById("newStatus").value; if (s === cur.status) return showToast("Status is unchanged.", "info");
  e.target.disabled = true; await setStatus(cur.ref, s); e.target.disabled = false; bootstrap.Modal.getInstance("#viewModal").hide();
};
load();
