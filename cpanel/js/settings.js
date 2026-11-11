/* ===== WEBSITE SETTINGS: one form, four groups. PHP saves these to a settings table; the public site will read them. ===== */
const sf = document.getElementById("settingsForm");
(async () => { const s = await AdminAPI.getSettings(); Object.keys(s).forEach(k => { if (sf.elements[k]) sf.elements[k].value = s[k]; }); })();
bindPreview(sf.elements.logo, document.getElementById("logoPrev"));
sf.addEventListener("submit", async e => {
  e.preventDefault(); const d = Object.fromEntries(new FormData(sf)); delete d.logo; const err = {};
  const file = sf.elements.logo.files[0];
  if (d.name.trim().length < 2) err.name = "Enter the restaurant name.";
  if (d.tagline.trim().length < 3) err.tagline = "Enter a tagline.";
  if (d.story.trim().length < 20) err.story = "Story must be at least 20 characters.";
  if (d.mission.trim().length < 10) err.mission = "Enter the mission.";
  if (d.vision.trim().length < 10) err.vision = "Enter the vision.";
  if (d.address.trim().length < 5) err.address = "Enter the address.";
  if (!/^(\+?234|0)?[\d\s]{9,14}$/.test(d.phone.trim())) err.phone = "Enter a valid phone number.";
  if (!/^\S+@\S+\.\S+$/.test(d.email)) err.email = "Enter a valid email address.";
  if (!d.open || !d.close || d.open >= d.close) err.close = "Closing time must be after opening time.";
  ["facebook","x","instagram","youtube"].forEach(k => { if (d[k] && !/^https?:\/\/\S+$/.test(d[k])) err[k] = "Enter a full link starting with https://"; });
  if (d.seoTitle.trim().length < 5 || d.seoTitle.length > 60) err.seoTitle = "Title must be 5–60 characters.";
  if (d.seoDescription.trim().length < 20 || d.seoDescription.length > 160) err.seoDescription = "Description must be 20–160 characters.";
  if (file && (!IMG_TYPES.includes(file.type) || file.size > MAX_IMG)) err.logo = "Use a JPG, PNG or WEBP logo up to 2MB.";
  showErrors(sf, err); if (Object.keys(err).length) return showToast("Please fix the highlighted fields.", "error");
  const btn = sf.querySelector("[type=submit]"); if (btn.disabled) return; btn.disabled = true; btn.textContent = "Saving…";
  const r = await AdminAPI.updateSettings(d); showToast(r.message, r.success ? "success" : "error"); btn.disabled = false; btn.textContent = "Save settings";
});
