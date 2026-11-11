/* ===== E-MENU SCRIPT — uses the SAME DEMO_PRODUCTS / API.getProducts() as the main website ===== */
const PER = 8; let all = [], cat = "all", q = "", page = 1;
const naira = n => "₦" + n.toLocaleString("en-NG");
const img = p => "../" + p.image;                 // product image paths in data.js are relative to the site root

/* --- Theme: same localStorage key as the main site, so the choice carries over --- */
function applyTheme(t) {
  document.documentElement.dataset.theme = t; localStorage.setItem("joy-theme", t);
  document.querySelector(".theme-toggle i").className = t === "dark" ? "fa-solid fa-lightbulb" : "fa-solid fa-moon";
}
applyTheme(localStorage.getItem("joy-theme") || "dark");
document.querySelector(".theme-toggle").onclick = () => applyTheme(document.documentElement.dataset.theme === "dark" ? "light" : "dark");

/* --- Render: filter by category + search, then paginate; draws both the table and the card list --- */
function render() {
  const list = all.filter(p => (cat === "all" || p.category.toLowerCase() === cat) &&
    (p.name + " " + p.description).toLowerCase().includes(q));
  const pages = Math.max(1, Math.ceil(list.length / PER)); page = Math.min(page, pages);
  const rows = list.slice((page-1)*PER, page*PER);
  const badge = p => `<span class="badge ${p.available ? "badge-ok" : "badge-no"}">${p.available ? "Available" : "Sold out"}</span>`;
  const thumb = p => `<img class="em-thumb" src="${img(p)}" alt="${p.name}" loading="lazy" onerror="this.style.opacity='0'">`;
  document.getElementById("tableBody").innerHTML = rows.map(p => `<tr><td>${thumb(p)}</td><td class="fw-bold">${p.name}</td>
    <td class="sec-sub">${p.description}</td><td>${p.category}</td><td class="price">${naira(p.price)}</td><td>${badge(p)}</td></tr>`).join("");
  document.getElementById("cardList").innerHTML = rows.map(p => `<div class="em-card">${thumb(p)}<div class="flex-grow-1">
    <div class="d-flex justify-content-between"><b>${p.name}</b><span class="price">${naira(p.price)}</span></div>
    <div class="sec-sub small">${p.description}</div><div class="mt-1">${badge(p)}</div></div></div>`).join("");
  document.getElementById("empty").hidden = rows.length > 0;     // friendly empty state
  document.getElementById("pager").innerHTML = pages < 2 ? "" : Array.from({length:pages}, (_, i) =>
    `<li class="page-item ${i+1===page?"active":""}"><button class="page-link" data-p="${i+1}">${i+1}</button></li>`).join("");
}

/* --- Startup: show skeleton rows while products load, then build the dynamic categories --- */
(async function init() {
  document.getElementById("cardList").innerHTML = '<div class="skeleton em-skel"></div>'.repeat(4);
  try { all = await API.getProducts(); } catch { document.getElementById("cardList").innerHTML = "<p class='text-center'>Could not load the menu. Please refresh.</p>"; return; }
  const cats = [...(() => { const m = new Map(); all.forEach(p => { const k = p.category.toLowerCase(); if (!m.has(k)) m.set(k, p.category); }); return m.entries(); })()];   // unique, case-insensitive
  const f = document.getElementById("filters");
  f.innerHTML = `<button class="filter-btn active" data-c="all">All</button>` + cats.map(([k, l]) => `<button class="filter-btn" data-c="${k}">${l}</button>`).join("");
  f.onclick = e => { const b = e.target.closest("[data-c]"); if (!b) return; cat = b.dataset.c; page = 1;
    f.querySelectorAll(".filter-btn").forEach(x => x.classList.toggle("active", x === b)); render(); };
  document.getElementById("search").oninput = e => { q = e.target.value.trim().toLowerCase(); page = 1; render(); };
  document.getElementById("pager").onclick = e => { const b = e.target.closest("[data-p]"); if (b) { page = +b.dataset.p; render(); scrollTo({top:0, behavior:"smooth"}); } };
  render();
})();
