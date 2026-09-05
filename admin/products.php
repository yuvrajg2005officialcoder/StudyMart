<?php
session_start();
include 'include/conn.php';

// ── AJAX: Subcategories ──────────────────────────────────────
if (isset($_GET['get_subcategories'])) {
    $cat_id = intval($_GET['cat_id']);
    $res    = $mysqli->query("SELECT sub_id, sub_name FROM subcategory WHERE cat_id = $cat_id ORDER BY sub_name ASC");
    $subs   = [];
    while ($r = $res->fetch_assoc()) $subs[] = $r;
    echo json_encode($subs);
    exit;
}

$error = '';

// ── DELETE ───────────────────────────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id   = intval($_POST['product_id']);
    $stmt = $mysqli->prepare("SELECT product_img FROM product WHERE product_id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($row && !empty($row['product_img'])) {
        $p = __DIR__ . '/assets/img/products/' . $row['product_img'];
        if (file_exists($p)) unlink($p);
    }
    $del = $mysqli->prepare("DELETE FROM product WHERE product_id=?");
    $del->bind_param("i", $id);
    $del->execute();
    $del->close();
    header("Location: products.php?msg=deleted");
    exit;
}

// ── ADD ──────────────────────────────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'add') {
    $product_name    = trim($_POST['product_name']);
    $category_id     = intval($_POST['category_id']);
    $sub_category_id = intval($_POST['sub_category_id']);
    $brand_id        = intval($_POST['brand_id']);
    $price           = floatval($_POST['price']);
    $old_price       = !empty($_POST['old_price']) ? floatval($_POST['old_price']) : null;
    $quantity        = intval($_POST['quantity']);
    $unit            = trim($_POST['unit']);
    $weight          = trim($_POST['weight']);
    $rating          = !empty($_POST['rating']) ? floatval($_POST['rating']) : 0;
    $reviews_count   = intval($_POST['reviews_count']);
    $is_featured     = intval($_POST['is_featured']);
    $product_status  = intval($_POST['product_status']);
    $product_img     = '';

    if (empty($product_name)) { $error = 'Product name is required.'; }
    if ($rating < 0 || $rating > 5) { $error = 'Rating must be between 0 and 5.'; }

    if (empty($error) && !empty($_FILES['product_img']['name']) && $_FILES['product_img']['error'] == 0) {
        $allowed = ['jpg','jpeg','png','webp','gif'];
        $ext     = strtolower(pathinfo($_FILES['product_img']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            $error = 'Only JPG, PNG, WEBP, GIF allowed.';
        } else {
            $dir = __DIR__ . '/assets/img/products/';
            if (!is_dir($dir)) mkdir($dir, 0777, true);
            $new = time() . '_' . preg_replace('/\s+/', '_', basename($_FILES['product_img']['name']));
            if (move_uploaded_file($_FILES['product_img']['tmp_name'], $dir . $new)) {
                $product_img = $new;
            } else {
                $error = 'Image upload failed.';
            }
        }
    }

    if (empty($error)) {
        $stmt = $mysqli->prepare("INSERT INTO product (product_name, cat_id, sub_cat_id, brand_id, product_price, old_price, quantity, product_unit, product_weight, rating, reviews_count, is_featured, product_img, product_status, created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())");
        /*
         * FIX: type-string corrected to match the column order exactly.
         * Columns:  product_name(s) cat_id(i) sub_cat_id(i) brand_id(i)
         *           product_price(d) old_price(d) quantity(i) product_unit(s)
         *           product_weight(s) rating(d) reviews_count(i) is_featured(i)
         *           product_img(s) product_status(i)
         * Old string "siiiddissdiiis" had the last three letters out of
         * order (i-i-s instead of i-s-i), which silently swapped
         * is_featured / product_img / product_status values on save —
         * this is what corrupted product data (e.g. wrong category/status
         * ending up on products like the Washing Machine).
         */
        $stmt->bind_param("siiiddissdiisi", $product_name, $category_id, $sub_category_id, $brand_id, $price, $old_price, $quantity, $unit, $weight, $rating, $reviews_count, $is_featured, $product_img, $product_status);
        if ($stmt->execute()) {
            header("Location: products.php?msg=added");
            exit;
        } else {
            $error = 'Database error: ' . $mysqli->error;
        }
        $stmt->close();
    }
}

// ── EDIT ─────────────────────────────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id              = intval($_POST['product_id']);
    $product_name    = trim($_POST['product_name']);
    $category_id     = intval($_POST['category_id']);
    $sub_category_id = intval($_POST['sub_category_id']);
    $brand_id        = intval($_POST['brand_id']);
    $price           = floatval($_POST['price']);
    $old_price       = !empty($_POST['old_price']) ? floatval($_POST['old_price']) : null;
    $quantity        = intval($_POST['quantity']);
    $unit            = trim($_POST['unit']);
    $weight          = trim($_POST['weight']);
    $rating          = !empty($_POST['rating']) ? floatval($_POST['rating']) : 0;
    $reviews_count   = intval($_POST['reviews_count']);
    $is_featured     = intval($_POST['is_featured']);
    $product_status  = intval($_POST['product_status']);
    $old_img         = trim($_POST['old_img']);
    $product_img     = $old_img;

    if (empty($product_name)) { $error = 'Product name is required.'; }
    if ($rating < 0 || $rating > 5) { $error = 'Rating must be between 0 and 5.'; }

    if (empty($error) && !empty($_FILES['product_img']['name']) && $_FILES['product_img']['error'] == 0) {
        $allowed = ['jpg','jpeg','png','webp','gif'];
        $ext     = strtolower(pathinfo($_FILES['product_img']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            $error = 'Only JPG, PNG, WEBP, GIF allowed.';
        } else {
            $dir = __DIR__ . '/assets/img/products/';
            if (!is_dir($dir)) mkdir($dir, 0777, true);
            $new = time() . '_' . preg_replace('/\s+/', '_', basename($_FILES['product_img']['name']));
            if (move_uploaded_file($_FILES['product_img']['tmp_name'], $dir . $new)) {
                if (!empty($old_img) && file_exists($dir . $old_img)) unlink($dir . $old_img);
                $product_img = $new;
            } else {
                $error = 'Image upload failed.';
            }
        }
    }

    if (empty($error)) {
        $stmt = $mysqli->prepare("UPDATE product SET product_name=?, cat_id=?, sub_cat_id=?, brand_id=?, product_price=?, old_price=?, quantity=?, product_unit=?, product_weight=?, rating=?, reviews_count=?, is_featured=?, product_img=?, product_status=? WHERE product_id=?");
        /*
         * FIX: same type-string correction as INSERT above, plus the
         * trailing "i" for the WHERE product_id=? parameter.
         * Old string "siiiddissdiiisi" had the same is_featured /
         * product_img / product_status order bug as INSERT.
         */
        $stmt->bind_param("siiiddissdiisii", $product_name, $category_id, $sub_category_id, $brand_id, $price, $old_price, $quantity, $unit, $weight, $rating, $reviews_count, $is_featured, $product_img, $product_status, $id);
        if ($stmt->execute()) {
            header("Location: products.php?msg=updated");
            exit;
        } else {
            $error = 'Database error: ' . $mysqli->error;
        }
        $stmt->close();
    }
}

// ── FETCH ALL ────────────────────────────────────────────────
$result = $mysqli->query("
    SELECT p.*,
           IFNULL(c.cat_name,'—')   AS cat_name,
           IFNULL(s.sub_name,'—')   AS sub_name,
           IFNULL(b.brand_name,'—') AS brand_name
    FROM product p
    LEFT JOIN category    c ON p.cat_id     = c.cat_id
    LEFT JOIN subcategory s ON p.sub_cat_id = s.sub_id
    LEFT JOIN brands      b ON p.brand_id   = b.brand_id
    ORDER BY p.created_at DESC
");

// ── Dropdowns ────────────────────────────────────────────────
$cats_arr   = [];
$brands_arr = [];
$r = $mysqli->query("SELECT cat_id, cat_name FROM category ORDER BY cat_name ASC");
while ($row = $r->fetch_assoc()) $cats_arr[] = $row;

$r = $mysqli->query("SELECT brand_id, brand_name FROM brands ORDER BY brand_name ASC");
while ($row = $r->fetch_assoc()) $brands_arr[] = $row;

include 'include/header.php';
?>

<style>
.modal-overlay {
    display: none;
    position: fixed; top:0; left:0; width:100%; height:100%;
    background: rgba(15,19,34,0.6);
    z-index: 1040;
    backdrop-filter: blur(3px);
}
.modal-box-wrap {
    display: none;
    position: fixed; top:0; left:0; width:100%; height:100%;
    z-index: 1050;
    align-items: center; justify-content: center;
    overflow-y: auto; padding: 20px 12px;
}
.modal-box-wrap.open {
    display: flex;
    animation: modalIn 0.28s cubic-bezier(0.34,1.56,0.64,1) both;
}
@keyframes modalIn {
    from { opacity:0; transform:scale(0.88) translateY(20px); }
    to   { opacity:1; transform:scale(1) translateY(0); }
}
.upload-zone {
    border: 2px dashed #dde1ef; border-radius:12px; padding:16px;
    text-align:center; cursor:pointer;
    transition: border-color 0.2s, background 0.2s;
}
.upload-zone:hover  { border-color:#4361ee; background:rgba(67,97,238,0.03); }
.upload-zone.has-img { border-color:#4361ee; border-style:solid; }
.thumb-img {
    width:52px; height:52px; object-fit:contain;
    border-radius:10px; border:2px solid #eef0f8; padding:3px;
    transition: transform 0.2s, box-shadow 0.2s;
}
.thumb-img:hover { transform:scale(1.1); box-shadow:0 4px 14px rgba(0,0,0,0.15); cursor:zoom-in; }
.thumb-ph {
    width:52px; height:52px; background:#f4f5f7;
    border-radius:10px; display:flex; align-items:center;
    justify-content:center; border:2px solid #eef0f8;
}
.fg2 { display:grid; grid-template-columns:1fr 1fr; gap:14px 18px; }
.fg3 { display:grid; grid-template-columns:1fr 1fr 1fr; gap:14px 18px; }
.p-form .form-control {
    border-radius:9px !important; border:1.5px solid #e2e8f0 !important;
    padding:9px 13px !important; font-size:13.5px !important;
    transition:border-color 0.2s,box-shadow 0.2s !important;
    color:#1a1f36 !important; height:auto !important;
}
.p-form .form-control:focus {
    border-color:#4361ee !important;
    box-shadow:0 0 0 3px rgba(67,97,238,0.12) !important;
    outline:none !important;
}
.p-form .fl { font-weight:600; font-size:13px; color:#3a4158; margin-bottom:5px; display:block; }
.badge-count {
    background:linear-gradient(135deg,#4361ee,#6c8fff);
    color:#fff; font-size:13px; padding:5px 14px;
    border-radius:20px; font-weight:700;
    box-shadow:0 4px 12px rgba(67,97,238,0.25);
}
.btn-act {
    border-radius:7px !important; font-weight:600 !important;
    font-size:12.5px !important; padding:5px 11px !important;
    transition:all 0.2s !important;
}
.btn-act:hover { transform:translateY(-1px); box-shadow:0 4px 10px rgba(0,0,0,0.12); }
.star-display { color:#f59e0b; font-size:13px; }
</style>

<div class="main-panel">
<div class="content">
<div class="container-fluid">

  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4 mt-2">
    <div>
      <h4 class="page-title mb-0">Products</h4>
      <p class="text-muted mb-0" style="font-size:13px;margin-top:3px;">Manage all your products in one place</p>
    </div>
    <button class="btn btn-primary btn-sm" style="gap:6px;display:flex;align-items:center;" onclick="openModal('addModal')">
      <i class="la la-plus" style="font-size:16px;"></i> Add New Product
    </button>
  </div>

  <!-- Flash -->
  <?php if (isset($_GET['msg'])):
    $msgs = [
      'added'   => ['success','<i class="la la-check-circle mr-1"></i> Product added successfully!'],
      'updated' => ['success','<i class="la la-check-circle mr-1"></i> Product updated successfully!'],
      'deleted' => ['danger', '<i class="la la-trash mr-1"></i> Product deleted successfully!'],
    ];
    $m = $msgs[$_GET['msg']] ?? null;
    if ($m): ?>
    <div class="alert alert-<?= $m[0] ?>"
         style="border-radius:10px;border:none;font-weight:600;font-size:13.5px;box-shadow:0 4px 14px rgba(0,0,0,0.08);">
      <?= $m[1] ?>
      <button onclick="this.closest('.alert').remove()"
              style="background:none;border:none;float:right;font-size:20px;cursor:pointer;opacity:0.6;line-height:1;">&times;</button>
    </div>
  <?php endif; endif; ?>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"
         style="border-radius:10px;border:none;font-weight:600;font-size:13.5px;box-shadow:0 4px 14px rgba(0,0,0,0.08);">
      <i class="la la-exclamation-circle mr-1"></i> <?= htmlspecialchars($error) ?>
    </div>
  <?php endif; ?>

  <!-- Table Card -->
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="card-title mb-0" style="display:flex;align-items:center;gap:8px;">
        <i class="la la-shopping-cart" style="color:#4361ee;font-size:20px;"></i> All Products
      </h5>
      <div class="d-flex align-items-center" style="gap:12px;">
        <input type="text" id="srch" placeholder="Search products..."
               style="border-radius:20px;border:1px solid #e8ecf4;background:#f4f6fb;
                      padding:6px 16px;font-size:13px;width:200px;outline:none;"
               onkeyup="doSearch(this.value)">
        <span class="badge-count"><?= $result ? $result->num_rows : 0 ?> Products</span>
      </div>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0" id="pTbl">
          <thead>
            <tr>
              <th style="padding-left:22px;width:60px;">#</th>
              <th style="width:72px;">Image</th>
              <th>Product Name</th>
              <th>Category</th>
              <th>Brand</th>
              <th>Price</th>
              <th>Qty</th>
              <th>Rating</th>
              <th>Featured</th>
              <th>Status</th>
              <th>Created</th>
              <th style="text-align:right;padding-right:22px;width:165px;">Actions</th>
            </tr>
          </thead>
          <tbody>
<?php if ($result && $result->num_rows > 0):
  while ($row = $result->fetch_assoc()):
    $img_dir  = __DIR__ . '/assets/img/products/';
    $img_file = $row['product_img'] ?? '';
    $img_src  = 'assets/img/products/' . $img_file;
    $has_img  = !empty($img_file) && file_exists($img_dir . $img_file);
    $active   = ($row['product_status'] == 1);
    $featured = ($row['is_featured'] == 1);

    $js_name      = addslashes(htmlspecialchars($row['product_name'], ENT_QUOTES));
    $js_cat       = addslashes(htmlspecialchars($row['cat_name'], ENT_QUOTES));
    $js_sub       = addslashes(htmlspecialchars($row['sub_name'], ENT_QUOTES));
    $js_brand     = addslashes(htmlspecialchars($row['brand_name'], ENT_QUOTES));
    $js_unit      = addslashes(htmlspecialchars($row['product_unit'] ?? '', ENT_QUOTES));
    $js_weight    = addslashes(htmlspecialchars($row['product_weight'] ?? '', ENT_QUOTES));
    $js_img       = $has_img ? addslashes(htmlspecialchars($img_src, ENT_QUOTES)) : '';
    $js_oldimg    = addslashes(htmlspecialchars($img_file, ENT_QUOTES));
    $js_date      = date('d M Y, h:i A', strtotime($row['created_at']));
    $old_price_val = !empty($row['old_price']) ? floatval($row['old_price']) : 0;
    $rating_val    = floatval($row['rating'] ?? 0);
    $reviews_val   = intval($row['reviews_count'] ?? 0);
?>
            <tr>
              <td style="padding-left:22px;">
                <span style="color:#8d9db5;font-weight:700;font-size:13px;">#<?= $row['product_id'] ?></span>
              </td>
              <td>
                <?php if ($has_img): ?>
                  <img src="<?= htmlspecialchars($img_src) ?>" class="thumb-img"
                       onerror="this.style.display='none';this.nextElementSibling.style.display='flex';" alt="">
                  <div class="thumb-ph" style="display:none;"><i class="la la-image" style="color:#bbb;font-size:20px;"></i></div>
                <?php else: ?>
                  <div class="thumb-ph"><i class="la la-image" style="color:#bbb;font-size:20px;"></i></div>
                <?php endif; ?>
              </td>
              <td>
                <span style="font-weight:700;color:#1a1f36;font-size:14px;"><?= htmlspecialchars($row['product_name']) ?></span>
                <?php if (!empty($row['sub_name']) && $row['sub_name'] !== '—'): ?>
                  <br><small style="color:#8d9db5;font-size:11px;"><?= htmlspecialchars($row['sub_name']) ?></small>
                <?php endif; ?>
              </td>
              <td>
                <span style="background:#eff6ff;color:#1d4ed8;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:600;">
                  <?= htmlspecialchars($row['cat_name']) ?>
                </span>
              </td>
              <td>
                <span style="background:#fdf4ff;color:#7e22ce;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:600;">
                  <?= htmlspecialchars($row['brand_name']) ?>
                </span>
              </td>
              <td>
                <span style="font-weight:700;color:#4361ee;font-size:13px;">₹<?= number_format($row['product_price'],2) ?></span>
                <?php if ($old_price_val > 0): ?>
                  <br><small style="text-decoration:line-through;color:#aaa;font-size:11px;">₹<?= number_format($old_price_val,2) ?></small>
                  <?php $disc = round((($old_price_val - $row['product_price']) / $old_price_val) * 100); ?>
                  <span style="background:#e8f5e9;color:#2e7d32;font-size:10px;font-weight:700;padding:1px 5px;border-radius:4px;margin-left:2px;"><?= $disc ?>% off</span>
                <?php endif; ?>
              </td>
              <td style="font-weight:600;color:#1a1f36;font-size:13px;">
                <?= intval($row['quantity']) ?>
                <?php if (!empty($row['product_unit'])): ?><small style="color:#8d9db5;"><?= htmlspecialchars($row['product_unit']) ?></small><?php endif; ?>
              </td>
              <td>
                <?php if ($rating_val > 0): ?>
                  <span class="star-display">★</span>
                  <span style="font-weight:700;font-size:13px;color:#1a1f36;"><?= number_format($rating_val,1) ?></span>
                  <br><small style="color:#aaa;font-size:11px;">(<?= $reviews_val ?>)</small>
                <?php else: ?>
                  <span style="color:#ccc;font-size:12px;">—</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($featured): ?>
                  <span style="background:#fff8e1;color:#f59e0b;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:700;">★ Yes</span>
                <?php else: ?>
                  <span style="color:#ccc;font-size:12px;">No</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($active): ?>
                  <span style="background:#e8f5e9;color:#2e7d32;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:700;">● Active</span>
                <?php else: ?>
                  <span style="background:#fdecea;color:#c62828;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:700;">● Inactive</span>
                <?php endif; ?>
              </td>
              <td style="font-size:13px;color:#8d9db5;">
                <i class="la la-calendar" style="font-size:14px;"></i> <?= date('d M Y', strtotime($row['created_at'])) ?>
              </td>
              <td style="text-align:right;padding-right:22px;">
                <button class="btn btn-sm btn-act" style="border:1.5px solid #0288d1;color:#0288d1;margin-right:3px;"
                  onclick="openView('<?= $row['product_id'] ?>','<?= $js_name ?>','<?= $js_img ?>','<?= $js_cat ?>','<?= $js_sub ?>','<?= $js_brand ?>','<?= $row['product_price'] ?>','<?= $old_price_val ?>','<?= intval($row['quantity']) ?>','<?= $js_unit ?>','<?= $js_weight ?>','<?= $row['product_status'] ?>','<?= $js_date ?>','<?= $rating_val ?>','<?= $reviews_val ?>','<?= intval($row['is_featured']) ?>')" title="View">
                  <i class="la la-eye"></i>
                </button>
                <button class="btn btn-sm btn-act" style="border:1.5px solid #4361ee;color:#4361ee;margin-right:3px;"
                  onclick="openEdit('<?= $row['product_id'] ?>','<?= $js_name ?>','<?= $row['cat_id'] ?>','<?= $row['sub_cat_id'] ?>','<?= $row['brand_id'] ?>','<?= $row['product_price'] ?>','<?= $old_price_val ?>','<?= intval($row['quantity']) ?>','<?= $js_unit ?>','<?= $js_weight ?>','<?= $row['product_status'] ?>','<?= $js_img ?>','<?= $js_oldimg ?>','<?= $rating_val ?>','<?= $reviews_val ?>','<?= intval($row['is_featured']) ?>')" title="Edit">
                  <i class="la la-edit"></i> Edit
                </button>
                <button class="btn btn-sm btn-act" style="border:1.5px solid #e53935;color:#e53935;"
                  onclick="openDelete('<?= $row['product_id'] ?>','<?= $js_name ?>')" title="Delete">
                  <i class="la la-trash"></i>
                </button>
              </td>
            </tr>
<?php endwhile; else: ?>
            <tr>
              <td colspan="12" style="text-align:center;padding:60px 20px;color:#aaa;">
                <i class="la la-inbox" style="font-size:48px;display:block;margin-bottom:10px;color:#d0d5e8;"></i>
                <p style="font-size:15px;color:#b0b8cc;margin:0 0 10px;">No products found.</p>
                <a href="javascript:void(0)" onclick="openModal('addModal')" style="color:#4361ee;font-weight:600;font-size:13px;">+ Add your first product</a>
              </td>
            </tr>
<?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>
</div>

<!-- BACKDROP -->
<div id="modalBackdrop" class="modal-overlay" onclick="closeAllModals()"></div>

<!-- ══ ADD MODAL ════════════════════════════════════════════ -->
<div id="addModal" class="modal-box-wrap">
  <div class="p-form" style="background:#fff;border-radius:16px;width:100%;max-width:680px;margin:auto;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,0.18);">
    <div style="background:linear-gradient(135deg,#4361ee,#6c8fff);padding:20px 24px;display:flex;align-items:center;justify-content:space-between;">
      <div style="display:flex;align-items:center;gap:10px;">
        <div style="width:36px;height:36px;background:rgba(255,255,255,0.2);border-radius:9px;display:flex;align-items:center;justify-content:center;">
          <i class="la la-plus-circle" style="color:#fff;font-size:20px;"></i>
        </div>
        <div>
          <h5 style="color:#fff;font-weight:800;margin:0;font-size:16px;">Add New Product</h5>
          <p style="color:rgba(255,255,255,0.7);margin:0;font-size:12px;">Fill in the product details</p>
        </div>
      </div>
      <button onclick="closeAllModals()" style="background:rgba(255,255,255,0.15);border:none;width:32px;height:32px;border-radius:8px;cursor:pointer;color:#fff;font-size:18px;display:flex;align-items:center;justify-content:center;">&times;</button>
    </div>
    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="action" value="add">
      <div style="padding:22px 24px;max-height:72vh;overflow-y:auto;">

        <!-- Product Name -->
        <div style="margin-bottom:14px;">
          <label class="fl">Product Name <span style="color:#e53935;">*</span></label>
          <input type="text" name="product_name" class="form-control" placeholder="e.g. Basmati Rice 5kg, iPhone 15..." required>
        </div>

        <!-- Category / Subcategory / Brand / Status -->
        <div class="fg2" style="margin-bottom:14px;">
          <div>
            <label class="fl">Category <span style="color:#e53935;">*</span></label>
            <select name="category_id" id="add_cat" class="form-control" required onchange="loadSubs(this.value,'add_sub')">
              <option value="">-- Select Category --</option>
              <?php foreach ($cats_arr as $c): ?>
                <option value="<?= $c['cat_id'] ?>"><?= htmlspecialchars($c['cat_name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="fl">Sub Category</label>
            <select name="sub_category_id" id="add_sub" class="form-control">
              <option value="">-- Select Category First --</option>
            </select>
          </div>
          <div>
            <label class="fl">Brand</label>
            <select name="brand_id" class="form-control">
              <option value="0">-- Select Brand --</option>
              <?php foreach ($brands_arr as $b): ?>
                <option value="<?= $b['brand_id'] ?>"><?= htmlspecialchars($b['brand_name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="fl">Status</label>
            <select name="product_status" class="form-control">
              <option value="1">Active</option>
              <option value="0">Inactive</option>
            </select>
          </div>
        </div>

        <!-- Price / Old Price / Quantity / Unit / Weight -->
        <div style="background:#f8f9ff;border-radius:12px;padding:14px 16px;margin-bottom:14px;">
          <p style="font-size:12px;font-weight:700;color:#4361ee;margin:0 0 12px;text-transform:uppercase;letter-spacing:0.8px;">Pricing & Stock</p>
          <div class="fg2">
            <div>
              <label class="fl">Sale Price (₹) <span style="color:#e53935;">*</span></label>
              <input type="number" name="price" class="form-control" placeholder="e.g. 499.00" step="0.01" min="0" required>
            </div>
            <div>
              <label class="fl">MRP / Old Price (₹) <span style="font-weight:400;font-size:11px;color:#aaa;">for discount %</span></label>
              <input type="number" name="old_price" class="form-control" placeholder="e.g. 699.00" step="0.01" min="0">
            </div>
            <div>
              <label class="fl">Quantity <span style="color:#e53935;">*</span></label>
              <input type="number" name="quantity" class="form-control" placeholder="e.g. 100" min="0" required>
            </div>
            <div>
              <label class="fl">Unit</label>
              <select name="unit" class="form-control">
                <?php foreach (['piece','kg','gram','liter','ml','meter','box','pack','pair','set'] as $u): ?>
                  <option value="<?= $u ?>"><?= ucfirst($u) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div style="margin-top:12px;">
            <label class="fl">Weight</label>
            <input type="text" name="weight" class="form-control" placeholder="e.g. 500g, 1.2kg">
          </div>
        </div>

        <!-- Rating / Reviews / Featured -->
        <div style="background:#fffbf0;border-radius:12px;padding:14px 16px;margin-bottom:14px;border:1px solid #fde68a;">
          <p style="font-size:12px;font-weight:700;color:#d97706;margin:0 0 12px;text-transform:uppercase;letter-spacing:0.8px;">★ Rating & Visibility</p>
          <div class="fg3">
            <div>
              <label class="fl">Rating <span style="font-weight:400;font-size:11px;color:#aaa;">(0–5)</span></label>
              <input type="number" name="rating" class="form-control" placeholder="e.g. 4.5" step="0.1" min="0" max="5" value="0">
            </div>
            <div>
              <label class="fl">Reviews Count</label>
              <input type="number" name="reviews_count" class="form-control" placeholder="e.g. 128" min="0" value="0">
            </div>
            <div>
              <label class="fl">Featured on Homepage</label>
              <select name="is_featured" class="form-control">
                <option value="0">No</option>
                <option value="1">Yes ★</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Image Upload -->
        <div>
          <label class="fl">Product Image</label>
          <div class="upload-zone" id="add_zone" onclick="document.getElementById('add_img_inp').click()">
            <div id="add_prev_box" style="display:none;margin-bottom:8px;">
              <img id="add_prev" src="#" style="max-height:120px;max-width:100%;border-radius:10px;box-shadow:0 4px 14px rgba(0,0,0,0.1);">
            </div>
            <div id="add_ph">
              <div style="width:46px;height:46px;background:#f0f4ff;border-radius:12px;display:flex;align-items:center;justify-content:center;margin:0 auto 8px;">
                <i class="la la-cloud-upload" style="font-size:24px;color:#4361ee;"></i>
              </div>
              <p style="color:#4361ee;font-weight:700;margin:0 0 3px;font-size:13px;">Click to upload image</p>
              <p style="color:#aaa;font-size:11px;margin:0;">JPG, PNG, WEBP, GIF</p>
            </div>
            <input type="file" name="product_img" id="add_img_inp" accept="image/*" style="display:none;"
                   onchange="prevImg(this,'add_prev','add_prev_box','add_ph','add_zone')">
          </div>
        </div>

      </div>
      <div style="border-top:1px solid #f0f2f8;padding:16px 24px;display:flex;justify-content:flex-end;gap:10px;background:#fafbff;">
        <button type="button" class="btn btn-default" onclick="closeAllModals()"
                style="border:1.5px solid #e0e4ef;color:#5a6080;border-radius:9px;padding:8px 20px;font-weight:600;">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="la la-save mr-1"></i> Save Product</button>
      </div>
    </form>
  </div>
</div>

<!-- ══ VIEW MODAL ═══════════════════════════════════════════ -->
<div id="viewModal" class="modal-box-wrap">
  <div style="background:#fff;border-radius:16px;width:100%;max-width:520px;margin:auto;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,0.18);">
    <div style="background:linear-gradient(135deg,#1b5e20,#2e7d32);padding:20px 24px;display:flex;align-items:center;justify-content:space-between;">
      <div style="display:flex;align-items:center;gap:10px;">
        <div style="width:36px;height:36px;background:rgba(255,255,255,0.2);border-radius:9px;display:flex;align-items:center;justify-content:center;">
          <i class="la la-eye" style="color:#fff;font-size:20px;"></i>
        </div>
        <h5 style="color:#fff;font-weight:800;margin:0;font-size:16px;">Product Details</h5>
      </div>
      <button onclick="closeAllModals()" style="background:rgba(255,255,255,0.15);border:none;width:32px;height:32px;border-radius:8px;cursor:pointer;color:#fff;font-size:18px;display:flex;align-items:center;justify-content:center;">&times;</button>
    </div>
    <div style="padding:24px;">
      <div style="display:flex;gap:18px;align-items:flex-start;">
        <div style="flex-shrink:0;">
          <img id="v_img" src="" alt=""
               style="width:100px;height:100px;object-fit:contain;border-radius:14px;border:2px solid #e8f5e9;padding:5px;box-shadow:0 4px 14px rgba(0,0,0,0.08);"
               onerror="this.style.display='none';document.getElementById('v_noimg').style.display='flex';">
          <div id="v_noimg" style="display:none;width:100px;height:100px;background:#f4f5f7;border-radius:14px;align-items:center;justify-content:center;border:2px solid #eef0f8;">
            <i class="la la-image" style="font-size:32px;color:#bbb;"></i>
          </div>
        </div>
        <div style="flex:1;">
          <h4 id="v_name" style="font-weight:800;color:#1a1f36;margin:0 0 6px;font-size:18px;"></h4>
          <p id="v_id" style="color:#8d9db5;font-size:12px;margin:0 0 10px;"></p>
          <div id="v_badges" style="display:flex;flex-wrap:wrap;gap:6px;"></div>
        </div>
      </div>
      <div id="v_stats" style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:10px;margin-top:18px;"></div>
      <p id="v_date" style="margin-top:12px;font-size:12px;color:#8d9db5;display:flex;align-items:center;gap:5px;"></p>
    </div>
    <div style="border-top:1px solid #f0f2f8;padding:14px 24px;display:flex;justify-content:center;background:#fafbff;">
      <button class="btn btn-default" onclick="closeAllModals()" style="border:1.5px solid #e0e4ef;color:#5a6080;border-radius:9px;padding:8px 28px;font-weight:600;">Close</button>
    </div>
  </div>
</div>

<!-- ══ EDIT MODAL ═══════════════════════════════════════════ -->
<div id="editModal" class="modal-box-wrap">
  <div class="p-form" style="background:#fff;border-radius:16px;width:100%;max-width:680px;margin:auto;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,0.18);">
    <div style="background:linear-gradient(135deg,#e65100,#ff8c00);padding:20px 24px;display:flex;align-items:center;justify-content:space-between;">
      <div style="display:flex;align-items:center;gap:10px;">
        <div style="width:36px;height:36px;background:rgba(255,255,255,0.2);border-radius:9px;display:flex;align-items:center;justify-content:center;">
          <i class="la la-edit" style="color:#fff;font-size:20px;"></i>
        </div>
        <div>
          <h5 style="color:#fff;font-weight:800;margin:0;font-size:16px;">Edit Product</h5>
          <p style="color:rgba(255,255,255,0.75);margin:0;font-size:12px;">Update product information</p>
        </div>
      </div>
      <button onclick="closeAllModals()" style="background:rgba(255,255,255,0.15);border:none;width:32px;height:32px;border-radius:8px;cursor:pointer;color:#fff;font-size:18px;display:flex;align-items:center;justify-content:center;">&times;</button>
    </div>
    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="action"     value="edit">
      <input type="hidden" name="product_id" id="e_pid">
      <input type="hidden" name="old_img"    id="e_oldimg">
      <div style="padding:22px 24px;max-height:72vh;overflow-y:auto;">

        <!-- Product Name -->
        <div style="margin-bottom:14px;">
          <label class="fl">Product Name <span style="color:#e53935;">*</span></label>
          <input type="text" name="product_name" id="e_name" class="form-control" required>
        </div>

        <!-- Category / Subcategory / Brand / Status -->
        <div class="fg2" style="margin-bottom:14px;">
          <div>
            <label class="fl">Category <span style="color:#e53935;">*</span></label>
            <select name="category_id" id="e_cat" class="form-control" required onchange="loadSubs(this.value,'e_sub')">
              <option value="">-- Select Category --</option>
              <?php foreach ($cats_arr as $c): ?>
                <option value="<?= $c['cat_id'] ?>"><?= htmlspecialchars($c['cat_name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="fl">Sub Category</label>
            <select name="sub_category_id" id="e_sub" class="form-control">
              <option value="">-- Loading... --</option>
            </select>
          </div>
          <div>
            <label class="fl">Brand</label>
            <select name="brand_id" id="e_brand" class="form-control">
              <option value="0">-- Select Brand --</option>
              <?php foreach ($brands_arr as $b): ?>
                <option value="<?= $b['brand_id'] ?>"><?= htmlspecialchars($b['brand_name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="fl">Status</label>
            <select name="product_status" id="e_status" class="form-control">
              <option value="1">Active</option>
              <option value="0">Inactive</option>
            </select>
          </div>
        </div>

        <!-- Pricing & Stock -->
        <div style="background:#f8f9ff;border-radius:12px;padding:14px 16px;margin-bottom:14px;">
          <p style="font-size:12px;font-weight:700;color:#4361ee;margin:0 0 12px;text-transform:uppercase;letter-spacing:0.8px;">Pricing & Stock</p>
          <div class="fg2">
            <div>
              <label class="fl">Sale Price (₹) <span style="color:#e53935;">*</span></label>
              <input type="number" name="price" id="e_price" class="form-control" step="0.01" min="0" required>
            </div>
            <div>
              <label class="fl">MRP / Old Price (₹) <span style="font-weight:400;font-size:11px;color:#aaa;">for discount %</span></label>
              <input type="number" name="old_price" id="e_old_price" class="form-control" placeholder="e.g. 699.00" step="0.01" min="0">
            </div>
            <div>
              <label class="fl">Quantity <span style="color:#e53935;">*</span></label>
              <input type="number" name="quantity" id="e_qty" class="form-control" min="0" required>
            </div>
            <div>
              <label class="fl">Unit</label>
              <select name="unit" id="e_unit" class="form-control">
                <?php foreach (['piece','kg','gram','liter','ml','meter','box','pack','pair','set'] as $u): ?>
                  <option value="<?= $u ?>"><?= ucfirst($u) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div style="margin-top:12px;">
            <label class="fl">Weight</label>
            <input type="text" name="weight" id="e_weight" class="form-control" placeholder="e.g. 500g">
          </div>
        </div>

        <!-- Rating / Reviews / Featured -->
        <div style="background:#fffbf0;border-radius:12px;padding:14px 16px;margin-bottom:14px;border:1px solid #fde68a;">
          <p style="font-size:12px;font-weight:700;color:#d97706;margin:0 0 12px;text-transform:uppercase;letter-spacing:0.8px;">★ Rating & Visibility</p>
          <div class="fg3">
            <div>
              <label class="fl">Rating <span style="font-weight:400;font-size:11px;color:#aaa;">(0–5)</span></label>
              <input type="number" name="rating" id="e_rating" class="form-control" step="0.1" min="0" max="5">
            </div>
            <div>
              <label class="fl">Reviews Count</label>
              <input type="number" name="reviews_count" id="e_reviews" class="form-control" min="0">
            </div>
            <div>
              <label class="fl">Featured on Homepage</label>
              <select name="is_featured" id="e_featured" class="form-control">
                <option value="0">No</option>
                <option value="1">Yes ★</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Current Image -->
        <div style="margin-bottom:10px;">
          <label class="fl">Current Image</label>
          <div style="display:flex;align-items:center;gap:14px;">
            <img id="e_cur_img" src="" alt=""
                 style="height:78px;width:78px;object-fit:contain;border-radius:12px;border:2px solid #eef0f8;padding:4px;box-shadow:0 2px 8px rgba(0,0,0,0.07);"
                 onerror="this.style.display='none';document.getElementById('e_no_img').style.display='flex';">
            <div id="e_no_img" style="display:none;width:78px;height:78px;background:#f4f5f7;border-radius:12px;align-items:center;justify-content:center;border:2px solid #eef0f8;">
              <i class="la la-image" style="color:#bbb;font-size:24px;"></i>
            </div>
            <p style="font-size:12px;color:#aaa;margin:0;">Upload below to replace.</p>
          </div>
        </div>

        <!-- New Image -->
        <div>
          <label class="fl">New Image <span style="font-weight:400;font-size:12px;color:#aaa;">(optional)</span></label>
          <div class="upload-zone" id="e_zone" onclick="document.getElementById('e_img_inp').click()">
            <div id="e_prev_box" style="display:none;margin-bottom:8px;">
              <img id="e_prev" src="" style="max-height:110px;border-radius:10px;box-shadow:0 4px 14px rgba(0,0,0,0.1);">
            </div>
            <div id="e_ph">
              <i class="la la-cloud-upload" style="font-size:24px;color:#4361ee;"></i>
              <p style="color:#4361ee;font-weight:600;font-size:13px;margin:4px 0 0;">Click to select new image</p>
            </div>
            <input type="file" name="product_img" id="e_img_inp" accept="image/*" style="display:none;"
                   onchange="prevImg(this,'e_prev','e_prev_box','e_ph','e_zone')">
          </div>
        </div>

      </div>
      <div style="border-top:1px solid #f0f2f8;padding:16px 24px;display:flex;justify-content:flex-end;gap:10px;background:#fafbff;">
        <button type="button" class="btn btn-default" onclick="closeAllModals()"
                style="border:1.5px solid #e0e4ef;color:#5a6080;border-radius:9px;padding:8px 20px;font-weight:600;">Cancel</button>
        <button type="submit" class="btn btn-warning" style="color:#fff;border-radius:9px;font-weight:700;padding:8px 20px;box-shadow:0 4px 12px rgba(230,81,0,0.3);">
          <i class="la la-save mr-1"></i> Update Product
        </button>
      </div>
    </form>
  </div>
</div>

<!-- ══ DELETE MODAL ══════════════════════════════════════════ -->
<div id="delModal" class="modal-box-wrap">
  <div style="background:#fff;border-radius:16px;width:100%;max-width:420px;margin:auto;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,0.18);">
    <div style="background:linear-gradient(135deg,#b71c1c,#e53935);padding:20px 24px;display:flex;align-items:center;justify-content:space-between;">
      <div style="display:flex;align-items:center;gap:10px;">
        <div style="width:36px;height:36px;background:rgba(255,255,255,0.2);border-radius:9px;display:flex;align-items:center;justify-content:center;">
          <i class="la la-exclamation-triangle" style="color:#fff;font-size:18px;"></i>
        </div>
        <h5 style="color:#fff;font-weight:800;margin:0;font-size:16px;">Delete Product</h5>
      </div>
      <button onclick="closeAllModals()" style="background:rgba(255,255,255,0.15);border:none;width:32px;height:32px;border-radius:8px;cursor:pointer;color:#fff;font-size:18px;display:flex;align-items:center;justify-content:center;">&times;</button>
    </div>
    <div style="padding:32px 24px;text-align:center;">
      <div style="width:72px;height:72px;background:#fdecea;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 18px;">
        <i class="la la-trash" style="font-size:36px;color:#e53935;"></i>
      </div>
      <p style="font-size:15px;color:#444;margin:0 0 6px;font-weight:600;">Delete "<strong id="d_name" style="color:#c62828;"></strong>"?</p>
      <p style="font-size:13px;color:#aaa;margin:0;">This action cannot be undone.</p>
    </div>
    <div style="border-top:1px solid #f0f2f8;padding:16px 24px;display:flex;justify-content:center;gap:12px;background:#fafbff;">
      <button class="btn btn-default" onclick="closeAllModals()" style="border:1.5px solid #e0e4ef;color:#5a6080;border-radius:9px;padding:8px 24px;font-weight:600;min-width:110px;">Cancel</button>
      <form method="POST" style="margin:0;">
        <input type="hidden" name="action"     value="delete">
        <input type="hidden" name="product_id" id="d_pid">
        <button type="submit" class="btn btn-danger" style="border-radius:9px;font-weight:700;padding:8px 24px;min-width:110px;box-shadow:0 4px 12px rgba(229,57,53,0.35);">
          <i class="la la-trash mr-1"></i> Delete
        </button>
      </form>
    </div>
  </div>
</div>

<?php include 'include/footer.php'; ?>
</div><!-- /main-panel -->

<script>
// ── Modals ─────────────────────────────────────────────────
function openModal(id) {
    document.getElementById('modalBackdrop').style.display = 'block';
    document.getElementById(id).classList.add('open');
}
function closeAllModals() {
    ['addModal','viewModal','editModal','delModal'].forEach(function(id) {
        document.getElementById(id).classList.remove('open');
    });
    document.getElementById('modalBackdrop').style.display = 'none';
}
document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeAllModals(); });

// ── Image Preview ──────────────────────────────────────────
function prevImg(input, prevId, boxId, phId, zoneId) {
    if (input.files && input.files[0]) {
        var r = new FileReader();
        r.onload = function(e) {
            document.getElementById(prevId).src = e.target.result;
            document.getElementById(boxId).style.display = 'block';
            document.getElementById(phId).style.display  = 'none';
            if (zoneId) document.getElementById(zoneId).classList.add('has-img');
        };
        r.readAsDataURL(input.files[0]);
    }
}

// ── Load Subcategories ─────────────────────────────────────
function loadSubs(catId, targetId, selectedId) {
    var sel = document.getElementById(targetId);
    sel.innerHTML = '<option value="">Loading...</option>';
    if (!catId) { sel.innerHTML = '<option value="">-- Select Category First --</option>'; return; }
    fetch('products.php?get_subcategories=1&cat_id=' + catId)
        .then(function(r){ return r.json(); })
        .then(function(data){
            if (data.length > 0) {
                sel.innerHTML = '<option value="">-- Select Sub Category --</option>';
                data.forEach(function(s){
                    var o = document.createElement('option');
                    o.value = s.sub_id; o.text = s.sub_name;
                    if (selectedId && s.sub_id == selectedId) o.selected = true;
                    sel.appendChild(o);
                });
            } else {
                sel.innerHTML = '<option value="">No subcategories found</option>';
            }
        })
        .catch(function(){ sel.innerHTML = '<option value="">Error loading</option>'; });
}

// ── Search ─────────────────────────────────────────────────
function doSearch(v) {
    v = v.toLowerCase();
    document.querySelectorAll('#pTbl tbody tr').forEach(function(row){
        row.style.display = row.textContent.toLowerCase().includes(v) ? '' : 'none';
    });
}

// ── View Modal ─────────────────────────────────────────────
function openView(id, name, img, cat, sub, brand, price, oldPrice, qty, unit, weight, status, date, rating, reviews, featured) {
    document.getElementById('v_name').textContent = name;
    document.getElementById('v_id').textContent   = 'Product #' + id;
    document.getElementById('v_date').innerHTML   = '<i class="la la-calendar"></i> Added on ' + date;

    var vi = document.getElementById('v_img'), vn = document.getElementById('v_noimg');
    if (img) { vi.src = img; vi.style.display = 'block'; vn.style.display = 'none'; }
    else     { vi.style.display = 'none'; vn.style.display = 'flex'; }

    var b = '';
    if (cat && cat !== '—')     b += '<span style="background:#eff6ff;color:#1d4ed8;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:600;">' + cat + '</span>';
    if (sub && sub !== '—')     b += '<span style="background:#f0fdf4;color:#15803d;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:600;">' + sub + '</span>';
    if (brand && brand !== '—') b += '<span style="background:#fdf4ff;color:#7e22ce;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:600;">' + brand + '</span>';
    if (featured == 1)          b += '<span style="background:#fff8e1;color:#f59e0b;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:700;">★ Featured</span>';
    b += (status == 1)
        ? '<span style="background:#e8f5e9;color:#2e7d32;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:700;">● Active</span>'
        : '<span style="background:#fdecea;color:#c62828;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:700;">● Inactive</span>';
    document.getElementById('v_badges').innerHTML = b;

    var priceHtml = '₹' + parseFloat(price).toFixed(2);
    if (parseFloat(oldPrice) > 0) {
        var disc = Math.round(((oldPrice - price) / oldPrice) * 100);
        priceHtml += '<br><small style="text-decoration:line-through;color:#aaa;font-size:11px;">₹' + parseFloat(oldPrice).toFixed(2) + '</small> <span style="background:#e8f5e9;color:#2e7d32;font-size:10px;font-weight:700;padding:1px 5px;border-radius:4px;">' + disc + '% off</span>';
    }

    var ratingHtml = (parseFloat(rating) > 0)
        ? '<span style="color:#f59e0b;font-size:16px;">★</span> <strong>' + parseFloat(rating).toFixed(1) + '</strong><br><small style="color:#aaa;font-size:11px;">(' + reviews + ' reviews)</small>'
        : '<span style="color:#ccc;">No rating</span>';

    document.getElementById('v_stats').innerHTML =
        '<div style="background:#f8f9ff;border-radius:10px;padding:12px;text-align:center;">' +
            '<p style="font-size:10px;color:#aaa;margin:0 0 3px;text-transform:uppercase;letter-spacing:0.8px;font-weight:700;">Price</p>' +
            '<p style="font-weight:800;margin:0;font-size:16px;color:#4361ee;">' + priceHtml + '</p>' +
        '</div>' +
        '<div style="background:#f8f9ff;border-radius:10px;padding:12px;text-align:center;">' +
            '<p style="font-size:10px;color:#aaa;margin:0 0 3px;text-transform:uppercase;letter-spacing:0.8px;font-weight:700;">Quantity</p>' +
            '<p style="font-weight:800;margin:0;font-size:17px;color:#1a1f36;">' + qty + ' <small style="font-size:11px;color:#8d9db5;">' + (unit||'') + '</small></p>' +
        '</div>' +
        '<div style="background:#f8f9ff;border-radius:10px;padding:12px;text-align:center;">' +
            '<p style="font-size:10px;color:#aaa;margin:0 0 3px;text-transform:uppercase;letter-spacing:0.8px;font-weight:700;">Weight</p>' +
            '<p style="font-weight:800;margin:0;font-size:15px;color:#1a1f36;">' + (weight||'—') + '</p>' +
        '</div>' +
        '<div style="background:#fffbf0;border-radius:10px;padding:12px;text-align:center;border:1px solid #fde68a;">' +
            '<p style="font-size:10px;color:#d97706;margin:0 0 3px;text-transform:uppercase;letter-spacing:0.8px;font-weight:700;">Rating</p>' +
            '<p style="font-weight:800;margin:0;font-size:15px;color:#1a1f36;">' + ratingHtml + '</p>' +
        '</div>';

    openModal('viewModal');
}

// ── Edit Modal ─────────────────────────────────────────────
function openEdit(id, name, catId, subId, brandId, price, oldPrice, qty, unit, weight, status, img, oldImg, rating, reviews, featured) {
    document.getElementById('e_pid').value       = id;
    document.getElementById('e_name').value      = name;
    document.getElementById('e_price').value     = price;
    document.getElementById('e_old_price').value = oldPrice > 0 ? oldPrice : '';
    document.getElementById('e_qty').value       = qty;
    document.getElementById('e_weight').value    = weight;
    document.getElementById('e_oldimg').value    = oldImg;
    document.getElementById('e_cat').value       = catId;
    document.getElementById('e_brand').value     = brandId;
    document.getElementById('e_status').value    = status;
    document.getElementById('e_unit').value      = unit;
    document.getElementById('e_rating').value    = rating > 0 ? rating : '';
    document.getElementById('e_reviews').value   = reviews;
    document.getElementById('e_featured').value  = featured;

    loadSubs(catId, 'e_sub', subId);

    var ci = document.getElementById('e_cur_img'), ni = document.getElementById('e_no_img');
    if (img) { ci.src = img; ci.style.display = 'block'; ni.style.display = 'none'; }
    else     { ci.style.display = 'none'; ni.style.display = 'flex'; }

    document.getElementById('e_img_inp').value = '';
    document.getElementById('e_prev_box').style.display = 'none';
    document.getElementById('e_ph').style.display = 'block';
    document.getElementById('e_zone').classList.remove('has-img');

    openModal('editModal');
}

// ── Delete Modal ───────────────────────────────────────────
function openDelete(id, name) {
    document.getElementById('d_name').textContent = name;
    document.getElementById('d_pid').value        = id;
    openModal('delModal');
}

// Auto-open on POST error
<?php if (!empty($error) && isset($_POST['action'])): ?>
openModal('<?= $_POST['action'] === 'add' ? 'addModal' : 'editModal' ?>');
<?php endif; ?>
</script>