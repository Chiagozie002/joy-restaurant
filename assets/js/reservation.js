/* ===== RESERVATION FORM — rules come from SETTINGS (opening hours, max guests) in data.js ===== */
const rf = document.getElementById("resForm");
const dateInput = rf.elements.date; const now = new Date(), pad = n => String(n).padStart(2, "0");
const today = `${now.getFullYear()}-${pad(now.getMonth()+1)}-${pad(now.getDate())}`;   // local date, not UTC
dateInput.min = today;   // blocks past dates
/* Build time slots from the configurable opening hours (last slot = 1h before closing) */
const [oh, om] = SETTINGS.open.split(":").map(Number), [ch, cm] = SETTINGS.close.split(":").map(Number);
for (let m = oh*60+om; m <= ch*60+cm-60; m += SETTINGS.slotMinutes) {
  const t = `${String(Math.floor(m/60)).padStart(2,"0")}:${String(m%60).padStart(2,"0")}`;
  rf.elements.time.add(new Option(t, t));
}
rf.addEventListener("submit", async e => {
  e.preventDefault();
  const d = Object.fromEntries(new FormData(rf)); const err = {};
  if (d.name.trim().length < 2) err.name = "Enter your full name.";
  if (!/^\S+@\S+\.\S+$/.test(d.email)) err.email = "Enter a valid email address.";
  if (!/^(\+?234|0)[789][01]\d{8}$/.test(d.phone.replace(/\s/g, ""))) err.phone = "Enter a valid Nigerian phone number.";
  if (!(+d.guests >= 1 && +d.guests <= SETTINGS.maxGuests)) err.guests = `Guests must be 1–${SETTINGS.maxGuests}.`;
  if (!d.date || d.date < dateInput.min) err.date = "Choose today or a future date.";
  if (!d.time) err.time = "Choose a time within opening hours.";
  else if (d.date === today && d.time <= `${pad(now.getHours())}:${pad(now.getMinutes())}`) err.time = "That time has already passed today.";
  rf.querySelectorAll("[data-err]").forEach(s => s.textContent = err[s.dataset.err] || "");
  if (Object.keys(err).length) return showToast("Please fix the highlighted fields.", "error");
  const btn = rf.querySelector("button[type=submit]"); if (btn.disabled) return;   // no duplicate submits
  btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Sending…';
  try {
    const r = await API.createReservation(d);
    if (!r.success) throw new Error(r.message);
    /* DEMO ONLY: real PHP should redirect using a trusted session/token, not browser storage. */
    sessionStorage.setItem("joy-last-reservation", JSON.stringify(r.data));
    location.href = "reservation-success.html";          // only after a successful save
  } catch (x) { showToast(x.message || "Could not send request.", "error"); btn.disabled = false; btn.textContent = "Request reservation"; }
});
