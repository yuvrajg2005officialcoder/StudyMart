<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login – StudyMart</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,400&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="style.css" />
</head>
<body class="auth-body">

<div class="auth-page">
  <a href="index.php" class="auth-logo"><span class="brand-icon">📚</span> Study<strong>Mart</strong></a>

  <div class="auth-card" id="authCard">
    <div class="auth-tabs">
      <button class="auth-tab active" onclick="switchTab('login')">Login</button>
      <button class="auth-tab" onclick="switchTab('register')">Register</button>
    </div>

    <!-- LOGIN FORM -->
    <div id="loginForm">
      <h4 class="auth-title">Welcome Back 👋</h4>
      <p class="auth-sub">Login to access your student dashboard</p>
      <div class="auth-form">
        <div class="form-group mb-3">
          <label>Email Address</label>
          <div class="input-icon-wrap">
            <i class="fa fa-envelope"></i>
            <input type="email" id="loginEmail" class="form-control" placeholder="student@college.edu" />
          </div>
        </div>
        <div class="form-group mb-3">
          <label>Password</label>
          <div class="input-icon-wrap">
            <i class="fa fa-lock"></i>
            <input type="password" class="form-control" placeholder="••••••••" id="loginPass" />
            <i class="fa fa-eye toggle-pass" onclick="togglePass('loginPass')"></i>
          </div>
        </div>
        <div class="d-flex justify-content-between mb-3">
          <label><input type="checkbox" /> Remember me</label>
          <a href="#" class="forgot-link">Forgot password?</a>
        </div>
        <button class="btn btn-hero-primary w-100" onclick="loginUser()">Login</button>
        <div class="auth-divider"><span>or continue with</span></div>
        <div class="social-auth">
          <button class="social-btn"><i class="fab fa-google"></i> Google</button>
          <button class="social-btn"><i class="fab fa-facebook"></i> Facebook</button>
        </div>
      </div>
    </div>

    <!-- REGISTER FORM -->
    <div id="registerForm" style="display:none;">
      <h4 class="auth-title">Create Account 🎓</h4>
      <p class="auth-sub">Join 50,000+ students on StudyMart</p>
      <div class="auth-form">
        <div class="row g-3 mb-3">
          <div class="col-6">
            <label>First Name</label>
            <input type="text" id="regFirstName" class="form-control" placeholder="Rahul" />
          </div>
          <div class="col-6">
            <label>Last Name</label>
            <input type="text" id="regLastName" class="form-control" placeholder="Kumar" />
          </div>
        </div>
        <div class="form-group mb-3">
          <label>Student Email</label>
          <div class="input-icon-wrap">
            <i class="fa fa-envelope"></i>
            <input type="email" id="regEmail" class="form-control" placeholder="your@college.edu" />
          </div>
        </div>
        <div class="form-group mb-3">
          <label>Mobile No.</label>
          <div class="input-icon-wrap">
            <i class="fa fa-phone"></i>
            <input type="text" id="regMobile" class="form-control" placeholder="9876543210" maxlength="15" />
          </div>
        </div>
        <div class="form-group mb-3">
          <label>College / University</label>
          <input type="text" id="regCollege" class="form-control" placeholder="IIT Delhi" />
        </div>
        <div class="row g-3 mb-3">
          <div class="col-4">
            <label>State</label>
            <input type="text" id="regState" class="form-control" placeholder="Rajasthan" />
          </div>
          <div class="col-4">
            <label>City</label>
            <input type="text" id="regCity" class="form-control" placeholder="Jaipur" />
          </div>
          <div class="col-4">
            <label>Area</label>
            <input type="text" id="regArea" class="form-control" placeholder="Malviya Nagar" />
          </div>
        </div>
        <div class="form-group mb-3">
          <label>Full Address</label>
          <textarea id="regAddress" class="form-control" rows="2" placeholder="Poora address..."></textarea>
        </div>
        <div class="form-group mb-3">
          <label>Password</label>
          <div class="input-icon-wrap">
            <i class="fa fa-lock"></i>
            <input type="password" class="form-control" placeholder="Create password" id="regPass" />
            <i class="fa fa-eye toggle-pass" onclick="togglePass('regPass')"></i>
          </div>
        </div>
        <div class="form-check mb-3">
          <input type="checkbox" class="form-check-input" id="terms" />
          <label class="form-check-label" for="terms">I agree to <a href="#">Terms & Conditions</a></label>
        </div>
        <button class="btn btn-hero-primary w-100" onclick="registerUser()">Create Account</button>
      </div>
    </div>
  </div>

  <p class="auth-back"><a href="index.php">← Back to Home</a></p>
</div>

<!-- Toast -->
<div id="authToast" style="position:fixed;top:20px;right:20px;z-index:9999;min-width:280px;border-radius:12px;padding:14px 18px;font-size:13.5px;font-weight:600;box-shadow:0 8px 24px rgba(0,0,0,0.15);display:none;align-items:center;gap:10px;"></div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
<script src="app.js"></script>
<script>

/* ── TAB SWITCH ───────────────────────── */
function switchTab(tab) {
  document.querySelectorAll('.auth-tab').forEach(t => t.classList.remove('active'));
  event.target.classList.add('active');
  document.getElementById('loginForm').style.display    = tab === 'login'    ? 'block' : 'none';
  document.getElementById('registerForm').style.display = tab === 'register' ? 'block' : 'none';
}

/* ── PASSWORD TOGGLE ──────────────────── */
function togglePass(id) {
  const el = document.getElementById(id);
  el.type = el.type === 'password' ? 'text' : 'password';
}

/* ── TOAST ────────────────────────────── */
function showAuthToast(msg, type) {
  var toast = document.getElementById('authToast');
  var ok    = type === 'success';
  toast.style.background = ok ? '#e6f9f0' : '#fdecea';
  toast.style.color      = ok ? '#1a9e5f' : '#e53935';
  toast.style.border     = '1.5px solid ' + (ok ? '#1a9e5f' : '#e53935');
  toast.innerHTML        = '<span style="font-size:18px;">' + (ok ? '✔' : '✖') + '</span> ' + msg;
  toast.style.display    = 'flex';
  clearTimeout(toast._t);
  toast._t = setTimeout(function() { toast.style.display = 'none'; }, 3000);
}

/* ── LOGIN ────────────────────────────── */
function loginUser() {
  var email = document.getElementById('loginEmail').value.trim();
  var pass  = document.getElementById('loginPass').value.trim();

  if (!email || !pass) {
    showAuthToast('Email aur Password dono required hain!', 'error');
    return;
  }

  var fd = new FormData();
  fd.append('action',   'login');
  fd.append('email',    email);
  fd.append('password', pass);

  fetch('auth.php', { method: 'POST', body: fd })
    .then(function(r) { return r.json(); })
    .then(function(res) {
      if (res.success) {
        showAuthToast(res.message, 'success');
        setTimeout(function() { window.location.href = res.redirect; }, 1500);
      } else {
        showAuthToast(res.message, 'error');
      }
    })
    .catch(function() { showAuthToast('Server error aaya!', 'error'); });
}

/* ── REGISTER ─────────────────────────── */
function registerUser() {
  var fname   = document.getElementById('regFirstName').value.trim();
  var lname   = document.getElementById('regLastName').value.trim();
  var email   = document.getElementById('regEmail').value.trim();
  var mobile  = document.getElementById('regMobile').value.trim();
  var college = document.getElementById('regCollege').value.trim();
  var state   = document.getElementById('regState').value.trim();
  var city    = document.getElementById('regCity').value.trim();
  var area    = document.getElementById('regArea').value.trim();
  var address = document.getElementById('regAddress').value.trim();
  var pass    = document.getElementById('regPass').value.trim();
  var terms   = document.getElementById('terms').checked;

  if (!fname || !lname || !email || !mobile || !pass) {
    showAuthToast('Saare required fields fill karo!', 'error');
    return;
  }
  if (!terms) {
    showAuthToast('Terms & Conditions accept karo!', 'error');
    return;
  }

  var fd = new FormData();
  fd.append('action',     'register');
  fd.append('first_name', fname);
  fd.append('last_name',  lname);
  fd.append('email',      email);
  fd.append('mobile_no',  mobile);
  fd.append('college',    college);
  fd.append('state',      state);
  fd.append('city',       city);
  fd.append('area',       area);
  fd.append('address',    address);
  fd.append('password',   pass);

  fetch('auth.php', { method: 'POST', body: fd })
    .then(function(r) { return r.json(); })
    .then(function(res) {
      if (res.success) {
        showAuthToast(res.message, 'success');
        setTimeout(function() {
          document.querySelectorAll('.auth-tab')[0].click();
        }, 1800);
      } else {
        showAuthToast(res.message, 'error');
      }
    })
    .catch(function() { showAuthToast('Server error aaya!', 'error'); });
}

</script>
</body>
</html>