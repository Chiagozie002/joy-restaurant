/* ===== PROFILE PAGE: details form + password form. PHP must re-validate and hash the new password. ===== */
const pf = document.getElementById("profileForm"), pw = document.getElementById("pwForm");
const PHONE = /^(\+?234|0)[789][01]\d{8}$/;
(async () => { const p = await AdminAPI.getProfile(); ["name","email","phone","title"].forEach(k => pf.elements[k].value = p[k] || "");
  if (p.avatar) { const a = document.getElementById("avatarPrev"); a.src = p.avatar; a.hidden = false; } })();
bindPreview(pf.elements.image, document.getElementById("avatarPrev"));
const lock = (btn, on, label) => { btn.disabled = on; btn.innerHTML = on ? '<span class="spinner-border spinner-border-sm"></span> Saving…' : label; };
pf.addEventListener("submit", async e => {
  e.preventDefault(); const d = Object.fromEntries(new FormData(pf)), file = pf.elements.image.files[0], err = {};
  if (d.name.trim().length < 2) err.name = "Enter your full name.";
  if (!/^\S+@\S+\.\S+$/.test(d.email)) err.email = "Enter a valid email address.";
  if (!PHONE.test(d.phone.replace(/\s/g, ""))) err.phone = "Enter a valid Nigerian phone number.";
  if (d.title.trim().length < 2) err.title = "Enter your professional title.";
  if (file && (!IMG_TYPES.includes(file.type) || file.size > MAX_IMG)) err.image = "Use a JPG, PNG or WEBP image up to 2MB.";
  showErrors(pf, err); if (Object.keys(err).length) return;
  const btn = pf.querySelector("[type=submit]"); if (btn.disabled) return; lock(btn, true);
  const p = {name:d.name.trim(), email:d.email.trim(), phone:d.phone.trim(), title:d.title.trim()}; if (file) p.avatar = await readFile(file);
  const r = await AdminAPI.updateProfile(p); showToast(r.message, r.success ? "success" : "error"); lock(btn, false, "Save changes");
});
pw.addEventListener("submit", async e => {
  e.preventDefault(); const d = Object.fromEntries(new FormData(pw)), err = {};
  if (!d.current) err.current = "Enter your current password.";
  if (d.newPw.length < 8 || !/[A-Z]/.test(d.newPw) || !/\d/.test(d.newPw)) err.newPw = "Use 8+ characters with an uppercase letter and a number.";
  if (d.confirm !== d.newPw) err.confirm = "Passwords do not match.";
  showErrors(pw, err); if (Object.keys(err).length) return;
  const btn = pw.querySelector("[type=submit]"); if (btn.disabled) return; lock(btn, true);
  const r = await AdminAPI.updatePassword({current:d.current, password:d.newPw}); showToast(r.message, r.success ? "success" : "error");
  lock(btn, false, "Change password"); if (r.success) pw.reset();
});
