<?php require_once 'config.php'; ?>
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
    'shoes' => '👟',
    'footwear' => '👟',
    'notes' => '📝',
    'mobile' => '📱',
    'tablet' => '📱',
    'clothing' => '👕',
    'sports' => '⚽'
  ];
  foreach ($icons as $key => $icon)
    if (strpos($name, $key) !== false)
      return $icon;
  return '🛍️';
}

function getCategoryColor($index)
{
  $colors = ['#6366f1', '#0ea5e9', '#f59e0b', '#10b981', '#ec4899', '#f97316', '#8b5cf6', '#14b8a6'];
  return $colors[$index % count($colors)];
}

$categories = [];
try {
  $stmt = $pdo->query("SELECT * FROM category WHERE cat_status = 0 ORDER BY cat_id ASC");
  $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  $categories = [];
}

$subcatId = isset($_GET['subcat']) ? (int) $_GET['subcat'] : 0;
$currentSubcat = null;
if ($subcatId > 0) {
  try {
    $stmt = $pdo->prepare("SELECT s.*, c.cat_name, c.cat_id FROM subcategory s LEFT JOIN category c ON s.cat_id = c.cat_id WHERE s.sub_id = ?");
    $stmt->execute([$subcatId]);
    $currentSubcat = $stmt->fetch(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    $currentSubcat = null;
  }
}

$sort = $_GET['sort'] ?? 'latest';
$orderBy = match ($sort) {
  'price_low' => 'p.product_price ASC',
  'price_high' => 'p.product_price DESC',
  'name' => 'p.product_name ASC',
  'rating' => 'p.rating DESC',
  default => 'p.product_id DESC',
};

$products = [];
if ($subcatId > 0) {
  try {
    $stmt = $pdo->prepare("
            SELECT p.*, b.brand_name
            FROM product p
            LEFT JOIN brands b ON p.brand_id = b.brand_id
            WHERE p.sub_cat_id = ? AND p.product_status = 1
            ORDER BY {$orderBy}
        ");
    $stmt->execute([$subcatId]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    $products = [];
  }
}

$subcatName = $currentSubcat['sub_name'] ?? 'Products';
$categoryName = $currentSubcat['cat_name'] ?? 'Category';
$categoryId = $currentSubcat['cat_id'] ?? 0;
$subcatIcon = getCategoryIcon($subcatName);
$accent = getCategoryColor($subcatId);

// ✅ FIX: Base path for file_exists check
$basePath = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/' . trim(dirname($_SERVER['SCRIPT_NAME']), '/');
$basePath = rtrim($basePath, '/') . '/';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($subcatName) ?> – StudyMart</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link
    href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,400&display=swap"
    rel="stylesheet" />
  <link rel="stylesheet" href="style.css" />
  <style>
    body {
      background: #f4f5f7;
    }

    /* ── Banner ── */
    .subcat-banner {
      background: #fff;
      border-bottom: 1px solid #ebebeb;
      padding: 38px 0 30px;
    }

    .breadcrumb-row {
      font-family: 'DM Sans', sans-serif;
      font-size: .85rem;
      color: #9a9a9a;
      margin-bottom: 14px;
      display: flex;
      align-items: center;
      flex-wrap: wrap;
      gap: 4px;
    }

    .breadcrumb-row a {
      color: #9a9a9a;
      text-decoration: none;
    }

    .breadcrumb-row a:hover {
      color: #333;
    }

    .breadcrumb-row .sep {
      font-size: .6rem;
      margin: 0 3px;
    }

    .subcat-head {
      display: flex;
      align-items: center;
      gap: 14px;
    }

    .icon-badge {
      width: 52px;
      height: 52px;
      border-radius: 14px;
      font-size: 1.6rem;
      display: flex;
      align-items: center;
      justify-content: center;
      background:
        <?= $accent ?>
        18;
      border: 1.5px solid
        <?= $accent ?>
        33;
      flex-shrink: 0;
    }

    .subcat-head h1 {
      font-family: 'Syne', sans-serif;
      font-weight: 800;
      font-size: clamp(1.4rem, 2.5vw, 2rem);
      margin: 0;
      color: #1a1a1a;
    }

    .count-pill {
      font-family: 'DM Sans', sans-serif;
      font-size: .82rem;
      color: #9a9a9a;
      margin-top: 3px;
    }

    /* ── Toolbar ── */
    .toolbar-wrap {
      background: #fff;
      border-bottom: 1px solid #ebebeb;
      padding: 14px 0;
      margin-bottom: 32px;
    }

    .toolbar-inner {
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 10px;
    }

    .results-text {
      font-family: 'DM Sans', sans-serif;
      font-size: .88rem;
      color: #6b6b6b;
    }

    .sort-select {
      font-family: 'DM Sans', sans-serif;
      font-size: .85rem;
      padding: 7px 12px;
      border-radius: 9px;
      border: 1.5px solid #e2e2e2;
      background: #fff;
      color: #333;
      cursor: pointer;
    }

    .sort-select:focus {
      outline: none;
      border-color:
        <?= $accent ?>
      ;
    }

    /* ── PRODUCT CARD ── */
    .p-card {
      background: #fff;
      border-radius: 16px;
      border: 1.5px solid #ebebeb;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      height: 100%;
      transition: transform .22s, box-shadow .22s, border-color .22s;
      cursor: pointer;
      text-decoration: none;
      color: inherit;
    }

    .p-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 16px 40px rgba(0, 0, 0, .1);
      border-color:
        <?= $accent ?>
      ;
      color: inherit;
    }

    /* image area */
    .p-card .img-area {
  position: relative;
  width: 100%;
  aspect-ratio: 4/3;
  background: linear-gradient(135deg, #f0f0f0, #e8e8e8);
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
}

  .p-card .img-area img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center;
  transition: transform .3s ease;
}

    .p-card:hover .img-area img {
      transform: scale(1.06);
    }

    
    .p-card .img-area .no-img-icon {
      font-size: 3.5rem;
      opacity: .2;
    }

    .disc-badge {
      position: absolute;
      top: 12px;
      right: 12px;
      background:
        <?= $accent ?>
      ;
      color: #fff;
      font-family: 'DM Sans', sans-serif;
      font-size: .68rem;
      font-weight: 700;
      padding: 4px 10px;
      border-radius: 20px;
    }

    .feat-badge {
      position: absolute;
      top: 12px;
      left: 12px;
      background: #fff8e1;
      color: #b45309;
      font-family: 'DM Sans', sans-serif;
      font-size: .65rem;
      font-weight: 700;
      padding: 3px 9px;
      border-radius: 20px;
      border: 1px solid #fde68a;
    }

    /* card body */
    .p-card .card-body {
      padding: 16px 18px 0;
      flex: 1;
      display: flex;
      flex-direction: column;
    }

    .status-pill {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-family: 'DM Sans', sans-serif;
      font-size: .75rem;
      font-weight: 600;
      padding: 4px 11px;
      border-radius: 20px;
      margin-bottom: 10px;
      width: fit-content;
    }

    .status-pill.active {
      background: #e8f5e9;
      color: #1c9d63;
    }

    .status-pill.active::before {
      content: '●';
      font-size: .6rem;
    }

    .status-pill.out {
      background: #fdecea;
      color: #dc2626;
    }

    .status-pill.out::before {
      content: '●';
      font-size: .6rem;
    }

    .p-card .p-name {
      font-family: 'DM Sans', sans-serif;
      font-weight: 600;
      font-size: 1rem;
      color: #1a1a1a;
      line-height: 1.4;
      display: -webkit-box;
      -webkit-line-clamp: 3;
      -webkit-box-orient: vertical;
      overflow: hidden;
      min-height: 4.2em;
      margin-bottom: 6px;
    }

    .p-card .p-brand {
      font-family: 'DM Sans', sans-serif;
      font-size: .75rem;
      color: #9a9a9a;
      margin-bottom: 14px;
    }

    .stat-row {
      display: grid;
      grid-template-columns: 1fr 1fr 1fr;
      border-top: 1.5px solid #f0f0f0;
      margin-top: auto;
    }

    .stat-cell {
      padding: 12px 14px;
      border-right: 1.5px solid #f0f0f0;
    }

    .stat-cell:last-child {
      border-right: none;
    }

    .stat-label {
      font-family: 'DM Sans', sans-serif;
      font-size: .72rem;
      color: #9a9a9a;
      margin-bottom: 3px;
    }

    .stat-value {
      font-family: 'Syne', sans-serif;
      font-weight: 700;
      font-size: .95rem;
      color: #1a1a1a;
    }

    .stat-value.price {
      color:
        <?= $accent ?>
      ;
    }

    .stat-value .old {
      font-family: 'DM Sans', sans-serif;
      font-size: .72rem;
      color: #bbb;
      text-decoration: line-through;
      font-weight: 400;
      display: block;
      margin-top: 1px;
    }

    .stat-value .stars {
      color: #f59e0b;
      font-size: .8rem;
    }

    .btn-cart {
      width: calc(100% - 36px);
      margin: 12px 18px 16px;
      padding: 10px;
      border-radius: 10px;
      font-family: 'DM Sans', sans-serif;
      font-weight: 600;
      font-size: .88rem;
      border: none;
      background:
        <?= $accent ?>
        16;
      color:
        <?= $accent ?>
      ;
      transition: background .2s, color .2s;
    }

    .btn-cart:hover:not(:disabled) {
      background:
        <?= $accent ?>
      ;
      color: #fff;
    }

    .btn-cart:disabled {
      opacity: .4;
      cursor: not-allowed;
    }

    .empty-wrap {
      background: #fff;
      border-radius: 18px;
      padding: 80px 20px;
      text-align: center;
      border: 1.5px solid #ebebeb;
    }

    .empty-wrap .es-icon {
      font-size: 3.5rem;
      margin-bottom: 14px;
    }

    .empty-wrap h3 {
      font-family: 'Syne', sans-serif;
      font-weight: 800;
      color: #1a1a1a;
      margin-bottom: 8px;
    }

    .empty-wrap p {
      font-family: 'DM Sans', sans-serif;
      color: #777;
      margin-bottom: 24px;
    }

    .btn-back {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 12px 26px;
      border-radius: 50px;
      font-family: 'DM Sans', sans-serif;
      font-weight: 600;
      font-size: .9rem;
      background:
        <?= $accent ?>
      ;
      color: #fff;
      text-decoration: none;
      transition: opacity .2s;
    }

    .btn-back:hover {
      opacity: .85;
      color: #fff;
    }
  </style>
</head>

<body>

  <div class="announce-bar">
    <span>🎓 Student Exclusive: Extra 15% off with code <strong>STUDY15</strong> &nbsp;|&nbsp; Free shipping on orders
      above ₹499</span>
  </div>

  <nav class="navbar navbar-expand-lg sticky-top" id="mainNav">
    <div class="container">
      <a class="navbar-brand" href="index.php"><span
          class="brand-icon">📚</span><span>Study<strong>Mart</strong></span></a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navCollapse">
        <span class="hamburger-icon"><i class="fa fa-bars"></i></span>
      </button>
      <div class="collapse navbar-collapse" id="navCollapse">
        <ul class="navbar-nav mx-auto gap-1">
          <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle active" href="#" data-bs-toggle="dropdown">Categories</a>
            <ul class="dropdown-menu mega-menu">
              <?php foreach ($categories as $cat): ?>
                <li><a class="dropdown-item"
                    href="subcategory.php?category=<?= $cat['cat_id'] ?>"><?= getCategoryIcon($cat['cat_name']) ?>
                    &nbsp;<?= htmlspecialchars($cat['cat_name']) ?></a></li>
              <?php endforeach; ?>
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
          <a href="wishlist.php" class="nav-icon-btn"><i class="fa fa-heart"></i></a>
          <a href="cart.php" class="nav-icon-btn cart-btn">
            <i class="fa fa-shopping-cart"></i><span class="cart-badge" id="cartCount">0</span>
          </a>
          <a href="login.php" class="btn btn-sm btn-login">Login</a>
        </div>
      </div>
    </div>
  </nav>

  <!-- BANNER -->
  <section class="subcat-banner">
    <div class="container">
      <div class="breadcrumb-row">
        <a href="index.php">Home</a><span class="sep"><i class="fa fa-chevron-right"></i></span>
        <a href="shop.php">Categories</a><span class="sep"><i class="fa fa-chevron-right"></i></span>
        <a href="subcategory.php?category=<?= $categoryId ?>"><?= htmlspecialchars($categoryName) ?></a>
        <span class="sep"><i class="fa fa-chevron-right"></i></span>
        <span><?= htmlspecialchars($subcatName) ?></span>
      </div>
      <div class="subcat-head">
        <div class="icon-badge"><?= $subcatIcon ?></div>
        <div>
          <h1><?= htmlspecialchars($subcatName) ?></h1>
          <div class="count-pill"><?= count($products) ?> product<?= count($products) === 1 ? '' : 's' ?> available
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- TOOLBAR -->
  <div class="toolbar-wrap">
    <div class="container">
      <div class="toolbar-inner">
        <span class="results-text">
          <?= count($products) ?> result<?= count($products) === 1 ? '' : 's' ?> in
          <strong><?= htmlspecialchars($subcatName) ?></strong>
        </span>
        <form method="get" action="products.php" class="d-flex align-items-center gap-2">
          <input type="hidden" name="subcat" value="<?= $subcatId ?>" />
          <select name="sort" class="sort-select" onchange="this.form.submit()">
            <option value="latest" <?= $sort === 'latest' ? 'selected' : '' ?>>Newest First</option>
            <option value="price_low" <?= $sort === 'price_low' ? 'selected' : '' ?>>Price: Low → High</option>
            <option value="price_high" <?= $sort === 'price_high' ? 'selected' : '' ?>>Price: High → Low</option>
            <option value="rating" <?= $sort === 'rating' ? 'selected' : '' ?>>Top Rated</option>
            <option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>Name: A–Z</option>
          </select>
        </form>
      </div>
    </div>
  </div>

  <!-- PRODUCTS -->
  <section class="pb-5">
    <div class="container">

      <?php if (empty($products)): ?>
        <div class="empty-wrap">
          <div class="es-icon">📦</div>
          <h3>No products here yet</h3>
          <p>We're restocking <strong><?= htmlspecialchars($subcatName) ?></strong>. Check back soon!</p>
          <a href="subcategory.php?category=<?= $categoryId ?>" class="btn-back">
            <i class="fa fa-arrow-left"></i> Back to <?= htmlspecialchars($categoryName) ?>
          </a>
        </div>

      <?php else: ?>
        <div class="row g-4">
          <?php foreach ($products as $p):
            $imgFile = $p['product_img'] ?? '';
            $imgPath = !empty($imgFile) ? 'admin/assets/img/products/' . $imgFile : '';
            // ✅ FIX: Absolute path use karo file_exists ke liye
            $hasImg = !empty($imgFile) && file_exists($basePath . 'admin/assets/img/products/' . $imgFile);
            $inStock = ((int) ($p['quantity'] ?? 0)) > 0;
            $price = floatval($p['product_price']);
            $oldPrice = floatval($p['old_price'] ?? 0);
            $disc = ($oldPrice > $price && $oldPrice > 0) ? round((($oldPrice - $price) / $oldPrice) * 100) : 0;
            $rating = floatval($p['rating'] ?? 0);
            $reviews = intval($p['reviews_count'] ?? 0);
            $qty = intval($p['quantity'] ?? 0);
            $featured = intval($p['is_featured'] ?? 0);
            $unit = htmlspecialchars($p['product_unit'] ?? '');
            $weight = htmlspecialchars($p['product_weight'] ?? '');
            ?>
            <div class="col-12 col-sm-6 col-lg-4">
              <a href="product_detail.php?id=<?= (int) $p['product_id'] ?>" class="p-card">

                <!-- IMAGE AREA -->
                <div class="img-area">
                  <?php if ($disc > 0): ?>
                    <span class="disc-badge"><?= $disc ?>% OFF</span>
                  <?php endif; ?>
                  <?php if ($featured): ?>
                    <span class="feat-badge">★ Featured</span>
                  <?php endif; ?>
                  <?php if ($hasImg): ?>
                    <img src="<?= htmlspecialchars($imgPath) ?>" alt="<?= htmlspecialchars($p['product_name']) ?>"
                      loading="lazy" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                    <span class="no-img-icon" style="display:none;">🛍️</span>
                  <?php else: ?>
                    <span class="no-img-icon">🛍️</span>
                  <?php endif; ?>
                </div>

                <!-- CARD BODY -->
                <div class="card-body">
                  <span class="status-pill <?= $inStock ? 'active' : 'out' ?>">
                    <?= $inStock ? 'In Stock' : 'Out of Stock' ?>
                  </span>
                  <div class="p-name"><?= htmlspecialchars($p['product_name']) ?></div>
                  <div class="p-brand">
                    <?php if (!empty($p['brand_name'])): ?>       <?= htmlspecialchars($p['brand_name']) ?>     <?php endif; ?>
                    <?php if (!empty($weight)): ?> · <?= $weight ?><?php endif; ?>
                  </div>
                </div>

                <!-- STAT ROW -->
                <div class="stat-row">
                  <div class="stat-cell">
                    <div class="stat-label">Price</div>
                    <div class="stat-value price">
                      ₹<?= number_format($price, 2) ?>
                      <?php if ($oldPrice > 0): ?>
                        <span class="old">₹<?= number_format($oldPrice, 2) ?></span>
                      <?php endif; ?>
                    </div>
                  </div>
                  <div class="stat-cell">
                    <div class="stat-label">Stock<?= $unit ? ' (' . $unit . ')' : '' ?></div>
                    <div class="stat-value"><?= $qty > 0 ? $qty : '—' ?></div>
                  </div>
                  <div class="stat-cell">
                    <div class="stat-label">Rating</div>
                    <div class="stat-value">
                      <?php if ($rating > 0): ?>
                        <span class="stars">★</span> <?= number_format($rating, 1) ?>
                        <?php if ($reviews > 0): ?><span class="old"
                            style="text-decoration:none;">(<?= $reviews ?>)</span><?php endif; ?>
                      <?php else: ?>
                        <span style="color:#ccc;">—</span>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>

                <!-- ADD TO CART -->
                <button class="btn-cart"
                  onclick="event.preventDefault();addToCart(<?= (int) $p['product_id'] ?>,'<?= addslashes(htmlspecialchars($p['product_name'])) ?>')"
                  <?= !$inStock ? 'disabled' : '' ?>>
                  <?= $inStock ? '<i class="fa fa-cart-plus me-1"></i> Add to Cart' : 'Out of Stock' ?>
                </button>

              </a>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- FOOTER -->
  <footer class="site-footer pt-5 pb-3">
    <div class="container">
      <div class="row g-4 mb-4">
        <div class="col-lg-3 col-md-6">
          <div class="footer-brand"><span class="brand-icon">📚</span><span>Study<strong>Mart</strong></span></div>
          <p class="footer-desc mt-3">Your one-stop destination for all student essentials.</p>
          <div class="social-links mt-3">
            <a href="#"><i class="fab fa-instagram"></i></a><a href="#"><i class="fab fa-twitter"></i></a>
            <a href="#"><i class="fab fa-facebook"></i></a><a href="#"><i class="fab fa-youtube"></i></a>
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
          <span class="pay-text">UPI</span><span class="pay-text">Paytm</span>
        </div>
      </div>
    </div>
  </footer>

  <div class="cart-toast" id="cartToast"><i class="fa fa-check-circle"></i> Added to cart!</div>
  <button class="back-to-top" id="backTop" onclick="scrollToTop()"><i class="fa fa-arrow-up"></i></button>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
  <script src="app.js"></script>
  <script>
    function addToCart(id, name) {
      let cart = JSON.parse(localStorage.getItem('cart') || '[]');
      const idx = cart.findIndex(i => i.id === id);
      if (idx > -1) { cart[idx].qty++; } else { cart.push({ id, name, qty: 1 }); }
      localStorage.setItem('cart', JSON.stringify(cart));
      document.getElementById('cartCount').textContent = cart.reduce((s, i) => s + i.qty, 0);
      const t = document.getElementById('cartToast');
      t.classList.add('show');
      setTimeout(() => t.classList.remove('show'), 2500);
    }
    document.addEventListener('DOMContentLoaded', () => {
      const saved = JSON.parse(localStorage.getItem('cart') || '[]');
      document.getElementById('cartCount').textContent = saved.reduce((s, i) => s + i.qty, 0);
    });
    window.addEventListener('scroll', () => {
      document.getElementById('backTop').style.display = window.scrollY > 400 ? 'flex' : 'none';
    });
    function scrollToTop() { window.scrollTo({ top: 0, behavior: 'smooth' }); }
  </script>
</body>

</html>