<?php
require_once __DIR__ . '/shop_bootstrap.php';

$pageTitle = $pageTitle ?? '';
$siteName  = setting('site_name', APP_NAME);
$fullTitle = $pageTitle !== '' ? "$pageTitle · $siteName" : $siteName;
$cats      = shop_active_categories();
$customer  = current_customer();

// Highlight current nav link.
$current = basename($_SERVER['PHP_SELF'] ?? '');
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($fullTitle) ?></title>
<meta name="description" content="<?= e(setting('site_about', 'Shop the latest products.')) ?>">
<link rel="icon" type="image/png" href="<?= e(url('assets/images/web-logo.png')) ?>">
<script>(function(){try{const t=localStorage.getItem('mobilehub-theme');if(t==='dark'||t==='light')document.documentElement.setAttribute('data-bs-theme',t)}catch(e){}})();</script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e(url('assets/css/shop.css') . '?v=' . (string)filemtime(ROOT_PATH . '/assets/css/shop.css')) ?>">
</head>
<body class="<?= e($bodyClass ?? '') ?>">

<header class="site-header">
  <div class="container">
    <nav class="navbar navbar-expand-lg">
      <a href="<?= e(shop_url()) ?>" class="navbar-brand">
        <img class="brand-logo" src="<?= e(url('assets/images/mobilehub-logo.png')) ?>"
             alt="<?= e($siteName) ?>">
      </a>

      <!-- Always-visible cart + mobile toggle (right side on small screens) -->
      <div class="d-flex align-items-center header-actions order-lg-last">
        <a href="<?= e(shop_url('cart.php')) ?>" class="cart-link" title="Cart">
          <i class="bi bi-bag"></i>
          <?php $cnt = cart_count(); if ($cnt > 0): ?>
            <span class="cart-count"><?= $cnt ?></span>
          <?php endif; ?>
        </a>
        <button type="button" id="customerThemeToggle" class="theme-toggle" title="Switch to dark mode" aria-label="Switch to dark mode">
          <i class="bi bi-moon-stars" aria-hidden="true"></i>
        </button>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#mainNav" aria-controls="mainNav"
                aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
      </div>

      <div class="collapse navbar-collapse" id="mainNav">
        <ul class="navbar-nav mx-auto">
          <li class="nav-item">
            <a class="nav-link <?= $current === 'index.php' ? 'active' : '' ?>"
               href="<?= e(shop_url()) ?>">Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= $current === 'shop.php' ? 'active' : '' ?>"
               href="<?= e(shop_url('shop.php')) ?>">Shop</a>
          </li>
          <?php if ($cats): ?>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                Categories
              </a>
              <ul class="dropdown-menu">
                <?php foreach ($cats as $c): ?>
                  <li>
                    <a class="dropdown-item"
                       href="<?= e(shop_url('category.php?slug=' . urlencode($c['slug']))) ?>">
                      <?= e($c['name']) ?>
                    </a>
                  </li>
                <?php endforeach; ?>
              </ul>
            </li>
          <?php endif; ?>
          <li class="nav-item">
            <a class="nav-link" href="<?= e(shop_url('index.php#about')) ?>">About</a>
          </li>
          <?php if ($customer): ?>
            <li class="nav-item">
              <a class="nav-link <?= $current === 'track.php' ? 'active' : '' ?>"
                 href="<?= e(shop_url('track.php')) ?>">Track order</a>
            </li>
          <?php endif; ?>
          <?php if ($customer): ?>
            <li class="nav-item">
              <a class="nav-link customer-account-link <?= $current === 'account.php' ? 'active' : '' ?>"
                 href="<?= e(shop_url('account.php')) ?>" title="Profile" aria-label="Profile">
                <?php if (!empty($customer['avatar'])): ?>
                  <img src="<?= e(avatar_url($customer['avatar'], $customer['name'])) ?>" alt="" class="customer-nav-avatar customer-photo-preview" data-photo-name="<?= e($customer['name']) ?>">
                <?php else: ?>
                  <i class="bi bi-person-circle" aria-hidden="true"></i>
                <?php endif; ?>
                <span class="customer-account-label">Profile</span>
              </a>
            </li>
            <li class="nav-item">
              <form method="post" action="<?= e(shop_url('customer_logout.php')) ?>" class="customer-signout-form">
                <?= csrf_field() ?>
                <button type="submit" class="nav-link customer-signout-link"><i class="bi bi-box-arrow-right" aria-hidden="true"></i> Log out</button>
              </form>
            </li>
          <?php else: ?>
            <li class="nav-item">
              <a class="nav-link <?= $current === 'customer_login.php' ? 'active' : '' ?>"
                 href="<?= e(shop_url('customer_login.php')) ?>">Log in</a>
            </li>
          <?php endif; ?>
        </ul>

        <form class="d-flex header-search" method="get" action="<?= e(shop_url('shop.php')) ?>" autocomplete="off">
          <div class="search-box">
            <i class="bi bi-search"></i>
            <input type="search" name="q" class="form-control" autocomplete="off"
                   placeholder="Search…"
                   value="<?= e($_GET['q'] ?? '') ?>">
          </div>
        </form>
      </div>
    </nav>
  </div>
</header>

<div class="modal fade" id="customerPhotoModal" tabindex="-1" aria-labelledby="customerPhotoModalTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 bg-transparent shadow-none">
      <div class="modal-header border-0 justify-content-end p-0 mb-2">
        <h2 class="visually-hidden" id="customerPhotoModalTitle">Profile photo</h2>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body d-flex justify-content-center p-0">
        <img id="customerPhotoModalImage" class="customer-photo-large rounded-circle" alt="Customer profile photo">
      </div>
    </div>
  </div>
</div>

<main class="site-main">
