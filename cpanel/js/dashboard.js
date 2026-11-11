/* ===== DASHBOARD: stats, recent lists, charts (Chart.js) and QR card — all from AdminAPI (demo or PHP) ===== */
const naira = n => "₦" + n.toLocaleString("en-NG");
(async function () {
  try {
    const [res, prod, msg, trash] = await Promise.all([AdminAPI.getReservations(), AdminAPI.getProducts(), AdminAPI.getMessages(), AdminAPI.getTrash()]);
    const s = {res:res.length, prod:prod.length, msg:msg.length, trash:trash.length};
    document.querySelectorAll("[data-s]").forEach(el => el.textContent = s[el.dataset.s]);
    document.getElementById("recentRes").innerHTML = res.slice(0, 5).map(r => `<tr><td>${r.ref}</td><td>${r.name}</td><td>${r.guests}</td><td>${r.date}</td><td><span class="badge st-${r.status}">${r.status}</span></td></tr>`).join("");
    document.getElementById("recentMsg").innerHTML = msg.slice(0, 3).map(m => `<li class="py-1 border-bottom" style="border-color:var(--line)!important"><b>${m.subject}</b> <span class="sec-sub small">— ${m.name}${m.read ? "" : " • new"}</span></li>`).join("");
    document.getElementById("recentProd").innerHTML = prod.slice(-4).reverse().map(p => `<li class="py-1">${p.name} <span class="price float-end">${naira(p.price)}</span></li>`).join("");
    /* Charts: colours follow the brand */
    const css = getComputedStyle(document.documentElement), txt = css.getPropertyValue("--text").trim();
    Chart.defaults.color = "#9a8678";   // neutral grey, readable on both themes
    new Chart("trend", {type:"line", data:{labels:DEMO_TREND.labels, datasets:[{data:DEMO_TREND.values, borderColor:"#F47716", backgroundColor:"rgba(244,119,22,.2)", fill:true, tension:.35}]},
      options:{maintainAspectRatio:false, plugins:{legend:{display:false}}}});
    const count = st => res.filter(r => r.status === st).length;
    new Chart("split", {type:"doughnut", data:{labels:["Pending","Confirmed","Cancelled"], datasets:[{data:[count("Pending"), count("Confirmed"), count("Cancelled")], backgroundColor:["#d99a1d","#1e9e5a","#d64545"], borderWidth:0}]},
      options:{maintainAspectRatio:false}});
  } catch { showToast("Could not load dashboard data.", "error"); }
  /* QR card: URL of the e-menu (replace the domain when you go live) */
  const url = new URL("../emenu/index.html", location.href).href;
  document.getElementById("menuUrl").textContent = url;
  document.getElementById("copyUrl").onclick = async () => { try { await navigator.clipboard.writeText(url); showToast("Menu URL copied.", "success"); } catch { showToast("Copy failed. Select the URL manually.", "error"); } };
})();
