<?php
include 'include/conn.php';

$success = '';
$error = '';

// ══════════════════════════════════════════
//  DELETE
// ══════════════════════════════════════════
if (isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = intval($_POST['sub_id']);
    $stmt = $mysqli->prepare("SELECT sub_img FROM subcategory WHERE sub_id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($row && !empty($row['sub_img'])) {
        $p = __DIR__ . '/assets/img/subcategory/' . $row['sub_img'];
        if (file_exists($p))
            unlink($p);
    }
    $del = $mysqli->prepare("DELETE FROM subcategory WHERE sub_id=?");
    $del->bind_param("i", $id);
    $del->execute();
    $del->close();
    header("Location: sub-categories.php?msg=deleted");
    exit;
}

// ══════════════════════════════════════════
//  ADD
// ══════════════════════════════════════════
if (isset($_POST['action']) && $_POST['action'] === 'add') {
    $sub_name = trim($_POST['sub_name']);
    $cat_id = intval($_POST['cat_id']);
    $sub_img = '';

    if (empty($sub_name)) {
        $error = 'Subcategory name is required.';
    }
    if (!$cat_id) {
        $error = 'Please select a parent category.';
    }

    if (empty($error)) {
        if (isset($_FILES['sub_img']) && $_FILES['sub_img']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            $ext = strtolower(pathinfo($_FILES['sub_img']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed)) {
                $error = 'Only JPG, PNG, WEBP, GIF allowed.';
            } else {
                $dir = __DIR__ . '/assets/img/subcategory/';
                if (!is_dir($dir))
                    mkdir($dir, 0777, true);
                $new = time() . '_' . basename($_FILES['sub_img']['name']);
                if (move_uploaded_file($_FILES['sub_img']['tmp_name'], $dir . $new)) {
                    $sub_img = $new;
                } else {
                    $error = 'Image upload failed.';
                }
            }
        } else {
            $error = 'Subcategory image is required.';
        }
    }

    if (empty($error)) {
        $stmt = $mysqli->prepare("INSERT INTO subcategory (cat_id, sub_name, sub_img) VALUES (?,?,?)");
        $stmt->bind_param("iss", $cat_id, $sub_name, $sub_img);
        $stmt->execute();
        $stmt->close();
        header("Location: sub-categories.php?msg=added");
        exit;
    }
}

// ══════════════════════════════════════════
//  EDIT
// ══════════════════════════════════════════
if (isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id = intval($_POST['sub_id']);
    $sub_name = trim($_POST['sub_name']);
    $cat_id = intval($_POST['cat_id']);
    $old_img = $_POST['old_img'];
    $sub_img = $old_img;

    if (empty($sub_name)) {
        $error = 'Subcategory name is required.';
    }
    if (!$cat_id) {
        $error = 'Please select a parent category.';
    }

    if (empty($error) && isset($_FILES['sub_img']) && $_FILES['sub_img']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $ext = strtolower(pathinfo($_FILES['sub_img']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $dir = __DIR__ . '/assets/img/subcategory/';
            if (!is_dir($dir))
                mkdir($dir, 0777, true);
            $new = time() . '_' . basename($_FILES['sub_img']['name']);
            if (move_uploaded_file($_FILES['sub_img']['tmp_name'], $dir . $new)) {
                if (!empty($old_img) && file_exists($dir . $old_img))
                    unlink($dir . $old_img);
                $sub_img = $new;
            } else {
                $error = 'Image upload failed.';
            }
        } else {
            $error = 'Only JPG, PNG, WEBP, GIF allowed.';
        }
    }

    if (empty($error)) {
        $stmt = $mysqli->prepare("UPDATE subcategory SET cat_id=?, sub_name=?, sub_img=? WHERE sub_id=?");
        $stmt->bind_param("issi", $cat_id, $sub_name, $sub_img, $id);
        $stmt->execute();
        $stmt->close();
        header("Location: sub-categories.php?msg=updated");
        exit;
    }
}

// ══════════════════════════════════════════
//  FETCH DATA
// ══════════════════════════════════════════
$result = $mysqli->query(
    "SELECT s.*, c.cat_name
     FROM subcategory s
     LEFT JOIN category c ON s.cat_id = c.cat_id
     ORDER BY s.sub_id DESC"
);

$cat_result = $mysqli->query("SELECT cat_id, cat_name FROM category ORDER BY cat_name ASC");

include 'include/header.php';
?>

<div class="main-panel">
    <div class="content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4 mt-2">
                <div>
                    <h4 class="page-title mb-0">Sub Categories</h4>
                    <p class="text-muted mb-0" style="font-size:13px; margin-top:3px;">Manage all product subcategories
                        in one place</p>
                </div>
                <button class="btn btn-primary btn-sm d-flex align-items-center" onclick="openModal('addModal')"
                    style="gap:6px;">
                    <i class="la la-plus" style="font-size:16px;"></i> Add New Subcategory
                </button>
            </div>

            <!-- Flash Messages -->
            <?php if (isset($_GET['msg'])): ?>
                <?php
                $msgs = [
                    'added' => ['success', '<i class="la la-check-circle mr-1"></i> Subcategory added successfully!'],
                    'updated' => ['success', '<i class="la la-check-circle mr-1"></i> Subcategory updated successfully!'],
                    'deleted' => ['danger', '<i class="la la-trash mr-1"></i> Subcategory deleted successfully!'],
                ];
                $m = $msgs[$_GET['msg']] ?? null;
                ?>
                <?php if ($m): ?>
                    <div class="alert alert-<?= $m[0] ?> alert-dismissible fade show" style="border-radius:10px; border:none; font-weight:600; font-size:13.5px;
                box-shadow:0 4px 14px rgba(0,0,0,0.08); animation: fadeInUp 0.3s ease;">
                        <?= $m[1] ?>
                        <button type="button" onclick="this.closest('.alert').remove()"
                            style="background:none;border:none;float:right;font-size:20px;cursor:pointer;opacity:0.6;line-height:1;">&times;</button>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" style="border-radius:10px; border:none; font-weight:600; font-size:13.5px;
                box-shadow:0 4px 14px rgba(0,0,0,0.08);">
                    <i class="la la-exclamation-circle mr-1"></i> <?= htmlspecialchars($error) ?>
                    <button type="button" onclick="this.closest('.alert').remove()"
                        style="background:none;border:none;float:right;font-size:20px;cursor:pointer;opacity:0.6;line-height:1;">&times;</button>
                </div>
            <?php endif; ?>

            <!-- Table Card -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0" style="display:flex;align-items:center;gap:8px;">
                        <i class="la la-list" style="color:#4361ee;font-size:20px;"></i> All Subcategories
                    </h5>
                    <span class="badge-count"><?= $result->num_rows ?> Subcategories</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th style="padding-left:22px; width:60px;">ID</th>
                                    <th style="width:90px;">Image</th>
                                    <th>Subcategory Name</th>
                                    <th>Parent Category</th>
                                    <th>Created At</th>
                                    <th style="width:200px; text-align:right; padding-right:22px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result->num_rows > 0):
                                    while ($row = $result->fetch_assoc()):
                                        $img_dir = __DIR__ . '/assets/img/subcategory/';
                                        $img_file = !empty($row['sub_img']) ? $row['sub_img'] : '';
                                        $img_src = 'assets/img/subcategory/' . $img_file;
                                        $has_img = !empty($img_file) && file_exists($img_dir . $img_file);
                                        ?>
                                        <tr>
                                            <td style="padding-left:22px;">
                                                <span
                                                    style="color:#8d9db5; font-weight:700; font-size:13px;">#<?= $row['sub_id'] ?></span>
                                            </td>
                                            <td>
                                                <?php if ($has_img): ?>
                                                    <img src="<?= htmlspecialchars($img_src) ?>" class="thumb-img"
                                                        onerror="this.style.display='none';this.nextElementSibling.style.display='flex';"
                                                        alt="<?= htmlspecialchars($row['sub_name']) ?>">
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
                                                    <?= htmlspecialchars($row['sub_name']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span style="background:#eff6ff;color:#1d4ed8;padding:4px 12px;
                             border-radius:20px;font-size:12px;font-weight:600;">
                                                    <?= htmlspecialchars($row['cat_name'] ?? '—') ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span
                                                    style="font-size:13px; color:#8d9db5; display:flex; align-items:center; gap:5px;">
                                                    <i class="la la-calendar" style="font-size:15px;"></i>
                                                    <?= date('d M Y', strtotime($row['created_at'])) ?>
                                                    <span style="color:#bbb;">·</span>
                                                    <?= date('h:i A', strtotime($row['created_at'])) ?>
                                                </span>
                                            </td>
                                            <td style="text-align:right; padding-right:22px;">

                                                <!-- View -->
                                                <button class="btn btn-sm btn-action"
                                                    style="border:1.5px solid #0288d1; color:#0288d1; margin-right:4px;"
                                                    onclick="openView(
                    '<?= $row['sub_id'] ?>',
                    '<?= htmlspecialchars($row['sub_name'], ENT_QUOTES) ?>',
                    '<?= htmlspecialchars($row['cat_name'] ?? '', ENT_QUOTES) ?>',
                    '<?= $has_img ? htmlspecialchars($img_src, ENT_QUOTES) : '' ?>',
                    '<?= date('d M Y, h:i A', strtotime($row['created_at'])) ?>'
                  )" title="View">
                                                    <i class="la la-eye"></i>
                                                </button>

                                                <!-- Edit -->
                                                <button class="btn btn-sm btn-action"
                                                    style="border:1.5px solid #4361ee; color:#4361ee; margin-right:4px;"
                                                    onclick="openEdit(
                    '<?= $row['sub_id'] ?>',
                    '<?= htmlspecialchars($row['sub_name'], ENT_QUOTES) ?>',
                    '<?= $row['cat_id'] ?>',
                    '<?= $has_img ? htmlspecialchars($img_src, ENT_QUOTES) : '' ?>',
                    '<?= htmlspecialchars($row['sub_img'] ?? '', ENT_QUOTES) ?>'
                  )" title="Edit">
                                                    <i class="la la-edit"></i> Edit
                                                </button>

                                                <!-- Delete -->
                                                <button class="btn btn-sm btn-action"
                                                    style="border:1.5px solid #e53935; color:#e53935;"
                                                    onclick="openDelete('<?= $row['sub_id'] ?>', '<?= htmlspecialchars($row['sub_name'], ENT_QUOTES) ?>')"
                                                    title="Delete">
                                                    <i class="la la-trash"></i>
                                                </button>

                                            </td>
                                        </tr>
                                    <?php endwhile; else: ?>
                                    <tr>
                                        <td colspan="6" style="text-align:center; padding:60px 20px; color:#aaa;">
                                            <i class="la la-inbox"
                                                style="font-size:48px; display:block; margin-bottom:10px; color:#d0d5e8;"></i>
                                            <p style="font-size:15px; color:#b0b8cc; margin:0 0 10px;">No subcategories
                                                found.</p>
                                            <a href="javascript:void(0)" onclick="openModal('addModal')"
                                                style="color:#4361ee; font-weight:600; font-size:13px;">+ Add your first
                                                subcategory</a>
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

            <div style="background:linear-gradient(135deg,#4361ee,#6c8fff); padding:20px 24px;
                display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:10px;">
                    <div style="width:36px;height:36px;background:rgba(255,255,255,0.2);border-radius:9px;
                    display:flex;align-items:center;justify-content:center;">
                        <i class="la la-plus-circle" style="color:#fff; font-size:20px;"></i>
                    </div>
                    <div>
                        <h5 style="color:#fff; font-weight:800; margin:0; font-size:16px;">Add New Subcategory</h5>
                        <p style="color:rgba(255,255,255,0.7); margin:0; font-size:12px;">Fill in the details below</p>
                    </div>
                </div>
                <button onclick="closeAllModals()" style="background:rgba(255,255,255,0.15);border:none;width:32px;height:32px;
                     border-radius:8px;cursor:pointer;color:#fff;font-size:18px;
                     display:flex;align-items:center;justify-content:center;
                     transition:background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.25)'"
                    onmouseout="this.style.background='rgba(255,255,255,0.15)'">&times;</button>
            </div>

            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add">
                <div style="padding:24px;">

                    <!-- Parent Category -->
                    <div style="margin-bottom:20px;">
                        <label class="form-label">Parent Category <span style="color:#e53935;">*</span></label>
                        <select name="cat_id" class="form-control" required>
                            <option value="">-- Select Category --</option>
                            <?php
                            $cat_result->data_seek(0);
                            while ($cat = $cat_result->fetch_assoc()):
                                ?>
                                <option value="<?= $cat['cat_id'] ?>" <?= (isset($_POST['action']) && $_POST['action'] == 'add' && !empty($error) && isset($_POST['cat_id']) && $_POST['cat_id'] == $cat['cat_id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['cat_name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <!-- Subcategory Name -->
                    <div style="margin-bottom:20px;">
                        <label class="form-label">Subcategory Name <span style="color:#e53935;">*</span></label>
                        <input type="text" name="sub_name" class="form-control"
                            placeholder="e.g. Notebooks, T-Shirts, Laptops..."
                            value="<?= (isset($_POST['action']) && $_POST['action'] == 'add' && !empty($error)) ? htmlspecialchars($_POST['sub_name']) : '' ?>"
                            required>
                    </div>

                    <!-- Image Upload -->
                    <div>
                        <label class="form-label">Subcategory Image <span style="color:#e53935;">*</span></label>
                        <div class="upload-zone" id="add_zone" onclick="document.getElementById('add_sub_img').click()">
                            <div id="add_preview_box" style="display:none; margin-bottom:10px;">
                                <img id="add_img_preview" src="#" style="max-height:150px; max-width:240px; border-radius:10px;
                          object-fit:cover; box-shadow:0 4px 14px rgba(0,0,0,0.12);">
                            </div>
                            <div id="add_placeholder">
                                <div style="width:52px;height:52px;background:#f0f4ff;border-radius:12px;
                          display:flex;align-items:center;justify-content:center;margin:0 auto 10px;">
                                    <i class="la la-cloud-upload" style="font-size:28px; color:#4361ee;"></i>
                                </div>
                                <p style="color:#4361ee; font-weight:700; margin:0 0 3px; font-size:14px;">Click to
                                    upload image</p>
                                <p style="color:#aaa; font-size:12px; margin:0;">JPG, PNG, WEBP, GIF · Max 5MB</p>
                            </div>
                            <input type="file" name="sub_img" id="add_sub_img" accept="image/*" style="display:none;"
                                onchange="previewImg(this,'add_img_preview','add_preview_box','add_placeholder','add_zone')"
                                required>
                        </div>
                    </div>

                </div>

                <div style="border-top:1px solid #f0f2f8; padding:16px 24px;
                  display:flex; justify-content:flex-end; gap:10px; background:#fafbff;">
                    <button type="button" class="btn btn-default" onclick="closeAllModals()" style="border:1.5px solid #e0e4ef; color:#5a6080; border-radius:9px;
                       padding:8px 20px; font-weight:600; font-size:13.5px;">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="font-size:13.5px;">
                        <i class="la la-save mr-1"></i> Save Subcategory
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
                    <h5 style="color:#fff; font-weight:800; margin:0; font-size:16px;">Subcategory Details</h5>
                </div>
                <button onclick="closeAllModals()" style="background:rgba(255,255,255,0.15);border:none;width:32px;height:32px;
                     border-radius:8px;cursor:pointer;color:#fff;font-size:18px;
                     display:flex;align-items:center;justify-content:center;">&times;</button>
            </div>
            <div style="padding:32px 24px; text-align:center;">
                <div style="position:relative; display:inline-block; margin-bottom:18px;">
                    <img id="view_img" src="" alt="" style="width:120px;height:120px;object-fit:cover;border-radius:16px;
                    border:3px solid #e8f5e9; box-shadow:0 6px 20px rgba(0,0,0,0.1);"
                        onerror="this.style.display='none'; document.getElementById('view_no_img').style.display='flex';">
                    <div id="view_no_img" style="display:none;width:120px;height:120px;background:#f4f5f7;border-radius:16px;
                    align-items:center;justify-content:center;border:3px solid #e8f5e9; margin:0 auto;">
                        <i class="la la-image" style="font-size:40px;color:#bbb;"></i>
                    </div>
                </div>
                <h4 id="view_name" style="font-weight:800; color:#1a1f36; margin-bottom:10px; font-size:20px;"></h4>
                <div style="margin-bottom:20px;">
                    <span id="view_cat_badge"
                        style="background:#eff6ff;color:#1d4ed8;padding:4px 14px;border-radius:20px;font-size:12px;font-weight:600;"></span>
                </div>
                <div style="display:flex; justify-content:center; gap:28px; flex-wrap:wrap;">
                    <div style="text-align:center; min-width:70px;">
                        <p
                            style="font-size:11px; color:#aaa; margin:0 0 4px; text-transform:uppercase; letter-spacing:0.8px; font-weight:700;">
                            ID</p>
                        <p id="view_id" style="font-weight:800; margin:0; font-size:18px; color:#4361ee;"></p>
                    </div>
                    <div style="text-align:center;">
                        <p
                            style="font-size:11px; color:#aaa; margin:0 0 4px; text-transform:uppercase; letter-spacing:0.8px; font-weight:700;">
                            Created</p>
                        <p id="view_date" style="font-weight:600; margin:0; font-size:13px; color:#444;"></p>
                    </div>
                </div>
            </div>
            <div
                style="border-top:1px solid #f0f2f8; padding:16px 24px; display:flex; justify-content:center; background:#fafbff;">
                <button class="btn btn-default" onclick="closeAllModals()" style="border:1.5px solid #e0e4ef; color:#5a6080; border-radius:9px;
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
                        <h5 style="color:#fff; font-weight:800; margin:0; font-size:16px;">Edit Subcategory</h5>
                        <p style="color:rgba(255,255,255,0.75); margin:0; font-size:12px;">Update the subcategory info
                        </p>
                    </div>
                </div>
                <button onclick="closeAllModals()" style="background:rgba(255,255,255,0.15);border:none;width:32px;height:32px;
                     border-radius:8px;cursor:pointer;color:#fff;font-size:18px;
                     display:flex;align-items:center;justify-content:center;">&times;</button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="sub_id" id="edit_sub_id">
                <input type="hidden" name="old_img" id="edit_old_img">
                <div style="padding:24px;">

                    <!-- Parent Category -->
                    <div style="margin-bottom:20px;">
                        <label class="form-label">Parent Category <span style="color:#e53935;">*</span></label>
                        <select name="cat_id" id="edit_cat_id" class="form-control" required>
                            <option value="">-- Select Category --</option>
                            <?php
                            $cat_result->data_seek(0);
                            while ($cat = $cat_result->fetch_assoc()):
                                ?>
                                <option value="<?= $cat['cat_id'] ?>"><?= htmlspecialchars($cat['cat_name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <!-- Subcategory Name -->
                    <div style="margin-bottom:20px;">
                        <label class="form-label">Subcategory Name <span style="color:#e53935;">*</span></label>
                        <input type="text" name="sub_name" id="edit_sub_name" class="form-control" required>
                    </div>

                    <!-- Current Image -->
                    <div style="margin-bottom:16px;">
                        <label class="form-label">Current Image</label>
                        <div style="display:flex; align-items:center; gap:14px;">
                            <img id="edit_current_img" src="" alt="" style="height:80px;width:80px;object-fit:cover;border-radius:12px;
                        border:2px solid #eef0f8; box-shadow:0 2px 8px rgba(0,0,0,0.08);"
                                onerror="this.style.display='none'; document.getElementById('edit_no_img').style.display='flex';">
                            <div id="edit_no_img" style="display:none;width:80px;height:80px;background:#f4f5f7;border-radius:12px;
                        align-items:center;justify-content:center;border:2px solid #eef0f8;">
                                <i class="la la-image" style="color:#bbb;font-size:24px;"></i>
                            </div>
                            <div>
                                <p style="font-size:12px;color:#aaa;margin:0;">This is the current image.</p>
                                <p style="font-size:12px;color:#aaa;margin:0;">Upload a new one to replace it.</p>
                            </div>
                        </div>
                    </div>

                    <!-- New Image -->
                    <div>
                        <label class="form-label">
                            New Image
                            <span style="font-weight:400; font-size:12px; color:#aaa;">(optional — only if
                                replacing)</span>
                        </label>
                        <div class="upload-zone" id="edit_zone"
                            onclick="document.getElementById('edit_sub_img').click()">
                            <div id="edit_preview_box" style="display:none; margin-bottom:8px;">
                                <img id="edit_img_preview" src=""
                                    style="max-height:120px; border-radius:10px; box-shadow:0 4px 14px rgba(0,0,0,0.1);">
                            </div>
                            <div id="edit_placeholder">
                                <i class="la la-cloud-upload" style="font-size:26px; color:#4361ee;"></i>
                                <p style="color:#4361ee; font-weight:600; font-size:13px; margin:4px 0 0;">Click to
                                    select new image</p>
                            </div>
                            <input type="file" name="sub_img" id="edit_sub_img" accept="image/*" style="display:none;"
                                onchange="previewImg(this,'edit_img_preview','edit_preview_box','edit_placeholder','edit_zone')">
                        </div>
                    </div>

                </div>

                <div style="border-top:1px solid #f0f2f8; padding:16px 24px;
                  display:flex; justify-content:flex-end; gap:10px; background:#fafbff;">
                    <button type="button" class="btn btn-default" onclick="closeAllModals()" style="border:1.5px solid #e0e4ef; color:#5a6080; border-radius:9px;
                       padding:8px 20px; font-weight:600; font-size:13.5px;">Cancel</button>
                    <button type="submit" class="btn btn-warning" style="color:#fff; border-radius:9px; font-weight:700; font-size:13.5px;
                       padding:8px 20px; box-shadow:0 4px 12px rgba(230,81,0,0.3);
                       transition:all 0.2s;">
                        <i class="la la-save mr-1"></i> Update Subcategory
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
                    <h5 style="color:#fff; font-weight:800; margin:0; font-size:16px;">Delete Subcategory</h5>
                </div>
                <button onclick="closeAllModals()" style="background:rgba(255,255,255,0.15);border:none;width:32px;height:32px;
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
                <button class="btn btn-default" onclick="closeAllModals()" style="border:1.5px solid #e0e4ef; color:#5a6080; border-radius:9px;
                     padding:8px 24px; font-weight:600; min-width:110px;">Cancel</button>
                <form method="POST" style="margin:0;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="sub_id" id="delete_sub_id">
                    <button type="submit" class="btn btn-danger" style="border-radius:9px; font-weight:700; padding:8px 24px; min-width:110px;
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
        document.getElementById(id).classList.add('open');
    }

    function closeAllModals() {
        ['addModal', 'viewModal', 'editModal', 'deleteModal'].forEach(function (id) {
            document.getElementById(id).classList.remove('open');
        });
        document.getElementById('modalBackdrop').style.display = 'none';
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeAllModals();
    });

    // ── Image Preview ────────────────────────────────────────────
    function previewImg(input, previewId, boxId, placeholderId, zoneId) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById(previewId).src = e.target.result;
                document.getElementById(boxId).style.display = 'block';
                document.getElementById(placeholderId).style.display = 'none';
                if (zoneId) document.getElementById(zoneId).classList.add('has-image');
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // ── View Modal ───────────────────────────────────────────────
    function openView(id, name, catName, imgSrc, date) {
        document.getElementById('view_id').textContent = '#' + id;
        document.getElementById('view_name').textContent = name;
        document.getElementById('view_cat_badge').textContent = catName;
        document.getElementById('view_date').textContent = date;
        var viewImg = document.getElementById('view_img');
        var noImg = document.getElementById('view_no_img');
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
    function openEdit(id, name, catId, imgSrc, oldImgName) {
        document.getElementById('edit_sub_id').value = id;
        document.getElementById('edit_sub_name').value = name;
        document.getElementById('edit_old_img').value = oldImgName;

        // Set selected category in dropdown
        var sel = document.getElementById('edit_cat_id');
        for (var i = 0; i < sel.options.length; i++) {
            sel.options[i].selected = (sel.options[i].value == catId);
        }

        // Reset new-image upload area
        document.getElementById('edit_preview_box').style.display = 'none';
        document.getElementById('edit_placeholder').style.display = 'block';
        document.getElementById('edit_sub_img').value = '';
        document.getElementById('edit_zone').classList.remove('has-image');

        var curImg = document.getElementById('edit_current_img');
        var noImg = document.getElementById('edit_no_img');
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
        document.getElementById('delete_sub_id').value = id;
        openModal('deleteModal');
    }

    // Auto-open modal on validation error
    <?php if (!empty($error) && isset($_POST['action'])): ?>
        openModal('<?= $_POST['action'] === 'add' ? 'addModal' : 'editModal' ?>');
    <?php endif; ?>
</script>