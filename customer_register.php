<?php
require_once __DIR__ . '/includes/shop_bootstrap.php';

if (current_customer()) {
    redirect('account.php');
}

$pageTitle = 'Create account';
$returnTo = (string)($_GET['return_to'] ?? $_POST['return_to'] ?? '');
if (!in_array($returnTo, ['checkout.php', 'track.php'], true)) $returnTo = '';
$errors = [];
$form = ['name' => '', 'email' => '', 'phone' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    foreach (array_keys($form) as $key) {
        $form[$key] = trim((string)($_POST[$key] ?? ''));
    }
    $password = (string)($_POST['password'] ?? '');
    $confirm = (string)($_POST['confirm_password'] ?? '');
    $form['email'] = strtolower($form['email']);

    if ($form['name'] === '' || mb_strlen($form['name']) > 150) {
        $errors[] = 'Enter your name (up to 150 characters).';
    }
    if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($form['email']) > 150) {
        $errors[] = 'Enter a valid email address.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Your password must be at least 8 characters.';
    }
    if ($password !== $confirm) {
        $errors[] = 'The passwords do not match.';
    }

    if (!$errors) {
        try {
            $pdo = db();
            $pdo->beginTransaction();
            $stmt = $pdo->prepare(
                'INSERT INTO customer_users (name, email, phone, password_hash) VALUES (:name, :email, :phone, :password)'
            );
            $stmt->execute([
                ':name' => $form['name'],
                ':email' => $form['email'],
                ':phone' => $form['phone'] ?: null,
                ':password' => password_hash($password, PASSWORD_DEFAULT),
            ]);
            $customerId = (int)$pdo->lastInsertId();

            // Attach previous guest orders using this email to the new account.
            $pdo->prepare(
                'UPDATE orders SET customer_id = :customer_id
                 WHERE customer_id IS NULL AND LOWER(customer_email) = :email'
            )->execute([':customer_id' => $customerId, ':email' => $form['email']]);
            $pdo->commit();

            $form['id'] = $customerId;
            customer_sign_in($form);
            flash('success', 'Your MobileHub account is ready.');
            redirect($returnTo !== '' ? $returnTo : 'account.php');
        } catch (PDOException $e) {
            if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
            $errors[] = (string)$e->getCode() === '23000'
                ? 'An account with this email already exists. Please log in.'
                : 'Could not create your account. Please try again.';
        } catch (Throwable $e) {
            if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
            $errors[] = 'Could not create your account. Please try again.';
        }
    }
}

include __DIR__ . '/includes/shop_header.php';
?>
<section class="section customer-auth-page customer-register-page">
  <div class="container" style="max-width:540px">
    <div class="section-header">
      <span class="section-eyebrow">MobileHub</span>
      <h2>Create Account</h2>
    </div>
    <?php if ($errors): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <form method="post" class="card"><div class="card-body p-4">
      <?= csrf_field() ?>
      <?php if ($returnTo !== ''): ?><input type="hidden" name="return_to" value="<?= e($returnTo) ?>"><?php endif; ?>
      <div class="mb-3"><label class="form-label" for="name">Full name</label><input class="form-control" id="name" name="name" maxlength="150" value="<?= e($form['name']) ?>" autocomplete="name" required></div>
      <div class="mb-3"><label class="form-label" for="email">Email</label><input class="form-control" type="email" id="email" name="email" maxlength="150" value="<?= e($form['email']) ?>" autocomplete="email" required></div>
      <div class="mb-3"><label class="form-label" for="phone">Phone (Optional)</label><input class="form-control" type="tel" id="phone" name="phone" maxlength="40" value="<?= e($form['phone']) ?>" autocomplete="tel"></div>
      <div class="mb-3"><label class="form-label" for="password">Password</label><input class="form-control" type="password" id="password" name="password" minlength="8" autocomplete="new-password" required><div class="form-text">Use at least 8 characters.</div></div>
      <div class="mb-4"><label class="form-label" for="confirm_password">Confirm password</label><input class="form-control" type="password" id="confirm_password" name="confirm_password" minlength="8" autocomplete="new-password" required></div>
      <button class="btn btn-primary w-100">Create account</button>
      <p class="text-center mb-0 mt-3">Already have an account? <a href="<?= e(shop_url('customer_login.php')) ?>">Log in</a></p>
    </div></form>
  </div>
</section>
<?php include __DIR__ . '/includes/shop_footer.php'; ?>
