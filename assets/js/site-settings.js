/* ===== SITE SETTINGS — fills the public pages and the e-menu from the database =====
   Elements opt in with data attributes:
     data-setting="tagline|story|mission|vision|address|phone|email|hours"   -> replaces the text
     data-social="facebook|x|instagram|youtube"                               -> sets the link (hidden when empty)
     data-map                                                                  -> sets the Google Maps iframe address
   Edit a value in Admin > Website Settings and the next page load shows it here. If the request fails, the
   text already written in the HTML stays, so the page never looks broken. */
const SITE_ROOT = new URL("../../", document.currentScript.src).href;     // site root, whatever the page depth

window.siteSettingsReady = (async () => {
  let s = null;
  try { s = await API.getSettings(); } catch { /* keep the HTML defaults */ }
  if (!s) return null;
  Object.assign(SETTINGS, {open: s.open, close: s.close, maxGuests: s.maxGuests, slotMinutes: s.slotMinutes, name: s.name});   // reservation rules

  /* "09:00" -> "9AM", "23:30" -> "11:30PM" */
  const fmt = t => { const [h, m] = t.split(":").map(Number); return `${h % 12 || 12}${m ? ":" + String(m).padStart(2, "0") : ""}${h < 12 ? "AM" : "PM"}`; };
  const values = {...s, hours: `Mon – Sun: ${fmt(s.open)} – ${fmt(s.close)}`};
  document.querySelectorAll("[data-setting]").forEach(el => { const v = values[el.dataset.setting]; if (v) el.textContent = v; });

  document.querySelectorAll("[data-social]").forEach(a => {
    const url = s[a.dataset.social];
    if (url) { a.href = url; a.target = "_blank"; a.rel = "noopener noreferrer"; } else a.hidden = true;
  });
  const map = document.querySelector("[data-map]"); if (map && s.mapUrl) map.src = s.mapUrl;

  /* Brand: restaurant name (first word big, the rest small) and an uploaded logo in place of the icon badge */
  const words = s.name.trim().split(/\s+/), first = words[0], rest = words.slice(1).join(" ");
  document.querySelectorAll(".brand, .pl-word").forEach(b => {
    const text = [...b.childNodes].find(n => n.nodeType === 3 && n.nodeValue.trim()), small = b.querySelector("small");
    if (text) text.nodeValue = small ? first : s.name;
    if (small) small.textContent = small.textContent.trim() === "Admin Panel" ? small.textContent : rest;
  });
  if (s.logo) document.querySelectorAll(".brand > i").forEach(i => i.replaceWith(Object.assign(document.createElement("img"), {src: SITE_ROOT + s.logo, alt: s.name, className: "brand-logo"})));

  /* Search-engine title and description (home page only) */
  if (document.getElementById("home")) {
    if (s.seoTitle) document.title = s.seoTitle;
    const m = document.querySelector('meta[name="description"]'); if (m && s.seoDescription) m.content = s.seoDescription;
  }
  return s;
})();
