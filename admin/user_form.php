<?php
require_once __DIR__ . '/includes/auth.php';
require_role('admin');

$id = max(0, (int)($_GET['id'] ?? 0));
$me = current_user();
$pageTitle = $id ? 'Edit admin user' : 'Add admin user';
$errors = [];
$isSelf = false;
$user = [
    'id' => 0,
    'name' => '',
    'email' => '',
    'role' => 'editor',
    'status' => 'active',
];

if ($id > 0) {
    $stmt = db()->prepare("SELECT id, name, email, role, status FROM admin_users WHERE id = :id AND role IN ('admin', 'editor') LIMIT 1");
    $stmt->execute([':id' => $id]);
    $user = $stmt->fetch();
    if (!$user) {
        flash('warning', 'Admin user not found.');
        admin_redirect('users.php');
    }
    $isSelf = (int)$user['id'] === (int)$me['id'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $name = trim((string)($_POST['name'] ?? ''));
    $email = strtolower(trim((string)($_POST['email'] ?? '')));
    $password = (string)($_POST['password'] ?? '');
    $role = $isSelf ? $user['role'] : (string)($_POST['role'] ?? 'editor');
    $status = $isSelf ? $user['status'] : (string)($_POST['status'] ?? 'active');

    if ($name === '' || mb_strlen($name) > 100) $errors[] = 'Enter a name with no more than 100 characters.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) $errors[] = 'Enter a valid email address.';
    if (!in_array($role, ['admin', 'editor'], true)) $errors[] = 'Choose either Admin or Editor access.';
    if (!in_array($status, ['active', 'inactive'], true)) $errors[] = 'Choose a valid account status.';
    if (!$id && strlen($password) < 8) $errors[] = 'A password of at least 8 characters is required for a new account.';
    if ($id && $password !== '' && strlen($password) < 8) $errors[] = 'A new password must be at least 8 characters.';

    if (!$errors) {
        $stmt = db()->prepare('SELECT id FROM admin_users WHERE email = :email AND id <> :id LIMIT 1');
        $stmt->execute([':email' => $email, ':id' => $id]);
        if ($stmt->fetch()) $errors[] = 'That email address is already in use.';
    }

    if (!$errors && $id && $user['status'] === 'active' && $user['role'] === 'admin'
        && ($role !== 'admin' || $status !== 'active')) {
        $stmt = db()->prepare("SELECT COUNT(*) FROM admin_users WHERE role = 'admin' AND status = 'active' AND id <> :id");
        $stmt->execute([':id' => $id]);
        if ((int)$stmt->fetchColumn() === 0) $errors[] = 'Keep at least one active administrator account.';
    }

    if (!$errors) {
        if ($id) {
            $sql = 'UPDATE admin_users SET name = :name, email = :email, role = :role, status = :status';
            $params = [':name' => $name, ':email' => $email, ':role' => $role, ':status' => $status, ':id' => $id];
            if ($password !== '') {
                $sql .= ', password = :password, must_change_password = 1';
                $params[':password'] = password_hash($password, PASSWORD_DEFAULT);
            }
            $sql .= ' WHERE id = :id';
            db()->prepare($sql)->execute($params);
            $targetLabel = $name . ' - ' . $email;
            if ($user['status'] === 'active' && $status === 'inactive') {
                log_activity($role . '_user.deactivate', 'Deactivated ' . $role . ' user ' . $targetLabel);
                flash('success', 'Admin access removed. The account and activity history were retained.');
            } elseif ($user['status'] === 'inactive' && $status === 'active') {
                log_activity($role . '_user.activate', 'Activated ' . $role . ' user ' . $targetLabel);
                flash('success', 'Admin user reactivated.');
            } elseif ($user['role'] !== $role) {
                log_activity('admin_user.role_change', 'Changed role for ' . $targetLabel . ' from ' . ucfirst($user['role']) . ' to ' . ucfirst($role));
                flash('success', 'Admin user updated.');
            } else {
                log_activity('admin_user.update', 'Updated admin user ' . $targetLabel);
                flash('success', 'Admin user updated.');
            }
        } else {
            db()->prepare(
                'INSERT INTO admin_users (name, email, password, role, status, must_change_password)
                 VALUES (:name, :email, :password, :role, :status, 1)'
            )->execute([
                ':name' => $name,
                ':email' => $email,
                ':password' => password_hash($password, PASSWORD_DEFAULT),
                ':role' => $role,
                ':status' => $status,
            ]);
            log_activity($role . '_user.create', 'Created ' . $role . ' user ' . $name . ' - ' . $email);
            flash('success', 'Admin user created.');
        }
        admin_redirect('users.php');
    }

    $user = array_merge($user, compact('name', 'email', 'role', 'status'));
}

include __DIR__ . '/includes/header.php';
?>

<div class="row">
  <div class="col-lg-8 col-xl-7">
    <div class="card">
      <div class="card-body p-4">
        <h2 class="h5 mb-3"><?= $id ? 'Update admin user' : 'Create admin user' ?></h2>
        <?php foreach ($errors as $error): ?><div class="alert alert-danger py-2"><?= e($error) ?></div><?php endforeach; ?>
        <form method="post" autocomplete="off">
          <?= csrf_field() ?>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label" for="name">Full name</label>
              <input class="form-control" id="name" name="name" maxlength="100" value="<?= e($user['name']) ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="email">Email</label>
              <input class="form-control" type="email" id="email" name="email" maxlength="150" value="<?= e($user['email']) ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="role">Role</label>
              <?php if ($isSelf): ?>
                <input class="form-control" value="<?= e(ucfirst($user['role'])) ?> (your own role cannot be changed here)" disabled>
              <?php else: ?>
                <select class="form-select" id="role" name="role">
                  <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                  <option value="editor" <?= $user['role'] === 'editor' ? 'selected' : '' ?>>Editor</option>
                </select>
              <?php endif; ?>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="status">Status</label>
              <?php if ($isSelf): ?>
                <input class="form-control" value="<?= e(ucfirst($user['status'])) ?> (your own status cannot be changed here)" disabled>
              <?php else: ?>
                <select class="form-select" id="status" name="status">
                  <option value="active" <?= $user['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                  <option value="inactive" <?= $user['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
              <?php endif; ?>
            </div>
            <div class="col-12">
              <label class="form-label" for="password"><?= $id ? 'Temporary password reset (optional)' : 'Temporary password' ?></label>
              <input class="form-control" type="password" id="password" name="password" minlength="8" autocomplete="new-password" <?= $id ? '' : 'required' ?>>
              <div class="form-text"><?= $id ? 'Leave blank to keep the current password. If entered, the user must change it at next sign-in.' : 'Share this password securely. The new admin must change it at first sign-in.' ?></div>
            </div>
          </div>
          <div class="mt-4 d-flex gap-2">
            <button class="btn btn-primary" type="submit"><i class="bi bi-check2 me-1"></i><?= $id ? 'Save changes' : 'Create user' ?></button>
            <a class="btn btn-light" href="<?= e(admin_url('users.php')) ?>">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
