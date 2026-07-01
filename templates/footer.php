</main>
<?php include __DIR__ . '/layouts/footer.php'; ?>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<?php
  $assetBase = function_exists('app_url') ? app_url() : '';
  $assetVersion = '20260701-form-validation';
?>
<script src="<?php echo htmlspecialchars($assetBase); ?>public/assets/js/theme-switcher.js?v=<?php echo htmlspecialchars($assetVersion); ?>"></script>
<script src="<?php echo htmlspecialchars($assetBase); ?>public/assets/js/components.js?v=<?php echo htmlspecialchars($assetVersion); ?>"></script>
<script src="<?php echo htmlspecialchars($assetBase); ?>public/assets/js/search.js?v=<?php echo htmlspecialchars($assetVersion); ?>"></script>
<script src="<?php echo htmlspecialchars($assetBase); ?>public/assets/js/cart.js?v=<?php echo htmlspecialchars($assetVersion); ?>"></script>
<script>
  $(function () {
    $('.toast').toast({ delay: 5000, autohide: true }).toast('show');
    $(document).on('click', '.toast .close', function () {
      $(this).closest('.toast').toast('hide');
    });
  });
</script>
</body>
</html>
