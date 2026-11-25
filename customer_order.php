<?php
require_once __DIR__ . '/includes/shop_bootstrap.php';

$customer = current_customer();
if (!$customer) {
    flash('info', 'Sign in to view your order details.');
    redirect('customer_login.php');
}

$orderId = max(0, (int)($_GET['id'] ?? 0));
$stmt = db()->prepare(
    'SELECT o.* FROM orders o
     WHERE o.id = :order_id AND o.customer_id = :customer_id
     LIMIT 1'
);
$stmt->execute([':order_id' => $orderId, ':customer_id' => (int)$customer['id']]);
$order = $stmt->fetch();

if (!$order) {
    http_response_code(404);
    $pageTitle = 'Order not found';
} else {
    $pageTitle = 'Order ' . $order['order_number'];
    $stmt = db()->prepare('SELECT * FROM order_items WHERE order_id = :order_id ORDER BY id ASC');
    $stmt->execute([':order_id' => (int)$order['id']]);
    $items = $stmt->fetchAll();
}

include __DIR__ . '/includes/shop_header.php';
?>
<section class="section customer-order-page">
  <div class="container">
    <a class="btn btn-link px-0 mb-3" href="<?= e(shop_url('account.php')) ?>"><i class="bi bi-arrow-left"></i> Back to my account</a>

    <?php if (!$order): ?>
      <div class="alert alert-warning">We couldn’t find that order in your account.</div>
    <?php else: ?>
      <?php $statusClass = ['pending' => 'text-bg-warning', 'processing' => 'text-bg-info', 'shipped' => 'text-bg-primary', 'completed' => 'text-bg-success', 'cancelled' => 'text-bg-secondary'][$order['status']] ?? 'text-bg-secondary'; ?>
      <div class="customer-order-heading mb-4">
        <div>
          <span class="section-eyebrow">Order Details</span>
          <div class="customer-order-number-line">
            <h2 class="mb-0"><?= e($order['order_number']) ?></h2>
            <span class="badge <?= e($statusClass) ?> fs-6"><?= e(ucfirst($order['status'])) ?></span>
          </div>
          <p class="text-muted mb-0 mt-1">Placed <?= e(date('M j, Y g:i a', strtotime($order['created_at']))) ?></p>
        </div>
      </div>

      <div class="card mb-4">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table align-middle mb-0">
              <thead><tr><th>Product</th><th class="text-center">Quantity</th><th class="text-end">Unit Price</th><th class="text-end">Total</th></tr></thead>
              <tbody>
                <?php foreach ($items as $item): ?>
                  <tr>
                    <td>
                      <div class="d-flex align-items-center gap-3">
                        <img src="<?= e(product_image_url($item['product_image'])) ?>" alt="" class="rounded border" width="60" height="60" style="object-fit:cover">
                        <div><strong><?= e($item['product_name']) ?></strong><?php if (!empty($item['product_sku'])): ?><div class="small text-muted">SKU: <?= e($item['product_sku']) ?></div><?php endif; ?><?php if (!empty($item['product_color'])): ?><div class="small text-muted">Color: <?= e($item['product_color']) ?></div><?php endif; ?></div>
                      </div>
                    </td>
                    <td class="text-center"><?= (int)$item['quantity'] ?></td>
                    <td class="text-end"><?= e(price((float)$item['unit_price'])) ?></td>
                    <td class="text-end fw-semibold"><?= e(price((float)$item['line_total'])) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="row g-4">
        <div class="col-md-6">
          <div class="card h-100"><div class="card-body">
            <h5 class="card-title"><?= ($order['fulfillment_method'] ?? 'delivery') === 'pickup' ? 'Store pickup details' : 'Delivery Details' ?></h5>
            <div class="customer-order-fields">
              <div class="customer-order-field"><span>Name</span><strong><?= e($order['customer_name']) ?></strong></div>
              <div class="customer-order-field"><span>Email</span><div><?= e($order['customer_email']) ?></div></div>
              <?php if (!empty($order['customer_phone'])): ?><div class="customer-order-field"><span>Phone</span><div><?= e($order['customer_phone']) ?></div></div><?php endif; ?>
            </div>
            <hr>
            <?php if (($order['fulfillment_method'] ?? 'delivery') === 'pickup'): ?>
              <div class="customer-order-fields">
                <div class="customer-order-field"><span>Pickup Location</span><div><?= nl2br(e(setting('store_address', '') ?: 'Contact the store to confirm the pickup location.')) ?></div></div>
              </div>
              <div class="small text-muted mt-1">Bring your order number when collecting your reserved items.</div>
            <?php else: ?>
              <div class="customer-order-fields">
                <?php if (trim((string)$order['shipping_address']) !== ''): ?><div class="customer-order-field"><span>Delivery Address</span><div><?= nl2br(e($order['shipping_address'])) ?></div></div><?php endif; ?>
                <?php $cityZip = trim(($order['shipping_city'] ?? '') . ' ' . ($order['shipping_zip'] ?? '')); ?>
                <?php if ($cityZip !== ''): ?><div class="customer-order-field"><span>City / ZIP</span><div><?= e($cityZip) ?></div></div><?php endif; ?>
                <?php if (trim((string)$order['shipping_country']) !== ''): ?><div class="customer-order-field"><span>Country</span><div><?= e($order['shipping_country']) ?></div></div><?php endif; ?>
              </div>
            <?php endif; ?>
          </div></div>
        </div>
        <div class="col-md-6">
          <div class="card h-100"><div class="card-body">
            <h5 class="card-title">Payment and Total</h5>
            <div class="customer-order-fields">
              <div class="customer-order-field"><span>Payment Method</span><?= e($order['payment_method'] === 'bank_transfer' ? 'Bank Transfer' : (($order['fulfillment_method'] ?? 'delivery') === 'pickup' ? 'Pay at pickup' : 'Cash on Delivery')) ?></div>
              <div class="customer-order-field"><span>Subtotal</span><div><?= e(price((float)$order['subtotal'])) ?></div></div>
              <div class="customer-order-field"><span><?= ($order['fulfillment_method'] ?? 'delivery') === 'pickup' ? 'Store pickup' : 'Delivery fee' ?></span><div><?= ($order['fulfillment_method'] ?? 'delivery') === 'pickup' || (float)$order['shipping_fee'] <= 0 ? 'Free' : e(price((float)$order['shipping_fee'])) ?></div></div>
            </div>
            <hr>
            <div class="customer-order-field customer-order-total"><strong>Total</strong><strong><?= e(price((float)$order['total'])) ?></strong></div>
          </div></div>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php include __DIR__ . '/includes/shop_footer.php'; ?>
