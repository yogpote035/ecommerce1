<?php
$toastId = $toastId ?? 'appToast';
$toastType = $toastType ?? 'info';
$toastTitle = $toastTitle ?? ucfirst($toastType);
$toastMessage = $toastMessage ?? 'This is a toast notification.';
$toastClass = $toastType === 'success' ? 'bg-success text-white' : ($toastType === 'danger' ? 'bg-danger text-white' : ($toastType === 'warning' ? 'bg-warning text-dark' : 'bg-info text-white'));
$toastDelay = isset($toastDelay) ? (int) $toastDelay : 5000;
?>
<div class="toast-container" aria-live="polite" aria-atomic="true">
  <div
    id="<?php echo htmlspecialchars($toastId); ?>"
    class="toast"
    role="alert"
    aria-live="assertive"
    aria-atomic="true"
    data-delay="<?php echo $toastDelay; ?>"
    data-autohide="true"
  >
    <div class="toast-header <?php echo $toastClass; ?>">
      <strong class="mr-auto"><?php echo htmlspecialchars($toastTitle); ?></strong>
      <button type="button" class="ml-2 mb-1 close <?php echo $toastType === 'warning' ? '' : 'text-white'; ?>" data-dismiss="toast" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
    <div class="toast-body">
      <?php echo htmlspecialchars($toastMessage); ?>
    </div>
  </div>
</div>
      </div>
      <div class="toast-body">
        <?php echo $toastMessage; ?>
      </div>
    </div>
  </div>
</div>
