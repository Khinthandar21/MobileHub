<?php
/**
 * Top of every authenticated page.
 * Pages should set $pageTitle before including this file.
 */
require_once __DIR__ . '/auth.php';
require_login();

$pageTitle = $pageTitle ?? 'Dashboard';
$me        = current_user();
$siteName  = setting('site_name', APP_NAME);
?>
<!doctype html>
<html lang="en" data-bs-theme="light">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?> · <?= e($siteName) ?></title>
<link rel="icon" type="image/png" href="<?= e(url('assets/images/web-logo.png')) ?>">
<script>(function(){try{const t=localStorage.getItem('mobilehub-theme');if(t==='dark'||t==='light')document.documentElement.setAttribute('data-bs-theme',t)}catch(e){}})();</script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e(url('assets/css/style.css') . '?v=' . (string)filemtime(ROOT_PATH . '/assets/css/style.css')) ?>">
</head>
<body>
<div class="app" id="adminApp">
  <script>
    // Apply the saved desktop state before rendering the sidebar contents, so
    // navigation between admin pages does not flash the expanded sidebar.
    try {
      if (window.matchMedia('(min-width: 992px)').matches && localStorage.getItem('mobilehub-admin-sidebar-collapsed') === '1') {
        document.getElementById('adminApp').classList.add('sidebar-collapsed');
      }
    } catch (error) {}
  </script>

  <?php include __DIR__ . '/sidebar.php'; ?>
  <div class="sidebar-backdrop" id="sidebarBackdrop" aria-hidden="true"></div>

  <main class="app-main">

    <!-- Top bar -->
    <nav class="topbar">
      <button class="btn btn-light btn-sm" id="sidebarToggle" type="button"
              aria-label="Toggle navigation" aria-controls="sidebar" aria-expanded="true" title="Toggle navigation">
        <i class="bi bi-list" aria-hidden="true"></i>
      </button>

      <h1 class="topbar-title"><?= e($pageTitle) ?></h1>

      <div class="ms-auto d-flex align-items-center gap-2">
        <button class="btn btn-light btn-sm" id="themeToggle" title="Switch to dark mode" aria-label="Switch to dark mode" type="button">
          <i class="bi bi-moon-stars"></i>
        </button>

        <div class="dropdown">
          <button class="btn btn-light btn-sm dropdown-toggle d-flex align-items-center gap-2"
                  data-bs-toggle="dropdown" aria-expanded="false">
            <img src="<?= e(avatar_url($me['avatar'] ?? null, $me['name'])) ?>"
                 class="rounded-circle" width="28" height="28" alt="">
            <span class="d-none d-sm-inline"><?= e($me['name']) ?></span>
          </button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><h6 class="dropdown-header"><?= e($me['email']) ?></h6></li>
            <li><a class="dropdown-item" href="<?= e(admin_url('profile.php')) ?>">
              <i class="bi bi-person"></i> Profile</a></li>
            <?php if (has_role('admin')): ?>
              <li><a class="dropdown-item" href="<?= e(admin_url('settings.php')) ?>">
                <i class="bi bi-gear"></i> Settings</a></li>
            <?php endif; ?>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="<?= e(admin_url('logout.php')) ?>">
              <i class="bi bi-box-arrow-right"></i> Logout</a></li>
          </ul>
        </div>
      </div>
    </nav>

    <div class="app-content">
      <?php $adminFlashes = get_flashes(); if ($adminFlashes): ?>
        <div class="admin-toast-stack" aria-live="polite" aria-atomic="false">
          <?php foreach ($adminFlashes as $f): $toastType = in_array($f['type'], ['success', 'warning', 'danger', 'info'], true) ? $f['type'] : 'info'; ?>
            <div class="admin-toast" data-admin-toast data-toast-type="<?= e($toastType) ?>" role="status">
              <i class="bi <?= $toastType === 'success' ? 'bi-check-circle-fill' : ($toastType === 'warning' ? 'bi-exclamation-triangle-fill' : ($toastType === 'danger' ? 'bi-x-circle-fill' : 'bi-info-circle-fill')) ?>" aria-hidden="true"></i>
              <span><?= e($f['message']) ?></span>
              <button type="button" class="admin-toast-close" aria-label="Dismiss notification"><i class="bi bi-x-lg"></i></button>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
