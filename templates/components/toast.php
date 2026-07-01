<?php
$toastId = $toastId ?? 'appToast';
$toastType = $toastType ?? 'info';
$toastMessage = $toastMessage ?? 'This is a toast notification.';
$toastClass = in_array($toastType, ['success', 'danger', 'warning', 'info'], true) ? 'app-toast-' . $toastType : 'app-toast-info';
$toastDelay = isset($toastDelay) ? (int) $toastDelay : 5000;
?>
<div class="toast-container" aria-live="polite" aria-atomic="true">
  <div
    id="<?php echo htmlspecialchars($toastId); ?>"
    class="toast app-toast <?php echo htmlspecialchars($toastClass); ?>"
    role="alert"
    aria-live="assertive"
    aria-atomic="true"
    data-delay="<?php echo $toastDelay; ?>"
    data-autohide="true"
  >
    <div class="toast-body">
      <span><?php echo htmlspecialchars($toastMessage); ?></span>
      <button type="button" class="close" data-dismiss="toast" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
  </div>
</div>
