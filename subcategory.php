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
    'lab supply' => '🔬',
    'printing' => '🖨️',
    'shoes' => '👟',
    'notes' => '📝',
    'mobile' => '📱',
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

// Sidebar categories
$categories = [];
try {
  $stmt = $pdo->query("SELECT * FROM category WHERE cat_status = 0 ORDER BY cat_id ASC");
  $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  $categories = [];
}

// Current category
$categoryId = isset($_GET['category']) ? (int) $_GET['category'] : 0;
$currentCategory = null;
if ($categoryId > 0) {
  try {
    $stmt = $pdo->prepare("SELECT * FROM category WHERE cat_id = ?");
    $stmt->execute([$categoryId]);
    $currentCategory = $stmt->fetch(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    $currentCategory = null;
  }
}

// Subcategories for this category
$subcategories = [];
if ($categoryId > 0) {
  try {
    $stmt = $pdo->prepare("SELECT * FROM subcategory WHERE cat_id = ? ORDER BY sub_id ASC");
    $stmt->execute([$categoryId]);
    $subcategories = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    $subcategories = [];
  }
}

$categoryName = $currentCategory['cat_name'] ?? 'Category';
$categoryIcon = getCategoryIcon($categoryName);
$categoryColor = getCategoryColor($categoryId > 0 ? $categoryId : 0);

// ✅ FIX: Base path for file_exists check
$basePath = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/' . trim(dirname($_SERVER['SCRIPT_NAME']), '/');
$basePath = rtrim($basePath, '/') . '/';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($categoryName) ?> – StudyMart</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link
    href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,400&display=swap"
    rel="stylesheet" />
  <link rel="stylesheet" href="style.css" />
  <style>
    .subcat-banner {
      padding: 56px 0 40px;
      background: linear-gradient(135deg,
          <?= $categoryColor ?>
          22, transparent 70%);
    }

    .subcat-banner .breadcrumb-row {
      font-family: 'DM Sans', sans-serif;
      font-size: 0.9rem;
      color: #6b6b6b;
      margin-bottom: 18px;
    }

    .subcat-banner .breadcrumb-row a {
      color: #6b6b6b;
      text-decoration: none;
    }

    .subcat-banner .breadcrumb-row a:hover {
      color: #222;
    }

    .subcat-banner .breadcrumb-row i {
      font-size: 0.7rem;
      margin: 0 8px;
      opacity: .6;
    }

    .subcat-head {
      display: flex;
      align-items: center;
      gap: 18px;
    }

    .subcat-head .icon-badge {
      width: 64px;
      height: 64px;
      border-radius: 18px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 2rem;
      background:
        <?= $categoryColor ?>
        33;
      border: 1px solid
        <?= $categoryColor ?>
        55;
    }

    .subcat-head h1 {
      font-family: 'Syne', sans-serif;
      font-weight: 800;
      font-size: clamp(1.6rem, 3vw, 2.4rem);
      margin: 0;
      color: #1c1c1c;
    }

    .subcat-head .count-pill {
      font-family: 'DM Sans', sans-serif;
      font-size: 0.85rem;
      color: #6b6b6b;
      margin-top: 4px;
    }

    /* Subcategory Cards */
    .subcat-card {
      display: block;
      text-decoration: none;
      color: inherit;
      background: #fff;
      border: 1px solid #ececec;
      border-radius: 20px;
      overflow: hidden;
      height: 100%;
      transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
    }

    .subcat-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 16px 32px rgba(0, 0, 0, 0.10);
      border-color:
        <?= $categoryColor ?>
      ;
    }

    .subcat-card .thumb-wrap {
      width: 100%;
      aspect-ratio: 1/1;
      background:
        <?= $categoryColor ?>
        15;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
    }

    .subcat-card .thumb-wrap img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .subcat-card .thumb-wrap .no-img {
      font-size: 3rem;
      opacity: .4;
    }

    .subcat-card .info {
      padding: 16px;
      text-align: center;
    }

    .subcat-card .info .s-name {
      font-family: 'Syne', sans-serif;
      font-weight: 700;
      font-size: 1rem;
      color: #1c1c1c;
    }

    .subcat-card .info .s-arrow {
      margin-top: 8px;
      font-size: 0.8rem;
      color:
        <?= $categoryColor ?>
      ;
      font-family: 'DM Sans', sans-serif;
    }

    .empty-state {
      text-align: center;
      padding: 70px 20px;
    }

    .empty-state .emoji {
      font-size: 3rem;
      margin-bottom: 14px;
    }

    .empty-state h3 {
      font-family: 'Syne', sans-serif;
      font-weight: 700;
      margin-bottom: 8px;
    }

    .empty-state p {
      font-family: 'DM Sans', sans-serif;
      color: #777;
      margin-bottom: 22px;
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
        <span class="brand-icon">📚</span><span>Study<strong>Mart</strong></span>
      </a>
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

  <!-- BANNER -->
  <section class="subcat-banner">
    <div class="container">
      <div class="breadcrumb-row">
        <a href="index.php">Home</a>
        <i class="fa fa-chevron-right"></i>
        <a href="shop.php">Categories</a>
        <i class="fa fa-chevron-right"></i>
        <span><?= htmlspecialchars($categoryName) ?></span>
      </div>
      <div class="subcat-head">
        <div class="icon-badge"><?= $categoryIcon ?></div>
        <div>
          <h1><?= htmlspecialchars($categoryName) ?></h1>
          <div class="count-pill"><?= count($subcategories) ?>
            subcategor<?= count($subcategories) === 1 ? 'y' : 'ies' ?> available</div>
        </div>
      </div>
    </div>
  </section>

  <!-- SUBCATEGORIES GRID -->
  <section class="py-5">
    <div class="container">

      <?php if ($categoryId <= 0): ?>
        <div class="empty-state">
          <div class="emoji">🤔</div>
          <h3>No category selected</h3>
          <p>Pick a category from the menu.</p>
          <a href="shop.php" class="btn btn-hero-primary">Browse All Categories</a>
        </div>

      <?php elseif (!$currentCategory): ?>
        <div class="empty-state">
          <div class="emoji">🔍</div>
          <h3>Category not found</h3>
          <p>This category may have been removed.</p>
          <a href="shop.php" class="btn btn-hero-primary">Browse All Categories</a>
        </div>

      <?php elseif (empty($subcategories)): ?>
        <div class="empty-state">
          <div class="emoji">📦</div>
          <h3>No subcategories yet</h3>
          <p>Coming soon for <?= htmlspecialchars($categoryName) ?>!</p>
          <a href="shop.php" class="btn btn-hero-primary">Explore Other Categories</a>
        </div>

      <?php else: ?>
        <div class="row g-4">
          <?php foreach ($subcategories as $sub):
            $imgFile = $sub['sub_img'] ?? '';
            $imgPath = !empty($imgFile) ? 'admin/assets/img/subcategory/' . $imgFile : '';
            // ✅ FIX: Absolute path use karo file_exists ke liye
            $hasImg = !empty($imgFile) && file_exists($basePath . 'admin/assets/img/subcategory/' . $imgFile);
            ?>
            <div class="col-6 col-md-4 col-lg-3">
              <a href="products.php?subcat=<?= (int) $sub['sub_id'] ?>" class="subcat-card">
                <div class="thumb-wrap">
                  <?php if ($hasImg): ?>
                    <img src="<?= htmlspecialchars($imgPath) ?>" alt="<?= htmlspecialchars($sub['sub_name']) ?>"
                      onerror="this.style.display='none';this.nextElementSibling.style.display='block';">
                    <span class="no-img" style="display:none;"><?= getCategoryIcon($sub['sub_name']) ?></span>
                  <?php else: ?>
                    <span class="no-img"><?= getCategoryIcon($sub['sub_name']) ?></span>
                  <?php endif; ?>
                </div>
                <div class="info">
                  <div class="s-name"><?= htmlspecialchars($sub['sub_name']) ?></div>
                  <div class="s-arrow">Shop Now →</div>
                </div>
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