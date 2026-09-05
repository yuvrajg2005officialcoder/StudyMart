<?php
session_start();
if (!isset($_SESSION['admin_id'])) { header("Location: login.php"); exit; }
require_once 'include/conn.php';

// ── AJAX Handlers ────────────────────────────────────────────
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');

    // DELETE
    if ($_GET['ajax'] === 'delete' && isset($_GET['id'])) {
        $id = intval($_GET['id']);
        if ($conn->query("DELETE FROM vendor_orders_new WHERE vendor_order_id = $id")) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
        exit;
    }

    // GET SINGLE ORDER (for view/edit modal)
    if ($_GET['ajax'] === 'get' && isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $row = $conn->query("
            SELECT vo.*, v.vendor_name
            FROM vendor_orders_new vo
            LEFT JOIN vendor v ON vo.vendor_id = v.vendor_id
            WHERE vo.vendor_order_id = $id
        ")->fetch_assoc();
        echo json_encode($row ?: []);
        exit;
    }

   // SAVE (add or edit)
    if ($_GET['ajax'] === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id          = intval($_POST['vendor_order_id'] ?? 0);
        $vendor_id   = intval($_POST['vendor_id']);
        $vendor_area = trim($conn->real_escape_string($_POST['vendor_area']));
        $order_qty   = intval($_POST['order_quantity']);
        $order_date  = $conn->real_escape_string($_POST['order_date']);
        $status      = $conn->real_escape_string($_POST['vendor_order_status']);
        $total       = floatval($_POST['total']);
        $pay_status  = $conn->real_escape_string($_POST['payment_status']);
        $pay_mode    = $conn->real_escape_string($_POST['payment_mode']);

        if (!$vendor_id || !$vendor_area || !$order_qty || !$order_date || !$total) {
            echo json_encode(['success' => false, 'error' => 'Sabhi required fields fill karo!']);
            exit;
        }

        if ($id > 0) {
            // ✅ Edit mein ordid update nahi hogi
            $sql = "UPDATE vendor_orders_new SET
                    vendor_id=$vendor_id, vendor_area='$vendor_area',
                    order_quantity=$order_qty, order_date='$order_date',
                    vendor_order_status='$status', total=$total,
                    payment_status='$pay_status', payment_mode='$pay_mode'
                    WHERE vendor_order_id=$id";

            if ($conn->query($sql)) {
                echo json_encode(['success' => true, 'message' => 'Order successfully update ho gaya!']);
            } else {
                echo json_encode(['success' => false, 'error' => $conn->error]);
            }
            exit;

        } else {
            // ✅ Naye order ko pehle insert karo (ordid khali), phir vendor_order_id se ordid banao
            $sql = "INSERT INTO vendor_orders_new
                    (vendor_id, vendor_area, order_quantity, order_date, vendor_order_status, total, payment_status, payment_mode)
                    VALUES ($vendor_id, '$vendor_area', $order_qty, '$order_date', '$status', $total, '$pay_status', '$pay_mode')";

            if ($conn->query($sql)) {
                $newId = $conn->insert_id;                       // auto-increment id jo abhi mila
                $ordid = 'ORD-' . str_pad($newId, 4, '0', STR_PAD_LEFT);

                $conn->query("UPDATE vendor_orders_new SET ordid='$ordid' WHERE vendor_order_id=$newId");

                echo json_encode(['success' => true, 'message' => 'Order successfully add ho gaya!']);
            } else {
                echo json_encode(['success' => false, 'error' => $conn->error]);
            }
            exit;
        }
    }


    // GET VENDOR AREA
    if ($_GET['ajax'] === 'vendor_area' && isset($_GET['vendor_id'])) {
        $vid = intval($_GET['vendor_id']);
        $row = $conn->query("SELECT vendor_area FROM vendor WHERE vendor_id = $vid")->fetch_assoc();
        echo json_encode($row ?: []);
        exit;
    }

    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit;
}

// ── Page Load: Fetch Orders + Vendors ───────────────────────
$where = "1";
$search        = trim($_GET['search'] ?? '');
$filter_status = $_GET['status'] ?? '';
$filter_payment= $_GET['payment'] ?? '';

if ($search) {
    $s = $conn->real_escape_string($search);
    $where .= " AND (v.vendor_name LIKE '%$s%' OR vo.vendor_area LIKE '%$s%')";
}
if ($filter_status)
    $where .= " AND vo.vendor_order_status = '" . $conn->real_escape_string($filter_status) . "'";
if ($filter_payment)
    $where .= " AND vo.payment_status = '" . $conn->real_escape_string($filter_payment) . "'";

$orders  = $conn->query("
    SELECT vo.*, v.vendor_name
    FROM vendor_orders_new vo
    LEFT JOIN vendor v ON vo.vendor_id = v.vendor_id
    WHERE $where
    ORDER BY vo.vendor_order_id DESC
");

$vendors = $conn->query("SELECT vendor_id, vendor_name FROM vendor WHERE vendor_status = 'Active' ORDER BY vendor_name");

$pageTitle = 'Vendor Orders';
include 'include/header.php';
?>

<style>
.badge-pill { font-size:11px; padding:4px 11px; border-radius:20px; font-weight:700; display:inline-block; }
.badge-pending    { background:#fff3cd; color:#856404; }
.badge-processing { background:#cfe2ff; color:#084298; }
.badge-delivered  { background:#e8f5e9; color:#2e7d32; }
.badge-cancelled  { background:#fce4ec; color:#c62828; }
.badge-paid       { background:#e8f5e9; color:#2e7d32; }
.badge-unpaid     { background:#fce4ec; color:#c62828; }
.badge-partial    { background:#fff3e0; color:#e65100; }

.filter-bar {
    background:#fff; border-radius:12px; padding:16px 20px;
    box-shadow:0 1px 8px rgba(0,0,0,0.06); margin-bottom:18px;
}

.modal-overlay {
    display:none; position:fixed; top:0; left:0; width:100%; height:100%;
    background:rgba(15,19,34,0.6); z-index:1040; backdrop-filter:blur(3px);
}
.modal-box-wrap {
    display:none; position:fixed; top:0; left:0; width:100%; height:100%;
    z-index:1050; align-items:center; justify-content:center; overflow-y:auto; padding:20px;
}
.modal-box-wrap.open { display:flex; animation:modalIn 0.28s cubic-bezier(0.34,1.56,0.64,1) both; }
@keyframes modalIn {
    from { opacity:0; transform:scale(0.88) translateY(20px); }
    to   { opacity:1; transform:scale(1) translateY(0); }
}

.modal-inner { background:#fff; border-radius:16px; width:100%; max-width:600px; margin:auto; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,0.18); }
.modal-head  { padding:20px 24px; display:flex; align-items:center; justify-content:space-between; }
.modal-head-icon { width:36px; height:36px; background:rgba(255,255,255,0.2); border-radius:9px; display:flex; align-items:center; justify-content:center; }
.modal-head h5 { color:#fff; font-weight:800; margin:0; font-size:16px; }
.modal-head p  { color:rgba(255,255,255,0.7); margin:0; font-size:12px; }
.modal-close-btn {
    background:rgba(255,255,255,0.15); border:none; width:32px; height:32px;
    border-radius:8px; cursor:pointer; color:#fff; font-size:18px;
    display:flex; align-items:center; justify-content:center; transition:background 0.2s;
}
.modal-close-btn:hover { background:rgba(255,255,255,0.28); }
.modal-body { padding:24px; }
.modal-footer { border-top:1px solid #f0f2f8; padding:16px 24px; display:flex; justify-content:flex-end; gap:10px; background:#fafbff; }

.form-label  { font-weight:600; color:#1a1f36; font-size:13px; margin-bottom:4px; }
.section-label { font-size:11px; font-weight:800; color:#8d9db5; text-transform:uppercase; letter-spacing:1.5px; margin:16px 0 10px; padding-bottom:6px; border-bottom:2px solid #f0f2f8; }

.toast-box {
    position:fixed; top:20px; right:20px; z-index:99999;
    background:#fff; border-radius:12px; padding:14px 20px;
    box-shadow:0 8px 30px rgba(0,0,0,0.15); display:flex; align-items:center; gap:12px;
    min-width:280px; border-left:4px solid #11c26d;
    animation:slideIn 0.3s ease; display:none;
}
.toast-box.show { display:flex; }
.toast-box.error { border-left-color:#e91e63; }
@keyframes slideIn { from { transform:translateX(100px); opacity:0; } to { transform:translateX(0); opacity:1; } }

.detail-row { display:flex; border-bottom:1px solid #f5f6fa; padding:10px 0; align-items:flex-start; gap:12px; }
.detail-row:last-child { border-bottom:none; }
.detail-label { font-size:11px; font-weight:700; color:#8d9db5; text-transform:uppercase; letter-spacing:0.8px; min-width:120px; padding-top:2px; }
.detail-value { font-size:14px; font-weight:600; color:#1a1f36; }
</style>

<!-- Toast -->
<div class="toast-box" id="toastBox">
    <i class="las la-check-circle" style="font-size:22px;color:#11c26d;" id="toastIcon"></i>
    <span id="toastMsg" style="font-weight:600;color:#1a1f36;font-size:13px;"></span>
</div>

<!-- Modal Backdrop -->
<div class="modal-overlay" id="modalBackdrop" onclick="closeAllModals()"></div>

<!-- ADD / EDIT MODAL -->
<div id="saveModal" class="modal-box-wrap">
  <div class="modal-inner">
    <div class="modal-head" id="saveModalHead" style="background:linear-gradient(135deg,#4361ee,#6c8fff);">
      <div style="display:flex;align-items:center;gap:10px;">
        <div class="modal-head-icon"><i class="las la-clipboard-list" style="color:#fff;font-size:20px;" id="saveModalIcon"></i></div>
        <div>
          <h5 id="saveModalTitle">Add New Order</h5>
          <p id="saveModalSub">Fill in the order details below</p>
        </div>
      </div>
      <button class="modal-close-btn" onclick="closeAllModals()">&times;</button>
    </div>

    <form id="saveForm">
      <input type="hidden" name="vendor_order_id" id="f_order_id" value="0">
      <div class="modal-body">

        <div class="section-label"><i class="las la-store mr-1"></i> Vendor Information</div>
        <div class="row">
          <div class="col-md-6 mb-2">
            <label class="form-label">Vendor <span class="text-danger">*</span></label>
            <select name="vendor_id" id="f_vendor_id" class="form-control" required onchange="ajaxFetchVendorArea(this.value)">
              <option value="">-- Vendor Select Karo --</option>
              <?php if ($vendors && $vendors->num_rows > 0):
                while ($v = $vendors->fetch_assoc()): ?>
                <option value="<?= $v['vendor_id'] ?>"><?= htmlspecialchars($v['vendor_name']) ?></option>
              <?php endwhile; endif; ?>
            </select>
          </div>
          <div class="col-md-6 mb-2">
            <label class="form-label">Vendor Area <span class="text-danger">*</span></label>
            <input type="text" name="vendor_area" id="f_vendor_area" class="form-control" placeholder="Auto-fill or enter manually" required>
          </div>
        </div>

        <div class="section-label"><i class="las la-box mr-1"></i> Order Details</div>
        <div class="row">
          <div class="col-md-6 mb-2">
            <label class="form-label">Order Ref ID</label>
            <!-- ✅ readonly - auto generate hogi -->
            <input type="text" name="ordid" id="f_ordid" class="form-control"
                   placeholder="Auto-generate hogi..." readonly
                   style="background:#f5f6fa;color:#8d9db5;cursor:not-allowed;">
          </div>
          <div class="col-md-6 mb-2">
            <label class="form-label">Quantity <span class="text-danger">*</span></label>
            <input type="number" name="order_quantity" id="f_order_qty" class="form-control" min="1" placeholder="e.g. 50" required>
          </div>
          <div class="col-md-6 mb-2">
            <label class="form-label">Order Date <span class="text-danger">*</span></label>
            <input type="date" name="order_date" id="f_order_date" class="form-control" required>
          </div>
        </div>

        <div class="section-label"><i class="las la-money-bill mr-1"></i> Payment & Status</div>
        <div class="row">
          <div class="col-md-3 mb-2">
            <label class="form-label">Total (₹) <span class="text-danger">*</span></label>
            <input type="number" name="total" id="f_total" class="form-control" step="0.01" min="0" placeholder="e.g. 5000" required>
          </div>
          <div class="col-md-3 mb-2">
            <label class="form-label">Payment Status</label>
            <select name="payment_status" id="f_pay_status" class="form-control">
              <option value="Unpaid">Unpaid</option>
              <option value="Paid">Paid</option>
              <option value="Partial">Partial</option>
            </select>
          </div>
          <div class="col-md-3 mb-2">
            <label class="form-label">Payment Mode</label>
            <select name="payment_mode" id="f_pay_mode" class="form-control">
              <option value="Cash">Cash</option>
              <option value="Online">Online</option>
              <option value="Cheque">Cheque</option>
              <option value="UPI">UPI</option>
            </select>
          </div>
          <div class="col-md-3 mb-2">
            <label class="form-label">Order Status</label>
            <select name="vendor_order_status" id="f_order_status" class="form-control">
              <option value="Pending">Pending</option>
              <option value="Processing">Processing</option>
              <option value="Delivered">Delivered</option>
              <option value="Cancelled">Cancelled</option>
            </select>
          </div>
        </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" onclick="closeAllModals()"
                style="border:1.5px solid #e0e4ef;color:#5a6080;border-radius:9px;padding:8px 20px;font-weight:600;font-size:13.5px;">
          Cancel
        </button>
        <button type="submit" id="saveBtnLabel" class="btn btn-primary" style="font-size:13.5px;">
          <i class="las la-save mr-1"></i> <span>Save Order</span>
        </button>
      </div>
    </form>
  </div>
</div>

<!-- VIEW MODAL -->
<div id="viewModal" class="modal-box-wrap">
  <div class="modal-inner" style="max-width:520px;">
    <div class="modal-head" style="background:linear-gradient(135deg,#1b5e20,#2e7d32);">
      <div style="display:flex;align-items:center;gap:10px;">
        <div class="modal-head-icon"><i class="las la-eye" style="color:#fff;font-size:20px;"></i></div>
        <h5>Order Details</h5>
      </div>
      <button class="modal-close-btn" onclick="closeAllModals()">&times;</button>
    </div>
    <div class="modal-body" id="viewBody" style="padding:20px 24px;"></div>
    <div class="modal-footer" style="justify-content:center;">
      <button class="btn btn-default" onclick="closeAllModals()"
              style="border:1.5px solid #e0e4ef;color:#5a6080;border-radius:9px;padding:8px 28px;font-weight:600;">
        Close
      </button>
    </div>
  </div>
</div>

<!-- DELETE MODAL -->
<div id="deleteModal" class="modal-box-wrap">
  <div class="modal-inner" style="max-width:420px;">
    <div class="modal-head" style="background:linear-gradient(135deg,#b71c1c,#e53935);">
      <div style="display:flex;align-items:center;gap:10px;">
        <div class="modal-head-icon"><i class="las la-exclamation-triangle" style="color:#fff;font-size:18px;"></i></div>
        <h5>Delete Order</h5>
      </div>
      <button class="modal-close-btn" onclick="closeAllModals()">&times;</button>
    </div>
    <div style="padding:32px 24px;text-align:center;">
      <div style="width:72px;height:72px;background:#fdecea;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 18px;">
        <i class="las la-trash" style="font-size:36px;color:#e53935;"></i>
      </div>
      <p style="font-size:15px;color:#444;margin:0 0 6px;font-weight:600;">
        Order <strong id="delOrderLabel" style="color:#c62828;"></strong> delete karo?
      </p>
      <p style="font-size:13px;color:#aaa;margin:0;">Ye action undo nahi ho sakti!</p>
    </div>
    <div class="modal-footer" style="justify-content:center;">
      <button class="btn btn-default" onclick="closeAllModals()"
              style="border:1.5px solid #e0e4ef;color:#5a6080;border-radius:9px;padding:8px 24px;font-weight:600;min-width:110px;">
        Cancel
      </button>
      <button class="btn btn-danger" id="confirmDeleteBtn"
              style="border-radius:9px;font-weight:700;padding:8px 24px;min-width:110px;box-shadow:0 4px 12px rgba(229,57,53,0.35);">
        <i class="las la-trash mr-1"></i> Haan, Delete Karo
      </button>
    </div>
  </div>
</div>

<!-- MAIN PAGE -->
<div class="main-panel">
  <div class="content">
    <div class="container-fluid">

      <div class="d-flex justify-content-between align-items-center mb-2 mt-2">
        <div>
          <h4 class="page-title mb-0">Vendor Orders</h4>
          <p class="text-muted mb-0" style="font-size:12px;">Sabhi vendor orders manage karo</p>
        </div>
        <button class="btn btn-primary btn-sm d-flex align-items-center" style="gap:6px;" onclick="openAdd()">
          <i class="las la-plus" style="font-size:16px;"></i> Add New Order
        </button>
      </div>

      <!-- Filter Bar -->
      <div class="filter-bar">
        <form method="GET">
          <div style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
            <div style="flex:1;min-width:160px;">
              <label style="font-size:12px;font-weight:600;color:#8d9db5;display:block;margin-bottom:4px;">Search</label>
              <input type="text" name="search"
                style="width:100%;height:34px;border:1.5px solid #e2e8f0;border-radius:7px;padding:0 10px;font-size:13px;color:#1a1f36;background:#fff;"
                placeholder="Vendor name, area..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div style="flex:1;min-width:140px;">
              <label style="font-size:12px;font-weight:600;color:#8d9db5;display:block;margin-bottom:4px;">Order Status</label>
              <select name="status"
                style="width:100%;height:34px;border:1.5px solid #e2e8f0;border-radius:7px;padding:0 10px;font-size:13px;color:#1a1f36;background:#fff;">
                <option value="">All Status</option>
                <?php foreach (['Pending','Processing','Delivered','Cancelled'] as $s): ?>
                  <option value="<?= $s ?>" <?= $filter_status == $s ? 'selected' : '' ?>><?= $s ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div style="flex:1;min-width:140px;">
              <label style="font-size:12px;font-weight:600;color:#8d9db5;display:block;margin-bottom:4px;">Payment Status</label>
              <select name="payment"
                style="width:100%;height:34px;border:1.5px solid #e2e8f0;border-radius:7px;padding:0 10px;font-size:13px;color:#1a1f36;background:#fff;">
                <option value="">All Payments</option>
                <?php foreach (['Paid','Unpaid','Partial'] as $p): ?>
                  <option value="<?= $p ?>" <?= $filter_payment == $p ? 'selected' : '' ?>><?= $p ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div style="display:flex;gap:6px;flex-shrink:0;">
              <button type="submit" class="btn btn-primary btn-sm" style="height:34px;padding:0 16px;">
                <i class="la la-search"></i> Filter
              </button>
              <a href="vendor-order.php" class="btn btn-default btn-sm" style="height:34px;padding:0 16px;line-height:34px;display:inline-block;">
                Reset
              </a>
            </div>
          </div>
        </form>
      </div>

      <!-- Table Card -->
      <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between py-2">
          <h5 class="card-title mb-0" style="font-size:15px;">
            <i class="la la-list mr-1"></i> All Vendor Orders
          </h5>
          <span style="font-size:12px;color:#8d9db5;font-weight:600;">
            Total: <strong><?= $orders ? $orders->num_rows : 0 ?></strong> orders
          </span>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table mb-0" id="ordersTable">
              <thead>
                <tr>
                  <th>#ID</th>
                  <th>Ref ID</th>
                  <th>Vendor</th>
                  <th>Area</th>
                  <th>Qty</th>
                  <th>Order Date</th>
                  <th>Total</th>
                  <th>Payment</th>
                  <th>Mode</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody id="ordersBody">
                <?php if ($orders && $orders->num_rows > 0):
                  while ($o = $orders->fetch_assoc()):
                    $pc = ['Paid'=>'badge-paid','Unpaid'=>'badge-unpaid','Partial'=>'badge-partial'];
                    $sc = ['Pending'=>'badge-pending','Processing'=>'badge-processing','Delivered'=>'badge-delivered','Cancelled'=>'badge-cancelled'];
                ?>
                <tr id="row-<?= $o['vendor_order_id'] ?>">
                  <td><strong style="color:#4361ee;">#<?= $o['vendor_order_id'] ?></strong></td>
                  <td><span style="background:#e8f0fe;color:#4361ee;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:700;"><?= htmlspecialchars($o['ordid'] ?? '—') ?></span></td>
                  <td><strong><?= htmlspecialchars($o['vendor_name'] ?? '—') ?></strong></td>
                  <td style="color:#6c7a9c;"><?= htmlspecialchars($o['vendor_area']) ?></td>
                  <td><?= $o['order_quantity'] ?></td>
                  <td style="color:#6c7a9c;"><?= date('d M Y', strtotime($o['order_date'])) ?></td>
                  <td><strong style="color:#4361ee;">₹<?= number_format($o['total'], 2) ?></strong></td>
                  <td><span class="badge-pill <?= $pc[$o['payment_status']] ?? 'badge-unpaid' ?>"><?= $o['payment_status'] ?></span></td>
                  <td style="color:#6c7a9c;font-size:12px;"><?= $o['payment_mode'] ?></td>
                  <td><span class="badge-pill <?= $sc[$o['vendor_order_status']] ?? 'badge-pending' ?>"><?= $o['vendor_order_status'] ?></span></td>
                  <td>
                    <button type="button" class="btn btn-sm btn-action"
                      style="border:1.5px solid #0288d1;color:#0288d1;border-radius:7px;width:32px;height:32px;padding:0;display:inline-flex;align-items:center;justify-content:center;"
                      onclick="openView(<?= $o['vendor_order_id'] ?>)" title="View">
                      <i class="la la-eye"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-action ml-1"
                      style="border:1.5px solid #4361ee;color:#4361ee;border-radius:7px;width:32px;height:32px;padding:0;display:inline-flex;align-items:center;justify-content:center;"
                      onclick="openEdit(<?= $o['vendor_order_id'] ?>)" title="Edit">
                      <i class="la la-edit"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-action ml-1"
                      style="border:1.5px solid #e53935;color:#e53935;border-radius:7px;width:32px;height:32px;padding:0;display:inline-flex;align-items:center;justify-content:center;"
                      onclick="openDelete(<?= $o['vendor_order_id'] ?>)" title="Delete">
                      <i class="la la-trash"></i>
                    </button>
                  </td>
                </tr>
                <?php endwhile; else: ?>
                <tr id="emptyRow">
                  <td colspan="11" class="text-center py-5" style="color:#8d9db5;">
                    <i class="la la-clipboard-list" style="font-size:32px;display:block;margin-bottom:8px;"></i>
                    Koi vendor order nahi hai. <a href="javascript:void(0)" onclick="openAdd()" style="color:#4361ee;">Pehla order add karo!</a>
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
</div>

<script>
function openModal(id) {
    document.getElementById('modalBackdrop').style.display = 'block';
    document.getElementById(id).classList.add('open');
}
function closeAllModals() {
    ['saveModal','viewModal','deleteModal'].forEach(id => document.getElementById(id).classList.remove('open'));
    document.getElementById('modalBackdrop').style.display = 'none';
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeAllModals(); });

function showToast(msg, isError = false) {
    const box  = document.getElementById('toastBox');
    const icon = document.getElementById('toastIcon');
    document.getElementById('toastMsg').textContent = msg;
    box.classList.toggle('error', isError);
    icon.className = isError ? 'las la-exclamation-circle' : 'las la-check-circle';
    icon.style.color = isError ? '#e91e63' : '#11c26d';
    box.classList.add('show');
    setTimeout(() => box.classList.remove('show'), 3500);
}

function ajaxFetchVendorArea(vid) {
    if (!vid) { document.getElementById('f_vendor_area').value = ''; return; }
    fetch('vendor-order.php?ajax=vendor_area&vendor_id=' + vid)
        .then(r => r.json())
        .then(d => { if (d.vendor_area) document.getElementById('f_vendor_area').value = d.vendor_area; });
}

function resetForm() {
    document.getElementById('saveForm').reset();
    document.getElementById('f_order_id').value = '0';
    document.getElementById('f_order_date').value = new Date().toISOString().split('T')[0];
    document.getElementById('f_ordid').value = '';
}

function openAdd() {
    resetForm();
    document.getElementById('f_ordid').placeholder = 'Save karne par auto-generate hogi...';
    document.getElementById('saveModalTitle').textContent = 'Add New Order';
    document.getElementById('saveModalSub').textContent   = 'Fill in the order details below';
    document.getElementById('saveModalHead').style.background = 'linear-gradient(135deg,#4361ee,#6c8fff)';
    document.getElementById('saveModalIcon').className = 'las la-plus-circle';
    document.querySelector('#saveBtnLabel span').textContent = 'Save Order';
    openModal('saveModal');
}

function setSelectValue(elemId, val) {
    const sel = document.getElementById(elemId);
    if (!sel || val === undefined || val === null) return;
    const strVal = String(val).trim();
    sel.value = strVal;
    if (sel.value !== strVal) {
        for (let i = 0; i < sel.options.length; i++) {
            if (String(sel.options[i].value).trim() == strVal) { sel.selectedIndex = i; break; }
        }
    }
}

function openEdit(id) {
    fetch('vendor-order.php?ajax=get&id=' + id)
        .then(r => r.json())
        .then(d => {
            if (!d || !d.vendor_order_id) { showToast('Order data nahi mila!', true); return; }
            resetForm();

            document.getElementById('f_order_id').value    = d.vendor_order_id;
            document.getElementById('f_vendor_area').value = d.vendor_area || '';
            document.getElementById('f_order_qty').value   = d.order_quantity || '';
            document.getElementById('f_order_date').value  = d.order_date ? d.order_date.split(' ')[0].split('T')[0] : '';
            document.getElementById('f_total').value       = d.total || '';
            // ✅ Edit mein ordid show karo (readonly)
            document.getElementById('f_ordid').value       = d.ordid || '—';

            setSelectValue('f_vendor_id',    d.vendor_id);
            setSelectValue('f_pay_status',   d.payment_status);
            setSelectValue('f_pay_mode',     d.payment_mode);
            setSelectValue('f_order_status', d.vendor_order_status);

            document.getElementById('saveModalTitle').textContent = 'Edit Order #' + id;
            document.getElementById('saveModalSub').textContent   = 'Order details update karo';
            document.getElementById('saveModalHead').style.background = 'linear-gradient(135deg,#e65100,#ff8c00)';
            document.getElementById('saveModalIcon').className = 'las la-edit';
            document.querySelector('#saveBtnLabel span').textContent = 'Update Order';
            openModal('saveModal');
        });
}

function openView(id) {
    fetch('vendor-order.php?ajax=get&id=' + id)
        .then(r => r.json())
        .then(d => {
            if (!d || !d.vendor_order_id) { showToast('Order data nahi mila!', true); return; }

            const payBadges  = { Paid:'badge-paid', Unpaid:'badge-unpaid', Partial:'badge-partial' };
            const statBadges = { Pending:'badge-pending', Processing:'badge-processing', Delivered:'badge-delivered', Cancelled:'badge-cancelled' };

            const fmtDate = str => {
                if (!str) return '—';
                const parts = str.split('-');
                if (parts.length < 3) return str;
                const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                return parts[2] + ' ' + months[parseInt(parts[1]) - 1] + ' ' + parts[0];
            };

            const fmtDateTime = str => {
                if (!str || str === '0000-00-00 00:00:00') return 'N/A';
                const parts = str.split(' ');
                const datePart = parts[0] || '';
                const timePart = parts[1] || '';
                const dp = datePart.split('-');
                const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                let dateStr = dp.length === 3 ? dp[2] + ' ' + months[parseInt(dp[1]) - 1] + ' ' + dp[0] : datePart;
                if (!timePart) return dateStr;
                const tp = timePart.split(':');
                let h = parseInt(tp[0]), m = tp[1] || '00';
                const ampm = h >= 12 ? 'PM' : 'AM';
                h = h % 12 || 12;
                return dateStr + ', ' + h + ':' + m + ' ' + ampm;
            };

            document.getElementById('viewBody').innerHTML = `
              <div class="detail-row">
                <span class="detail-label">Order ID</span>
                <span class="detail-value" style="color:#4361ee;font-size:16px;">#${d.vendor_order_id}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Ref ID</span>
                <span class="detail-value" style="color:#4361ee;">${d.ordid || '—'}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Vendor</span>
                <span class="detail-value">${d.vendor_name || '—'}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Area</span>
                <span class="detail-value">${d.vendor_area || '—'}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Quantity</span>
                <span class="detail-value">${d.order_quantity}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Order Date</span>
                <span class="detail-value">${fmtDate(d.order_date)}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Total Amount</span>
                <span class="detail-value" style="color:#4361ee;">₹${parseFloat(d.total).toLocaleString('en-IN', {minimumFractionDigits:2})}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Payment Status</span>
                <span class="badge-pill ${payBadges[d.payment_status] || 'badge-unpaid'}">${d.payment_status}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Payment Mode</span>
                <span class="detail-value">${d.payment_mode}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Order Status</span>
                <span class="badge-pill ${statBadges[d.vendor_order_status] || 'badge-pending'}">${d.vendor_order_status}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Created At</span>
                <span class="detail-value" style="font-size:13px;color:#6c7a9c;">${fmtDateTime(d.created_at)}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Updated At</span>
                <span class="detail-value" style="font-size:13px;color:#6c7a9c;">${fmtDateTime(d.updated_at)}</span>
              </div>
            `;
            openModal('viewModal');
        });
}

let deleteTargetId = null;
function openDelete(id) {
    deleteTargetId = id;
    document.getElementById('delOrderLabel').textContent = '#' + id;
    openModal('deleteModal');
}

document.getElementById('confirmDeleteBtn').addEventListener('click', function () {
    if (!deleteTargetId) return;
    fetch('vendor-order.php?ajax=delete&id=' + deleteTargetId)
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                closeAllModals();
                const row = document.getElementById('row-' + deleteTargetId);
                if (row) {
                    row.style.transition = 'opacity 0.3s, transform 0.3s';
                    row.style.opacity = '0';
                    row.style.transform = 'translateX(20px)';
                    setTimeout(() => {
                        row.remove();
                        const tbody = document.getElementById('ordersBody');
                        if (tbody.querySelectorAll('tr').length === 0) {
                            tbody.innerHTML = `
                              <tr id="emptyRow">
                                <td colspan="11" class="text-center py-5" style="color:#8d9db5;">
                                  <i class="la la-clipboard-list" style="font-size:32px;display:block;margin-bottom:8px;"></i>
                                  Koi vendor order nahi hai. <a href="javascript:void(0)" onclick="openAdd()" style="color:#4361ee;">Pehla order add karo!</a>
                                </td>
                              </tr>`;
                        }
                    }, 300);
                }
                showToast('Order #' + deleteTargetId + ' successfully delete ho gaya!');
                deleteTargetId = null;
            } else {
                showToast(d.error || 'Delete fail ho gaya!', true);
            }
        });
});

document.getElementById('saveForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch('vendor-order.php?ajax=save', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                closeAllModals();
                showToast(d.message);
                setTimeout(() => location.reload(), 800);
            } else {
                showToast(d.error || 'Kuch error hua!', true);
            }
        });
});
</script>

<?php include 'include/footer.php'; ?>