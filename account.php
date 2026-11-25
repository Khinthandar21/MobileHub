<?php
require_once __DIR__ . '/includes/shop_bootstrap.php';

$sessionCustomer = current_customer();
if (!$sessionCustomer) {
    flash('info', 'Sign in to view your customer account.');
    redirect('customer_login.php');
}

customer_claim_guest_orders($sessionCustomer);

$stmt = db()->prepare(
    'SELECT * FROM customer_users WHERE id = :id LIMIT 1'
);
$stmt->execute([':id' => (int)$sessionCustomer['id']]);
$customer = $stmt->fetch();
if (!$customer) {
    customer_sign_out();
    flash('warning', 'Your account could not be found. Please sign in again.');
    redirect('customer_login.php');
}

$pageTitle = 'My Account';
$fieldErrors = [];
$formErrors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_profile') {
    require_csrf();
    $profile = [
        'name' => trim((string)($_POST['name'] ?? '')),
        'email' => strtolower(trim((string)($_POST['email'] ?? ''))),
        'phone' => trim((string)($_POST['phone'] ?? '')),
        'shipping_address' => trim((string)($_POST['shipping_address'] ?? '')),
        'shipping_city' => trim((string)($_POST['shipping_city'] ?? '')),
        'shipping_zip' => trim((string)($_POST['shipping_zip'] ?? '')),
        'shipping_country' => trim((string)($_POST['shipping_country'] ?? '')),
    ];
    $currentPassword = (string)($_POST['current_password'] ?? '');
    $newPassword = (string)($_POST['new_password'] ?? '');
    $confirmPassword = (string)($_POST['confirm_password'] ?? '');
    $avatarUpload = $_FILES['avatar'] ?? null;
    $avatarExtension = null;
    $removeAvatar = ($_POST['remove_avatar'] ?? '') === '1';

    if ($profile['name'] === '' || mb_strlen($profile['name']) > 150) {
        $fieldErrors['name'][] = 'Enter your name (up to 150 characters).';
    }
    if (!filter_var($profile['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($profile['email']) > 150) {
        $fieldErrors['email'][] = 'Enter a valid email address.';
    }
    if ($newPassword !== '' && $newPassword !== $confirmPassword) {
        $fieldErrors['confirm_password'][] = 'The new passwords do not match.';
    }
    if ($newPassword !== '' && strlen($newPassword) < 8) {
        $fieldErrors['new_password'][] = 'The new password must be at least 8 characters.';
    }
    if ($newPassword !== '' && !password_verify($currentPassword, $customer['password_hash'])) {
        $fieldErrors['current_password'][] = 'Enter your current password to set a new password.';
    }
    if ($avatarUpload && ($avatarUpload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        if (($avatarUpload['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            $fieldErrors['avatar'][] = 'Profile photo upload failed: ' . upload_error_message((int)$avatarUpload['error']);
        } elseif ((int)$avatarUpload['size'] > 2 * 1024 * 1024) {
            $fieldErrors['avatar'][] = 'Profile photo must be 2 MB or smaller.';
        } else {
            $avatarMime = (new finfo(FILEINFO_MIME_TYPE))->file($avatarUpload['tmp_name']);
            $avatarTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
            if (!isset($avatarTypes[$avatarMime]) || !is_uploaded_file($avatarUpload['tmp_name'])
                || !@getimagesize($avatarUpload['tmp_name'])) {
                $fieldErrors['avatar'][] = 'Choose a valid JPG, PNG, or WEBP profile photo.';
            } else {
                $avatarExtension = $avatarTypes[$avatarMime];
            }
        }
    }

    if (!$fieldErrors && !$formErrors) {
        $newAvatarPath = null;
        $oldAvatarPath = !empty($customer['avatar']) ? UPLOADS_PATH . '/' . $customer['avatar'] : null;
        try {
            $pdo = db();
            $pdo->beginTransaction();
            $stmt = $pdo->prepare(
                'UPDATE customer_users
                 SET name = :name, email = :email, phone = :phone,
                     shipping_address = :address, shipping_city = :city,
                     shipping_zip = :zip, shipping_country = :country
                 WHERE id = :id'
            );
            $stmt->execute([
                ':name' => $profile['name'],
                ':email' => $profile['email'],
                ':phone' => $profile['phone'] ?: null,
                ':address' => $profile['shipping_address'] ?: null,
                ':city' => $profile['shipping_city'] ?: null,
                ':zip' => $profile['shipping_zip'] ?: null,
                ':country' => $profile['shipping_country'] ?: null,
                ':id' => (int)$customer['id'],
            ]);
            if ($avatarExtension) {
                $avatarDir = UPLOADS_PATH . '/customer-avatars';
                if (!is_dir($avatarDir) && !mkdir($avatarDir, 0775, true) && !is_dir($avatarDir)) {
                    throw new RuntimeException('Could not create profile photo storage.');
                }
                $avatarName = 'customer_' . (int)$customer['id'] . '_' . bin2hex(random_bytes(8)) . '.' . $avatarExtension;
                $newAvatarPath = $avatarDir . '/' . $avatarName;
                if (!move_uploaded_file($avatarUpload['tmp_name'], $newAvatarPath)) {
                    $newAvatarPath = null;
                    throw new RuntimeException('Could not save the profile photo.');
                }
                $pdo->prepare(
                    'UPDATE customer_users SET avatar = :image WHERE id = :id'
                )->execute([':id' => (int)$customer['id'], ':image' => 'customer-avatars/' . $avatarName]);
            } elseif ($removeAvatar) {
                $pdo->prepare('UPDATE customer_users SET avatar = NULL WHERE id = :id')
                    ->execute([':id' => (int)$customer['id']]);
            }
            if ($newPassword !== '') {
                $pdo->prepare('UPDATE customer_users SET password_hash = :password WHERE id = :id')
                    ->execute([':password' => password_hash($newPassword, PASSWORD_DEFAULT), ':id' => (int)$customer['id']]);
            }
            // Attach any matching guest orders; existing orders retain their customer_id.
            $pdo->prepare(
                'UPDATE orders SET customer_id = :customer_id
                 WHERE customer_id IS NULL AND LOWER(customer_email) = :email'
            )->execute([':customer_id' => (int)$customer['id'], ':email' => $profile['email']]);
            $pdo->commit();

            if (($avatarExtension || $removeAvatar) && $oldAvatarPath && is_file($oldAvatarPath)) {
                @unlink($oldAvatarPath);
            }
            $stmt = db()->prepare(
                'SELECT * FROM customer_users WHERE id = :id LIMIT 1'
            );
            $stmt->execute([':id' => (int)$customer['id']]);
            $customer = $stmt->fetch();
            customer_sign_in($customer);
            if ($avatarExtension || $removeAvatar) {
                $_SESSION['_customer_success_toast'] = $newPassword !== ''
                    ? 'Profile photo and password updated successfully.'
                    : 'Profile photo updated successfully.';
            } elseif ($newPassword !== '') {
                $_SESSION['_customer_success_toast'] = 'Password updated successfully.';
            } else {
                $_SESSION['_customer_success_toast'] = 'Profile updated successfully.';
            }
            redirect('account.php#profile-details');
        } catch (PDOException $e) {
            if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
            if (!empty($newAvatarPath) && is_file($newAvatarPath)) @unlink($newAvatarPath);
            if ((string)$e->getCode() === '23000') {
                $fieldErrors['email'][] = 'That email address is already used by another account.';
            } else {
                $formErrors[] = 'Could not update your profile. Please try again.';
            }
        } catch (Throwable $e) {
            if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
            if (!empty($newAvatarPath) && is_file($newAvatarPath)) @unlink($newAvatarPath);
            $formErrors[] = 'Could not update your profile. Please try again.';
        }
    }
    $customer = array_merge($customer, $profile);
}

$stmt = db()->prepare(
    'SELECT o.id, o.order_number, o.total, o.status, o.payment_method, o.fulfillment_method, o.created_at,
            (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) AS item_count
     FROM orders o
     WHERE o.customer_id = :customer_id
     ORDER BY o.created_at DESC, o.id DESC'
);
$stmt->execute([':customer_id' => (int)$customer['id']]);
$orders = $stmt->fetchAll();
$customerSuccessToast = (string)($_SESSION['_customer_success_toast'] ?? '');
unset($_SESSION['_customer_success_toast']);

include __DIR__ . '/includes/shop_header.php';
?>
<?php if ($customerSuccessToast !== ''): ?>
  <div class="customer-success-toast" id="customerSuccessToast" role="status" aria-live="polite">
    <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
    <span><?= e($customerSuccessToast) ?></span>
    <button type="button" class="profile-toast-close" aria-label="Dismiss message"><i class="bi bi-x-lg"></i></button>
  </div>
<?php endif; ?>
<section class="section customer-account-page">
  <div class="container">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
      <div><h2 class="mb-1">My account</h2><p class="text-muted mb-0">Update your details and review your orders.</p></div>
      <form method="post" action="<?= e(shop_url('customer_logout.php')) ?>">
        <?= csrf_field() ?><button class="btn btn-outline-secondary"><i class="bi bi-box-arrow-right"></i> Log out</button>
      </form>
    </div>

    <div class="row g-4 align-items-start">
      <div class="col-lg-5">
        <form method="post" enctype="multipart/form-data" class="card" id="profile-details" data-profile-errors="<?= ($fieldErrors || $formErrors) ? '1' : '0' ?>" data-error-target="<?= (!empty($fieldErrors['current_password']) || !empty($fieldErrors['new_password']) || !empty($fieldErrors['confirm_password'])) ? 'password-section' : (($fieldErrors || $formErrors) ? 'profile-details' : '') ?>">
          <div class="card-body p-4">
            <h5 class="card-title mb-3">Profile details</h5>
            <?= csrf_field() ?><input type="hidden" name="action" value="update_profile">
            <div class="profile-photo-panel mb-4">
              <div class="profile-photo-portrait">
                <img id="accountAvatarPreview"
                     src="<?= !empty($customer['avatar']) ? e(avatar_url($customer['avatar'], $customer['name'])) : '' ?>"
                     data-original-src="<?= !empty($customer['avatar']) ? e(avatar_url($customer['avatar'], $customer['name'])) : '' ?>"
                     alt="Your profile photo"
                     class="customer-profile-photo customer-photo-preview border <?= empty($customer['avatar']) ? 'd-none' : '' ?>"
                     width="96" height="96" data-photo-name="<?= e($customer['name']) ?>">
                <span id="accountAvatarPlaceholder" class="customer-avatar-placeholder <?= !empty($customer['avatar']) ? 'd-none' : '' ?>" aria-hidden="true"><i class="bi bi-person"></i></span>

              </div>
              <div class="profile-photo-content">
                <h6 class="mb-1">Profile photo</h6>
                <p class="small text-muted mb-3">Choose a photo to personalize your account.</p>
                <div class="profile-photo-actions">
                  <label for="avatar" class="btn btn-primary btn-sm"><i class="bi bi-upload me-1"></i>Upload photo</label>
                  <button id="deleteProfilePhoto" class="btn-profile-delete" type="button"
                          data-has-original="<?= !empty($customer['avatar']) ? '1' : '0' ?>"
                          <?= empty($customer['avatar']) ? 'disabled aria-disabled="true"' : '' ?>>
                    <span class="profile-delete-icon"><i class="bi bi-trash3"></i></span>
                    <span>Remove photo</span>
                  </button>
                </div>
                <input class="visually-hidden" type="file" id="avatar" name="avatar" accept="image/jpeg,image/png,image/webp">
                <input type="hidden" name="remove_avatar" id="removeAvatarFlag" value="0">
                <div id="profilePhotoStatus" class="profile-photo-status mt-2 <?= !empty($fieldErrors['avatar']) ? 'text-danger' : '' ?>" aria-live="polite">
                  <?php if (!empty($fieldErrors['avatar'])): ?><?= e(implode(' ', $fieldErrors['avatar'])) ?><?php else: ?>JPG, PNG, or WEBP · Up to 2 MB. Save your profile to apply photo changes.<?php endif; ?>
                </div>
              </div>
            </div>
            <div class="mb-3"><label class="form-label" for="name">Full name</label><input class="form-control <?= !empty($fieldErrors['name']) ? 'is-invalid' : '' ?>" id="name" name="name" maxlength="150" value="<?= e($customer['name']) ?>" autocomplete="name" required><?php foreach ($fieldErrors['name'] ?? [] as $error): ?><div class="invalid-feedback d-block"><?= e($error) ?></div><?php endforeach; ?></div>
            <div class="mb-3"><label class="form-label" for="email">Email</label><input class="form-control <?= !empty($fieldErrors['email']) ? 'is-invalid' : '' ?>" type="email" id="email" name="email" maxlength="150" value="<?= e($customer['email']) ?>" autocomplete="email" required><?php foreach ($fieldErrors['email'] ?? [] as $error): ?><div class="invalid-feedback d-block"><?= e($error) ?></div><?php endforeach; ?></div>
            <div class="mb-3"><label class="form-label" for="phone">Phone</label><input class="form-control" type="tel" id="phone" name="phone" maxlength="40" value="<?= e($customer['phone'] ?? '') ?>" autocomplete="tel"></div>
            <div class="mb-3"><label class="form-label" for="shipping_address">Shipping address</label><textarea class="form-control" id="shipping_address" name="shipping_address" rows="2" autocomplete="street-address"><?= e($customer['shipping_address'] ?? '') ?></textarea></div>
            <div class="row g-3 mb-3">
              <div class="col-6"><label class="form-label" for="shipping_city">City</label><input class="form-control" id="shipping_city" name="shipping_city" value="<?= e($customer['shipping_city'] ?? '') ?>" autocomplete="address-level2"></div>
              <div class="col-6"><label class="form-label" for="shipping_zip">ZIP / Postal</label><input class="form-control" id="shipping_zip" name="shipping_zip" value="<?= e($customer['shipping_zip'] ?? '') ?>" autocomplete="postal-code"></div>
            </div>
            <div class="mb-4"><label class="form-label" for="shipping_country">Country</label><input class="form-control" id="shipping_country" name="shipping_country" value="<?= e($customer['shipping_country'] ?? '') ?>" autocomplete="country-name"></div>
            <div id="password-section">
            <hr><h6>Change password <span class="text-muted fw-normal">(optional)</span></h6>
            <div class="mb-3"><label class="form-label" for="current_password">Current password</label><input class="form-control <?= !empty($fieldErrors['current_password']) ? 'is-invalid' : '' ?>" type="password" id="current_password" name="current_password" autocomplete="current-password"><?php foreach ($fieldErrors['current_password'] ?? [] as $error): ?><div class="invalid-feedback d-block"><?= e($error) ?></div><?php endforeach; ?></div>
            <div class="mb-3"><label class="form-label" for="new_password">New password</label><input class="form-control <?= !empty($fieldErrors['new_password']) ? 'is-invalid' : '' ?>" type="password" id="new_password" name="new_password" minlength="8" autocomplete="new-password"><?php foreach ($fieldErrors['new_password'] ?? [] as $error): ?><div class="invalid-feedback d-block"><?= e($error) ?></div><?php endforeach; ?></div>
            <div class="mb-4"><label class="form-label" for="confirm_password">Confirm new password</label><input class="form-control <?= !empty($fieldErrors['confirm_password']) ? 'is-invalid' : '' ?>" type="password" id="confirm_password" name="confirm_password" minlength="8" autocomplete="new-password" aria-describedby="password-match-feedback"><?php foreach ($fieldErrors['confirm_password'] ?? [] as $error): ?><div class="invalid-feedback d-block"><?= e($error) ?></div><?php endforeach; ?><div id="password-match-feedback" class="form-text" aria-live="polite"></div></div>
            </div>
            <?php foreach ($formErrors as $error): ?><div class="small text-danger mb-3" role="alert"><?= e($error) ?></div><?php endforeach; ?>
            <button class="btn btn-primary w-100">Save profile</button>
          </div>
        </form>
      </div>

      <div class="col-lg-7">
        <div class="card">
          <div class="card-body p-4">
            <h5 class="card-title mb-3">Order history <span class="badge text-bg-light"><?= count($orders) ?></span></h5>
            <?php if ($orders): ?>
              <div class="table-responsive"><table class="table align-middle mb-0">
                <thead><tr><th>Order</th><th>Placed</th><th>Status</th><th class="text-end">Total</th></tr></thead>
                <tbody><?php foreach ($orders as $order): ?>
                  <?php $badge = ['pending' => 'text-bg-warning', 'processing' => 'text-bg-info', 'shipped' => 'text-bg-primary', 'completed' => 'text-bg-success', 'cancelled' => 'text-bg-secondary'][$order['status']] ?? 'text-bg-secondary'; ?>
                  <tr>
                    <td><a class="fw-semibold" href="<?= e(shop_url('customer_order.php?id=' . (int)$order['id'])) ?>" title="View order details"><?= e($order['order_number']) ?></a><div class="small text-muted"><?= (int)$order['item_count'] ?> item<?= (int)$order['item_count'] === 1 ? '' : 's' ?> · <?= e(($order['fulfillment_method'] ?? 'delivery') === 'pickup' ? 'Store pickup' : 'Delivery') ?> · <?= e($order['payment_method'] === 'cod' ? (($order['fulfillment_method'] ?? 'delivery') === 'pickup' ? 'Pay at pickup' : 'Cash on Delivery') : 'Bank Transfer') ?></div></td>
                    <td><?= e(date('M j, Y', strtotime($order['created_at']))) ?></td>
                    <td><span class="badge <?= e($badge) ?>"><?= e(ucfirst($order['status'])) ?></span></td>
                    <td class="text-end fw-semibold"><?= e(price((float)$order['total'])) ?></td>
                  </tr>
                <?php endforeach; ?></tbody>
              </table></div>
            <?php else: ?>
              <div class="text-center text-muted py-5"><i class="bi bi-bag fs-2 d-block mb-2"></i>No orders linked to this account yet.</div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<?php include __DIR__ . '/includes/shop_footer.php'; ?>
