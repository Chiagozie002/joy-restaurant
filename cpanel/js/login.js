/* ===== LOGIN FORM — client checks are for convenience only; PHP must validate again and own the session ===== */
const lf = document.getElementById("loginForm");
document.getElementById("showPw").onclick = e => {                      // show/hide password
  const p = lf.elements.password, show = p.type === "password"; p.type = show ? "text" : "password";
  e.currentTarget.innerHTML = `<i class="fa-solid ${show ? "fa-eye-slash" : "fa-eye"}"></i>`;
};
lf.addEventListener("submit", async e => {
  e.preventDefault();
  const d = Object.fromEntries(new FormData(lf)); const err = {};
  const id = d.identifier.trim();
  if (!id) err.identifier = "Enter your email or username.";
  else if (id.includes("@") ? !/^\S+@\S+\.\S+$/.test(id) : id.length < 3) err.identifier = "Enter a valid email or username.";
  if (!d.password) err.password = "Enter your password.";
  else if (d.password.length < 8) err.password = "Password must be at least 8 characters.";
  lf.querySelectorAll("[data-err]").forEach(s => s.textContent = err[s.dataset.err] || "");
  if (Object.keys(err).length) return;
  const btn = lf.querySelector("[type=submit]"); if (btn.disabled) return;   // block repeat submits
  btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Signing in…';
  try {
    const r = await AdminAPI.login({identifier:id, password:d.password});
    if (!r.success) throw new Error(r.message);
    showToast(r.message, "success"); setTimeout(() => location.href = "index.html", 600);
  } catch (x) { showToast(x.message || "Network error. Try again.", "error"); btn.disabled = false; btn.textContent = "Log in"; }
});
