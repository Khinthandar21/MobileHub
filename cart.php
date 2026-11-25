<?php
require_once __DIR__ . '/includes/shop_bootstrap.php';

$pageTitle = 'Shopping Cart';

// ----- Handle POST actions -----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $pid = (int)($_POST['product_id'] ?? 0);
        $qty = (int)($_POST['qty']        ?? 1);

        // Verify product exists & is active.
        if ($pid > 0) {
            $stmt = db()->prepare(
                "SELECT id, name, stock FROM products
                 WHERE id = :id AND status = 'active' LIMIT 1"
            );
            $stmt->execute([':id' => $pid]);
            $p = $stmt->fetch();
            if ($p) {
                $selectedColor = trim((string)($_POST['selected_color'] ?? ''));
                $selectedColorImage = '';
                $validColor = true;
                if ($selectedColor !== '') {
                    $colorStmt = db()->prepare(
                    'SELECT image, label FROM product_images WHERE product_id = :product_id AND label = :label LIMIT 1'
                    );
                    $colorStmt->execute([':product_id' => $pid, ':label' => $selectedColor]);
                    $colorRow = $colorStmt->fetch();
                    if (!$colorRow || preg_match('/\b(view|all colors?|detail|product image)\b/i', (string)$colorRow['label'])) {
                        $validColor = false;
                        flash('warning', 'Please select an available product color.');
                    } else {
                        $selectedColorImage = (string)$colorRow['image'];
                    }
                }
                if ((int)$p['stock'] <= 0) {
                    flash('warning', $p['name'] . ' is currently out of stock.');
                } elseif ($validColor) {
                    $newQty = cart_add($pid, $qty);
                    if ($selectedColor !== '') {
                        $_SESSION['cart_colors'][$pid] = [
                            'label' => $selectedColor,
                            'image' => $selectedColorImage,
                        ];
                    } else {
                        unset($_SESSION['cart_colors'][$pid]);
                    }
                    if ($newQty > (int)$p['stock']) {
                        cart_set($pid, (int)$p['stock']);
                        flash('warning', 'Only ' . (int)$p['stock'] . ' of '
                            . $p['name'] . ' are available.');
                    }
                }
            }
        }
        // After adding, allow returning to one of a small allow-list of pages.
        // 'checkout.php' is used by the "Buy now" button on the product page.
        $return  = $_POST['return_to'] ?? 'cart.php';
        $allowed = ['cart.php', 'checkout.php', 'shop.php', 'index.php'];
        redirect(in_array($return, $allowed, true) ? $return : 'cart.php');
    }

    if ($action === 'update') {
        // A row "remove" button submits remove_id alongside the form.
        if (!empty($_POST['remove_id'])) {
            cart_remove((int)$_POST['remove_id']);
            $_SESSION['_customer_success_toast'] = 'Item removed from your cart.';
        } else {
            $items = $_POST['qty'] ?? [];
            if (is_array($items)) {
                foreach ($items as $pid => $qty) {
                    cart_set((int)$pid, (int)$qty);
                }
            }
            $_SESSION['_customer_success_toast'] = 'Cart updated successfully.';
        }
        redirect('cart.php');
    }

    if ($action === 'remove') {
        $pid = (int)($_POST['product_id'] ?? 0);
        if ($pid) cart_remove($pid);
        $_SESSION['_customer_success_toast'] = 'Item removed from your cart.';
        redirect('cart.php');
    }

    if ($action === 'clear') {
        cart_clear();
        $_SESSION['_customer_success_toast'] = 'Your cart has been cleared.';
        redirect('cart.php');
    }
}

$cart = cart_load();
// Cart actions and other customer flows may queue generic flash messages;
// keep them from accumulating and appearing unexpectedly on a later page.
get_flashes();
$bodyClass = empty($cart['items']) ? 'cart-empty-body' : '';
$customerSuccessToast = (string)($_SESSION['_customer_success_toast'] ?? '');
unset($_SESSION['_customer_success_toast']);

include __DIR__ . '/includes/shop_header.php';
?>

<section class="section cart-page-section<?= !$cart['items'] ? ' cart-empty-layout' : '' ?>">
  <?php if ($customerSuccessToast !== ''): ?>
    <div class="customer-success-toast" id="customerSuccessToast" role="status" aria-live="polite">
      <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
      <span><?= e($customerSuccessToast) ?></span>
      <button type="button" class="profile-toast-close" aria-label="Dismiss message"><i class="bi bi-x-lg"></i></button>
    </div>
  <?php endif; ?>
  <div class="container">

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= e(shop_url()) ?>">Home</a></li>
        <li class="breadcrumb-item active">Cart</li>
      </ol>
    </nav>

    <div class="cart-page-heading">
      <div class="cart-page-heading-icon" aria-hidden="true"><i class="bi bi-bag"></i></div>
      <div>
        <div class="cart-page-title-line cart-page-eyebrow">
          <h2>Shopping Cart</h2>
          <span class="cart-page-count"><?= count($cart['items']) ?> item<?= count($cart['items']) === 1 ? '' : 's' ?></span>
        </div>
        <p>Review your items before checkout.</p>
      </div>
    </div>

    <?php if (!$cart['items']): ?>
      <div class="cart-empty-center">
        <div class="empty-state">
          <div class="empty-cart-icon" aria-hidden="true"><i class="bi bi-cart-x"></i></div>
          <h3>Your cart is empty</h3>
          <p>Looks like you haven't added anything yet.</p>
          <a href="<?= e(shop_url('shop.php')) ?>" class="btn btn-primary empty-cart-cta">
            <i class="bi bi-bag"></i><span>Continue shopping</span><i class="bi bi-arrow-right empty-cart-arrow"></i>
          </a>
        </div>
      </div>
    <?php else: ?>
      <div class="row g-4">
        <div class="col-lg-8">

          <form method="post" id="cartForm">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="update">

            <div class="card">
              <div class="card-body p-0">
                <div class="table-responsive">
                  <table class="table align-middle mb-0 cart-table">
                    <thead>
                      <tr>
                        <th colspan="2">Product</th>
                        <th>Price</th>
                        <th style="width:120px">Qty</th>
                        <th class="text-end">Total</th>
                        <th style="width:46px"></th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($cart['items'] as $i): $p = $i['product']; ?>
                        <tr>
                          <td style="width:84px">
                            <img src="<?= e(product_image_url($i['color_image'] ?: $p['image'])) ?>"
                                 class="rounded" width="64" height="64"
                                 style="object-fit:cover" alt="">
                          </td>
                          <td>
                            <a href="<?= e(shop_url('product.php?slug=' . urlencode($p['slug']))) ?>"
                               class="fw-semibold text-reset text-decoration-none">
                              <?= e($p['name']) ?>
                            </a>
                            <?php if (!empty($p['sku'])): ?>
                              <div class="small text-muted">SKU: <?= e($p['sku']) ?></div>
                            <?php endif; ?>
                            <?php if (!empty($i['selected_color'])): ?><div class="small text-muted">Color: <?= e($i['selected_color']) ?></div><?php endif; ?>
                          </td>
                          <td><?= e(price($i['unit_price'])) ?></td>
                          <td>
                            <input type="number" name="qty[<?= (int)$p['id'] ?>]"
                                   class="form-control form-control-sm qty-input"
                                   value="<?= (int)$i['qty'] ?>"
                                   min="0" max="<?= (int)$p['stock'] ?>">
                          </td>
                          <td class="text-end fw-semibold"><?= e(price($i['line_total'])) ?></td>
                          <td class="text-end">
                            <button class="btn btn-sm btn-outline-danger"
                                    name="remove_id" value="<?= (int)$p['id'] ?>"
                                    title="Remove" formnovalidate
                                    data-confirm="Remove this item from your cart?"
                                    data-confirm-button="Remove item">
                              <i class="bi bi-x-lg"></i>
                            </button>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

          </form>

          <div class="d-flex align-items-center gap-2 mt-3 flex-wrap cart-actions">
            <button type="submit" form="cartForm" class="btn btn-outline-primary">
              <i class="bi bi-pencil-square"></i> Update Cart
            </button>
            <a href="<?= e(shop_url('shop.php')) ?>" class="btn btn-outline-secondary">
              <i class="bi bi-bag"></i> Continue Shopping
            </a>
            <form method="post" class="ms-auto">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="clear">
            <button class="btn btn-outline-danger"
                    data-confirm="Clear all items from your cart?"
                    data-confirm-button="Clear cart">
              <i class="bi bi-trash"></i> Clear Cart
            </button>
            </form>
          </div>
        </div>

        <div class="col-lg-4">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Order summary</h5>
              <hr>
              <div class="d-flex justify-content-between mb-2">
                <span>Subtotal</span>
                <strong><?= e(price($cart['subtotal'])) ?></strong>
              </div>
              <?php $shipping = (float) setting('shipping_fee', '0'); ?>
              <div class="d-flex justify-content-between mb-2">
                <span>Shipping</span>
                <strong>
                  <?= $shipping > 0 ? e(price($shipping)) : '<span class="text-success">Free</span>' ?>
                </strong>
              </div>
              <hr>
              <div class="d-flex justify-content-between fs-5">
                <span>Total</span>
                <strong><?= e(price($cart['subtotal'] + $shipping)) ?></strong>
              </div>

              <a href="<?= e(shop_url('checkout.php')) ?>" class="btn btn-primary w-100 mt-3">
                <i class="bi bi-lock-fill"></i> Proceed to Checkout
              </a>
            </div>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>

<div class="modal fade action-confirm-modal" id="actionConfirmModal" tabindex="-1" aria-labelledby="actionConfirmTitle" aria-describedby="actionConfirmMessage" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body text-center p-4 p-md-5">
        <div class="action-confirm-icon"><i class="bi bi-cart-x" aria-hidden="true"></i></div>
        <h5 class="modal-title mb-2" id="actionConfirmTitle">Remove from cart?</h5>
        <p class="text-muted mb-4" id="actionConfirmMessage">Are you sure you want to remove this item?</p>
        <div class="d-flex justify-content-center gap-2">
          <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Keep item</button>
          <button type="button" class="btn btn-danger px-4" id="actionConfirmAccept">Remove item</button>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/shop_footer.php'; ?>
