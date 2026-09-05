<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// DB Connection
$conn = new mysqli("localhost", "root", "", "ecommerce");
if ($conn->connect_error)
    die("Connection failed: " . $conn->connect_error);

// ✅ DELETE PRODUCT
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM product WHERE product_id = ?");
    $stmt->bind_param("i", $del_id);
    $stmt->execute();
    $stmt->close();
    header("Location: index.php?deleted=1");
    exit;
}

// Auto-detect table names
function detectTable($conn, $options)
{
    $res = $conn->query("SHOW TABLES");
    $tables = [];
    while ($r = $res->fetch_array())
        $tables[] = strtolower($r[0]);
    foreach ($options as $t) {
        if (in_array(strtolower($t), $tables))
            return $t;
    }
    return $options[0];
}

$catTable = detectTable($conn, ['categories', 'category', 'tbl_category', 'tbl_categories']);
$subcatTable = detectTable($conn, ['subcategories', 'subcategory', 'sub_categories', 'tbl_subcategory']);
$brandTable = detectTable($conn, ['brands', 'brand', 'tbl_brand', 'tbl_brands']);

$totalProducts = $conn->query("SELECT COUNT(*) as c FROM product")->fetch_assoc()['c'] ?? 0;
$totalCategories = $conn->query("SELECT COUNT(*) as c FROM $catTable")->fetch_assoc()['c'] ?? 0;
$totalBrands = $conn->query("SELECT COUNT(*) as c FROM $brandTable")->fetch_assoc()['c'] ?? 0;
$totalSubCats = $conn->query("SELECT COUNT(*) as c FROM $subcatTable")->fetch_assoc()['c'] ?? 0;

$recentProducts = $conn->query("
    SELECT p.*, c.cat_name AS category_name
    FROM product p
    LEFT JOIN category c ON p.cat_id = c.cat_id
    ORDER BY p.product_id DESC LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <title>Admin Panel – Dashboard</title>
    <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no'
        name='viewport' />
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito:400,600,700,800,900">
    <link rel="stylesheet" href="assets/css/ready.css">
    <link rel="stylesheet" href="assets/css/demo.css">
    <link rel="stylesheet"
        href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
    <style>
        .sidebar {
            background: #161b2e !important;
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.2);
        }

        .sidebar .sidebar-wrapper {
            background: #161b2e !important;
        }

        .sidebar .user {
            background: #0f1322 !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
            padding: 18px 16px !important;
        }

        .sidebar .user .info a span {
            color: #fff !important;
            font-weight: 700;
            font-size: 14px;
        }

        .sidebar .user .info .user-level {
            color: #6c7a9c !important;
            font-size: 11px;
            font-weight: 500;
            display: block;
            margin-top: 2px;
        }

        .sidebar-section-label {
            color: #4a5578;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            padding: 18px 20px 6px;
            display: block;
        }

        .sidebar .nav .nav-item>a {
            color: #8a96b0 !important;
            border-radius: 12px !important;
            margin: 2px 10px !important;
            padding: 11px 16px !important;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            font-weight: 600;
            font-size: 13.5px;
        }

        .sidebar .nav .nav-item>a:hover {
            background: rgba(255, 255, 255, 0.06) !important;
            color: #fff !important;
        }

        .sidebar .nav .nav-item>a i {
            color: #6c7a9c;
            font-size: 19px;
            margin-right: 10px;
            min-width: 22px;
        }

        .sidebar .nav .nav-item>a p {
            color: inherit !important;
            font-weight: 600;
            font-size: 13.5px;
            margin: 0;
            flex: 1;
        }

        .sidebar .nav .nav-item.active>a {
            background: #ffffff !important;
            color: #1a1f36 !important;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.18);
            border-radius: 12px !important;
        }

        .sidebar .nav .nav-item.active>a i {
            color: #1a1f36 !important;
        }

        .sidebar .nav .nav-item.active>a p {
            color: #1a1f36 !important;
        }

        .sidebar .nav-collapse {
            background: rgba(0, 0, 0, 0.12) !important;
            border-radius: 10px;
            margin: 2px 10px !important;
            padding: 4px 0 !important;
        }

        .sidebar .nav-collapse li a {
            color: #6c7a9c !important;
            padding: 9px 16px 9px 20px !important;
            margin: 0 !important;
            border-radius: 8px !important;
            font-size: 13px !important;
            font-weight: 600;
            display: flex;
            align-items: center;
            transition: all 0.2s;
        }

        .sidebar .nav-collapse li a:hover {
            color: #fff !important;
            background: rgba(255, 255, 255, 0.07) !important;
        }

        .sidebar .nav-collapse li.active a {
            color: #4361ee !important;
            font-weight: 700 !important;
            background: rgba(67, 97, 238, 0.12) !important;
        }

        .main-header {
            background: #fff !important;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.07) !important;
            border-bottom: none !important;
        }

        .logo-header {
            background: #161b2e !important;
            border-bottom: none !important;
        }

        .logo-header .logo {
            color: #fff !important;
            font-weight: 800 !important;
            font-size: 17px !important;
        }

        .main-panel {
            background: #f0f2f8 !important;
        }

        .page-title {
            color: #1a1f36 !important;
            font-weight: 700 !important;
            font-size: 22px !important;
        }

        .card {
            border: none !important;
            border-radius: 14px !important;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.07) !important;
        }

        .card-header {
            background: #fff !important;
            border-bottom: 1px solid #f0f2f8 !important;
            border-radius: 14px 14px 0 0 !important;
            padding: 16px 20px !important;
        }

        .card-title {
            color: #1a1f36 !important;
            font-weight: 600 !important;
        }

        .btn-primary {
            background: linear-gradient(135deg, #4361ee, #6c8fff) !important;
            border: none !important;
            border-radius: 8px !important;
            font-weight: 600 !important;
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3) !important;
        }

        .table thead th {
            background: #f8f9fc !important;
            color: #8d9db5 !important;
            font-size: 11px !important;
            font-weight: 700 !important;
            letter-spacing: 1px !important;
            text-transform: uppercase !important;
            border-bottom: 2px solid #f0f2f8 !important;
            padding: 14px 16px !important;
        }

        .table tbody tr {
            border-bottom: 1px solid #f5f6fa !important;
        }

        .table tbody tr:hover {
            background: #f8f9ff !important;
        }

        .table tbody td {
            padding: 14px 16px !important;
            vertical-align: middle !important;
            border: none !important;
        }

        /* Stat Cards */
        .stat-card {
            border-radius: 16px !important;
            padding: 22px 24px;
            color: #fff;
            position: relative;
            overflow: hidden;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12) !important;
        }

        .stat-card .stat-icon {
            font-size: 48px;
            opacity: 0.18;
            position: absolute;
            right: 16px;
            top: 12px;
        }

        .stat-card .stat-num {
            font-size: 34px;
            font-weight: 800;
            line-height: 1;
        }

        .stat-card .stat-label {
            font-size: 12px;
            font-weight: 700;
            opacity: 0.85;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 5px;
        }

        .stat-card .stat-link {
            font-size: 11px;
            opacity: 0.75;
            margin-top: 12px;
            display: inline-block;
            color: #fff;
            text-decoration: none;
        }

        .stat-card .stat-link:hover {
            opacity: 1;
        }

        .stat-products {
            background: linear-gradient(135deg, #4361ee, #6c8fff);
        }

        .stat-categories {
            background: linear-gradient(135deg, #11c26d, #1de49b);
        }

        .stat-subcats {
            background: linear-gradient(135deg, #f77f00, #ffb347);
        }

        .stat-brands {
            background: linear-gradient(135deg, #e91e63, #f06292);
        }

        .badge-active {
            background: #e8f5e9;
            color: #2e7d32;
            font-size: 11px;
            padding: 4px 10px;
            border-radius: 20px;
            font-weight: 600;
        }

        .badge-inactive {
            background: #fce4ec;
            color: #c62828;
            font-size: 11px;
            padding: 4px 10px;
            border-radius: 20px;
            font-weight: 600;
        }

        .badge-oos {
            background: #fff3e0;
            color: #e65100;
            font-size: 11px;
            padding: 4px 10px;
            border-radius: 20px;
            font-weight: 600;
        }

        .notification {
            background: #e53935 !important;
            font-size: 9px !important;
            padding: 2px 5px !important;
        }

        .profile-pic span {
            font-weight: 600;
            color: #1a1f36 !important;
            font-size: 13px;
        }

        /* ✅ Custom Delete Confirm Modal */
        .delete-modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }

        .delete-modal-overlay.active {
            display: flex;
        }

        .delete-modal-box {
            background: #fff;
            border-radius: 16px;
            padding: 32px 28px;
            max-width: 400px;
            width: 90%;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            animation: popIn 0.2s ease;
        }

        @keyframes popIn {
            from {
                transform: scale(0.85);
                opacity: 0;
            }

            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        .delete-modal-icon {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: #fce4ec;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
        }

        .delete-modal-icon i {
            font-size: 32px;
            color: #e91e63;
        }

        .delete-modal-title {
            font-size: 18px;
            font-weight: 700;
            color: #1a1f36;
            margin-bottom: 8px;
        }

        .delete-modal-msg {
            font-size: 13px;
            color: #6c7a9c;
            margin-bottom: 24px;
            line-height: 1.5;
        }

        .delete-modal-btns {
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .btn-cancel-modal {
            padding: 10px 24px;
            border-radius: 8px;
            border: 2px solid #e8ecf4;
            background: #fff;
            color: #6c7a9c;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-cancel-modal:hover {
            background: #f8f9fc;
            border-color: #d0d5e8;
        }

        .btn-confirm-delete {
            padding: 10px 24px;
            border-radius: 8px;
            border: none;
            background: linear-gradient(135deg, #e91e63, #f06292);
            color: #fff;
            font-weight: 700;
            font-size: 13px;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(233, 30, 99, 0.3);
            transition: all 0.2s;
        }

        .btn-confirm-delete:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(233, 30, 99, 0.4);
        }

        /* ✅ Success Toast */
        .toast-success {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 99999;
            background: #fff;
            border-radius: 12px;
            padding: 14px 20px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 280px;
            animation: slideIn 0.3s ease;
            border-left: 4px solid #11c26d;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100px);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .toast-success i {
            font-size: 22px;
            color: #11c26d;
        }

        .toast-success span {
            font-weight: 600;
            color: #1a1f36;
            font-size: 13px;
        }
    </style>
</head>

<body>

    <!-- ✅ Delete Confirm Modal -->
    <div class="delete-modal-overlay" id="deleteModal">
        <div class="delete-modal-box">
            <div class="delete-modal-icon">
                <i class="las la-trash-alt "></i>
            </div>
            <div class="delete-modal-title">Product Delete Karo?</div>
            <div class="delete-modal-msg">
                Kya aap <strong id="modalProductName"></strong> ko delete karna chahte hain?<br>
                Ye action undo nahi ho sakti!
            </div>
            <div class="delete-modal-btns">
                <button class="btn-cancel-modal" id="cancelDelete">Cancel</button>
                <a href="#" class="btn-confirm-delete" id="confirmDelete">
                    <i class="las la-trash mr-1"></i> Haan, Delete Karo
                </a>
            </div>
        </div>
    </div>

    <!-- ✅ Success Toast (shown after delete) -->
    <?php if (isset($_GET['deleted'])): ?>
        <div class="toast-success" id="successToast">
            <i class="las la-check-circle"></i>
            <span>Product successfully delete ho gaya!</span>
        </div>
    <?php endif; ?>

    <div class="wrapper">

        <!-- HEADER -->
        <div class="main-header">
            <div class="logo-header">
                <a href="index.php" class="logo">
                    <i class="las la-store mr-1"></i> Admin Panel
                </a>
                <button class="navbar-toggler sidenav-toggler ml-auto" type="button" data-toggle="collapse"
                    data-target="collapse" aria-controls="sidebar" aria-expanded="false">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <button class="topbar-toggler more"><i class="las la-ellipsis-v"></i></button>
            </div>
            <nav class="navbar navbar-header navbar-expand-lg">
                <div class="container-fluid">
                    <form class="navbar-left navbar-form nav-search mr-md-3" action="">
                        <div class="input-group">
                            <input type="text" placeholder="Search ..." class="form-control">
                            <div class="input-group-append">
                                <span class="input-group-text"><i class="las la-search search-icon"></i></span>
                            </div>
                        </div>
                    </form>
                    <ul class="navbar-nav topbar-nav ml-md-auto align-items-center">
                        <li class="nav-item dropdown hidden-caret">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown">
                                <i class="las la-bell" style="font-size:20px;"></i>
                                <span class="notification">3</span>
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="dropdown-toggle profile-pic" data-toggle="dropdown" href="#"
                                aria-expanded="false">
                                <img src="assets/img/profile.jpg" alt="user-img" width="36" class="img-circle"
                                    style="border:2px solid #4361ee;">
                                <span><?= htmlspecialchars($_SESSION['admin_name']) ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-user">
                                <li>
                                    <div class="user-box">
                                        <div class="u-img"><img src="assets/img/profile.jpg" alt="user"></div>
                                        <div class="u-text">
                                            <h4><?= htmlspecialchars($_SESSION['admin_name']) ?></h4>
                                            <p class="text-muted"><?= htmlspecialchars($_SESSION['admin_email']) ?></p>
                                        </div>
                                    </div>
                                </li>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="logout.php"><i
                                        class="las la-power-off text-danger mr-1"></i> Logout</a>
                            </ul>
                        </li>
                    </ul>
                </div>
            </nav>
        </div>
        <!-- HEADER END -->

        <!-- SIDEBAR -->
        <div class="sidebar">
            <div class="scrollbar-inner sidebar-wrapper">
                <div class="user">
                    <div class="photo">
                        <img src="assets/img/profile.jpg" style="border:2px solid #4361ee;">
                    </div>
                    <div class="info">
                        <a data-toggle="collapse" href="#collapseExample" aria-expanded="true">
                            <span>
                                <?= htmlspecialchars($_SESSION['admin_name']) ?>
                                <span class="user-level">Administrator</span>
                                <span class="caret"></span>
                            </span>
                        </a>
                        <div class="clearfix"></div>
                        <div class="collapse show" id="collapseExample">
                            <ul class="nav">
                                <li><a href="#"><span class="link-collapse">My Profile</span></a></li>
                                <li><a href="logout.php"><span class="link-collapse">Logout</span></a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <ul class="nav" style="padding: 10px 0 20px;">
                   <li class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
						<a href="index.php"><i class="las la-home"></i>
							<p>Dashboard</p>
						</a>
					</li>

					<li class="sidebar-section-label">Components</li>

                    	<!-- HOME SECTION -->
					<li class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'home.php' ? 'active' : '' ?>">
						<a href="home.php"><i class="las la-image"></i>
							<p>Home Section</p>
						</a>
					</li>

					<!-- CATEGORY -->
					<li
						class="nav-item <?= in_array(basename($_SERVER['PHP_SELF']), ['categories.php', 'add-category.php', 'edit-category.php']) ? 'active' : '' ?>">
						<a href="categories.php"><i class="las la-th-list"></i>
							<p>Category</p>
						</a>
					</li>

					<!-- SUB CATEGORY -->
					<li class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'sub-categories.php' ? 'active' : '' ?>">
						<a href="sub-categories.php"><i class="las la-sitemap"></i>
							<p>Sub Category</p>
						</a>
					</li>

					<!-- BRAND -->
					<li class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'brand.php' ? 'active' : '' ?>">
						<a href="brand.php"><i class="las la-tag"></i>
							<p>Brand</p>
						</a>
					</li>

					<!-- PRODUCT -->
					<li class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : '' ?>">
						<a href="products.php"><i class="las la-shopping-cart"></i>
							<p>Product</p>
						</a>
					</li>

					<!-- VENDOR — direct link, no submenu -->
					<li class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'vendor.php' ? 'active' : '' ?>">
						<a href="vendor.php"><i class="las la-store"></i>
							<p>Vendors</p>
						</a>
					</li>

					<!-- VENDOR ORDERS -->
					<li
						class="nav-item <?= in_array(basename($_SERVER['PHP_SELF']), ['view-vendor-order.php', 'add-vendor-order.php', 'edit-vendor-order.php']) ? 'active' : '' ?>">
						<a href="vendor-order.php">
							<i class="las la-clipboard-list"></i>
							<p>Vendor Orders</p>
						</a>
					</li>

                    		<!-- CONTACT MESSAGES -->
					<li class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'messages.php' ? 'active' : '' ?>">
						<a href="messages.php"><i class="las la-envelope"></i>
							<p>Contact Messages</p>
						</a>
					</li>
 
                </ul>
            </div>
        </div>
        <!-- SIDEBAR END -->

        <!-- MAIN PANEL -->
        <div class="main-panel">
            <div class="content">
                <div class="container-fluid">
                    <div class="page-inner">

                        <div class="mt-3 mb-4">
                            <h4 class="page-title mb-0">Dashboard</h4>
                            <p class="text-muted mb-0" style="font-size:13px;">
                                Welcome back, <?= htmlspecialchars($_SESSION['admin_name']) ?>!
                            </p>
                        </div>

                        <!-- Stats Cards -->
                        <div class="row mb-4">
                            <div class="col-sm-6 col-xl-3 mb-3">
                                <div class="stat-card stat-products">
                                    <i class="las la-shopping-cart stat-icon"></i>
                                    <div class="stat-num"><?= $totalProducts ?></div>
                                    <div class="stat-label">Total Products</div>
                                    <a href="view-product.php" class="stat-link">View all products →</a>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xl-3 mb-3">
                                <div class="stat-card stat-categories">
                                    <i class="las la-th-list stat-icon"></i>
                                    <div class="stat-num"><?= $totalCategories ?></div>
                                    <div class="stat-label">Categories</div>
                                    <a href="view-category.php" class="stat-link">View all →</a>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xl-3 mb-3">
                                <div class="stat-card stat-subcats">
                                    <i class="las la-sitemap stat-icon"></i>
                                    <div class="stat-num"><?= $totalSubCats ?></div>
                                    <div class="stat-label">Sub Categories</div>
                                    <a href="view-subcategory.php" class="stat-link">View all →</a>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xl-3 mb-3">
                                <div class="stat-card stat-brands">
                                    <i class="las la-tag stat-icon"></i>
                                    <div class="stat-num"><?= $totalBrands ?></div>
                                    <div class="stat-label">Brands</div>
                                    <a href="view-brand.php" class="stat-link">View all →</a>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Products Table -->
                        <div class="card">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <h4 class="card-title mb-0">
                                    <i class="las la-clock-o mr-2" style="color:#4361ee;"></i> Recent Products
                                </h4>
                                <a href="add-product.php" class="btn btn-primary btn-sm">
                                    <i class="las la-plus mr-1"></i> Add Product
                                </a>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table mb-0">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Product</th>
                                                <th>Category</th>
                                                <th>Price</th>
                                                <th>Qty</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if ($recentProducts && $recentProducts->num_rows > 0): ?>
                                                <?php $i = 1;
                                                while ($p = $recentProducts->fetch_assoc()): ?>
                                                    <tr>
                                                        <td><?= $i++ ?></td>
                                                        <td>
                                                            <?php if (!empty($p['image']) && file_exists("uploads/products/" . $p['image'])): ?>
                                                                <img src="uploads/products/<?= htmlspecialchars($p['image']) ?>"
                                                                    style="width:38px;height:38px;object-fit:cover;border-radius:8px;border:2px solid #e8ecf4;margin-right:8px;"
                                                                    alt="">
                                                            <?php endif; ?>
                                                            <strong><?= htmlspecialchars($p['product_name']) ?></strong>
                                                        </td>
                                                        <td><?= htmlspecialchars($p['category_name'] ?? '—') ?></td>
                                                        <td><strong
                                                                style="color:#4361ee;">₹<?= number_format($p['product_price'], 2) ?></strong>
                                                        </td>
                                                        <td><?= $p['quantity'] ?></td>
                                                        <td>
                                                            <?php if ($p['product_status'] == 'Active'): ?>
                                                                <span class="badge-active">Active</span>
                                                            <?php elseif ($p['product_status'] == 'Inactive'): ?>
                                                                <span class="badge-inactive">Inactive</span>
                                                            <?php else: ?>
                                                                <span class="badge-oos">Out of Stock</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <!-- Edit Button -->
                                                            <a href="edit-product.php?id=<?= $p['product_id'] ?>"
                                                                class="btn btn-warning btn-sm"
                                                                style="border-radius:6px;width:32px;height:32px;padding:0;display:inline-flex;align-items:center;justify-content:center;"
                                                                title="Edit">
                                                                <i class="las la-edit"></i>
                                                            </a>
                                                            <!-- ✅ Delete Button -->
                                                            <button type="button"
                                                                class="btn btn-danger btn-sm ml-1 open-delete-modal"
                                                                style="border-radius:6px;width:32px;height:32px;padding:0;display:inline-flex;align-items:center;justify-content:center;"
                                                                data-id="<?= $p['product_id'] ?>"
                                                                data-name="<?= htmlspecialchars($p['product_name']) ?>"
                                                                title="Delete">
                                                                <i class="las la-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="7" class="text-center py-5" style="color:#8d9db5;">
                                                        <i class="las la-box"
                                                            style="font-size:32px;display:block;margin-bottom:8px;"></i>
                                                        Koi product nahi hai. <a href="add-product.php"
                                                            style="color:#4361ee;">Pehla product add karo!</a>
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

            <footer class="footer">
                <div class="container-fluid">
                    <div class="copyright">
                        2024 &copy; Admin Panel
                    </div>
                </div>
            </footer>
        </div>
        <!-- MAIN PANEL END -->

    </div>

    <script src="assets/js/core/jquery.3.2.1.min.js"></script>
    <script src="assets/js/core/popper.min.js"></script>
    <script src="assets/js/core/bootstrap.min.js"></script>
    <script src="assets/js/ready.min.js"></script>
    <script>
        $(document).ready(function () {

            // Sidebar accordion
            $('.sidebar .nav-item > a[href^="#"]').on('click', function (e) {
                e.preventDefault();
                var target = $(this).attr('href');
                var isOpen = $(target).hasClass('show');
                if (isOpen) {
                    $(target).collapse('hide');
                    $(this).attr('aria-expanded', 'false');
                } else {
                    $(target).collapse('show');
                    $(this).attr('aria-expanded', 'true');
                }
            });

            // ✅ Open delete modal
            $(document).on('click', '.open-delete-modal', function () {
                var id = $(this).data('id');
                var name = $(this).data('name');
                $('#modalProductName').text('"' + name + '"');
                $('#confirmDelete').attr('href', 'index.php?delete=' + id);
                $('#deleteModal').addClass('active');
            });

            // ✅ Cancel / close modal
            $('#cancelDelete').on('click', function () {
                $('#deleteModal').removeClass('active');
            });

            // Close modal on overlay click
            $('#deleteModal').on('click', function (e) {
                if ($(e.target).is('#deleteModal')) {
                    $('#deleteModal').removeClass('active');
                }
            });

            // ✅ Auto-hide success toast after 3 seconds
            if ($('#successToast').length) {
                setTimeout(function () {
                    $('#successToast').fadeOut(400, function () { $(this).remove(); });
                }, 3000);
            }

        });
    </script>
</body>

</html>