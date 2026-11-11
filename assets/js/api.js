/* ===== API LAYER — every backend call lives here. Demo mode returns sample data; real mode calls ajax/*.php ===== */
/* Every endpoint must return: {success:boolean, message:string, data?:any, errors?:{}} (see README endpoint table) */
async function request(url, method = "GET", body = null) {
  if (DEMO_MODE) return null;                      // callers fall back to demo data
  const res = await fetch(url, {method, headers:{"Content-Type":"application/json"}, body: body ? JSON.stringify(body) : null});
  return res.json();
}
const wait = ms => new Promise(r => setTimeout(r, ms));   // simulates network so skeletons/loaders are visible in demo
const API = {
  async getProducts() {
    await wait(700);
    const r = await request("ajax/get-products.php");
    return r ? r.data : DEMO_PRODUCTS;
  },
  async sendContact(form) {
    await wait(900);
    return (await request("ajax/send-contact-message.php", "POST", form)) || {success:true, message:"Message sent. We'll reply soon."};
  },
  async createReservation(form) {
    await wait(1100);
    /* Real backend: validate, check tables, save, create the reference, email via PHPMailer, then respond.
       The demo reference below is display-only — NEVER generate real references in JavaScript. */
    return (await request("ajax/create-reservation.php", "POST", form)) ||
      {success:true, message:"Request received.", data:{reference:"DEMO-"+Date.now().toString().slice(-6), ...form}};
  }
};
