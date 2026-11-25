<?php
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    admin_redirect('dashboard.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();

    $email    = trim($_POST['email']    ?? '');
    $password = (string)($_POST['password'] ?? '');

    if (!$email || !$password) {
        $errors[] = 'Email and password are required.';
    } elseif (!attempt_login($email, $password)) {
        $errors[] = 'Email and password is incorrect, or your account is disabled.';
    } else {
        if (!empty(current_user()['must_change_password'])) {
            flash('warning', 'For security, set a new password before using the admin panel.');
            admin_redirect('change_password.php');
        }
        flash('success', 'Welcome back, ' . current_user()['name'] . '!');
        admin_redirect('dashboard.php');
    }
    set_old(['email' => $email]);
}

$siteName = setting('site_name', APP_NAME);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login · <?= e($siteName) ?></title>
<link rel="icon" type="image/png" href="<?= e(url('assets/images/web-logo.png')) ?>">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e(url('assets/css/style.css') . '?v=' . (string)filemtime(ROOT_PATH . '/assets/css/style.css')) ?>">
</head>
<body>
<div class="auth-wrapper">
  <div class="card auth-card">
    <div class="card-body">

      <div class="auth-brand">
        <img class="admin-login-logo" src="<?= e(url('assets/images/mobilehub-logo.png')) ?>"
             alt="<?= e($siteName) ?>">
        <p class="text-muted small mb-0">Log in to your account</p>
      </div>

      <?php $loginFlashes = get_flashes(); if ($loginFlashes): ?>
        <div class="admin-toast-stack" aria-live="polite" aria-atomic="false">
          <?php foreach ($loginFlashes as $f): $toastType = in_array($f['type'], ['success', 'warning', 'danger', 'info'], true) ? $f['type'] : 'info'; ?>
            <div class="admin-toast" data-admin-toast data-toast-type="<?= e($toastType) ?>" role="status">
              <i class="bi <?= $toastType === 'success' ? 'bi-check-circle-fill' : ($toastType === 'warning' ? 'bi-exclamation-triangle-fill' : ($toastType === 'danger' ? 'bi-x-circle-fill' : 'bi-info-circle-fill')) ?>" aria-hidden="true"></i>
              <span><?= e($f['message']) ?></span>
              <button type="button" class="admin-toast-close" aria-label="Dismiss notification"><i class="bi bi-x-lg"></i></button>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <?php foreach ($errors as $err): ?>
        <div class="alert alert-danger py-2"><?= e($err) ?></div>
      <?php endforeach; ?>

      <form method="post" autocomplete="off" novalidate>
        <?= csrf_field() ?>
        <div class="mb-3">
          <label class="form-label">Email address</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
            <input type="email" name="email" class="form-control"
                   value="<?= old('email') ?>" required autofocus>
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-key"></i></span>
            <input type="password" name="password" class="form-control" required>
          </div>
        </div>
        <button class="btn btn-primary w-100">
          <i class="bi bi-box-arrow-in-right"></i> Log in
        </button>
      </form>

    </div>
  </div>
</div>
<?php clear_old(); ?>
<script src="<?= e(url('assets/js/script.js') . '?v=' . (string)filemtime(ROOT_PATH . '/assets/js/script.js')) ?>"></script>
</body>
</html>
