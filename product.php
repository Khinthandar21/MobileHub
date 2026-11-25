<?php
require_once __DIR__ . '/includes/shop_bootstrap.php';

$slug = trim((string)($_GET['slug'] ?? ''));

if ($slug === '') {
    header('Location: ' . shop_url('shop.php'));
    exit;
}

try {
    $stmt = db()->prepare(
        "SELECT p.*, c.name AS category_name, c.slug AS category_slug
         FROM products p
         LEFT JOIN categories c ON c.id = p.category_id
         WHERE p.slug = :slug AND p.status = 'active'
         LIMIT 1"
    );
    $stmt->execute([':slug' => $slug]);
    $product = $stmt->fetch();
} catch (Throwable $e) {
    $product = false;
}

if (!$product) {
    http_response_code(404);
    $pageTitle = 'Product not found';
    include __DIR__ . '/includes/shop_header.php';
    ?>
    <section class="section">
      <div class="container">
        <div class="empty-state">
          <i class="bi bi-question-circle"></i>
          <h3>Product not found</h3>
          <p>The product you're looking for doesn't exist or is no longer available.</p>
          <a href="<?= e(shop_url('shop.php')) ?>" class="btn btn-primary">
            Browse all products
          </a>
        </div>
      </div>
    </section>
    <?php
    include __DIR__ . '/includes/shop_footer.php';
    exit;
}

$pageTitle = $product['name'];
$gallery = [];
$seenGallery = [];
$primaryImage = trim((string)($product['image'] ?? ''));
if ($primaryImage !== '' && file_exists(UPLOADS_PATH . '/products/' . $primaryImage)) {
    $gallery[] = ['image' => $primaryImage, 'label' => 'Product detail'];
    $seenGallery[strtolower($primaryImage)] = 0;
}
try {
    $imageStmt = db()->prepare(
        'SELECT image, label FROM product_images
         WHERE product_id = :id ORDER BY sort_order ASC, id ASC'
    );
    $imageStmt->execute([':id' => $product['id']]);
    foreach ($imageStmt->fetchAll() as $galleryImage) {
        $imageName = trim((string)($galleryImage['image'] ?? ''));
        if ($imageName === '' || !file_exists(UPLOADS_PATH . '/products/' . $imageName)) {
            continue;
        }
        $imageKey = strtolower($imageName);
        if (array_key_exists($imageKey, $seenGallery)) {
            $duplicateIndex = $seenGallery[$imageKey];
            if ($gallery[$duplicateIndex]['label'] === 'Product detail'
                && !empty($galleryImage['label'])) {
                $gallery[$duplicateIndex]['label'] = $galleryImage['label'];
            }
            continue;
        }
        $seenGallery[$imageKey] = count($gallery);
        $gallery[] = $galleryImage;
    }
} catch (Throwable $e) {
    // Older installations can still show the product's primary image.
}
if (!$gallery) {
    $gallery[] = ['image' => null, 'label' => 'Product image'];
}
$colorOptions = [];
$finishSwatches = [
    'black' => '#242424', 'navy' => '#26354d', 'blue' => '#8fb9dc',
    'ice' => '#c4d8e8', 'lilac' => '#b9a6d2', 'purple' => '#8552b2',
    'pink' => '#e8a9bd', 'red' => '#c83c4d', 'green' => '#689878',
    'silver' => '#c6c9ce', 'white' => '#f1f2f4', 'cream' => '#e7dfc8',
    'yellow' => '#e7cd65', 'burgundy' => '#641f2f', 'glacier' => '#d9e6f1',
    'gold' => '#d6b36a',
];
foreach ($gallery as $index => $photo) {
    $label = trim((string)($photo['label'] ?? ''));
    if (preg_match('/\b(view|all colors?|detail|product image)\b/i', $label)) {
        continue;
    }
    $swatch = '#9ca3af';
    foreach ($finishSwatches as $colorName => $hex) {
        if (stripos($label, $colorName) !== false) {
            $swatch = $hex;
            break;
        }
    }
    $colorOptions[] = ['index' => $index, 'label' => $label, 'swatch' => $swatch];
}

$onSale     = product_is_on_sale($product);
$discount   = $onSale ? product_discount_percent($product) : 0;
$inStock    = (int)$product['stock'] > 0;

// Related products from same category
$related = [];
if (!empty($product['category_id'])) {
    try {
        $stmt = db()->prepare(
            "SELECT p.*, c.name AS category_name, c.slug AS category_slug
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             WHERE p.category_id = :cid
               AND p.id <> :pid
               AND p.status = 'active'
             ORDER BY p.created_at DESC
             LIMIT 4"
        );
        $stmt->execute([':cid' => $product['category_id'], ':pid' => $product['id']]);
        $related = $stmt->fetchAll();
    } catch (Throwable $e) {}
}

include __DIR__ . '/includes/shop_header.php';
?>

<section class="product-detail">
  <div class="container">

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= e(shop_url()) ?>">Home</a></li>
        <li class="breadcrumb-item"><a href="<?= e(shop_url('shop.php')) ?>">Shop</a></li>
        <?php if (!empty($product['category_slug'])): ?>
          <li class="breadcrumb-item">
            <a href="<?= e(shop_url('category.php?slug=' . urlencode($product['category_slug']))) ?>">
              <?= e($product['category_name']) ?>
            </a>
          </li>
        <?php endif; ?>
        <li class="breadcrumb-item active"><?= e($product['name']) ?></li>
      </ol>
    </nav>

    <div class="row g-4">
      <div class="col-md-6">
        <div class="product-gallery" data-product-gallery>
          <div class="product-gallery-stage">
            <img data-gallery-main
                 src="<?= e(product_image_url($gallery[0]['image'])) ?>"
                 alt="<?= e($product['name'] . ' — ' . $gallery[0]['label']) ?>">
            <?php if (count($gallery) > 1): ?>
              <button type="button" class="product-gallery-arrow is-prev" data-gallery-prev aria-label="Previous photo">
                <i class="bi bi-chevron-left"></i>
              </button>
              <button type="button" class="product-gallery-arrow is-next" data-gallery-next aria-label="Next photo">
                <i class="bi bi-chevron-right"></i>
              </button>
            <?php endif; ?>
          </div>
          <?php if (count($gallery) > 1): ?>
            <div class="product-gallery-dots" role="group" aria-label="Choose product photo">
              <?php foreach ($gallery as $index => $photo): ?>
                <button type="button" class="product-gallery-dot<?= $index === 0 ? ' is-active' : '' ?>"
                        data-gallery-dot data-index="<?= $index ?>"
                        data-src="<?= e(product_image_url($photo['image'])) ?>"
                        data-alt="<?= e($product['name'] . ' — ' . $photo['label']) ?>"
                        data-label="<?= e($photo['label']) ?>"
                        data-image="<?= e($photo['image'] ?? '') ?>"
                        aria-label="Photo <?= $index + 1 ?>: <?= e($photo['label']) ?>"
                        aria-pressed="<?= $index === 0 ? 'true' : 'false' ?>"></button>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
          <?php if ($colorOptions): ?>
            <div class="product-finish-picker">
              <p class="product-finish-availability">Available in <?= count($colorOptions) ?> finishes</p>
              <div class="product-finish-swatches" role="group" aria-label="Choose a color">
                <?php foreach ($colorOptions as $option): ?>
                  <button type="button" class="product-finish-option"
                          data-gallery-color data-index="<?= (int)$option['index'] ?>"
                          data-label="<?= e($option['label']) ?>"
                          data-image="<?= e($gallery[$option['index']]['image'] ?? '') ?>"
                          aria-label="Choose <?= e($option['label']) ?>" aria-pressed="false">
                    <span class="product-finish-swatch" style="--finish-color: <?= e($option['swatch']) ?>"></span>
                    <span class="product-finish-name"><?= e($option['label']) ?></span>
                  </button>
                <?php endforeach; ?>
              </div>
              <p class="product-finish-selected" data-finish-selected data-all-finishes="<?= e(implode(', ', array_column($colorOptions, 'label'))) ?>"><?= e(implode(', ', array_column($colorOptions, 'label'))) ?></p>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <div class="col-md-6">
        <?php if (!empty($product['category_name'])): ?>
          <div class="product-cat text-uppercase small text-muted mb-2">
            <a href="<?= e(shop_url('category.php?slug=' . urlencode($product['category_slug']))) ?>"
               class="text-muted">
              <?= e($product['category_name']) ?>
            </a>
          </div>
        <?php endif; ?>

        <h1><?= e($product['name']) ?></h1>

        <?php if (!empty($product['short_description'])): ?>
          <p class="lead text-muted"><?= e($product['short_description']) ?></p>
        <?php endif; ?>

        <div class="price-block my-3">
          <?php if ($onSale): ?>
            <span><?= e(price((float)$product['sale_price'])) ?></span>
            <span class="old"><?= e(price((float)$product['price'])) ?></span>
            <span class="badge bg-danger fs-6 align-self-center">-<?= $discount ?>%</span>
          <?php else: ?>
            <span><?= e(price((float)$product['price'])) ?></span>
          <?php endif; ?>
        </div>

        <div class="stock-row">
          <?php if ($inStock): ?>
            <span class="in-stock"><i class="bi bi-check-circle-fill"></i> In stock</span>
            — <?= (int)$product['stock'] ?> available
          <?php else: ?>
            <span class="out-of-stock"><i class="bi bi-x-circle-fill"></i> Out of stock</span>
          <?php endif; ?>
          <?php if (!empty($product['sku'])): ?>
            · <span class="text-muted">SKU: <code><?= e($product['sku']) ?></code></span>
          <?php endif; ?>
        </div>

        <form method="post" action="<?= e(shop_url('cart.php')) ?>" class="my-4">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="add">
          <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
          <input type="hidden" name="selected_color" id="selected-product-color" value="">

          <div class="row g-2 align-items-stretch">
            <div class="col-12 col-md-3">
              <input type="number" name="qty" value="1" min="1"
                     max="<?= (int)$product['stock'] ?>"
                     class="form-control form-control-lg"
                     <?= $inStock ? '' : 'disabled' ?>>
            </div>
            <div class="col-12 col-md">
              <button class="btn btn-outline-primary btn-lg w-100"
                      name="return_to" value="cart.php"
                      <?= $inStock ? '' : 'disabled' ?>>
                <i class="bi bi-bag-plus"></i>
                <?= $inStock ? 'Add to cart' : 'Sold out' ?>
              </button>
            </div>
            <?php if ($inStock): ?>
              <div class="col-12 col-md">
                <button class="btn btn-primary btn-lg w-100"
                        name="return_to" value="checkout.php">
                  <i class="bi bi-lightning-charge-fill"></i> Buy now
                </button>
              </div>
            <?php endif; ?>
          </div>
        </form>

        <?php if (!empty($product['description'])): ?>
          <hr>
          <h5>Description</h5>
          <div class="text-muted" style="white-space: pre-line"><?= e($product['description']) ?></div>
        <?php endif; ?>
      </div>
    </div>

    <?php if ($related): ?>
      <hr class="my-5">
      <div class="section-header">
        <h2>You might also like</h2>
        <p>Other products in <?= e($product['category_name']) ?></p>
      </div>

      <div class="row g-3">
        <?php foreach ($related as $p): ?>
          <div class="col-6 col-md-3">
            <?php include __DIR__ . '/_product_card.php'; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </div>
</section>

<script>
document.querySelectorAll('[data-product-gallery]').forEach((gallery) => {
  const mainImage = gallery.querySelector('[data-gallery-main]');
  const dots = [...gallery.querySelectorAll('[data-gallery-dot]')];
  const colors = [...gallery.querySelectorAll('[data-gallery-color]')];
  const selectedFinish = gallery.querySelector('[data-finish-selected]');
  const colorInput = document.getElementById('selected-product-color');
  let activeIndex = 0;

  const showPhoto = (index) => {
    if (!dots.length) return;
    activeIndex = (index + dots.length) % dots.length;
    const activeDot = dots[activeIndex];
    mainImage.src = activeDot.dataset.src;
    mainImage.alt = activeDot.dataset.alt;
    dots.forEach((dot, i) => {
      dot.classList.toggle('is-active', i === activeIndex);
      dot.setAttribute('aria-pressed', i === activeIndex ? 'true' : 'false');
    });
    colors.forEach((color) => {
      const active = Number(color.dataset.index) === activeIndex;
      color.classList.toggle('is-active', active);
      color.setAttribute('aria-pressed', active ? 'true' : 'false');
    });
    if (selectedFinish) {
      const selectedColor = colors.find((color) => Number(color.dataset.index) === activeIndex);
      selectedFinish.textContent = selectedColor
        ? selectedColor.dataset.label
        : selectedFinish.dataset.allFinishes;
      if (colorInput) colorInput.value = selectedColor ? selectedColor.dataset.label : '';
    }
  };

  gallery.querySelector('[data-gallery-prev]')?.addEventListener('click', () => showPhoto(activeIndex - 1));
  gallery.querySelector('[data-gallery-next]')?.addEventListener('click', () => showPhoto(activeIndex + 1));
  dots.forEach((dot) => dot.addEventListener('click', () => showPhoto(Number(dot.dataset.index))));
  colors.forEach((color) => color.addEventListener('click', () => showPhoto(Number(color.dataset.index))));
  showPhoto(0);
});

</script>
<?php include __DIR__ . '/includes/shop_footer.php'; ?>
