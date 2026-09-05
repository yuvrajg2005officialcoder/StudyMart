<?php
session_start();
require_once 'config.php';

// Product ID URL se lo
$product_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if (!$product_id) {
  header("Location: index.php");
  exit;
}

// Product fetch with joins
$product = null;
try {
  $stmt = $pdo->prepare("
        SELECT p.*,
               IFNULL(c.cat_name,  '—') AS cat_name,
               IFNULL(s.sub_name,  '—') AS sub_name,
               IFNULL(b.brand_name,'—') AS brand_name
        FROM product p
        LEFT JOIN category    c ON p.cat_id     = c.cat_id
        LEFT JOIN subcategory s ON p.sub_cat_id = s.sub_id
        LEFT JOIN brands      b ON p.brand_id   = b.brand_id
        WHERE p.product_id = ? AND p.product_status = 1
    ");
  $stmt->execute([$product_id]);
  $product = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  $product = null;
}

if (!$product) {
  header("Location: index.php");
  exit;
}

// Related products (same subcategory)
$related = [];
try {
  $rel = $pdo->prepare("
        SELECT product_id, product_name, product_price, old_price, product_img, rating
        FROM product
        WHERE sub_cat_id = ? AND product_id != ? AND product_status = 1
        LIMIT 4
    ");
  $rel->execute([$product['sub_cat_id'], $product_id]);
  $related = $rel->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  $related = [];
}

// Discount %
$discount = 0;
if (!empty($product['old_price']) && $product['old_price'] > $product['product_price']) {
  $discount = round((($product['old_price'] - $product['product_price']) / $product['old_price']) * 100);
}

// Image
// ✅ Product images admin folder ke andar store hote hain
$img_dir = __DIR__ . '/admin/assets/img/products/';
$img_file = $product['product_img'] ?? '';
$img_src = 'admin/assets/img/products/' . $img_file;
$has_img = !empty($img_file) && file_exists($img_dir . $img_file);

// Navbar categories
$categories = [];
try {
  $r = $pdo->query("SELECT cat_id, cat_name FROM category WHERE cat_status = 0 ORDER BY cat_id ASC");
  $categories = $r->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  $categories = [];
}

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
    'printing' => '🖨️',
    'shoes' => '👟',
    'notes' => '📝'
  ];
  foreach ($icons as $key => $icon) {
    if (strpos($name, $key) !== false)
      return $icon;
  }
  return '🛍️';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($product['product_name']) ?> – StudyMart</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link
    href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,400&display=swap"
    rel="stylesheet" />
  <link rel="stylesheet" href="style.css" />
  <style>
    /* ── Product Detail Page ── */
    .pd-wrap {
      background: #fff;
      border-radius: 20px;
      box-shadow: 0 4px 32px rgba(0, 0, 0, 0.07);
      overflow: hidden;
      margin: 32px 0;
    }

    .pd-img-panel {
      background: #f7f8fc;
      padding: 28px 24px;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 16px;
      position: relative;
      min-height: 520px;
    }

    .pd-discount-badge {
      position: absolute;
      top: 18px;
      right: 18px;
      background: #e67e22;
      color: #fff;
      font-size: 13px;
      font-weight: 800;
      padding: 5px 14px;
      border-radius: 20px;
    }

    .pd-featured-badge {
      position: absolute;
      top: 18px;
      left: 18px;
      background: #fff;
      color: #555;
      font-size: 12px;
      font-weight: 700;
      padding: 5px 12px;
      border-radius: 20px;
      border: 1.5px solid #e8e8e8;
    }

    .pd-main-img {
  width: 100%;
  max-width: 100%;
  height: 620px;          /* 480px se badha diya */
  object-fit: contain;
  border-radius: 14px;
  transition: transform 0.35s ease;
}

    .pd-main-img:hover {
      transform: scale(1.05);
    }

    .pd-no-img {
  width: 100%;
  height: 620px;          /* same height match */
  background: #eef0f8;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 130px;
}

    .pd-info-panel {
      padding: 40px 36px;
      display: flex;
      flex-direction: column;
      gap: 18px;
    }

    .pd-stock-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: #e8f5e9;
      color: #2e7d32;
      padding: 5px 14px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 700;
    }

    .pd-stock-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: #43a047;
    }

    .pd-name {
      font-size: 1.85rem;
      font-weight: 800;
      color: #1a1f36;
      line-height: 1.25;
    }

    .pd-meta {
      font-size: 13px;
      color: #999;
    }

    .pd-meta span {
      color: #555;
      font-weight: 600;
    }

    .pd-stars {
      color: #f59e0b;
      font-size: 15px;
    }

    .pd-rating-num {
      font-weight: 800;
      font-size: 15px;
      color: #1a1f36;
    }

    .pd-rating-count {
      font-size: 12px;
      color: #bbb;
    }

    .pd-divider {
      height: 1px;
      background: #f0f2f8;
    }

    .pd-price-row {
      display: flex;
      align-items: baseline;
      gap: 14px;
      flex-wrap: wrap;
    }

    .pd-price {
      font-size: 2rem;
      font-weight: 800;
      color: #e67e22;
    }

    .pd-old-price {
      font-size: 1.1rem;
      color: #ccc;
      text-decoration: line-through;
    }

    .pd-save {
      background: #fff3e0;
      color: #e67e22;
      padding: 3px 10px;
      border-radius: 6px;
      font-size: 12px;
      font-weight: 700;
    }

    .pd-specs {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 10px;
    }

    .pd-spec-item {
      background: #f7f8fc;
      border-radius: 10px;
      padding: 12px 14px;
    }

    .pd-spec-label {
      font-size: 11px;
      color: #aaa;
      text-transform: uppercase;
      letter-spacing: 0.6px;
      font-weight: 700;
      margin-bottom: 3px;
    }

    .pd-spec-val {
      font-size: 14px;
      font-weight: 700;
      color: #1a1f36;
    }

    .pd-qty-row {
      display: flex;
      align-items: center;
      gap: 14px;
    }

    .pd-qty-ctrl {
      display: flex;
      align-items: center;
      border: 1.5px solid #e0e4ef;
      border-radius: 10px;
      overflow: hidden;
    }

    .pd-qty-btn {
      background: #f5f7ff;
      border: none;
      width: 38px;
      height: 40px;
      font-size: 1.2rem;
      color: #555;
      cursor: pointer;
      transition: background 0.2s;
    }

    .pd-qty-btn:hover {
      background: #ffe0b2;
      color: #e67e22;
    }

    .pd-qty-num {
      width: 46px;
      text-align: center;
      font-size: 1rem;
      font-weight: 800;
      color: #1a1f36;
      border: none;
      outline: none;
    }

    .pd-actions {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
    }

    .btn-pd-cart {
      flex: 1;
      min-width: 140px;
      background: #fff;
      border: 2px solid #e67e22;
      color: #e67e22;
      padding: 13px 18px;
      border-radius: 12px;
      font-weight: 700;
      font-size: 15px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      transition: all 0.2s;
    }

    .btn-pd-cart:hover {
      background: #fff3e0;
    }

    .btn-pd-buy {
      flex: 1;
      min-width: 140px;
      background: #e67e22;
      border: none;
      color: #fff;
      padding: 13px 18px;
      border-radius: 12px;
      font-weight: 700;
      font-size: 15px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      transition: background 0.2s;
    }

    .btn-pd-buy:hover {
      background: #d35400;
    }

    .btn-pd-wish {
      width: 50px;
      height: 50px;
      flex-shrink: 0;
      border: 2px solid #e0e4ef;
      background: #fff;
      border-radius: 12px;
      font-size: 1.25rem;
      color: #ccc;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.2s;
    }

    .btn-pd-wish:hover,
    .btn-pd-wish.active {
      color: #e74c3c;
      border-color: #e74c3c;
    }

    .pd-box {
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 2px 16px rgba(0, 0, 0, 0.05);
      padding: 30px 36px;
      margin-bottom: 24px;
    }

    .pd-section-title {
      font-size: 16px;
      font-weight: 800;
      color: #1a1f36;
      margin-bottom: 16px;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .pd-section-title::after {
      content: '';
      flex: 1;
      height: 2px;
      background: #f0f2f8;
    }

    .pd-related-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 16px;
    }

    .pd-rel-card {
      background: #fff;
      border-radius: 14px;
      box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
      text-decoration: none;
      color: inherit;
      overflow: hidden;
      transition: transform 0.2s, box-shadow 0.2s;
    }

    .pd-rel-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 28px rgba(0, 0, 0, 0.12);
    }

    .pd-rel-img {
      width: 100%;
      height: 150px;
      object-fit: cover;
      background: #f5f5f5;
      display: block;
    }

    .pd-rel-body {
      padding: 12px 14px;
    }

    .pd-rel-name {
      font-size: 13px;
      font-weight: 700;
      color: #1a1f36;
      margin-bottom: 5px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .pd-rel-price {
      font-size: 14px;
      font-weight: 800;
      color: #e67e22;
    }

    .pd-toast {
      position: fixed;
      bottom: 28px;
      right: 28px;
      background: #1a1f36;
      color: #fff;
      padding: 13px 22px;
      border-radius: 12px;
      font-size: 14px;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 10px;
      box-shadow: 0 8px 28px rgba(0, 0, 0, 0.2);
      transform: translateY(90px);
      opacity: 0;
      transition: all 0.32s cubic-bezier(.34, 1.56, .64, 1);
      z-index: 9999;
    }

    .pd-toast.show {
      transform: translateY(0);
      opacity: 1;
    }

    .pd-toast i {
      color: #e67e22;
      font-size: 18px;
    }

    .pd-breadcrumb {
      background: #fff;
      padding: 12px 0;
      border-bottom: 1px solid #f0f2f8;
      font-size: 13px;
      color: #aaa;
    }

    .pd-breadcrumb a {
      color: #888;
      text-decoration: none;
    }

    .pd-breadcrumb a:hover {
      color: #e67e22;
    }

    .pd-breadcrumb .crumb-current {
      color: #1a1f36;
      font-weight: 600;
    }

    @media (max-width: 768px) {
      .pd-info-panel {
        padding: 24px 20px;
      }

      .pd-img-panel {
        padding: 20px 16px;
        min-height: auto;
      }

      .pd-main-img,
      .pd-no-img {
        height: 320px;
      }

      .pd-name {
        font-size: 1.4rem;
      }

      .pd-related-grid {
        grid-template-columns: repeat(2, 1fr);
      }

      .pd-box {
        padding: 20px;
      }
    }
  </style>
</head>

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
          <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Categories</a>
            <ul class="dropdown-menu mega-menu">
              <?php foreach ($categories as $cat): ?>
                <li>
                  <a class="dropdown-item" href="subcategory.php?category=<?= $cat['cat_id'] ?>">
                    <?= getCategoryIcon($cat['cat_name']) ?> &nbsp;<?= htmlspecialchars($cat['cat_name']) ?>
                  </a>
                </li>
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

  <!-- BREADCRUMB -->
  <div class="pd-breadcrumb">
    <div class="container">
      <a href="index.php">Home</a> /
      <?php if ($product['cat_name'] !== '—'): ?>
        <a href="subcategory.php?category=<?= $product['cat_id'] ?>"><?= htmlspecialchars($product['cat_name']) ?></a> /
      <?php endif; ?>
      <?php if ($product['sub_name'] !== '—'): ?>
        <a href="products.php?subcat=<?= $product['sub_cat_id'] ?>"><?= htmlspecialchars($product['sub_name']) ?></a> /
      <?php endif; ?>
      <span class="crumb-current"><?= htmlspecialchars($product['product_name']) ?></span>
    </div>
  </div>

  <!-- MAIN CONTENT -->
  <div class="container">

    <!-- Product Detail Card -->
    <div class="pd-wrap">
      <div class="row g-0">

        <!-- LEFT: Image -->
        <div class="col-lg-5 pd-img-panel">
          <?php if ($discount > 0): ?>
            <div class="pd-discount-badge"><?= $discount ?>% OFF</div>
          <?php endif; ?>
          <?php if ($product['is_featured'] == 1): ?>
            <div class="pd-featured-badge">⭐ Featured</div>
          <?php endif; ?>

          <?php if ($has_img): ?>
            <img class="pd-main-img" id="pdMainImg" src="<?= htmlspecialchars($img_src) ?>"
              alt="<?= htmlspecialchars($product['product_name']) ?>"
              onerror="this.style.display='none';document.getElementById('pdNoImg').style.display='flex';" />
            <div class="pd-no-img" id="pdNoImg" style="display:none;">📦</div>
          <?php else: ?>
            <div class="pd-no-img">📦</div>
          <?php endif; ?>
        </div>

        <!-- RIGHT: Info -->
        <div class="col-lg-7 pd-info-panel">

          <!-- Stock Status -->
          <?php if ($product['quantity'] > 0): ?>
            <div><span class="pd-stock-badge"><span class="pd-stock-dot"></span> In Stock</span></div>
          <?php else: ?>
            <div><span class="pd-stock-badge" style="background:#fdecea;color:#c62828;">
                <span class="pd-stock-dot" style="background:#c62828;"></span> Out of Stock
              </span></div>
          <?php endif; ?>

          <!-- Product Name -->
          <h1 class="pd-name"><?= htmlspecialchars($product['product_name']) ?></h1>

          <!-- Brand & Weight -->
          <p class="pd-meta">
            <?php if ($product['brand_name'] !== '—'): ?>
              Brand: <span><?= htmlspecialchars($product['brand_name']) ?></span>
            <?php endif; ?>
            <?php if (!empty($product['product_weight'])): ?>
              &nbsp;·&nbsp; Weight: <span><?= htmlspecialchars($product['product_weight']) ?></span>
            <?php endif; ?>
          </p>

          <!-- Rating -->
          <?php if (!empty($product['rating']) && $product['rating'] > 0): ?>
            <div class="d-flex align-items-center gap-2">
              <span class="pd-stars">
                <?php
                $rVal = round($product['rating']);
                for ($i = 1; $i <= 5; $i++)
                  echo $i <= $rVal ? '★' : '☆';
                ?>
              </span>
              <span class="pd-rating-num"><?= number_format($product['rating'], 1) ?></span>
              <span class="pd-rating-count">(<?= intval($product['reviews_count']) ?> reviews)</span>
            </div>
          <?php endif; ?>

          <div class="pd-divider"></div>

          <!-- Price -->
          <div class="pd-price-row">
            <span class="pd-price">₹<?= number_format($product['product_price'], 2) ?></span>
            <?php if (!empty($product['old_price']) && $product['old_price'] > $product['product_price']): ?>
              <span class="pd-old-price">₹<?= number_format($product['old_price'], 2) ?></span>
              <span class="pd-save">Save ₹<?= number_format($product['old_price'] - $product['product_price'], 0) ?></span>
            <?php endif; ?>
          </div>

          <!-- Specs -->
          <div class="pd-specs">
            <div class="pd-spec-item">
              <div class="pd-spec-label">Category</div>
              <div class="pd-spec-val"><?= htmlspecialchars($product['cat_name']) ?></div>
            </div>
            <div class="pd-spec-item">
              <div class="pd-spec-label">Sub Category</div>
              <div class="pd-spec-val"><?= htmlspecialchars($product['sub_name']) ?></div>
            </div>
            <div class="pd-spec-item">
              <div class="pd-spec-label">Stock</div>
              <div class="pd-spec-val">
                <?= intval($product['quantity']) ?>
                <?php if (!empty($product['product_unit'])): ?>
                  <small style="color:#aaa;font-size:11px;"> <?= htmlspecialchars($product['product_unit']) ?></small>
                <?php endif; ?>
              </div>
            </div>
            <div class="pd-spec-item">
              <div class="pd-spec-label">Product ID</div>
              <div class="pd-spec-val">#<?= str_pad($product['product_id'], 4, '0', STR_PAD_LEFT) ?></div>
            </div>
          </div>

          <!-- Quantity -->
          <div class="pd-qty-row">
            <span style="font-size:13px;font-weight:700;color:#555;">Quantity:</span>
            <div class="pd-qty-ctrl">
              <button class="pd-qty-btn" onclick="changeQty(-1)">−</button>
              <input class="pd-qty-num" type="number" id="pdQty" value="1" min="1"
                max="<?= intval($product['quantity']) ?>" />
              <button class="pd-qty-btn" onclick="changeQty(1)">+</button>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="pd-actions">
            <button class="btn-pd-cart" onclick="addToCart()">
              <i class="fa fa-shopping-cart"></i> Add to Cart
            </button>
            <button class="btn-pd-buy" onclick="buyNow()">
              <i class="fa fa-bolt"></i> Buy Now
            </button>
            <button class="btn-pd-wish" id="wishBtn" onclick="toggleWishlist(this)" title="Wishlist">
              <i class="fa-regular fa-heart"></i>
            </button>
          </div>

        </div><!-- /pd-info-panel -->
      </div>
    </div><!-- /pd-wrap -->

    <!-- Related Products -->
    <?php if (!empty($related)): ?>
      <div class="pd-box">
        <div class="pd-section-title">Related Products</div>
        <div class="pd-related-grid">
          <?php foreach ($related as $rp):
            $r_img = 'admin/assets/img/products/' . $rp['product_img'];
            $r_has = !empty($rp['product_img']) && file_exists(__DIR__ . '/admin/assets/img/products/' . $rp['product_img']);
            ?>
            <a href="product_detail.php?id=<?= $rp['product_id'] ?>" class="pd-rel-card">
              <?php if ($r_has): ?>
                <img class="pd-rel-img" src="<?= htmlspecialchars($r_img) ?>"
                  alt="<?= htmlspecialchars($rp['product_name']) ?>" onerror="this.style.background='#f0f2f8';this.src='';">
              <?php else: ?>
                <div class="pd-rel-img d-flex align-items-center justify-content-center"
                  style="font-size:48px;background:#f7f8fc;">📦</div>
              <?php endif; ?>
              <div class="pd-rel-body">
                <div class="pd-rel-name"><?= htmlspecialchars($rp['product_name']) ?></div>
                <div class="pd-rel-price">₹<?= number_format($rp['product_price'], 2) ?></div>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

  </div><!-- /container -->

  <!-- FOOTER -->
  <footer class="site-footer pt-5 pb-3">
    <div class="container">
      <div class="row g-4 mb-4">
        <div class="col-lg-3 col-md-6">
          <div class="footer-brand"><span class="brand-icon">📚</span><span>Study<strong>Mart</strong></span></div>
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

  <!-- Toast Notification -->
  <div class="pd-toast" id="pdToast">
    <i class="fa fa-check-circle"></i>
    <span id="pdToastMsg">Done!</span>
  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
  <script src="app.js"></script>
  <script>
    // Product data for cart (matches the format app.js / cart.php expects)
    var PD_PRODUCT = {
      id: <?= (int) $product['product_id'] ?>,
      name: <?= json_encode($product['product_name']) ?>,
      cat: <?= json_encode($product['sub_name'] !== '—' ? $product['sub_name'] : $product['cat_name']) ?>,
      price: <?= (float) $product['product_price'] ?>,
      oldPrice: <?= !empty($product['old_price']) ? (float) $product['old_price'] : 0 ?>,
      rating: <?= (float) ($product['rating'] ?? 0) ?>,
      reviews: <?= (int) ($product['reviews_count'] ?? 0) ?>,
      img: <?= json_encode($has_img ? $img_src : '') ?>,
      inStock: <?= ($product['quantity'] > 0) ? 'true' : 'false' ?>
    };

    // Quantity control
    function changeQty(d) {
      var inp = document.getElementById('pdQty');
      var v = parseInt(inp.value) + d;
      var max = parseInt(inp.max) || 99;
      if (v < 1) v = 1;
      if (v > max) v = max;
      inp.value = v;
    }

    // Toast
    function showToast(msg) {
      var t = document.getElementById('pdToast');
      document.getElementById('pdToastMsg').textContent = msg;
      t.classList.add('show');
      setTimeout(function () { t.classList.remove('show'); }, 3000);
    }

    // Add to Cart - uses the same localStorage key/shape as app.js (sm_cart)
    function addToCart() {
      var qty = parseInt(document.getElementById('pdQty').value) || 1;
      var cartData = JSON.parse(localStorage.getItem('sm_cart') || '[]');
      var existing = cartData.find(function (i) { return i.id === PD_PRODUCT.id; });
      if (existing) {
        existing.qty += qty;
      } else {
        var item = Object.assign({}, PD_PRODUCT, { qty: qty });
        cartData.push(item);
      }
      localStorage.setItem('sm_cart', JSON.stringify(cartData));
      var count = cartData.reduce(function (s, i) { return s + i.qty; }, 0);
      document.querySelectorAll('#cartCount').forEach(function (el) { el.textContent = count; });
      showToast('Cart mein add ho gaya!');
    }

    // Buy Now
    function buyNow() {
      addToCart();
      window.location.href = 'checkout.php?product_id=' + PD_PRODUCT.id + '&qty=' + document.getElementById('pdQty').value;
    }

    // Wishlist toggle
    function toggleWishlist(btn) {
      btn.classList.toggle('active');
      var icon = btn.querySelector('i');
      if (btn.classList.contains('active')) {
        icon.className = 'fa-solid fa-heart';
        showToast('Wishlist mein add ho gaya!');
      } else {
        icon.className = 'fa-regular fa-heart';
        showToast('Wishlist se remove ho gaya.');
      }
    }

    // Cart count on page load (sm_cart key)
    window.addEventListener('load', function () {
      var cartData = JSON.parse(localStorage.getItem('sm_cart') || '[]');
      var count = cartData.reduce(function (s, i) { return s + i.qty; }, 0);
      document.querySelectorAll('#cartCount').forEach(function (el) { el.textContent = count; });
    });
  </script>
</body>

</html>