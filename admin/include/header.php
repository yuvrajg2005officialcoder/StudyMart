<!DOCTYPE html>
<html>

<head>
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<title>Admin Panel</title>
	<meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no'
		name='viewport' />
	<link rel="stylesheet" href="assets/css/bootstrap.min.css">
	<link rel="stylesheet"
		href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i">
	<link rel="stylesheet" href="assets/css/ready.css">
	<link rel="stylesheet" href="assets/css/demo.css">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet"
		href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
	<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js"></script>

	<style>
		/* ===== BASE ===== */
		* {
			box-sizing: border-box;
		}

		/* ===== SIDEBAR ===== */
		.sidebar {
			background: #161b2e !important;
			box-shadow: 4px 0 20px rgba(0, 0, 0, 0.2);
			transition: width 0.3s ease, transform 0.3s ease;
		}

		.sidebar .sidebar-wrapper {
			background: #161b2e !important;
			overflow-y: auto;
			scrollbar-width: thin;
			scrollbar-color: #2d3561 #161b2e;
		}

		.sidebar .sidebar-wrapper::-webkit-scrollbar {
			width: 4px;
		}

		.sidebar .sidebar-wrapper::-webkit-scrollbar-track {
			background: #161b2e;
		}

		.sidebar .sidebar-wrapper::-webkit-scrollbar-thumb {
			background: #2d3561;
			border-radius: 4px;
		}

		.sidebar .sidebar-wrapper::-webkit-scrollbar-thumb:hover {
			background: #4361ee;
		}

		/* User section */
		.sidebar .user {
			background: #0f1322 !important;
			border-bottom: 1px solid rgba(255, 255, 255, 0.06);
			padding: 18px 16px !important;
			transition: background 0.2s ease;
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

		.sidebar .user .info .collapse .nav li a {
			color: #8a96b0 !important;
			font-size: 12px;
			padding: 6px 12px !important;
			margin: 0;
			border-radius: 6px;
			font-weight: 500;
			transition: all 0.2s ease;
		}

		.sidebar .user .info .collapse .nav li a:hover {
			color: #fff !important;
			background: rgba(255, 255, 255, 0.06) !important;
		}

		/* Section labels */
		.sidebar-section-label {
			color: #4a5578;
			font-size: 10px;
			font-weight: 800;
			letter-spacing: 1.5px;
			text-transform: uppercase;
			padding: 18px 20px 6px;
			list-style: none;
		}

		/* Nav items */
		.sidebar .nav .nav-item>a {
			color: #8a96b0 !important;
			border-radius: 12px !important;
			margin: 2px 10px !important;
			padding: 11px 16px !important;
			transition: all 0.25s ease;
			display: flex;
			align-items: center;
			font-weight: 600;
			font-size: 13.5px;
			cursor: pointer;
			text-decoration: none;
		}

		.sidebar .nav .nav-item>a:hover {
			background: rgba(255, 255, 255, 0.07) !important;
			color: #fff !important;
			transform: translateX(2px);
		}

		.sidebar .nav .nav-item>a i {
			color: #6c7a9c;
			font-size: 19px;
			margin-right: 10px;
			min-width: 22px;
			transition: color 0.2s, transform 0.2s;
		}

		.sidebar .nav .nav-item>a:hover i {
			transform: scale(1.1);
		}

		.sidebar .nav .nav-item>a p {
			color: inherit !important;
			font-weight: 600;
			font-size: 13.5px;
			margin: 0;
			flex: 1;
		}

		/* Active state */
		.sidebar .nav .nav-item.active>a {
			background: #ffffff !important;
			color: #1a1f36 !important;
			box-shadow: 0 4px 16px rgba(0, 0, 0, 0.18);
			border-radius: 12px !important;
			transform: none;
		}

		.sidebar .nav .nav-item.active>a i {
			color: #1a1f36 !important;
		}

		.sidebar .nav .nav-item.active>a p {
			color: #1a1f36 !important;
		}

		.sidebar .nav-item>a::after {
			display: none !important;
		}

		/* ===== SUB MENU — animated ===== */
		.sidebar .nav-collapse {
			background: rgba(0, 0, 0, 0.12) !important;
			border-radius: 10px;
			margin: 2px 10px !important;
			padding: 0 !important;
			max-height: 0;
			overflow: hidden;
			transition: max-height 0.35s cubic-bezier(0.4, 0, 0.2, 1),
				padding 0.2s ease,
				opacity 0.25s ease;
			opacity: 0;
		}

		.sidebar .nav-collapse.open {
			max-height: 300px;
			padding: 4px 0 !important;
			opacity: 1;
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
			transition: all 0.2s ease;
			text-decoration: none;
		}

		.sidebar .nav-collapse li a:hover {
			color: #fff !important;
			background: rgba(255, 255, 255, 0.07) !important;
			padding-left: 24px !important;
		}

		.sidebar .nav-collapse li.active a {
			color: #4361ee !important;
			font-weight: 700 !important;
			background: rgba(67, 97, 238, 0.12) !important;
		}

		.sidebar .nav-collapse li a i {
			font-size: 15px;
			margin-right: 8px;
			color: inherit;
		}

		/* Caret rotation */
		.sidebar .nav-item>a .caret {
			transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
			display: inline-block;
		}

		.sidebar .nav-item>a.menu-open .caret {
			transform: rotate(180deg);
		}

		/* ===== HEADER ===== */
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
			letter-spacing: 0.5px;
			transition: opacity 0.2s;
		}

		.logo-header .logo:hover {
			opacity: 0.85;
		}

		.navbar-header {
			background: #fff !important;
		}

		.nav-search .form-control {
			border-radius: 20px 0 0 20px !important;
			border: 1px solid #e8ecf4 !important;
			background: #f4f6fb !important;
			font-size: 13px;
			transition: border-color 0.2s, box-shadow 0.2s;
		}

		.nav-search .form-control:focus {
			border-color: #4361ee !important;
			box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1) !important;
			outline: none;
		}

		.nav-search .input-group-text {
			border-radius: 0 20px 20px 0 !important;
			background: #f4f6fb !important;
			border: 1px solid #e8ecf4 !important;
			border-left: none !important;
		}

		.notification {
			background: #e53935 !important;
			font-size: 9px !important;
			padding: 2px 5px !important;
			animation: pulse 2s infinite;
		}

		@keyframes pulse {

			0%,
			100% {
				transform: scale(1);
			}

			50% {
				transform: scale(1.15);
			}
		}

		.profile-pic span {
			font-weight: 600;
			color: #1a1f36 !important;
			font-size: 13px;
		}

		.profile-pic img {
			transition: transform 0.2s ease, box-shadow 0.2s ease;
		}

		.profile-pic:hover img {
			transform: scale(1.05);
			box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.3);
		}

		/* ===== MAIN PANEL ===== */
		.main-panel {
			background: #f0f2f8 !important;
		}

		.page-title {
			color: #1a1f36 !important;
			font-weight: 700 !important;
			font-size: 22px !important;
		}

		/* ===== CARDS ===== */
		.card {
			border: none !important;
			border-radius: 14px !important;
			box-shadow: 0 2px 15px rgba(0, 0, 0, 0.07) !important;
			transition: box-shadow 0.25s ease, transform 0.25s ease;
		}

		.card:hover {
			box-shadow: 0 6px 25px rgba(0, 0, 0, 0.11) !important;
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

		/* ===== TABLE ===== */
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
			transition: background 0.15s ease, transform 0.15s ease;
		}

		.table tbody tr:hover {
			background: #f8f9ff !important;
		}

		.table tbody td {
			padding: 14px 16px !important;
			vertical-align: middle !important;
			border: none !important;
		}

		/* ===== BUTTONS ===== */
		.btn-primary {
			background: linear-gradient(135deg, #4361ee, #6c8fff) !important;
			border: none !important;
			border-radius: 8px !important;
			font-weight: 600 !important;
			box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3) !important;
			padding: 8px 18px !important;
			transition: all 0.25s ease !important;
		}

		.btn-primary:hover {
			transform: translateY(-2px);
			box-shadow: 0 8px 20px rgba(67, 97, 238, 0.4) !important;
		}

		.btn-primary:active {
			transform: translateY(0);
		}

		/* Table action buttons */
		.btn-action {
			border-radius: 7px !important;
			font-weight: 600 !important;
			font-size: 12.5px !important;
			padding: 5px 12px !important;
			transition: all 0.2s ease !important;
		}

		.btn-action:hover {
			transform: translateY(-1px);
			box-shadow: 0 4px 10px rgba(0, 0, 0, 0.12);
		}

		/* ===== PAGE ENTER ANIMATION ===== */
		.content {
			animation: fadeInUp 0.4s ease both;
		}

		@keyframes fadeInUp {
			from {
				opacity: 0;
				transform: translateY(16px);
			}

			to {
				opacity: 1;
				transform: translateY(0);
			}
		}

		/* ===== MODAL ANIMATIONS ===== */
		.modal-overlay {
			display: none;
			position: fixed;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			background: rgba(15, 19, 34, 0.6);
			z-index: 1040;
			backdrop-filter: blur(3px);
			animation: fadeIn 0.2s ease;
		}

		.modal-box-wrap {
			display: none;
			position: fixed;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			z-index: 1050;
			align-items: center;
			justify-content: center;
		}

		.modal-box-wrap.open {
			display: flex;
			animation: modalIn 0.28s cubic-bezier(0.34, 1.56, 0.64, 1) both;
		}

		@keyframes fadeIn {
			from {
				opacity: 0;
			}

			to {
				opacity: 1;
			}
		}

		@keyframes modalIn {
			from {
				opacity: 0;
				transform: scale(0.88) translateY(20px);
			}

			to {
				opacity: 1;
				transform: scale(1) translateY(0);
			}
		}

		/* ===== IMAGE THUMBNAIL ===== */
		.thumb-img {
			width: 52px;
			height: 52px;
			object-fit: contain;
			border-radius: 10px;
			border: 2px solid #eef0f8;
			padding: 4px;
			transition: transform 0.2s ease, box-shadow 0.2s ease;
		}

		.thumb-img:hover {
			transform: scale(1.1);
			box-shadow: 0 4px 14px rgba(0, 0, 0, 0.15);
			cursor: zoom-in;
		}

		.thumb-placeholder {
			width: 52px;
			height: 52px;
			background: #f4f5f7;
			border-radius: 10px;
			display: flex;
			align-items: center;
			justify-content: center;
			border: 2px solid #eef0f8;
		}

		/* ===== FORM IMPROVEMENTS ===== */
		.form-control {
			border-radius: 9px !important;
			border: 1.5px solid #e2e8f0 !important;
			padding: 10px 14px !important;
			font-size: 13.5px !important;
			transition: border-color 0.2s, box-shadow 0.2s !important;
			color: #1a1f36 !important;
		}

		.form-control:focus {
			border-color: #4361ee !important;
			box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.12) !important;
			outline: none !important;
		}

		.form-label {
			font-weight: 600;
			font-size: 13px;
			color: #3a4158;
			margin-bottom: 6px;
		}

		/* Upload drop zone */
		.upload-zone {
			border: 2px dashed #dde1ef;
			border-radius: 12px;
			padding: 20px;
			text-align: center;
			cursor: pointer;
			transition: border-color 0.2s ease, background 0.2s ease;
		}

		.upload-zone:hover {
			border-color: #4361ee;
			background: rgba(67, 97, 238, 0.03);
		}

		.upload-zone.has-image {
			border-color: #4361ee;
			border-style: solid;
		}

		/* Badge */
		.badge-count {
			background: linear-gradient(135deg, #4361ee, #6c8fff);
			color: #fff;
			font-size: 13px;
			padding: 6px 14px;
			border-radius: 20px;
			font-weight: 700;
			box-shadow: 0 4px 12px rgba(67, 97, 238, 0.25);
		}
	</style>
</head>

<body>
	<div class="wrapper">

		<!-- MAIN HEADER -->
		<div class="main-header">
			<div class="logo-header">
				<a href="index.php" class="logo">
					<i class="las la-store mr-1"></i> Admin Panel
				</a>
				<button class="navbar-toggler sidenav-toggler ml-auto" type="button">
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
							<ul class="dropdown-menu notif-box">
								<li>
									<div class="dropdown-title">3 new notifications</div>
								</li>
								<li>
									<div class="notif-center">
										<a href="#">
											<div class="notif-icon notif-primary"><i class="las la-user-plus"></i></div>
											<div class="notif-content">
												<span class="block">New user registered</span>
												<span class="time">5 minutes ago</span>
											</div>
										</a>
									</div>
								</li>
								<li><a class="see-all" href="javascript:void(0);"><strong>See all
											notifications</strong></a></li>
							</ul>
						</li>
						<li class="nav-item dropdown">
							<a class="dropdown-toggle profile-pic" data-toggle="dropdown" href="#"
								aria-expanded="false">
								<img src="assets/img/profile.jpg" alt="user-img" width="36" class="img-circle"
									style="border:2px solid #4361ee;border-radius:50%;">
								<span>Admin</span>
							</a>
							<ul class="dropdown-menu dropdown-user">
								<li>
									<div class="user-box">
										<div class="u-img"><img src="assets/img/profile.jpg" alt="user"></div>
										<div class="u-text">
											<h4>Admin</h4>
											<p class="text-muted">admin@email.com</p>
										</div>
									</div>
								</li>
								<div class="dropdown-divider"></div>
								<a class="dropdown-item" href="#"><i class="las la-power-off text-danger mr-1"></i>
									Logout</a>
							</ul>
						</li>
					</ul>
				</div>
			</nav>
		</div>
		<!-- MAIN HEADER END -->

		<!-- SIDEBAR -->
		<div class="sidebar">
			<div class="scrollbar-inner sidebar-wrapper">

				<div class="user">
					<div class="photo"><img src="assets/img/profile.jpg"
							style="border:2px solid #4361ee;border-radius:50%;"></div>
					<div class="info">
						<a href="#" onclick="toggleUserMenu(event)">
							<span>Admin<span class="user-level">Administrator</span></span>
						</a>
						<div class="clearfix"></div>
						<div id="userMenu" style="display:block;">
							<ul class="nav">
								<li><a href="#"><span class="link-collapse">My Profile</span></a></li>
								<li><a href="#"><span class="link-collapse">Settings</span></a></li>
							</ul>
						</div>
					</div>
				</div>

				<ul class="nav" style="padding:10px 0 20px;">

					<!-- Dashboard -->
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

		<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

		<script>
			function toggleMenu(e, menuId) {
				e.preventDefault();
				e.stopPropagation();
				var menu = document.getElementById(menuId);
				var link = e.currentTarget;
				var isOpen = menu.classList.contains('open');
				if (isOpen) {
					menu.classList.remove('open');
					link.classList.remove('menu-open');
				} else {
					menu.classList.add('open');
					link.classList.add('menu-open');
				}
			}
			function toggleUserMenu(e) {
				e.preventDefault();
				var m = document.getElementById('userMenu');
				m.style.display = m.style.display === 'none' ? 'block' : 'none';
			}
		</script>