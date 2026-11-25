<?php
require_once __DIR__ . '/includes/auth.php';
require_role('admin');

$pageTitle = 'Admin users';
$me = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $id = (int)($_POST['id'] ?? 0);
    $newStatus = (string)($_POST['status'] ?? '');
    $stmt = db()->prepare('SELECT id, name, email, role, status FROM admin_users WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $id]);
    $target = $stmt->fetch();

    if (!$target) {
        flash('warning', 'Admin user not found.');
    } elseif (!in_array($newStatus, ['active', 'inactive'], true)) {
        flash('danger', 'Invalid account status.');
    } elseif ($id === (int)$me['id'] && $newStatus === 'inactive') {
        flash('warning', 'You cannot deactivate your own account.');
    } elseif ($target['status'] === 'active' && $newStatus === 'inactive' && $target['role'] === 'admin') {
        $activeAdmins = (int)db()->query("SELECT COUNT(*) FROM admin_users WHERE role = 'admin' AND status = 'active'")->fetchColumn();
        if ($activeAdmins <= 1) {
            flash('warning', 'Keep at least one active administrator account.');
            admin_redirect('users.php');
        }
        db()->prepare('UPDATE admin_users SET status = :status WHERE id = :id')
            ->execute([':status' => $newStatus, ':id' => $id]);
        log_activity($target['role'] . '_user.deactivate', 'Deactivated ' . $target['role'] . ' user ' . $target['name'] . ' - ' . $target['email']);
        flash('success', 'Admin access removed. The account and activity history were retained.');
    } else {
        db()->prepare('UPDATE admin_users SET status = :status WHERE id = :id')
            ->execute([':status' => $newStatus, ':id' => $id]);
        $action = $target['role'] . '_user.' . ($newStatus === 'active' ? 'activate' : 'deactivate');
        $statusVerb = $newStatus === 'active' ? 'Activated' : 'Deactivated';
        log_activity($action, $statusVerb . ' ' . $target['role'] . ' user ' . $target['name'] . ' - ' . $target['email']);
        flash('success', $newStatus === 'active' ? 'Admin user reactivated.' : 'Admin access removed.');
    }
    admin_redirect('users.php');
}

$users = db()->query(
    "SELECT id, name, email, role, status, must_change_password, created_at
     FROM admin_users
     WHERE role IN ('admin', 'editor')
     ORDER BY (status = 'active') DESC, name ASC"
)->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<div class="card">
  <div class="card-body">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
      <div>
        <h2 class="h5 mb-1">Admin users</h2>
        <p class="text-muted mb-0">Create accounts, update access, or deactivate administrators and editors.</p>
      </div>
      <a class="btn btn-primary" href="<?= e(admin_url('user_form.php')) ?>"><i class="bi bi-person-plus me-1"></i>Add admin user</a>
    </div>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Password setup</th><th>Created</th><th class="text-end">Actions</th></tr></thead>
        <tbody>
          <?php foreach ($users as $user): ?>
            <tr>
              <td class="fw-semibold"><?= e($user['name']) ?><?= (int)$user['id'] === (int)$me['id'] ? ' <span class="text-muted small">(you)</span>' : '' ?></td>
              <td><?= e($user['email']) ?></td>
              <td><span class="badge role-<?= e($user['role']) ?>"><?= e(ucfirst($user['role'])) ?></span></td>
              <td><span class="badge status-<?= e($user['status']) ?>"><?= e(ucfirst($user['status'])) ?></span></td>
              <td><?php if ($user['must_change_password']): ?><span class="badge text-bg-warning">Change required</span><?php else: ?><span class="text-muted small">Set</span><?php endif; ?></td>
              <td class="text-muted small"><?= e(format_date($user['created_at'])) ?></td>
              <td class="text-end text-nowrap">
                <a class="btn btn-sm btn-light" href="<?= e(admin_url('user_form.php?id=' . (int)$user['id'])) ?>" title="Edit user" aria-label="Edit <?= e($user['name']) ?>"><i class="bi bi-pencil-square"></i></a>
                <?php if ((int)$user['id'] !== (int)$me['id']): ?>
                  <form method="post" class="d-inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= (int)$user['id'] ?>">
                    <input type="hidden" name="status" value="<?= $user['status'] === 'active' ? 'inactive' : 'active' ?>">
                    <button type="submit" data-confirm-title="<?= $user['status'] === 'active' ? 'Remove admin access?' : 'Restore admin access?' ?>"
                            data-confirm="<?= $user['status'] === 'active' ? 'This admin user will no longer be able to sign in. Their account and activity history will be retained.' : 'This admin user will be able to sign in again.' ?>"
                            data-confirm-button="<?= $user['status'] === 'active' ? 'Deactivate account' : 'Reactivate account' ?>"
                            class="btn btn-sm <?= $user['status'] === 'active' ? 'btn-outline-danger' : 'btn-outline-success' ?>" title="<?= $user['status'] === 'active' ? 'Deactivate' : 'Reactivate' ?>" aria-label="<?= $user['status'] === 'active' ? 'Deactivate' : 'Reactivate' ?> <?= e($user['name']) ?>">
                      <i class="bi <?= $user['status'] === 'active' ? 'bi-person-x' : 'bi-person-check' ?>"></i>
                    </button>
                  </form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$users): ?><tr><td colspan="7" class="text-center text-muted py-4">No admin users yet.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
    <p class="small text-muted mt-3 mb-0"><i class="bi bi-info-circle me-1"></i>Deactivated accounts can no longer sign in. Their records and activity history are retained.</p>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
