<?php
require_once __DIR__ . '/includes/auth.php';
require_role('admin', 'editor');

$id = (int)($_GET['id'] ?? 0);

$stmt = db()->prepare('SELECT * FROM orders WHERE id = :id LIMIT 1');
$stmt->execute([':id' => $id]);
$order = $stmt->fetch();

if (!$order) {
    flash('warning', 'Order not found.');
    admin_redirect('orders.php');
}

$paymentProofUrl = null;
if ($order['payment_method'] === 'bank_transfer') {
    $safeOrderNumber = preg_replace('/[^A-Za-z0-9-]/', '', (string)$order['order_number']);
    foreach (['jpg', 'png', 'webp'] as $extension) {
        if (is_file(UPLOADS_PATH . '/payment-proofs/' . $safeOrderNumber . '.' . $extension)) {
            $paymentProofUrl = admin_url('payment_proof.php?id=' . (int)$order['id']);
            break;
        }
    }
}

$pageTitle = 'Order ' . $order['order_number'];

$allowedStatuses = ['pending', 'processing', 'shipped', 'ready_for_pickup', 'completed', 'cancelled'];
$statusLabels = [
    'ready_for_pickup' => 'Ready for pickup',
    'completed' => ($order['fulfillment_method'] ?? 'delivery') === 'pickup' ? 'Collected' : 'Completed',
];

// ----- Status update -----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_status') {
    require_csrf();
    $newStatus = $_POST['status'] ?? '';
    if (!in_array($newStatus, $allowedStatuses, true)) {
        flash('danger', 'Invalid status.');
    } elseif ($newStatus === $order['status']) {
        flash('info', 'Status unchanged.');
    } else {
        db()->prepare('UPDATE orders SET status = :s WHERE id = :id')
            ->execute([':s' => $newStatus, ':id' => $id]);
        log_activity(
            'order.status',
            'Order ' . $order['order_number']
            . ' status: ' . $order['status'] . ' → ' . $newStatus
        );
        flash('success', 'Order status updated to ' . ucfirst($newStatus) . '.');
    }
    admin_redirect('order_view.php?id=' . $id);
}

$stmt = db()->prepare('SELECT * FROM order_items WHERE order_id = :id');
$stmt->execute([':id' => $id]);
$items = $stmt->fetchAll();

$statusBadge = [
    'pending'    => 'text-bg-warning',
    'processing' => 'text-bg-info',
    'shipped'    => 'text-bg-primary',
    'ready_for_pickup' => 'text-bg-primary',
    'completed'  => 'text-bg-success',
    'cancelled'  => 'text-bg-secondary',
];

include __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between flex-wrap align-items-center mb-3 gap-2">
  <div>
    <a href="<?= e(admin_url('orders.php')) ?>" class="text-decoration-none">
      <i class="bi bi-arrow-left"></i> Back to orders
    </a>
    <h3 class="mt-1 mb-0">
      Order <?= e($order['order_number']) ?>
      <span class="badge <?= e($statusBadge[$order['status']] ?? 'text-bg-secondary') ?> align-middle ms-2">
        <?= e($statusLabels[$order['status']] ?? ucfirst($order['status'])) ?>
      </span>
    </h3>
    <div class="text-muted small">
      Placed <?= e(date('M j, Y \a\t g:i a', strtotime($order['created_at']))) ?>
    </div>
  </div>

  <form method="post" class="d-flex gap-2">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="update_status">
    <select name="status" class="form-select" style="width:auto">
      <?php foreach ($allowedStatuses as $s): ?>
        <option value="<?= $s ?>" <?= $order['status'] === $s ? 'selected' : '' ?>>
          <?= e($statusLabels[$s] ?? ucfirst($s)) ?>
        </option>
      <?php endforeach; ?>
    </select>
    <button class="btn btn-primary"><i class="bi bi-check2"></i> Update status</button>
  </form>
</div>
<?php if (($order['fulfillment_method'] ?? 'delivery') === 'pickup'): ?>
  <div class="alert alert-info py-2 small">For store pickup, choose <strong>Ready for pickup</strong> when the reservation is prepared, then <strong>Collected</strong> after the customer picks it up.</div>
<?php endif; ?>

<div class="row g-3 mb-3">
  <div class="col-md-4">
    <div class="card h-100">
      <div class="card-body">
        <h6 class="text-muted text-uppercase small">Customer</h6>
        <div class="fw-semibold"><?= e($order['customer_name']) ?></div>
        <div>
          <a href="mailto:<?= e($order['customer_email']) ?>"><?= e($order['customer_email']) ?></a>
        </div>
        <?php if (!empty($order['customer_phone'])): ?>
          <div>
            <a href="tel:<?= e($order['customer_phone']) ?>"><?= e($order['customer_phone']) ?></a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card h-100">
      <div class="card-body">
        <?php if (($order['fulfillment_method'] ?? 'delivery') === 'pickup'): ?>
          <h6 class="text-muted text-uppercase small">Store pickup</h6>
          <div><?= nl2br(e(setting('store_address', '') ?: 'Pickup address has not been configured.')) ?></div>
          <div class="small text-muted mt-2">Reserved items should be collected at the shop.</div>
        <?php else: ?>
          <h6 class="text-muted text-uppercase small">Delivery</h6>
          <div><?= nl2br(e($order['shipping_address'])) ?></div>
          <div><?= e(trim(($order['shipping_city'] ?? '') . ' ' . ($order['shipping_zip'] ?? ''))) ?></div>
          <?php if (!empty($order['shipping_country'])): ?>
            <div><?= e($order['shipping_country']) ?></div>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card h-100">
      <div class="card-body">
        <h6 class="text-muted text-uppercase small">Payment</h6>
        <div class="fw-semibold">
          <?= e($order['payment_method'] === 'bank_transfer' ? 'Bank Transfer' : (($order['fulfillment_method'] ?? 'delivery') === 'pickup' ? 'Pay at pickup' : 'Cash on Delivery')) ?>
        </div>
        <div class="text-muted small">
          Last updated <?= e(date('M j, Y g:i a', strtotime($order['updated_at']))) ?>
        </div>
        <?php if ($paymentProofUrl): ?>
          <a href="<?= e($paymentProofUrl) ?>" target="_blank" rel="noopener" aria-label="Open full payment screenshot">
            <img src="<?= e($paymentProofUrl) ?>" class="img-fluid border rounded mt-3 d-block"
                 alt="Uploaded bank transfer payment screenshot" style="max-width:100%; max-height:360px; object-fit:contain;">
          </a>
          <a class="btn btn-sm btn-outline-primary mt-3" href="<?= e($paymentProofUrl) ?>" target="_blank" rel="noopener">
            <i class="bi bi-image"></i> Open full screenshot
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<div class="card mb-3">
  <div class="card-body">
    <h5 class="card-title">Items</h5>
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <th style="width:60px"></th>
            <th>Product</th>
            <th>SKU</th>
            <th class="text-end">Unit price</th>
            <th class="text-center">Qty</th>
            <th class="text-end">Subtotal</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($items as $i): ?>
            <tr>
              <td>
                <img src="<?= e(product_image_url($i['product_image'])) ?>"
                     class="rounded" width="44" height="44" style="object-fit:cover" alt="">
              </td>
              <td>
                <?php if ($i['product_id']): ?>
                  <a href="<?= e(admin_url('product_form.php?id=' . (int)$i['product_id'])) ?>"
                     class="fw-semibold text-reset"><?= e($i['product_name']) ?></a>
                  <?php if (!empty($i['product_color'])): ?><div class="small text-muted">Color: <?= e($i['product_color']) ?></div><?php endif; ?>
                <?php else: ?>
                  <span class="fw-semibold"><?= e($i['product_name']) ?></span>
                  <?php if (!empty($i['product_color'])): ?><div class="small text-muted">Color: <?= e($i['product_color']) ?></div><?php endif; ?>
                  <span class="badge text-bg-secondary ms-1">deleted</span>
                <?php endif; ?>
              </td>
              <td><code><?= e($i['product_sku'] ?: '—') ?></code></td>
              <td class="text-end"><?= e(price((float)$i['unit_price'])) ?></td>
              <td class="text-center"><?= (int)$i['quantity'] ?></td>
              <td class="text-end fw-semibold"><?= e(price((float)$i['line_total'])) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr>
            <td colspan="5" class="text-end">Subtotal</td>
            <td class="text-end"><?= e(price((float)$order['subtotal'])) ?></td>
          </tr>
          <tr>
            <td colspan="5" class="text-end">Shipping</td>
            <td class="text-end">
              <?= ((float)$order['shipping_fee'] > 0)
                    ? e(price((float)$order['shipping_fee']))
                    : '<span class="text-success">Free</span>' ?>
            </td>
          </tr>
          <tr class="fs-5 fw-bold">
            <td colspan="5" class="text-end">Total</td>
            <td class="text-end"><?= e(price((float)$order['total'])) ?></td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</div>

<?php if (!empty($order['notes'])): ?>
  <div class="card mb-3">
    <div class="card-body">
      <h6 class="text-muted text-uppercase small">Customer note</h6>
      <p class="mb-0"><?= nl2br(e($order['notes'])) ?></p>
    </div>
  </div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
