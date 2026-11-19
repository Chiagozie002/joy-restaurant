<?php require __DIR__ . '/../includes/auth_guard.php'; ?>
<!DOCTYPE html>
<html lang="en" data-theme="dark"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="icon" type="image/svg+xml" href="../assets/images/favicon.svg">
<link rel="icon" type="image/png" sizes="32x32" href="../assets/images/favicon-32.png">
<link rel="icon" type="image/png" sizes="16x16" href="../assets/images/favicon-16.png">
<link rel="shortcut icon" href="../favicon.ico">
<link rel="apple-touch-icon" href="../assets/images/apple-touch-icon.png">
<link rel="manifest" href="../assets/images/site.webmanifest">
<meta name="theme-color" content="#F47716">

<title>Dashboard | Joy Restaurant Admin</title><meta name="robots" content="noindex">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Playfair+Display:wght@600;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@7.0.0/css/all.min.css" rel="stylesheet">
<link href="../assets/css/style.css" rel="stylesheet"><link href="css/cpanel.css" rel="stylesheet"><script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script></head>
<body>
<!-- START: ADMIN SIDEBAR (desktop) -->
<aside class="cp-side desk"><div class="brand fs-4 mb-4 px-2"><i class="fa-solid fa-utensils me-2"></i>Joy Admin</div><a href="index.php"><i class="fa-solid fa-gauge"></i>Dashboard</a><a href="reservations.php"><i class="fa-solid fa-calendar-check"></i>Reservations</a><a href="products.php"><i class="fa-solid fa-utensils"></i>Menu Products</a><a href="add-product.php"><i class="fa-solid fa-plus"></i>Add Product</a><a href="messages.php"><i class="fa-solid fa-envelope"></i>Messages Inbox</a><a href="trash.php"><i class="fa-solid fa-trash"></i>Trash</a><a href="profile.php"><i class="fa-solid fa-user"></i>My Profile</a><a href="settings.php"><i class="fa-solid fa-gear"></i>Website Settings</a><button class="cp-link" data-logout><i class="fa-solid fa-right-from-bracket"></i>Logout</button></aside>
<!-- END: ADMIN SIDEBAR -->
<!-- Mobile sidebar: same links inside a Bootstrap offcanvas -->
<div class="offcanvas offcanvas-start" id="cpMenu" tabindex="-1" aria-label="Admin menu"><div class="oc-head"><div class="brand"><i class="fa-solid fa-utensils"></i>Joy<small>Admin Panel</small></div><button class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button></div>
<div class="offcanvas-body cp-side" style="position:static;width:auto;border:0;background:none;padding-top:.8rem"><a href="index.php"><i class="fa-solid fa-gauge"></i>Dashboard</a><a href="reservations.php"><i class="fa-solid fa-calendar-check"></i>Reservations</a><a href="products.php"><i class="fa-solid fa-utensils"></i>Menu Products</a><a href="add-product.php"><i class="fa-solid fa-plus"></i>Add Product</a><a href="messages.php"><i class="fa-solid fa-envelope"></i>Messages Inbox</a><a href="trash.php"><i class="fa-solid fa-trash"></i>Trash</a><a href="profile.php"><i class="fa-solid fa-user"></i>My Profile</a><a href="settings.php"><i class="fa-solid fa-gear"></i>Website Settings</a><button class="cp-link" data-logout><i class="fa-solid fa-right-from-bracket"></i>Logout</button></div>
<div class="oc-foot"><div class="d-flex align-items-center gap-3"><div class="cp-avatar">JA</div><div><b class="text-white">Joy Admin</b><div class="small">Restaurant Manager</div></div></div></div></div>
<div class="cp-main">
<!-- START: ADMIN TOP NAV -->
<header class="cp-top"><button class="nav-burger d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#cpMenu" aria-label="Menu"><i class="fa-solid fa-bars"></i></button>
 <h1 class="h4 mb-0 me-auto">Dashboard</h1>
 <a href="../index.html" target="_blank" class="btn-ghost py-1 px-3 d-none d-sm-inline-block"><i class="fa-solid fa-globe me-1"></i>View site</a>
 <button class="theme-toggle" aria-label="Toggle theme"><i class="fa-solid fa-lightbulb"></i></button>
 <div class="cp-avatar" title="Joy Admin">JA</div></header>
<!-- END: ADMIN TOP NAV -->
<main class="cp-content">
<!-- Welcome banner with quick actions -->
<div class="welcome mb-4">
  <div><div class="welcome-eyebrow" id="today"></div><h2 class="welcome-title">Welcome back, <span id="adminName">Admin</span></h2><p class="mb-0 sec-sub small">Here is what is happening at your restaurant.</p></div>
  <div class="d-flex flex-wrap gap-2"><a class="btn-joy py-2" href="add-product.php"><i class="fa-solid fa-plus me-1"></i>Add product</a><a class="btn-ghost py-2" href="reservations.php"><i class="fa-regular fa-calendar-check me-1"></i>Reservations</a></div>
</div>
<!-- Stat cards: each one links to its page -->
<div class="row g-3 mb-4" id="stats">
 <div class="col-6 col-xl-3"><a class="card-joy stat dash-card" href="reservations.php"><i class="fa-solid fa-calendar-check"></i><div><b data-s="res">–</b><span>Reservations</span></div><em class="fa-solid fa-arrow-right"></em></a></div>
 <div class="col-6 col-xl-3"><a class="card-joy stat dash-card s2" href="products.php"><i class="fa-solid fa-utensils"></i><div><b data-s="prod">–</b><span>Menu products</span></div><em class="fa-solid fa-arrow-right"></em></a></div>
 <div class="col-6 col-xl-3"><a class="card-joy stat dash-card s3" href="messages.php"><i class="fa-solid fa-envelope"></i><div><b data-s="msg">–</b><span>Messages</span></div><em class="fa-solid fa-arrow-right"></em></a></div>
 <div class="col-6 col-xl-3"><a class="card-joy stat dash-card s4" href="trash.php"><i class="fa-solid fa-trash"></i><div><b data-s="trash">–</b><span>In Trash</span></div><em class="fa-solid fa-arrow-right"></em></a></div></div>
<!-- Charts -->
<div class="row g-3 mb-4">
 <div class="col-lg-8"><div class="card-joy dash-card"><div class="card-head"><div><h2 class="h6">Reservation trend</h2><small class="sec-sub">Requests received, last 7 days</small></div></div><div class="chart-box"><canvas id="trend"></canvas></div></div></div>
 <div class="col-lg-4"><div class="card-joy dash-card"><div class="card-head"><div><h2 class="h6">Status split</h2><small class="sec-sub">All reservations</small></div></div><div class="chart-box"><canvas id="split"></canvas></div></div></div></div>
<!-- Lists: align-items-start so every card is only as tall as its own content -->
<div class="row g-3 align-items-start">
 <div class="col-lg-7 d-grid gap-3">
  <div class="card-joy dash-card"><div class="card-head"><h2 class="h6">Recent reservations</h2><a class="view-more" href="reservations.php">View more</a></div><div id="recentRes"><div class="skeleton skel-list"></div></div></div>
  <div class="card-joy dash-card"><div class="card-head"><h2 class="h6">Recent messages</h2><a class="view-more" href="messages.php">View more</a></div><div id="recentMsg"><div class="skeleton skel-list"></div></div></div>
 </div>
 <div class="col-lg-5 d-grid gap-3">
  <div class="card-joy dash-card text-center"><div class="card-head"><h2 class="h6">Digital menu QR</h2></div>
   <div class="qr-box"><i class="fa-solid fa-qrcode"></i></div><!-- Replace with your generated QR image -->
   <p class="small sec-sub mt-3 mb-2 text-break" id="menuUrl"></p>
   <div class="d-flex gap-2 justify-content-center flex-wrap"><button class="btn-ghost py-1" id="copyUrl">Copy URL</button><a class="btn-joy py-1" href="../emenu/index.html" target="_blank">Open menu</a></div></div>
  <div class="card-joy dash-card"><div class="card-head"><h2 class="h6">Menu products</h2><a class="view-more" href="products.php">View more</a></div><div id="recentProd"><div class="skeleton skel-list"></div></div></div>
 </div>
</div>
</main></div>
<div class="modal fade" id="logoutModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content card-joy"><div class="modal-body text-center p-4">
<h2 class="h5">Log out?</h2><p class="sec-sub">You will need to sign in again.</p>
<button class="btn-ghost me-2" data-bs-dismiss="modal">Cancel</button><button class="btn-joy" id="confirmLogout">Log out</button></div></div></div></div>
<div id="toastBox"></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/data.js"></script><script src="js/admin-data.js"></script><script src="js/admin-api.js"></script><script src="js/cpanel.js"></script><script src="js/dashboard.js"></script></body></html>