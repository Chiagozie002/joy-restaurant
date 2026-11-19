/* ===== API LAYER — every backend call lives here. DEMO_MODE=true returns sample data; false calls the PHP files in /ajax ===== */
const esc = s => String(s ?? "").replace(/[&<>"']/g, c => ({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;"}[c]));   // escape text before it goes into innerHTML (stops script injection)
/* Every endpoint answers {success:boolean, message:string, data?:any, errors?:{field:message}} (see README endpoint table) */
const AJAX = new URL("../../ajax/", document.currentScript.src).href;   // works from any page depth (home, e-menu, ...)
let csrfToken = null;
/* POST requests carry a CSRF token (fetched once from csrf.php and kept for the page) */
async function csrf() {
  if (!csrfToken) { const r = await fetch(AJAX + "csrf.php", {credentials: "same-origin"}); csrfToken = (await r.json()).data.token; }
  return csrfToken;
}
async function request(url, method = "GET", body = null) {
  if (DEMO_MODE) return null;                                   // callers fall back to demo data
  const headers = {"Content-Type": "application/json"};
  if (method !== "GET") headers["X-CSRF-Token"] = await csrf();
  const res = await fetch(url, {method, headers, credentials: "same-origin", body: body ? JSON.stringify(body) : null});
  if (res.status === 419) csrfToken = null;                    // token expired: the next call fetches a fresh one
  return res.json().catch(() => ({success: false, message: "Unexpected server response. Please try again."}));
}
/* Put server-side validation messages next to the matching inputs ([data-err="field"]) */
function showServerErrors(form, errors) {
  form.querySelectorAll("[data-err]").forEach(s => s.textContent = (errors && errors[s.dataset.err]) || "");
}
const wait = ms => new Promise(r => setTimeout(r, ms));          // simulates network in demo mode so skeletons are visible
const API = {
  async getProducts() {
    await wait(DEMO_MODE ? 700 : 0);
    const r = await request(AJAX + "get-products.php");
    if (r && !r.success) throw new Error(r.message);
    return r ? r.data : DEMO_PRODUCTS;
  },
  async getSettings() {                                         // website settings edited in Admin > Website Settings
    const r = await request(AJAX + "get-settings.php");
    return r && r.success ? r.data : null;
  },
  async sendContact(form) {
    await wait(DEMO_MODE ? 900 : 0);
    return (await request(AJAX + "send-contact-message.php", "POST", form)) || {success: true, message: "Message sent. We'll reply soon."};
  },
  async createReservation(form) {
    await wait(DEMO_MODE ? 1100 : 0);
    /* The PHP side validates, checks seating, saves, creates the reference, emails, then answers.
       The demo reference below is display-only: real references are made by the server. */
    return (await request(AJAX + "create-reservation.php", "POST", form)) ||
      {success: true, message: "Request received.", data: {reference: "DEMO-" + Date.now().toString().slice(-6), ...form}};
  }
};
