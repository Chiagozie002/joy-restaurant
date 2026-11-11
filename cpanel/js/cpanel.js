/* ===== CPANEL SHARED SCRIPT: theme, toast, active link, logout confirmation ===== */
/* --- Theme (same key as the public site). Login page is dark-only so it never loads this toggle. --- */
function applyTheme(t) {
  document.documentElement.dataset.theme = t; localStorage.setItem("joy-theme", t);
  document.querySelectorAll(".theme-toggle i").forEach(i => i.className = t === "dark" ? "fa-solid fa-lightbulb" : "fa-solid fa-moon");
}
if (!document.body.classList.contains("auth-page")) applyTheme(localStorage.getItem("joy-theme") || "dark");
document.querySelectorAll(".theme-toggle").forEach(b => b.onclick = () => applyTheme(document.documentElement.dataset.theme === "dark" ? "light" : "dark"));

/* --- Toast: showToast(message, "success" | "error" | "info"); identical messages are not stacked --- */
function showToast(msg, type = "info") {
  const box = document.getElementById("toastBox");
  if ([...box.children].some(t => t.textContent === msg)) return;
  const t = Object.assign(document.createElement("div"), {className:`toast-joy ${type}`, textContent:msg}); box.append(t);
  setTimeout(() => { t.classList.add("out"); setTimeout(() => t.remove(), 300); }, 3500);
}

/* --- Highlight the sidebar link that matches the current page --- */
const here = location.pathname.split("/").pop() || "index.html";
document.querySelectorAll(".cp-side a[href]").forEach(a => a.classList.toggle("active", a.getAttribute("href") === here));

/* --- Logout: confirm first, then call the API --- */
document.querySelectorAll("[data-logout]").forEach(b => b.onclick = () => bootstrap.Modal.getOrCreateInstance("#logoutModal").show());
document.getElementById("confirmLogout")?.addEventListener("click", async e => {
  e.target.disabled = true; await AdminAPI.logout(); location.href = "login.html";
});
