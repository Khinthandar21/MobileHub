<?php
require_once __DIR__ . '/includes/auth.php';
require_role('admin', 'editor');

$id        = (int)($_GET['id'] ?? 0);
$pageTitle = $id ? 'Edit category' : 'Add category';
$errors    = [];
$nameError = '';
$descriptionError = '';

$cat = ['id' => 0, 'name' => '', 'slug' => '', 'description' => '', 'status' => 'active'];
if ($id) {
    $stmt = db()->prepare('SELECT * FROM categories WHERE id = :id');
    $stmt->execute([':id' => $id]);
    if (!($cat = $stmt->fetch())) {
        flash('warning', 'Category not found.');
        admin_redirect('categories.php');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();

    $name        = trim($_POST['name']        ?? '');
    $slug        = trim($_POST['slug']        ?? '');
    $description = trim($_POST['description'] ?? '');
    $status      = $_POST['status'] ?? 'active';

    if ($name === '') {
        $nameError = 'Category name is required.';
    } elseif (preg_match('/\A[\p{L}\p{M}\p{N} -]+\z/u', $name) !== 1
        && (!$id || $name !== (string)$cat['name'])) {
        $nameError = 'Use letters, numbers, spaces, and hyphens only.';
    }
    if ($description === '') {
        $descriptionError = 'Category description is required.';
    }
    if (!in_array($status, ['active', 'inactive'], true)) $status = 'active';

    if (!$errors && $nameError === '' && $descriptionError === '') {
        $slug = $slug !== '' ? slugify($slug) : slugify($name);
        $slug = unique_slug($slug, 'categories', $id);

        if ($id) {
            db()->prepare(
                'UPDATE categories SET name=:n, slug=:s, description=:d, status=:st WHERE id=:id'
            )->execute([
                ':n' => $name, ':s' => $slug, ':d' => $description,
                ':st' => $status, ':id' => $id,
            ]);
            log_activity('category.update', 'Updated category ' . $name);
            flash('success', 'Category updated.');
        } else {
            db()->prepare(
                'INSERT INTO categories (name, slug, description, status)
                 VALUES (:n, :s, :d, :st)'
            )->execute([
                ':n' => $name, ':s' => $slug, ':d' => $description, ':st' => $status,
            ]);
            log_activity('category.create', 'Created category ' . $name);
            flash('success', 'Category created.');
        }
        admin_redirect('categories.php');
    }

    $cat = array_merge($cat, compact('name', 'slug', 'description', 'status'));
}

include __DIR__ . '/includes/header.php';
?>

<div class="row">
  <div class="col-lg-8">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title mb-3"><?= $id ? 'Edit category' : 'Add new category' ?></h5>

        <?php foreach ($errors as $err): ?>
          <div class="alert alert-danger py-2"><?= e($err) ?></div>
        <?php endforeach; ?>

        <form method="post" autocomplete="off">
          <?= csrf_field() ?>

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label" for="categoryName">Name <span class="text-danger">*</span></label>
              <input type="text" id="categoryName" name="name" class="form-control <?= $nameError !== '' ? 'is-invalid' : '' ?>"
                     value="<?= e($cat['name']) ?>" maxlength="100" required aria-describedby="categoryNameHelp<?= $nameError !== '' ? ' categoryNameError' : '' ?>">
              <div id="categoryNameHelp" class="form-text">Use letters, numbers, spaces, and hyphens.</div>
              <?php if ($nameError !== ''): ?><div id="categoryNameError" class="invalid-feedback d-block"><?= e($nameError) ?></div><?php endif; ?>
            </div>
            <div class="col-md-6">
              <label class="form-label">Slug <small class="text-muted">(auto if blank)</small></label>
              <input type="text" name="slug" class="form-control"
                     value="<?= e($cat['slug']) ?>" placeholder="auto-generated">
            </div>
            <div class="col-md-6">
              <label class="form-label">Status</label>
              <select name="status" class="form-select">
                <option value="active"   <?= $cat['status'] === 'active'   ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= $cat['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label" for="categoryDescription">Description <span class="text-danger">*</span></label>
              <textarea id="categoryDescription" name="description" class="form-control <?= $descriptionError !== '' ? 'is-invalid' : '' ?>" rows="4" required<?= $descriptionError !== '' ? ' aria-describedby="categoryDescriptionError"' : '' ?>><?= e($cat['description'] ?? '') ?></textarea>
              <?php if ($descriptionError !== ''): ?><div id="categoryDescriptionError" class="invalid-feedback d-block"><?= e($descriptionError) ?></div><?php endif; ?>
            </div>
          </div>

          <div class="mt-4 d-flex gap-2">
            <button class="btn btn-primary"><i class="bi bi-check2"></i>
              <?= $id ? 'Save changes' : 'Create category' ?></button>
            <a href="<?= e(admin_url('categories.php')) ?>" class="btn btn-light">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
