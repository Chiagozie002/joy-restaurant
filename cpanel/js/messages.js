/* ===== MESSAGES INBOX: search, read/unread filter, view (marks read), delete, delete all — all deletes confirm first ===== */
let all = [], q = "", flt = "", cur = null, delTarget = null; const box = document.getElementById("rows");
async function load() {
  box.innerHTML = '<tr><td colspan="6"><div class="skeleton" style="height:200px"></div></td></tr>';
  try { all = await AdminAPI.getMessages(); } catch { box.innerHTML = '<tr><td colspan="6">Could not load messages.</td></tr>'; return; }
  render();
}
function render() {
  const list = all.filter(m => (!flt || (flt === "read") === m.read) && (m.name + m.email + m.subject).toLowerCase().includes(q));
  box.innerHTML = list.map(m => `<tr class="${m.read ? "" : "fw-bold"}"><td>${m.name}</td><td>${m.email}</td><td>${m.subject}</td><td>${m.date}</td>
    <td><span class="badge ${m.read ? "bg-secondary" : "badge-ok"}">${m.read ? "Read" : "Unread"}</span></td>
    <td class="text-nowrap"><button class="btn btn-sm btn-outline-warning" data-view="${m.id}"><i class="fa-solid fa-eye"></i></button>
    <button class="btn btn-sm btn-outline-danger" data-del="${m.id}"><i class="fa-solid fa-trash"></i></button></td></tr>`).join("") ||
    '<tr><td colspan="6" class="text-center sec-sub py-4">No messages found.</td></tr>';
}
document.getElementById("search").oninput = e => { q = e.target.value.trim().toLowerCase(); render(); };
document.getElementById("readF").onchange = e => { flt = e.target.value; render(); };
box.onclick = async e => {
  const v = e.target.closest("[data-view]"), d = e.target.closest("[data-del]");
  if (v) { cur = all.find(m => m.id == v.dataset.view);
    document.getElementById("msgBody").innerHTML = `<h3 class="h6">${cur.subject}</h3><p class="sec-sub small">From ${cur.name} (${cur.email}) on ${cur.date}</p><p style="white-space:pre-wrap"></p>`;
    document.querySelector("#msgBody p:last-child").textContent = cur.message;      // textContent: message text is user input, never inject as HTML
    bootstrap.Modal.getOrCreateInstance("#viewModal").show();
    if (!cur.read) { await AdminAPI.markMessageRead(cur.id); cur.read = true; render(); } }     // opening a message marks it read
  if (d) { delTarget = +d.dataset.del; document.getElementById("confirmText").textContent = "Delete this message? This cannot be undone."; bootstrap.Modal.getOrCreateInstance("#confirmModal").show(); }
};
document.getElementById("delAll").onclick = () => { delTarget = "all"; document.getElementById("confirmText").textContent = "Delete ALL messages? This cannot be undone."; bootstrap.Modal.getOrCreateInstance("#confirmModal").show(); };
document.getElementById("confirmOk").onclick = async e => {
  e.target.disabled = true; const r = delTarget === "all" ? await AdminAPI.deleteAllMessages() : await AdminAPI.deleteMessage(delTarget); e.target.disabled = false;
  showToast(r.message, r.success ? "success" : "error"); bootstrap.Modal.getInstance("#confirmModal").hide(); load();
};
load();
