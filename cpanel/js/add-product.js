/* ===== ADD PRODUCT PAGE ===== */
const af = document.getElementById("addForm");
bindPreview(af.elements.image, document.getElementById("preview"));
af.addEventListener("submit", async e => {
  e.preventDefault();
  const d = Object.fromEntries(new FormData(af)), file = af.elements.image.files[0];
  const err = validateProduct(d, file, true); showErrors(af, err);
  if (Object.keys(err).length) return showToast("Please fix the highlighted fields.", "error");
  const btn = af.querySelector("[type=submit]"); if (btn.disabled) return;      // block double submit
  btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Saving…';
  try {
    /* Demo keeps the image as a data URL; PHP will receive a multipart upload and store a file path instead */
    const r = await AdminAPI.addProduct({name:d.name.trim(), description:d.description.trim(), price:Number(d.price), category:d.category.trim(), available:d.available === "1", image: await readFile(file)});
    showToast(r.message, r.success ? "success" : "error");
    if (r.success) { af.reset(); document.getElementById("preview").hidden = true; }
  } catch { showToast("Could not save the product.", "error"); }
  btn.disabled = false; btn.textContent = "Save product";
});
