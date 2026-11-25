<?php
require_once __DIR__ . '/includes/shop_bootstrap.php';
require_once __DIR__ . '/admin/includes/auth.php';

if (is_logged_in()) {
    admin_redirect('dashboard.php');
}
if (current_customer()) {
    redirect('account.php');
}

$pageTitle = 'Sign in';
$email = '';
$error = '';
$returnTo = (string)($_GET['return_to'] ?? $_POST['return_to'] ?? '');
// Only honor known same-site destinations; never accept an arbitrary URL.
if (!in_array($returnTo, ['checkout.php', 'track.php'], true)) $returnTo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $email = strtolower(trim((string)($_POST['email'] ?? '')));
    $password = (string)($_POST['password'] ?? '');

    // Admins can use this shared sign-in form and are routed to the admin panel.
    // attempt_login enforces active status, session security, audit logging, and
    // the required password-change flow for temporary admin passwords.
    if (filter_var($email, FILTER_VALIDATE_EMAIL) && attempt_login($email, $password)) {
        unset($_SESSION['customer']);
        if (!empty(current_user()['must_change_password'])) {
            flash('warning', 'For security, set a new password before using the admin panel.');
            admin_redirect('change_password.php');
        }
        flash('success', 'Welcome back, ' . current_user()['name'] . '!');
        admin_redirect('dashboard.php');
    }

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $stmt = db()->prepare(
            'SELECT * FROM customer_users WHERE email = :email LIMIT 1'
        );
        $stmt->execute([':email' => $email]);
        $customer = $stmt->fetch();
    } else {
        $customer = false;
    }

    if ($customer && password_verify($password, $customer['password_hash'])) {
        customer_sign_in($customer);
        flash('success', 'Welcome back, ' . $customer['name'] . '.');
        redirect($returnTo !== '' ? $returnTo : 'account.php');
    }
    $error = 'The email or password is incorrect.';
}

include __DIR__ . '/includes/shop_header.php';
?>
<section class="section customer-auth-page customer-login-page">
  <div class="container" style="max-width:540px">
    <div class="section-header">
      <span class="section-eyebrow">MobileHub</span>
      <h2>Log in</h2>
      <p>Sign in to your customer or admin account.</p>
    </div>
    <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
    <form method="post" class="card"><div class="card-body p-4">
      <?= csrf_field() ?>
      <?php if ($returnTo !== ''): ?><input type="hidden" name="return_to" value="<?= e($returnTo) ?>"><?php endif; ?>
      <div class="mb-3"><label class="form-label" for="email">Email</label><input class="form-control" type="email" id="email" name="email" value="<?= e($email) ?>" autocomplete="email" required></div>
      <div class="mb-4"><label class="form-label" for="password">Password</label><input class="form-control" type="password" id="password" name="password" autocomplete="current-password" required></div>
      <button class="btn btn-primary w-100">Log in</button>
      <p class="text-center mb-0 mt-3">New to MobileHub? <a href="<?= e(shop_url('customer_register.php' . ($returnTo !== '' ? '?return_to=' . rawurlencode($returnTo) : ''))) ?>">Create an account</a></p>
    </div></form>
  </div>
</section>
<?php include __DIR__ . '/includes/shop_footer.php'; ?>
