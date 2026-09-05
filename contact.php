<?php
require_once 'config.php';

// ── Handle AJAX form submission ──────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send_contact') {
    header('Content-Type: application/json');

    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (!$name || !$email || !$subject || !$message) {
        echo json_encode(['success' => false, 'error' => 'Sab fields fill karo.']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'error' => 'Email sahi se daalo.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $subject, $message]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error, try again.']);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Contact – StudyMart</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,400&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="style.css" />
</head>
<body>

<div class="announce-bar"><span>🎓 Student Exclusive: Extra 15% off with code <strong>STUDY15</strong></span></div>

<nav class="navbar navbar-expand-lg sticky-top" id="mainNav">
  <div class="container">
    <a class="navbar-brand" href="index.php"><span class="brand-icon">📚</span><span>Study<strong>Mart</strong></span></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navCollapse">
      <span class="hamburger-icon"><i class="fa fa-bars"></i></span>
    </button>
    <div class="collapse navbar-collapse" id="navCollapse">
      <ul class="navbar-nav mx-auto gap-1">
        <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="shop.php">Shop</a></li>
        <li class="nav-item"><a class="nav-link active" href="contact.php">Contact</a></li>
      </ul>
      <div class="nav-actions d-flex align-items-center gap-3">
        <a href="cart.php" class="nav-icon-btn cart-btn"><i class="fa fa-shopping-cart"></i><span class="cart-badge" id="cartCount">0</span></a>
        <a href="login.php" class="btn btn-sm btn-login">Login</a>
      </div>
    </div>
  </div>
</nav>

<div class="page-header">
  <div class="container">
    <h1>Contact Us</h1>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
        <li class="breadcrumb-item active">Contact</li>
      </ol>
    </nav>
  </div>
</div>

<section class="contact-section py-5">
  <div class="container">
    <div class="row g-5 align-items-start">
      <div class="col-lg-5">
        <h3 class="mb-4">Get in Touch</h3>
        <div class="contact-info-cards">
          <div class="contact-info-card">
            <div class="ci-icon" style="background:#FF6B6B22;color:#FF6B6B;"><i class="fa fa-map-marker-alt"></i></div>
            <div>
              <strong>Our Address</strong>
              <p>42, Edu Street, Jaipur, Rajasthan 302001</p>
            </div>
          </div>
          <div class="contact-info-card">
            <div class="ci-icon" style="background:#4ECDC422;color:#4ECDC4;"><i class="fa fa-phone"></i></div>
            <div>
              <strong>Phone</strong>
              <p>+91 98765 43210</p>
            </div>
          </div>
          <div class="contact-info-card">
            <div class="ci-icon" style="background:#C9B1FF22;color:#C9B1FF;"><i class="fa fa-envelope"></i></div>
            <div>
              <strong>Email</strong>
              <p>hello@studymart.in</p>
            </div>
          </div>
          <div class="contact-info-card">
            <div class="ci-icon" style="background:#FFE66D22;color:#b8860b;"><i class="fa fa-clock"></i></div>
            <div>
              <strong>Business Hours</strong>
              <p>Mon–Sat: 9AM–6PM IST</p>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-7">
        <div class="contact-form-card">
          <h4 class="mb-4">Send a Message</h4>
          <div id="contactAlert" style="display:none;" class="mb-3"></div>
          <div class="row g-3">
            <div class="col-md-6">
              <label>Your Name</label>
              <input type="text" class="form-control" id="cName" placeholder="Rahul Kumar" />
            </div>
            <div class="col-md-6">
              <label>Email Address</label>
              <input type="email" class="form-control" id="cEmail" placeholder="rahul@college.edu" />
            </div>
            <div class="col-12">
              <label>Subject</label>
              <input type="text" class="form-control" id="cSubject" placeholder="How can we help?" />
            </div>
            <div class="col-12">
              <label>Message</label>
              <textarea class="form-control" id="cMessage" rows="5" placeholder="Write your message here…"></textarea>
            </div>
            <div class="col-12">
              <button class="btn btn-hero-primary" id="sendBtn" onclick="submitContact()">Send Message <i class="fa fa-paper-plane ms-2"></i></button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<footer class="site-footer pt-4 pb-3">
  <div class="container">
    <div class="footer-bottom"><span>© 2025 StudyMart. All rights reserved.</span></div>
  </div>
</footer>

<div class="cart-toast" id="cartToast"><i class="fa fa-check-circle"></i> Sent!</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
<script src="app.js"></script>
<script>
function submitContact() {
  const name    = document.getElementById('cName').value.trim();
  const email   = document.getElementById('cEmail').value.trim();
  const subject = document.getElementById('cSubject').value.trim();
  const message = document.getElementById('cMessage').value.trim();
  const alertBox = document.getElementById('contactAlert');
  const btn = document.getElementById('sendBtn');

  alertBox.style.display = 'none';

  if (!name || !email || !subject || !message) {
    alertBox.className = 'alert alert-danger mb-3';
    alertBox.textContent = 'Please fill in all fields.';
    alertBox.style.display = 'block';
    return;
  }

  btn.disabled = true;
  btn.innerHTML = 'Sending...';

  const formData = new FormData();
  formData.append('action', 'send_contact');
  formData.append('name', name);
  formData.append('email', email);
  formData.append('subject', subject);
  formData.append('message', message);

  fetch('contact.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    btn.disabled = false;
    btn.innerHTML = 'Send Message <i class="fa fa-paper-plane ms-2"></i>';

    if (data.success) {
      alertBox.className = 'alert alert-success mb-3';
      alertBox.textContent = '✅ Message sent successfully! We\'ll get back to you soon.';
      alertBox.style.display = 'block';

      // Clear form
      document.getElementById('cName').value = '';
      document.getElementById('cEmail').value = '';
      document.getElementById('cSubject').value = '';
      document.getElementById('cMessage').value = '';

      showToast();
    } else {
      alertBox.className = 'alert alert-danger mb-3';
      alertBox.textContent = '❌ ' + (data.error || 'Something went wrong.');
      alertBox.style.display = 'block';
    }
  })
  .catch(err => {
    btn.disabled = false;
    btn.innerHTML = 'Send Message <i class="fa fa-paper-plane ms-2"></i>';
    alertBox.className = 'alert alert-danger mb-3';
    alertBox.textContent = '❌ Network error. Please try again.';
    alertBox.style.display = 'block';
  });
}
</script>
</body>
</html>