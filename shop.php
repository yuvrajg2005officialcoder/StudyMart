    <?php
    require_once 'config.php';

    $shop_products = [];
    try {
        $stmt = $pdo->query("
            SELECT p.product_id, p.product_name, p.product_price, p.old_price,
                  p.rating, p.reviews_count, p.product_img, p.quantity,
                  c.cat_name
            FROM product p
            LEFT JOIN category c ON p.cat_id = c.cat_id
            WHERE p.product_status = 1
            ORDER BY p.product_id DESC
        ");
        $shop_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $shop_products = [];
    }

    $categories = [];
    try {
        $stmt = $pdo->query("SELECT * FROM category WHERE cat_status = 0 ORDER BY cat_id ASC");
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $categories = [];
    }

    $max_price = 5000;
    if (!empty($shop_products)) {
        $max_price = max(array_column($shop_products, 'product_price'));
        $max_price = ceil($max_price / 1000) * 1000;
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
      <meta charset="UTF-8" />
      <meta name="viewport" content="width=device-width, initial-scale=1.0" />
      <title>Shop – StudyMart</title>
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" />
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
      <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,400&display=swap" rel="stylesheet" />
      <link rel="stylesheet" href="style.css" />
    </head>
    <body>

    <div class="announce-bar">
      <span>🎓 Student Exclusive: Extra 15% off with code <strong>STUDY15</strong> &nbsp;|&nbsp; Free shipping on orders above ₹499</span>
    </div>

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
            <li class="nav-item"><a class="nav-link active" href="shop.php">Shop</a></li>
            <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
            <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
          </ul>
          <div class="nav-actions d-flex align-items-center gap-3">
            <div class="nav-search-wrap">
              <input type="text" class="nav-search" placeholder="Search products…" id="navSearch" oninput="filterShop(this.value)" />
              <i class="fa fa-search search-icon"></i>
            </div>
            <a href="wishlist.php" class="nav-icon-btn"><i class="fa fa-heart"></i></a>
            <a href="cart.php" class="nav-icon-btn cart-btn">
              <i class="fa fa-shopping-cart"></i>
              <span class="cart-badge" id="cartCount">0</span>
            </a>
            <a href="login.php" class="btn btn-sm btn-login">Login</a>
          </div>
        </div>
      </div>
    </nav>

    <div class="page-header">
      <div class="container">
        <h1>Our Products</h1>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active">Shop</li>
          </ol>
        </nav>
      </div>
    </div>

    <section class="shop-section py-5">
      <div class="container">
        <div class="row g-4">

          <!-- SIDEBAR FILTERS -->
          <div class="col-lg-3">
            <div class="filter-sidebar">
              <div class="filter-header">
                <h5>Filters</h5>
                <button onclick="clearFilters()" class="clear-btn">Clear All</button>
              </div>

              <!-- CATEGORY FILTER - FIXED -->
              <div class="filter-group">
                <h6>Category</h6>
                <div class="filter-options" id="catFilter">

                  <!-- "All Products" checkbox - value="all", checked by default -->
                  <label>
                    <input type="checkbox"
                          value="all"
                          checked
                          onchange="toggleAllFilter(this)" />
                    All Products
                  </label>

                  <!-- DB se categories - har ek ka value = lowercase cat_name -->
                  <?php foreach ($categories as $cat): ?>
                    <label>
                      <input type="checkbox"
                            value="<?= strtolower(trim(htmlspecialchars($cat['cat_name']))) ?>"
                            onchange="applyFilters()" />
                      <?= htmlspecialchars($cat['cat_name']) ?>
                    </label>
                  <?php endforeach; ?>

                </div>
              </div>

              <!-- PRICE RANGE -->
              <div class="filter-group">
                <h6>Price Range</h6>
                <input type="range" class="price-range" min="0"
                      max="<?= $max_price ?>"
                      value="<?= $max_price ?>"
                      id="priceRange" oninput="updatePrice(this.value)" />
                <div class="price-labels">
                  <span>₹0</span>
                  <span id="priceVal">₹<?= number_format($max_price) ?></span>
                </div>
              </div>

              <!-- RATING -->
              <div class="filter-group">
                <h6>Rating</h6>
                <div class="filter-options">
                  <label><input type="radio" name="rating" value="0" onchange="applyFilters()" checked /> All Ratings</label>
                  <label><input type="radio" name="rating" value="4" onchange="applyFilters()" /> 4★ & above</label>
                  <label><input type="radio" name="rating" value="3" onchange="applyFilters()" /> 3★ & above</label>
                </div>
              </div>

              <!-- AVAILABILITY -->
              <div class="filter-group">
                <h6>Availability</h6>
                <div class="filter-options">
                  <label><input type="checkbox" id="inStockOnly" onchange="applyFilters()" /> In Stock Only</label>
                  <label><input type="checkbox" id="onSaleOnly" onchange="applyFilters()" /> On Sale</label>
                </div>
              </div>
            </div>
          </div>

          <!-- PRODUCT AREA -->
          <div class="col-lg-9">
            <div class="shop-toolbar mb-4">
              <span class="results-count" id="resultsCount">Showing all products</span>
              <div class="shop-toolbar-right d-flex gap-2 align-items-center">
                <select class="form-select sort-select" id="sortBy" onchange="applyFilters()">
                  <option value="default">Sort: Default</option>
                  <option value="price-asc">Price: Low to High</option>
                  <option value="price-desc">Price: High to Low</option>
                  <option value="rating">Top Rated</option>
                </select>
                <div class="view-toggle">
                  <button class="view-btn active" id="gridView" onclick="setView('grid')"><i class="fa fa-th"></i></button>
                  <button class="view-btn" id="listView" onclick="setView('list')"><i class="fa fa-list"></i></button>
                </div>
              </div>
            </div>
            <div class="row g-4" id="shopGrid"></div>
            <div class="text-center mt-5">
              <button class="btn btn-load-more" onclick="loadMore()">Load More Products <i class="fa fa-refresh ms-2"></i></button>
            </div>
          </div>

        </div>
      </div>
    </section>

    <footer class="site-footer pt-5 pb-3">
      <div class="container">
        <div class="footer-bottom">
          <span>© 2026 StudyMart. All rights reserved.</span>
        </div>
      </div>
    </footer>

    <div class="cart-toast" id="cartToast">
      <i class="fa fa-check-circle"></i> Added to cart!
    </div>
    <button class="back-to-top" id="backTop" onclick="scrollToTop()"><i class="fa fa-arrow-up"></i></button>

    <script>
    const dbProducts = <?php echo json_encode(array_map(function($p) {
        $imgFile = trim($p['product_img'] ?? '');
        return [
            'id'       => (int)$p['product_id'],
            'name'     => $p['product_name'],
            'cat'      => strtolower(trim($p['cat_name'] ?? 'general')),
            'price'    => (float)$p['product_price'],
            'oldPrice' => !empty($p['old_price']) ? (float)$p['old_price'] : null,
            'rating'   => (float)($p['rating'] ?? 0),
            'reviews'  => (int)($p['reviews_count'] ?? 0),
            'img'      => !empty($imgFile)
                            ? 'admin/assets/img/products/' . $imgFile
                            : 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=400&q=80',
            'badge'    => !empty($p['old_price']) ? 'sale' : '',
            'inStock'  => ((int)($p['quantity'] ?? 0)) > 0,
        ];
    }, $shop_products)); ?>;

    const maxPrice = <?= $max_price ?>;
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
    <script src="app.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        // Products initialize karo
        allProducts = (typeof dbProducts !== "undefined") ? dbProducts : [];
        shopItems   = [...allProducts];

        // Price range set karo
        const pr = document.getElementById("priceRange");
        if (pr) { pr.max = maxPrice; pr.value = maxPrice; }
        const pv = document.getElementById("priceVal");
        if (pv) pv.textContent = "₹" + maxPrice.toLocaleString();

        // Sab products render karo
        visibleCount = 12;
        renderShopGrid(allProducts);
    });
    </script>

    </body>
    </html>