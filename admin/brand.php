<?php
session_start();
include 'include/conn.php';

// ── AJAX: Subcategories ──────────────────────────────────────
if (isset($_GET['get_subcategories'])) {
  $cat_id = intval($_GET['cat_id']);
  $res = $mysqli->query("SELECT sub_id, sub_name FROM subcategory WHERE cat_id = $cat_id ORDER BY sub_name ASC");
  $subs = [];
  while ($r = $res->fetch_assoc()) $subs[] = $r;
  echo json_encode($subs);
  exit;
}

$error = '';

// ── DELETE ───────────────────────────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'delete') {
  $del_id = intval($_POST['brand_id']);
  $s = $mysqli->prepare("SELECT brand_logo FROM brands WHERE brand_id=?");
  $s->bind_param("i", $del_id);
  $s->execute();
  $drow = $s->get_result()->fetch_assoc();
  $s->close();
  if ($drow && !empty($drow['brand_logo'])) {
    $p = __DIR__ . '/assets/img/brands/' . $drow['brand_logo'];
    if (file_exists($p)) unlink($p);
  }
  $d = $mysqli->prepare("DELETE FROM brands WHERE brand_id=?");
  $d->bind_param("i", $del_id);
  $d->execute();
  $d->close();
  header("Location: brand.php?msg=deleted");
  exit;
}

// ── ADD ──────────────────────────────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'add') {
  $category_id     = intval($_POST['category_id']);
  $sub_category_id = intval($_POST['sub_category_id']);
  $brand_name      = trim($_POST['brand_name']);
  $brand_status    = intval($_POST['brand_status']);
  $brand_logo      = '';

  if (empty($brand_name))  $error = 'Brand name is required.';
  if (!$category_id)       $error = 'Please select a category.';
  if (!$sub_category_id)   $error = 'Please select a sub category.';

  if (empty($error) && !empty($_FILES['brand_logo']['name']) && $_FILES['brand_logo']['error'] == 0) {
    $allowed  = ['jpg','jpeg','png','webp','gif','svg'];
    $file_ext = strtolower(pathinfo($_FILES['brand_logo']['name'], PATHINFO_EXTENSION));
    if (in_array($file_ext, $allowed)) {
      $upload_dir = __DIR__ . '/assets/img/brands/';
      if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
      $imgName = time() . '_' . preg_replace('/\s+/', '_', basename($_FILES['brand_logo']['name']));
      if (move_uploaded_file($_FILES['brand_logo']['tmp_name'], $upload_dir . $imgName)) {
        $brand_logo = $imgName;
      } else {
        $error = 'Logo upload failed. Please check folder permissions.';
      }
    } else {
      $error = 'Only JPG, PNG, SVG, WEBP allowed.';
    }
  }

  if (empty($error)) {
    $stmt = $mysqli->prepare("INSERT INTO brands (category_id, sub_category_id, brand_name, brand_logo, brand_status, created_at) VALUES (?,?,?,?,?,NOW())");
    $stmt->bind_param("iissi", $category_id, $sub_category_id, $brand_name, $brand_logo, $brand_status);
    if ($stmt->execute()) { header("Location: brand.php?msg=added"); exit; }
    else $error = 'Database error: ' . $mysqli->error;
    $stmt->close();
  }
}

// ── EDIT ─────────────────────────────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'edit') {
  $edit_id         = intval($_POST['brand_id']);
  $category_id     = intval($_POST['category_id']);
  $sub_category_id = intval($_POST['sub_category_id']);
  $brand_name      = trim($_POST['brand_name']);
  $brand_status    = intval($_POST['brand_status']);
  $old_logo        = trim($_POST['old_logo']);
  $brand_logo      = $old_logo;

  if (empty($brand_name))  $error = 'Brand name is required.';
  if (!$category_id)       $error = 'Please select a category.';
  if (!$sub_category_id)   $error = 'Please select a sub category.';

  if (empty($error) && !empty($_FILES['brand_logo']['name']) && $_FILES['brand_logo']['error'] == 0) {
    $allowed  = ['jpg','jpeg','png','webp','gif','svg'];
    $file_ext = strtolower(pathinfo($_FILES['brand_logo']['name'], PATHINFO_EXTENSION));
    if (in_array($file_ext, $allowed)) {
      $upload_dir = __DIR__ . '/assets/img/brands/';
      if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
      $imgName = time() . '_' . preg_replace('/\s+/', '_', basename($_FILES['brand_logo']['name']));
      if (move_uploaded_file($_FILES['brand_logo']['tmp_name'], $upload_dir . $imgName)) {
        if (!empty($old_logo) && file_exists($upload_dir . $old_logo)) unlink($upload_dir . $old_logo);
        $brand_logo = $imgName;
      } else {
        $error = 'Logo upload failed.';
      }
    } else {
      $error = 'Only JPG, PNG, SVG, WEBP allowed.';
    }
  }

  if (empty($error)) {
    $stmt = $mysqli->prepare("UPDATE brands SET category_id=?, sub_category_id=?, brand_name=?, brand_logo=?, brand_status=? WHERE brand_id=?");
    $stmt->bind_param("iissii", $category_id, $sub_category_id, $brand_name, $brand_logo, $brand_status, $edit_id);
    if ($stmt->execute()) { header("Location: brand.php?msg=updated"); exit; }
    else $error = 'Database error: ' . $mysqli->error;
    $stmt->close();
  }
}

// ── FETCH ALL ────────────────────────────────────────────────
$brands_result = $mysqli->query("
  SELECT b.*, c.cat_name, IFNULL(s.sub_name,'—') AS sub_name
  FROM brands b
  LEFT JOIN category c ON b.category_id = c.cat_id
  LEFT JOIN subcategory s ON b.sub_category_id = s.sub_id
  ORDER BY b.created_at DESC
");

$cats_arr = [];
$cr = $mysqli->query("SELECT cat_id, cat_name FROM category ORDER BY cat_name ASC");
while ($row = $cr->fetch_assoc()) $cats_arr[] = $row;

include 'include/header.php';
?>

<div class="main-panel">
  <div class="content">
    <div class="container-fluid">

      <!-- Page Header -->
      <div class="d-flex justify-content-between align-items-center mb-4 mt-2">
        <div>
          <h4 class="page-title mb-0">Brands</h4>
          <p class="text-muted mb-0" style="font-size:13px;margin-top:3px;">Manage all your brands in one place</p>
        </div>
        <button class="btn btn-primary btn-sm d-flex align-items-center" onclick="openModal('addModal')" style="gap:6px;">
          <i class="la la-plus" style="font-size:16px;"></i> Add New Brand
        </button>
      </div>

      <!-- Flash -->
      <?php if (isset($_GET['msg'])):
        $msgs = [
          'added'   => ['success','<i class="la la-check-circle mr-1"></i> Brand added successfully!'],
          'updated' => ['success','<i class="la la-check-circle mr-1"></i> Brand updated successfully!'],
          'deleted' => ['danger', '<i class="la la-trash mr-1"></i> Brand deleted successfully!'],
        ];
        $m = $msgs[$_GET['msg']] ?? null;
        if ($m): ?>
          <div class="alert alert-<?= $m[0] ?>" style="border-radius:10px;border:none;font-weight:600;font-size:13.5px;box-shadow:0 4px 14px rgba(0,0,0,0.08);">
            <?= $m[1] ?>
            <button onclick="this.closest('.alert').remove()" style="background:none;border:none;float:right;font-size:20px;cursor:pointer;opacity:0.6;line-height:1;">&times;</button>
          </div>
        <?php endif; endif; ?>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger" style="border-radius:10px;border:none;font-weight:600;font-size:13.5px;box-shadow:0 4px 14px rgba(0,0,0,0.08);">
          <i class="la la-exclamation-circle mr-1"></i> <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <!-- Table Card -->
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="card-title mb-0" style="display:flex;align-items:center;gap:8px;">
            <i class="la la-tag" style="color:#4361ee;font-size:20px;"></i> All Brands
          </h5>
          <span class="badge-count"><?= $brands_result ? $brands_result->num_rows : 0 ?> Brands</span>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead>
                <tr>
                  <th style="padding-left:22px;width:60px;">ID</th>
                  <th style="width:80px;">Logo</th>
                  <th>Brand Name</th>
                  <th>Category</th>
                  <th>Sub Category</th>
                  <th>Status</th>
                  <th>Created At</th>
                  <th style="width:210px;text-align:right;padding-right:22px;">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if ($brands_result && $brands_result->num_rows > 0):
                  while ($row = $brands_result->fetch_assoc()):
                    $logo_dir  = __DIR__ . '/assets/img/brands/';
                    $logo_file = $row['brand_logo'] ?? '';
                    $logo_src  = 'assets/img/brands/' . $logo_file;
                    $has_logo  = !empty($logo_file) && file_exists($logo_dir . $logo_file);

                    // ✅ REMOVED: $sub_opts pre-fetch loop — ab AJAX handle karega
                    $js_brand_id  = (int)$row['brand_id'];
                    $js_name      = json_encode($row['brand_name']);
                    $js_cat       = json_encode($row['cat_name'] ?? '');
                    $js_sub       = json_encode($row['sub_name'] ?? '');
                    $js_logo      = json_encode($has_logo ? $logo_src : '');
                    $js_oldlogo   = json_encode($logo_file);
                    $js_date      = json_encode(date('d M Y, h:i A', strtotime($row['created_at'])));
                    $js_cat_id    = (int)$row['category_id'];
                    $js_sub_id    = (int)$row['sub_category_id'];
                    $js_status    = (int)$row['brand_status'];
                    // ✅ REMOVED: $js_subopts — no longer needed
                ?>
                <tr>
                  <td style="padding-left:22px;">
                    <span style="color:#8d9db5;font-weight:700;font-size:13px;">#<?= $row['brand_id'] ?></span>
                  </td>
                  <td>
                    <?php if ($has_logo): ?>
                      <img src="<?= htmlspecialchars($logo_src) ?>" class="thumb-img"
                        onerror="this.style.display='none';this.nextElementSibling.style.display='flex';" alt="">
                      <div class="thumb-placeholder" style="display:none;"><i class="la la-image" style="color:#bbb;font-size:20px;"></i></div>
                    <?php else: ?>
                      <div class="thumb-placeholder"><i class="la la-image" style="color:#bbb;font-size:20px;"></i></div>
                    <?php endif; ?>
                  </td>
                  <td><span style="font-weight:700;color:#1a1f36;font-size:14px;"><?= htmlspecialchars($row['brand_name']) ?></span></td>
                  <td>
                    <span style="background:#eff6ff;color:#1d4ed8;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;">
                      <?= htmlspecialchars($row['cat_name'] ?? '—') ?>
                    </span>
                  </td>
                  <td>
                    <span style="background:#f0fdf4;color:#15803d;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;">
                      <?= htmlspecialchars($row['sub_name'] ?? '—') ?>
                    </span>
                  </td>
                  <td>
                    <?php if ($row['brand_status'] == 1): ?>
                      <span style="background:#e8f5e9;color:#2e7d32;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:700;">● Active</span>
                    <?php else: ?>
                      <span style="background:#fdecea;color:#c62828;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:700;">● Inactive</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <span style="font-size:13px;color:#8d9db5;display:flex;align-items:center;gap:5px;">
                      <i class="la la-calendar" style="font-size:15px;"></i>
                      <?= date('d M Y', strtotime($row['created_at'])) ?>
                      <span style="color:#bbb;">·</span>
                      <?= date('h:i A', strtotime($row['created_at'])) ?>
                    </span>
                  </td>
                  <td style="text-align:right;padding-right:22px;">
                    <!-- View Button -->
                    <button class="btn btn-sm btn-action"
                      style="border:1.5px solid #0288d1;color:#0288d1;margin-right:4px;"
                      data-id="<?= $js_brand_id ?>"
                      data-name=<?= $js_name ?>
                      data-cat=<?= $js_cat ?>
                      data-sub=<?= $js_sub ?>
                      data-logo=<?= $js_logo ?>
                      data-status="<?= $js_status ?>"
                      data-date=<?= $js_date ?>
                      onclick="openViewBtn(this)" title="View">
                      <i class="la la-eye"></i>
                    </button>

                    <!-- Edit Button — ✅ data-subopts REMOVED, AJAX se load hoga -->
                    <button class="btn btn-sm btn-action"
                      style="border:1.5px solid #4361ee;color:#4361ee;margin-right:4px;"
                      data-id="<?= $js_brand_id ?>"
                      data-name=<?= $js_name ?>
                      data-catid="<?= $js_cat_id ?>"
                      data-subid="<?= $js_sub_id ?>"
                      data-status="<?= $js_status ?>"
                      data-logo=<?= $js_logo ?>
                      data-oldlogo=<?= $js_oldlogo ?>
                      onclick="openEditBtn(this)" title="Edit">
                      <i class="la la-edit"></i> Edit
                    </button>

                    <!-- Delete Button -->
                    <button class="btn btn-sm btn-action"
                      style="border:1.5px solid #e53935;color:#e53935;"
                      data-id="<?= $js_brand_id ?>"
                      data-name=<?= $js_name ?>
                      onclick="openDeleteBtn(this)" title="Delete">
                      <i class="la la-trash"></i>
                    </button>
                  </td>
                </tr>
                <?php endwhile; else: ?>
                <tr>
                  <td colspan="8" style="text-align:center;padding:60px 20px;color:#aaa;">
                    <i class="la la-inbox" style="font-size:48px;display:block;margin-bottom:10px;color:#d0d5e8;"></i>
                    <p style="font-size:15px;color:#b0b8cc;margin:0 0 10px;">No brands found.</p>
                    <a href="javascript:void(0)" onclick="openModal('addModal')" style="color:#4361ee;font-weight:600;font-size:13px;">+ Add your first brand</a>
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
    <div style="background:#fff;border-radius:16px;width:100%;max-width:560px;margin:auto;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,0.18);">
      <div style="background:linear-gradient(135deg,#4361ee,#6c8fff);padding:20px 24px;display:flex;align-items:center;justify-content:space-between;">
        <div style="display:flex;align-items:center;gap:10px;">
          <div style="width:36px;height:36px;background:rgba(255,255,255,0.2);border-radius:9px;display:flex;align-items:center;justify-content:center;">
            <i class="la la-plus-circle" style="color:#fff;font-size:20px;"></i>
          </div>
          <div>
            <h5 style="color:#fff;font-weight:800;margin:0;font-size:16px;">Add New Brand</h5>
            <p style="color:rgba(255,255,255,0.7);margin:0;font-size:12px;">Fill in the details below</p>
          </div>
        </div>
        <button onclick="closeAllModals()" style="background:rgba(255,255,255,0.15);border:none;width:32px;height:32px;border-radius:8px;cursor:pointer;color:#fff;font-size:18px;display:flex;align-items:center;justify-content:center;">&times;</button>
      </div>
      <form method="POST" action="brand.php" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add">
        <div style="padding:24px;max-height:70vh;overflow-y:auto;">
          <div class="row">
            <div class="col-md-6">
              <div style="margin-bottom:16px;">
                <label class="form-label">Brand Name <span style="color:#e53935;">*</span></label>
                <input type="text" name="brand_name" class="form-control" placeholder="e.g. Nike, Samsung..." required>
              </div>
              <div style="margin-bottom:16px;">
                <label class="form-label">Category <span style="color:#e53935;">*</span></label>
                <select name="category_id" id="add_category_id" class="form-control" required onchange="loadSubcats(this.value,'add_sub_category_id')">
                  <option value="">-- Select Category --</option>
                  <?php foreach ($cats_arr as $c): ?>
                    <option value="<?= $c['cat_id'] ?>"><?= htmlspecialchars($c['cat_name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div style="margin-bottom:16px;">
                <label class="form-label">Sub Category <span style="color:#e53935;">*</span></label>
                <select name="sub_category_id" id="add_sub_category_id" class="form-control" required>
                  <option value="">-- Select Category First --</option>
                </select>
              </div>
              <div style="margin-bottom:16px;">
                <label class="form-label">Status</label>
                <select name="brand_status" class="form-control">
                  <option value="1">Active</option>
                  <option value="0">Inactive</option>
                </select>
              </div>
            </div>
            <div class="col-md-6 d-flex flex-column">
              <label class="form-label">Brand Logo</label>
              <div class="upload-zone" id="add_zone" onclick="document.getElementById('add_logo_input').click()"
                style="flex:1;min-height:200px;display:flex;flex-direction:column;align-items:center;justify-content:center;">
                <div id="add_preview_box" style="display:none;margin-bottom:10px;">
                  <img id="add_logo_preview" src="#" alt="Preview" style="max-height:130px;max-width:100%;border-radius:8px;object-fit:contain;">
                </div>
                <div id="add_placeholder">
                  <div style="width:52px;height:52px;background:#f0f4ff;border-radius:12px;display:flex;align-items:center;justify-content:center;margin:0 auto 10px;">
                    <i class="la la-cloud-upload" style="font-size:28px;color:#4361ee;"></i>
                  </div>
                  <p style="color:#4361ee;font-weight:700;margin:0 0 3px;font-size:14px;">Click to upload logo</p>
                  <p style="color:#aaa;font-size:12px;margin:0;">JPG, PNG, SVG, WEBP · Max 5MB</p>
                </div>
                <input type="file" name="brand_logo" id="add_logo_input" accept="image/*" style="display:none;"
                  onchange="previewImg(this,'add_logo_preview','add_preview_box','add_placeholder','add_zone')">
              </div>
            </div>
          </div>
        </div>
        <div style="border-top:1px solid #f0f2f8;padding:16px 24px;display:flex;justify-content:flex-end;gap:10px;background:#fafbff;">
          <button type="button" class="btn btn-default" onclick="closeAllModals()" style="border:1.5px solid #e0e4ef;color:#5a6080;border-radius:9px;padding:8px 20px;font-weight:600;">Cancel</button>
          <button type="submit" class="btn btn-primary"><i class="la la-save mr-1"></i> Save Brand</button>
        </div>
      </form>
    </div>
  </div>

  <!-- ══ VIEW MODAL ═══════════════════════════════════════════ -->
  <div id="viewModal" class="modal-box-wrap">
    <div style="background:#fff;border-radius:16px;width:100%;max-width:460px;margin:auto;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,0.18);">
      <div style="background:linear-gradient(135deg,#1b5e20,#2e7d32);padding:20px 24px;display:flex;align-items:center;justify-content:space-between;">
        <div style="display:flex;align-items:center;gap:10px;">
          <div style="width:36px;height:36px;background:rgba(255,255,255,0.2);border-radius:9px;display:flex;align-items:center;justify-content:center;">
            <i class="la la-eye" style="color:#fff;font-size:20px;"></i>
          </div>
          <h5 style="color:#fff;font-weight:800;margin:0;font-size:16px;">Brand Details</h5>
        </div>
        <button onclick="closeAllModals()" style="background:rgba(255,255,255,0.15);border:none;width:32px;height:32px;border-radius:8px;cursor:pointer;color:#fff;font-size:18px;display:flex;align-items:center;justify-content:center;">&times;</button>
      </div>
      <div style="padding:32px 24px;text-align:center;">
        <div style="margin-bottom:18px;">
          <img id="view_logo" src="" alt=""
            style="width:120px;height:120px;object-fit:contain;border-radius:16px;border:3px solid #e8f5e9;box-shadow:0 6px 20px rgba(0,0,0,0.1);padding:8px;"
            onerror="this.style.display='none';document.getElementById('view_no_logo').style.display='flex';">
          <div id="view_no_logo" style="display:none;width:120px;height:120px;background:#f4f5f7;border-radius:16px;align-items:center;justify-content:center;border:3px solid #e8f5e9;margin:0 auto;">
            <i class="la la-image" style="font-size:40px;color:#bbb;"></i>
          </div>
        </div>
        <h4 id="view_name" style="font-weight:800;color:#1a1f36;margin-bottom:10px;font-size:20px;"></h4>
        <div style="display:flex;justify-content:center;gap:8px;margin-bottom:20px;flex-wrap:wrap;">
          <span id="view_cat_badge" style="background:#eff6ff;color:#1d4ed8;padding:4px 14px;border-radius:20px;font-size:12px;font-weight:600;"></span>
          <span id="view_sub_badge" style="background:#f0fdf4;color:#15803d;padding:4px 14px;border-radius:20px;font-size:12px;font-weight:600;"></span>
          <span id="view_status_badge" style="padding:4px 14px;border-radius:20px;font-size:12px;font-weight:600;"></span>
        </div>
        <div style="display:flex;justify-content:center;gap:28px;">
          <div>
            <p style="font-size:11px;color:#aaa;margin:0 0 4px;text-transform:uppercase;letter-spacing:0.8px;font-weight:700;">ID</p>
            <p id="view_id" style="font-weight:800;margin:0;font-size:18px;color:#4361ee;"></p>
          </div>
          <div>
            <p style="font-size:11px;color:#aaa;margin:0 0 4px;text-transform:uppercase;letter-spacing:0.8px;font-weight:700;">Created</p>
            <p id="view_date" style="font-weight:600;margin:0;font-size:13px;color:#444;"></p>
          </div>
        </div>
      </div>
      <div style="border-top:1px solid #f0f2f8;padding:16px 24px;display:flex;justify-content:center;background:#fafbff;">
        <button class="btn btn-default" onclick="closeAllModals()" style="border:1.5px solid #e0e4ef;color:#5a6080;border-radius:9px;padding:8px 28px;font-weight:600;">Close</button>
      </div>
    </div>
  </div>

  <!-- ══ EDIT MODAL ═══════════════════════════════════════════ -->
  <div id="editModal" class="modal-box-wrap">
    <div style="background:#fff;border-radius:16px;width:100%;max-width:560px;margin:auto;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,0.18);">
      <div style="background:linear-gradient(135deg,#e65100,#ff8c00);padding:20px 24px;display:flex;align-items:center;justify-content:space-between;">
        <div style="display:flex;align-items:center;gap:10px;">
          <div style="width:36px;height:36px;background:rgba(255,255,255,0.2);border-radius:9px;display:flex;align-items:center;justify-content:center;">
            <i class="la la-edit" style="color:#fff;font-size:20px;"></i>
          </div>
          <div>
            <h5 style="color:#fff;font-weight:800;margin:0;font-size:16px;">Edit Brand</h5>
            <p style="color:rgba(255,255,255,0.75);margin:0;font-size:12px;">Update the brand info</p>
          </div>
        </div>
        <button onclick="closeAllModals()" style="background:rgba(255,255,255,0.15);border:none;width:32px;height:32px;border-radius:8px;cursor:pointer;color:#fff;font-size:18px;display:flex;align-items:center;justify-content:center;">&times;</button>
      </div>
      <form method="POST" action="brand.php" enctype="multipart/form-data">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="brand_id"  id="edit_brand_id">
        <input type="hidden" name="old_logo"  id="edit_old_logo">
        <div style="padding:24px;max-height:70vh;overflow-y:auto;">
          <div class="row">
            <div class="col-md-6">
              <div style="margin-bottom:16px;">
                <label class="form-label">Brand Name <span style="color:#e53935;">*</span></label>
                <input type="text" name="brand_name" id="edit_brand_name" class="form-control" required>
              </div>
              <div style="margin-bottom:16px;">
                <label class="form-label">Category <span style="color:#e53935;">*</span></label>
                <select name="category_id" id="edit_category_id" class="form-control" required onchange="loadSubcats(this.value,'edit_sub_category_id')">
                  <option value="">-- Select Category --</option>
                  <?php foreach ($cats_arr as $c): ?>
                    <option value="<?= $c['cat_id'] ?>"><?= htmlspecialchars($c['cat_name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div style="margin-bottom:16px;">
                <label class="form-label">Sub Category <span style="color:#e53935;">*</span></label>
                <select name="sub_category_id" id="edit_sub_category_id" class="form-control" required>
                  <option value="">-- Select Category First --</option>
                </select>
              </div>
              <div style="margin-bottom:16px;">
                <label class="form-label">Status</label>
                <select name="brand_status" id="edit_brand_status" class="form-control">
                  <option value="1">Active</option>
                  <option value="0">Inactive</option>
                </select>
              </div>
            </div>
            <div class="col-md-6 d-flex flex-column">
              <label class="form-label">Current Logo</label>
              <div style="display:flex;align-items:center;gap:14px;margin-bottom:14px;">
                <img id="edit_current_logo" src="" alt=""
                  style="height:80px;width:80px;object-fit:contain;border-radius:12px;border:2px solid #eef0f8;padding:4px;box-shadow:0 2px 8px rgba(0,0,0,0.08);"
                  onerror="this.style.display='none';document.getElementById('edit_no_logo').style.display='flex';">
                <div id="edit_no_logo" style="display:none;width:80px;height:80px;background:#f4f5f7;border-radius:12px;align-items:center;justify-content:center;border:2px solid #eef0f8;">
                  <i class="la la-image" style="color:#bbb;font-size:24px;"></i>
                </div>
                <p style="font-size:12px;color:#aaa;margin:0;">Upload new to replace.</p>
              </div>
              <label class="form-label">New Logo <span style="font-weight:400;font-size:12px;color:#aaa;">(optional)</span></label>
              <div class="upload-zone" id="edit_zone" onclick="document.getElementById('edit_logo_input').click()"
                style="flex:1;min-height:110px;display:flex;flex-direction:column;align-items:center;justify-content:center;">
                <div id="edit_preview_box" style="display:none;margin-bottom:8px;">
                  <img id="edit_logo_preview" src="" style="max-height:90px;border-radius:10px;box-shadow:0 4px 14px rgba(0,0,0,0.1);">
                </div>
                <div id="edit_placeholder">
                  <i class="la la-cloud-upload" style="font-size:26px;color:#4361ee;"></i>
                  <p style="color:#4361ee;font-weight:600;font-size:13px;margin:4px 0 0;">Click to select new logo</p>
                </div>
                <input type="file" name="brand_logo" id="edit_logo_input" accept="image/*" style="display:none;"
                  onchange="previewImg(this,'edit_logo_preview','edit_preview_box','edit_placeholder','edit_zone')">
              </div>
            </div>
          </div>
        </div>
        <div style="border-top:1px solid #f0f2f8;padding:16px 24px;display:flex;justify-content:flex-end;gap:10px;background:#fafbff;">
          <button type="button" class="btn btn-default" onclick="closeAllModals()" style="border:1.5px solid #e0e4ef;color:#5a6080;border-radius:9px;padding:8px 20px;font-weight:600;">Cancel</button>
          <button type="submit" class="btn btn-warning" style="color:#fff;border-radius:9px;font-weight:700;padding:8px 20px;box-shadow:0 4px 12px rgba(230,81,0,0.3);">
            <i class="la la-save mr-1"></i> Update Brand
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- ══ DELETE MODAL ══════════════════════════════════════════ -->
  <div id="deleteModal" class="modal-box-wrap">
    <div style="background:#fff;border-radius:16px;width:100%;max-width:420px;margin:auto;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,0.18);">
      <div style="background:linear-gradient(135deg,#b71c1c,#e53935);padding:20px 24px;display:flex;align-items:center;justify-content:space-between;">
        <div style="display:flex;align-items:center;gap:10px;">
          <div style="width:36px;height:36px;background:rgba(255,255,255,0.2);border-radius:9px;display:flex;align-items:center;justify-content:center;">
            <i class="la la-exclamation-triangle" style="color:#fff;font-size:18px;"></i>
          </div>
          <h5 style="color:#fff;font-weight:800;margin:0;font-size:16px;">Delete Brand</h5>
        </div>
        <button onclick="closeAllModals()" style="background:rgba(255,255,255,0.15);border:none;width:32px;height:32px;border-radius:8px;cursor:pointer;color:#fff;font-size:18px;display:flex;align-items:center;justify-content:center;">&times;</button>
      </div>
      <div style="padding:32px 24px;text-align:center;">
        <div style="width:72px;height:72px;background:#fdecea;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 18px;">
          <i class="la la-trash" style="font-size:36px;color:#e53935;"></i>
        </div>
        <p style="font-size:15px;color:#444;margin:0 0 6px;font-weight:600;">Delete "<strong id="delete_name" style="color:#c62828;"></strong>"?</p>
        <p style="font-size:13px;color:#aaa;margin:0;">This action cannot be undone.</p>
      </div>
      <div style="border-top:1px solid #f0f2f8;padding:16px 24px;display:flex;justify-content:center;gap:12px;background:#fafbff;">
        <button class="btn btn-default" onclick="closeAllModals()" style="border:1.5px solid #e0e4ef;color:#5a6080;border-radius:9px;padding:8px 24px;font-weight:600;min-width:110px;">Cancel</button>
        <form method="POST" action="brand.php" style="margin:0;">
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="brand_id" id="delete_brand_id">
          <button type="submit" class="btn btn-danger" style="border-radius:9px;font-weight:700;padding:8px 24px;min-width:110px;box-shadow:0 4px 12px rgba(229,57,53,0.35);">
            <i class="la la-trash mr-1"></i> Delete
          </button>
        </form>
      </div>
    </div>
  </div>

  <?php include 'include/footer.php'; ?>
</div>

<script>
// ── Modals ────────────────────────────────────────────────
function openModal(id) {
  document.getElementById('modalBackdrop').style.display = 'block';
  document.getElementById(id).classList.add('open');
}
function closeAllModals() {
  ['addModal','viewModal','editModal','deleteModal'].forEach(function(id) {
    document.getElementById(id).classList.remove('open');
  });
  document.getElementById('modalBackdrop').style.display = 'none';
}
document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeAllModals(); });

// ── Image Preview ─────────────────────────────────────────
function previewImg(input, previewId, boxId, phId, zoneId) {
  if (input.files && input.files[0]) {
    var r = new FileReader();
    r.onload = function(e) {
      document.getElementById(previewId).src = e.target.result;
      document.getElementById(boxId).style.display  = 'block';
      document.getElementById(phId).style.display   = 'none';
      if (zoneId) document.getElementById(zoneId).classList.add('has-image');
    };
    r.readAsDataURL(input.files[0]);
  }
}

// ── Load Subcategories via AJAX ───────────────────────────
function loadSubcats(catId, targetId, selectedId) {
  var sel = document.getElementById(targetId);
  sel.innerHTML = '<option value="">Loading...</option>';
  if (!catId) { sel.innerHTML = '<option value="">-- Select Category First --</option>'; return; }
  fetch('brand.php?get_subcategories=1&cat_id=' + catId)
    .then(function(r) { return r.json(); })
    .then(function(data) {
      if (data.length > 0) {
        sel.innerHTML = '<option value="">-- Select Sub Category --</option>';
        data.forEach(function(sub) {
          var opt = document.createElement('option');
          opt.value = sub.sub_id;
          opt.textContent = sub.sub_name;
          if (selectedId && String(sub.sub_id) === String(selectedId)) opt.selected = true;
          sel.appendChild(opt);
        });
      } else {
        sel.innerHTML = '<option value="">No sub categories found</option>';
      }
    })
    .catch(function() { sel.innerHTML = '<option value="">Error loading</option>'; });
}

// ── View Modal ────────────────────────────────────────────
function openViewBtn(btn) {
  var id      = btn.dataset.id;
  var name    = btn.dataset.name;
  var catName = btn.dataset.cat;
  var subName = btn.dataset.sub;
  var logoSrc = btn.dataset.logo;
  var status  = btn.dataset.status;
  var date    = btn.dataset.date;

  document.getElementById('view_id').textContent        = '#' + id;
  document.getElementById('view_name').textContent      = name;
  document.getElementById('view_cat_badge').textContent = catName;
  document.getElementById('view_sub_badge').textContent = subName;
  document.getElementById('view_date').textContent      = date;

  var sb = document.getElementById('view_status_badge');
  if (parseInt(status) === 1) {
    sb.textContent = '● Active';
    sb.style.cssText = 'background:#e8f5e9;color:#2e7d32;padding:4px 14px;border-radius:20px;font-size:12px;font-weight:700;';
  } else {
    sb.textContent = '● Inactive';
    sb.style.cssText = 'background:#fdecea;color:#c62828;padding:4px 14px;border-radius:20px;font-size:12px;font-weight:700;';
  }

  var logo = document.getElementById('view_logo');
  var noL  = document.getElementById('view_no_logo');
  if (logoSrc) { logo.src = logoSrc; logo.style.display = 'block'; noL.style.display = 'none'; }
  else         { logo.style.display = 'none'; noL.style.display = 'flex'; }

  openModal('viewModal');
}

// ── Edit Modal ────────────────────────────────────────────
// ✅ FIX: data-subopts hataya — ab AJAX se subcategories load hoti hain
// Isse naye aur purane dono brands ka edit sahi kaam karta hai
function openEditBtn(btn) {
  var id          = btn.dataset.id;
  var name        = btn.dataset.name;
  var catId       = btn.dataset.catid;
  var subId       = btn.dataset.subid;
  var status      = btn.dataset.status;
  var logoSrc     = btn.dataset.logo;
  var oldLogoName = btn.dataset.oldlogo;

  document.getElementById('edit_brand_id').value    = id;
  document.getElementById('edit_brand_name').value  = name;
  document.getElementById('edit_old_logo').value    = oldLogoName;
  document.getElementById('edit_category_id').value = catId;
  document.getElementById('edit_brand_status').value = parseInt(status) === 1 ? '1' : '0';

  // ✅ AJAX se subcategories load karo aur subId auto-select karo
  loadSubcats(catId, 'edit_sub_category_id', subId);

  // Reset upload zone
  document.getElementById('edit_logo_input').value          = '';
  document.getElementById('edit_preview_box').style.display = 'none';
  document.getElementById('edit_placeholder').style.display = 'block';
  document.getElementById('edit_zone').classList.remove('has-image');

  // Current logo
  var curLogo = document.getElementById('edit_current_logo');
  var noLogo  = document.getElementById('edit_no_logo');
  if (logoSrc) { curLogo.src = logoSrc; curLogo.style.display = 'block'; noLogo.style.display = 'none'; }
  else         { curLogo.style.display = 'none'; noLogo.style.display = 'flex'; }

  openModal('editModal');
}

// ── Delete Modal ──────────────────────────────────────────
function openDeleteBtn(btn) {
  document.getElementById('delete_name').textContent = btn.dataset.name;
  document.getElementById('delete_brand_id').value   = btn.dataset.id;
  openModal('deleteModal');
}

<?php if (!empty($error) && isset($_POST['action'])): ?>
  openModal('<?= $_POST['action'] === 'add' ? 'addModal' : 'editModal' ?>');
<?php endif; ?>
</script>