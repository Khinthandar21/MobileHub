<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

$pageTitle = 'Change password';
$errors = [];
$user = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $password = (string)($_POST['password'] ?? '');
    $confirm = (string)($_POST['confirm_password'] ?? '');

    if (strlen($password) < 8) $errors[] = 'Your new password must be at least 8 characters.';
    if ($password !== $confirm) $errors[] = 'The new passwords do not match.';

    if (!$errors) {
        $stmt = db()->prepare('SELECT password FROM admin_users WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => (int)$user['id']]);
        $currentHash = (string)$stmt->fetchColumn();
        if ($currentHash !== '' && password_verify($password, $currentHash)) {
            $errors[] = 'Choose a password different from your temporary password.';
        }
    }

    if (!$errors) {
        db()->prepare('UPDATE admin_users SET password = :password, must_change_password = 0 WHERE id = :id')
            ->execute([
                ':password' => password_hash($password, PASSWORD_DEFAULT),
                ':id' => (int)$user['id'],
            ]);
        log_activity('password.change', 'Completed required first-login password change');
        refresh_user_session();
        flash('success', 'Your password has been changed successfully.');
        admin_redirect('dashboard.php');
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
  <div class="col-md-8 col-lg-6 col-xl-5">
    <div class="card shadow-sm">
      <div class="card-body p-4 p-lg-5">
        <div class="text-center mb-4">
          <div class="stat-icon bg-indigo mx-auto mb-3"><i class="bi bi-shield-lock"></i></div>
          <h2 class="h4 mb-2">Set your new password</h2>
          <p class="text-muted mb-0">For security, replace the temporary password before continuing.</p>
        </div>

        <?php foreach ($errors as $error): ?><div class="alert alert-danger py-2"><?= e($error) ?></div><?php endforeach; ?>

        <form method="post" autocomplete="off">
          <?= csrf_field() ?>
          <div class="mb-3">
            <label class="form-label" for="password">New password</label>
            <input class="form-control" type="password" id="password" name="password" minlength="8" autocomplete="new-password" required autofocus>
            <div class="form-text">Use at least 8 characters and avoid reusing your temporary password.</div>
          </div>
          <div class="mb-4">
            <label class="form-label" for="confirm_password">Confirm new password</label>
            <input class="form-control" type="password" id="confirm_password" name="confirm_password" minlength="8" autocomplete="new-password" required>
          </div>
          <button class="btn btn-primary w-100" type="submit"><i class="bi bi-check2-circle me-1"></i>Save new password</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
