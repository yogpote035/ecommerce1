<?php
$modalId = $modalId ?? 'genericModal';
$modalTitle = $modalTitle ?? 'Modal Title';
$modalBody = $modalBody ?? 'Modal content goes here.';
$modalFooter = $modalFooter ?? '<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>';
$modalSize = $modalSize ?? '';
$dialogClass = trim('modal-dialog modal-dialog-centered ' . $modalSize);
?>
<div class="modal fade" id="<?php echo htmlspecialchars($modalId); ?>" tabindex="-1" role="dialog" aria-labelledby="<?php echo htmlspecialchars($modalId); ?>Label" aria-hidden="true">
  <div class="<?php echo htmlspecialchars($dialogClass); ?>" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="<?php echo htmlspecialchars($modalId); ?>Label"><?php echo htmlspecialchars($modalTitle); ?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <?php echo is_callable($modalBody) ? $modalBody() : $modalBody; ?>
      </div>
      <div class="modal-footer">
        <?php echo is_callable($modalFooter) ? $modalFooter() : $modalFooter; ?>
      </div>
    </div>
  </div>
</div>
