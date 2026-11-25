<?php
require_once __DIR__ . '/includes/shop_bootstrap.php';

$pageTitle = 'Track your order';

if (!current_customer()) {
    flash('warning', 'Please sign in before tracking your order.');
    redirect('customer_login.php?return_to=track.php');
}
$signedInCustomer = current_customer();

// Pre-fill from query string (e.g. when linking from confirmation page).
$orderNumber = trim((string)($_GET['order'] ?? $_POST['order'] ?? ''));
$email       = trim((string)($_GET['email'] ?? $_POST['email'] ?? ''));

$order  = null;
$items  = [];
$errors = [];
$searched = false;

if ($orderNumber !== '' || $email !== '') {
    $searched = true;

    if ($orderNumber === '') {
        $errors[] = 'Please enter your order number.';
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter the email you used at checkout.';
    }

    if (!$errors) {
        $stmt = db()->prepare(
            "SELECT * FROM orders
             WHERE order_number = :n AND customer_email = :e AND customer_id = :customer_id
             LIMIT 1"
        );
        $stmt->execute([
            ':n' => $orderNumber,
            ':e' => $email,
            ':customer_id' => (int)$signedInCustomer['id'],
        ]);
        $order = $stmt->fetch();

        if (!$order) {
            $errors[] = "We couldn't find an order matching that number and email. "
                      . "Please double-check both and try again.";
        } else {
            $stmt = db()->prepare("SELECT * FROM order_items WHERE order_id = :id");
            $stmt->execute([':id' => $order['id']]);
            $items = $stmt->fetchAll();
        }
    }
}

/**
 * Status timeline (in order). 'cancelled' is shown separately as a red endpoint.
 */
$isPickupOrder = $order && ($order['fulfillment_method'] ?? 'delivery') === 'pickup';
$timeline = [
    'pending'    => ['label' => 'Order placed', 'icon' => 'bi-receipt'],
    'processing' => ['label' => $isPickupOrder ? 'Preparing reservation' : 'Processing', 'icon' => 'bi-box-seam'],
    'shipped'    => ['label' => $isPickupOrder ? 'Ready for pickup' : 'Shipped', 'icon' => $isPickupOrder ? 'bi-shop' : 'bi-truck'],
    'completed'  => ['label' => $isPickupOrder ? 'Collected' : 'Delivered', 'icon' => 'bi-check2-circle'],
];

$statusBadge = [
    'pending'    => 'bg-warning text-dark',
    'processing' => 'bg-info text-white',
    'shipped'    => 'bg-primary text-white',
    'completed'  => 'bg-success text-white',
    'cancelled'  => 'bg-secondary text-white',
];

include __DIR__ . '/includes/shop_header.php';
?>

<section class="section track-order-page">
  <div class="container" style="max-width: 900px;">

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= e(shop_url()) ?>">Home</a></li>
        <li class="breadcrumb-item active">Track order</li>
      </ol>
    </nav>

    <div class="section-header text-center">
      <h2><i class="bi bi-geo-alt"></i> Track your order</h2>
      <p>Enter your order number and email to see the latest status.</p>
    </div>

    <div class="card mb-4">
      <div class="card-body">
        <form method="get" class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Order number</label>
            <input type="text" name="order" class="form-control form-control-lg"
                   value="<?= e($orderNumber) ?>"
                   placeholder="e.g. ORD-202605-0001" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Email used at checkout</label>
            <input type="email" name="email" class="form-control form-control-lg"
                   value="<?= e($email) ?>"
                   placeholder="you@example.com" required>
          </div>
          <div class="col-12 d-grid">
            <button class="btn btn-primary btn-lg">
              <i class="bi bi-search"></i> Track order
            </button>
          </div>
        </form>
      </div>
    </div>

    <?php foreach ($errors as $err): ?>
      <div class="alert alert-danger"><?= e($err) ?></div>
    <?php endforeach; ?>

    <?php if ($order): ?>
      <?php
        $isCancelled = $order['status'] === 'cancelled';
        // Map current status to step index (0..3) for the visual timeline.
        $stepIndex = array_search($order['status'], array_keys($timeline), true);
        if ($stepIndex === false) $stepIndex = -1;
      ?>

      <div class="card mb-4">
        <div class="card-body">
          <div class="mb-3">
            <div class="text-muted small">Order number</div>
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
              <div class="h4 mb-0"><?= e($order['order_number']) ?></div>
              <span class="badge <?= e($statusBadge[$order['status']] ?? 'bg-secondary') ?> fs-6 px-3 py-2">
                <?= e(ucfirst($order['status'])) ?>
              </span>
            </div>
          </div>

          <hr>

          <?php if ($isCancelled): ?>
            <div class="alert alert-secondary mb-0">
              <i class="bi bi-x-circle"></i>
              This order has been <strong>cancelled</strong>. If this is unexpected,
              please contact us with your order number.
            </div>
          <?php else: ?>
            <div class="status-timeline">
              <?php $i = 0; foreach ($timeline as $key => $step):
                $isDone    = $i <= $stepIndex;
                $isCurrent = $i === $stepIndex;
              ?>
                <div class="timeline-step <?= $isDone ? 'is-done' : '' ?> <?= $isCurrent ? 'is-current' : '' ?>">
                  <div class="timeline-bullet"><i class="bi <?= e($step['icon']) ?>"></i></div>
                  <div class="timeline-label"><?= e($step['label']) ?></div>
                </div>
                <?php if ($i < count($timeline) - 1): ?>
                  <div class="timeline-line <?= $i < $stepIndex ? 'is-done' : '' ?>"></div>
                <?php endif; ?>
              <?php $i++; endforeach; ?>
            </div>

            <div class="text-muted small mt-3">
              <?php
                $deliveryMessages = [
                  'pending'    => 'We have received your order and will start processing it soon.',
                  'processing' => "We're preparing your order for shipment.",
                  'shipped'    => 'Your order is on its way!',
                  'completed'  => 'Your order has been delivered. Thank you for shopping with us!',
                ];
                $pickupMessages = [
                  'pending'    => 'Your reservation is confirmed. We will prepare your items for pickup.',
                  'processing' => 'We are preparing your reserved items.',
                  'shipped'    => 'Your order is ready for pickup at the shop.',
                  'completed'  => 'Your order has been collected. Thank you for shopping with us!',
                ];
                $msg = ($isPickupOrder ? $pickupMessages : $deliveryMessages)[$order['status']] ?? '';
              ?>
              <i class="bi bi-info-circle"></i> <?= e($msg) ?>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <div class="row g-3 mb-4">
        <div class="col-md-6">
          <div class="card h-100">
            <div class="card-body">
              <?php if ($isPickupOrder): ?>
                <h6 class="track-info-title"><i class="bi bi-shop"></i> Store pickup</h6>
                <div class="track-info-list">
                  <div class="track-info-row"><span>Name</span><strong><?= e($order['customer_name']) ?></strong></div>
                  <div class="track-info-row"><span>Pickup location</span><div><?= nl2br(e(setting('store_address', '') ?: 'Contact the store to confirm the pickup location.')) ?></div></div>
                </div>
                <div class="small text-muted mt-2">Bring your order number when collecting.</div>
              <?php else: ?>
                <h6 class="track-info-title"><i class="bi bi-geo-alt"></i> Ship to</h6>
                <div class="track-info-list">
                  <div class="track-info-row"><span>Name</span><strong><?= e($order['customer_name']) ?></strong></div>
                  <?php if (trim((string)$order['shipping_address']) !== ''): ?>
                    <div class="track-info-row"><span>Address</span><div><?= nl2br(e($order['shipping_address'])) ?></div></div>
                  <?php endif; ?>
                  <?php $cityZip = trim(($order['shipping_city'] ?? '') . ' ' . ($order['shipping_zip'] ?? '')); ?>
                  <?php if ($cityZip !== ''): ?><div class="track-info-row"><span>City / ZIP</span><div><?= e($cityZip) ?></div></div><?php endif; ?>
                  <?php if (trim((string)$order['shipping_country']) !== ''): ?><div class="track-info-row"><span>Country</span><div><?= e($order['shipping_country']) ?></div></div><?php endif; ?>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card h-100">
            <div class="card-body">
              <h6 class="track-info-title"><i class="bi bi-receipt"></i> Order details</h6>
              <div class="track-info-list">
                <div class="track-info-row"><span>Ordered</span><div><?= e(date('M j, Y g:i a', strtotime($order['created_at']))) ?></div></div>
                <div class="track-info-row"><span>Last updated</span><div><?= e(date('M j, Y g:i a', strtotime($order['updated_at']))) ?></div></div>
                <div class="track-info-row"><span>Payment</span><div><?= e($order['payment_method'] === 'bank_transfer' ? 'Bank Transfer' : ($isPickupOrder ? 'Pay at pickup' : 'Cash on Delivery')) ?></div></div>
                <div class="track-info-row"><span><?= $isPickupOrder ? 'Store pickup' : 'Delivery fee' ?></span><div><?= (float)$order['shipping_fee'] > 0 ? e(price((float)$order['shipping_fee'])) : 'Free' ?></div></div>
                <div class="track-info-row"><span>Total</span><strong><?= e(price((float)$order['total'])) ?></strong></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-body p-0">
          <div class="table-responsive">
          <table class="table mb-0">
            <thead>
              <tr>
                <th>Product</th>
                <th class="text-center">Qty</th>
                <th class="text-end">Price</th>
                <th class="text-end">Subtotal</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($items as $i): ?>
                <tr>
                  <td>
                    <?= e($i['product_name']) ?>
                    <?php if (!empty($i['product_sku'])): ?>
                      <div class="small text-muted">SKU: <?= e($i['product_sku']) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($i['product_color'])): ?>
                      <div class="small text-muted">Color: <?= e($i['product_color']) ?></div>
                    <?php endif; ?>
                  </td>
                  <td class="text-center"><?= (int)$i['quantity'] ?></td>
                  <td class="text-end"><?= e(price((float)$i['unit_price'])) ?></td>
                  <td class="text-end"><?= e(price((float)$i['line_total'])) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
            <tfoot>
              <tr>
                <td colspan="3" class="text-end">Subtotal</td>
                <td class="text-end"><?= e(price((float)$order['subtotal'])) ?></td>
              </tr>
              <tr>
                <td colspan="3" class="text-end"><?= $isPickupOrder ? 'Store pickup' : 'Delivery fee' ?></td>
                <td class="text-end">
                  <?= ((float)$order['shipping_fee'] > 0)
                        ? e(price((float)$order['shipping_fee']))
                        : '<span class="text-success">Free</span>' ?>
                </td>
              </tr>
              <tr class="fw-bold fs-5">
                <td colspan="3" class="text-end">Total</td>
                <td class="text-end"><?= e(price((float)$order['total'])) ?></td>
              </tr>
            </tfoot>
          </table>
          </div>
        </div>
      </div>

    <?php elseif ($searched && !$errors): ?>
      <div class="empty-state">
        <i class="bi bi-question-circle"></i>
        <h3>Order not found</h3>
      </div>
    <?php endif; ?>

  </div>
</section>

<?php include __DIR__ . '/includes/shop_footer.php'; ?>
