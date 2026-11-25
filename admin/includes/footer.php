    </div><!-- /.app-content -->

    <footer class="app-footer">
      <small>
        &copy; <?= date('Y') ?> <?= e(setting('site_name', APP_NAME)) ?> ·
        Built with PHP &amp; Bootstrap.
      </small>
    </footer>
  </main><!-- /.app-main -->
</div><!-- /.app -->

<div class="modal fade admin-action-confirm-modal" id="actionConfirmModal" tabindex="-1" aria-labelledby="actionConfirmTitle" aria-describedby="actionConfirmMessage" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered admin-action-confirm-dialog">
    <div class="modal-content">
      <div class="modal-body text-center p-4">
        <div class="admin-confirm-icon"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i></div>
        <h2 class="modal-title h5 mb-2" id="actionConfirmTitle">Confirm action?</h2>
        <p class="text-muted mb-4" id="actionConfirmMessage">Are you sure?</p>
        <div class="d-flex justify-content-center gap-2">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-danger" id="actionConfirmAccept">Confirm</button>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script src="<?= e(url('assets/js/script.js') . '?v=' . (string)filemtime(ROOT_PATH . '/assets/js/script.js')) ?>"></script>
</body>
</html>
