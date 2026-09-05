<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Wishlist – StudyMart</title>
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css"
    />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
    />
    <link
      href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,400&display=swap"
      rel="stylesheet"
    />
    <link rel="stylesheet" href="style.css" />
    <style>
      /* ── WISHLIST PAGE STYLES ── */

      .wishlist-hero {
        background: linear-gradient(
          135deg,
          #fdf2f8 0%,
          #fce7f3 40%,
          #ede9fe 100%
        );
        padding: 52px 0 44px;
        border-bottom: 1px solid var(--border);
        position: relative;
        overflow: hidden;
      }
      .wishlist-hero::before {
        content: "";
        position: absolute;
        inset: 0;
        background-image: radial-gradient(
          rgba(236, 72, 153, 0.07) 1px,
          transparent 1px
        );
        background-size: 24px 24px;
      }
      .wishlist-hero-inner {
        position: relative;
        z-index: 1;
      }
      .wishlist-hero h1 {
        font-family: var(--font-head);
        font-size: clamp(1.8rem, 4vw, 2.8rem);
        font-weight: 800;
        color: var(--dark);
        margin-bottom: 6px;
      }
      .wishlist-hero h1 span {
        color: #db2777;
      }
      .wishlist-hero p {
        color: var(--mid);
        font-size: 0.95rem;
      }
      .wishlist-hero-icon {
        font-size: 5rem;
        animation: heartBeat 2s ease-in-out infinite;
      }
      @keyframes heartBeat {
        0%,
        100% {
          transform: scale(1);
        }
        14% {
          transform: scale(1.15);
        }
        28% {
          transform: scale(1);
        }
        42% {
          transform: scale(1.1);
        }
        56% {
          transform: scale(1);
        }
      }
      .wishlist-badge-count {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #fff;
        border: 1.5px solid #fbcfe8;
        border-radius: 50px;
        padding: 6px 18px;
        font-family: var(--font-head);
        font-weight: 700;
        font-size: 0.85rem;
        color: #db2777;
        margin-top: 12px;
      }

      /* TOOLBAR */
      .wishlist-toolbar {
        background: #fff;
        border: 1.5px solid var(--border);
        border-radius: var(--radius);
        padding: 16px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 28px;
      }
      .wt-left {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
      }
      .wt-count {
        font-size: 0.88rem;
        color: var(--mid);
      }
      .wt-count strong {
        color: var(--dark);
      }
      .wt-right {
        display: flex;
        align-items: center;
        gap: 10px;
      }
      .btn-move-all {
        background: var(--primary);
        color: #fff;
        border: none;
        border-radius: 10px;
        padding: 9px 20px;
        font-family: var(--font-head);
        font-weight: 700;
        font-size: 0.84rem;
        cursor: pointer;
        transition: var(--transition);
        display: flex;
        align-items: center;
        gap: 8px;
      }
      .btn-move-all:hover {
        background: var(--primary-dark);
        transform: translateY(-1px);
      }
      .btn-clear-wish {
        background: #fef2f2;
        color: #ef4444;
        border: 1.5px solid #fecaca;
        border-radius: 10px;
        padding: 8px 16px;
        font-family: var(--font-head);
        font-weight: 700;
        font-size: 0.84rem;
        cursor: pointer;
        transition: var(--transition);
      }
      .btn-clear-wish:hover {
        background: #fee2e2;
      }
      .sort-select-wish {
        border: 1.5px solid var(--border);
        border-radius: 10px;
        font-size: 0.84rem;
        padding: 8px 12px;
        outline: none;
        cursor: pointer;
        font-family: var(--font-body);
      }

      /* WISHLIST CARD */
      .wish-card {
        background: #fff;
        border-radius: 20px;
        border: 1.5px solid var(--border);
        overflow: hidden;
        transition: var(--transition);
        position: relative;
      }
      .wish-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 16px 48px rgba(15, 23, 42, 0.1);
        border-color: #fbcfe8;
      }
      .wish-img-wrap {
        position: relative;
        aspect-ratio: 1;
        background: var(--light);
        overflow: hidden;
      }
      .wish-img-wrap img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.4s ease;
      }
      .wish-card:hover .wish-img-wrap img {
        transform: scale(1.06);
      }

      /* Remove button */
      .wish-remove-btn {
        position: absolute;
        top: 12px;
        right: 12px;
        width: 36px;
        height: 36px;
        background: #fff;
        border: none;
        border-radius: 50%;
        color: #ef4444;
        font-size: 0.95rem;
        cursor: pointer;
        box-shadow: var(--shadow);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: var(--transition);
        z-index: 2;
      }
      .wish-remove-btn:hover {
        background: #ef4444;
        color: #fff;
        transform: scale(1.1);
      }

      /* Discount badge */
      .wish-disc-badge {
        position: absolute;
        top: 12px;
        left: 12px;
        background: #ef4444;
        color: #fff;
        font-size: 0.7rem;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: 50px;
        font-family: var(--font-head);
      }

      /* Out of stock overlay */
      .wish-oos {
        position: absolute;
        inset: 0;
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(2px);
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: var(--font-head);
        font-weight: 800;
        font-size: 0.9rem;
        color: var(--mid);
      }

      /* Quick add overlay */
      .wish-quick-add {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: linear-gradient(
          to top,
          rgba(15, 23, 42, 0.85),
          transparent
        );
        padding: 20px 14px 14px;
        transform: translateY(100%);
        transition: var(--transition);
      }
      .wish-card:hover .wish-quick-add {
        transform: translateY(0);
      }
      .btn-quick-add {
        width: 100%;
        background: #fff;
        color: var(--primary);
        border: none;
        border-radius: 10px;
        padding: 10px;
        font-family: var(--font-head);
        font-weight: 700;
        font-size: 0.84rem;
        cursor: pointer;
        transition: var(--transition);
      }
      .btn-quick-add:hover {
        background: var(--primary);
        color: #fff;
      }

      /* Card body */
      .wish-body {
        padding: 16px;
      }
      .wish-cat {
        font-size: 0.72rem;
        font-weight: 700;
        color: #db2777;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
      }
      .wish-name {
        font-family: var(--font-head);
        font-weight: 700;
        font-size: 0.92rem;
        color: var(--dark);
        margin-bottom: 6px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        line-height: 1.35;
      }
      .wish-rating {
        display: flex;
        align-items: center;
        gap: 4px;
        font-size: 0.78rem;
        color: var(--mid);
        margin-bottom: 10px;
      }
      .wish-rating span {
        color: #f59e0b;
      }
      .wish-price-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
      }
      .wish-price-new {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 1.1rem;
        font-weight: 800;
        color: var(--primary);
      }
      .wish-price-old {
        font-size: 0.8rem;
        color: var(--mid);
        text-decoration: line-through;
        margin-left: 6px;
      }
      .wish-price-save {
        font-size: 0.72rem;
        font-weight: 700;
        background: #dcfce7;
        color: #15803d;
        padding: 2px 8px;
        border-radius: 4px;
      }
      .btn-wish-cart {
        background: var(--primary);
        color: #fff;
        border: none;
        border-radius: 50px;
        padding: 8px 16px;
        font-size: 0.78rem;
        font-weight: 700;
        font-family: var(--font-head);
        cursor: pointer;
        transition: var(--transition);
        white-space: nowrap;
      }
      .btn-wish-cart:hover {
        background: var(--primary-dark);
        transform: scale(1.04);
      }
      .btn-wish-cart.added {
        background: var(--accent2);
      }

      /* EMPTY STATE */
      .wishlist-empty {
        text-align: center;
        padding: 80px 20px;
      }
      .we-icon {
        font-size: 6rem;
        margin-bottom: 20px;
        animation: heartBeat 2.5s ease-in-out infinite;
        display: block;
      }
      .wishlist-empty h3 {
        font-family: var(--font-head);
        font-weight: 800;
        font-size: 1.6rem;
        margin-bottom: 8px;
      }
      .wishlist-empty p {
        color: var(--mid);
        font-size: 0.95rem;
        max-width: 360px;
        margin: 0 auto 24px;
      }

      /* SUGGESTED SECTION */
      .suggested-section {
        background: var(--light);
      }
      .suggested-section .section-title::before {
        content: "💡 ";
      }

      /* SHARE WISHLIST */
      .share-wishlist-box {
        background: linear-gradient(135deg, #fdf2f8, #ede9fe);
        border: 1.5px solid #fbcfe8;
        border-radius: 20px;
        padding: 28px 32px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
        margin-top: 32px;
      }
      .swb-left h5 {
        font-family: var(--font-head);
        font-weight: 800;
        margin-bottom: 4px;
      }
      .swb-left p {
        color: var(--mid);
        font-size: 0.88rem;
        margin: 0;
      }
      .share-btns {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
      }
      .btn-share {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        border-radius: 10px;
        font-family: var(--font-head);
        font-weight: 700;
        font-size: 0.83rem;
        border: none;
        cursor: pointer;
        transition: var(--transition);
        text-decoration: none;
      }
      .btn-share:hover {
        transform: translateY(-2px);
      }
      .btn-share-link {
        background: var(--primary);
        color: #fff;
      }
      .btn-share-wa {
        background: #25d366;
        color: #fff;
      }
      .btn-share-copy {
        background: var(--light);
        color: var(--dark);
        border: 1.5px solid var(--border);
      }
      .btn-share-copy:hover {
        border-color: var(--primary);
        color: var(--primary);
      }

      /* STATS ROW */
      .wish-stats-row {
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 24px;
      }
      .wish-stat-pill {
        background: #fff;
        border: 1.5px solid var(--border);
        border-radius: 50px;
        padding: 8px 18px;
        font-size: 0.83rem;
        font-weight: 600;
        color: var(--mid);
        display: flex;
        align-items: center;
        gap: 6px;
      }
      .wish-stat-pill strong {
        color: var(--dark);
      }
      .wish-stat-pill .dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
      }

      @media (max-width: 767px) {
        .wishlist-hero {
          padding: 36px 0 32px;
        }
        .wishlist-toolbar {
          flex-direction: column;
          align-items: flex-start;
        }
        .wt-right {
          width: 100%;
          justify-content: space-between;
        }
        .share-wishlist-box {
          flex-direction: column;
        }
      }
    </style>
  </head>
  <body>
    <!-- ANNOUNCE BAR -->
    <div class="announce-bar">
      <span
        >💝 Wishlist Sale — Extra 10% off on saved items this week! Code:
        <strong>WISHSALE</strong></span
      >
    </div>

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg sticky-top" id="mainNav">
      <div class="container">
        <a class="navbar-brand" href="index.php">
          <span class="brand-icon">📚</span
          ><span>Study<strong>Mart</strong></span>
        </a>
        <button
          class="navbar-toggler"
          type="button"
          data-bs-toggle="collapse"
          data-bs-target="#navCollapse"
        >
          <span class="hamburger-icon"><i class="fa fa-bars"></i></span>
        </button>
        <div class="collapse navbar-collapse" id="navCollapse">
          <ul class="navbar-nav mx-auto gap-1">
            <li class="nav-item">
              <a class="nav-link" href="index.php">Home</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="shop.php">Shop</a>
            </li>
        
            <li class="nav-item">
              <a class="nav-link" href="about.php">About</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="contact.php">Contact</a>
            </li>
          </ul>
          <div class="nav-actions d-flex align-items-center gap-3">
            <div class="nav-search-wrap">
              <input
                type="text"
                class="nav-search"
                placeholder="Search products…"
              />
              <i class="fa fa-search search-icon"></i>
            </div>
            <a href="wishlist.php" class="nav-icon-btn" style="color: #db2777">
              <i class="fa fa-heart"></i>
            </a>
            <a href="cart.php" class="nav-icon-btn cart-btn">
              <i class="fa fa-shopping-cart"></i>
              <span class="cart-badge" id="cartCount">0</span>
            </a>
            <a href="login.php" class="btn btn-sm btn-login">Login</a>
          </div>
        </div>
      </div>
    </nav>

    <!-- ═══════════════════════════════════════
     HERO
═══════════════════════════════════════ -->
    <section class="wishlist-hero">
      <div class="container wishlist-hero-inner">
        <div class="row align-items-center">
          <div class="col-lg-8">
            <h1>My <span>Wishlist</span> 💕</h1>
            <p>
              Products you've saved — add them to cart whenever you're ready.
            </p>
            <div class="wishlist-badge-count" id="heroWishCount">
              <i class="fa fa-heart"></i>
              <span id="heroCountText">0 items saved</span>
            </div>
          </div>
          <div class="col-lg-4 text-center d-none d-lg-block">
            <div class="wishlist-hero-icon">💝</div>
          </div>
        </div>
      </div>
    </section>

    <!-- ═══════════════════════════════════════
     MAIN WISHLIST SECTION
═══════════════════════════════════════ -->
    <section class="py-5">
      <div class="container">
        <!-- Empty State -->
        <div id="wishlistEmpty" style="display: none">
          <div class="wishlist-empty">
            <span class="we-icon">🤍</span>
            <h3>Your Wishlist is Empty</h3>
            <p>
              You haven't saved any products yet. Browse our store and tap the
              heart icon to save items here.
            </p>
            <a href="shop.php" class="btn btn-hero-primary">
              <i class="fa fa-store me-2"></i> Browse Products
            </a>
            <div class="mt-4">
              <p style="color: var(--mid); font-size: 0.85rem">
                Discover popular items 👇
              </p>
            </div>
          </div>
        </div>

        <!-- Wishlist Content -->
        <div id="wishlistContent">
          <!-- Stats Row -->
          <div class="wish-stats-row" id="wishStatsRow"></div>

          <!-- Toolbar -->
          <div class="wishlist-toolbar">
            <div class="wt-left">
              <span class="wt-count"
                ><strong id="toolbarCount">0</strong> items in your
                wishlist</span
              >
              <select
                class="sort-select-wish"
                id="wishSort"
                onchange="sortWishlist()"
              >
                <option value="default">Sort: Default</option>
                <option value="price-asc">Price: Low to High</option>
                <option value="price-desc">Price: High to Low</option>
                <option value="discount">Highest Discount</option>
                <option value="rating">Top Rated</option>
              </select>
            </div>
            <div class="wt-right">
              <button class="btn-move-all" onclick="moveAllToCart()">
                <i class="fa fa-shopping-cart"></i> Add All to Cart
              </button>
              <button class="btn-clear-wish" onclick="clearWishlist()">
                <i class="fa fa-trash me-1"></i> Clear All
              </button>
            </div>
          </div>

          <!-- Product Grid -->
          <div class="row g-4" id="wishGrid"></div>

          <!-- Share Wishlist -->
          <div class="share-wishlist-box" id="shareBox">
            <div class="swb-left">
              <h5>📤 Share Your Wishlist</h5>
              <p>
                Send your wishlist to friends or family — perfect for gifting!
              </p>
            </div>
            <div class="share-btns">
              <button
                class="btn-share btn-share-wa"
                onclick="shareWishlist('whatsapp')"
              >
                <i class="fab fa-whatsapp"></i> WhatsApp
              </button>
              <button
                class="btn-share btn-share-link"
                onclick="shareWishlist('link')"
              >
                <i class="fa fa-link"></i> Share Link
              </button>
              <button
                class="btn-share btn-share-copy"
                id="copyShareBtn"
                onclick="shareWishlist('copy')"
              >
                <i class="fa fa-copy"></i> Copy List
              </button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ═══════════════════════════════════════
     YOU MAY ALSO LIKE (Suggested)
═══════════════════════════════════════ -->
    <section class="suggested-section py-5">
      <div class="container">
        <div class="section-header mb-4">
          <h2 class="section-title">You May Also Like</h2>
          <a href="shop.php" class="see-all"
            >See All <i class="fa fa-arrow-right"></i
          ></a>
        </div>
        <div class="row g-4" id="suggestedGrid"></div>
      </div>
    </section>

    <!-- RECENTLY VIEWED -->
    <section class="py-5" style="background: #fff">
      <div class="container">
        <div class="section-header mb-4">
          <h2 class="section-title">🕐 Recently Added</h2>
          <a href="#" class="see-all"
            >Today's Deals <i class="fa fa-arrow-right"></i
          ></a>
        </div>
        <div class="row g-4" id="recentGrid"></div>
      </div>
    </section>

    <!-- FULL FOOTER -->
    <footer class="site-footer pt-5 pb-3">
      <div class="container">
        <div class="row g-4 mb-4">
          <div class="col-lg-3 col-md-6">
            <div class="footer-brand">
              <span class="brand-icon">📚</span
              ><span>Study<strong>Mart</strong></span>
            </div>
            <p class="footer-desc mt-3">
              Your one-stop destination for all student essentials.
            </p>
            <div class="social-links mt-3">
              <a href="#"><i class="fab fa-instagram"></i></a>
              <a href="#"><i class="fab fa-twitter"></i></a>
              <a href="#"><i class="fab fa-facebook"></i></a>
              <a href="#"><i class="fab fa-youtube"></i></a>
            </div>
          </div>
          <div class="col-lg-2 col-6">
            <h6>Quick Links</h6>
            <ul class="footer-links">
              <li><a href="index.php">Home</a></li>
              <li><a href="shop.php">Shop</a></li>
        
              <li><a href="about.php">About</a></li>
              <li><a href="contact.php">Contact</a></li>
            </ul>
          </div>
          <div class="col-lg-2 col-6">
            <h6>Categories</h6>
            <ul class="footer-links">
              <li><a href="shop.php">Books</a></li>
              <li><a href="shop.php">Electronics</a></li>
              <li><a href="shop.php">Stationery</a></li>
              <li><a href="shop.php">Bags</a></li>
              <li><a href="shop.php">Lab Supplies</a></li>
            </ul>
          </div>
          <div class="col-lg-2 col-6">
            <h6>Support</h6>
            <ul class="footer-links">
              <li><a href="#">FAQ</a></li>
              <li><a href="#">Returns</a></li>
              <li><a href="#">Shipping Info</a></li>
              <li><a href="#">Track Order</a></li>
            </ul>
          </div>
          <div class="col-lg-3 col-md-6">
            <h6>Contact Us</h6>
            <ul class="footer-contact">
              <li>
                <i class="fa fa-map-marker-alt"></i> 42, Edu Street, Jaipur,
                Rajasthan
              </li>
              <li><i class="fa fa-phone"></i> +91 98765 43210</li>
              <li><i class="fa fa-envelope"></i> hello@studymart.in</li>
            </ul>
          </div>
        </div>
        <div class="footer-bottom">
          <span>© 2025 StudyMart. All rights reserved.</span>
          <div class="payment-icons">
            <img
              src="https://upload.wikimedia.org/wikipedia/commons/2/2a/Mastercard-logo.svg"
              alt="Mastercard"
            />
            <img
              src="https://upload.wikimedia.org/wikipedia/commons/5/5e/Visa_Inc._logo.svg"
              alt="Visa"
            />
            <span class="pay-text">UPI</span>
            <span class="pay-text">Paytm</span>
          </div>
        </div>
      </div>
    </footer>

    <div class="cart-toast" id="cartToast">
      <i class="fa fa-check-circle"></i> Done!
    </div>
    <button class="back-to-top" id="backTop" onclick="scrollToTop()">
      <i class="fa fa-arrow-up"></i>
    </button>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
    <script src="app.js"></script>
    <script>
      // ── WISHLIST PAGE LOGIC ────────────────────────────────────────────

      // Render everything
      function renderWishlistPage() {
        updateCartBadge();
        const wishItems = products.filter((p) => wishlist.includes(p.id));

        // Hero count
        const countText = document.getElementById("heroCountText");
        if (countText)
          countText.textContent = `${wishItems.length} item${wishItems.length !== 1 ? "s" : ""} saved`;

        // Toolbar count
        const tc = document.getElementById("toolbarCount");
        if (tc) tc.textContent = wishItems.length;

        const empty = document.getElementById("wishlistEmpty");
        const content = document.getElementById("wishlistContent");
        const grid = document.getElementById("wishGrid");
        const shareBox = document.getElementById("shareBox");

        if (wishItems.length === 0) {
          if (empty) empty.style.display = "block";
          if (content) content.style.display = "none";
        } else {
          if (empty) empty.style.display = "none";
          if (content) content.style.display = "block";
          if (grid)
            grid.innerHTML = wishItems.map((p) => buildWishCard(p)).join("");
          if (shareBox)
            shareBox.style.display = wishItems.length > 0 ? "flex" : "none";
          renderStatsRow(wishItems);
        }

        renderSuggested(wishItems);
        renderRecent();
      }

      function buildWishCard(p) {
        const disc = p.oldPrice
          ? Math.round((1 - p.price / p.oldPrice) * 100)
          : 0;
        const save = p.oldPrice ? p.oldPrice - p.price : 0;
        return `
  <div class="col-6 col-md-4 col-lg-3" id="wishCard${p.id}">
    <div class="wish-card">
      <div class="wish-img-wrap">
        <img src="${p.img}" alt="${p.name}" loading="lazy" />
        ${disc > 0 ? `<span class="wish-disc-badge">-${disc}%</span>` : ""}
        <button class="wish-remove-btn" title="Remove from Wishlist" onclick="removeFromWishlist(${p.id})">
          <i class="fa fa-times"></i>
        </button>
        ${!p.inStock ? '<div class="wish-oos">Out of Stock</div>' : ""}
        ${
          p.inStock
            ? `
        <div class="wish-quick-add">
          <button class="btn-quick-add" id="qab${p.id}" onclick="quickAddToCart(${p.id})">
            <i class="fa fa-bolt me-1"></i> Quick Add to Cart
          </button>
        </div>`
            : ""
        }
      </div>
      <div class="wish-body">
        <div class="wish-cat">${p.cat}</div>
        <div class="wish-name">${p.name}</div>
        <div class="wish-rating">
          <span>${"★".repeat(Math.floor(p.rating))}</span>
          ${p.rating} &nbsp;(${p.reviews} reviews)
        </div>
        <div class="wish-price-row">
          <div>
            <span class="wish-price-new">₹${p.price.toLocaleString()}</span>
            ${p.oldPrice ? `<span class="wish-price-old">₹${p.oldPrice.toLocaleString()}</span>` : ""}
            ${save > 0 ? `<div class="wish-price-save mt-1">Save ₹${save.toLocaleString()}</div>` : ""}
          </div>
          <button
            class="btn-wish-cart"
            id="cartBtn${p.id}"
            onclick="addToCartFromWish(${p.id})"
            ${!p.inStock ? 'disabled style="opacity:.45;cursor:not-allowed;"' : ""}
          >
            ${p.inStock ? '<i class="fa fa-cart-plus"></i>' : "Sold Out"}
          </button>
        </div>
      </div>
    </div>
  </div>`;
      }

      function renderStatsRow(items) {
        const row = document.getElementById("wishStatsRow");
        if (!row) return;
        const totalSave = items
          .filter((p) => p.oldPrice)
          .reduce((s, p) => s + (p.oldPrice - p.price), 0);
        const totalValue = items.reduce((s, p) => s + p.price, 0);
        const inStock = items.filter((p) => p.inStock).length;
        const onSale = items.filter((p) => p.badge === "sale").length;

        row.innerHTML = `
    <div class="wish-stat-pill">
      <span class="dot" style="background:#2563eb;"></span>
      Total: <strong>₹${totalValue.toLocaleString()}</strong>
    </div>
    <div class="wish-stat-pill">
      <span class="dot" style="background:#10b981;"></span>
      Potential savings: <strong>₹${totalSave.toLocaleString()}</strong>
    </div>
    <div class="wish-stat-pill">
      <span class="dot" style="background:#f59e0b;"></span>
      In stock: <strong>${inStock}/${items.length}</strong>
    </div>
    <div class="wish-stat-pill">
      <span class="dot" style="background:#ef4444;"></span>
      On sale: <strong>${onSale} items</strong>
    </div>
  `;
      }

      function removeFromWishlist(id) {
        wishlist = wishlist.filter((i) => i !== id);
        saveWishlist();
        // Animate card out
        const card = document.getElementById("wishCard" + id);
        if (card) {
          card.style.transition = "all .35s ease";
          card.style.opacity = "0";
          card.style.transform = "scale(.85)";
          setTimeout(() => renderWishlistPage(), 360);
        }
        showToast("Removed from wishlist");
      }

      function addToCartFromWish(id) {
        addToCart(id);
        const btn = document.getElementById("cartBtn" + id);
        if (btn) {
          btn.innerHTML = '<i class="fa fa-check"></i>';
          btn.classList.add("added");
          setTimeout(() => {
            btn.innerHTML = '<i class="fa fa-cart-plus"></i>';
            btn.classList.remove("added");
          }, 1800);
        }
      }

      function quickAddToCart(id) {
        addToCart(id);
        const btn = document.getElementById("qab" + id);
        if (btn) {
          btn.textContent = "✓ Added!";
          btn.style.background = "var(--accent2)";
          btn.style.color = "#fff";
          setTimeout(() => {
            btn.innerHTML = '<i class="fa fa-bolt"></i> Quick Add to Cart';
            btn.style.background = "";
            btn.style.color = "";
          }, 1800);
        }
      }

      function moveAllToCart() {
        const wishItems = products.filter(
          (p) => wishlist.includes(p.id) && p.inStock,
        );
        if (wishItems.length === 0) {
          showToast("No in-stock items to add!");
          return;
        }
        wishItems.forEach((p) => addToCart(p.id));
        showToast(`🛒 ${wishItems.length} items added to cart!`);
      }

      function clearWishlist() {
        if (!confirm("Remove all items from your wishlist?")) return;
        wishlist = [];
        saveWishlist();
        renderWishlistPage();
        showToast("Wishlist cleared");
      }

      function sortWishlist() {
        const sort = document.getElementById("wishSort")?.value;
        let items = products.filter((p) => wishlist.includes(p.id));
        if (sort === "price-asc") items.sort((a, b) => a.price - b.price);
        if (sort === "price-desc") items.sort((a, b) => b.price - a.price);
        if (sort === "discount")
          items.sort((a, b) => {
            const da = a.oldPrice ? a.oldPrice - a.price : 0;
            const db = b.oldPrice ? b.oldPrice - b.price : 0;
            return db - da;
          });
        if (sort === "rating") items.sort((a, b) => b.rating - a.rating);
        const grid = document.getElementById("wishGrid");
        if (grid) grid.innerHTML = items.map((p) => buildWishCard(p)).join("");
      }

      function renderSuggested(wishItems) {
        const grid = document.getElementById("suggestedGrid");
        if (!grid) return;
        const wishIds = wishItems.map((p) => p.id);
        const cats = [...new Set(wishItems.map((p) => p.cat))];
        let suggestions = products
          .filter((p) => !wishIds.includes(p.id) && cats.includes(p.cat))
          .slice(0, 4);
        // Fill if not enough
        if (suggestions.length < 4) {
          const extras = products
            .filter((p) => !wishIds.includes(p.id) && !suggestions.includes(p))
            .slice(0, 4 - suggestions.length);
          suggestions = [...suggestions, ...extras];
        }
        grid.innerHTML = suggestions
          .slice(0, 4)
          .map(
            (p) =>
              `<div class="col-6 col-md-4 col-lg-3">${createProductCard(p)}</div>`,
          )
          .join("");
      }

      function renderRecent() {
        const grid = document.getElementById("recentGrid");
        if (!grid) return;
        const recent = products.filter((p) => p.badge === "new").slice(0, 4);
        grid.innerHTML = recent
          .map(
            (p) =>
              `<div class="col-6 col-md-4 col-lg-3">${createProductCard(p)}</div>`,
          )
          .join("");
      }

      function shareWishlist(type) {
        const wishItems = products.filter((p) => wishlist.includes(p.id));
        const names = wishItems
          .map((p) => `• ${p.name} — ₹${p.price}`)
          .join("\n");
        const text = `My StudyMart Wishlist 💕\n\n${names}\n\nShop at: https://studymart.in`;

        if (type === "whatsapp") {
          window.open(
            `https://wa.me/?text=${encodeURIComponent(text)}`,
            "_blank",
          );
        } else if (type === "link") {
          showToast("🔗 Share link copied!");
        } else if (type === "copy") {
          navigator.clipboard.writeText(text).catch(() => {});
          const btn = document.getElementById("copyShareBtn");
          if (btn) {
            btn.innerHTML = '<i class="fa fa-check"></i> Copied!';
            btn.style.background = "#dcfce7";
            btn.style.color = "#15803d";
            btn.style.borderColor = "#86efac";
            setTimeout(() => {
              btn.innerHTML = '<i class="fa fa-copy"></i> Copy List';
              btn.style.background = "";
              btn.style.color = "";
              btn.style.borderColor = "";
            }, 2000);
          }
          showToast("📋 Wishlist copied to clipboard!");
        }
      }

      // ── INIT ────────────────────────────────────────────────────────
      document.addEventListener("DOMContentLoaded", () => {
        // Demo: Add some products to wishlist if empty
        if (wishlist.length === 0) {
          wishlist = [1, 2, 5, 9, 11];
          saveWishlist();
        }
        renderWishlistPage();
      });
    </script>
  </body>
</html>
