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

<title>Reservations | Joy Restaurant Admin</title><meta name="robots" content="noindex">
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
 <h1 class="h4 mb-0 me-auto">Reservations</h1>
 <a href="../index.html" target="_blank" class="btn-ghost py-1 px-3 d-none d-sm-inline-block"><i class="fa-solid fa-globe me-1"></i>View site</a>
 <button class="theme-toggle" aria-label="Toggle theme"><i class="fa-solid fa-lightbulb"></i></button>
 <div class="cp-avatar" title="Joy Admin">JA</div></header>
<!-- END: ADMIN TOP NAV -->
<main class="cp-content">
<!-- Status chips: click one to filter the table. Numbers come from the data. -->
<div class="row g-3 mb-3">
 <div class="col-6 col-lg-3"><button class="sc-chip active" data-st=""><span class="sc-ic all"><i class="fa-solid fa-layer-group"></i></span><span><b id="cAll">–</b><small>All requests</small></span></button></div>
 <div class="col-6 col-lg-3"><button class="sc-chip" data-st="Pending"><span class="sc-ic pend"><i class="fa-regular fa-hourglass-half"></i></span><span><b id="cPending">–</b><small>Pending</small></span></button></div>
 <div class="col-6 col-lg-3"><button class="sc-chip" data-st="Confirmed"><span class="sc-ic conf"><i class="fa-solid fa-check"></i></span><span><b id="cConfirmed">–</b><small>Confirmed</small></span></button></div>
 <div class="col-6 col-lg-3"><button class="sc-chip" data-st="Cancelled"><span class="sc-ic canc"><i class="fa-solid fa-xmark"></i></span><span><b id="cCancelled">–</b><small>Cancelled</small></span></button></div>
</div>
<div class="card-joy prem-card">
 <div class="prem-tools">
  <div class="prem-search"><i class="fa-solid fa-magnifying-glass"></i><input id="search" class="form-control" type="search" placeholder="Search name, email, phone or reference…" aria-label="Search reservations"></div>
  <input id="dateF" class="form-control prem-date" type="date" aria-label="Filter by date">
  <button class="btn-ghost py-1" id="clearF" type="button"><i class="fa-solid fa-rotate-left me-1"></i>Clear</button>
 </div>
 <div class="prem-wrap"><table class="prem-table"><thead><tr><th>Guest</th><th>Date &amp; time</th><th>Party</th><th>Status</th><th>Reference</th><th class="text-end">Actions</th></tr></thead><tbody id="rows"></tbody></table></div>
 <div class="prem-foot"><span id="showing" class="sec-sub small"></span><ul class="pagination pagination-sm mb-0" id="pager"></ul></div>
</div>
<!-- Details + status change -->
<div class="modal fade" id="viewModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content card-joy">
 <div class="modal-header"><h2 class="h5 modal-title">Reservation details</h2><button class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button></div>
 <div class="modal-body"><div id="detail"></div><label class="form-label mt-3" for="newStatus">Status</label><select id="newStatus" class="form-select"><option>Pending</option><option>Confirmed</option><option>Cancelled</option></select>
  <p class="small sec-sub mt-2 mb-0">Confirmed or Cancelled also emails the customer.</p></div>
 <div class="modal-footer"><button class="btn-ghost py-1" data-bs-dismiss="modal">Close</button><button class="btn-joy py-1" id="saveStatus">Update status</button></div></div></div></div>
<!-- Cancel confirmation -->
<div class="modal fade" id="confirmModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content card-joy"><div class="modal-body text-center p-4">
 <h2 class="h5">Cancel this reservation?</h2><p class="sec-sub" id="confirmText"></p>
 <button class="btn-ghost me-2" data-bs-dismiss="modal">Keep it</button><button class="btn-joy" id="confirmOk">Yes, cancel</button></div></div></div></div>
</main></div>
<div class="modal fade" id="logoutModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content card-joy"><div class="modal-body text-center p-4">
<h2 class="h5">Log out?</h2><p class="sec-sub">You will need to sign in again.</p>
<button class="btn-ghost me-2" data-bs-dismiss="modal">Cancel</button><button class="btn-joy" id="confirmLogout">Log out</button></div></div></div></div>
<div id="toastBox"></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/data.js"></script><script src="js/admin-data.js"></script><script src="js/admin-api.js"></script><script src="js/cpanel.js"></script><script src="js/reservations.js"></script></body></html>