<?php
session_start();
include 'include/conn.php';

// ── AJAX: Add Vendor ─────────────────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'add') {
    $vendor_name  = trim($_POST['vendor_name']);
    $vendor_email = trim($_POST['vendor_email']);
    $mobile_no    = trim($_POST['mobile_no']);
    $address      = trim($_POST['address']);
    $state        = trim($_POST['state']);
    $city         = trim($_POST['city']);
    $area         = trim($_POST['area']);
    $password     = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
    $status       = intval($_POST['status']);

    $check = $mysqli->prepare("SELECT vendor_id FROM vendor WHERE vendor_email = ?");
    $check->bind_param("s", $vendor_email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Yeh email pehle se registered hai!']);
    } else {
        $stmt = $mysqli->prepare("INSERT INTO vendor (vendor_name, vendor_email, mobile_no, address, state, city, area, password, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->bind_param("ssssssssi", $vendor_name, $vendor_email, $mobile_no, $address, $state, $city, $area, $password, $status);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Vendor successfully add ho gaya!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $mysqli->error]);
        }
        $stmt->close();
    }
    $check->close();
    exit;
}

// ── AJAX: Edit Vendor ────────────────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id           = intval($_POST['vendor_id']);
    $vendor_name  = trim($_POST['vendor_name']);
    $vendor_email = trim($_POST['vendor_email']);
    $mobile_no    = trim($_POST['mobile_no']);
    $address      = trim($_POST['address']);
    $state        = trim($_POST['state']);
    $city         = trim($_POST['city']);
    $area         = trim($_POST['area']);
    $status       = intval($_POST['status']);

    $check = $mysqli->prepare("SELECT vendor_id FROM vendor WHERE vendor_email = ? AND vendor_id != ?");
    $check->bind_param("si", $vendor_email, $id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Yeh email kisi aur vendor ke paas registered hai!']);
    } else {
        $new_password = trim($_POST['new_password']);
        if (!empty($new_password)) {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $mysqli->prepare("UPDATE vendor SET vendor_name=?, vendor_email=?, mobile_no=?, address=?, state=?, city=?, area=?, password=?, status=?, updated_at=NOW() WHERE vendor_id=?");
            $stmt->bind_param("ssssssssii", $vendor_name, $vendor_email, $mobile_no, $address, $state, $city, $area, $hashed, $status, $id);
        } else {
            $stmt = $mysqli->prepare("UPDATE vendor SET vendor_name=?, vendor_email=?, mobile_no=?, address=?, state=?, city=?, area=?, status=?, updated_at=NOW() WHERE vendor_id=?");
            $stmt->bind_param("sssssssii", $vendor_name, $vendor_email, $mobile_no, $address, $state, $city, $area, $status, $id);
        }
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Vendor successfully update ho gaya!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $mysqli->error]);
        }
        $stmt->close();
    }
    $check->close();
    exit;
}

// ── AJAX: Delete Vendor ──────────────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id  = intval($_POST['vendor_id']);
    $del = $mysqli->prepare("DELETE FROM vendor WHERE vendor_id = ?");
    $del->bind_param("i", $id);
    if ($del->execute()) {
        echo json_encode(['success' => true, 'message' => 'Vendor delete ho gaya!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Delete failed!']);
    }
    $del->close();
    exit;
}

// ── AJAX: Toggle Status ──────────────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'toggle_status') {
    $id  = intval($_POST['vendor_id']);
    $res = $mysqli->query("SELECT status FROM vendor WHERE vendor_id = $id");
    $row = $res->fetch_assoc();
    $new = $row['status'] == 1 ? 0 : 1;
    $mysqli->query("UPDATE vendor SET status = $new, updated_at = NOW() WHERE vendor_id = $id");
    echo json_encode(['success' => true, 'new_status' => $new]);
    exit;
}

// ── AJAX: Get Single Vendor ──────────────────────────────────
if (isset($_GET['action']) && $_GET['action'] === 'get' && isset($_GET['id'])) {
    $id  = intval($_GET['id']);
    $res = $mysqli->query("SELECT * FROM vendor WHERE vendor_id = $id");
    $row = $res->fetch_assoc();
    echo json_encode($row);
    exit;
}

// ── AJAX: Search / List Vendors ──────────────────────────────
if (isset($_GET['action']) && $_GET['action'] === 'list') {
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $where  = '';
    if ($search !== '') {
        $s     = $mysqli->real_escape_string($search);
        $where = "WHERE vendor_name LIKE '%$s%' OR vendor_email LIKE '%$s%' OR mobile_no LIKE '%$s%' OR city LIKE '%$s%'";
    }
    $result  = $mysqli->query("SELECT * FROM vendor $where ORDER BY created_at DESC");
    $vendors = [];
    while ($v = $result->fetch_assoc()) {
        unset($v['password']); // never expose
        $vendors[] = $v;
    }
    echo json_encode(['success' => true, 'data' => $vendors, 'total' => count($vendors)]);
    exit;
}

include 'include/header.php';
?>

<style>
  /* ── Badges ── */
  .badge-active   { background:#e6f9f0; color:#1a9e5f; font-size:11px; padding:4px 10px; border-radius:20px; font-weight:700; }
  .badge-inactive { background:#fdecea; color:#e53935; font-size:11px; padding:4px 10px; border-radius:20px; font-weight:700; }

  /* ── Avatar ── */
  .vendor-avatar {
    width:38px; height:38px; border-radius:50%;
    background:linear-gradient(135deg,#4361ee,#6c8fff);
    color:#fff; display:inline-flex; align-items:center;
    justify-content:center; font-weight:800; font-size:14px;
    flex-shrink:0;
  }

  /* ── Action buttons ── */
  .action-btn {
    width:30px; height:30px; border-radius:7px; border:none;
    display:inline-flex; align-items:center; justify-content:center;
    font-size:14px; cursor:pointer; transition:all .2s;
  }
  .btn-view-v   { background:#eef3ff; color:#4361ee; }
  .btn-view-v:hover    { background:#4361ee; color:#fff; }
  .btn-edit-v   { background:#e8f0fe; color:#4361ee; }
  .btn-edit-v:hover    { background:#4361ee; color:#fff; }
  .btn-delete-v { background:#fdecea; color:#e53935; }
  .btn-delete-v:hover  { background:#e53935; color:#fff; }
  .btn-toggle-v { background:#e6f9f0; color:#1a9e5f; }
  .btn-toggle-v.inactive { background:#fdecea; color:#e53935; }
  .btn-toggle-v:hover  { background:#1a9e5f; color:#fff; }
  .btn-toggle-v.inactive:hover { background:#e53935; color:#fff; }

  /* ── Search ── */
  .search-box .form-control { border-radius:20px 0 0 20px !important; height:36px; font-size:13px; }
  .search-box .btn          { border-radius:0 20px 20px 0 !important; height:36px; }

  /* ── Password toggle ── */
  .pw-wrap { position:relative; }
  .pw-wrap .pw-eye {
    position:absolute; right:10px; top:50%; transform:translateY(-50%);
    cursor:pointer; color:#aaa; font-size:16px; z-index:5;
  }
  .pw-wrap input { padding-right:36px !important; }

  /* ── Toast ── */
  #vendorToast {
    position:fixed; top:20px; right:20px; z-index:9999;
    min-width:280px; border-radius:12px; padding:14px 18px;
    font-size:13.5px; font-weight:600;
    box-shadow:0 8px 24px rgba(0,0,0,0.15);
    display:none; align-items:center; gap:10px;
    animation:slideInRight 0.3s ease;
  }
  @keyframes slideInRight {
    from { opacity:0; transform:translateX(40px); }
    to   { opacity:1; transform:translateX(0); }
  }

  /* ── Spinner ── */
  .spin { animation:spin 0.8s linear infinite; display:inline-block; }
  @keyframes spin { to { transform:rotate(360deg); } }

  /* ── Grid form ── */
  .row-2col { display:grid; grid-template-columns:1fr 1fr; gap:10px 16px; }
  .row-3col { display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px 16px; }
  .compact-form .form-group { margin-bottom:12px; }
  .compact-form label { font-size:13px; margin-bottom:3px; font-weight:600; color:#3a4158; display:block; }

  /* ── View modal info grid ── */
  .info-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-top:16px; }
  .info-item { background:#f8f9fc; border-radius:10px; padding:10px 14px; }
  .info-item .label { font-size:10px; font-weight:800; color:#aaa; text-transform:uppercase; letter-spacing:0.8px; margin-bottom:3px; }
  .info-item .value { font-size:13px; font-weight:600; color:#1a1f36; }

  /* ── Empty state ── */
  .empty-state { text-align:center; padding:60px 20px; color:#aaa; }
  .empty-state i { font-size:52px; color:#d0d5e8; display:block; margin-bottom:12px; }
</style>

<!-- Toast -->
<div id="vendorToast"></div>

<div class="main-panel">
  <div class="content">
    <div class="container-fluid">

      <!-- Page Header -->
      <div class="d-flex justify-content-between align-items-center mb-3 mt-2">
        <div>
          <h4 class="page-title mb-0">Vendors</h4>
          <p class="text-muted mb-0" style="font-size:12px;">Sabhi vendors manage karo ek jagah</p>
        </div>
        <button class="btn btn-primary btn-sm" onclick="openModal('addModal')" style="gap:6px;">
          <i class="la la-plus"></i> Add New Vendor
        </button>
      </div>

      <!-- Table Card -->
      <div class="card">
        <div class="card-header py-2 d-flex justify-content-between align-items-center">
          <h5 class="card-title mb-0" style="font-size:15px;">
            <i class="la la-users mr-1" style="color:#4361ee;"></i> All Vendors
            <span id="vendorCount" class="badge-count" style="font-size:11px; margin-left:8px; padding:4px 10px;">0</span>
          </h5>
          <!-- Search -->
          <div class="d-flex search-box" style="gap:6px;">
            <div class="input-group" style="max-width:300px;">
              <input type="text" id="searchInput" class="form-control" placeholder="Search vendor..." />
              <div class="input-group-append">
                <button class="btn btn-primary btn-sm" onclick="loadVendors()">
                  <i class="la la-search"></i>
                </button>
              </div>
            </div>
            <button class="btn btn-default btn-sm" onclick="document.getElementById('searchInput').value=''; loadVendors();" title="Clear" style="border-radius:20px!important; height:36px; border:1px solid #dee2e6;">
              <i class="la la-times"></i>
            </button>
          </div>
        </div>

        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table mb-0">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Vendor</th>
                  <th>Email</th>
                  <th>Mobile</th>
                  <th>City / State</th>
                  <th>Status</th>
                  <th>Joined</th>
                  <th style="text-align:center;">Actions</th>
                </tr>
              </thead>
              <tbody id="vendorTableBody">
                <tr><td colspan="8" class="text-center py-4"><i class="la la-spin la-spinner spin" style="font-size:24px;color:#4361ee;"></i></td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════════
     BACKDROP
════════════════════════════════════════════════════════════ -->
<div id="modalBackdrop" class="modal-overlay" onclick="closeAllModals()"></div>

<!-- ═══════════════════════════════════════════════════════════
     ADD MODAL
════════════════════════════════════════════════════════════ -->
<div id="addModal" class="modal-box-wrap">
  <div style="background:#fff; border-radius:16px; width:100%; max-width:580px; margin:auto; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,0.18);">

    <!-- Header -->
    <div style="background:linear-gradient(135deg,#4361ee,#6c8fff); padding:18px 24px; display:flex; align-items:center; justify-content:space-between;">
      <div style="display:flex; align-items:center; gap:10px;">
        <div style="width:36px;height:36px;background:rgba(255,255,255,0.2);border-radius:9px;display:flex;align-items:center;justify-content:center;">
          <i class="la la-user-plus" style="color:#fff;font-size:20px;"></i>
        </div>
        <div>
          <h5 style="color:#fff;font-weight:800;margin:0;font-size:16px;">Add New Vendor</h5>
          <p style="color:rgba(255,255,255,0.7);margin:0;font-size:12px;">Naya vendor register karo</p>
        </div>
      </div>
      <button onclick="closeAllModals()" style="background:rgba(255,255,255,0.15);border:none;width:32px;height:32px;border-radius:8px;cursor:pointer;color:#fff;font-size:18px;display:flex;align-items:center;justify-content:center;">&times;</button>
    </div>

    <div style="padding:22px 24px;" class="compact-form">
      <div class="row-2col">
        <div class="form-group">
          <label>Vendor Name <span style="color:#e53935;">*</span></label>
          <input type="text" id="add_vendor_name" class="form-control" placeholder="Vendor ka naam" />
        </div>
        <div class="form-group">
          <label>Email Address <span style="color:#e53935;">*</span></label>
          <input type="email" id="add_vendor_email" class="form-control" placeholder="vendor@example.com" />
        </div>
      </div>
      <div class="row-2col">
        <div class="form-group">
          <label>Mobile No. <span style="color:#e53935;">*</span></label>
          <input type="text" id="add_mobile_no" class="form-control" placeholder="9876543210" maxlength="15" />
        </div>
        <div class="form-group">
          <label>Password <span style="color:#e53935;">*</span></label>
          <div class="pw-wrap">
            <input type="password" id="add_password" class="form-control" placeholder="Password set karo" />
            <i class="la la-eye pw-eye" onclick="togglePw('add_password', this)"></i>
          </div>
        </div>
      </div>
      <div class="row-3col">
        <div class="form-group">
          <label>State</label>
          <input type="text" id="add_state" class="form-control" placeholder="Rajasthan" />
        </div>
        <div class="form-group">
          <label>City</label>
          <input type="text" id="add_city" class="form-control" placeholder="Jaipur" />
        </div>
        <div class="form-group">
          <label>Area</label>
          <input type="text" id="add_area" class="form-control" placeholder="Malviya Nagar" />
        </div>
      </div>
      <div class="form-group">
        <label>Full Address</label>
        <textarea id="add_address" class="form-control" rows="2" placeholder="Poora address..."></textarea>
      </div>
      <div class="form-group" style="max-width:220px;">
        <label>Status</label>
        <select id="add_status" class="form-control">
          <option value="1">Active</option>
          <option value="0">Inactive</option>
        </select>
      </div>
    </div>

    <div style="border-top:1px solid #f0f2f8; padding:14px 24px; display:flex; justify-content:flex-end; gap:10px; background:#fafbff;">
      <button class="btn btn-default btn-sm" onclick="closeAllModals()">Cancel</button>
      <button class="btn btn-primary btn-sm" id="addSaveBtn" onclick="submitAdd()">
        <i class="la la-save"></i> Save Vendor
      </button>
    </div>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════════
     VIEW MODAL
════════════════════════════════════════════════════════════ -->
<div id="viewModal" class="modal-box-wrap">
  <div style="background:#fff; border-radius:16px; width:100%; max-width:500px; margin:auto; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,0.18);">

    <div style="background:linear-gradient(135deg,#1b5e20,#2e7d32); padding:18px 24px; display:flex; align-items:center; justify-content:space-between;">
      <div style="display:flex; align-items:center; gap:10px;">
        <div style="width:36px;height:36px;background:rgba(255,255,255,0.2);border-radius:9px;display:flex;align-items:center;justify-content:center;">
          <i class="la la-eye" style="color:#fff;font-size:20px;"></i>
        </div>
        <h5 style="color:#fff;font-weight:800;margin:0;font-size:16px;">Vendor Details</h5>
      </div>
      <button onclick="closeAllModals()" style="background:rgba(255,255,255,0.15);border:none;width:32px;height:32px;border-radius:8px;cursor:pointer;color:#fff;font-size:18px;display:flex;align-items:center;justify-content:center;">&times;</button>
    </div>

    <div style="padding:24px;">
      <!-- Avatar + name -->
      <div style="display:flex; align-items:center; gap:14px; margin-bottom:16px;">
        <div id="view_avatar" class="vendor-avatar" style="width:52px;height:52px;font-size:20px;">V</div>
        <div>
          <div id="view_name" style="font-size:17px;font-weight:800;color:#1a1f36;"></div>
          <div id="view_status_badge" style="margin-top:4px;"></div>
        </div>
      </div>
      <div class="info-grid">
        <div class="info-item"><div class="label">Email</div><div class="value" id="view_email">—</div></div>
        <div class="info-item"><div class="label">Mobile</div><div class="value" id="view_mobile">—</div></div>
        <div class="info-item"><div class="label">City</div><div class="value" id="view_city">—</div></div>
        <div class="info-item"><div class="label">State</div><div class="value" id="view_state">—</div></div>
        <div class="info-item"><div class="label">Area</div><div class="value" id="view_area">—</div></div>
        <div class="info-item"><div class="label">Joined</div><div class="value" id="view_joined">—</div></div>
        <div class="info-item" style="grid-column:span 2;"><div class="label">Address</div><div class="value" id="view_address">—</div></div>
      </div>
    </div>

    <div style="border-top:1px solid #f0f2f8; padding:14px 24px; display:flex; justify-content:center; background:#fafbff;">
      <button class="btn btn-default btn-sm" onclick="closeAllModals()" style="padding:7px 28px;">Close</button>
    </div>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════════
     EDIT MODAL
════════════════════════════════════════════════════════════ -->
<div id="editModal" class="modal-box-wrap">
  <div style="background:#fff; border-radius:16px; width:100%; max-width:580px; margin:auto; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,0.18);">

    <div style="background:linear-gradient(135deg,#e65100,#ff8c00); padding:18px 24px; display:flex; align-items:center; justify-content:space-between;">
      <div style="display:flex; align-items:center; gap:10px;">
        <div style="width:36px;height:36px;background:rgba(255,255,255,0.2);border-radius:9px;display:flex;align-items:center;justify-content:center;">
          <i class="la la-edit" style="color:#fff;font-size:20px;"></i>
        </div>
        <div>
          <h5 style="color:#fff;font-weight:800;margin:0;font-size:16px;">Edit Vendor</h5>
          <p style="color:rgba(255,255,255,0.75);margin:0;font-size:12px;">Vendor details update karo</p>
        </div>
      </div>
      <button onclick="closeAllModals()" style="background:rgba(255,255,255,0.15);border:none;width:32px;height:32px;border-radius:8px;cursor:pointer;color:#fff;font-size:18px;display:flex;align-items:center;justify-content:center;">&times;</button>
    </div>

    <div style="padding:22px 24px;" class="compact-form">
      <input type="hidden" id="edit_vendor_id" />
      <div class="row-2col">
        <div class="form-group">
          <label>Vendor Name <span style="color:#e53935;">*</span></label>
          <input type="text" id="edit_vendor_name" class="form-control" />
        </div>
        <div class="form-group">
          <label>Email Address <span style="color:#e53935;">*</span></label>
          <input type="email" id="edit_vendor_email" class="form-control" />
        </div>
      </div>
      <div class="row-2col">
        <div class="form-group">
          <label>Mobile No. <span style="color:#e53935;">*</span></label>
          <input type="text" id="edit_mobile_no" class="form-control" maxlength="15" />
        </div>
        <div class="form-group">
          <label>New Password <small style="color:#aaa;font-weight:400;">(khali chhodo agar nahi badalna)</small></label>
          <div class="pw-wrap">
            <input type="password" id="edit_new_password" class="form-control" placeholder="New password (optional)" />
            <i class="la la-eye pw-eye" onclick="togglePw('edit_new_password', this)"></i>
          </div>
        </div>
      </div>
      <div class="row-3col">
        <div class="form-group">
          <label>State</label>
          <input type="text" id="edit_state" class="form-control" placeholder="Rajasthan" />
        </div>
        <div class="form-group">
          <label>City</label>
          <input type="text" id="edit_city" class="form-control" placeholder="Jaipur" />
        </div>
        <div class="form-group">
          <label>Area</label>
          <input type="text" id="edit_area" class="form-control" placeholder="Malviya Nagar" />
        </div>
      </div>
      <div class="form-group">
        <label>Full Address</label>
        <textarea id="edit_address" class="form-control" rows="2"></textarea>
      </div>
      <div class="form-group" style="max-width:220px;">
        <label>Status</label>
        <select id="edit_status" class="form-control">
          <option value="1">Active</option>
          <option value="0">Inactive</option>
        </select>
      </div>
    </div>

    <div style="border-top:1px solid #f0f2f8; padding:14px 24px; display:flex; justify-content:flex-end; gap:10px; background:#fafbff;">
      <button class="btn btn-default btn-sm" onclick="closeAllModals()">Cancel</button>
      <button class="btn btn-sm" id="editSaveBtn" onclick="submitEdit()" style="background:linear-gradient(135deg,#e65100,#ff8c00);color:#fff;border:none;border-radius:8px;font-weight:700;padding:7px 18px;box-shadow:0 4px 12px rgba(230,81,0,0.3);">
        <i class="la la-save"></i> Update Vendor
      </button>
    </div>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════════
     DELETE MODAL
════════════════════════════════════════════════════════════ -->
<div id="deleteModal" class="modal-box-wrap">
  <div style="background:#fff; border-radius:16px; width:100%; max-width:400px; margin:auto; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,0.18);">

    <div style="background:linear-gradient(135deg,#b71c1c,#e53935); padding:18px 24px; display:flex; align-items:center; justify-content:space-between;">
      <div style="display:flex; align-items:center; gap:10px;">
        <div style="width:36px;height:36px;background:rgba(255,255,255,0.2);border-radius:9px;display:flex;align-items:center;justify-content:center;">
          <i class="la la-exclamation-triangle" style="color:#fff;font-size:18px;"></i>
        </div>
        <h5 style="color:#fff;font-weight:800;margin:0;font-size:16px;">Delete Vendor</h5>
      </div>
      <button onclick="closeAllModals()" style="background:rgba(255,255,255,0.15);border:none;width:32px;height:32px;border-radius:8px;cursor:pointer;color:#fff;font-size:18px;display:flex;align-items:center;justify-content:center;">&times;</button>
    </div>

    <div style="padding:32px 24px; text-align:center;">
      <div style="width:68px;height:68px;background:#fdecea;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
        <i class="la la-trash" style="font-size:32px;color:#e53935;"></i>
      </div>
      <p style="font-size:15px;color:#444;margin:0 0 6px;font-weight:600;">
        Delete "<strong id="delete_name" style="color:#c62828;"></strong>"?
      </p>
      <p style="font-size:13px;color:#aaa;margin:0;">Yeh action undo nahi ho sakta.</p>
    </div>

    <div style="border-top:1px solid #f0f2f8; padding:14px 24px; display:flex; justify-content:center; gap:12px; background:#fafbff;">
      <button class="btn btn-default btn-sm" onclick="closeAllModals()" style="min-width:100px;">Cancel</button>
      <button class="btn btn-danger btn-sm" id="deleteConfirmBtn" onclick="submitDelete()" style="min-width:100px;font-weight:700;">
        <i class="la la-trash"></i> Delete
      </button>
    </div>
  </div>
</div>

<?php include 'include/footer.php'; ?>

<script>
/* ════════════════════════════════════════════
   STATE
════════════════════════════════════════════ */
var currentDeleteId = null;

/* ════════════════════════════════════════════
   MODAL HELPERS
════════════════════════════════════════════ */
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

/* ════════════════════════════════════════════
   TOAST
════════════════════════════════════════════ */
function showToast(msg, type) {
    var t   = document.getElementById('vendorToast');
    var clr = type === 'success' ? '#e6f9f0' : '#fdecea';
    var ic  = type === 'success' ? '✔' : '✖';
    var tc  = type === 'success' ? '#1a9e5f' : '#e53935';
    t.style.background = clr;
    t.style.color      = tc;
    t.style.border     = '1.5px solid ' + tc;
    t.innerHTML        = '<span style="font-size:18px;">' + ic + '</span> ' + msg;
    t.style.display    = 'flex';
    clearTimeout(t._timer);
    t._timer = setTimeout(function() { t.style.display = 'none'; }, 3200);
}

/* ════════════════════════════════════════════
   PASSWORD TOGGLE
════════════════════════════════════════════ */
function togglePw(id, icon) {
    var inp = document.getElementById(id);
    if (inp.type === 'password') {
        inp.type = 'text';
        icon.classList.replace('la-eye','la-eye-slash');
    } else {
        inp.type = 'password';
        icon.classList.replace('la-eye-slash','la-eye');
    }
}

/* ════════════════════════════════════════════
   LOAD / RENDER VENDORS
════════════════════════════════════════════ */
function loadVendors() {
    var search = document.getElementById('searchInput').value.trim();
    var tbody  = document.getElementById('vendorTableBody');
    tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4"><i class="la la-spinner spin" style="font-size:24px;color:#4361ee;"></i></td></tr>';

    fetch('vendor.php?action=list&search=' + encodeURIComponent(search))
        .then(function(r) { return r.json(); })
        .then(function(res) {
            document.getElementById('vendorCount').textContent = res.total;
            if (res.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8"><div class="empty-state"><i class="la la-users"></i><p style="font-size:15px;color:#b0b8cc;margin:0 0 10px;">Koi vendor nahi mila.</p><button class="btn btn-primary btn-sm" onclick="openModal(\'addModal\')"><i class="la la-plus"></i> Add Vendor</button></div></td></tr>';
                return;
            }
            var html = '';
            res.data.forEach(function(v, i) {
                var initials = v.vendor_name.charAt(0).toUpperCase();
                var city_state = [v.city, v.state].filter(Boolean).join(', ') || '—';
                var joined    = formatDate(v.created_at);
                var isActive  = v.status == 1;
                var statusBadge = isActive
                    ? '<span class="badge-active"><i class="la la-check-circle"></i> Active</span>'
                    : '<span class="badge-inactive"><i class="la la-times-circle"></i> Inactive</span>';
                var toggleTitle = isActive ? 'Deactivate' : 'Activate';
                var toggleIcon  = isActive ? 'la-toggle-on' : 'la-toggle-off';
                var toggleClass = isActive ? '' : 'inactive';

                html += '<tr>' +
                    '<td style="color:#aaa;font-size:12px;">' + (i+1) + '</td>' +
                    '<td><div class="d-flex align-items-center" style="gap:10px;">' +
                        '<div class="vendor-avatar">' + initials + '</div>' +
                        '<div><div style="font-weight:700;font-size:13px;color:#1a1f36;">' + esc(v.vendor_name) + '</div>' +
                        (v.area ? '<div style="font-size:11px;color:#aaa;">' + esc(v.area) + '</div>' : '') +
                        '</div></div></td>' +
                    '<td style="font-size:13px;color:#555;">' + esc(v.vendor_email) + '</td>' +
                    '<td style="font-size:13px;">' + esc(v.mobile_no) + '</td>' +
                    '<td style="font-size:13px;">' + esc(city_state) + '</td>' +
                    '<td>' + statusBadge + '</td>' +
                    '<td style="font-size:12px;color:#aaa;">' + joined + '</td>' +
                    '<td><div class="d-flex justify-content-center" style="gap:5px;">' +
                        '<button class="action-btn btn-view-v" title="View" onclick="openView(' + v.vendor_id + ')"><i class="la la-eye"></i></button>' +
                        '<button class="action-btn btn-toggle-v ' + toggleClass + '" title="' + toggleTitle + '" onclick="toggleStatus(' + v.vendor_id + ', this)"><i class="la ' + toggleIcon + '"></i></button>' +
                        '<button class="action-btn btn-edit-v" title="Edit" onclick="openEdit(' + v.vendor_id + ')"><i class="la la-edit"></i></button>' +
                        '<button class="action-btn btn-delete-v" title="Delete" onclick="openDelete(' + v.vendor_id + ', \'' + escJs(v.vendor_name) + '\')"><i class="la la-trash"></i></button>' +
                    '</div></td>' +
                '</tr>';
            });
            tbody.innerHTML = html;
        })
        .catch(function() {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center py-3 text-danger">Load karne me error aaya.</td></tr>';
        });
}

/* ════════════════════════════════════════════
   VIEW MODAL
════════════════════════════════════════════ */
function openView(id) {
    fetch('vendor.php?action=get&id=' + id)
        .then(function(r) { return r.json(); })
        .then(function(v) {
            document.getElementById('view_avatar').textContent = v.vendor_name.charAt(0).toUpperCase();
            document.getElementById('view_name').textContent   = v.vendor_name;
            document.getElementById('view_email').textContent  = v.vendor_email || '—';
            document.getElementById('view_mobile').textContent = v.mobile_no    || '—';
            document.getElementById('view_city').textContent   = v.city         || '—';
            document.getElementById('view_state').textContent  = v.state        || '—';
            document.getElementById('view_area').textContent   = v.area         || '—';
            document.getElementById('view_address').textContent= v.address      || '—';
            document.getElementById('view_joined').textContent = formatDate(v.created_at);
            document.getElementById('view_status_badge').innerHTML = v.status == 1
                ? '<span class="badge-active"><i class="la la-check-circle"></i> Active</span>'
                : '<span class="badge-inactive"><i class="la la-times-circle"></i> Inactive</span>';
            openModal('viewModal');
        });
}

/* ════════════════════════════════════════════
   EDIT MODAL
════════════════════════════════════════════ */
function openEdit(id) {
    fetch('vendor.php?action=get&id=' + id)
        .then(function(r) { return r.json(); })
        .then(function(v) {
            document.getElementById('edit_vendor_id').value    = v.vendor_id;
            document.getElementById('edit_vendor_name').value  = v.vendor_name;
            document.getElementById('edit_vendor_email').value = v.vendor_email;
            document.getElementById('edit_mobile_no').value    = v.mobile_no;
            document.getElementById('edit_state').value        = v.state   || '';
            document.getElementById('edit_city').value         = v.city    || '';
            document.getElementById('edit_area').value         = v.area    || '';
            document.getElementById('edit_address').value      = v.address || '';
            document.getElementById('edit_status').value       = v.status;
            document.getElementById('edit_new_password').value = '';
            openModal('editModal');
        });
}

/* ════════════════════════════════════════════
   DELETE MODAL
════════════════════════════════════════════ */
function openDelete(id, name) {
    currentDeleteId = id;
    document.getElementById('delete_name').textContent = name;
    openModal('deleteModal');
}

/* ════════════════════════════════════════════
   ADD SUBMIT
════════════════════════════════════════════ */
function submitAdd() {
    var name  = document.getElementById('add_vendor_name').value.trim();
    var email = document.getElementById('add_vendor_email').value.trim();
    var mob   = document.getElementById('add_mobile_no').value.trim();
    var pass  = document.getElementById('add_password').value.trim();

    if (!name || !email || !mob || !pass) {
        showToast('Name, Email, Mobile aur Password required hain!', 'error');
        return;
    }

    var btn = document.getElementById('addSaveBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="la la-spinner spin"></i> Saving...';

    var fd = new FormData();
    fd.append('action',       'add');
    fd.append('vendor_name',  name);
    fd.append('vendor_email', email);
    fd.append('mobile_no',    mob);
    fd.append('password',     pass);
    fd.append('state',        document.getElementById('add_state').value.trim());
    fd.append('city',         document.getElementById('add_city').value.trim());
    fd.append('area',         document.getElementById('add_area').value.trim());
    fd.append('address',      document.getElementById('add_address').value.trim());
    fd.append('status',       document.getElementById('add_status').value);

    fetch('vendor.php', { method:'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res.success) {
                closeAllModals();
                clearAddForm();
                showToast(res.message, 'success');
                loadVendors();
            } else {
                showToast(res.message, 'error');
            }
        })
        .finally(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="la la-save"></i> Save Vendor';
        });
}

function clearAddForm() {
    ['add_vendor_name','add_vendor_email','add_mobile_no','add_password',
     'add_state','add_city','add_area','add_address'].forEach(function(id) {
        document.getElementById(id).value = '';
    });
    document.getElementById('add_status').value = '1';
}

/* ════════════════════════════════════════════
   EDIT SUBMIT
════════════════════════════════════════════ */
function submitEdit() {
    var name  = document.getElementById('edit_vendor_name').value.trim();
    var email = document.getElementById('edit_vendor_email').value.trim();
    var mob   = document.getElementById('edit_mobile_no').value.trim();

    if (!name || !email || !mob) {
        showToast('Name, Email aur Mobile required hain!', 'error');
        return;
    }

    var btn = document.getElementById('editSaveBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="la la-spinner spin"></i> Updating...';

    var fd = new FormData();
    fd.append('action',        'edit');
    fd.append('vendor_id',     document.getElementById('edit_vendor_id').value);
    fd.append('vendor_name',   name);
    fd.append('vendor_email',  email);
    fd.append('mobile_no',     mob);
    fd.append('new_password',  document.getElementById('edit_new_password').value.trim());
    fd.append('state',         document.getElementById('edit_state').value.trim());
    fd.append('city',          document.getElementById('edit_city').value.trim());
    fd.append('area',          document.getElementById('edit_area').value.trim());
    fd.append('address',       document.getElementById('edit_address').value.trim());
    fd.append('status',        document.getElementById('edit_status').value);

    fetch('vendor.php', { method:'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res.success) {
                closeAllModals();
                showToast(res.message, 'success');
                loadVendors();
            } else {
                showToast(res.message, 'error');
            }
        })
        .finally(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="la la-save"></i> Update Vendor';
        });
}

/* ════════════════════════════════════════════
   DELETE SUBMIT
════════════════════════════════════════════ */
function submitDelete() {
    if (!currentDeleteId) return;

    var btn = document.getElementById('deleteConfirmBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="la la-spinner spin"></i> Deleting...';

    var fd = new FormData();
    fd.append('action',    'delete');
    fd.append('vendor_id', currentDeleteId);

    fetch('vendor.php', { method:'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            closeAllModals();
            showToast(res.message, res.success ? 'success' : 'error');
            if (res.success) loadVendors();
        })
        .finally(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="la la-trash"></i> Delete';
            currentDeleteId = null;
        });
}

/* ════════════════════════════════════════════
   TOGGLE STATUS
════════════════════════════════════════════ */
function toggleStatus(id, btn) {
    var fd = new FormData();
    fd.append('action',    'toggle_status');
    fd.append('vendor_id', id);

    fetch('vendor.php', { method:'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res.success) {
                loadVendors();
                showToast(res.new_status == 1 ? 'Vendor activate ho gaya!' : 'Vendor deactivate ho gaya!', 'success');
            }
        });
}

/* ════════════════════════════════════════════
   SEARCH ON ENTER
════════════════════════════════════════════ */
document.getElementById('searchInput').addEventListener('keyup', function(e) {
    if (e.key === 'Enter') loadVendors();
});

/* ════════════════════════════════════════════
   UTILS
════════════════════════════════════════════ */
function esc(str) {
    if (!str) return '—';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
function escJs(str) {
    return String(str || '').replace(/'/g,"\\'").replace(/"/g,'\\"');
}
function formatDate(dt) {
    if (!dt) return '—';
    var d = new Date(dt);
    var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    return d.getDate() + ' ' + months[d.getMonth()] + ' ' + d.getFullYear();
}

/* ════════════════════════════════════════════
   INIT
════════════════════════════════════════════ */
loadVendors();
</script>