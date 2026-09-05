<?php
session_start();
if (isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel – Login</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito:400,600,700,800,900">
    <link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Nunito', sans-serif;
            background: #0f1322;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            top: -120px; left: -120px;
            width: 420px; height: 420px;
            background: radial-gradient(circle, rgba(67,97,238,0.22) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }
        body::after {
            content: '';
            position: fixed;
            bottom: -100px; right: -100px;
            width: 380px; height: 380px;
            background: radial-gradient(circle, rgba(108,143,255,0.18) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .auth-wrapper {
            width: 100%;
            max-width: 440px;
            padding: 20px;
            position: relative;
            z-index: 1;
        }

        .brand { text-align: center; margin-bottom: 32px; }
        .brand-icon {
            width: 60px; height: 60px;
            background: linear-gradient(135deg, #4361ee, #6c8fff);
            border-radius: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 12px;
            box-shadow: 0 8px 24px rgba(67,97,238,0.4);
        }
        .brand-icon i { font-size: 28px; color: #fff; }
        .brand h1 { color: #fff; font-size: 22px; font-weight: 800; letter-spacing: 0.3px; }
        .brand p { color: #6c7a9c; font-size: 13px; margin-top: 4px; }

        .auth-card {
            background: #161b2e;
            border-radius: 20px;
            padding: 36px 36px 32px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.4), 0 0 0 1px rgba(255,255,255,0.05);
        }

        .tab-switcher {
            display: flex;
            background: #0f1322;
            border-radius: 12px;
            padding: 4px;
            margin-bottom: 28px;
        }
        .tab-btn {
            flex: 1; padding: 10px; border: none;
            background: transparent; color: #6c7a9c;
            font-family: 'Nunito', sans-serif; font-size: 14px; font-weight: 700;
            border-radius: 9px; cursor: pointer; transition: all 0.2s;
        }
        .tab-btn.active {
            background: #fff; color: #1a1f36;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }

        .form-panel { display: none; }
        .form-panel.active { display: block; }

        .panel-title { color: #fff; font-size: 18px; font-weight: 800; margin-bottom: 4px; }
        .panel-subtitle { color: #6c7a9c; font-size: 12.5px; margin-bottom: 24px; }

        .input-group { margin-bottom: 18px; }
        .input-group label {
            display: block; color: #8a96b0; font-size: 12px; font-weight: 700;
            letter-spacing: 0.8px; text-transform: uppercase; margin-bottom: 8px;
        }
        .input-wrap { position: relative; }
        .input-wrap i {
            position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
            color: #4a5578; font-size: 18px; pointer-events: none; transition: color 0.2s;
        }
        .input-wrap input {
            width: 100%; padding: 12px 14px 12px 42px;
            background: #0f1322; border: 1.5px solid rgba(255,255,255,0.07);
            border-radius: 10px; color: #fff; font-family: 'Nunito', sans-serif;
            font-size: 14px; font-weight: 600; outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .input-wrap input::placeholder { color: #4a5578; }
        .input-wrap input:focus {
            border-color: #4361ee;
            box-shadow: 0 0 0 3px rgba(67,97,238,0.15);
        }
        .input-wrap:focus-within i { color: #4361ee; }

        .toggle-pw {
            position: absolute; right: 14px; top: 50%; transform: translateY(-50%);
            color: #4a5578; font-size: 18px; cursor: pointer;
            pointer-events: all; transition: color 0.2s;
        }
        .toggle-pw:hover { color: #8a96b0; }

        .row-flex {
            display: flex; align-items: center;
            justify-content: space-between; margin-bottom: 24px;
        }
        .remember {
            display: flex; align-items: center; gap: 8px;
            color: #8a96b0; font-size: 13px; font-weight: 600; cursor: pointer;
        }
        .remember input[type="checkbox"] {
            width: 16px; height: 16px; accent-color: #4361ee; cursor: pointer;
        }
        .forgot-link {
            color: #4361ee; font-size: 13px; font-weight: 700;
            text-decoration: none; transition: color 0.2s;
        }
        .forgot-link:hover { color: #6c8fff; }

        .btn-submit {
            width: 100%; padding: 13px;
            background: linear-gradient(135deg, #4361ee, #6c8fff);
            border: none; border-radius: 12px; color: #fff;
            font-family: 'Nunito', sans-serif; font-size: 15px; font-weight: 800;
            cursor: pointer; box-shadow: 0 6px 20px rgba(67,97,238,0.35);
            transition: all 0.2s; letter-spacing: 0.3px;
        }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 10px 28px rgba(67,97,238,0.45); }
        .btn-submit:active { transform: translateY(0); }

        .divider {
            display: flex; align-items: center; gap: 12px; margin: 22px 0;
        }
        .divider::before, .divider::after {
            content: ''; flex: 1; height: 1px; background: rgba(255,255,255,0.07);
        }
        .divider span {
            color: #4a5578; font-size: 12px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 1px;
        }

        .btn-google {
            width: 100%; padding: 12px;
            background: rgba(255,255,255,0.05); border: 1.5px solid rgba(255,255,255,0.09);
            border-radius: 12px; color: #c8cfe8; font-family: 'Nunito', sans-serif;
            font-size: 14px; font-weight: 700; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            gap: 10px; transition: all 0.2s;
        }
        .btn-google:hover { background: rgba(255,255,255,0.09); border-color: rgba(255,255,255,0.15); }
        .btn-google svg { width: 20px; height: 20px; flex-shrink: 0; }

        .terms-note {
            color: #4a5578; font-size: 11.5px; text-align: center;
            margin-top: 18px; line-height: 1.6;
        }
        .terms-note a { color: #6c8fff; text-decoration: none; font-weight: 700; }
        .terms-note a:hover { text-decoration: underline; }

        .alert {
            padding: 10px 14px; border-radius: 8px; font-size: 13px;
            font-weight: 600; margin-bottom: 18px; display: none;
        }
        .alert.error {
            background: rgba(229,57,53,0.12); border: 1px solid rgba(229,57,53,0.25); color: #ff6b6b;
        }
        .alert.success {
            background: rgba(38,166,91,0.12); border: 1px solid rgba(38,166,91,0.25); color: #4cda7e;
        }

        /* ─── Warning Modal ─── */
        .modal-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,0.7); backdrop-filter: blur(6px);
            z-index: 9999; align-items: center; justify-content: center;
        }
        .modal-overlay.show { display: flex; }

        .modal-box {
            background: #161b2e; border: 1px solid rgba(255,255,255,0.08);
            border-radius: 20px; padding: 36px 32px 28px;
            max-width: 380px; width: 90%; text-align: center;
            box-shadow: 0 24px 64px rgba(0,0,0,0.5);
            animation: modalIn 0.25s ease;
        }
        @keyframes modalIn {
            from { opacity: 0; transform: scale(0.92) translateY(12px); }
            to   { opacity: 1; transform: scale(1) translateY(0); }
        }

        .modal-icon {
            width: 64px; height: 64px;
            background: rgba(239,68,68,0.12);
            border-radius: 50%;
            display: inline-flex; align-items: center; justify-content: center;
            margin-bottom: 18px;
        }
        .modal-icon i { font-size: 32px; color: #ef4444; }

        .modal-title { color: #fff; font-size: 18px; font-weight: 800; margin-bottom: 8px; }
        .modal-msg { color: #8a96b0; font-size: 13.5px; line-height: 1.7; margin-bottom: 28px; }
        .modal-msg strong { color: #c8cfe8; }

        .modal-actions { display: flex; gap: 10px; }
        .btn-stay {
            flex: 1; padding: 12px;
            background: linear-gradient(135deg, #4361ee, #6c8fff);
            border: none; border-radius: 10px; color: #fff;
            font-family: 'Nunito', sans-serif; font-size: 14px; font-weight: 800;
            cursor: pointer; box-shadow: 0 4px 14px rgba(67,97,238,0.35); transition: all 0.2s;
        }
        .btn-stay:hover { transform: translateY(-1px); }

        .btn-leave {
            flex: 1; padding: 12px;
            background: rgba(255,255,255,0.05); border: 1.5px solid rgba(255,255,255,0.1);
            border-radius: 10px; color: #8a96b0; font-family: 'Nunito', sans-serif;
            font-size: 14px; font-weight: 700; cursor: pointer; transition: all 0.2s;
        }
        .btn-leave:hover {
            background: rgba(239,68,68,0.1); border-color: rgba(239,68,68,0.3); color: #ef4444;
        }
    </style>
</head>
<body>

<!-- ─── Warning Modal ─── -->
<div class="modal-overlay" id="warningModal">
    <div class="modal-box">
        <div class="modal-icon">
            <i class="las la-exclamation-triangle"></i>
        </div>
        <div class="modal-title">Hold On! 🚨 Access Restricted</div>
        <div class="modal-msg">
            You cannot proceed without <strong>Login or Registration</strong>.<br><br>
            If you leave this page, you will <strong>lose access</strong> to the Admin Panel.
        </div>
        <div class="modal-actions">
            <button class="btn-stay" onclick="stayOnPage()">
                <i class="las la-arrow-left" style="margin-right:5px;"></i> Go Back
            </button>
            <button class="btn-leave" onclick="confirmLeave()">
                Leave Page
            </button>
        </div>
    </div>
</div>

<div class="auth-wrapper">

    <!-- Brand -->
    <div class="brand">
        <div class="brand-icon">
            <i class="las la-store"></i>
        </div>
        <h1>Admin Panel</h1>
        <p>E-Commerce Management System</p>
    </div>

    <!-- Card -->
    <div class="auth-card">

        <div class="tab-switcher">
            <button class="tab-btn active" id="tab-login" onclick="switchTab('login')">Login</button>
            <button class="tab-btn" id="tab-register" onclick="switchTab('register')">Register</button>
        </div>

        <!-- ===== LOGIN PANEL ===== -->
        <div class="form-panel active" id="panel-login">
            <div class="panel-title">Welcome Back 👋</div>
            <div class="panel-subtitle">Enter your email address and password to continue</div>

            <div class="alert error" id="login-error"></div>

            <form method="POST" action="login-process.php" onsubmit="return validateLogin()">
                <div class="input-group">
                    <label>Email Address</label>
                    <div class="input-wrap">
                        <i class="las la-envelope"></i>
                        <input type="email" name="email" id="login-email" placeholder="admin@example.com" required>
                    </div>
                </div>

                <div class="input-group">
                    <label>Password</label>
                    <div class="input-wrap">
                        <i class="las la-lock"></i>
                        <input type="password" name="password" id="login-password" placeholder="••••••••" required>
                        <span class="toggle-pw las la-eye" onclick="togglePw('login-password', this)"></span>
                    </div>
                </div>

                <div class="row-flex">
                    <label class="remember">
                        <input type="checkbox" name="remember"> Remember me
                    </label>
                    <a href="forgot-password.php" class="forgot-link">Forgot password?</a>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="las la-sign-in-alt" style="margin-right:6px;"></i> Sign In
                </button>
            </form>

            <div class="divider"><span>or</span></div>

            <button class="btn-google" onclick="googleLogin()">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                </svg>
                Continue with Google
            </button>

            <div class="terms-note">
                Don't have an account? <a href="#" onclick="switchTab('register')">Register here</a>
            </div>
        </div>

        <!-- ===== REGISTER PANEL ===== -->
        <div class="form-panel" id="panel-register">
            <div class="panel-title">Create an Account ✨</div>
            <div class="panel-subtitle">Register a new admin account to get started</div>

            <div class="alert error" id="reg-error"></div>
            <div class="alert success" id="reg-success"></div>

            <form method="POST" action="register-process.php" onsubmit="return validateRegister()">
                <div class="input-group">
                    <label>Full Name</label>
                    <div class="input-wrap">
                        <i class="las la-user"></i>
                        <input type="text" name="name" id="reg-name" placeholder="Your full name" required>
                    </div>
                </div>

                <div class="input-group">
                    <label>Email Address</label>
                    <div class="input-wrap">
                        <i class="las la-envelope"></i>
                        <input type="email" name="email" id="reg-email" placeholder="yourname@gmail.com" required>
                    </div>
                </div>

                <div class="input-group">
                    <label>Password</label>
                    <div class="input-wrap">
                        <i class="las la-lock"></i>
                        <input type="password" name="password" id="reg-password" placeholder="Min. 8 characters" required>
                        <span class="toggle-pw las la-eye" onclick="togglePw('reg-password', this)"></span>
                    </div>
                </div>

                <div class="input-group">
                    <label>Confirm Password</label>
                    <div class="input-wrap">
                        <i class="las la-lock"></i>
                        <input type="password" name="confirm_password" id="reg-confirm" placeholder="Re-enter your password" required>
                        <span class="toggle-pw las la-eye" onclick="togglePw('reg-confirm', this)"></span>
                    </div>
                </div>

                <button type="submit" class="btn-submit" style="margin-top:4px;">
                    <i class="las la-user-plus" style="margin-right:6px;"></i> Create Account
                </button>
            </form>

            <div class="divider"><span>or</span></div>

            <button class="btn-google" onclick="googleLogin()">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                </svg>
                Continue with Google
            </button>

            <div class="terms-note">
                Already have an account? <a href="#" onclick="switchTab('login')">Sign in here</a><br>
                By registering, you agree to our <a href="#">Terms of Service</a>.
            </div>
        </div>

    </div>
</div>

<script>
    // ─────────────────────────────────────────────
    //  WARNING MODAL LOGIC
    // ─────────────────────────────────────────────
    let pendingNavUrl   = null;
    let allowNavigation = false;
    let formSubmitting  = false;

    // 1) Browser native dialog on tab close / reload / back
    window.addEventListener('beforeunload', function (e) {
        if (allowNavigation || formSubmitting) return;
        e.preventDefault();
        e.returnValue = 'You are not logged in. Are you sure you want to leave this page?';
        return e.returnValue;
    });

    // 2) Custom modal on first page load
    window.addEventListener('load', function () {
        setTimeout(() => showModal(), 600);
    });

    // 3) Intercept any anchor link click inside the page
    document.addEventListener('click', function (e) {
        const anchor = e.target.closest('a');
        if (!anchor) return;
        const href = anchor.getAttribute('href');
        if (!href || href === '#' || href.startsWith('javascript')) return;
        if (!allowNavigation) {
            e.preventDefault();
            pendingNavUrl = href;
            showModal();
        }
    });

    // Disable warning on form submit
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', () => { formSubmitting = true; });
    });

    function showModal() {
        document.getElementById('warningModal').classList.add('show');
    }

    function stayOnPage() {
        document.getElementById('warningModal').classList.remove('show');
        pendingNavUrl = null;
    }

    function confirmLeave() {
        allowNavigation = true;
        document.getElementById('warningModal').classList.remove('show');
        if (pendingNavUrl) {
            window.location.href = pendingNavUrl;
        } else {
            history.back();
        }
    }

    // ─────────────────────────────────────────────
    //  EXISTING FUNCTIONS
    // ─────────────────────────────────────────────

    function switchTab(tab) {
        document.querySelectorAll('.form-panel').forEach(p => p.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.getElementById('panel-' + tab).classList.add('active');
        document.getElementById('tab-' + tab).classList.add('active');
    }

    function togglePw(inputId, icon) {
        const input = document.getElementById(inputId);
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('la-eye', 'la-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('la-eye-slash', 'la-eye');
        }
    }

    function validateLogin() {
        const email = document.getElementById('login-email').value.trim();
        const pass  = document.getElementById('login-password').value.trim();
        const err   = document.getElementById('login-error');
        if (!email || !pass) { showAlert(err, 'Email address and password are required.'); return false; }
        if (!email.includes('@')) { showAlert(err, 'Please enter a valid email address.'); return false; }
        err.style.display = 'none';
        return true;
    }

    function validateRegister() {
        const name    = document.getElementById('reg-name').value.trim();
        const email   = document.getElementById('reg-email').value.trim();
        const pass    = document.getElementById('reg-password').value.trim();
        const confirm = document.getElementById('reg-confirm').value.trim();
        const err     = document.getElementById('reg-error');
        if (!name || !email || !pass || !confirm) { showAlert(err, 'Please fill in all fields.'); return false; }
        if (!email.includes('@')) { showAlert(err, 'Please enter a valid email address.'); return false; }
        if (pass.length < 8) { showAlert(err, 'Password must be at least 8 characters long.'); return false; }
        if (pass !== confirm) { showAlert(err, 'Passwords do not match. Please try again.'); return false; }
        err.style.display = 'none';
        return true;
    }

    function showAlert(el, msg) {
        el.textContent = msg;
        el.style.display = 'block';
        setTimeout(() => el.style.display = 'none', 4000);
    }

    function googleLogin() {
        alert('Please set up a Google Client ID in your backend to enable Google OAuth.');
    }
</script>

</body>
</html>