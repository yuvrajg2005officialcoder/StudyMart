<?php include 'include/header.php'; ?>

<div class="main-panel">
    <div class="content">
        <div class="container-fluid">

            <!-- Page header -->
            <div class="d-flex justify-content-between align-items-center mb-4 mt-2">
                <div>
                    <h4 class="page-title mb-0">Home Section</h4>
                    <p class="text-muted mb-0" style="font-size:13px; margin-top:3px;">Manage homepage banner content
                        (title, paragraph, button, products, students, rating, image)</p>
                </div>
                <button class="btn btn-primary btn-sm d-flex align-items-center gap-1" onclick="openAddModal()"
                    style="gap:6px;">
                    <i class="la la-plus" style="font-size:16px;"></i> Add New
                </button>
            </div>

            <!-- Flash message placeholder (filled by JS) -->
            <div id="flashBox"></div>

            <!-- Table card -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0" style="display:flex;align-items:center;gap:8px;">
                        <i class="la la-list" style="color:#4361ee;font-size:20px;"></i> All Home Sections
                    </h5>
                    <span class="badge-count" id="heroCount">0 Records</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th style="padding-left:22px; width:60px;">ID</th>
                                    <th style="width:90px;">Image</th>
                                    <th>Title</th>
                                    <th>Created At</th>
                                    <th style="width:90px;">Status</th>
                                    <th style="width:180px; text-align:right; padding-right:22px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="heroTableBody">
                                <tr>
                                    <td colspan="6" style="text-align:center; padding:40px; color:#aaa;">Loading...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- ── BACKDROP ───────────────────────────────────── -->
    <div id="modalBackdrop" class="modal-overlay" onclick="closeAllModals()"></div>

    <!-- ══ ADD / EDIT MODAL (shared form) ════════════════════════════ -->
    <div id="heroFormModal" class="modal-box-wrap">
        <div
            style="background:#fff; border-radius:16px; width:100%; max-width:560px;
              margin:auto; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,0.18); max-height:90vh; display:flex; flex-direction:column;">

            <div style="background:linear-gradient(135deg,#4361ee,#6c8fff); padding:20px 24px;
                display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:10px;">
                    <div style="width:36px;height:36px;background:rgba(255,255,255,0.2);border-radius:9px;
                    display:flex;align-items:center;justify-content:center;">
                        <i id="formModalIcon" class="la la-plus-circle" style="color:#fff; font-size:20px;"></i>
                    </div>
                    <div>
                        <h5 id="formModalTitle" style="color:#fff; font-weight:800; margin:0; font-size:16px;">Add New
                            Home Section</h5>
                        <p style="color:rgba(255,255,255,0.7); margin:0; font-size:12px;">Fill in the details below</p>
                    </div>
                </div>
                <button onclick="closeAllModals()" style="background:rgba(255,255,255,0.15);border:none;width:32px;height:32px;
                     border-radius:8px;cursor:pointer;color:#fff;font-size:18px;
                     display:flex;align-items:center;justify-content:center;">&times;</button>
            </div>

            <form id="heroForm" enctype="multipart/form-data" style="overflow-y:auto;">
                <input type="hidden" name="action" id="form_action" value="add">
                <input type="hidden" name="home_id" id="form_home_id" value="">
                <input type="hidden" name="old_image" id="form_old_image" value="">

                <div style="padding:24px;">

                    <div style="margin-bottom:16px;">
                        <label class="form-label">Title <span style="color:#e53935;">*</span></label>
                        <input type="text" name="title" id="f_title" class="form-control"
                            placeholder="Everything a Student Needs, Here." required>
                    </div>

                    <div style="margin-bottom:16px;">
                        <label class="form-label">Paragraph</label>
                        <textarea name="paragraph" id="f_paragraph" class="form-control" rows="3"
                            placeholder="From textbooks to tech gadgets — curated gear for every campus life. Shop smarter, learn harder."></textarea>
                    </div>

                    <div class="row" style="margin-bottom:16px;">
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Button Text</label>
                            <input type="text" name="button_text" id="f_button_text" class="form-control"
                                placeholder="Explore Shop">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Button Link</label>
                            <input type="text" name="button_link" id="f_button_link" class="form-control"
                                placeholder="shop.php">
                        </div>
                    </div>

                    <label class="form-label" style="margin-bottom:6px;">Stats</label>
                    <div class="row" style="margin-bottom:16px;">
                        <div class="col-md-4 mb-2">
                            <label class="form-label" style="font-size:12px;color:#aaa;">Products</label>
                            <input type="text" name="products" id="f_products" class="form-control" placeholder="12K+">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label" style="font-size:12px;color:#aaa;">Students</label>
                            <input type="text" name="students" id="f_students" class="form-control" placeholder="50K+">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label" style="font-size:12px;color:#aaa;">Rating</label>
                            <input type="text" name="rating" id="f_rating" class="form-control" placeholder="4.9★">
                        </div>
                    </div>

                    <div style="margin-bottom:16px;">
                        <label class="form-label">Status</label>
                        <select name="status" id="f_status" class="form-control">
                            <option value="0">Active</option>
                            <option value="1">Inactive</option>
                        </select>
                    </div>

                    <!-- Image Upload -->
                    <div>
                        <label class="form-label">Image</label>
                        <div class="upload-zone" id="form_zone"
                            onclick="document.getElementById('f_image').click()">
                            <div id="form_preview_box" style="display:none; margin-bottom:10px;">
                                <img id="form_img_preview" src="#" style="max-height:150px; max-width:240px; border-radius:10px;
                          object-fit:cover; box-shadow:0 4px 14px rgba(0,0,0,0.12);">
                            </div>
                            <div id="form_placeholder">
                                <div style="width:52px;height:52px;background:#f0f4ff;border-radius:12px;
                          display:flex;align-items:center;justify-content:center;margin:0 auto 10px;">
                                    <i class="la la-cloud-upload" style="font-size:28px; color:#4361ee;"></i>
                                </div>
                                <p style="color:#4361ee; font-weight:700; margin:0 0 3px; font-size:14px;">Click to
                                    upload image</p>
                                <p style="color:#aaa; font-size:12px; margin:0;">JPG, PNG, WEBP, GIF · Max 5MB</p>
                            </div>
                            <input type="file" name="image" id="f_image" accept="image/*"
                                style="display:none;"
                                onchange="previewImg(this,'form_img_preview','form_preview_box','form_placeholder','form_zone')">
                        </div>
                    </div>
                </div>

                <div style="border-top:1px solid #f0f2f8; padding:16px 24px;
                  display:flex; justify-content:flex-end; gap:10px; background:#fafbff;">
                    <button type="button" class="btn btn-default" onclick="closeAllModals()" style="border:1.5px solid #e0e4ef; color:#5a6080; border-radius:9px;
                       padding:8px 20px; font-weight:600; font-size:13.5px;">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="font-size:13.5px;">
                        <i class="la la-save mr-1"></i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ══ VIEW MODAL ═══════════════════════════════════ -->
    <div id="viewModal" class="modal-box-wrap">
        <div style="background:#fff; border-radius:16px; width:100%; max-width:480px;
              margin:auto; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,0.18);">
            <div style="background:linear-gradient(135deg,#1b5e20,#2e7d32); padding:20px 24px;
                display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:10px;">
                    <div style="width:36px;height:36px;background:rgba(255,255,255,0.2);border-radius:9px;
                    display:flex;align-items:center;justify-content:center;">
                        <i class="la la-eye" style="color:#fff; font-size:20px;"></i>
                    </div>
                    <h5 style="color:#fff; font-weight:800; margin:0; font-size:16px;">Home Section Details</h5>
                </div>
                <button onclick="closeAllModals()" style="background:rgba(255,255,255,0.15);border:none;width:32px;height:32px;
                     border-radius:8px;cursor:pointer;color:#fff;font-size:18px;
                     display:flex;align-items:center;justify-content:center;">&times;</button>
            </div>
            <div style="padding:28px 24px;">
                <img id="view_img" src="" alt="" style="display:none; width:100%; max-height:180px; object-fit:cover;
           border-radius:12px; margin-bottom:16px; box-shadow:0 6px 20px rgba(0,0,0,0.1);">

                <p style="font-size:12px;color:#aaa;margin:0 0 4px;text-transform:uppercase;font-weight:700;">Title</p>
                <p id="view_title" style="margin:0 0 14px;font-weight:800;font-size:18px;color:#1a1f36;"></p>

                <p style="font-size:12px;color:#aaa;margin:0 0 4px;text-transform:uppercase;font-weight:700;">Paragraph
                </p>
                <p id="view_paragraph" style="margin:0 0 14px;color:#555;font-size:13.5px;"></p>

                <p style="font-size:12px;color:#aaa;margin:0 0 4px;text-transform:uppercase;font-weight:700;">Button
                </p>
                <p id="view_buttons" style="margin:0 0 14px;font-size:13.5px;"></p>

                <div style="display:flex; gap:24px; margin-bottom:14px;">
                    <div>
                        <p style="font-size:11px;color:#aaa;margin:0;text-transform:uppercase;font-weight:700;">Products
                        </p>
                        <p id="view_stat1" style="margin:0;font-weight:700;"></p>
                    </div>
                    <div>
                        <p style="font-size:11px;color:#aaa;margin:0;text-transform:uppercase;font-weight:700;">Students
                        </p>
                        <p id="view_stat2" style="margin:0;font-weight:700;"></p>
                    </div>
                    <div>
                        <p style="font-size:11px;color:#aaa;margin:0;text-transform:uppercase;font-weight:700;">Rating
                        </p>
                        <p id="view_stat3" style="margin:0;font-weight:700;"></p>
                    </div>
                </div>
                <p style="font-size:12px;color:#aaa;margin:0 0 4px;text-transform:uppercase;font-weight:700;">Status</p>
                <p id="view_status" style="margin:0;"></p>
            </div>
            <div
                style="border-top:1px solid #f0f2f8; padding:16px 24px; display:flex; justify-content:center; background:#fafbff;">
                <button class="btn btn-default" onclick="closeAllModals()" style="border:1.5px solid #e0e4ef; color:#5a6080; border-radius:9px;
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
                    <h5 style="color:#fff; font-weight:800; margin:0; font-size:16px;">Delete Home Section</h5>
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
                <button class="btn btn-danger" id="confirmDeleteBtn" style="border-radius:9px; font-weight:700; padding:8px 24px; min-width:110px;
                     box-shadow:0 4px 12px rgba(229,57,53,0.35);">
                    <i class="la la-trash mr-1"></i> Delete
                </button>
            </div>
        </div>
    </div>

    <?php include 'include/footer.php'; ?>
</div>

<script>
    const AJAX_URL = 'home_ajax.php';
    let deleteId = null;

    // ── Modal Open/Close ─────────────────────────────────────────
    function openModal(id) {
        document.getElementById('modalBackdrop').style.display = 'block';
        document.getElementById(id).classList.add('open');
    }
    function closeAllModals() {
        ['heroFormModal', 'viewModal', 'deleteModal'].forEach(id => document.getElementById(id).classList.remove('open'));
        document.getElementById('modalBackdrop').style.display = 'none';
    }
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeAllModals(); });

    function showFlash(type, msg) {
        document.getElementById('flashBox').innerHTML = `
      <div class="alert alert-${type} alert-dismissible fade show"
           style="border-radius:10px; border:none; font-weight:600; font-size:13.5px;
                  box-shadow:0 4px 14px rgba(0,0,0,0.08);">
        <i class="la la-${type === 'success' ? 'check-circle' : 'exclamation-circle'} mr-1"></i> ${msg}
        <button type="button" onclick="this.closest('.alert').remove()"
                style="background:none;border:none;float:right;font-size:20px;cursor:pointer;opacity:0.6;">&times;</button>
      </div>`;
    }

    // ── Image Preview ────────────────────────────────────────────
    function previewImg(input, previewId, boxId, placeholderId, zoneId) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = e => {
                document.getElementById(previewId).src = e.target.result;
                document.getElementById(boxId).style.display = 'block';
                document.getElementById(placeholderId).style.display = 'none';
                if (zoneId) document.getElementById(zoneId).classList.add('has-image');
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function resetForm() {
        document.getElementById('heroForm').reset();
        document.getElementById('form_preview_box').style.display = 'none';
        document.getElementById('form_placeholder').style.display = 'block';
        document.getElementById('form_zone').classList.remove('has-image');
        document.getElementById('form_home_id').value = '';
        document.getElementById('form_old_image').value = '';
    }

    // ── Load Table (AJAX) ────────────────────────────────────────
    function loadHeroTable() {
        fetch(AJAX_URL + '?action=list')
            .then(r => r.json())
            .then(res => {
                const tbody = document.getElementById('heroTableBody');
                const rows = res.data || [];
                document.getElementById('heroCount').textContent = rows.length + ' Records';
                if (rows.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="6" style="text-align:center; padding:60px 20px; color:#aaa;">
                <i class="la la-inbox" style="font-size:48px; display:block; margin-bottom:10px; color:#d0d5e8;"></i>
                <p style="font-size:15px; color:#b0b8cc; margin:0 0 10px;">No home sections found.</p>
                <a href="javascript:void(0)" onclick="openAddModal()" style="color:#4361ee; font-weight:600; font-size:13px;">+ Add your first one</a>
              </td></tr>`;
                    return;
                }
                tbody.innerHTML = rows.map(row => {
                    const imgHtml = row.image_url
                        ? `<img src="${row.image_url}" class="thumb-img" alt="">`
                        : `<div class="thumb-placeholder"><i class="la la-image"></i></div>`;
                    const statusHtml = row.status == 0
                        ? `<span style="background:#e8f5e9;color:#2e7d32;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:700;">● Active</span>`
                        : `<span style="background:#fdecea;color:#c62828;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:700;">● Inactive</span>`;
                    const dt = row.created_at ? new Date(row.created_at.replace(' ', 'T')) : null;
                    const dateStr = dt ? dt.toLocaleString('en-IN', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '';

                    return `<tr>
                <td style="padding-left:22px;"><span style="color:#8d9db5; font-weight:700; font-size:13px;">#${row.home_id}</span></td>
                <td>${imgHtml}</td>
                <td><span style="font-weight:700;">${escapeHtml(row.title)}</span></td>
                <td><span style="font-size:13px; color:#8d9db5;">${dateStr}</span></td>
                <td>${statusHtml}</td>
                <td style="text-align:right; padding-right:22px;">
                  <button class="btn btn-sm btn-action" style="border:1.5px solid #0288d1; color:#0288d1; margin-right:4px;"
                    onclick="openViewModal(${row.home_id})" title="View"><i class="la la-eye"></i></button>
                  <button class="btn btn-sm btn-action" style="border:1.5px solid #4361ee; color:#4361ee; margin-right:4px;"
                    onclick="openEditModal(${row.home_id})" title="Edit"><i class="la la-edit"></i> Edit</button>
                  <button class="btn btn-sm btn-action" style="border:1.5px solid #e53935; color:#e53935;"
                    onclick="openDeleteModal(${row.home_id}, '${escapeHtml(row.title).replace(/'/g, "\\'")}')" title="Delete"><i class="la la-trash"></i></button>
                </td>
              </tr>`;
                }).join('');
            })
            .catch(() => showFlash('danger', 'Failed to load data.'));
    }

    function escapeHtml(str) {
        return (str || '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    }

    // ── Add ───────────────────────────────────────────────────────
    function openAddModal() {
        resetForm();
        document.getElementById('form_action').value = 'add';
        document.getElementById('formModalTitle').textContent = 'Add New Home Section';
        document.getElementById('formModalIcon').className = 'la la-plus-circle';
        openModal('heroFormModal');
    }

    // ── Edit ──────────────────────────────────────────────────────
    function openEditModal(id) {
        fetch(`${AJAX_URL}?action=get&id=${id}`).then(r => r.json()).then(res => {
            if (!res.success) { showFlash('danger', res.message); return; }
            const d = res.data;
            resetForm();
            document.getElementById('form_action').value = 'edit';
            document.getElementById('form_home_id').value = d.home_id;
            document.getElementById('form_old_image').value = d.image || '';
            document.getElementById('formModalTitle').textContent = 'Edit Home Section';
            document.getElementById('formModalIcon').className = 'la la-edit';

            document.getElementById('f_title').value = d.title || '';
            document.getElementById('f_paragraph').value = d.paragraph || '';
            document.getElementById('f_button_text').value = d.button_text || '';
            document.getElementById('f_button_link').value = d.button_link || '';
            document.getElementById('f_products').value = d.products || '';
            document.getElementById('f_students').value = d.students || '';
            document.getElementById('f_rating').value = d.rating || '';
            document.getElementById('f_status').value = d.status;

            if (d.image_url) {
                document.getElementById('form_img_preview').src = d.image_url;
                document.getElementById('form_preview_box').style.display = 'block';
                document.getElementById('form_placeholder').style.display = 'none';
            }
            openModal('heroFormModal');
        });
    }

    // ── Submit (Add/Edit) ───────────────────────────────────────
    document.getElementById('heroForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const fd = new FormData(this);
        fetch(AJAX_URL, { method: 'POST', body: fd })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    closeAllModals();
                    showFlash('success', res.message);
                    loadHeroTable();
                } else {
                    showFlash('danger', res.message);
                }
            })
            .catch(() => showFlash('danger', 'Something went wrong.'));
    });

    // ── View ──────────────────────────────────────────────────────
    function openViewModal(id) {
        fetch(`${AJAX_URL}?action=get&id=${id}`).then(r => r.json()).then(res => {
            if (!res.success) { showFlash('danger', res.message); return; }
            const d = res.data;
            document.getElementById('view_title').textContent = d.title || '—';
            document.getElementById('view_paragraph').textContent = d.paragraph || '—';
            document.getElementById('view_buttons').textContent = `${d.button_text || '—'} (${d.button_link || '#'})`;
            document.getElementById('view_stat1').textContent = `${d.products || ''} Products`;
            document.getElementById('view_stat2').textContent = `${d.students || ''} Students`;
            document.getElementById('view_stat3').textContent = `${d.rating || ''} Rating`;
            document.getElementById('view_status').innerHTML = d.status == 0
                ? '<span style="background:#e8f5e9;color:#2e7d32;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:700;">● Active</span>'
                : '<span style="background:#fdecea;color:#c62828;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:700;">● Inactive</span>';
            const viewImg = document.getElementById('view_img');
            if (d.image_url) { viewImg.src = d.image_url; viewImg.style.display = 'block'; }
            else { viewImg.style.display = 'none'; }
            openModal('viewModal');
        });
    }

    // ── Delete ────────────────────────────────────────────────────
    function openDeleteModal(id, name) {
        deleteId = id;
        document.getElementById('delete_name').textContent = name;
        openModal('deleteModal');
    }
    document.getElementById('confirmDeleteBtn').addEventListener('click', function () {
        if (!deleteId) return;
        const fd = new FormData();
        fd.append('action', 'delete');
        fd.append('home_id', deleteId);
        fetch(AJAX_URL, { method: 'POST', body: fd })
            .then(r => r.json())
            .then(res => {
                closeAllModals();
                showFlash(res.success ? 'success' : 'danger', res.message);
                if (res.success) loadHeroTable();
            });
    });

    // ── Init ──────────────────────────────────────────────────────
    loadHeroTable();
</script>