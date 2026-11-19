/* ===== DASHBOARD: stats, charts (Chart.js), recent lists and QR card. All data comes from AdminAPI (database or demo). ===== */
const naira = n => "₦" + Number(n).toLocaleString("en-NG");
const initials = n => n.trim().split(/\s+/).slice(0, 2).map(w => w[0]).join("").toUpperCase();
const fmtDate = d => new Date(d + "T00:00:00").toLocaleDateString("en-GB", {weekday: "short", day: "numeric", month: "short"});
const fmtTime = t => { const [h, m] = t.split(":").map(Number); return `${h % 12 || 12}:${String(m).padStart(2, "0")} ${h < 12 ? "AM" : "PM"}`; };
const empty = (icon, text) => `<div class="empty-note"><i class="fa-regular ${icon}"></i>${text}</div>`;   // friendly message when a list has no rows

/* Today's date under the welcome heading */
document.getElementById("today").textContent = new Date().toLocaleDateString("en-GB", {weekday: "long", day: "numeric", month: "long", year: "numeric"});

(async function () {
  /* Admin name for the welcome banner (failure is harmless) */
  AdminAPI.getProfile().then(p => { if (p && p.name) document.getElementById("adminName").textContent = p.name.split(" ")[0]; }).catch(() => {});
  try {
    const [res, prod, msg, trash, trend] = await Promise.all([AdminAPI.getReservations(), AdminAPI.getProducts(), AdminAPI.getMessages(), AdminAPI.getTrash(), AdminAPI.getTrend()]);
    const s = {res: res.length, prod: prod.length, msg: msg.length, trash: trash.length};
    document.querySelectorAll("[data-s]").forEach(el => el.textContent = s[el.dataset.s]);

    /* Recent reservations: avatar, name, when, status */
    document.getElementById("recentRes").innerHTML = res.slice(0, 5).map(r => `<div class="li">
      <span class="avatar-i">${esc(initials(r.name))}</span>
      <div class="li-main"><b>${esc(r.name)}</b><small>${fmtDate(r.date)} · ${fmtTime(r.time)} · ${r.guests} guest${r.guests == 1 ? "" : "s"}</small></div>
      <span class="st-pill st-${esc(r.status)}">${esc(r.status)}</span></div>`).join("") || empty("fa-calendar-xmark", "No reservations yet.");

    /* Recent messages: dot marks unread */
    document.getElementById("recentMsg").innerHTML = msg.slice(0, 4).map(m => `<div class="li">
      <span class="read-dot ${m.read ? "" : "read-dot-new"}" title="${m.read ? "Read" : "Unread"}"></span>
      <div class="li-main"><b>${esc(m.subject)}</b><small>${esc(m.name)} · ${esc(m.date)}</small></div>
      ${m.read ? "" : '<span class="new-tag">New</span>'}</div>`).join("") || empty("fa-envelope", "No messages yet.");

    /* Newest products: photo, name, category, price */
    document.getElementById("recentProd").innerHTML = prod.slice(0, 5).map(p => `<div class="li">
      <img class="thumb" src="${esc(p.image.startsWith("data:") ? p.image : "../" + p.image)}" alt="" loading="lazy" onerror="this.style.opacity='0'">
      <div class="li-main"><b>${esc(p.name)}</b><small>${esc(p.category)}</small></div><span class="price-pill">${naira(p.price)}</span></div>`).join("") || empty("fa-utensils", "No products yet.");

    /* Charts */
    Chart.defaults.color = "#9a8678"; Chart.defaults.borderColor = "rgba(255,170,50,.12)"; Chart.defaults.font.family = "DM Sans, sans-serif";
    const ctx = document.getElementById("trend").getContext("2d");
    const grad = ctx.createLinearGradient(0, 0, 0, 260); grad.addColorStop(0, "rgba(244,119,22,.45)"); grad.addColorStop(1, "rgba(244,119,22,0)");
    new Chart(ctx, {type: "line", data: {labels: trend.labels, datasets: [{data: trend.values, borderColor: "#F47716", backgroundColor: grad, fill: true, tension: .4, pointBackgroundColor: "#FFAA32", pointRadius: 4}]},
      options: {maintainAspectRatio: false, plugins: {legend: {display: false}}, scales: {y: {beginAtZero: true, ticks: {precision: 0}}, x: {grid: {display: false}}}}});
    const count = st => res.filter(r => r.status === st).length;
    new Chart("split", {type: "doughnut", data: {labels: ["Pending", "Confirmed", "Cancelled"], datasets: [{data: [count("Pending"), count("Confirmed"), count("Cancelled")], backgroundColor: ["#d99a1d", "#1e9e5a", "#d64545"], borderWidth: 0}]},
      options: {maintainAspectRatio: false, cutout: "68%", plugins: {legend: {position: "bottom", labels: {usePointStyle: true, boxWidth: 8}}}}});
  } catch { showToast("Could not load dashboard data.", "error"); }
  /* QR card: address of the digital menu (you generate the QR image yourself) */
  const url = new URL("../emenu/index.html", location.href).href;
  document.getElementById("menuUrl").textContent = url;
  document.getElementById("copyUrl").onclick = async () => { try { await navigator.clipboard.writeText(url); showToast("Menu URL copied.", "success"); } catch { showToast("Copy failed. Select the URL manually.", "error"); } };
})();
