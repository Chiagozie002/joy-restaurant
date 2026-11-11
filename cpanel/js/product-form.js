/* ===== SHARED PRODUCT FORM HELPERS (Add page + Edit modal). PHP must repeat all of these checks, plus inspect the real file content. ===== */
const naira = n => "₦" + Number(n).toLocaleString("en-NG");
const MAX_IMG = 2 * 1024 * 1024, IMG_TYPES = ["image/jpeg", "image/png", "image/webp"];
/* Returns an {field: message} object; imageRequired is true on the Add page, false when editing */
function validateProduct(d, file, imageRequired) {
  const e = {};
  if (d.name.trim().length < 2) e.name = "Enter the product name.";
  if (d.description.trim().length < 10) e.description = "Description must be at least 10 characters.";
  if (!(Number(d.price) > 0)) e.price = "Enter a price greater than 0.";
  if (d.category.trim().length < 2) e.category = "Enter a category, for example Rice.";
  if (!file && imageRequired) e.image = "Choose a product image.";
  else if (file && !IMG_TYPES.includes(file.type)) e.image = "Use a JPG, PNG or WEBP image.";
  else if (file && file.size > MAX_IMG) e.image = "Image must be 2MB or smaller.";
  return e;
}
const showErrors = (form, e) => form.querySelectorAll("[data-err]").forEach(s => s.textContent = e[s.dataset.err] || "");
const readFile = f => new Promise((ok, no) => { const r = new FileReader(); r.onload = () => ok(r.result); r.onerror = no; r.readAsDataURL(f); });
/* Show a preview of the chosen file inside <img> */
function bindPreview(input, img) { input.onchange = () => { const f = input.files[0]; if (f && IMG_TYPES.includes(f.type)) { img.src = URL.createObjectURL(f); img.hidden = false; } }; }
