<?php
include 'include/conn.php';

$success = '';
$error   = '';

// ── DELETE MESSAGE ────────────────────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id  = intval($_POST['msg_id']);
    $del = $mysqli->prepare("DELETE FROM contact_messages WHERE msg_id=?");
    $del->bind_param("i", $id);
    $del->execute();
    $del->close();
    header("Location: messages.php?msg=deleted");
    exit;
}

// ── MARK AS READ (optional, when opened via View) ───────────────
if (isset($_POST['action']) && $_POST['action'] === 'mark_read') {
    $id = intval($_POST['msg_id']);
    $upd = $mysqli->prepare("UPDATE contact_messages SET is_read=1 WHERE msg_id=?");
    $upd->bind_param("i", $id);
    $upd->execute();
    $upd->close();
    echo json_encode(['success' => true]);
    exit;
}

// Add is_read column if it doesn't exist yet (safe auto-upgrade)
$colCheck = $mysqli->query("SHOW COLUMNS FROM contact_messages LIKE 'is_read'");
if ($colCheck && $colCheck->num_rows === 0) {
    $mysqli->query("ALTER TABLE contact_messages ADD COLUMN is_read TINYINT(1) DEFAULT 0");
}

$result = $mysqli->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
$unreadCount = $mysqli->query("SELECT COUNT(*) as c FROM contact_messages WHERE is_read=0")->fetch_assoc()['c'] ?? 0;

include 'include/header.php';
?>

<div class="main-panel">
<div class="content">
<div class="container-fluid">

  <!-- Page header -->
  <div class="d-flex justify-content-between align-items-center mb-4 mt-2">
    <div>
      <h4 class="page-title mb-0">Contact Messages</h4>
      <p class="text-muted mb-0" style="font-size:13px; margin-top:3px;">Messages submitted via the Contact Us form</p>
    </div>
    <span class="badge-count">
      <?= $unreadCount ?> Unread
    </span>
  </div>

  <!-- Flash messages -->
  <?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
    <div class="alert alert-danger alert-dismissible fade show"
         style="border-radius:10px; border:none; font-weight:600; font-size:13.5px;
                box-shadow:0 4px 14px rgba(0,0,0,0.08);">
      <i class="la la-trash mr-1"></i> Message deleted successfully!
      <button type="button" onclick="this.closest('.alert').remove()"
              style="background:none;border:none;float:right;font-size:20px;cursor:pointer;opacity:0.6;line-height:1;">&times;</button>
    </div>
  <?php endif; ?>

  <!-- Table card -->
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="card-title mb-0" style="display:flex;align-items:center;gap:8px;">
        <i class="la la-envelope" style="color:#4361ee;font-size:20px;"></i> All Messages
      </h5>
      <span class="badge-count"><?= $result->num_rows ?> Total</span>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th style="padding-left:22px; width:60px;">ID</th>
              <th>Name</th>
              <th>Email</th>
              <th>Subject</th>
              <th>Received At</th>
              <th style="width:90px;">Status</th>
              <th style="width:150px; text-align:right; padding-right:22px;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($result->num_rows > 0): while ($row = $result->fetch_assoc()): ?>
            <tr style="<?= $row['is_read'] == 0 ? 'background:#f5f7ff;' : '' ?>">
              <td style="padding-left:22px;">
                <span style="color:#8d9db5; font-weight:700; font-size:13px;">#<?= $row['msg_id'] ?></span>
              </td>
              <td>
                <span style="font-weight:700; color:#1a1f36; font-size:14px;">
                  <?= htmlspecialchars($row['name']) ?>
                </span>
              </td>
              <td>
                <span style="font-size:13px; color:#5a6080;">
                  <?= htmlspecialchars($row['email']) ?>
                </span>
              </td>
              <td>
                <span style="font-size:13px; color:#5a6080;">
                  <?= htmlspecialchars(mb_strimwidth($row['subject'], 0, 40, '...')) ?>
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
                <?php if ($row['is_read'] == 0): ?>
                  <span style="background:#fff3e0;color:#e65100;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:700;">
                    ● New
                  </span>
                <?php else: ?>
                  <span style="background:#e8f5e9;color:#2e7d32;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:700;">
                    ● Read
                  </span>
                <?php endif; ?>
              </td>
              <td style="text-align:right; padding-right:22px;">

                <button class="btn btn-sm btn-action view-btn"
                  style="border:1.5px solid #0288d1; color:#0288d1; margin-right:4px;"
                  data-id="<?= $row['msg_id'] ?>"
                  data-name="<?= htmlspecialchars($row['name'], ENT_QUOTES) ?>"
                  data-email="<?= htmlspecialchars($row['email'], ENT_QUOTES) ?>"
                  data-subject="<?= htmlspecialchars($row['subject'], ENT_QUOTES) ?>"
                  data-message="<?= htmlspecialchars($row['message'], ENT_QUOTES) ?>"
                  data-date="<?= date('d M Y, h:i A', strtotime($row['created_at'])) ?>"
                  title="View">
                  <i class="la la-eye"></i> View
                </button>

                <button class="btn btn-sm btn-action delete-btn"
                  style="border:1.5px solid #e53935; color:#e53935;"
                  data-id="<?= $row['msg_id'] ?>"
                  data-name="<?= htmlspecialchars($row['name'], ENT_QUOTES) ?>"
                  title="Delete">
                  <i class="la la-trash"></i>
                </button>

              </td>
            </tr>
            <?php endwhile; else: ?>
            <tr>
              <td colspan="7" style="text-align:center; padding:60px 20px; color:#aaa;">
                <i class="la la-inbox" style="font-size:48px; display:block; margin-bottom:10px; color:#d0d5e8;"></i>
                <p style="font-size:15px; color:#b0b8cc; margin:0;">No messages received yet.</p>
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

<!-- ══ VIEW MODAL ═══════════════════════════════════ -->
<div id="viewModal" class="modal-box-wrap">
  <div style="background:#fff; border-radius:16px; width:100%; max-width:540px;
              margin:auto; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,0.18);">
    <div style="background:linear-gradient(135deg,#0288d1,#26c6da); padding:20px 24px;
                display:flex; align-items:center; justify-content:space-between;">
      <div style="display:flex; align-items:center; gap:10px;">
        <div style="width:36px;height:36px;background:rgba(255,255,255,0.2);border-radius:9px;
                    display:flex;align-items:center;justify-content:center;">
          <i class="la la-envelope-open" style="color:#fff; font-size:20px;"></i>
        </div>
        <h5 style="color:#fff; font-weight:800; margin:0; font-size:16px;">Message Details</h5>
      </div>
      <button onclick="closeAllModals()"
              style="background:rgba(255,255,255,0.15);border:none;width:32px;height:32px;
                     border-radius:8px;cursor:pointer;color:#fff;font-size:18px;
                     display:flex;align-items:center;justify-content:center;">&times;</button>
    </div>
    <div style="padding:28px 24px;">

      <div style="display:flex; gap:20px; margin-bottom:18px; flex-wrap:wrap;">
        <div style="flex:1; min-width:180px;">
          <p style="font-size:11px; color:#aaa; margin:0 0 4px; text-transform:uppercase; letter-spacing:0.8px; font-weight:700;">From</p>
          <p id="view_name" style="font-weight:700; margin:0; font-size:15px; color:#1a1f36;"></p>
        </div>
        <div style="flex:1; min-width:180px;">
          <p style="font-size:11px; color:#aaa; margin:0 0 4px; text-transform:uppercase; letter-spacing:0.8px; font-weight:700;">Email</p>
          <p id="view_email" style="font-weight:600; margin:0; font-size:14px; color:#4361ee;"></p>
        </div>
      </div>

      <div style="margin-bottom:18px;">
        <p style="font-size:11px; color:#aaa; margin:0 0 4px; text-transform:uppercase; letter-spacing:0.8px; font-weight:700;">Subject</p>
        <p id="view_subject" style="font-weight:700; margin:0; font-size:15px; color:#1a1f36;"></p>
      </div>

      <div style="margin-bottom:18px;">
        <p style="font-size:11px; color:#aaa; margin:0 0 6px; text-transform:uppercase; letter-spacing:0.8px; font-weight:700;">Message</p>
        <div id="view_message" style="background:#f8f9fc; border-radius:10px; padding:16px;
                    font-size:13.5px; color:#3a4158; line-height:1.6; white-space:pre-wrap;
                    max-height:220px; overflow-y:auto; border:1px solid #f0f2f8;"></div>
      </div>

      <p style="font-size:12px; color:#aaa; margin:0; display:flex; align-items:center; gap:6px;">
        <i class="la la-calendar"></i> Received: <span id="view_date" style="font-weight:600; color:#5a6080;"></span>
      </p>

    </div>
    <div style="border-top:1px solid #f0f2f8; padding:16px 24px; display:flex;
                justify-content:space-between; align-items:center; background:#fafbff;">
      <a id="view_reply_btn" href="#" target="_blank"
         style="color:#0288d1; font-weight:700; font-size:13px; text-decoration:none; display:flex; align-items:center; gap:6px;">
        <i class="la la-reply"></i> Reply via Email
      </a>
      <button class="btn btn-default" onclick="closeAllModals()"
              style="border:1.5px solid #e0e4ef; color:#5a6080; border-radius:9px;
                     padding:8px 28px; font-weight:600;">Close</button>
    </div>
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
        <h5 style="color:#fff; font-weight:800; margin:0; font-size:16px;">Delete Message</h5>
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
        Delete message from "<strong id="delete_name" style="color:#c62828;"></strong>"?
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
        <input type="hidden" name="msg_id" id="delete_msg_id">
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
    document.getElementById(id).classList.add('open');
}

function closeAllModals() {
    ['viewModal','deleteModal'].forEach(function(id) {
        document.getElementById(id).classList.remove('open');
    });
    document.getElementById('modalBackdrop').style.display = 'none';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeAllModals();
});

// ── View button clicks ────────────────────────────────────────
document.querySelectorAll('.view-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var id      = this.dataset.id;
        var name    = this.dataset.name;
        var email   = this.dataset.email;
        var subject = this.dataset.subject;
        var message = this.dataset.message;
        var date    = this.dataset.date;

        document.getElementById('view_name').textContent    = name;
        document.getElementById('view_email').textContent   = email;
        document.getElementById('view_subject').textContent = subject;
        document.getElementById('view_message').textContent = message;
        document.getElementById('view_date').textContent    = date;
        document.getElementById('view_reply_btn').href =
            'mailto:' + email + '?subject=' + encodeURIComponent('Re: ' + subject);

        openModal('viewModal');

        // Mark as read via AJAX (no page reload)
        fetch('messages.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=mark_read&msg_id=' + id
        }).then(function() {
            // Update the row's status badge live without reload
            var row = btn.closest('tr');
        });
    });
});

// ── Delete button clicks ───────────────────────────────────────
document.querySelectorAll('.delete-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.getElementById('delete_name').textContent = this.dataset.name;
        document.getElementById('delete_msg_id').value      = this.dataset.id;
        openModal('deleteModal');
    });
});
</script>