/* ===== MAIN SCRIPT: theme, toast, reveal, navbar, menu, contact ===== */
/* --- Theme (persisted in localStorage). Bulb icon = tap for light, moon icon = tap for dark --- */
function applyTheme(t) {
  document.documentElement.dataset.theme = t;
  localStorage.setItem("joy-theme", t);
  document.querySelectorAll(".theme-toggle i").forEach(i => i.className = t === "dark" ? "fa-solid fa-lightbulb" : "fa-solid fa-moon");
}
applyTheme(localStorage.getItem("joy-theme") || "dark");
document.querySelectorAll(".theme-toggle").forEach(b => b.onclick = () => applyTheme(document.documentElement.dataset.theme === "dark" ? "light" : "dark"));

/* --- Global toast: showToast("text","success|error|info"). Same message isn't repeated while visible --- */
function showToast(msg, type = "info") {
  const box = document.getElementById("toastBox");
  if ([...box.children].some(t => t.textContent === msg)) return;
  const t = Object.assign(document.createElement("div"), {className:`toast-joy ${type}`, textContent:msg});
  box.append(t);
  setTimeout(() => { t.classList.add("out"); setTimeout(() => t.remove(), 300); }, 3500);
}

/* --- Preloader + scroll-reveal (IntersectionObserver) --- */
window.addEventListener("load", () => setTimeout(() => document.getElementById("preloader")?.classList.add("done"), 500));
const io = new IntersectionObserver(es => es.forEach(e => { if (e.isIntersecting) { e.target.classList.add("in"); io.unobserve(e.target); } }), {threshold:.15});
document.querySelectorAll(".reveal").forEach(el => io.observe(el));

/* --- Navbar: shadow on scroll + active link by section (home page only) --- */
const nav = document.querySelector(".joy-nav");
addEventListener("scroll", () => nav.classList.toggle("scrolled", scrollY > 20));
const links = document.querySelectorAll("[data-section]");
const spy = new IntersectionObserver(es => es.forEach(e => { if (e.isIntersecting)
  links.forEach(l => l.classList.toggle("active", l.dataset.section === e.target.id)); }), {rootMargin:"-45% 0px -50% 0px"});
document.querySelectorAll("section[id]").forEach(s => spy.observe(s));
document.getElementById("toTop")?.addEventListener("click", () => scrollTo({top:0}));
/* Close the mobile offcanvas after tapping a link */
document.querySelectorAll("#mobileNav a[href^='#']").forEach(a => a.addEventListener("click", () =>
  bootstrap.Offcanvas.getInstance(document.getElementById("mobileNav"))?.hide()));

/* --- Menu: dynamic categories (case-insensitive), filter, pagination (6 per page) --- */
const PER_PAGE = 6; let products = [], cat = "all", page = 1;
const naira = n => "₦" + n.toLocaleString("en-NG");
function renderMenu() {
  const grid = document.getElementById("menuGrid");
  const list = products.filter(p => cat === "all" || p.category.toLowerCase() === cat);
  const pages = Math.max(1, Math.ceil(list.length / PER_PAGE));
  grid.classList.add("fading");
  setTimeout(() => {
    grid.innerHTML = list.slice((page-1)*PER_PAGE, page*PER_PAGE).map(p => `
      <div class="col-md-6 col-lg-4"><article class="card-joy">
        <div class="card-img"><img src="${p.image}" alt="${p.name}" loading="lazy" onerror="this.style.display='none'"><span class="chip">${p.category}</span></div>
        <div class="p-3"><div class="d-flex justify-content-between align-items-start"><h3 class="h5">${p.name}</h3><span class="price">${naira(p.price)}</span></div>
        <p class="sec-sub small mb-2">${p.description}</p>
        <span class="badge ${p.available ? "badge-ok" : "badge-no"}">${p.available ? "Available" : "Sold out"}</span></div></article></div>`).join("") || `<p class="text-center">No dishes found.</p>`;
    document.getElementById("menuPager").innerHTML = pages < 2 ? "" : Array.from({length:pages}, (_, i) =>
      `<li class="page-item ${i+1===page?"active":""}"><button class="page-link" data-p="${i+1}">${i+1}</button></li>`).join("");
    grid.classList.remove("fading");
  }, 200);
}
async function initMenu() {
  const grid = document.getElementById("menuGrid"); if (!grid) return;
  grid.innerHTML = '<div class="col-md-6 col-lg-4"><div class="skeleton"></div></div>'.repeat(3);   // skeleton cards
  try { products = await API.getProducts(); } catch { grid.innerHTML = "<p class='text-center'>Could not load the menu.</p>"; return; }
  const cats = [...(() => { const m = new Map(); products.forEach(p => { const k = p.category.toLowerCase(); if (!m.has(k)) m.set(k, p.category); }); return m.entries(); })()];  // unique, case-insensitive
  document.getElementById("menuFilters").innerHTML = `<button class="filter-btn active" data-c="all">All</button>` +
    cats.map(([k, label]) => `<button class="filter-btn" data-c="${k}">${label}</button>`).join("");
  document.getElementById("menuFilters").onclick = e => { const b = e.target.closest("[data-c]"); if (!b) return;
    cat = b.dataset.c; page = 1; document.querySelectorAll(".filter-btn").forEach(x => x.classList.toggle("active", x === b)); renderMenu(); };
  document.getElementById("menuPager").onclick = e => { const b = e.target.closest("[data-p]"); if (b) { page = +b.dataset.p; renderMenu(); } };
  renderMenu();
}
initMenu();

/* --- Contact form: client validation (PHP must re-validate), blocks double submits --- */
const cf = document.getElementById("contactForm");
cf?.addEventListener("submit", async e => {
  e.preventDefault();
  const d = Object.fromEntries(new FormData(cf)); const err = {};
  if (d.name.trim().length < 2) err.name = "Enter your full name.";
  if (!/^\S+@\S+\.\S+$/.test(d.email)) err.email = "Enter a valid email address.";
  if (d.subject.trim().length < 3) err.subject = "Enter a subject.";
  if (d.message.trim().length < 10) err.message = "Message must be at least 10 characters.";
  cf.querySelectorAll("[data-err]").forEach(s => s.textContent = err[s.dataset.err] || "");
  if (Object.keys(err).length) return;
  const btn = cf.querySelector("button"); if (btn.disabled) return;
  btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Sending…';
  try { const r = await API.sendContact(d); showToast(r.message, r.success ? "success" : "error"); if (r.success) cf.reset(); }
  catch { showToast("Network error. Please try again.", "error"); }
  btn.disabled = false; btn.textContent = "Send message";
});
