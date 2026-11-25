<?php
require_once __DIR__ . '/includes/shop_bootstrap.php';

$pageTitle = 'Checkout';

// Checkout is available only to signed-in customers. Preserve the cart and
// return here after successful sign-in.
if (!current_customer()) {
    flash('warning', 'Please sign in before proceeding to checkout.');
    redirect('customer_login.php?return_to=checkout.php');
}

$cart = cart_load();

if (empty($cart['items'])) {
    flash('warning', 'Your cart is empty. Add some products before checking out.');
    redirect('cart.php');
}

$deliveryFee = (float) setting('shipping_fee', '0');
$shippingFee = $deliveryFee;
$grandTotal  = $cart['subtotal'] + $shippingFee;
$storeAddress = trim((string)setting('store_address', ''));
$mmqrFile = __DIR__ . '/assets/images/mmqr.png';
$mmqrUrl  = file_exists($mmqrFile) ? url('assets/images/mmqr.png') : null;
$customer = current_customer();

$errors = [];
$old    = [
    'customer_name'    => $customer['name'] ?? '',
    'customer_email'   => $customer['email'] ?? '',
    'customer_phone'   => $customer['phone'] ?? '',
    'shipping_address' => $customer['shipping_address'] ?? '',
    'shipping_city'    => $customer['shipping_city'] ?? '',
    'shipping_zip'     => $customer['shipping_zip'] ?? '',
    'shipping_country' => $customer['shipping_country'] ?? '',
    'fulfillment_method' => 'delivery',
    'payment_method'   => 'cod',
    'notes'            => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();

    foreach (array_keys($old) as $k) $old[$k] = trim((string)($_POST[$k] ?? ''));

    $allowedFulfillment = ['delivery', 'pickup'];
    if (!in_array($old['fulfillment_method'], $allowedFulfillment, true)) {
        $errors[] = 'Please choose delivery or store pickup.';
    }
    $isPickup = $old['fulfillment_method'] === 'pickup';
    $shippingFee = $isPickup ? 0.0 : $deliveryFee;
    $grandTotal = $cart['subtotal'] + $shippingFee;

    if ($old['customer_name'] === '')        $errors[] = 'Full name is required.';
    if (!filter_var($old['customer_email'], FILTER_VALIDATE_EMAIL))
                                              $errors[] = 'Valid email is required.';
    if ($old['customer_phone'] === '')        $errors[] = 'Phone number is required.';
    if (!$isPickup && $old['shipping_address'] === '') $errors[] = 'Shipping address is required.';
    if (!$isPickup && $old['shipping_city'] === '')    $errors[] = 'City is required.';
    if ($isPickup) {
        $old['shipping_address'] = 'Store pickup';
        $old['shipping_city'] = '';
        $old['shipping_zip'] = '';
        $old['shipping_country'] = '';
    }

    $allowedPayments = ['cod', 'bank_transfer'];
    if (!in_array($old['payment_method'], $allowedPayments, true)) {
        $errors[] = 'Please choose a valid payment method.';
    }

    $proofExtension = null;
    if ($old['payment_method'] === 'bank_transfer') {
        $proofUpload = $_FILES['payment_proof'] ?? null;
        if (!$proofUpload || ($proofUpload['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            $errors[] = 'Please attach your bank transfer payment screenshot.';
        } elseif (($proofUpload['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            $errors[] = 'Payment screenshot upload failed: ' . upload_error_message((int)$proofUpload['error']);
        } elseif ((int)$proofUpload['size'] > 5 * 1024 * 1024) {
            $errors[] = 'Payment screenshot must be 5 MB or smaller.';
        } else {
            $fileInfo = new finfo(FILEINFO_MIME_TYPE);
            $proofMime = $fileInfo->file($proofUpload['tmp_name']);
            $proofTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
            $proofDimensions = @getimagesize($proofUpload['tmp_name']);
            if (!isset($proofTypes[$proofMime]) || !is_uploaded_file($proofUpload['tmp_name'])) {
                $errors[] = 'Attach a valid JPG, PNG, or WEBP payment screenshot.';
            } elseif (!$proofDimensions || $proofDimensions[0] < 200 || $proofDimensions[1] < 200) {
                $errors[] = 'Payment screenshot must be at least 200 × 200 pixels so it can be read.';
            } else {
                $proofExtension = $proofTypes[$proofMime];
            }
        }
    }

    // Re-verify stock right before order placement.
    if (!$errors) {
        foreach ($cart['items'] as $i) {
            if ((int)$i['qty'] > (int)$i['product']['stock']) {
                $errors[] = 'Not enough stock for "' . $i['product']['name']
                          . '" (only ' . (int)$i['product']['stock'] . ' left).';
            }
        }
    }

    if (!$errors) {
        $pdo = db();
        $savedProofPath = null;
        try {
            $pdo->beginTransaction();

            $orderNumber = generate_order_number();

            $stmt = $pdo->prepare(
                "INSERT INTO orders
                  (customer_id, order_number, customer_name, customer_email, customer_phone,
                   shipping_address, shipping_city, shipping_zip, shipping_country,
                   subtotal, shipping_fee, total, payment_method, fulfillment_method, notes, status)
                 VALUES
                  (:customer_id, :order_number, :name, :email, :phone,
                   :address, :city, :zip, :country,
                   :subtotal, :shipping_fee, :total, :payment, :fulfillment, :notes, 'pending')"
            );
            $stmt->execute([
                ':customer_id'  => $customer ? (int)$customer['id'] : null,
                ':order_number'  => $orderNumber,
                ':name'          => $old['customer_name'],
                ':email'         => $old['customer_email'],
                ':phone'         => $old['customer_phone'],
                ':address'       => $old['shipping_address'],
                ':city'          => $old['shipping_city'],
                ':zip'           => $old['shipping_zip'],
                ':country'       => $old['shipping_country'],
                ':subtotal'      => $cart['subtotal'],
                ':shipping_fee'  => $shippingFee,
                ':total'         => $grandTotal,
                ':payment'       => $old['payment_method'],
                ':fulfillment'   => $old['fulfillment_method'],
                ':notes'         => $old['notes'],
            ]);
            $orderId = (int)$pdo->lastInsertId();

            if ($old['payment_method'] === 'bank_transfer') {
                $proofDir = UPLOADS_PATH . '/payment-proofs';
                if (!is_dir($proofDir) && !mkdir($proofDir, 0775, true) && !is_dir($proofDir)) {
                    throw new RuntimeException('Could not create payment proof storage.');
                }
                $proofName = $orderNumber . '.' . $proofExtension;
                $savedProofPath = $proofDir . '/' . $proofName;
                if (!move_uploaded_file($proofUpload['tmp_name'], $savedProofPath)) {
                    $savedProofPath = null;
                    throw new RuntimeException('Could not save the payment screenshot.');
                }
            }

            $itemStmt = $pdo->prepare(
                "INSERT INTO order_items
                  (order_id, product_id, product_name, product_sku, product_image, product_color,
                   unit_price, quantity, line_total)
                 VALUES
                  (:order_id, :product_id, :name, :sku, :image, :color,
                   :unit_price, :qty, :line_total)"
            );
            $stockStmt = $pdo->prepare(
                "UPDATE products SET stock = GREATEST(stock - :q, 0) WHERE id = :id"
            );

            foreach ($cart['items'] as $i) {
                $p = $i['product'];
                $itemStmt->execute([
                    ':order_id'   => $orderId,
                    ':product_id' => (int)$p['id'],
                    ':name'       => $p['name'],
                    ':sku'        => $p['sku'] ?: null,
                    ':image'      => $i['color_image'] ?: ($p['image'] ?: null),
                    ':color'      => $i['selected_color'] ?: null,
                    ':unit_price' => $i['unit_price'],
                    ':qty'        => $i['qty'],
                    ':line_total' => $i['line_total'],
                ]);
                $stockStmt->execute([
                    ':q'  => $i['qty'],
                    ':id' => (int)$p['id'],
                ]);
            }

            $pdo->commit();

            cart_clear();
            redirect('order_confirmation.php?o=' . urlencode($orderNumber));
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            if ($savedProofPath && is_file($savedProofPath)) {
                @unlink($savedProofPath);
            }
            $errors[] = 'Could not place your order: ' . $e->getMessage();
        }
    }
}

include __DIR__ . '/includes/shop_header.php';
?>

<section class="section checkout-page checkout-modern">
  <div class="container">

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= e(shop_url()) ?>">Home</a></li>
        <li class="breadcrumb-item"><a href="<?= e(shop_url('cart.php')) ?>">Cart</a></li>
        <li class="breadcrumb-item active">Checkout</li>
      </ol>
    </nav>

    <div class="checkout-heading checkout-entrance checkout-delay-1">
      <h1>Checkout</h1>
      <div class="checkout-heading-note"><i class="bi bi-info-circle" aria-hidden="true"></i><p>Please review your details before checkout and update them if anything is incorrect.</p></div>
    </div>

    <?php if ($errors): ?>
      <div class="alert alert-danger">
        <strong>Please fix the following:</strong>
        <ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
      </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="checkout-form" novalidate>
      <?= csrf_field() ?>

      <div class="row g-4 g-xl-5">

        <div class="col-lg-8 checkout-form-sections">
          <section class="card checkout-section-card checkout-entrance checkout-delay-1">
            <div class="card-body">
              <h2 class="card-title"><i class="bi bi-person text-muted" aria-hidden="true"></i> Contact information</h2>
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Full name *</label>
                  <input name="customer_name" class="form-control"
                         value="<?= e($old['customer_name']) ?>" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Email *</label>
                  <input name="customer_email" type="email" class="form-control"
                         value="<?= e($old['customer_email']) ?>" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Phone *</label>
                  <input name="customer_phone" class="form-control"
                         value="<?= e($old['customer_phone']) ?>" required>
                </div>
              </div>
            </div>
          </section>

          <section class="card checkout-section-card mt-4 checkout-entrance checkout-delay-2">
            <div class="card-body">
              <h2 class="card-title"><i class="bi bi-box-seam text-muted" aria-hidden="true"></i> How would you like to receive your order?</h2>
              <div class="checkout-choice <?= $old['fulfillment_method'] === 'delivery' ? 'is-selected' : '' ?> mb-2">
                <input class="form-check-input" type="radio" name="fulfillment_method" id="fulfillment_delivery" value="delivery" <?= $old['fulfillment_method'] === 'delivery' ? 'checked' : '' ?>>
                <label class="form-check-label" for="fulfillment_delivery">
                  <strong><i class="bi bi-truck me-1"></i> Delivery</strong>
                  <span class="small text-muted">Have your order delivered to your address.</span>
                </label>
              </div>
              <div class="checkout-choice <?= $old['fulfillment_method'] === 'pickup' ? 'is-selected' : '' ?>">
                <input class="form-check-input" type="radio" name="fulfillment_method" id="fulfillment_pickup" value="pickup" <?= $old['fulfillment_method'] === 'pickup' ? 'checked' : '' ?>>
                <label class="form-check-label" for="fulfillment_pickup">
                  <strong><i class="bi bi-shop me-1"></i> Reserve for store pickup</strong>
                  <span class="small text-muted">We’ll reserve your items so you can collect them from the shop. No delivery fee.</span>
                </label>
              </div>
              <div id="pickup-location-note" class="alert alert-info small mt-3 mb-0 <?= $old['fulfillment_method'] === 'pickup' ? '' : 'd-none' ?>">
                <strong class="d-block">Pickup location:</strong>
                <?php if ($storeAddress !== ''): ?>
                  <?= nl2br(e($storeAddress)) ?>
                <?php else: ?>
                  <?php $pickupContactEmail = trim((string)setting('site_email', '')); ?>
                  <?= $pickupContactEmail !== '' ? 'Please contact ' . e($pickupContactEmail) . ' to confirm the shop address before visiting.' : 'Please contact the shop to confirm the pickup address before visiting.' ?>
                <?php endif; ?>
                <div class="mt-2">Please choose your preferred payment method below before placing your order.</div>
                <div id="pickup-payment-note" class="mt-1"><?= $old['payment_method'] === 'bank_transfer' ? 'Pay with MMQR and attach your payment screenshot below.' : 'Payment by cash is due when you collect your order.' ?></div>
              </div>
            </div>
          </section>

          <section class="card checkout-section-card mt-4 checkout-entrance checkout-delay-3 <?= $old['fulfillment_method'] === 'pickup' ? 'd-none' : '' ?>" id="delivery-address-fields">
            <div class="card-body">
              <h2 class="card-title"><i class="bi bi-geo-alt text-muted" aria-hidden="true"></i> Shipping address</h2>
              <div class="row g-3">
                <div class="col-12">
                  <label class="form-label">Street address *</label>
                  <textarea id="shipping-address" name="shipping_address" class="form-control" rows="2" <?= $old['fulfillment_method'] === 'delivery' ? 'required' : '' ?>><?= e($old['shipping_address']) ?></textarea>
                </div>
                <div class="col-md-6">
                  <label class="form-label">City *</label>
                  <input id="shipping-city" name="shipping_city" class="form-control"
                         value="<?= e($old['shipping_city']) ?>" <?= $old['fulfillment_method'] === 'delivery' ? 'required' : '' ?>>
                </div>
                <div class="col-md-3">
                  <label class="form-label">ZIP / Postal</label>
                  <input name="shipping_zip" class="form-control"
                         value="<?= e($old['shipping_zip']) ?>">
                </div>
                <div class="col-md-3">
                  <label class="form-label">Country</label>
                  <input name="shipping_country" class="form-control"
                         value="<?= e($old['shipping_country']) ?>">
                </div>
              </div>
            </div>
          </section>

          <section class="card checkout-section-card mt-4 checkout-entrance checkout-delay-4">
            <div class="card-body">
              <h2 class="card-title"><i class="bi bi-credit-card text-muted" aria-hidden="true"></i> Payment method</h2>
              <div class="checkout-choice <?= $old['payment_method'] === 'cod' ? 'is-selected' : '' ?> mb-2">
                <input class="form-check-input" type="radio" name="payment_method"
                       id="pm_cod" value="cod"
                       <?= $old['payment_method'] === 'cod' ? 'checked' : '' ?>>
                <label class="form-check-label" for="pm_cod">
                  <strong id="cod-payment-title"><?= $old['fulfillment_method'] === 'pickup' ? 'Pay when you collect' : 'Cash on Delivery' ?></strong>
                  <span id="cod-payment-description" class="small text-muted"><?= $old['fulfillment_method'] === 'pickup' ? 'Pay with cash at the shop when you collect your order.' : 'Pay with cash when the order arrives.' ?></span>
                </label>
              </div>
              <div class="checkout-choice <?= $old['payment_method'] === 'bank_transfer' ? 'is-selected' : '' ?>">
                <input class="form-check-input" type="radio" name="payment_method"
                       id="pm_bank" value="bank_transfer"
                       <?= $old['payment_method'] === 'bank_transfer' ? 'checked' : '' ?>>
                <label class="form-check-label" for="pm_bank">
                  <strong>Bank Transfer</strong>
                  <span class="small text-muted">Pay with MMQR and attach your payment screenshot.</span>
                </label>
              </div>
              <div id="bank-transfer-details" class="mt-3 p-3 border rounded-3 <?= $old['payment_method'] === 'bank_transfer' ? '' : 'd-none' ?>" aria-live="polite">
                <h6 class="mb-2">Pay with MMQR</h6>
                <p class="small text-muted mb-2">Scan this QR code with your banking app to pay <strong id="bank-payment-amount" data-delivery="<?= e(price($cart['subtotal'] + $deliveryFee)) ?>" data-pickup="<?= e(price($cart['subtotal'])) ?>"><?= e(price($grandTotal)) ?></strong>.</p>
                <?php if ($mmqrUrl): ?>
                  <img src="<?= e($mmqrUrl) ?>" alt="MobileHub MMQR payment code" class="img-fluid d-block mx-auto rounded mb-3" style="width: min(100%, 280px);">
                <?php else: ?>
                  <div class="alert alert-warning mb-0 small">MMQR image not found. Add the QR image at <code>assets/images/mmqr.png</code> to display it here.</div>
                <?php endif; ?>
                <label for="payment-proof" class="form-label fw-semibold">Attach payment screenshot <span class="text-danger">*</span></label>
                <input type="file" id="payment-proof" name="payment_proof" class="form-control" accept="image/jpeg,image/png,image/webp">
                <div class="form-text">JPG, PNG, or WEBP; at least 200 × 200 pixels and no more than 5 MB.</div>
                <div id="payment-proof-feedback" class="small mt-2" aria-live="polite"></div>
                <img id="payment-proof-preview" class="img-fluid rounded border mt-2 d-none" alt="Payment screenshot preview" style="max-height: 220px;">
              </div>
            </div>
          </section>

          <section class="card checkout-section-card mt-4 checkout-entrance checkout-delay-5">
            <div class="card-body">
              <h2 class="card-title"><i class="bi bi-pencil-square text-muted" aria-hidden="true"></i> Order notes <span class="small text-muted fw-normal">(optional)</span></h2>
              <textarea name="notes" class="form-control" rows="3"
                        placeholder="Anything we should know about delivery?"><?= e($old['notes']) ?></textarea>
            </div>
          </section>
        </div>

        <div class="col-lg-4">
          <div class="card checkout-summary checkout-entrance checkout-delay-summary">
            <div class="card-body">
              <h5 class="card-title">Your order</h5>
              <hr>
              <ul class="list-unstyled mb-3">
                <?php foreach ($cart['items'] as $i): ?>
                  <li class="checkout-item-row">
                    <div class="checkout-item-info">
                      <?php if (!empty($i['color_image']) || !empty($i['product']['image'])): ?>
                        <img src="<?= e(product_image_url($i['color_image'] ?: $i['product']['image'])) ?>" alt="" class="checkout-item-image">
                      <?php else: ?>
                        <span class="checkout-item-image checkout-item-placeholder"><i class="bi bi-phone" aria-hidden="true"></i></span>
                      <?php endif; ?>
                      <span class="checkout-item-name"><?= e($i['product']['name']) ?><?php if (!empty($i['selected_color'])): ?><small>Color: <?= e($i['selected_color']) ?></small><?php endif; ?><small>Qty: <?= (int)$i['qty'] ?></small></span>
                    </div>
                    <span class="fw-semibold checkout-item-total"><?= e(price($i['line_total'])) ?></span>
                  </li>
                <?php endforeach; ?>
              </ul>
              <hr>
              <div class="d-flex justify-content-between mb-2">
                <span>Subtotal</span>
                <strong><?= e(price($cart['subtotal'])) ?></strong>
              </div>
              <div class="d-flex justify-content-between mb-2">
                <span>Shipping</span>
                <strong id="shipping-summary"
                        data-delivery="<?= $deliveryFee > 0 ? e(price($deliveryFee)) : 'Free' ?>"
                        data-pickup="Store pickup · Free">
                  <?= $old['fulfillment_method'] === 'pickup' ? 'Store pickup · Free' : ($shippingFee > 0 ? e(price($shippingFee)) : '<span class="text-success">Free</span>') ?>
                </strong>
              </div>
              <hr>
              <div class="d-flex justify-content-between align-items-end checkout-total-row mb-3">
                <span>Total</span>
                <strong id="checkout-grand-total"
                        data-delivery="<?= e(price($cart['subtotal'] + $deliveryFee)) ?>"
                        data-pickup="<?= e(price($cart['subtotal'])) ?>"><?= e(price($grandTotal)) ?></strong>
              </div>
              <button id="place-order-btn" class="btn btn-primary w-100 btn-lg" <?= $old['payment_method'] === 'bank_transfer' ? 'disabled' : '' ?>>
                <i class="bi bi-check2-circle"></i> Place order
              </button>
              <a href="<?= e(shop_url('cart.php')) ?>"
                 class="btn btn-link w-100 mt-2">Back to cart</a>
            </div>
          </div>
        </div>

      </div>
    </form>

  </div>
</section>

<script>
const paymentRadios = document.querySelectorAll('input[name="payment_method"]');
const transferDetails = document.getElementById('bank-transfer-details');
const proofInput = document.getElementById('payment-proof');
const proofFeedback = document.getElementById('payment-proof-feedback');
const proofPreview = document.getElementById('payment-proof-preview');
const placeOrderButton = document.getElementById('place-order-btn');
const maxProofBytes = 5 * 1024 * 1024;
const allowedProofTypes = ['image/jpeg', 'image/png', 'image/webp'];
const fulfillmentRadios = document.querySelectorAll('input[name="fulfillment_method"]');

function syncFulfillmentMethod() {
  const pickupSelected = document.getElementById('fulfillment_pickup').checked;
  document.querySelectorAll('input[name="fulfillment_method"]').forEach((radio) => {
    radio.closest('.checkout-choice')?.classList.toggle('is-selected', radio.checked);
  });
  const addressFields = document.getElementById('delivery-address-fields');
  const pickupNote = document.getElementById('pickup-location-note');
  addressFields.classList.toggle('d-none', pickupSelected);
  pickupNote.classList.toggle('d-none', !pickupSelected);
  document.getElementById('shipping-address').required = !pickupSelected;
  document.getElementById('shipping-city').required = !pickupSelected;

  const shippingSummary = document.getElementById('shipping-summary');
  const grandTotal = document.getElementById('checkout-grand-total');
  shippingSummary.textContent = pickupSelected ? shippingSummary.dataset.pickup : shippingSummary.dataset.delivery;
  grandTotal.textContent = pickupSelected ? grandTotal.dataset.pickup : grandTotal.dataset.delivery;

  const bankAmount = document.getElementById('bank-payment-amount');
  bankAmount.textContent = pickupSelected ? bankAmount.dataset.pickup : bankAmount.dataset.delivery;
  document.getElementById('cod-payment-title').textContent = pickupSelected ? 'Pay when you collect' : 'Cash on Delivery';
  document.getElementById('cod-payment-description').textContent = pickupSelected
    ? 'Pay with cash at the shop when you collect your order.'
    : 'Pay with cash when the order arrives.';
  document.getElementById('pickup-payment-note').textContent = document.getElementById('pm_bank').checked
    ? 'Pay with MMQR and attach your payment screenshot below.'
    : 'Your booking will be cancelled automatically when you do not collect your order within 3 days.';
}

function syncPaymentMethod() {
  const bankSelected = document.getElementById('pm_bank').checked;
  paymentRadios.forEach((radio) => {
    radio.closest('.checkout-choice')?.classList.toggle('is-selected', radio.checked);
  });
  transferDetails.classList.toggle('d-none', !bankSelected);
  proofInput.required = bankSelected;
  const file = proofInput.files[0];
  const validFile = file && file.size <= maxProofBytes && allowedProofTypes.includes(file.type);
  placeOrderButton.disabled = bankSelected && !validFile;
  if (file && !validFile) {
    proofFeedback.textContent = file.size > maxProofBytes
      ? 'File is too large. Choose an image under 5 MB.'
      : 'Choose a JPG, PNG, or WEBP image.';
    proofFeedback.className = 'small mt-2 text-danger';
    proofPreview.classList.add('d-none');
  } else if (file) {
    proofFeedback.textContent = file.name;
    proofFeedback.className = 'small mt-2 text-success';
    proofPreview.src = URL.createObjectURL(file);
    proofPreview.classList.remove('d-none');
  } else {
    proofFeedback.textContent = '';
    proofPreview.classList.add('d-none');
    proofPreview.removeAttribute('src');
  }
}

paymentRadios.forEach((radio) => radio.addEventListener('change', () => {
  syncPaymentMethod();
  syncFulfillmentMethod();
}));
fulfillmentRadios.forEach((radio) => radio.addEventListener('change', syncFulfillmentMethod));
proofInput.addEventListener('change', syncPaymentMethod);
syncFulfillmentMethod();
syncPaymentMethod();
</script>
<?php include __DIR__ . '/includes/shop_footer.php'; ?>
