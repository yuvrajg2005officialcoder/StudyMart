<?php
include 'include/conn.php';

$success = '';
$error   = '';

if (isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id   = intval($_POST['cat_id']);
    $stmt = $mysqli->prepare("SELECT cat_img FROM category WHERE cat_id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($row && !empty($row['cat_img'])) {
        $p = __DIR__ . '/assets/img/categories/' . $row['cat_img'];
        if (file_exists($p)) unlink($p);
    }
    $del = $mysqli->prepare("DELETE FROM category WHERE cat_id=?");
    $del->bind_param("i", $id);
    $del->execute();
    $del->close();
    header("Location: categories.php?msg=deleted");
    exit;
}

if (isset($_POST['action']) && $_POST['action'] === 'add') {
    $cat_name = trim($_POST['cat_name']);
    $cat_img  = '';
    if (empty($cat_name)) { $error = 'Category name is required.'; }
    if (empty($error)) {
        if (isset($_FILES['cat_img']) && $_FILES['cat_img']['error'] == 0) {
            $allowed = ['jpg','jpeg','png','webp','gif'];
            $ext     = strtolower(pathinfo($_FILES['cat_img']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed)) {
                $error = 'Only JPG, PNG, WEBP, GIF allowed.';
            } else {
                $dir = __DIR__ . '/assets/img/categories/';
                if (!is_dir($dir)) mkdir($dir, 0777, true);
                $new = time() . '_' . basename($_FILES['cat_img']['name']);
                if (move_uploaded_file($_FILES['cat_img']['tmp_name'], $dir . $new)) {
                    $cat_img = $new;
                } else { $error = 'Image upload failed.'; }
            }
        } else { $error = 'Category image is required.'; }
    }
    if (empty($error)) {
        $stmt = $mysqli->prepare("INSERT INTO category (cat_name, cat_img, created_at) VALUES (?,?,NOW())");
        $stmt->bind_param("ss", $cat_name, $cat_img);
        $stmt->execute();
        $stmt->close();
        header("Location: categories.php?msg=added");
        exit;
    }
}

if (isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id       = intval($_POST['cat_id']);
    $cat_name = trim($_POST['cat_name']);
    $old_img  = $_POST['old_img'];
    $cat_img  = $old_img;
    if (empty($cat_name)) { $error = 'Category name is required.'; }
    if (empty($error) && isset($_FILES['cat_img']) && $_FILES['cat_img']['error'] == 0) {
        $allowed = ['jpg','jpeg','png','webp','gif'];
        $ext     = strtolower(pathinfo($_FILES['cat_img']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $dir = __DIR__ . '/assets/img/categories/';
            if (!is_dir($dir)) mkdir($dir, 0777, true);
            $new = time() . '_' . basename($_FILES['cat_img']['name']);
            if (move_uploaded_file($_FILES['cat_img']['tmp_name'], $dir . $new)) {
                if (!empty($old_img) && file_exists($dir . $old_img)) unlink($dir . $old_img);
                $cat_img = $new;
            } else { $error = 'Image upload failed.'; }
        } else { $error = 'Only JPG, PNG, WEBP, GIF allowed.'; }
    }
    if (empty($error)) {
        $stmt = $mysqli->prepare("UPDATE category SET cat_name=?, cat_img=? WHERE cat_id=?");
        $stmt->bind_param("ssi", $cat_name, $cat_img, $id);
        $stmt->execute();
        $stmt->close();
        header("Location: categories.php?msg=updated");
        exit;
    }
}

$result = $mysqli->query("SELECT * FROM category ORDER BY created_at DESC");
include 'include/header.php';
?>

<div class="main-panel">
<div class="content">
<div class="container-fluid">

  <!-- Page header -->
  <div class="d-flex justify-content-between align-items-center mb-4 mt-2">
    <div>
      <h4 class="page-title mb-0">Categories</h4>
      <p class="text-muted mb-0" style="font-size:13px; margin-top:3px;">Manage all product categories in one place</p>
    </div>
    <button class="btn btn-primary btn-sm d-flex align-items-center gap-1" onclick="openModal('addModal')" style="gap:6px;">
      <i class="la la-plus" style="font-size:16px;"></i> Add New Category
    </button>
  </div>

  <!-- Flash messages -->
  <?php if (isset($_GET['msg'])): ?>
    <?php
      $msgs = [
        'added'   => ['success', '<i class="la la-check-circle mr-1"></i> Category added successfully!'],
        'updated' => ['success', '<i class="la la-check-circle mr-1"></i> Category updated successfully!'],
        'deleted' => ['danger',  '<i class="la la-trash mr-1"></i> Category deleted successfully!'],
      ];
      $m = $msgs[$_GET['msg']] ?? null;
    ?>
    <?php if ($m): ?>
    <div class="alert alert-<?= $m[0] ?> alert-dismissible fade show"
         style="border-radius:10px; border:none; font-weight:600; font-size:13.5px;
                box-shadow:0 4px 14px rgba(0,0,0,0.08); animation: fadeInUp 0.3s ease;">
      <?= $m[1] ?>
      <button type="button" onclick="this.closest('.alert').remove()"
              style="background:none;border:none;float:right;font-size:20px;cursor:pointer;opacity:0.6;line-height:1;">&times;</button>
    </div>
    <?php endif; ?>
  <?php endif; ?>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show"
         style="border-radius:10px; border:none; font-weight:600; font-size:13.5px;
                box-shadow:0 4px 14px rgba(0,0,0,0.08);">
      <i class="la la-exclamation-circle mr-1"></i> <?= htmlspecialchars($error) ?>
      <button type="button" onclick="this.closest('.alert').remove()"
              style="background:none;border:none;float:right;font-size:20px;cursor:pointer;opacity:0.6;line-height:1;">&times;</button>
    </div>
  <?php endif; ?>

  <!-- Table card -->
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="card-title mb-0" style="display:flex;align-items:center;gap:8px;">
        <i class="la la-list" style="color:#4361ee;font-size:20px;"></i> All Categories
      </h5>
      <span class="badge-count"><?= $result->num_rows ?> Categories</span>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th style="padding-left:22px; width:60px;">ID</th>
              <th style="width:90px;">Image</th>
              <th>Category Name</th>
              <th>Created At</th>
              <th style="width:90px;">Status</th>
              <th style="width:180px; text-align:right; padding-right:22px;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($result->num_rows > 0): while ($row = $result->fetch_assoc()):
              $img_dir  = __DIR__ . '/assets/img/categories/';
              $img_file = !empty($row['cat_img']) ? $row['cat_img'] : '';
              $img_src  = 'assets/img/categories/' . $img_file;
              $has_img  = !empty($img_file) && file_exists($img_dir . $img_file);
              $st       = $row['cat_status'] ?? 0;
            ?>
            <tr>
              <td style="padding-left:22px;">
                <span style="color:#8d9db5; font-weight:700; font-size:13px;">#<?= $row['cat_id'] ?></span>
              </td>
              <td>
                <?php if ($has_img): ?>
                  <img src="<?= htmlspecialchars($img_src) ?>" class="thumb-img"
                       onerror="this.style.display='none';this.nextElementSibling.style.display='flex';"
                       alt="<?= htmlspecialchars($row['cat_name']) ?>">
                  <div class="thumb-placeholder" style="display:none;">
                    <i class="la la-image" style="color:#bbb;font-size:20px;"></i>
                  </div>
                <?php else: ?>
                  <div class="thumb-placeholder">
                    <i class="la la-image" style="color:#bbb;font-size:20px;"></i>
                  </div>
                <?php endif; ?>
              </td>
              <td>
                <span style="font-weight:700; color:#1a1f36; font-size:14px;">
                  <?= htmlspecialchars($row['cat_name']) ?>
                </span>
              </td>
              <td>
                <span style="font-size:13px; color:#8d9db5; display:flex; align-items:center; gap:5px;">
                  <i class="la la-calendar" style="font-size:15px;"></i>
                  <?= date('d M Y', strtotime($row['created_at'])) ?>
                  <span style="color:#bbb;">·</span>
                  <?= date('h:i A', strtotime($row['created_at'])) ?>
                </span>
              </td>
              <td>
                <?php if ($st == 0): ?>
                  <span style="background:#e8f5e9;color:#2e7d32;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:700;letter-spacing:0.3px;">
                    ● Active
                  </span>
                <?php else: ?>
                  <span style="background:#fdecea;color:#c62828;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:700;letter-spacing:0.3px;">
                    ● Inactive
                  </span>
                <?php endif; ?>
              </td>
              <td style="text-align:right; padding-right:22px;">

                <button class="btn btn-sm btn-action view-btn"
                  style="border:1.5px solid #0288d1; color:#0288d1; margin-right:4px;"
                  data-id="<?= $row['cat_id'] ?>"
                  data-name="<?= htmlspecialchars($row['cat_name'], ENT_QUOTES) ?>"
                  data-img="<?= $has_img ? htmlspecialchars($img_src, ENT_QUOTES) : '' ?>"
                  data-date="<?= date('d M Y, h:i A', strtotime($row['created_at'])) ?>"
                  data-status="<?= $st ?>"
                  title="View">
                  <i class="la la-eye"></i>
                </button>

                <button class="btn btn-sm btn-action edit-btn"
                  style="border:1.5px solid #4361ee; color:#4361ee; margin-right:4px;"
                  data-id="<?= $row['cat_id'] ?>"
                  data-name="<?= htmlspecialchars($row['cat_name'], ENT_QUOTES) ?>"
                  data-img="<?= $has_img ? htmlspecialchars($img_src, ENT_QUOTES) : '' ?>"
                  data-oldimg="<?= htmlspecialchars($row['cat_img'] ?? '', ENT_QUOTES) ?>"
                  title="Edit">
                  <i class="la la-edit"></i> Edit
                </button>

                <button class="btn btn-sm btn-action delete-btn"
                  style="border:1.5px solid #e53935; color:#e53935;"
                  data-id="<?= $row['cat_id'] ?>"
                  data-name="<?= htmlspecialchars($row['cat_name'], ENT_QUOTES) ?>"
                  title="Delete">
                  <i class="la la-trash"></i>
                </button>

              </td>
            </tr>
            <?php endwhile; else: ?>
            <tr>
              <td colspan="6" style="text-align:center; padding:60px 20px; color:#aaa;">
                <i class="la la-inbox" style="font-size:48px; display:block; margin-bottom:10px; color:#d0d5e8;"></i>
                <p style="font-size:15px; color:#b0b8cc; margin:0 0 10px;">No categories found.</p>
                <a href="javascript:void(0)" onclick="openModal('addModal')"
                   style="color:#4361ee; font-weight:600; font-size:13px;">+ Add your first category</a>
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

<!-- ── BACKDROP ───────────────────────────────────── -->
<div id="modalBackdrop" class="modal-overlay" onclick="closeAllModals()"></div>

<!-- ══ ADD MODAL ════════════════════════════════════════ -->
<div id="addModal" class="modal-box-wrap">
  <div style="background:#fff; border-radius:16px; width:100%; max-width:520px;
              margin:auto; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,0.18);">

    <!-- Header -->
    <div style="background:linear-gradient(135deg,#4361ee,#6c8fff); padding:20px 24px;
                display:flex; align-items:center; justify-content:space-between;">
      <div style="display:flex; align-items:center; gap:10px;">
        <div style="width:36px;height:36px;background:rgba(255,255,255,0.2);border-radius:9px;
                    display:flex;align-items:center;justify-content:center;">
          <i class="la la-plus-circle" style="color:#fff; font-size:20px;"></i>
        </div>
        <div>
          <h5 style="color:#fff; font-weight:800; margin:0; font-size:16px;">Add New Category</h5>
          <p style="color:rgba(255,255,255,0.7); margin:0; font-size:12px;">Fill in the details below</p>
        </div>
      </div>
      <button onclick="closeAllModals()"
              style="background:rgba(255,255,255,0.15);border:none;width:32px;height:32px;
                     border-radius:8px;cursor:pointer;color:#fff;font-size:18px;
                     display:flex;align-items:center;justify-content:center;
                     transition:background 0.2s;"
              onmouseover="this.style.background='rgba(255,255,255,0.25)'"
              onmouseout="this.style.background='rgba(255,255,255,0.15)'">&times;</button>
    </div>

    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="action" value="add">
      <div style="padding:24px;">

        <!-- Category Name -->
        <div style="margin-bottom:20px;">
          <label class="form-label">Category Name <span style="color:#e53935;">*</span></label>
          <input type="text" name="cat_name" class="form-control"
                 placeholder="e.g. Electronics, Clothing, Books..."
                 value="<?= (isset($_POST['action']) && $_POST['action']=='add' && !empty($error)) ? htmlspecialchars($_POST['cat_name']) : '' ?>"
                 required>
        </div>

        <!-- Image Upload -->
        <div>
          <label class="form-label">Category Image <span style="color:#e53935;">*</span></label>
          <div class="upload-zone" id="add_zone"
               onclick="document.getElementById('add_cat_img').click()">
            <div id="add_preview_box" style="display:none; margin-bottom:10px;">
              <img id="add_img_preview" src="#"
                   style="max-height:150px; max-width:240px; border-radius:10px;
                          object-fit:cover; box-shadow:0 4px 14px rgba(0,0,0,0.12);">
            </div>
            <div id="add_placeholder">
              <div style="width:52px;height:52px;background:#f0f4ff;border-radius:12px;
                          display:flex;align-items:center;justify-content:center;margin:0 auto 10px;">
                <i class="la la-cloud-upload" style="font-size:28px; color:#4361ee;"></i>
              </div>
              <p style="color:#4361ee; font-weight:700; margin:0 0 3px; font-size:14px;">Click to upload image</p>
              <p style="color:#aaa; font-size:12px; margin:0;">JPG, PNG, WEBP, GIF · Max 5MB</p>
            </div>
            <input type="file" name="cat_img" id="add_cat_img" accept="image/*"
                   style="display:none;"
                   onchange="previewImg(this,'add_img_preview','add_preview_box','add_placeholder','add_zone')"
                   required>
          </div>
        </div>
      </div>

      <div style="border-top:1px solid #f0f2f8; padding:16px 24px;
                  display:flex; justify-content:flex-end; gap:10px; background:#fafbff;">
        <button type="button" class="btn btn-default" onclick="closeAllModals()"
                style="border:1.5px solid #e0e4ef; color:#5a6080; border-radius:9px;
                       padding:8px 20px; font-weight:600; font-size:13.5px; transition:all 0.2s;">
          Cancel
        </button>
        <button type="submit" class="btn btn-primary" style="font-size:13.5px;">
          <i class="la la-save mr-1"></i> Save Category
        </button>
      </div>
    </form>
  </div>
</div>


<!-- ══ VIEW MODAL ═══════════════════════════════════ -->
<div id="viewModal" class="modal-box-wrap">
  <div style="background:#fff; border-radius:16px; width:100%; max-width:460px;
              margin:auto; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,0.18);">
    <div style="background:linear-gradient(135deg,#1b5e20,#2e7d32); padding:20px 24px;
                display:flex; align-items:center; justify-content:space-between;">
      <div style="display:flex; align-items:center; gap:10px;">
        <div style="width:36px;height:36px;background:rgba(255,255,255,0.2);border-radius:9px;
                    display:flex;align-items:center;justify-content:center;">
          <i class="la la-eye" style="color:#fff; font-size:20px;"></i>
        </div>
        <h5 style="color:#fff; font-weight:800; margin:0; font-size:16px;">Category Details</h5>
      </div>
      <button onclick="closeAllModals()"
              style="background:rgba(255,255,255,0.15);border:none;width:32px;height:32px;
                     border-radius:8px;cursor:pointer;color:#fff;font-size:18px;
                     display:flex;align-items:center;justify-content:center;">&times;</button>
    </div>
    <div style="padding:32px 24px; text-align:center;">
      <div style="position:relative; display:inline-block; margin-bottom:18px;">
        <img id="view_img" src="" alt=""
             style="width:120px;height:120px;object-fit:cover;border-radius:16px;
                    border:3px solid #e8f5e9; box-shadow:0 6px 20px rgba(0,0,0,0.1);"
             onerror="this.style.display='none'; document.getElementById('view_no_img').style.display='flex';">
        <div id="view_no_img"
             style="display:none;width:120px;height:120px;background:#f4f5f7;border-radius:16px;
                    align-items:center;justify-content:center;border:3px solid #e8f5e9; margin:0 auto;">
          <i class="la la-image" style="font-size:40px;color:#bbb;"></i>
        </div>
      </div>
      <h4 id="view_name" style="font-weight:800; color:#1a1f36; margin-bottom:20px; font-size:20px;"></h4>
      <div style="display:flex; justify-content:center; gap:28px; flex-wrap:wrap;">
        <div style="text-align:center; min-width:70px;">
          <p style="font-size:11px; color:#aaa; margin:0 0 4px; text-transform:uppercase; letter-spacing:0.8px; font-weight:700;">ID</p>
          <p id="view_id" style="font-weight:800; margin:0; font-size:18px; color:#4361ee;"></p>
        </div>
        <div style="text-align:center;">
          <p style="font-size:11px; color:#aaa; margin:0 0 4px; text-transform:uppercase; letter-spacing:0.8px; font-weight:700;">Created</p>
          <p id="view_date" style="font-weight:600; margin:0; font-size:13px; color:#444;"></p>
        </div>
        <div style="text-align:center;">
          <p style="font-size:11px; color:#aaa; margin:0 0 4px; text-transform:uppercase; letter-spacing:0.8px; font-weight:700;">Status</p>
          <p id="view_status" style="margin:0;"></p>
        </div>
      </div>
    </div>
    <div style="border-top:1px solid #f0f2f8; padding:16px 24px; display:flex; justify-content:center; background:#fafbff;">
      <button class="btn btn-default" onclick="closeAllModals()"
              style="border:1.5px solid #e0e4ef; color:#5a6080; border-radius:9px;
                     padding:8px 28px; font-weight:600;">Close</button>
    </div>
  </div>
</div>

<!-- ══ EDIT MODAL ═══════════════════════════════════ -->
<div id="editModal" class="modal-box-wrap">
  <div style="background:#fff; border-radius:16px; width:100%; max-width:520px;
              margin:auto; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,0.18);">
    <div style="background:linear-gradient(135deg,#e65100,#ff8c00); padding:20px 24px;
                display:flex; align-items:center; justify-content:space-between;">
      <div style="display:flex; align-items:center; gap:10px;">
        <div style="width:36px;height:36px;background:rgba(255,255,255,0.2);border-radius:9px;
                    display:flex;align-items:center;justify-content:center;">
          <i class="la la-edit" style="color:#fff; font-size:20px;"></i>
        </div>
        <div>
          <h5 style="color:#fff; font-weight:800; margin:0; font-size:16px;">Edit Category</h5>
          <p style="color:rgba(255,255,255,0.75); margin:0; font-size:12px;">Update the category info</p>
        </div>
      </div>
      <button onclick="closeAllModals()"
              style="background:rgba(255,255,255,0.15);border:none;width:32px;height:32px;
                     border-radius:8px;cursor:pointer;color:#fff;font-size:18px;
                     display:flex;align-items:center;justify-content:center;">&times;</button>
    </div>
    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="action"  value="edit">
      <input type="hidden" name="cat_id"  id="edit_cat_id">
      <input type="hidden" name="old_img" id="edit_old_img">
      <div style="padding:24px;">

        <div style="margin-bottom:20px;">
          <label class="form-label">Category Name <span style="color:#e53935;">*</span></label>
          <input type="text" name="cat_name" id="edit_cat_name" class="form-control" required>
        </div>

        <!-- Current image -->
        <div style="margin-bottom:16px;">
          <label class="form-label">Current Image</label>
          <div style="display:flex; align-items:center; gap:14px;">
            <img id="edit_current_img" src="" alt=""
                 style="height:80px;width:80px;object-fit:cover;border-radius:12px;
                        border:2px solid #eef0f8; box-shadow:0 2px 8px rgba(0,0,0,0.08);"
                 onerror="this.style.display='none'; document.getElementById('edit_no_img').style.display='flex';">
            <div id="edit_no_img"
                 style="display:none;width:80px;height:80px;background:#f4f5f7;border-radius:12px;
                        align-items:center;justify-content:center;border:2px solid #eef0f8;">
              <i class="la la-image" style="color:#bbb;font-size:24px;"></i>
            </div>
            <div>
              <p style="font-size:12px;color:#aaa;margin:0;">This is the current image.</p>
              <p style="font-size:12px;color:#aaa;margin:0;">Upload a new one to replace it.</p>
            </div>
          </div>
        </div>

        <!-- New image -->
        <div>
          <label class="form-label">
            New Image
            <span style="font-weight:400; font-size:12px; color:#aaa;">(optional — only if replacing)</span>
          </label>
          <div class="upload-zone" id="edit_zone"
               onclick="document.getElementById('edit_cat_img').click()">
            <div id="edit_preview_box" style="display:none; margin-bottom:8px;">
              <img id="edit_img_preview" src=""
                   style="max-height:120px; border-radius:10px; box-shadow:0 4px 14px rgba(0,0,0,0.1);">
            </div>
            <div id="edit_placeholder">
              <i class="la la-cloud-upload" style="font-size:26px; color:#4361ee;"></i>
              <p style="color:#4361ee; font-weight:600; font-size:13px; margin:4px 0 0;">Click to select new image</p>
            </div>
            <input type="file" name="cat_img" id="edit_cat_img" accept="image/*"
                   style="display:none;"
                   onchange="previewImg(this,'edit_img_preview','edit_preview_box','edit_placeholder','edit_zone')">
          </div>
        </div>
      </div>

      <div style="border-top:1px solid #f0f2f8; padding:16px 24px;
                  display:flex; justify-content:flex-end; gap:10px; background:#fafbff;">
        <button type="button" class="btn btn-default" onclick="closeAllModals()"
                style="border:1.5px solid #e0e4ef; color:#5a6080; border-radius:9px;
                       padding:8px 20px; font-weight:600; font-size:13.5px;">Cancel</button>
        <button type="submit" class="btn btn-warning"
                style="color:#fff; border-radius:9px; font-weight:700; font-size:13.5px;
                       padding:8px 20px; box-shadow:0 4px 12px rgba(230,81,0,0.3);
                       transition:all 0.2s;">
          <i class="la la-save mr-1"></i> Update Category
        </button>
      </div>
    </form>
  </div>
</div>

<!-- ══ DELETE MODAL ══════════════════════════════════ -->
<div id="deleteModal" class="modal-box-wrap">
  <div style="background:#fff; border-radius:16px; width:100%; max-width:420px;
              margin:auto; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,0.18);">
    <div style="background:linear-gradient(135deg,#b71c1c,#e53935); padding:20px 24px;
                display:flex; align-items:center; justify-content:space-between;">
      <div style="display:flex; align-items:center; gap:10px;">
        <div style="width:36px;height:36px;background:rgba(255,255,255,0.2);border-radius:9px;
                    display:flex;align-items:center;justify-content:center;">
          <i class="la la-exclamation-triangle" style="color:#fff; font-size:18px;"></i>
        </div>
        <h5 style="color:#fff; font-weight:800; margin:0; font-size:16px;">Delete Category</h5>
      </div>
      <button onclick="closeAllModals()"
              style="background:rgba(255,255,255,0.15);border:none;width:32px;height:32px;
                     border-radius:8px;cursor:pointer;color:#fff;font-size:18px;
                     display:flex;align-items:center;justify-content:center;">&times;</button>
    </div>
    <div style="padding:32px 24px; text-align:center;">
      <div style="width:72px;height:72px;background:#fdecea;border-radius:50%;
                  display:flex;align-items:center;justify-content:center;margin:0 auto 18px;">
        <i class="la la-trash" style="font-size:36px; color:#e53935;"></i>
      </div>
      <p style="font-size:15px; color:#444; margin:0 0 6px; font-weight:600;">
        Delete "<strong id="delete_name" style="color:#c62828;"></strong>"?
      </p>
      <p style="font-size:13px; color:#aaa; margin:0;">This action cannot be undone.</p>
    </div>
    <div style="border-top:1px solid #f0f2f8; padding:16px 24px;
                display:flex; justify-content:center; gap:12px; background:#fafbff;">
      <button class="btn btn-default" onclick="closeAllModals()"
              style="border:1.5px solid #e0e4ef; color:#5a6080; border-radius:9px;
                     padding:8px 24px; font-weight:600; min-width:110px;">Cancel</button>
      <form method="POST" style="margin:0;">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="cat_id" id="delete_cat_id">
        <button type="submit" class="btn btn-danger"
                style="border-radius:9px; font-weight:700; padding:8px 24px; min-width:110px;
                       box-shadow:0 4px 12px rgba(229,57,53,0.35); transition:all 0.2s;">
          <i class="la la-trash mr-1"></i> Delete
        </button>
      </form>
    </div>
  </div>
</div>

<?php include 'include/footer.php'; ?>
</div>

<script>
// ── Modal Open/Close ─────────────────────────────────────────
function openModal(id) {
    document.getElementById('modalBackdrop').style.display = 'block';
    var m = document.getElementById(id);
    m.classList.add('open');
}

function closeAllModals() {
    ['addModal','viewModal','editModal','deleteModal'].forEach(function(id) {
        document.getElementById(id).classList.remove('open');
    });
    document.getElementById('modalBackdrop').style.display = 'none';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeAllModals();
});

// ── Button clicks via event delegation (apostrophe-safe) ─────
document.querySelectorAll('.view-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        openView(
            this.dataset.id,
            this.dataset.name,
            this.dataset.img,
            this.dataset.date,
            this.dataset.status
        );
    });
});

document.querySelectorAll('.edit-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        openEdit(
            this.dataset.id,
            this.dataset.name,
            this.dataset.img,
            this.dataset.oldimg
        );
    });
});

document.querySelectorAll('.delete-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        openDelete(this.dataset.id, this.dataset.name);
    });
});

// ── Image Preview ────────────────────────────────────────────
function previewImg(input, previewId, boxId, placeholderId, zoneId) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById(previewId).src = e.target.result;
            document.getElementById(boxId).style.display = 'block';
            document.getElementById(placeholderId).style.display = 'none';
            if (zoneId) document.getElementById(zoneId).classList.add('has-image');
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// ── View Modal ───────────────────────────────────────────────
function openView(id, name, imgSrc, date, status) {
    document.getElementById('view_id').textContent   = '#' + id;
    document.getElementById('view_name').textContent = name;
    document.getElementById('view_date').textContent = date;
    document.getElementById('view_status').innerHTML =
        status == 0
        ? '<span style="background:#e8f5e9;color:#2e7d32;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:700;">● Active</span>'
        : '<span style="background:#fdecea;color:#c62828;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:700;">● Inactive</span>';
    var viewImg = document.getElementById('view_img');
    var noImg   = document.getElementById('view_no_img');
    if (imgSrc) {
        viewImg.src = imgSrc;
        viewImg.style.display = 'block';
        noImg.style.display = 'none';
    } else {
        viewImg.style.display = 'none';
        noImg.style.display = 'flex';
    }
    openModal('viewModal');
}

// ── Edit Modal ───────────────────────────────────────────────
function openEdit(id, name, imgSrc, oldImgName) {
    document.getElementById('edit_cat_id').value   = id;
    document.getElementById('edit_cat_name').value = name;
    document.getElementById('edit_old_img').value  = oldImgName;
    document.getElementById('edit_preview_box').style.display  = 'none';
    document.getElementById('edit_placeholder').style.display  = 'block';
    document.getElementById('edit_cat_img').value = '';
    document.getElementById('edit_zone').classList.remove('has-image');
    var curImg = document.getElementById('edit_current_img');
    var noImg  = document.getElementById('edit_no_img');
    if (imgSrc) {
        curImg.src = imgSrc;
        curImg.style.display = 'block';
        noImg.style.display = 'none';
    } else {
        curImg.style.display = 'none';
        noImg.style.display = 'flex';
    }
    openModal('editModal');
}

// ── Delete Modal ─────────────────────────────────────────────
function openDelete(id, name) {
    document.getElementById('delete_name').textContent = name;
    document.getElementById('delete_cat_id').value     = id;
    openModal('deleteModal');
}

// Auto-open on error
<?php if (!empty($error) && isset($_POST['action'])): ?>
openModal('<?= $_POST['action'] === 'add' ? 'addModal' : 'editModal' ?>');
<?php endif; ?>
</script>