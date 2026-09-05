<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>About Us – StudyMart</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,400&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="style.css" />
  <style>

    /* ── ABOUT PAGE STYLES ── */

    /* HERO */
    .about-hero {
      background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #312e81 100%);
      color: #fff;
      padding: 90px 0 100px;
      position: relative;
      overflow: hidden;
    }
    .about-hero::before {
      content:'';
      position:absolute;inset:0;
      background: radial-gradient(ellipse at 80% 40%, rgba(99,102,241,.3) 0%, transparent 65%);
    }
    .about-hero-dots {
      position:absolute;inset:0;
      background-image: radial-gradient(rgba(255,255,255,.05) 1px, transparent 1px);
      background-size: 26px 26px;
    }
    .about-hero-tag {
      display:inline-flex;align-items:center;gap:8px;
      background:rgba(255,255,255,.1);
      border:1px solid rgba(255,255,255,.2);
      border-radius:50px;padding:6px 18px;
      font-size:.8rem;font-weight:700;color:rgba(255,255,255,.85);
      letter-spacing:.5px;margin-bottom:20px;
      font-family:var(--font-head);
    }
    .about-hero h1 {
      font-family:var(--font-head);
      font-size:clamp(2.4rem,6vw,4rem);
      font-weight:800;line-height:1.1;margin-bottom:20px;
    }
    .about-hero h1 span { color:#fbbf24; }
    .about-hero p {
      color:rgba(255,255,255,.7);font-size:1.08rem;
      max-width:560px;line-height:1.8;margin-bottom:36px;
    }
    .hero-btn-row { display:flex;gap:12px;flex-wrap:wrap; }

    .about-hero-image-wrap {
      position:relative;display:flex;align-items:center;justify-content:center;
    }
    .about-hero-main-img {
      width:340px;height:380px;object-fit:cover;
      border-radius:28px;box-shadow:0 24px 64px rgba(0,0,0,.4);
      position:relative;z-index:1;
    }
    .about-img-ring {
      position:absolute;
      width:400px;height:400px;
      border:2px solid rgba(255,255,255,.1);
      border-radius:50%;
      animation:spinSlow 18s linear infinite;
    }
    .about-img-ring::before {
      content:'';position:absolute;top:-6px;left:50%;
      transform:translateX(-50%);
      width:12px;height:12px;
      background:#fbbf24;border-radius:50%;
    }
    @keyframes spinSlow { to { transform:rotate(360deg); } }
    .about-float-badge {
      position:absolute;
      background:rgba(255,255,255,.1);
      backdrop-filter:blur(10px);
      border:1px solid rgba(255,255,255,.15);
      border-radius:14px;
      padding:12px 16px;color:#fff;font-size:.82rem;z-index:2;
      animation:floatB 4s ease-in-out infinite;
    }
    .afb-1{top:10px;right:10px;}
    .afb-2{bottom:20px;left:0px;animation-delay:.8s;}
    @keyframes floatB{0%,100%{transform:translateY(0)}50%{transform:translateY(-8px)}}
    .afb-icon{font-size:1.4rem;margin-bottom:4px;}
    .afb-val{font-family:var(--font-head);font-weight:800;font-size:1.1rem;}
    .afb-label{font-size:.7rem;opacity:.7;}

    /* STATS BAR */
    .stats-bar {
      background:#fff;
      border-bottom:1px solid var(--border);
      padding:32px 0;
    }
    .stat-item {
      text-align:center;padding:0 20px;
      border-right:1px solid var(--border);
    }
    .stat-item:last-child{border-right:none;}
    .stat-num {
      font-family:var(--font-head);font-size:2.4rem;font-weight:800;
      color:var(--primary);display:block;line-height:1;margin-bottom:6px;
    }
    .stat-label{font-size:.85rem;color:var(--mid);font-weight:500;}

    /* MISSION SECTION */
    .mission-section{background:var(--light);}
    .mission-img-wrap{position:relative;}
    .mission-img {
      width:100%;height:440px;object-fit:cover;
      border-radius:24px;box-shadow:var(--shadow-lg);
    }
    .mission-img-badge {
      position:absolute;bottom:24px;left:24px;
      background:#fff;border-radius:16px;
      padding:16px 20px;box-shadow:var(--shadow-lg);
      display:flex;align-items:center;gap:12px;
    }
    .mib-icon{font-size:1.8rem;}
    .mib-val{font-family:var(--font-head);font-weight:800;font-size:1.1rem;color:var(--primary);}
    .mib-label{font-size:.78rem;color:var(--mid);}
    .mission-text h2{font-family:var(--font-head);font-size:clamp(1.7rem,3vw,2.4rem);font-weight:800;margin-bottom:18px;}
    .mission-text h2 span{color:var(--primary);}
    .mission-text p{color:var(--mid);line-height:1.8;font-size:.97rem;margin-bottom:16px;}
    .mission-points{list-style:none;padding:0;margin:24px 0;}
    .mission-points li{
      display:flex;align-items:flex-start;gap:12px;
      padding:10px 0;border-bottom:1px dashed var(--border);
      font-size:.93rem;color:var(--dark);
    }
    .mission-points li:last-child{border-bottom:none;}
    .mp-icon{
      width:32px;height:32px;background:#eff6ff;color:var(--primary);
      border-radius:8px;display:flex;align-items:center;justify-content:center;
      font-size:.85rem;flex-shrink:0;margin-top:1px;
    }

    /* VALUES SECTION */
    .values-section{background:#fff;}
    .value-card{
      background:var(--light);border-radius:20px;
      border:1.5px solid var(--border);padding:32px 24px;
      text-align:center;transition:var(--transition);
      position:relative;overflow:hidden;
    }
    .value-card::before{
      content:'';position:absolute;inset:0;
      background:var(--vc,var(--primary));opacity:0;
      transition:var(--transition);border-radius:20px;
    }
    .value-card:hover{transform:translateY(-6px);box-shadow:var(--shadow-lg);}
    .value-card:hover::before{opacity:.05;}
    .value-card:hover .vc-icon{transform:scale(1.15) rotate(-4deg);}
    .vc-icon{
      width:64px;height:64px;border-radius:18px;
      display:flex;align-items:center;justify-content:center;
      font-size:1.7rem;margin:0 auto 20px;transition:var(--transition);
      position:relative;
    }
    .value-card h5{font-family:var(--font-head);font-weight:800;margin-bottom:10px;position:relative;}
    .value-card p{color:var(--mid);font-size:.88rem;line-height:1.7;position:relative;}

    /* TEAM SECTION */
    .team-section{background:var(--light);}
    .team-card{
      background:#fff;border-radius:20px;
      border:1.5px solid var(--border);overflow:hidden;
      transition:var(--transition);text-align:center;
    }
    .team-card:hover{transform:translateY(-6px);box-shadow:var(--shadow-lg);}
    .team-img-wrap{
      position:relative;overflow:hidden;
      height:220px;background:linear-gradient(135deg,#eff6ff,#f0fdf4);
      display:flex;align-items:center;justify-content:center;
    }
    .team-avatar{
      width:120px;height:120px;border-radius:50%;
      border:4px solid #fff;box-shadow:var(--shadow);
      object-fit:cover;
    }
    .team-avatar-placeholder{
      width:120px;height:120px;border-radius:50%;
      border:4px solid #fff;box-shadow:var(--shadow);
      display:flex;align-items:center;justify-content:center;
      font-family:var(--font-head);font-size:2rem;font-weight:800;
      color:#fff;
    }
    .team-overlay{
      position:absolute;inset:0;
      background:rgba(37,99,235,.85);
      display:flex;align-items:center;justify-content:center;
      gap:12px;opacity:0;transition:var(--transition);
    }
    .team-card:hover .team-overlay{opacity:1;}
    .team-social{
      width:40px;height:40px;background:rgba(255,255,255,.2);
      border-radius:50%;display:flex;align-items:center;justify-content:center;
      color:#fff;text-decoration:none;font-size:.9rem;
      transition:var(--transition);
    }
    .team-social:hover{background:#fff;color:var(--primary);}
    .team-body{padding:20px;}
    .team-name{font-family:var(--font-head);font-weight:800;font-size:1rem;margin-bottom:4px;}
    .team-role{color:var(--primary);font-size:.82rem;font-weight:600;margin-bottom:8px;}
    .team-bio{color:var(--mid);font-size:.82rem;line-height:1.6;}

    /* JOURNEY / TIMELINE */
    .journey-section{background:#fff;}
    .timeline{position:relative;padding:0;}
    .timeline::before{
      content:'';position:absolute;left:50%;top:0;bottom:0;
      width:2px;background:var(--border);transform:translateX(-50%);
    }
    .tl-item{
      display:flex;justify-content:flex-end;
      padding-right:calc(50% + 32px);
      margin-bottom:40px;position:relative;
    }
    .tl-item:nth-child(even){
      justify-content:flex-start;
      padding-right:0;padding-left:calc(50% + 32px);
    }
    .tl-dot{
      position:absolute;left:50%;top:20px;
      width:16px;height:16px;border-radius:50%;
      background:var(--primary);border:3px solid #fff;
      box-shadow:0 0 0 3px var(--primary);
      transform:translateX(-50%);
    }
    .tl-card{
      background:var(--light);border:1.5px solid var(--border);
      border-radius:16px;padding:20px 24px;max-width:380px;
      transition:var(--transition);
    }
    .tl-card:hover{box-shadow:var(--shadow);border-color:var(--primary);}
    .tl-year{
      font-family:var(--font-head);font-weight:800;
      font-size:.8rem;color:var(--primary);
      text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;
    }
    .tl-card h5{font-family:var(--font-head);font-weight:800;margin-bottom:6px;font-size:.95rem;}
    .tl-card p{color:var(--mid);font-size:.85rem;line-height:1.6;margin:0;}

    /* PARTNERS */
    .partners-section{background:var(--light);}
    .partner-logo{
      background:#fff;border:1.5px solid var(--border);border-radius:14px;
      padding:20px;display:flex;align-items:center;justify-content:center;
      height:80px;font-family:var(--font-head);font-weight:800;
      font-size:1rem;color:var(--mid);transition:var(--transition);
      text-align:center;
    }
    .partner-logo:hover{border-color:var(--primary);color:var(--primary);box-shadow:var(--shadow);}

    /* CTA SECTION */
    .about-cta{
      background:linear-gradient(135deg,#0f172a,#1e1b4b);
      border-radius:24px;padding:60px 48px;
      color:#fff;text-align:center;
      position:relative;overflow:hidden;
    }
    .about-cta::before{
      content:'';position:absolute;inset:0;
      background:radial-gradient(ellipse at center,rgba(99,102,241,.25) 0%,transparent 70%);
    }
    .about-cta h2{font-family:var(--font-head);font-size:2rem;font-weight:800;position:relative;}
    .about-cta p{color:rgba(255,255,255,.65);max-width:500px;margin:12px auto 28px;position:relative;}
    .cta-btns{display:flex;gap:14px;justify-content:center;flex-wrap:wrap;position:relative;}
    .btn-cta-white{
      background:#fff;color:var(--primary) !important;
      border-radius:50px;padding:14px 32px;
      font-family:var(--font-head);font-weight:700;
      text-decoration:none;transition:var(--transition);
    }
    .btn-cta-white:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(0,0,0,.2);}
    .btn-cta-ghost{
      background:transparent;color:#fff !important;
      border:2px solid rgba(255,255,255,.3);
      border-radius:50px;padding:12px 28px;
      font-family:var(--font-head);font-weight:600;
      text-decoration:none;transition:var(--transition);
    }
    .btn-cta-ghost:hover{border-color:#fff;background:rgba(255,255,255,.08);}

    @media (max-width:991px){
      .timeline::before{left:20px;}
      .tl-item,.tl-item:nth-child(even){
        padding-right:0;padding-left:52px;
        justify-content:flex-start;
      }
      .tl-dot{left:20px;}
      .tl-card{max-width:100%;}
    }
    @media (max-width:767px){
      .about-hero{padding:56px 0 64px;}
      .stat-item{border-right:none;border-bottom:1px solid var(--border);padding:16px;}
      .stat-item:last-child{border-bottom:none;}
      .about-img-ring,.about-float-badge{display:none;}
      .about-cta{padding:36px 20px;}
      .mission-img{height:280px;}
    }
  </style>
</head>
<body>

<!-- ANNOUNCE BAR -->
<div class="announce-bar">
  <span>🎓 Student Exclusive: Extra 15% off with code <strong>STUDY15</strong> &nbsp;|&nbsp; Free shipping on orders above ₹499</span>
</div>

<!-- NAVBAR -->
 
<nav class="navbar navbar-expand-lg sticky-top" id="mainNav">
  <div class="container">
    <a class="navbar-brand" href="index.php">
      <span class="brand-icon">📚</span>
      <span>Study<strong>Mart</strong></span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navCollapse">
      <span class="hamburger-icon"><i class="fa fa-bars"></i></span>
    </button>
    <div class="collapse navbar-collapse" id="navCollapse">
      <ul class="navbar-nav mx-auto gap-1">
        <li class="nav-item"><a class="nav-link active" href="index.php">Home</a></li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Categories</a>
          <ul class="dropdown-menu mega-menu">
            <li><a class="dropdown-item" href="shop.php"><i class="fa fa-book me-2"></i>Books & Notes</a></li>
            
            <li><a class="dropdown-item" href="shop.php"><i class="fa fa-laptop me-2"></i>Electronics</a></li>

            <li><a class="dropdown-item" href="shop.php"><i class="fa fa-ruler me-2"></i>Stationery</a></li>
            
            <li><a class="dropdown-item" href="shop.php"><i class="fa fa-graduation-cap me-2"></i>Lab Supplies</a></li>
          </ul>
        </li>
        <li class="nav-item"><a class="nav-link" href="shop.php">Shop</a></li>

        <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
        <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
      </ul>

      <div class="nav-actions d-flex align-items-center gap-3">
        <div class="nav-search-wrap">
          <input type="text" class="nav-search" placeholder="Search products…" id="navSearch" />
          <i class="fa fa-search search-icon"></i>
        </div>
        <a href="wishlist.php" class="nav-icon-btn" title="Wishlist"><i class="fa fa-heart"></i></a>
        <a href="cart.php" class="nav-icon-btn cart-btn" title="Cart">
          <i class="fa fa-shopping-cart"></i>
          <span class="cart-badge" id="cartCount">0</span>
        </a>
        <a href="login.php" class="btn btn-sm btn-login">Login</a>
      </div>
    </div>
  </div>
</nav>
<!-- ═══════════════════════════════════════
     HERO
═══════════════════════════════════════ -->
<section class="about-hero">
  <div class="about-hero-dots"></div>
  <div class="container" style="position:relative;z-index:1;">
    <div class="row align-items-center g-5">

      <div class="col-lg-6">
        <div class="about-hero-tag">🎓 ABOUT STUDYMART</div>
        <h1>Built by Students,<br /><span>For Students</span></h1>
        <p>We started StudyMart because we knew firsthand how hard it is to find quality academic supplies at honest prices. Today, we serve 50,000+ students across India with the products they actually need.</p>
        <div class="hero-btn-row">
          <a href="shop.php" class="btn btn-hero-primary">Explore Shop <i class="fa fa-arrow-right ms-2"></i></a>
          <a href="contact.php" class="btn btn-hero-ghost" style="border-color:rgba(255,255,255,.3);color:#fff !important;">Contact Us</a>
        </div>
      </div>

      <div class="col-lg-6 d-none d-lg-flex justify-content-center">
        <div class="about-hero-image-wrap">
          <div class="about-img-ring"></div>
          <img
            class="about-hero-main-img"
            src="https://images.unsplash.com/photo-1523050854058-8df90110c9f1?w=600&q=80"
            alt="Students studying"
          />
          <div class="about-float-badge afb-1">
            <div class="afb-icon">🏆</div>
            <div class="afb-val">50K+</div>
            <div class="afb-label">Happy Students</div>
          </div>
          <div class="about-float-badge afb-2">
            <div class="afb-icon">📦</div>
            <div class="afb-val">1.2L+</div>
            <div class="afb-label">Orders Delivered</div>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ═══════════════════════════════════════
     STATS BAR
═══════════════════════════════════════ -->
<section class="stats-bar">
  <div class="container">
    <div class="row g-0 text-center">
      <div class="col-6 col-md-3 stat-item">
        <span class="stat-num" id="statStudents">0</span>
        <span class="stat-label">Students Served</span>
      </div>
      <div class="col-6 col-md-3 stat-item">
        <span class="stat-num" id="statProducts">0</span>
        <span class="stat-label">Products Listed</span>
      </div>
      <div class="col-6 col-md-3 stat-item">
        <span class="stat-num" id="statOrders">0</span>
        <span class="stat-label">Orders Delivered</span>
      </div>
      <div class="col-6 col-md-3 stat-item">
        <span class="stat-num" id="statRating">0</span>
        <span class="stat-label">Average Rating</span>
      </div>
    </div>
  </div>
</section>

<!-- ═══════════════════════════════════════
     OUR MISSION
═══════════════════════════════════════ -->
<section class="mission-section py-5">
  <div class="container">
    <div class="row g-5 align-items-center">

      <div class="col-lg-5">
        <div class="mission-img-wrap">
          <img
            class="mission-img"
            src="https://images.unsplash.com/photo-1434030216411-0b793f4b4173?w=700&q=80"
            alt="Our Mission"
          />
          <div class="mission-img-badge">
            <div class="mib-icon">🎯</div>
            <div>
              <div class="mib-val">Our Mission</div>
              <div class="mib-label">Empower every student</div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-7 mission-text">
        <div class="badge-pill mb-3">🚀 Why We Exist</div>
        <h2>Making Quality Education<br /><span>Supplies Accessible</span></h2>
        <p>Every student deserves access to the right tools without worrying about price. We bridge the gap between premium quality and student budgets — offering genuine products at prices that make sense for campus life.</p>
        <p>From Jaipur to IIT Delhi, from AIIMS to IIM — StudyMart is where students shop smart.</p>

        <ul class="mission-points">
          <li>
            <div class="mp-icon"><i class="fa fa-check"></i></div>
            <div><strong>100% Genuine Products</strong> — Every item verified for authenticity before listing</div>
          </li>
          <li>
            <div class="mp-icon"><i class="fa fa-tag"></i></div>
            <div><strong>Student-First Pricing</strong> — Exclusive discounts and bundle deals for learners</div>
          </li>
          <li>
            <div class="mp-icon"><i class="fa fa-truck"></i></div>
            <div><strong>Fast Pan-India Delivery</strong> — 2–5 business days to your hostel or home</div>
          </li>
          <li>
            <div class="mp-icon"><i class="fa fa-undo"></i></div>
            <div><strong>Hassle-Free Returns</strong> — 7-day easy return, no questions asked</div>
          </li>
          <li>
            <div class="mp-icon"><i class="fa fa-headset"></i></div>
            <div><strong>Dedicated Support</strong> — Real humans available Mon–Sat 9AM–6PM</div>
          </li>
        </ul>

        <a href="shop.php" class="btn btn-hero-primary mt-2">Start Shopping <i class="fa fa-arrow-right ms-2"></i></a>
      </div>

    </div>
  </div>
</section>

<!-- ═══════════════════════════════════════
     CORE VALUES
═══════════════════════════════════════ -->
<section class="values-section py-5">
  <div class="container">
    <div class="text-center mb-5">
      <div class="badge-pill mx-auto mb-3">💡 What We Stand For</div>
      <h2 class="section-title">Our Core Values</h2>
      <p class="section-sub mt-2">The principles that guide every decision we make at StudyMart.</p>
    </div>

    <div class="row g-4">

      <div class="col-sm-6 col-lg-3">
        <div class="value-card" style="--vc:#2563EB;">
          <div class="vc-icon" style="background:#eff6ff;">🎯</div>
          <h5>Student First</h5>
          <p>Every product, price and policy is built with the student in mind. Your success is our KPI.</p>
        </div>
      </div>

      <div class="col-sm-6 col-lg-3">
        <div class="value-card" style="--vc:#10b981;">
          <div class="vc-icon" style="background:#f0fdf4;">🛡️</div>
          <h5>100% Genuine</h5>
          <p>We source directly from manufacturers and authorised distributors. No counterfeits, ever.</p>
        </div>
      </div>

      <div class="col-sm-6 col-lg-3">
        <div class="value-card" style="--vc:#f97316;">
          <div class="vc-icon" style="background:#fff7ed;">⚡</div>
          <h5>Speed & Reliability</h5>
          <p>Your order ships within 24 hours. Real-time tracking so you always know where it is.</p>
        </div>
      </div>

      <div class="col-sm-6 col-lg-3">
        <div class="value-card" style="--vc:#7c3aed;">
          <div class="vc-icon" style="background:#faf5ff;">🤝</div>
          <h5>Honest Pricing</h5>
          <p>No hidden fees, no inflated MRPs. What you see is what you pay — always transparent.</p>
        </div>
      </div>

      <div class="col-sm-6 col-lg-3">
        <div class="value-card" style="--vc:#ec4899;">
          <div class="vc-icon" style="background:#fdf2f8;">🌱</div>
          <h5>Sustainability</h5>
          <p>We use eco-friendly packaging and partner with green-certified suppliers wherever possible.</p>
        </div>
      </div>

      <div class="col-sm-6 col-lg-3">
        <div class="value-card" style="--vc:#f59e0b;">
          <div class="vc-icon" style="background:#fffbeb;">💬</div>
          <h5>Community</h5>
          <p>We listen. Every review, complaint and suggestion shapes what we build next. You matter.</p>
        </div>
      </div>

      <div class="col-sm-6 col-lg-3">
        <div class="value-card" style="--vc:#14b8a6;">
          <div class="vc-icon" style="background:#f0fdfa;">🔒</div>
          <h5>Privacy & Safety</h5>
          <p>Your personal data is yours. We never sell it, never share it, and always protect it.</p>
        </div>
      </div>

      <div class="col-sm-6 col-lg-3">
        <div class="value-card" style="--vc:#6366f1;">
          <div class="vc-icon" style="background:#eef2ff;">📈</div>
          <h5>Continuous Growth</h5>
          <p>We add 200+ new products monthly, responding to what students are actually looking for.</p>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ═══════════════════════════════════════
     OUR JOURNEY (Timeline)
═══════════════════════════════════════ -->
<section class="journey-section py-5">
  <div class="container">
    <div class="text-center mb-5">
      <div class="badge-pill mx-auto mb-3">🗓️ Our Story</div>
      <h2 class="section-title">The StudyMart Journey</h2>
      <p class="section-sub mt-2">From a small dorm-room idea to India's favourite student store.</p>
    </div>

    <div class="timeline">

      <div class="tl-item">
        <div class="tl-dot"></div>
        <div class="tl-card">
          <div class="tl-year">📍 2021 — The Beginning</div>
          <h5>Founded in a Hostel Room</h5>
          <p>Two engineering students in Jaipur frustrated with overpriced campus stores decide to build something better. StudyMart is born.</p>
        </div>
      </div>

      <div class="tl-item">
        <div class="tl-dot"></div>
        <div class="tl-card">
          <div class="tl-year">🚀 2022 — First 1,000 Orders</div>
          <h5>Word Spreads on Campus</h5>
          <p>Within 6 months, 1,000+ orders are fulfilled. We onboard our first 50 products and launch official WhatsApp support.</p>
        </div>
      </div>

      <div class="tl-item">
        <div class="tl-dot"></div>
        <div class="tl-card">
          <div class="tl-year">📦 2022 — First Warehouse</div>
          <h5>Moving Beyond Dropshipping</h5>
          <p>We set up our first 800 sq ft warehouse in Jaipur. Same-day dispatch becomes possible for the first time.</p>
        </div>
      </div>

      <div class="tl-item">
        <div class="tl-dot"></div>
        <div class="tl-card">
          <div class="tl-year">🌍 2023 — Pan-India Expansion</div>
          <h5>Serving 100+ Cities</h5>
          <p>We integrate with 3 major logistics partners, enabling delivery to 100+ Indian cities including tier-2 and tier-3 towns.</p>
        </div>
      </div>

      <div class="tl-item">
        <div class="tl-dot"></div>
        <div class="tl-card">
          <div class="tl-year">🏆 2023 — Awards & Recognition</div>
          <h5>Best Student Startup — EdTech India</h5>
          <p>StudyMart wins the "Best Student Commerce Platform" award at EdTech India Summit 2023, Bangalore.</p>
        </div>
      </div>

      <div class="tl-item">
        <div class="tl-dot"></div>
        <div class="tl-card">
          <div class="tl-year">📱 2024 — Mobile App Launch</div>
          <h5>StudyMart Goes Mobile</h5>
          <p>Our Android and iOS apps launch with 10,000 downloads in the first week. Push notifications for deals go live.</p>
        </div>
      </div>

      <div class="tl-item">
        <div class="tl-dot"></div>
        <div class="tl-card">
          <div class="tl-year">🎯 2025 — 50K Students Milestone</div>
          <h5>The Community Keeps Growing</h5>
          <p>We cross 50,000 verified student users, 12,000+ products, and launch bundle deals and subscription boxes for the new academic year.</p>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ═══════════════════════════════════════
     TEAM
═══════════════════════════════════════ -->
<section class="team-section py-5">
  <div class="container">
    <div class="text-center mb-5">
      <div class="badge-pill mx-auto mb-3">👥 The People</div>
      <h2 class="section-title">Meet the Team</h2>
      <p class="section-sub mt-2">Students who built something — still driven by the same passion.</p>
    </div>

    <div class="row g-4">

      <div class="col-sm-6 col-lg-3">
        <div class="team-card">
          <div class="team-img-wrap" style="background:linear-gradient(135deg,#dbeafe,#bfdbfe);">
            <div class="team-avatar-placeholder" style="background:linear-gradient(135deg,#2563eb,#7c3aed);">YG</div>
            <div class="team-overlay">
              <a class="team-social" href="#"><i class="fab fa-linkedin"></i></a>
              <a class="team-social" href="#"><i class="fab fa-twitter"></i></a>
              <a class="team-social" href="#"><i class="fa fa-envelope"></i></a>
            </div>
          </div>
          <div class="team-body">
            <div class="team-name">Yuvraj Gupta</div>
            <div class="team-role">Founder & CEO</div>
            <div class="team-bio">BCA , Poornima University Jaipur. Loves building things that solve real problems for students.</div>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-lg-3">
        <div class="team-card">
          <div class="team-img-wrap" style="background:linear-gradient(135deg,#d1fae5,#a7f3d0);">
            <div class="team-avatar-placeholder" style="background:linear-gradient(135deg,#10b981,#0891b2);">PS</div>
            <div class="team-overlay">
              <a class="team-social" href="#"><i class="fab fa-linkedin"></i></a>
              <a class="team-social" href="#"><i class="fab fa-twitter"></i></a>
              <a class="team-social" href="#"><i class="fa fa-envelope"></i></a>
            </div>
          </div>
          <div class="team-body">
            <div class="team-name">Priya Sharma</div>
            <div class="team-role">Co-Founder & COO</div>
            <div class="team-bio">BCA, IIM Udaipur. Operations wizard who makes sure every order reaches on time.</div>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-lg-3">
        <div class="team-card">
          <div class="team-img-wrap" style="background:linear-gradient(135deg,#ede9fe,#ddd6fe);">
            <div class="team-avatar-placeholder" style="background:linear-gradient(135deg,#7c3aed,#db2777);">RV</div>
            <div class="team-overlay">
              <a class="team-social" href="#"><i class="fab fa-linkedin"></i></a>
              <a class="team-social" href="#"><i class="fab fa-twitter"></i></a>
              <a class="team-social" href="#"><i class="fa fa-envelope"></i></a>
            </div>
          </div>
          <div class="team-body">
            <div class="team-name">Rohan Verma</div>
            <div class="team-role">Co-Founder & COO</div>
            <div class="team-bio">Full-stack dev with a passion for performance and clean UI. Keeps the platform running 24/7.</div>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-lg-3">
        <div class="team-card">
          <div class="team-img-wrap" style="background:linear-gradient(135deg,#fef9c3,#fde68a);">
            <div class="team-avatar-placeholder" style="background:linear-gradient(135deg,#f59e0b,#ef4444);">NM</div>
            <div class="team-overlay">
              <a class="team-social" href="#"><i class="fab fa-linkedin"></i></a>
              <a class="team-social" href="#"><i class="fab fa-twitter"></i></a>
              <a class="team-social" href="#"><i class="fa fa-envelope"></i></a>
            </div>
          </div>
          <div class="team-body">
            <div class="team-name">Neha Mehra</div>
            <div class="team-role">Head of Marketing</div>
            <div class="team-bio">BBA, Symbiosis. Storyteller and campaign strategist who grew our community to 50K+ students.</div>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ═══════════════════════════════════════
     TRUSTED BY (Partners / Colleges)
═══════════════════════════════════════ -->
<section class="partners-section py-5">
  <div class="container">
    <div class="text-center mb-5">
      <div class="badge-pill mx-auto mb-3">🏫 Our Community</div>
      <h2 class="section-title">Trusted by Students From</h2>
    </div>
    <div class="row g-3">
      <div class="col-6 col-md-4 col-lg-2"><div class="partner-logo">IIT Delhi</div></div>
      <div class="col-6 col-md-4 col-lg-2"><div class="partner-logo">IIT Bombay</div></div>
      <div class="col-6 col-md-4 col-lg-2"><div class="partner-logo">AIIMS Delhi</div></div>
      <div class="col-6 col-md-4 col-lg-2"><div class="partner-logo">IIM Bangalore</div></div>
      <div class="col-6 col-md-4 col-lg-2"><div class="partner-logo">NIT Jaipur</div></div>
      <div class="col-6 col-md-4 col-lg-2"><div class="partner-logo">BITS Pilani</div></div>
      <div class="col-6 col-md-4 col-lg-2"><div class="partner-logo">VIT Vellore</div></div>
      <div class="col-6 col-md-4 col-lg-2"><div class="partner-logo">Jadavpur Univ.</div></div>
      <div class="col-6 col-md-4 col-lg-2"><div class="partner-logo">DU North Campus</div></div>
      <div class="col-6 col-md-4 col-lg-2"><div class="partner-logo">Symbiosis Pune</div></div>
      <div class="col-6 col-md-4 col-lg-2"><div class="partner-logo">Manipal Univ.</div></div>
      <div class="col-6 col-md-4 col-lg-2"><div class="partner-logo">Christ Univ.</div></div>
    </div>
  </div>
</section>

<!-- ═══════════════════════════════════════
     TESTIMONIALS
═══════════════════════════════════════ -->
<section class="testimonials-section py-5">
  <div class="container">
    <div class="text-center mb-5">
      <div class="badge-pill mx-auto mb-3">💬 Student Reviews</div>
      <h2 class="section-title">What Students Say About Us</h2>
    </div>
    <div class="row g-4">
      <div class="col-md-4">
        <div class="testi-card">
          <div class="testi-stars">★★★★★</div>
          <p>"I've ordered from StudyMart 6 times. Every single time — right products, right prices, fast delivery. This is exactly what students need."</p>
          <div class="testi-author">
            <div class="testi-avatar">RK</div>
            <div><strong>Rahul K.</strong><small>B.Tech CSE, IIT Delhi</small></div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="testi-card featured-testi">
          <div class="testi-stars">★★★★★</div>
          <p>"The bundle deals are insane value. Got my full semester stationery kit for under ₹700. No other platform comes close for student needs!"</p>
          <div class="testi-author">
            <div class="testi-avatar">AP</div>
            <div><strong>Ananya P.</strong><small>MBA, IIM Bangalore</small></div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="testi-card">
          <div class="testi-stars">★★★★★</div>
          <p>"As a medical student, lab supplies are expensive everywhere else. StudyMart's lab kit saved me ₹800 compared to the campus store. Love it!"</p>
          <div class="testi-author">
            <div class="testi-avatar">MS</div>
            <div><strong>Meera S.</strong><small>MBBS, AIIMS</small></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ═══════════════════════════════════════
     CTA SECTION
═══════════════════════════════════════ -->
<section class="py-5" style="background:var(--light);">
  <div class="container">
    <div class="about-cta">
      <h2>🎓 Ready to Shop Smarter?</h2>
      <p>Join 50,000+ students already saving big on textbooks, electronics, stationery and more.</p>
      <div class="cta-btns">
        <a href="shop.php" class="btn-cta-white">Browse Products <i class="fa fa-arrow-right ms-2"></i></a>
        <a href="#" class="btn-cta-ghost">Today's Deals ⚡</a>
      </div>
    </div>
  </div>
</section>

<!-- FULL FOOTER -->
<footer class="site-footer pt-5 pb-3">
  <div class="container">
    <div class="row g-4 mb-4">
      <div class="col-lg-3 col-md-6">
        <div class="footer-brand"><span class="brand-icon">📚</span><span>Study<strong>Mart</strong></span></div>
        <p class="footer-desc mt-3">Your one-stop destination for all student essentials. Quality products, student prices.</p>
        <div class="social-links mt-3">
          <a href="#"><i class="fab fa-instagram"></i></a>
          <a href="#"><i class="fab fa-twitter"></i></a>
          <a href="#"><i class="fab fa-facebook"></i></a>
          <a href="#"><i class="fab fa-youtube"></i></a>
        </div>
      </div>
      <div class="col-lg-2 col-6">
        <h6>Quick Links</h6>
        <ul class="footer-links">
          <li><a href="index.php">Home</a></li>
          <li><a href="shop.php">Shop</a></li>

          <li><a href="about.php">About</a></li>
          <li><a href="contact.php">Contact</a></li>
        </ul>
      </div>
      <div class="col-lg-2 col-6">
        <h6>Categories</h6>
        <ul class="footer-links">
          <li><a href="shop.php">Books</a></li>
          <li><a href="shop.php">Electronics</a></li>
          <li><a href="shop.php">Stationery</a></li>
          <li><a href="shop.php">Bags</a></li>
          <li><a href="shop.php">Lab Supplies</a></li>
        </ul>
      </div>
      <div class="col-lg-2 col-6">
        <h6>Support</h6>
        <ul class="footer-links">
          <li><a href="#">FAQ</a></li>
          <li><a href="#">Returns</a></li>
          <li><a href="#">Shipping Info</a></li>
          <li><a href="#">Track Order</a></li>
          <li><a href="#">Privacy Policy</a></li>
        </ul>
      </div>
      <div class="col-lg-3 col-md-6">
        <h6>Contact Us</h6>
        <ul class="footer-contact">
          <li><i class="fa fa-map-marker-alt"></i> 42, Edu Street, Jaipur, Rajasthan</li>
          <li><i class="fa fa-phone"></i> +91 98765 43210</li>
          <li><i class="fa fa-envelope"></i> hello@studymart.in</li>
          <li><i class="fa fa-clock"></i> Mon–Sat: 9AM – 6PM</li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <span>© 2025 StudyMart. All rights reserved.</span>
      <div class="payment-icons">
        <img src="https://upload.wikimedia.org/wikipedia/commons/2/2a/Mastercard-logo.svg" alt="Mastercard" />
        <img src="https://upload.wikimedia.org/wikipedia/commons/5/5e/Visa_Inc._logo.svg" alt="Visa" />
        <span class="pay-text">UPI</span>
        <span class="pay-text">Paytm</span>
      </div>
    </div>
  </div>
</footer>

<div class="cart-toast" id="cartToast"><i class="fa fa-check-circle"></i> Added to cart!</div>
<button class="back-to-top" id="backTop" onclick="scrollToTop()"><i class="fa fa-arrow-up"></i></button>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
<script src="app.js"></script>
<script>
// ── ANIMATED COUNTERS ────────────────────────────────────────────
function animateCounter(id, target, suffix, duration) {
  const el = document.getElementById(id);
  if (!el) return;
  let start = 0;
  const step = target / (duration / 16);
  const timer = setInterval(() => {
    start += step;
    if (start >= target) { start = target; clearInterval(timer); }
    el.textContent = (Number.isInteger(target) ? Math.floor(start).toLocaleString() : start.toFixed(1)) + suffix;
  }, 16);
}

// Start when stats bar enters viewport
const statsBar = document.querySelector('.stats-bar');
const observer = new IntersectionObserver((entries) => {
  if (entries[0].isIntersecting) {
    animateCounter('statStudents', 50000, '+', 1800);
    animateCounter('statProducts', 12000, '+', 1600);
    animateCounter('statOrders', 120000, '+', 2000);
    animateCounter('statRating',  4.9,   '★', 1200);
    observer.disconnect();
  }
}, { threshold: 0.3 });
if (statsBar) observer.observe(statsBar);
</script>
</body>
</html>
HTMLEOF
echo "Done"