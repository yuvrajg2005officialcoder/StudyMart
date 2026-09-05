<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Cart – StudyMart</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,400&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="style.css" />
</head>
<body>

<div class="announce-bar">
  <span>🎓 Student Exclusive: Extra 15% off with code <strong>STUDY15</strong></span>
</div>

<nav class="navbar navbar-expand-lg sticky-top" id="mainNav">
  <div class="container">
    <a class="navbar-brand" href="index.html"><span class="brand-icon">📚</span><span>Study<strong>Mart</strong></span></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navCollapse">
      <span class="hamburger-icon"><i class="fa fa-bars"></i></span>
    </button>
    <div class="collapse navbar-collapse" id="navCollapse">
      <ul class="navbar-nav mx-auto gap-1">
        <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="shop.php">Shop</a></li>
        <!-- <li class="nav-item"><a class="nav-link" href="deals.html">Deals</a></li> -->
      </ul>
      <div class="nav-actions d-flex align-items-center gap-3">
        <a href="wishlist.php" class="nav-icon-btn"><i class="fa fa-heart"></i></a>
        <a href="cart.php" class="nav-icon-btn cart-btn active">
          <i class="fa fa-shopping-cart"></i>
          <span class="cart-badge" id="cartCount">0</span>
        </a>
        <a href="login.php" class="btn btn-sm btn-login">Login</a>
      </div>
    </div>
  </div>
</nav>

<div class="page-header">
  <div class="container">
    <h1>Shopping Cart</h1>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
        <li class="breadcrumb-item active">Cart</li>
      </ol>
    </nav>
  </div>
</div>

<section class="cart-section py-5">
  <div class="container">
    <div id="cartEmpty" class="cart-empty text-center py-5" style="display:none;">
      <div class="empty-icon">🛒</div>
      <h3>Your cart is empty</h3>
      <p>Looks like you haven't added anything yet.</p>
      <a href="shop.php" class="btn btn-hero-primary mt-3">Start Shopping</a>
    </div>
    <div id="cartContent" class="row g-4">
      <div class="col-lg-8">
        <div class="cart-table-wrap">
          <table class="cart-table w-100" id="cartTable">
            <thead>
              <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Qty</th>
                <th>Total</th>
                <th></th>
              </tr>
            </thead>
            <tbody id="cartBody"></tbody>
          </table>
        </div>
        <div class="cart-actions mt-3 d-flex gap-2">
          <a href="shop.php" class="btn btn-outline-cart">← Continue Shopping</a>
          <button onclick="clearCart()" class="btn btn-outline-danger btn-sm">Clear Cart</button>
        </div>
        <div class="coupon-row mt-4">
          <h6>Coupon Code</h6>
          <div class="coupon-input">
            <input type="text" id="couponCode" placeholder="Enter coupon code" />
            <button onclick="applyCoupon()">Apply</button>
          </div>
          <div id="couponMsg" class="coupon-msg"></div>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="order-summary">
          <h5>Order Summary</h5>
          <div class="summary-row"><span>Subtotal</span><span id="cartSubtotal">₹0</span></div>
          <div class="summary-row discount-row" id="discountRow" style="display:none;"><span>Discount</span><span id="cartDiscount" class="text-success">-₹0</span></div>
          <div class="summary-row"><span>Shipping</span><span id="shippingCost">₹49</span></div>
          <div class="summary-row summary-total"><span>Total</span><span id="cartTotal">₹49</span></div>
          <a href="checkout.php" class="btn btn-hero-primary w-100 mt-3">Proceed to Checkout</a>
          <div class="secure-badge mt-3"><i class="fa fa-lock"></i> Secure Checkout</div>
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
<script src="app.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', renderCart);
</script>
</body>
</html>