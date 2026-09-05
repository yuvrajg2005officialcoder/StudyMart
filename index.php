<?php require_once 'config.php'; ?>

<?php
// Home Section fetch karo
$hero = null;
try {
  $stmt = $pdo->query("SELECT * FROM home_section WHERE status = 0 LIMIT 1");
  $hero = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  $hero = null;
}
?>



<?php
// All Products DB se fetch karo
$featured_products = [];
try {
  $stmt = $pdo->query("
    SELECT p.product_id, p.product_name, p.product_price, p.old_price,
           p.rating, p.reviews_count, p.product_img, p.quantity,
           c.cat_name
    FROM product p
    LEFT JOIN category c ON p.cat_id = c.cat_id
    WHERE p.is_featured = 1 AND p.product_status = 1
    ORDER BY p.product_id DESC
    LIMIT 8
");
  $featured_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  $featured_products = [];
}
?>



<?php
function getCategoryIcon($name)
{
  $name = strtolower(trim($name));
  $icons = [
    'book' => '📚',
    'books' => '📚',
    'electronics' => '💻',
    'stationery' => '✏️',
    'bags' => '🎒',
    'bag' => '🎒',
    'lab' => '🔬',
    'lab supply' => '🔬',
    'printing' => '🖨️',
    'shoes' => '👟',
    'notes' => '📝',
    'kasol' => '🏔️',
    'manali' => '🏔️',
  ];
  foreach ($icons as $key => $icon) {
    if (strpos($name, $key) !== false)
      return $icon;
  }
  return '🛍️';
}


function getCategoryColor($index)
{
  $colors = ['#FF6B6B', '#4ECDC4', '#FFE66D', '#A8E6CF', '#C9B1FF', '#FFA07A', '#74B9FF', '#FD79A8'];
  return $colors[$index % count($colors)];
}





$categories = [];
try {
  $stmt = $pdo->query("SELECT * FROM category WHERE cat_status = 0 ORDER BY cat_id ASC");
  $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  $categories = [];
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>StudyMart – The Student Store</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link
    href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,400&display=swap"
    rel="stylesheet" />
  <link rel="stylesheet" href="style.css" />
</head>

<style>
  #productGrid {
    align-items: stretch;
  }

  #productGrid>div {
    display: flex !important;
  }

  #productGrid .product-card {
    width: 100%;
    display: flex;
    flex-direction: column;
  }

  #productGrid .product-img-wrap {
    height: 250px !important;
    min-height: 300px !important;
    max-height: 300px !important;
    overflow: hidden;
    flex-shrink: 0;
  }

  #productGrid .product-img-wrap img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  #productGrid .product-body {
    flex: 1;
    display: flex;
    flex-direction: column;
  }

  #productGrid .product-price-row {
    margin-top: auto;
  }
</style>

<body>

  <!-- ANNOUNCEMENT BAR -->
  <div class="announce-bar">
    <span>🎓 Student Exclusive: Extra 15% off with code <strong>STUDY15</strong> &nbsp;|&nbsp; Free shipping on orders
      above ₹499</span>
  </div>

  <!-- NAVBAR -->
  <nav class="navbar navbar-expand-lg sticky-top" id="mainNav">
    <div class="container">
      <a class="navbar-brand" href="index.php">
        <span class="brand-icon">📚</span>
        <span>Study<strong>Mart</strong></span>
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navCollapse">
        <span class="hamburger-icon"><i class="fa fa-bars"></i></span>
      </button>
      <div class="collapse navbar-collapse" id="navCollapse">
        <ul class="navbar-nav mx-auto gap-1">
          <li class="nav-item"><a class="nav-link active" href="index.php">Home</a></li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Categories</a>
            <ul class="dropdown-menu mega-menu">
              <?php if (!empty($categories)): ?>
                <?php foreach ($categories as $cat): ?>
                  <li>
                    <a class="dropdown-item" href="subcategory.php?category=<?= $cat['cat_id'] ?>">
                      <?= getCategoryIcon($cat['cat_name']) ?> &nbsp;<?= htmlspecialchars($cat['cat_name']) ?>
                    </a>
                  </li>
                <?php endforeach; ?>
              <?php else: ?>
                <li><a class="dropdown-item" href="shop.php"><i class="fa fa-book me-2"></i>Books & Notes</a></li>
                <li><a class="dropdown-item" href="shop.php"><i class="fa fa-laptop me-2"></i>Electronics</a></li>
                <li><a class="dropdown-item" href="shop.php"><i class="fa fa-ruler me-2"></i>Stationery</a></li>
                <li><a class="dropdown-item" href="shop.php"><i class="fa fa-graduation-cap me-2"></i>Lab Supplies</a>
                </li>s
              <?php endif; ?>
            </ul>
          </li>
          <li class="nav-item"><a class="nav-link" href="shop.php">Shop</a></li>
          <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
          <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
        </ul>
        <div class="nav-actions d-flex align-items-center gap-3">
          <div class="nav-search-wrap">
            <input type="text" class="nav-search" placeholder="Search products…" id="navSearch" />
            <i class="fa fa-search search-icon"></i>
          </div>
          <a href="wishlist.php" class="nav-icon-btn" title="Wishlist"><i class="fa fa-heart"></i></a>
          <a href="cart.php" class="nav-icon-btn cart-btn" title="Cart">
            <i class="fa fa-shopping-cart"></i>
            <span class="cart-badge" id="cartCount">0</span>
          </a>
          <a href="login.php" class="btn btn-sm btn-login">Login</a>
        </div>
      </div>
    </div>
  </nav>

  <!-- HERO SECTION -->
  <section class="hero-section">
    <div class="hero-bg-shapes">
      <div class="shape shape-1"></div>
      <div class="shape shape-2"></div>
      <div class="shape shape-3"></div>
    </div>
    <div class="container">
      <div class="row align-items-center min-vh-85">
        <div class="col-lg-6 hero-text" data-aos="fade-right">
          <div class="badge-pill mb-3">🔥 New Arrivals 2026</div>

          <h1 class="hero-title">
            <?php if ($hero): ?>
              <?= $hero['title'] ?>
            <?php else: ?>
              Everything a <span class="highlight">Student</span><br />Needs, Here.
            <?php endif; ?>
          </h1>

          <p class="hero-sub">
            <?= $hero ? htmlspecialchars($hero['paragraph']) : 'From textbooks to tech gadgets — curated gear for every campus life. Shop smarter, learn harder.' ?>
          </p>

          <div class="hero-actions">
            <a href="<?= $hero ? htmlspecialchars($hero['button_link']) : 'shop.php' ?>" class="btn btn-hero-primary">
              <?= $hero ? htmlspecialchars($hero['button_text']) : 'Explore Shop' ?>
              <i class="fa fa-arrow-right ms-2"></i>
            </a>
            <a href="#" class="btn btn-hero-ghost">Today's Deals</a>
          </div>

          <div class="hero-stats">
            <div class="stat">
              <span><?= $hero ? htmlspecialchars($hero['products']) : '12K+' ?></span>
              <label>Products</label>
            </div>
            <div class="stat">
              <span><?= $hero ? htmlspecialchars($hero['students']) : '50K+' ?></span>
              <label>Students</label>
            </div>
            <div class="stat">
              <span><?= $hero ? htmlspecialchars($hero['rating']) : '4.9★' ?></span>
              <label>Rating</label>
            </div>
          </div>
        </div>

        <div class="col-lg-6 hero-visual d-none d-lg-flex">
          <div class="hero-card-stack">
            <div class="floating-card card-main">
              <?php if ($hero && !empty($hero['image'])): ?>
                <img src="admin/assets/img/home/<?= htmlspecialchars($hero['image']) ?>" alt="Hero" />
              <?php else: ?>
                <img src="https://images.unsplash.com/photo-1501504905252-473c47e087f8?w=400&q=80" alt="Student" />
              <?php endif; ?>
            </div>
            <div class="floating-badge badge-discount">-40% OFF</div>
            <div class="floating-badge badge-new">New ✨</div>
            <div class="product-float-card">
              <img src="https://images.unsplash.com/photo-1585386959984-a4155224a1ad?w=60&q=80" alt="" />
              <div>
                <strong>Noise Headphones</strong>
                <span>₹1,299</span>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- FEATURED CATEGORIES GRID -->
  <section class="categories-section py-5">
    <div class="container">
      <div class="section-header">
        <h2 class="section-title">Shop by Category</h2>
        <a href="shop.php" class="see-all">See All <i class="fa fa-arrow-right"></i></a>
      </div>
      <div class="row g-3 cat-grid">
        <?php if (!empty($categories)): ?>
          <?php foreach ($categories as $index => $cat): ?>
            <?php
            $catName = $cat['cat_name'];
            $icon = getCategoryIcon($catName);
            $color = getCategoryColor($index);
            $imgFile = $cat['cat_img'] ?? '';
            $imgPath = !empty($imgFile) ? 'admin/assets/images/uploads/' . $imgFile : '';
            ?>
            <div class="col-6 col-md-4 col-lg-2">
              <a href="subcategory.php?category=<?= $cat['cat_id'] ?>" class="cat-card" style="--cat-color:<?= $color ?>;">
                <div class="cat-icon">
                  <?php if (!empty($imgPath) && file_exists($imgPath)): ?>
                    <img src="<?= htmlspecialchars($imgPath) ?>" alt="<?= htmlspecialchars($catName) ?>"
                      style="width:52px;height:52px;object-fit:cover;border-radius:12px;" />
                  <?php else: ?>
                    <?= $icon ?>
                  <?php endif; ?>
                </div>
                <span><?= htmlspecialchars($catName) ?></span>
                <small>Active</small>
              </a>
            </div>

          <?php endforeach; ?>
        <?php else: ?>
          <div class="col-12 text-center text-muted py-4">
            <p>⚠️ No categories found in database.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>




  <!-- All PRODUCTS -->
  <section class="products-section py-5" id="featuredProducts">
    <div class="container">
      <div class="section-header">
        <h2 class="section-title">All Products</h2>
        <a href="shop.php" class="see-all">See All <i class="fa fa-arrow-right"></i></a>
      </div>
      <div class="row g-4" id="productGrid">
        <!-- JS renders products here -->
      </div>
    </div>
  </section>

  <!-- PROMO BANNER -->
  <section class="promo-banner py-5">
    <div class="container">
      <div class="promo-card row g-0 align-items-center">
        <div class="col-lg-7 promo-text p-5">
          <div class="badge-pill mb-3">⚡ Limited Time</div>
          <h2>Student Bundle Deals</h2>
          <p>Get your complete semester kit — notebook + pens + highlighters + scientific calculator at 35% off.</p>
          <a href="#" class="btn btn-promo">Grab the Bundle</a>
        </div>
        <div class="col-lg-5 promo-image d-none d-lg-block"></div>
      </div>
    </div>
  </section>

  <!-- TESTIMONIALS -->
  <section class="testimonials-section py-5">
    <div class="container">
      <div class="section-header text-center">
        <h2 class="section-title">What Students Say</h2>
        <p class="section-sub">Trusted by 50,000+ students across India</p>
      </div>
      <div class="row g-4 mt-2">
        <div class="col-md-4">
          <div class="testi-card">
            <div class="testi-stars">★★★★★</div>
            <p>"StudyMart saved my semester! Got all my lab supplies delivered next day. Amazing service and quality."
            </p>
            <div class="testi-author">
              <div class="testi-avatar">RK</div>
              <div><strong>Rahul K.</strong><small>B.Tech CSE, IIT Delhi</small></div>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="testi-card featured-testi">
            <div class="testi-stars">★★★★★</div>
            <p>"Best prices for engineering books anywhere online. The student discount makes it even better. Highly
              recommend!"</p>
            <div class="testi-author">
              <div class="testi-avatar">AP</div>
              <div><strong>Ananya P.</strong><small>MBA, IIM Bangalore</small></div>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="testi-card">
            <div class="testi-stars">★★★★★</div>
            <p>"Fast delivery and genuine products. The stationery bundle is a game-changer for note-taking. Will buy
              again!"</p>
            <div class="testi-author">
              <div class="testi-avatar">MS</div>
              <div><strong>Meera S.</strong><small>MBBS, AIIMS</small></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- NEWSLETTER -->
  <section class="newsletter-section py-5">
    <div class="container">
      <div class="newsletter-box">
        <div class="row align-items-center">
          <div class="col-lg-6 mb-4 mb-lg-0">
            <h3>Join the StudyMart Community</h3>
            <p>Get exclusive deals, new arrivals & study tips straight to your inbox.</p>
          </div>
          <div class="col-lg-6">
            <div class="newsletter-form">
              <input type="email" placeholder="Enter your student email…" id="newsletterEmail" />
              <button onclick="subscribeNewsletter()">Subscribe</button>
            </div>
            <small class="text-muted mt-2 d-block">No spam. Unsubscribe anytime.</small>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- FOOTER -->
  <footer class="site-footer pt-5 pb-3">
    <div class="container">
      <div class="row g-4 mb-4">
        <div class="col-lg-3 col-md-6">
          <div class="footer-brand">
            <span class="brand-icon">📚</span>
            <span>Study<strong>Mart</strong></span>
          </div>
          <p class="footer-desc mt-3">Your one-stop destination for all student essentials. Quality products, student
            prices.</p>
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
            <li><a href="about.php">About Us</a></li>
            <li><a href="contact.php">Contact</a></li>
          </ul>
        </div>
        <div class="col-lg-2 col-6">
          <h6>Categories</h6>
          <ul class="footer-links">
            <?php foreach (array_slice($categories, 0, 5) as $cat): ?>
              <li><a href="subcategory.php?category=<?= $cat['cat_id'] ?>"><?= htmlspecialchars($cat['cat_name']) ?></a>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
        <div class="col-lg-2 col-6">
          <h6>Support</h6>
          <ul class="footer-links">
            <li><a href="#">FAQ</a></li>
            <li><a href="#">Returns</a></li>
            <li><a href="#">Shipping Info</a></li>
            <li><a href="#">Track Order</a></li>
            <li><a href="#">Privacy Policy</a></li>
          </ul>
        </div>
        <div class="col-lg-3 col-md-6">
          <h6>Contact Us</h6>
          <ul class="footer-contact">
            <li><i class="fa fa-map-marker-alt"></i> 42, Edu Street, Jaipur, Rajasthan</li>
            <li><i class="fa fa-phone"></i> +91 98765 43210</li>
            <li><i class="fa fa-envelope"></i> hello@studymart.in</li>
            <li><i class="fa fa-clock"></i> Mon–Sat: 9AM – 6PM</li>
          </ul>
        </div>
      </div>
      <div class="footer-bottom">
        <span>© 2026 StudyMart. All rights reserved.</span>
        <div class="payment-icons">
          <img src="https://upload.wikimedia.org/wikipedia/commons/2/2a/Mastercard-logo.svg" alt="Mastercard" />
          <img src="https://upload.wikimedia.org/wikipedia/commons/5/5e/Visa_Inc._logo.svg" alt="Visa" />
          <span class="pay-text">UPI</span>
          <span class="pay-text">Paytm</span>
        </div>
      </div>
    </div>
  </footer>

  <!-- CART TOAST -->
  <div class="cart-toast" id="cartToast">
    <i class="fa fa-check-circle"></i> Added to cart!
  </div>

  <!-- BACK TO TOP -->
  <button class="back-to-top" id="backTop" onclick="scrollToTop()"><i class="fa fa-arrow-up"></i></button>

  <!-- ✅ DB Products ko JS mein pass karo -->

  <script>
    const dbProducts = <?php echo json_encode(array_map(function ($p) {
      $imgFile = trim($p['product_img'] ?? '');
      return [
        'id' => (int) $p['product_id'],
        'name' => $p['product_name'],
        'cat' => strtolower(trim($p['cat_name'] ?? 'general')),
        'price' => (float) $p['product_price'],
        'oldPrice' => !empty($p['old_price']) ? (float) $p['old_price'] : null,
        'rating' => (float) ($p['rating'] ?? 0),
        'reviews' => (int) ($p['reviews_count'] ?? 0),
        'img' => !empty($imgFile)
          ? 'admin/assets/img/products/' . $imgFile
          : 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=400&q=80',
        'badge' => !empty($p['old_price']) ? 'sale' : '',
        'inStock' => ((int) ($p['quantity'] ?? 0)) > 0,
      ];
    }, $featured_products)); ?>;
  </script>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
  <script src="app.js?v=<?php echo time(); ?>"></script> <!-- Cache bust -->


  <script>
    document.addEventListener("DOMContentLoaded", function () {
      const grid = document.getElementById("productGrid");
      if (!grid) return;

      const source = (typeof dbProducts !== "undefined" && dbProducts.length > 0)
        ? dbProducts
        : [];

      if (source.length === 0) return;

      grid.innerHTML = source.slice(0, 8).map(function (p) {
        const disc = p.oldPrice ? Math.round((1 - p.price / p.oldPrice) * 100) : 0;
        const img = p.img || 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=400&q=80';
        return `<div class="col-6 col-md-4 col-lg-3" style="display:flex;">
           <div class="product-card" data-id="${p.id}" onclick="openProductPage(${p.id})">
                <div class="product-img-wrap">
                    <img src="${img}" alt="${p.name}" loading="lazy"
                         onerror="this.src='https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=400&q=80'" />
                    <div class="product-badges">
                        ${disc > 0 ? `<span class="badge-sale">-${disc}%</span>` : ""}
                        ${!p.inStock ? `<span class="badge-sale" style="background:#64748b;">Out of Stock</span>` : ""}
                    </div>
                </div>
                <div class="product-body">
                    <div class="product-cat">${p.cat}</div>
                    <div class="product-name">${p.name}</div>
                    <div class="product-rating">
                        <span style="color:#f59e0b;">${"★".repeat(Math.floor(p.rating || 0))}</span>
                        <span>${p.rating} (${p.reviews})</span>
                    </div>
                    <div class="product-price-row">
                        <div>
                            <span class="price-new">₹${Number(p.price).toLocaleString()}</span>
                            ${p.oldPrice ? `<span class="price-old ms-1">₹${Number(p.oldPrice).toLocaleString()}</span>` : ""}
                        </div>
                        <button class="btn-add-cart" onclick="event.stopPropagation(); addToCart(${p.id})">
                            + Cart
                        </button>
                    </div>
                </div>
            </div>
        </div>`;
      }).join("");
    });
  </script>

  <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
<script src="app.js"></script> -->


</body>

</html>