/* ===== PASSWORD RECOVERY: forgot-password.html + reset-password.html =====
   The emailed link carries a one-time token. PHP checks it (single use, expires) and sets the new password;
   nothing here is trusted for security. */
const lock = (btn, on, label) => { btn.disabled = on; btn.innerHTML = on ? '<span class="spinner-border spinner-border-sm"></span> Please wait…' : label; };
const errs = (form, e) => form.querySelectorAll("[data-err]").forEach(s => s.textContent = (e && e[s.dataset.err]) || "");

/* --- forgot-password.html: ask for the link --- */
const ff = document.getElementById("forgotForm");
ff?.addEventListener("submit", async e => {
  e.preventDefault(); const email = ff.elements.email.value.trim();
  if (!/^\S+@\S+\.\S+$/.test(email)) return errs(ff, {email: "Enter a valid email address."});
  errs(ff, {}); const btn = ff.querySelector("[type=submit]"); if (btn.disabled) return; lock(btn, true);
  try {
    const r = await AdminAPI.forgotPassword(email);
    if (r.success) { ff.innerHTML = `<div class="text-center py-2"><i class="fa-solid fa-envelope-circle-check fs-1 text-warning mb-2"></i><p class="mb-0"></p></div>`; ff.querySelector("p").textContent = r.message; return; }
    errs(ff, r.errors); showToast(r.message, "error");
  } catch { showToast("Network error. Please try again.", "error"); }
  lock(btn, false, "Send reset link");
});

/* --- reset-password.html: set the new password --- */
const rf = document.getElementById("resetForm");
if (rf) {
  const token = new URLSearchParams(location.search).get("token") || "";
  document.getElementById("showPw").onclick = e => { const p = rf.elements.password, show = p.type === "password"; p.type = show ? "text" : "password"; e.currentTarget.innerHTML = `<i class="fa-solid ${show ? "fa-eye-slash" : "fa-eye"}"></i>`; };
  const bad = () => { rf.hidden = true; document.getElementById("badLink").hidden = false; };
  if (!token) bad(); else AdminAPI.checkResetToken(token).then(ok => { if (!ok) bad(); }).catch(() => {});
  /* Strength meter: length, uppercase, number and a symbol each add to the score */
  const meter = rf.querySelector(".pw-meter span"), hint = document.getElementById("pwHint");
  rf.elements.password.addEventListener("input", () => {
    const v = rf.elements.password.value; const score = [v.length >= 8, /[A-Z]/.test(v), /\d/.test(v), /[^A-Za-z0-9]/.test(v), v.length >= 12].filter(Boolean).length;
    meter.style.width = (v ? score * 20 : 0) + "%"; meter.style.background = ["#d64545", "#d64545", "#d99a1d", "#d99a1d", "#1e9e5a", "#1e9e5a"][score];
    hint.textContent = v ? ["Too weak", "Too weak", "Getting there", "Almost there", "Strong", "Very strong"][score] : "8+ characters, with an uppercase letter and a number.";
  });
  rf.addEventListener("submit", async e => {
    e.preventDefault(); const pw = rf.elements.password.value, err = {};
    if (pw.length < 8 || !/[A-Z]/.test(pw) || !/\d/.test(pw)) err.password = "Use 8+ characters with an uppercase letter and a number.";
    if (rf.elements.confirm.value !== pw) err.confirm = "Passwords do not match.";
    errs(rf, err); if (Object.keys(err).length) return;
    const btn = rf.querySelector("[type=submit]"); if (btn.disabled) return; lock(btn, true);
    try {
      const r = await AdminAPI.resetPassword(token, pw);
      if (r.success) { showToast(r.message, "success"); setTimeout(() => location.href = "login.html", 1400); return; }
      errs(rf, r.errors); showToast(r.message, "error"); if (r.message.includes("expired")) bad();
    } catch { showToast("Network error. Please try again.", "error"); }
    lock(btn, false, "Update password");
  });
}
