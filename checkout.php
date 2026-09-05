<?php
session_start();
include 'admin/include/conn.php';

// ── Logged-in user ki saved details DB se laao ──────────────
$loggedIn = isset($_SESSION['vendor_id']);
$vendor   = null;

if ($loggedIn) {
    $stmt = $mysqli->prepare("SELECT vendor_name, vendor_email, mobile_no, address, state, city, area FROM vendor WHERE vendor_id = ?");
    $stmt->bind_param("i", $_SESSION['vendor_id']);
    $stmt->execute();
    $vendor = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$v_name  = $vendor['vendor_name']  ?? '';
$v_email = $vendor['vendor_email'] ?? '';
$v_phone = $vendor['mobile_no']    ?? '';
$v_addr  = $vendor['address']      ?? '';
$v_city  = $vendor['city']         ?? '';
$v_state = $vendor['state']        ?? '';
$v_area  = $vendor['area']         ?? '';

$indianStates = ['Rajasthan','Delhi','Maharashtra','Karnataka','Tamil Nadu','Uttar Pradesh',
  'Gujarat','West Bengal','Telangana','Bihar','Madhya Pradesh','Punjab','Haryana','Odisha','Assam'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Checkout – StudyMart</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,400&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="style.css" />
  <style>

    /* ══════════════════════════════════════
       CHECKOUT PAGE – EXCLUSIVE STYLES
    ══════════════════════════════════════ */

    body { background: #f1f5f9; }

    /* SECURE BAR */
    .secure-topbar {
      background: linear-gradient(90deg,#0f172a,#1e293b);
      color:#fff;
      padding:10px 0;
      font-size:.83rem;
      display:flex;align-items:center;justify-content:center;gap:28px;
      flex-wrap:wrap;
    }
    .secure-topbar span { display:flex;align-items:center;gap:6px; }
    .secure-topbar i { color:#4ade80; }

    /* CHECKOUT NAV */
    .checkout-nav {
      background:#fff;
      border-bottom:1px solid var(--border);
      padding:14px 0;
      position:sticky;top:0;z-index:200;
    }
    .checkout-nav .container {
      display:flex;align-items:center;justify-content:space-between;gap:16px;
    }
    .checkout-brand {
      font-family:var(--font-head);font-size:1.4rem;font-weight:700;
      color:var(--dark);text-decoration:none;display:flex;align-items:center;gap:8px;
    }
    .secure-lock {
      display:flex;align-items:center;gap:6px;
      font-size:.83rem;color:var(--mid);font-weight:600;
    }
    .secure-lock i { color:#10b981; }
    .secure-lock a { color:var(--primary);text-decoration:none; }

    /* STEP PROGRESS */
    .step-progress {
      background:#fff;border-bottom:1px solid var(--border);padding:18px 0;
    }
    .steps-wrap {
      display:flex;align-items:center;justify-content:center;gap:0;
      max-width:560px;margin:0 auto;
    }
    .step-item {
      display:flex;flex-direction:column;align-items:center;gap:6px;flex:1;
      position:relative;
    }
    .step-item::before {
      content:'';position:absolute;top:18px;left:calc(50% + 20px);right:calc(-50% + 20px);
      height:2px;background:var(--border);z-index:0;
    }
    .step-item:last-child::before { display:none; }
    .step-item.active::before,.step-item.done::before { background:var(--primary); }
    .step-circle {
      width:38px;height:38px;border-radius:50%;
      display:flex;align-items:center;justify-content:center;
      font-family:var(--font-head);font-weight:800;font-size:.9rem;
      background:var(--light);border:2px solid var(--border);
      color:var(--mid);z-index:1;transition:var(--transition);
    }
    .step-item.done .step-circle { background:var(--primary);border-color:var(--primary);color:#fff; }
    .step-item.active .step-circle {
      background:#fff;border-color:var(--primary);color:var(--primary);
      box-shadow:0 0 0 4px rgba(37,99,235,.15);
    }
    .step-label { font-size:.75rem;font-weight:700;color:var(--mid);font-family:var(--font-head); }
    .step-item.active .step-label,.step-item.done .step-label { color:var(--primary); }

    /* SECTION CARD */
    .co-card {
      background:#fff;border-radius:20px;
      border:1.5px solid var(--border);margin-bottom:20px;overflow:hidden;
    }
    .co-card-header {
      display:flex;align-items:center;gap:14px;padding:20px 24px;
      border-bottom:1px solid var(--border);background:var(--light);
    }
    .co-card-header.clickable { cursor:pointer;transition:var(--transition); }
    .co-card-header.clickable:hover { background:#e2e8f0; }
    .step-num {
      width:36px;height:36px;border-radius:12px;background:var(--primary);
      color:#fff;display:flex;align-items:center;justify-content:center;
      font-family:var(--font-head);font-weight:800;font-size:.9rem;flex-shrink:0;
    }
    .step-num.done { background:var(--accent2); }
    .co-card-title {
      font-family:var(--font-head);font-weight:800;font-size:1rem;flex:1;
    }
    .co-card-edit {
      font-size:.8rem;color:var(--primary);font-weight:700;cursor:pointer;
      background:none;border:none;font-family:var(--font-head);
    }
    .co-card-body { padding:24px; }

    /* FORM ELEMENTS */
    .co-label {
      display:block;font-size:.83rem;font-weight:700;
      color:var(--dark);margin-bottom:6px;
    }
    .co-label span { color:#ef4444; }
    .co-input {
      width:100%;border:1.5px solid var(--border);border-radius:12px;
      padding:11px 16px;font-family:var(--font-body);font-size:.9rem;
      outline:none;transition:var(--transition);background:#fff;
    }
    .co-input:focus { border-color:var(--primary);box-shadow:0 0 0 3px rgba(37,99,235,.1); }
    .co-input.error { border-color:#ef4444;background:#fff5f5; }
    .co-input::placeholder { color:#cbd5e1; }
    .co-select { appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%2394a3b8' stroke-width='1.5' fill='none'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 14px center;padding-right:36px;cursor:pointer; }
    .field-error { font-size:.75rem;color:#ef4444;margin-top:4px;display:none; }

    /* SAVED ADDRESS CARD */
    .saved-addr-wrap { display:flex;gap:10px;flex-wrap:wrap;margin-bottom:20px; }
    .saved-addr-card {
      border:1.5px solid var(--border);border-radius:14px;padding:14px 18px;
      cursor:pointer;transition:var(--transition);flex:1;min-width:180px;
      position:relative;
    }
    .saved-addr-card:hover { border-color:var(--primary); }
    .saved-addr-card.selected { border-color:var(--primary);background:#eff6ff; }
    .saved-addr-card .addr-type { font-size:.72rem;font-weight:700;color:var(--primary);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px; }
    .saved-addr-card .addr-name { font-family:var(--font-head);font-weight:700;font-size:.88rem; }
    .saved-addr-card .addr-text { font-size:.8rem;color:var(--mid);line-height:1.5; }
    .addr-check {
      position:absolute;top:10px;right:10px;width:20px;height:20px;
      border-radius:50%;background:var(--primary);color:#fff;
      display:none;align-items:center;justify-content:center;font-size:.65rem;
    }
    .saved-addr-card.selected .addr-check { display:flex; }
    .or-divider { display:flex;align-items:center;gap:12px;margin:16px 0;color:var(--mid);font-size:.82rem; }
    .or-divider::before,.or-divider::after { content:'';flex:1;height:1px;background:var(--border); }
    .guest-login-note {
      background:#fff7ed;border:1px solid #fed7aa;border-radius:12px;
      padding:14px 18px;margin-bottom:18px;font-size:.85rem;color:#9a3412;
    }
    .guest-login-note a { color:#c2410c;font-weight:700;text-decoration:none; }

    /* SHIPPING METHODS */
    .shipping-methods { display:flex;flex-direction:column;gap:10px; }
    .shipping-option {
      display:flex;align-items:center;gap:14px;padding:16px 18px;
      border:1.5px solid var(--border);border-radius:14px;cursor:pointer;
      transition:var(--transition);position:relative;
    }
    .shipping-option:hover { border-color:var(--primary); }
    .shipping-option.selected { border-color:var(--primary);background:#eff6ff; }
    .shipping-option input[type="radio"] { accent-color:var(--primary);width:16px;height:16px;flex-shrink:0; }
    .so-icon { font-size:1.6rem; }
    .so-info { flex:1; }
    .so-name { font-family:var(--font-head);font-weight:700;font-size:.9rem; }
    .so-desc { font-size:.78rem;color:var(--mid); }
    .so-price { font-family:var(--font-head);font-weight:800;color:var(--primary);font-size:.95rem; }
    .so-badge {
      position:absolute;top:-1px;right:12px;
      background:var(--accent2);color:#fff;
      font-size:.65rem;font-weight:700;padding:2px 10px;
      border-radius:0 0 8px 8px;font-family:var(--font-head);
    }

    /* PAYMENT METHODS */
    .payment-tabs { display:flex;background:var(--light);border-radius:14px;padding:4px;gap:4px;margin-bottom:20px; }
    .pay-tab {
      flex:1;padding:10px 6px;border:none;border-radius:10px;
      font-family:var(--font-head);font-weight:700;font-size:.78rem;
      color:var(--mid);cursor:pointer;transition:var(--transition);
      display:flex;flex-direction:column;align-items:center;gap:4px;background:none;
    }
    .pay-tab i { font-size:1.1rem; }
    .pay-tab.active { background:#fff;color:var(--primary);box-shadow:var(--shadow); }

    .pay-panel { display:none; }
    .pay-panel.active { display:block; }

    /* UPI */
    .upi-grid { display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px; }
    .upi-option {
      border:1.5px solid var(--border);border-radius:12px;padding:12px 16px;
      cursor:pointer;transition:var(--transition);display:flex;align-items:center;gap:10px;
      font-family:var(--font-head);font-weight:700;font-size:.85rem;
    }
    .upi-option:hover { border-color:var(--primary); }
    .upi-option.selected { border-color:var(--primary);background:#eff6ff; }
    .upi-option img { width:28px;height:28px;object-fit:contain; }

    /* CARD */
    .card-preview {
      background:linear-gradient(135deg,#1e1b4b,#2563eb);
      border-radius:18px;padding:24px;color:#fff;margin-bottom:20px;
      position:relative;overflow:hidden;height:160px;
    }
    .card-preview::before {
      content:'';position:absolute;top:-40px;right:-40px;
      width:160px;height:160px;border-radius:50%;
      background:rgba(255,255,255,.08);
    }
    .card-preview::after {
      content:'';position:absolute;bottom:-50px;right:40px;
      width:120px;height:120px;border-radius:50%;
      background:rgba(255,255,255,.05);
    }
    .card-chip { width:36px;height:26px;background:#fbbf24;border-radius:5px;margin-bottom:16px; }
    .card-number { font-family:var(--font-head);font-size:1.1rem;letter-spacing:3px;margin-bottom:12px; }
    .card-bottom { display:flex;justify-content:space-between;font-size:.78rem;opacity:.8; }
    .card-bottom strong { display:block;font-size:.9rem;opacity:1; }

    /* NET BANKING */
    .bank-grid { display:grid;grid-template-columns:repeat(auto-fill,minmax(110px,1fr));gap:10px;margin-bottom:16px; }
    .bank-option {
      border:1.5px solid var(--border);border-radius:12px;padding:12px 10px;
      cursor:pointer;transition:var(--transition);text-align:center;font-size:.78rem;
      font-weight:700;font-family:var(--font-head);color:var(--dark);
    }
    .bank-option:hover { border-color:var(--primary);color:var(--primary); }
    .bank-option.selected { border-color:var(--primary);background:#eff6ff;color:var(--primary); }
    .bank-option .bank-icon { font-size:1.4rem;margin-bottom:5px; }

    /* COD */
    .cod-box {
      background:var(--light);border-radius:16px;padding:20px 24px;
      display:flex;align-items:flex-start;gap:16px;
    }
    .cod-icon { font-size:2.5rem;flex-shrink:0; }
    .cod-info h6 { font-family:var(--font-head);font-weight:800;margin-bottom:4px; }
    .cod-info p { color:var(--mid);font-size:.85rem;line-height:1.7;margin:0; }
    .cod-fee { display:inline-block;background:#fff7ed;color:#c2410c;border-radius:6px;padding:3px 10px;font-size:.78rem;font-weight:700;margin-top:8px; }

    /* ORDER SUMMARY SIDEBAR */
    .order-sidebar { position:sticky;top:80px; }
    .os-card {
      background:#fff;border-radius:20px;border:1.5px solid var(--border);overflow:hidden;
    }
    .os-header { background:linear-gradient(135deg,#0f172a,#1e293b);padding:18px 22px;color:#fff; }
    .os-header h5 { font-family:var(--font-head);font-weight:800;margin:0;font-size:1rem; }
    .os-header small { color:rgba(255,255,255,.6);font-size:.78rem; }
    .os-body { padding:20px; }

    /* Cart items in sidebar */
    .os-items { max-height:260px;overflow-y:auto;margin-bottom:16px;scrollbar-width:thin; }
    .os-item { display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px dashed var(--border); }
    .os-item:last-child { border-bottom:none; }
    .os-item-img { width:52px;height:52px;border-radius:10px;object-fit:cover;flex-shrink:0; }
    .os-item-info { flex:1;min-width:0; }
    .os-item-name { font-family:var(--font-head);font-weight:700;font-size:.82rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
    .os-item-qty { font-size:.75rem;color:var(--mid); }
    .os-item-price { font-family:var(--font-head);font-weight:800;font-size:.88rem;color:var(--primary);white-space:nowrap; }

    /* Summary rows */
    .os-row { display:flex;justify-content:space-between;align-items:center;padding:9px 0;border-bottom:1px dashed var(--border);font-size:.88rem; }
    .os-row:last-of-type { border-bottom:none; }
    .os-row.total-row { font-family:var(--font-head);font-weight:800;font-size:1.1rem;color:var(--primary);padding-top:12px;border-top:2px solid var(--border); }
    .os-row .striked { text-decoration:line-through;color:var(--mid);font-size:.8rem; }
    .os-row .saved { color:#10b981;font-weight:700; }

    /* Coupon in sidebar */
    .coupon-mini {
      background:var(--light);border-radius:12px;padding:12px 14px;margin-bottom:16px;
    }
    .coupon-mini label { font-size:.8rem;font-weight:700;color:var(--mid);margin-bottom:6px;display:block; }
    .coupon-row-input { display:flex;gap:6px; }
    .coupon-row-input input {
      flex:1;border:1.5px solid var(--border);border-radius:9px;padding:9px 12px;
      font-size:.83rem;outline:none;font-family:var(--font-body);
    }
    .coupon-row-input input:focus { border-color:var(--primary); }
    .coupon-row-input button {
      background:var(--primary);color:#fff;border:none;border-radius:9px;
      padding:9px 14px;font-weight:700;font-size:.8rem;cursor:pointer;
      transition:var(--transition);font-family:var(--font-head);white-space:nowrap;
    }
    .coupon-row-input button:hover { background:var(--primary-dark); }
    .coupon-applied { display:flex;align-items:center;gap:8px;background:#f0fdf4;border:1.5px solid #bbf7d0;border-radius:10px;padding:10px 14px; }
    .coupon-applied i { color:#10b981; }
    .coupon-applied span { font-size:.83rem;font-weight:700;color:#15803d;flex:1; }
    .coupon-applied button { background:none;border:none;color:#ef4444;cursor:pointer;font-size:.85rem; }
    .coupon-msg { font-size:.75rem;margin-top:5px; }
    .coupon-msg.ok { color:#10b981; }
    .coupon-msg.err { color:#ef4444; }

    /* Place order button */
    .btn-place-order {
      width:100%;background:linear-gradient(135deg,var(--primary),#7c3aed);
      color:#fff;border:none;border-radius:14px;
      padding:16px;font-family:var(--font-head);font-weight:800;
      font-size:1rem;cursor:pointer;transition:var(--transition);
      display:flex;align-items:center;justify-content:center;gap:10px;
      box-shadow:0 6px 24px rgba(37,99,235,.35);letter-spacing:.3px;
    }
    .btn-place-order:hover { transform:translateY(-2px);box-shadow:0 10px 32px rgba(37,99,235,.45); }
    .btn-place-order:active { transform:scale(.98); }
    .btn-place-order.loading { opacity:.75;pointer-events:none; }

    .trust-row { display:flex;justify-content:center;gap:18px;margin-top:14px;flex-wrap:wrap; }
    .trust-item { font-size:.75rem;color:var(--mid);display:flex;align-items:center;gap:5px;font-weight:600; }
    .trust-item i { color:#10b981; }

    /* SUCCESS OVERLAY */
    .order-success-overlay {
      display:none;position:fixed;inset:0;
      background:rgba(15,23,42,.6);backdrop-filter:blur(6px);
      z-index:9999;align-items:center;justify-content:center;padding:20px;
    }
    .order-success-overlay.show { display:flex; }
    .success-card {
      background:#fff;border-radius:28px;padding:48px 40px;
      text-align:center;max-width:480px;width:100%;
      box-shadow:0 32px 80px rgba(0,0,0,.2);
      animation:popIn .4s cubic-bezier(.175,.885,.32,1.275);
    }
    @keyframes popIn { from{transform:scale(.7);opacity:0} to{transform:scale(1);opacity:1} }
    .success-icon {
      font-size:4.5rem;margin-bottom:16px;display:block;
      animation:bounceIn .6s ease .2s both;
    }
    @keyframes bounceIn {
      0%{transform:scale(0)} 60%{transform:scale(1.15)} 100%{transform:scale(1)}
    }
    .success-card h2 { font-family:var(--font-head);font-weight:800;font-size:1.6rem;color:var(--dark);margin-bottom:8px; }
    .success-card p { color:var(--mid);font-size:.92rem;line-height:1.7;margin-bottom:24px; }
    .order-id-box {
      background:var(--light);border:1.5px dashed var(--border);
      border-radius:12px;padding:12px 20px;margin-bottom:24px;
    }
    .order-id-box small { display:block;font-size:.75rem;color:var(--mid);margin-bottom:2px; }
    .order-id-box strong { font-family:var(--font-head);font-size:1.1rem;color:var(--primary); }
    .success-steps { display:flex;justify-content:center;gap:0;margin-bottom:28px;flex-wrap:wrap; }
    .ss-step {
      display:flex;flex-direction:column;align-items:center;gap:4px;
      padding:10px 16px;flex:1;min-width:80px;
      position:relative;font-size:.72rem;color:var(--mid);font-weight:600;
    }
    .ss-step::after { content:'→';position:absolute;right:-4px;top:12px;color:var(--mid);font-size:.8rem; }
    .ss-step:last-child::after { display:none; }
    .ss-icon { font-size:1.4rem;margin-bottom:2px; }
    .success-btns { display:flex;gap:10px;justify-content:center;flex-wrap:wrap; }
    .btn-track { background:var(--primary);color:#fff;border:none;border-radius:50px;padding:12px 28px;font-family:var(--font-head);font-weight:700;cursor:pointer;text-decoration:none;transition:var(--transition); }
    .btn-track:hover { background:var(--primary-dark);color:#fff; }
    .btn-back-shop { background:var(--light);color:var(--dark);border:1.5px solid var(--border);border-radius:50px;padding:11px 24px;font-family:var(--font-head);font-weight:700;cursor:pointer;text-decoration:none;transition:var(--transition); }
    .btn-back-shop:hover { border-color:var(--primary);color:var(--primary); }

    /* CONFETTI particles */
    .confetti-wrap { position:fixed;inset:0;pointer-events:none;z-index:10000;overflow:hidden; }
    .confetti-piece {
      position:absolute;top:-20px;
      width:10px;height:10px;border-radius:2px;
      animation:confettiFall linear forwards;
    }
    @keyframes confettiFall {
      to { transform:translateY(110vh) rotate(720deg); opacity:0; }
    }

    @media(max-width:991px) {
      .order-sidebar { position:relative;top:0; }
    }
    @media(max-width:767px) {
      .steps-wrap { gap:0; }
      .step-label { font-size:.68rem; }
      .co-card-body { padding:16px; }
      .success-card { padding:32px 20px; }
      .payment-tabs { flex-wrap:wrap; }
      .bank-grid { grid-template-columns:repeat(3,1fr); }
    }

    /* ══════════════════════════════════════
       MOBILE FIXES (≤576px)
    ══════════════════════════════════════ */
    @media(max-width:576px) {
      /* Secure top bar - compact single row, scrollable if needed */
      .secure-topbar {
        gap:10px;
        padding:8px 10px;
        font-size:.68rem;
        flex-wrap:nowrap;
        overflow-x:auto;
        justify-content:flex-start;
        white-space:nowrap;
        -ms-overflow-style:none;
        scrollbar-width:none;
      }
      .secure-topbar::-webkit-scrollbar { display:none; }
      .secure-topbar span { flex-shrink:0; }

      /* Checkout nav - keep single row, no wrap */
      .checkout-nav { padding:10px 0; }
      .checkout-nav .container {
        flex-wrap:nowrap;
        gap:8px;
        padding-left:14px;
        padding-right:14px;
      }
      .checkout-brand { font-size:1.05rem;gap:5px;flex-shrink:0; }
      .checkout-brand span:first-child { font-size:1.05rem; }
      .secure-lock { font-size:.72rem;gap:4px;flex-shrink:0;white-space:nowrap; }
      .checkout-nav a[href="cart.php"] {
        font-size:.72rem !important;
        flex-shrink:0;
        white-space:nowrap;
      }

      /* Step progress - tighter */
      .step-progress { padding:14px 0; }
      .step-circle { width:32px;height:32px;font-size:.8rem; }
      .step-label { font-size:.62rem; }
      .step-item::before { top:16px; }

      /* Cards */
      .co-card { border-radius:14px;margin-bottom:14px; }
      .co-card-header { padding:14px 16px;gap:10px; }
      .co-card-title { font-size:.9rem; }
      .step-num { width:30px;height:30px;font-size:.8rem;border-radius:9px; }
      .co-card-body { padding:14px; }

      .saved-addr-card { min-width:100%; }
      .guest-login-note { font-size:.8rem;padding:12px 14px; }

      .shipping-option { padding:12px 14px;gap:10px; }
      .so-name { font-size:.85rem; }
      .so-desc { font-size:.72rem; }

      .payment-tabs { gap:3px;padding:3px; }
      .pay-tab { font-size:.68rem;padding:8px 4px; }
      .pay-tab i { font-size:1rem; }

      .card-preview { padding:18px;height:140px; }
      .card-number { font-size:.95rem;letter-spacing:2px; }

      .os-header { padding:14px 16px; }
      .os-body { padding:14px; }

      .success-card { padding:28px 16px; }
      .success-icon { font-size:3.6rem; }
      .success-card h2 { font-size:1.3rem; }
      .success-steps { gap:0; }
      .ss-step { padding:8px 6px;min-width:64px;font-size:.62rem; }
    }

    @media(max-width:360px) {
      .secure-lock span.secure-text { display:none; }
      .checkout-nav a[href="cart.php"] span.back-text { display:none; }
    }
  </style>
</head>
<body>

<!-- SECURE TOPBAR -->
<div class="secure-topbar">
  <span><i class="fa fa-lock"></i> 256-bit SSL Encrypted</span>
  <span><i class="fa fa-shield-alt"></i> Secure Checkout</span>
  <span><i class="fa fa-undo"></i> Easy 7-Day Returns</span>
</div>

<!-- CHECKOUT NAVBAR -->
<nav class="checkout-nav">
  <div class="container">
    <a class="checkout-brand" href="index.php">
      <span>📚</span><span>Study<strong>Mart</strong></span>
    </a>
    <div class="secure-lock">
      <?php if ($loggedIn): ?>
        <i class="fa fa-user-circle" style="color:var(--primary);"></i> <span class="secure-text">Hi, <?= htmlspecialchars($v_name) ?> 👋</span>
      <?php else: ?>
        <i class="fa fa-lock"></i> <span class="secure-text">Secure Checkout &nbsp;·&nbsp; </span><a href="login.php">Login</a>
      <?php endif; ?>
    </div>
    <a href="cart.php" style="color:var(--mid);font-size:.84rem;font-weight:600;text-decoration:none;">
      <i class="fa fa-arrow-left me-1"></i> <span class="back-text">Back to Cart</span>
    </a>
  </div>
</nav>

<!-- STEP PROGRESS BAR -->
<div class="step-progress">
  <div class="container">
    <div class="steps-wrap" id="stepsWrap">
      <div class="step-item active" id="sp1">
        <div class="step-circle"><i class="fa fa-map-marker-alt"></i></div>
        <span class="step-label">Shipping</span>
      </div>
      <div class="step-item" id="sp2">
        <div class="step-circle">2</div>
        <span class="step-label">Payment</span>
      </div>
      <div class="step-item" id="sp3">
        <div class="step-circle">3</div>
        <span class="step-label">Review</span>
      </div>
      <div class="step-item" id="sp4">
        <div class="step-circle"><i class="fa fa-check"></i></div>
        <span class="step-label">Confirm</span>
      </div>
    </div>
  </div>
</div>

<!-- ═══════════════════════════════════════
     MAIN CHECKOUT LAYOUT
═══════════════════════════════════════ -->
<div class="container py-4">
  <div class="row g-4 align-items-start">

    <!-- ───────────── LEFT COLUMN ───────────── -->
    <div class="col-lg-7">

      <!-- ① SHIPPING INFO -->
      <div class="co-card" id="shippingCard">
        <div class="co-card-header">
          <div class="step-num" id="sn1">1</div>
          <span class="co-card-title">Shipping Information</span>
        </div>
        <div class="co-card-body" id="shippingBody">

          <?php if ($loggedIn): ?>
            <!-- Logged-in user ki saved details, DB se auto-loaded -->
            <div class="saved-addr-wrap">
              <div class="saved-addr-card selected" onclick="selectAddr(this); useSavedAddress();" id="addrSaved">
                <div class="addr-check"><i class="fa fa-check"></i></div>
                <div class="addr-type">👤 Your Account Details</div>
                <div class="addr-name"><?= htmlspecialchars($v_name) ?></div>
                <div class="addr-text">
                  <?= htmlspecialchars($v_phone ?: 'Phone not added') ?><br />
                  <?= htmlspecialchars(trim(implode(', ', array_filter([$v_addr, $v_area, $v_city, $v_state])), ', ') ?: 'No saved address yet') ?>
                </div>
              </div>
            </div>
            <div class="or-divider">or edit the details below</div>
          <?php else: ?>
            <div class="guest-login-note">
              <i class="fa fa-info-circle me-2"></i>
              <a href="login.php">Login</a> karo apni saved details automatically fill karne ke liye, ya neeche manually address daal kar guest checkout karo.
            </div>
          <?php endif; ?>

          <!-- New Address Form (auto-filled if logged in) -->
          <div id="newAddrForm">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="co-label">Full Name <span>*</span></label>
                <input type="text" class="co-input" id="sh_name" placeholder="Rahul Kumar" value="<?= htmlspecialchars($v_name) ?>" />
                <div class="field-error" id="err_name">Name is required</div>
              </div>
              <div class="col-md-6">
                <label class="co-label">Phone Number <span>*</span></label>
                <input type="tel" class="co-input" id="sh_phone" placeholder="+91 98765 43210" maxlength="10" value="<?= htmlspecialchars($v_phone) ?>" />
                <div class="field-error" id="err_phone">Valid 10-digit number required</div>
              </div>
              <div class="col-12">
                <label class="co-label">Email Address <span>*</span></label>
                <input type="email" class="co-input" id="sh_email" placeholder="rahul@college.edu" value="<?= htmlspecialchars($v_email) ?>" />
                <div class="field-error" id="err_email">Valid email required</div>
              </div>
              <div class="col-12">
                <label class="co-label">Address Line 1 <span>*</span></label>
                <input type="text" class="co-input" id="sh_addr1" placeholder="Flat / House No., Building Name, Street" value="<?= htmlspecialchars($v_addr) ?>" />
                <div class="field-error" id="err_addr1">Address is required</div>
              </div>
              <div class="col-12">
                <label class="co-label">Address Line 2</label>
                <input type="text" class="co-input" id="sh_addr2" placeholder="Landmark, Area (optional)" value="<?= htmlspecialchars($v_area) ?>" />
              </div>
              <div class="col-md-4">
                <label class="co-label">PIN Code <span>*</span></label>
                <input type="text" class="co-input" id="sh_pin" placeholder="302017" maxlength="6" oninput="autoFillCity(this.value)" />
                <div class="field-error" id="err_pin">Valid 6-digit PIN required</div>
              </div>
              <div class="col-md-4">
                <label class="co-label">City <span>*</span></label>
                <input type="text" class="co-input" id="sh_city" placeholder="Jaipur" value="<?= htmlspecialchars($v_city) ?>" />
                <div class="field-error" id="err_city">City is required</div>
              </div>
              <div class="col-md-4">
                <label class="co-label">State <span>*</span></label>
                <select class="co-input co-select" id="sh_state">
                  <option value="">Select State</option>
                  <?php foreach ($indianStates as $st): ?>
                    <option <?= ($v_state === $st) ? 'selected' : '' ?>><?= htmlspecialchars($st) ?></option>
                  <?php endforeach; ?>
                </select>
                <div class="field-error" id="err_state">State is required</div>
              </div>
              <div class="col-12">
                <label class="co-label d-flex align-items-center gap-2" style="cursor:pointer;">
                  <input type="checkbox" id="saveNewAddr" style="accent-color:var(--primary);width:15px;height:15px;" />
                  Save this address for future orders
                </label>
              </div>
            </div>
          </div>

          <!-- Shipping Method -->
          <div class="mt-4">
            <label class="co-label mb-3">Shipping Method</label>
            <div class="shipping-methods">
              <label class="shipping-option selected" onclick="selectShipping(this,'standard',49)">
                <input type="radio" name="shipping" value="standard" checked />
                <span class="so-icon">🚚</span>
                <div class="so-info">
                  <div class="so-name">Standard Delivery</div>
                  <div class="so-desc">3–5 business days · Tracking included</div>
                </div>
                <span class="so-price" id="std_price">₹49</span>
              </label>
              <label class="shipping-option" onclick="selectShipping(this,'express',99)">
                <span class="so-badge">Fast</span>
                <input type="radio" name="shipping" value="express" />
                <span class="so-icon">⚡</span>
                <div class="so-info">
                  <div class="so-name">Express Delivery</div>
                  <div class="so-desc">1–2 business days · Priority handling</div>
                </div>
                <span class="so-price">₹99</span>
              </label>
              <label class="shipping-option" onclick="selectShipping(this,'sameday',149)">
                <span class="so-badge" style="background:#f97316;">Today</span>
                <input type="radio" name="shipping" value="sameday" />
                <span class="so-icon">🏃</span>
                <div class="so-info">
                  <div class="so-name">Same Day Delivery</div>
                  <div class="so-desc">Order before 12 PM · Jaipur only</div>
                </div>
                <span class="so-price">₹149</span>
              </label>
            </div>
          </div>

          <button class="btn btn-hero-primary mt-4 w-100" onclick="goToPayment()">
            Continue to Payment <i class="fa fa-arrow-right ms-2"></i>
          </button>
        </div>
      </div>

      <!-- ② PAYMENT -->
      <div class="co-card" id="paymentCard" style="opacity:.5;pointer-events:none;">
        <div class="co-card-header clickable" onclick="goToSection('payment')">
          <div class="step-num" id="sn2">2</div>
          <span class="co-card-title">Payment Method</span>
          <button class="co-card-edit" id="payEditBtn" style="display:none;">Change</button>
        </div>
        <div class="co-card-body" id="paymentBody">

          <!-- Payment Tabs -->
          <div class="payment-tabs">
            <button class="pay-tab active" onclick="switchPay('upi',this)">
              <i class="fa fa-mobile-alt"></i> UPI
            </button>
            <button class="pay-tab" onclick="switchPay('card',this)">
              <i class="fa fa-credit-card"></i> Card
            </button>
            <button class="pay-tab" onclick="switchPay('netbanking',this)">
              <i class="fa fa-university"></i> Net Banking
            </button>
            <button class="pay-tab" onclick="switchPay('cod',this)">
              <i class="fa fa-money-bill-wave"></i> COD
            </button>
          </div>

          <!-- UPI Panel -->
          <div class="pay-panel active" id="pan_upi">
            <div class="upi-grid">
              <div class="upi-option selected" onclick="selectUpi(this,'gpay')">
                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/f/f2/Google_Pay_Logo.svg/120px-Google_Pay_Logo.svg.png" alt="GPay" />
                Google Pay
              </div>
              <div class="upi-option" onclick="selectUpi(this,'paytm')">
                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/24/Paytm_Logo_%28standalone%29.svg/120px-Paytm_Logo_%28standalone%29.svg.png" alt="Paytm" />
                Paytm
              </div>
              <div class="upi-option" onclick="selectUpi(this,'phonepe')">
                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/5f/PhonePe_Logo.svg/120px-PhonePe_Logo.svg.png" alt="PhonePe" />
                PhonePe
              </div>
              <div class="upi-option" onclick="selectUpi(this,'bhim')">
                <span style="font-size:1.6rem;">🇮🇳</span>
                BHIM UPI
              </div>
            </div>
            <label class="co-label">Or enter UPI ID manually</label>
            <input type="text" class="co-input" id="upiId" placeholder="yourname@upi" />
            <div class="coupon-msg" id="upiMsg"></div>
          </div>

          <!-- Card Panel -->
          <div class="pay-panel" id="pan_card">
            <div class="card-preview" id="cardPreview">
              <div class="card-chip"></div>
              <div class="card-number" id="previewNum">•••• •••• •••• ••••</div>
              <div class="card-bottom">
                <div><small>Card Holder</small><strong id="previewName">YOUR NAME</strong></div>
                <div><small>Expires</small><strong id="previewExp">MM/YY</strong></div>
                <div style="font-size:1.5rem;">💳</div>
              </div>
            </div>
            <div class="row g-3">
              <div class="col-12">
                <label class="co-label">Card Number <span>*</span></label>
                <input type="text" class="co-input" id="cardNum" placeholder="1234 5678 9012 3456" maxlength="19" oninput="formatCard(this)" />
              </div>
              <div class="col-12">
                <label class="co-label">Cardholder Name <span>*</span></label>
                <input type="text" class="co-input" id="cardName" placeholder="RAHUL KUMAR" oninput="document.getElementById('previewName').textContent=this.value.toUpperCase()||'YOUR NAME'" style="text-transform:uppercase;" />
              </div>
              <div class="col-6">
                <label class="co-label">Expiry Date <span>*</span></label>
                <input type="text" class="co-input" id="cardExp" placeholder="MM / YY" maxlength="7" oninput="formatExpiry(this)" />
              </div>
              <div class="col-6">
                <label class="co-label">CVV <span>*</span></label>
                <input type="password" class="co-input" id="cardCvv" placeholder="•••" maxlength="4" />
              </div>
              <div class="col-12">
                <label class="co-label d-flex align-items-center gap-2" style="cursor:pointer;">
                  <input type="checkbox" style="accent-color:var(--primary);width:15px;height:15px;" />
                  Save card for future payments (secured)
                </label>
              </div>
            </div>
          </div>

          <!-- Net Banking Panel -->
          <div class="pay-panel" id="pan_netbanking">
            <label class="co-label mb-2">Select Your Bank</label>
            <div class="bank-grid">
              <div class="bank-option selected" onclick="selectBank(this)"><div class="bank-icon">🏦</div>SBI</div>
              <div class="bank-option" onclick="selectBank(this)"><div class="bank-icon">🏦</div>HDFC</div>
              <div class="bank-option" onclick="selectBank(this)"><div class="bank-icon">🏦</div>ICICI</div>
              <div class="bank-option" onclick="selectBank(this)"><div class="bank-icon">🏦</div>Axis</div>
              <div class="bank-option" onclick="selectBank(this)"><div class="bank-icon">🏦</div>Kotak</div>
              <div class="bank-option" onclick="selectBank(this)"><div class="bank-icon">🏦</div>Punjab NB</div>
              <div class="bank-option" onclick="selectBank(this)"><div class="bank-icon">🏦</div>BoB</div>
              <div class="bank-option" onclick="selectBank(this)"><div class="bank-icon">🏦</div>Other</div>
            </div>
            <p style="font-size:.82rem;color:var(--mid);">You'll be redirected to your bank's portal to complete payment.</p>
          </div>

          <!-- COD Panel -->
          <div class="pay-panel" id="pan_cod">
            <div class="cod-box">
              <div class="cod-icon">💵</div>
              <div class="cod-info">
                <h6>Cash on Delivery</h6>
                <p>Pay in cash when your order arrives at your doorstep. Have exact change ready for a smooth handover.</p>
                <span class="cod-fee">⚠️ Extra ₹30 COD handling fee applies</span>
              </div>
            </div>
          </div>

          <button class="btn btn-hero-primary mt-4 w-100" onclick="goToReview()">
            Continue to Review <i class="fa fa-arrow-right ms-2"></i>
          </button>
        </div>
      </div>

      <!-- ③ ORDER REVIEW -->
      <div class="co-card" id="reviewCard" style="opacity:.5;pointer-events:none;">
        <div class="co-card-header clickable" onclick="goToSection('review')">
          <div class="step-num" id="sn3">3</div>
          <span class="co-card-title">Review Your Order</span>
          <button class="co-card-edit" id="reviewEditBtn" style="display:none;">Change</button>
        </div>
        <div class="co-card-body" id="reviewBody">

          <!-- Shipping Summary -->
          <div class="review-block mb-4">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
              <h6 style="font-family:var(--font-head);font-weight:800;margin:0;">📍 Delivery Address</h6>
              <button class="co-card-edit" onclick="goToSection('shipping')">Edit</button>
            </div>
            <div id="reviewAddress" style="background:var(--light);border-radius:12px;padding:14px;font-size:.88rem;color:var(--mid);line-height:1.7;"></div>
          </div>

          <!-- Payment Summary -->
          <div class="review-block mb-4">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
              <h6 style="font-family:var(--font-head);font-weight:800;margin:0;">💳 Payment Method</h6>
              <button class="co-card-edit" onclick="goToSection('payment')">Edit</button>
            </div>
            <div id="reviewPayment" style="background:var(--light);border-radius:12px;padding:14px;font-size:.88rem;color:var(--mid);"></div>
          </div>

          <!-- Items Summary -->
          <div class="review-block">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
              <h6 style="font-family:var(--font-head);font-weight:800;margin:0;">🛍️ Order Items</h6>
              <a href="cart.php" class="co-card-edit">Edit Cart</a>
            </div>
            <div id="reviewItems"></div>
          </div>

          <div class="mt-4 p-3" style="background:#fff7ed;border-radius:12px;border:1px solid #fed7aa;">
            <label class="co-label mb-1" style="font-size:.82rem;">📝 Order Notes (optional)</label>
            <textarea class="co-input" id="orderNotes" rows="2" placeholder="Any special instructions for delivery…" style="resize:none;"></textarea>
          </div>

          <button class="btn btn-hero-primary mt-4 w-100" onclick="confirmOrder()">
            Confirm & Place Order <i class="fa fa-lock ms-2"></i>
          </button>
          <p style="text-align:center;font-size:.75rem;color:var(--mid);margin-top:10px;">
            By placing your order, you agree to our <a href="#" style="color:var(--primary);">Terms of Service</a> and <a href="#" style="color:var(--primary);">Privacy Policy</a>
          </p>
        </div>
      </div>

    </div>

    <!-- ───────────── RIGHT SIDEBAR ───────────── -->
    <div class="col-lg-5">
      <div class="order-sidebar">
        <div class="os-card">
          <div class="os-header">
            <h5>Order Summary</h5>
            <small id="osItemCount">0 items</small>
          </div>
          <div class="os-body">

            <!-- Cart Items -->
            <div class="os-items" id="osSideItems"></div>

            <!-- Coupon -->
            <div class="coupon-mini">
              <label><i class="fa fa-tag me-1"></i> Have a Coupon Code?</label>
              <div id="couponInputWrap">
                <div class="coupon-row-input">
                  <input type="text" id="sidebarCoupon" placeholder="e.g. STUDY15" />
                  <button onclick="applySidebarCoupon()">Apply</button>
                </div>
                <div class="coupon-msg" id="sideCouponMsg"></div>
              </div>
              <div class="coupon-applied" id="couponAppliedBadge" style="display:none;">
                <i class="fa fa-check-circle"></i>
                <span id="couponAppliedText">Coupon applied!</span>
                <button onclick="removeCoupon()"><i class="fa fa-times"></i></button>
              </div>
            </div>

            <!-- Price Rows -->
            <div class="os-row"><span>Subtotal</span><span id="osSubtotal">₹0</span></div>
            <div class="os-row" id="osDiscRow" style="display:none;"><span>Coupon Discount</span><span class="saved" id="osDisc">-₹0</span></div>
            <div class="os-row"><span>Shipping</span><span id="osShipping">₹49</span></div>
            <div class="os-row" id="osCodRow" style="display:none;"><span>COD Fee</span><span>₹30</span></div>
            <div class="os-row total-row">
              <span>Total Payable</span>
              <span id="osTotal">₹49</span>
            </div>

            <!-- Place Order -->
            <button class="btn-place-order mt-3" id="placeOrderBtn" onclick="confirmOrder()">
              <i class="fa fa-lock"></i> Place Order Securely
            </button>

            <div class="trust-row">
              <span class="trust-item"><i class="fa fa-shield-alt"></i> 100% Safe</span>
              <span class="trust-item"><i class="fa fa-undo"></i> Easy Returns</span>
              <span class="trust-item"><i class="fa fa-truck"></i> Fast Ship</span>
            </div>
          </div>
        </div>

        <!-- Payment Logos -->
        <div style="background:#fff;border:1.5px solid var(--border);border-radius:16px;padding:14px 18px;margin-top:16px;">
          <p style="font-size:.75rem;color:var(--mid);text-align:center;margin-bottom:10px;font-weight:600;">ACCEPTED PAYMENT METHODS</p>
          <div style="display:flex;align-items:center;justify-content:center;gap:10px;flex-wrap:wrap;">
            <img src="https://upload.wikimedia.org/wikipedia/commons/5/5e/Visa_Inc._logo.svg" alt="Visa" style="height:18px;filter:grayscale(30%);" />
            <img src="https://upload.wikimedia.org/wikipedia/commons/2/2a/Mastercard-logo.svg" alt="MC" style="height:22px;" />
            <span style="font-size:.75rem;font-weight:800;color:var(--mid);background:var(--light);padding:3px 8px;border-radius:4px;">UPI</span>
            <span style="font-size:.75rem;font-weight:800;color:var(--mid);background:var(--light);padding:3px 8px;border-radius:4px;">Paytm</span>
            <span style="font-size:.75rem;font-weight:800;color:var(--mid);background:var(--light);padding:3px 8px;border-radius:4px;">COD</span>
          </div>
        </div>

      </div>
    </div>

  </div>
</div>

<!-- ═══════════════════════════════════════
     ORDER SUCCESS OVERLAY
═══════════════════════════════════════ -->
<div class="order-success-overlay" id="successOverlay">
  <div class="success-card">
    <span class="success-icon">🎉</span>
    <h2>Order Placed!</h2>
    <p>Yay! Your order has been confirmed. You'll receive a confirmation email and SMS shortly.</p>
    <div class="order-id-box">
      <small>Your Order ID</small>
      <strong id="successOrderId">#SM2025001234</strong>
    </div>
    <div class="success-steps">
      <div class="ss-step"><div class="ss-icon">✅</div>Confirmed</div>
      <div class="ss-step"><div class="ss-icon">📦</div>Packed</div>
      <div class="ss-step"><div class="ss-icon">🚚</div>Shipped</div>
      <div class="ss-step"><div class="ss-icon">🏠</div>Delivered</div>
    </div>
    <div class="success-btns">
      <a class="btn-track" href="index.php"><i class="fa fa-home me-2"></i>Go Home</a>
      <a class="btn-back-shop" href="shop.php"><i class="fa fa-store me-2"></i>Shop More</a>
    </div>
  </div>
</div>
<div class="confetti-wrap" id="confettiWrap"></div>

<!-- CART TOAST -->
<div class="cart-toast" id="cartToast"><i class="fa fa-check-circle"></i> Done!</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
<script src="app.js"></script>
<script>

/* ══════════════════════════════════════
   CHECKOUT PAGE JS
══════════════════════════════════════ */

let currentStep   = 'shipping';  // shipping | payment | review
let shippingCost  = 49;
let couponDiscount = 0;
let selectedPayMethod = 'upi';
let isCod = false;

// ── LOGGED-IN USER KI SAVED DETAILS (PHP se aayi hain) ──────────
const isLoggedIn   = <?= $loggedIn ? 'true' : 'false' ?>;
const savedAddress = <?= json_encode([
  'name'  => $v_name,
  'phone' => $v_phone,
  'email' => $v_email,
  'addr1' => $v_addr,
  'addr2' => $v_area,
  'city'  => $v_city,
  'state' => $v_state,
]) ?>;

// Form fields ko logged-in user ki saved details se bhar do
function useSavedAddress() {
  if (!isLoggedIn) return;
  const set = (id, val) => { const el = document.getElementById(id); if (el && val) el.value = val; };
  set('sh_name',  savedAddress.name);
  set('sh_phone', savedAddress.phone);
  set('sh_email', savedAddress.email);
  set('sh_addr1', savedAddress.addr1);
  set('sh_addr2', savedAddress.addr2);
  set('sh_city',  savedAddress.city);
  if (savedAddress.state) {
    const stateEl = document.getElementById('sh_state');
    if (stateEl) stateEl.value = savedAddress.state;
  }
}

// ── INIT ────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  updateCartBadge();

  // Demo: add items if cart empty
  if (cart.length === 0) {
    cart = [
      { ...products[0], qty: 1 },
      { ...products[2], qty: 2 },
      { ...products[4], qty: 1 },
    ];
    saveCart();
  }

  // Checkout page khulte hi logged-in user ki details auto-fill ho jaayein
  useSavedAddress();

  renderSidebar();
  updateOrderTotal();
  setStep('shipping');
});

// ── RENDER SIDEBAR ITEMS ────────────────────────────────────────
function renderSidebar() {
  const wrap = document.getElementById('osSideItems');
  const cnt  = document.getElementById('osItemCount');
  if (!wrap) return;
  const total = cart.reduce((s,i) => s + i.qty, 0);
  if (cnt) cnt.textContent = `${total} item${total !== 1 ? 's' : ''}`;
  wrap.innerHTML = cart.map(item => `
    <div class="os-item">
      <img class="os-item-img" src="${item.img}" alt="${item.name}" />
      <div class="os-item-info">
        <div class="os-item-name">${item.name}</div>
        <div class="os-item-qty">Qty: ${item.qty}</div>
      </div>
      <span class="os-item-price">₹${(item.price * item.qty).toLocaleString()}</span>
    </div>`).join('');
}

// ── ORDER TOTAL CALCULATION ─────────────────────────────────────
function updateOrderTotal() {
  const subtotal = cart.reduce((s,i) => s + i.price * i.qty, 0);
  const codFee   = isCod ? 30 : 0;
  const total    = subtotal - couponDiscount + shippingCost + codFee;

  setText('osSubtotal', '₹' + subtotal.toLocaleString());
  setText('osShipping', shippingCost === 0 ? 'FREE' : '₹' + shippingCost);
  setText('osTotal', '₹' + total.toLocaleString());

  const discRow = document.getElementById('osDiscRow');
  if (discRow) discRow.style.display = couponDiscount > 0 ? 'flex' : 'none';
  setText('osDisc', '-₹' + couponDiscount.toLocaleString());

  const codRow = document.getElementById('osCodRow');
  if (codRow) codRow.style.display = isCod ? 'flex' : 'none';
}
function setText(id, val) {
  const el = document.getElementById(id);
  if (el) el.textContent = val;
}

// ── STEP NAVIGATION ─────────────────────────────────────────────
function setStep(step) {
  currentStep = step;

  const cards   = { shipping:'shippingCard', payment:'paymentCard', review:'reviewCard' };
  const stepNums = { shipping:1, payment:2, review:3 };

  // Enable/dim cards
  Object.keys(cards).forEach(k => {
    const el = document.getElementById(cards[k]);
    if (!el) return;
    const isActive = (k === step);
    const isDone   = stepNums[k] < stepNums[step];
    el.style.opacity = (isActive || isDone) ? '1' : '.45';
    el.style.pointerEvents = (isActive || isDone) ? 'all' : 'none';
  });

  // Step progress circles
  ['sp1','sp2','sp3','sp4'].forEach((id,i) => {
    const el = document.getElementById(id);
    if (!el) return;
    const n = stepNums[step];
    el.className = 'step-item' + (i + 1 === n ? ' active' : (i + 1 < n ? ' done' : ''));
    if (i + 1 < n) el.querySelector('.step-circle').innerHTML = '<i class="fa fa-check"></i>';
  });

  // Edit buttons
  document.getElementById('payEditBtn')   && (document.getElementById('payEditBtn').style.display    = stepNums[step] > 2 ? 'inline' : 'none');
  document.getElementById('reviewEditBtn')&& (document.getElementById('reviewEditBtn').style.display = stepNums[step] > 3 ? 'inline' : 'none');

  // Step num colours
  Object.keys(stepNums).forEach(k => {
    const el = document.getElementById('sn' + stepNums[k]);
    if (!el) return;
    el.className = 'step-num' + (stepNums[k] < stepNums[step] ? ' done' : '');
    if (stepNums[k] < stepNums[step]) el.innerHTML = '<i class="fa fa-check"></i>';
    else el.textContent = stepNums[k];
  });

  // Scroll to active card
  const activeCard = document.getElementById(cards[step]);
  if (activeCard) setTimeout(() => activeCard.scrollIntoView({ behavior:'smooth', block:'start' }), 100);
}

function goToSection(step) {
  const order = ['shipping','payment','review'];
  const currentIdx = order.indexOf(currentStep);
  const targetIdx  = order.indexOf(step);
  if (targetIdx <= currentIdx) setStep(step);
}

// ── SHIPPING STEP → GO TO PAYMENT ──────────────────────────────
function goToPayment() {
  if (!validateShipping()) return;
  setStep('payment');
}

function validateShipping() {
  let ok = true;
  const fields = [
    { id:'sh_name',  err:'err_name',  check: v => v.length > 1 },
    { id:'sh_phone', err:'err_phone', check: v => /^\d{10}$/.test(v.replace(/\s/g,'')) },
    { id:'sh_email', err:'err_email', check: v => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v) },
    { id:'sh_addr1', err:'err_addr1', check: v => v.length > 4 },
    { id:'sh_pin',   err:'err_pin',   check: v => /^\d{6}$/.test(v) },
    { id:'sh_city',  err:'err_city',  check: v => v.length > 1 },
    { id:'sh_state', err:'err_state', check: v => v !== '' },
  ];
  fields.forEach(f => {
    const input = document.getElementById(f.id);
    const errEl = document.getElementById(f.err);
    if (!input) return;
    const pass = f.check(input.value.trim());
    input.classList.toggle('error', !pass);
    if (errEl) errEl.style.display = pass ? 'none' : 'block';
    if (!pass) ok = false;
  });
  return ok;
}

// ── PAYMENT STEP → GO TO REVIEW ────────────────────────────────
function goToReview() {
  // Build review display
  const name  = document.getElementById('sh_name')?.value  || 'Rahul Kumar';
  const phone = document.getElementById('sh_phone')?.value || '+91 98765 43210';
  const addr1 = document.getElementById('sh_addr1')?.value || 'B-204, Rajhans Hostel';
  const city  = document.getElementById('sh_city')?.value  || 'Jaipur';
  const pin   = document.getElementById('sh_pin')?.value   || '302017';
  const state = document.getElementById('sh_state')?.value || 'Rajasthan';

  setText('reviewAddress',
    `<strong>${name}</strong> &nbsp; ${phone}<br />${addr1}, ${city}, ${state} – ${pin}`
  );

  const payLabels = {
    upi:'UPI / Wallet', card:'Credit / Debit Card',
    netbanking:'Net Banking', cod:'Cash on Delivery (+₹30)'
  };
  setText('reviewPayment', `<strong>${payLabels[selectedPayMethod] || 'UPI'}</strong>`);

  const ri = document.getElementById('reviewItems');
  if (ri) ri.innerHTML = cart.map(item => `
    <div style="display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px dashed var(--border);">
      <img src="${item.img}" style="width:48px;height:48px;border-radius:10px;object-fit:cover;" />
      <div style="flex:1;font-size:.85rem;">
        <strong style="font-family:var(--font-head);">${item.name}</strong><br />
        <span style="color:var(--mid);">Qty: ${item.qty}</span>
      </div>
      <strong style="color:var(--primary);font-family:var(--font-head);">₹${(item.price*item.qty).toLocaleString()}</strong>
    </div>`).join('');

  setStep('review');
}

// ── PLACE ORDER ─────────────────────────────────────────────────
function confirmOrder() {
  if (cart.length === 0) { showToast('Your cart is empty!'); return; }
  const btn = document.getElementById('placeOrderBtn');
  if (btn) { btn.classList.add('loading'); btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Processing…'; }



  const payload = {
    pay_method: selectedPayMethod,
    items: cart.map(i => ({
      id: i.id,
      price: i.price,
      qty: i.qty,
      slug: i.slug || '',
      unit: i.unit || ''
    }))
  };

  fetch('place-order.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload)
  })
  .then(r => r.json())
  .then(d => {
    if (!d.success) {
      showToast(d.error || 'Order place karne mein error aaya!');
      if (btn) { btn.classList.remove('loading'); btn.innerHTML = '<i class="fa fa-lock"></i> Place Order Securely'; }
      return;
    }

    // Clear cart
    cart = []; saveCart(); updateCartBadge();

    // Real order_id ab backend se aaya hai
    setText('successOrderId', '#' + d.ord_id);

    document.getElementById('successOverlay').classList.add('show');
    launchConfetti();
  })
  .catch(err => {
    showToast('Network/server error: order save nahi hua!');
    if (btn) { btn.classList.remove('loading'); btn.innerHTML = '<i class="fa fa-lock"></i> Place Order Securely'; }
  });
}

// ── SHIPPING METHOD SELECT ──────────────────────────────────────
function selectShipping(label, method, cost) {
  document.querySelectorAll('.shipping-option').forEach(el => el.classList.remove('selected'));
  label.classList.add('selected');
  shippingCost = cost;
  updateOrderTotal();
}

// ── ADDRESS SELECT ──────────────────────────────────────────────
function selectAddr(card) {
  document.querySelectorAll('.saved-addr-card').forEach(c => c.classList.remove('selected'));
  card.classList.add('selected');
}

// ── PAYMENT METHOD SWITCH ───────────────────────────────────────
function switchPay(method, btn) {
  document.querySelectorAll('.pay-tab').forEach(t => t.classList.remove('active'));
  btn.classList.add('active');
  document.querySelectorAll('.pay-panel').forEach(p => p.classList.remove('active'));
  const panel = document.getElementById('pan_' + method);
  if (panel) panel.classList.add('active');
  selectedPayMethod = method;
  isCod = (method === 'cod');
  updateOrderTotal();
}

function selectUpi(el, id) {
  document.querySelectorAll('.upi-option').forEach(u => u.classList.remove('selected'));
  el.classList.add('selected');
}
function selectBank(el) {
  document.querySelectorAll('.bank-option').forEach(b => b.classList.remove('selected'));
  el.classList.add('selected');
}

// ── CARD FORMATTING ─────────────────────────────────────────────
function formatCard(input) {
  let v = input.value.replace(/\D/g,'').slice(0,16);
  input.value = v.replace(/(.{4})/g,'$1 ').trim();
  const preview = v.padEnd(16,'•').replace(/(.{4})/g,'$1 ').trim();
  document.getElementById('previewNum').textContent = preview;
}
function formatExpiry(input) {
  let v = input.value.replace(/\D/g,'');
  if (v.length >= 2) v = v.slice(0,2) + ' / ' + v.slice(2,4);
  input.value = v;
  document.getElementById('previewExp').textContent = v || 'MM/YY';
}

// ── AUTO-FILL CITY FROM PIN ─────────────────────────────────────
const pinCities = {
  '302017':'Jaipur','302001':'Jaipur','110001':'Delhi',
  '400001':'Mumbai','700001':'Kolkata','600001':'Chennai',
  '560001':'Bangalore','500001':'Hyderabad','380001':'Ahmedabad',
};
function autoFillCity(pin) {
  if (pin.length === 6 && pinCities[pin]) {
    const cityEl = document.getElementById('sh_city');
    if (cityEl && !cityEl.value) cityEl.value = pinCities[pin];
  }
}

// ── COUPON ───────────────────────────────────────────────────────
const COUPONS = { 'STUDY15':.15, 'SAVE10':.10, 'NEWUSER':.20, 'WISHSALE':.10 };

function applySidebarCoupon() {
  const code = document.getElementById('sidebarCoupon')?.value.trim().toUpperCase();
  const msg  = document.getElementById('sideCouponMsg');
  const subtotal = cart.reduce((s,i) => s + i.price * i.qty, 0);

  if (COUPONS[code]) {
    couponDiscount = Math.round(subtotal * COUPONS[code]);
    if (msg) { msg.textContent = `✓ Saved ₹${couponDiscount}!`; msg.className = 'coupon-msg ok'; }
    document.getElementById('couponInputWrap').style.display = 'none';
    const badge = document.getElementById('couponAppliedBadge');
    badge.style.display = 'flex';
    setText('couponAppliedText', `"${code}" — ₹${couponDiscount} saved!`);
    updateOrderTotal();
  } else {
    if (msg) { msg.textContent = '✗ Invalid coupon code'; msg.className = 'coupon-msg err'; }
  }
}

function removeCoupon() {
  couponDiscount = 0;
  document.getElementById('couponInputWrap').style.display = 'block';
  document.getElementById('couponAppliedBadge').style.display = 'none';
  document.getElementById('sidebarCoupon').value = '';
  const msg = document.getElementById('sideCouponMsg');
  if (msg) { msg.textContent = ''; }
  updateOrderTotal();
}

// ── CONFETTI ─────────────────────────────────────────────────────
function launchConfetti() {
  const wrap = document.getElementById('confettiWrap');
  if (!wrap) return;
  const colors = ['#2563eb','#7c3aed','#f97316','#10b981','#fbbf24','#ef4444','#ec4899'];
  for (let i = 0; i < 80; i++) {
    const c = document.createElement('div');
    c.className = 'confetti-piece';
    c.style.cssText = `
      left:${Math.random()*100}vw;
      width:${6+Math.random()*10}px;
      height:${6+Math.random()*10}px;
      background:${colors[Math.floor(Math.random()*colors.length)]};
      border-radius:${Math.random()>.5?'50%':'2px'};
      animation-duration:${1.5+Math.random()*2.5}s;
      animation-delay:${Math.random()*1}s;
      transform:rotate(${Math.random()*360}deg);
    `;
    wrap.appendChild(c);
  }
  setTimeout(() => wrap.innerHTML = '', 4500);
}

</script>
</body>
</html>