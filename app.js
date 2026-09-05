/* ===========================
   STUDYMART – MAIN APP JS
   =========================== */

// ── ACTIVE PRODUCT LIST (sirf DB se aayega) ──────────────────────
let allProducts = [];
let shopItems   = [];

function initProducts() {
  if (typeof dbProducts !== "undefined" && dbProducts.length > 0) {
    allProducts = dbProducts;
  } else {
    allProducts = [];
  }
  shopItems = [...allProducts];
}

// ── CART STATE ───────────────────────────────────────────────────
let cart          = JSON.parse(localStorage.getItem("sm_cart")     || "[]");
let wishlist      = JSON.parse(localStorage.getItem("sm_wishlist") || "[]");
let couponApplied = false;
let discount      = 0;

function saveCart() {
  localStorage.setItem("sm_cart", JSON.stringify(cart));
  updateCartBadge();
}

function saveWishlist() {
  localStorage.setItem("sm_wishlist", JSON.stringify(wishlist));
}

function updateCartBadge() {
  const count = cart.reduce((s, i) => s + i.qty, 0);
  document.querySelectorAll("#cartCount").forEach((el) => (el.textContent = count));
}

// ── CART ACTIONS ─────────────────────────────────────────────────
function addToCart(id) {
  const product = allProducts.find((p) => p.id === id);
  if (!product || !product.inStock) return;
  const existing = cart.find((i) => i.id === id);
  if (existing) {
    existing.qty++;
  } else {
    cart.push({ ...product, qty: 1 });
  }
  saveCart();
  showToast("Added to cart!");
}

function removeFromCart(id) {
  cart = cart.filter((i) => i.id !== id);
  saveCart();
  renderCart();
}

function updateQty(id, delta) {
  const item = cart.find((i) => i.id === id);
  if (!item) return;
  item.qty = Math.max(1, item.qty + delta);
  saveCart();
  renderCart();
}

function clearCart() {
  if (!confirm("Clear all items?")) return;
  cart = [];
  saveCart();
  renderCart();
}

function toggleWishlist(id, btn) {
  if (wishlist.includes(id)) {
    wishlist = wishlist.filter((i) => i !== id);
    if (btn) btn.classList.remove("liked");
    if (btn) btn.innerHTML = "♡";
  } else {
    wishlist.push(id);
    if (btn) btn.classList.add("liked");
    if (btn) btn.innerHTML = "♥";
    showToast("Added to wishlist!");
  }
  saveWishlist();
}

// ── RENDER PRODUCT CARD ──────────────────────────────────────────
function createProductCard(p) {
  const discountPct = p.oldPrice
    ? Math.round((1 - p.price / p.oldPrice) * 100)
    : 0;
  const isLiked = wishlist.includes(p.id);
  const imgSrc  = p.img && p.img !== ""
    ? p.img
    : "https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=400&q=80";

  return `
    <div class="product-card" data-cat="${p.cat}" data-id="${p.id}" onclick="goToProduct(${p.id})">
      <div class="product-img-wrap">
        <img src="${imgSrc}" alt="${p.name}" loading="lazy"
             onerror="this.src='https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=400&q=80'" />
        <div class="product-badges">
          ${discountPct > 0 ? `<span class="badge-sale">-${discountPct}%</span>` : ""}
          ${p.badge === "new" ? `<span class="badge-new-label">New</span>` : ""}
          ${!p.inStock ? `<span class="badge-sale" style="background:#64748b;">Out of Stock</span>` : ""}
        </div>
        <button class="product-wishlist ${isLiked ? "liked" : ""}"
                onclick="event.stopPropagation(); toggleWishlist(${p.id}, this)">
          ${isLiked ? "♥" : "♡"}
        </button>
      </div>
      <div class="product-body">
        <div class="product-cat">${p.cat}</div>
        <div class="product-name">${p.name}</div>
        <div class="product-rating">
          <span style="color:#f59e0b;">${"★".repeat(Math.floor(p.rating || 0))}</span>
          <span>${p.rating || 0} (${p.reviews || 0})</span>
        </div>
        <div class="product-price-row">
          <div>
            <span class="price-new">₹${Number(p.price).toLocaleString()}</span>
            ${p.oldPrice ? `<span class="price-old ms-1">₹${Number(p.oldPrice).toLocaleString()}</span>` : ""}
          </div>
          <button class="btn-add-cart"
                  onclick="event.stopPropagation(); addToCart(${p.id})"
                  ${!p.inStock ? 'disabled style="opacity:.5;cursor:not-allowed;"' : ""}>
            ${p.inStock ? "+ Cart" : "Sold Out"}
          </button>
        </div>
      </div>
    </div>`;
}

function goToProduct(id) {
  window.location.href = `product_detail.php?id=${id}`;
}

// ── HOME: FEATURED PRODUCTS ──────────────────────────────────────
function renderFeaturedProducts(items) {
  const grid = document.getElementById("productGrid");
  if (!grid) return;
  if (items.length === 0) {
    grid.innerHTML = `<div class="col-12 text-center py-5 text-muted"><p>Koi product nahi mila.</p></div>`;
    return;
  }
  grid.innerHTML = items
    .slice(0, 8)
    .map((p) => `<div class="col-6 col-md-4 col-lg-3">${createProductCard(p)}</div>`)
    .join("");
}

// ── DOM READY ────────────────────────────────────────────────────
document.addEventListener("DOMContentLoaded", () => {
  initProducts();
  updateCartBadge();

  // ── INDEX PAGE ──────────────────────────────────────────────────
  const homeGrid = document.getElementById("productGrid");
  if (homeGrid) {
    renderFeaturedProducts(allProducts);
  }

  // Cat pill filter (index page)
  document.querySelectorAll(".cat-pill").forEach((pill) => {
    pill.addEventListener("click", () => {
      document.querySelectorAll(".cat-pill").forEach((p) => p.classList.remove("active"));
      pill.classList.add("active");
      const cat      = pill.dataset.cat;
      const filtered = cat === "all"
        ? allProducts
        : allProducts.filter((p) => p.cat === cat);
      renderFeaturedProducts(filtered);
    });
  });

  // ── SHOP PAGE INIT ──────────────────────────────────────────────
  const shopGrid = document.getElementById("shopGrid");
  if (shopGrid) {

    // Price range max set karo
    if (typeof maxPrice !== "undefined") {
      const pr = document.getElementById("priceRange");
      if (pr) { pr.max = maxPrice; pr.value = maxPrice; }
      const pv = document.getElementById("priceVal");
      if (pv) pv.textContent = "₹" + maxPrice.toLocaleString();
    }

    // Pehle sab products render karo
    visibleCount = 12;
    shopItems = [...allProducts];
    renderShopGrid(allProducts);

    // ── CATEGORY CHECKBOXES – event listeners ──────────────────────
    // Specific category checkboxes
    document.querySelectorAll("#catFilter input[type='checkbox']:not([value='all'])")
      .forEach((cb) => {
        cb.addEventListener("change", () => {
          const allCb = document.querySelector("#catFilter input[value='all']");

          // Agar koi specific cat checked hai to "All" uncheck karo
          const anySpecificChecked = Array.from(
            document.querySelectorAll("#catFilter input[type='checkbox']:not([value='all'])")
          ).some((c) => c.checked);

          if (allCb) {
            allCb.checked = !anySpecificChecked;
          }

          applyFilters();
        });
      });

    // "All Products" checkbox
    const allCbMain = document.querySelector("#catFilter input[value='all']");
    if (allCbMain) {
      allCbMain.addEventListener("change", () => {
        if (allCbMain.checked) {
          // Baaki sab uncheck karo
          document.querySelectorAll("#catFilter input[type='checkbox']:not([value='all'])")
            .forEach((c) => (c.checked = false));
        } else {
          // "All" ko force checked rakho agar koi specific nahi chuna
          const anySpecific = Array.from(
            document.querySelectorAll("#catFilter input[type='checkbox']:not([value='all'])")
          ).some((c) => c.checked);
          if (!anySpecific) allCbMain.checked = true;
        }
        applyFilters();
      });
    }
  }

  // ── CART PAGE INIT ──────────────────────────────────────────────
  if (document.getElementById("cartBody")) {
    renderCart();
  }

  // Navbar scroll
  window.addEventListener("scroll", () => {
    const nav    = document.getElementById("mainNav");
    if (nav) nav.classList.toggle("scrolled", window.scrollY > 40);
    const topBtn = document.getElementById("backTop");
    if (topBtn) topBtn.classList.toggle("visible", window.scrollY > 400);
  });
});

// ── SHOP PAGE FUNCTIONS ───────────────────────────────────────────
let visibleCount = 12;

function renderShopGrid(items) {
  const grid = document.getElementById("shopGrid");
  if (!grid) return;
  const count = document.getElementById("resultsCount");

  if (items.length === 0) {
    grid.innerHTML = `<div class="col-12 text-center py-5 text-muted">
      <i class="fa fa-search fa-3x mb-3"></i>
      <p>Is category mein koi product nahi hai.</p>
    </div>`;
    if (count) count.textContent = "0 products found";
    return;
  }

  if (count)
    count.textContent = `Showing ${Math.min(visibleCount, items.length)} of ${items.length} products`;

  grid.innerHTML = items
    .slice(0, visibleCount)
    .map((p) => `<div class="col-6 col-md-4 col-lg-4">${createProductCard(p)}</div>`)
    .join("");
}

function applyFilters() {
  const allCheck     = document.querySelector("#catFilter input[value='all']");
  const isAllChecked = allCheck ? allCheck.checked : true;

  // Sirf specific (non-all) checked categories lo
  const checkedCats = Array.from(
    document.querySelectorAll("#catFilter input[type='checkbox']:not([value='all']):checked")
  ).map((i) => i.value.toLowerCase().trim());

  const sortBy      = document.getElementById("sortBy")?.value || "default";
  const maxP        = parseInt(document.getElementById("priceRange")?.value || 999999);
  const minRating   = parseFloat(document.querySelector('input[name="rating"]:checked')?.value || 0);
  const inStockOnly = document.getElementById("inStockOnly")?.checked;
  const onSaleOnly  = document.getElementById("onSaleOnly")?.checked;

  let filtered = allProducts.filter((p) => {
    // ── CATEGORY MATCH ──────────────────────────────────────────
    // "All" checked hai ya koi specific nahi chuna → sab dikhao
    if (!isAllChecked && checkedCats.length > 0) {
      const productCat = (p.cat || "").toLowerCase().trim();
      if (!checkedCats.includes(productCat)) return false;
    }

    // ── PRICE ───────────────────────────────────────────────────
    if (p.price > maxP) return false;

    // ── RATING ──────────────────────────────────────────────────
    if ((p.rating || 0) < minRating) return false;

    // ── STOCK ───────────────────────────────────────────────────
    if (inStockOnly && !p.inStock) return false;

    // ── SALE ────────────────────────────────────────────────────
    if (onSaleOnly && !p.oldPrice) return false;

    return true;
  });

  // ── SORT ────────────────────────────────────────────────────────
  if (sortBy === "price-asc")  filtered.sort((a, b) => a.price  - b.price);
  if (sortBy === "price-desc") filtered.sort((a, b) => b.price  - a.price);
  if (sortBy === "rating")     filtered.sort((a, b) => (b.rating || 0) - (a.rating || 0));

  shopItems    = filtered;
  visibleCount = 12;
  renderShopGrid(filtered);
}

// toggleAllFilter — PHP ke onchange="toggleAllFilter(this)" ke liye
function toggleAllFilter(el) {
  if (el.checked) {
    document.querySelectorAll("#catFilter input[type='checkbox']:not([value='all'])")
      .forEach((c) => (c.checked = false));
  } else {
    // "All" ko uncheck mat karo jab tak koi specific checked na ho
    const anySpecific = Array.from(
      document.querySelectorAll("#catFilter input[type='checkbox']:not([value='all'])")
    ).some((c) => c.checked);
    if (!anySpecific) el.checked = true;
  }
  applyFilters();
}

function updatePrice(val) {
  const el = document.getElementById("priceVal");
  if (el) el.textContent = "₹" + parseInt(val).toLocaleString();
  applyFilters();
}

function filterShop(query) {
  const q = query.toLowerCase();
  const filtered = allProducts.filter(
    (p) =>
      p.name.toLowerCase().includes(q) ||
      (p.cat || "").toLowerCase().includes(q)
  );
  shopItems    = filtered;
  visibleCount = 12;
  renderShopGrid(filtered);
}

function clearFilters() {
  // "All" check karo, baaki sab uncheck
  document.querySelectorAll("#catFilter input").forEach((c, i) => (c.checked = i === 0));

  // Price reset
  const pr = document.getElementById("priceRange");
  if (pr) { pr.value = pr.max; updatePrice(pr.max); }

  // Rating reset
  const firstRating = document.querySelector('input[name="rating"][value="0"]');
  if (firstRating) firstRating.checked = true;

  // Stock/Sale reset
  const is = document.getElementById("inStockOnly");
  const os = document.getElementById("onSaleOnly");
  if (is) is.checked = false;
  if (os) os.checked = false;

  // Search reset
  const ns = document.getElementById("navSearch");
  if (ns) ns.value = "";

  // Sort reset
  const sb = document.getElementById("sortBy");
  if (sb) sb.value = "default";

  visibleCount = 12;
  shopItems    = [...allProducts];
  renderShopGrid(allProducts);
}

function openProductPage(id) {
  window.location.href = "product_detail.php?id=" + id;
}

function setView(type) {
  document.getElementById("gridView")?.classList.toggle("active", type === "grid");
  document.getElementById("listView")?.classList.toggle("active", type === "list");
  const grid = document.getElementById("shopGrid");
  if (!grid) return;
  if (type === "list") {
    grid.classList.add("list-view");
    grid.querySelectorAll(".col-6, .col-md-4, .col-lg-4").forEach((c) => { c.className = "col-12"; });
  } else {
    grid.classList.remove("list-view");
    grid.querySelectorAll(".col-12").forEach((c) => { c.className = "col-6 col-md-4 col-lg-4"; });
  }
}

function loadMore() {
  visibleCount += 8;
  renderShopGrid(shopItems);
}

// ── CART PAGE ─────────────────────────────────────────────────────
function renderCart() {
  const body    = document.getElementById("cartBody");
  const empty   = document.getElementById("cartEmpty");
  const content = document.getElementById("cartContent");
  if (!body) return;

  updateCartBadge();

  if (cart.length === 0) {
    if (empty)   empty.style.display   = "block";
    if (content) content.style.display = "none";
    return;
  }
  if (empty)   empty.style.display   = "none";
  if (content) content.style.display = "";

  body.innerHTML = cart.map((item) => `
    <tr>
      <td>
        <div class="cart-product-info">
          <img src="${item.img}" alt="${item.name}" />
          <div>
            <strong>${item.name}</strong>
            <small>${item.cat}</small>
          </div>
        </div>
      </td>
      <td><strong>₹${item.price.toLocaleString()}</strong></td>
      <td>
        <div class="qty-control">
          <button class="qty-btn" onclick="updateQty(${item.id}, -1)">−</button>
          <span class="qty-num">${item.qty}</span>
          <button class="qty-btn" onclick="updateQty(${item.id}, 1)">+</button>
        </div>
      </td>
      <td><strong>₹${(item.price * item.qty).toLocaleString()}</strong></td>
      <td><button class="remove-btn" onclick="removeFromCart(${item.id})"><i class="fa fa-trash"></i></button></td>
    </tr>
  `).join("");

  updateOrderSummary();
}

function updateOrderSummary() {
  const subtotal = cart.reduce((s, i) => s + i.price * i.qty, 0);
  const shipping = subtotal >= 499 ? 0 : 49;
  const total    = subtotal - discount + shipping;

  const el = (id) => document.getElementById(id);
  if (el("cartSubtotal")) el("cartSubtotal").textContent = "₹" + subtotal.toLocaleString();
  if (el("shippingCost")) el("shippingCost").textContent = shipping === 0 ? "Free" : "₹" + shipping;
  if (el("cartTotal"))    el("cartTotal").textContent    = "₹" + total.toLocaleString();
  if (el("discountRow"))  el("discountRow").style.display = discount > 0 ? "" : "none";
  if (el("cartDiscount")) el("cartDiscount").textContent = "-₹" + discount;
}

function applyCoupon() {
  const code     = document.getElementById("couponCode")?.value.trim().toUpperCase();
  const msg      = document.getElementById("couponMsg");
  const subtotal = cart.reduce((s, i) => s + i.price * i.qty, 0);
  const coupons  = { STUDY15: 0.15, SAVE10: 0.1, NEWUSER: 0.2 };

  if (coupons[code]) {
    discount      = Math.round(subtotal * coupons[code]);
    couponApplied = true;
    if (msg) { msg.textContent = `✓ Coupon applied! You save ₹${discount}`; msg.className = "coupon-msg success"; }
    updateOrderSummary();
  } else {
    if (msg) { msg.textContent = "✗ Invalid coupon code"; msg.className = "coupon-msg error"; }
  }
}

// ── NEWSLETTER ───────────────────────────────────────────────────
function subscribeNewsletter() {
  const email = document.getElementById("newsletterEmail")?.value;
  if (!email || !email.includes("@")) { alert("Please enter a valid email."); return; }
  showToast("Subscribed successfully! 🎉");
  if (document.getElementById("newsletterEmail"))
    document.getElementById("newsletterEmail").value = "";
}

// ── TOAST ────────────────────────────────────────────────────────
function showToast(msg = "Added to cart!") {
  const toast = document.getElementById("cartToast");
  if (!toast) return;
  toast.innerHTML = `<i class="fa fa-check-circle"></i> ${msg}`;
  toast.classList.add("show");
  setTimeout(() => toast.classList.remove("show"), 2800);
}

// ── BACK TO TOP ──────────────────────────────────────────────────
function scrollToTop() {
  window.scrollTo({ top: 0, behavior: "smooth" });
}

// ── SYNC CART ACROSS TABS & BACK/FORWARD NAVIGATION ──────────────
window.addEventListener("storage", (e) => {
  if (e.key === "sm_cart") {
    cart = JSON.parse(localStorage.getItem("sm_cart") || "[]");
    updateCartBadge();
    if (document.getElementById("cartBody")) renderCart();
  }
});

window.addEventListener("pageshow", (e) => {
  if (e.persisted) {
    cart = JSON.parse(localStorage.getItem("sm_cart") || "[]");
    updateCartBadge();
    if (document.getElementById("cartBody")) renderCart();
  }
});