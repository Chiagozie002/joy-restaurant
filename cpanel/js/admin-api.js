/* ===== ADMIN API LAYER — every admin backend call lives here (same {success,message,data,errors} format). ===== */
const ADMIN_DEMO = typeof DEMO_MODE === "undefined" ? true : DEMO_MODE;
const wait = ms => new Promise(r => setTimeout(r, ms));        // fake latency so skeletons are visible
async function req(url, method = "GET", body = null) {
  if (ADMIN_DEMO) return null;                                   // null => caller returns demo data
  const r = await fetch(url, {method, headers:{"Content-Type":"application/json"}, body: body ? JSON.stringify(body) : null});
  return r.json();
}
const AdminAPI = {
  /* login: PHP must verify with password_verify(), use prepared statements, regenerate the session ID, check CSRF + rate limit.
     The demo check below is NOT security; it only lets you click through the interface. */
  async login(d) {
    await wait(900);
    const r = await req("../ajax/login.php", "POST", d); if (r) return r;
    const ok = ["admin", "admin@joyrestaurant.com"].includes(d.identifier.toLowerCase()) && d.password === "Joy@12345";
    return ok ? {success:true, message:"Welcome back!"} : {success:false, message:"Incorrect username or password."};
  },
  async logout() { await wait(300); return (await req("../ajax/logout.php", "POST")) || {success:true, message:"Logged out."}; },
  async getReservations() { await wait(600); const r = await req("../ajax/get-reservations.php"); return r ? r.data : DEMO_RESERVATIONS; },
  async getProducts() { await wait(600); const r = await req("../ajax/get-products.php"); return r ? r.data : DEMO_PRODUCTS; },
  async getMessages() { await wait(600); const r = await req("../ajax/get-messages.php"); return r ? r.data : DEMO_MESSAGES; }
};

/* ===== PRODUCT METHODS (demo store) =====
   In demo mode products live in localStorage so edits/deletes survive between admin pages.
   With DEMO_MODE=false each method calls its PHP endpoint instead (see README). Soft delete = deleted:true. */
const ProductStore = {
  load() { try { return JSON.parse(localStorage.getItem("joy-demo-products")) || DEMO_PRODUCTS.map(p => ({...p, deleted:false})); } catch { return DEMO_PRODUCTS.map(p => ({...p, deleted:false})); } },
  save(list) { try { localStorage.setItem("joy-demo-products", JSON.stringify(list)); return true; } catch { return false; } }   // false = storage full (big image)
};
Object.assign(AdminAPI, {
  async getProducts() { await wait(500); const r = await req("../ajax/get-products.php"); return r ? r.data : ProductStore.load().filter(p => !p.deleted); },
  async getTrash() { await wait(400); const r = await req("../ajax/get-products.php?deleted=1"); return r ? r.data : ProductStore.load().filter(p => p.deleted); },
  async addProduct(p) {          // PHP: validate + check image upload, INSERT product
    await wait(800); const r = await req("../ajax/add-product.php", "POST", p); if (r) return r;
    const list = ProductStore.load(); list.push({...p, id:Date.now(), deleted:false});
    return ProductStore.save(list) ? {success:true, message:"Product added."} : {success:false, message:"Image too large for demo storage. Use a smaller image."};
  },
  async updateProduct(p) {       // PHP: validate, UPDATE product (replace image only if a new one is sent)
    await wait(700); const r = await req("../ajax/update-product.php", "POST", p); if (r) return r;
    const list = ProductStore.load().map(x => x.id === p.id ? {...x, ...p} : x);
    return ProductStore.save(list) ? {success:true, message:"Product updated."} : {success:false, message:"Image too large for demo storage."};
  },
  async setDeleted(id, deleted) { // PHP: soft delete / restore => UPDATE products SET deleted=?
    await wait(400); const r = await req(deleted ? "../ajax/delete-product.php" : "../ajax/restore-product.php", "POST", {id}); if (r) return r;
    ProductStore.save(ProductStore.load().map(x => x.id === id ? {...x, deleted} : x));
    return {success:true, message: deleted ? "Moved to Trash." : "Product restored."};
  },
  async purgeProduct(id) {       // PHP: DELETE FROM products (and remove the image file)
    await wait(400); const r = await req("../ajax/permanently-delete-product.php", "POST", {id}); if (r) return r;
    ProductStore.save(ProductStore.load().filter(x => x.id !== id)); return {success:true, message:"Product permanently deleted."};
  }
});

/* ===== RESERVATIONS, MESSAGES, PROFILE, SETTINGS (demo stores in localStorage; PHP replaces each method) ===== */
const Store = (key, fallback) => ({
  load() { try { return JSON.parse(localStorage.getItem(key)) || structuredClone(fallback); } catch { return structuredClone(fallback); } },
  save(v) { try { localStorage.setItem(key, JSON.stringify(v)); return true; } catch { return false; } }
});
const DEMO_SETTINGS_ADMIN = {name:"Joy Restaurant", tagline:"Good Food. Great Mood.", story:"Joy Restaurant began with a simple idea: great food should make people happy.",
  mission:"Serve freshly prepared meals with warm hospitality.", vision:"Be the place people choose for every celebration.", address:"123 Restaurant Lane, Lagos, Nigeria",
  phone:"+234 901 234 5678", email:"hello@joyrestaurant.com", open:"09:00", close:"23:00", facebook:"", x:"", instagram:"", youtube:"",
  seoTitle:"Joy Restaurant | Good Food. Great Mood.", seoDescription:"Freshly prepared meals in a warm, elegant setting in Lagos."};
const ResStore = Store("joy-demo-res", DEMO_RESERVATIONS), MsgStore = Store("joy-demo-msg", DEMO_MESSAGES),
      ProfStore = Store("joy-demo-profile", DEMO_ADMIN), SetStore = Store("joy-demo-settings", DEMO_SETTINGS_ADMIN);
Object.assign(AdminAPI, {
  async getReservations() { await wait(500); const r = await req("../ajax/get-reservations.php"); return r ? r.data : ResStore.load(); },
  async updateReservationStatus(ref, status) {   // PHP: UPDATE status, then email the customer (confirmed/cancelled) via PHPMailer
    await wait(500); const r = await req("../ajax/update-reservation-status.php", "POST", {ref, status}); if (r) return r;
    ResStore.save(ResStore.load().map(x => x.ref === ref ? {...x, status} : x)); return {success:true, message:`Reservation ${status.toLowerCase()}.`};
  },
  async getMessages() { await wait(500); const r = await req("../ajax/get-messages.php"); return r ? r.data : MsgStore.load(); },
  async markMessageRead(id) { await wait(200); const r = await req("../ajax/update-message-status.php", "POST", {id, read:true}); if (r) return r;
    MsgStore.save(MsgStore.load().map(m => m.id === id ? {...m, read:true} : m)); return {success:true, message:"Marked as read."}; },
  async deleteMessage(id) { await wait(300); const r = await req("../ajax/delete-message.php", "POST", {id}); if (r) return r;
    MsgStore.save(MsgStore.load().filter(m => m.id !== id)); return {success:true, message:"Message deleted."}; },
  async deleteAllMessages() { await wait(300); const r = await req("../ajax/delete-message.php", "POST", {all:true}); if (r) return r;
    MsgStore.save([]); return {success:true, message:"All messages deleted."}; },
  async getProfile() { await wait(300); return ProfStore.load(); },
  async updateProfile(p) { await wait(700); const r = await req("../ajax/update-profile.php", "POST", p); if (r) return r;   // PHP: validate, handle avatar upload, UPDATE admin
    return ProfStore.save({...ProfStore.load(), ...p}) ? {success:true, message:"Profile saved."} : {success:false, message:"Image too large for demo storage."}; },
  async updatePassword(p) { await wait(800); return (await req("../ajax/update-password.php", "POST", p)) ||   // PHP: password_verify(current), password_hash(new), regenerate session
    {success:true, message:"Password changed (demo: nothing stored)."}; },
  async getSettings() { await wait(300); const r = await req("../ajax/update-settings.php"); return r ? r.data : SetStore.load(); },
  async updateSettings(s) { await wait(700); const r = await req("../ajax/update-settings.php", "POST", s); if (r) return r;   // PHP: validate, UPDATE settings row
    SetStore.save({...SetStore.load(), ...s}); return {success:true, message:"Settings saved."}; }
});
