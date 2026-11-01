<?php

$alertTypes = [
    'success' => ['class' => 'alert-success', 'icon' => 'bi-check-circle-fill'],
    'danger'  => ['class' => 'alert-danger',  'icon' => 'bi-exclamation-triangle-fill'],
    'warning' => ['class' => 'alert-warning', 'icon' => 'bi-exclamation-circle-fill'],
    'info'    => ['class' => 'alert-info',    'icon' => 'bi-info-circle-fill']
];

foreach ($alertTypes as $type => $meta):
    if (has_flash($type)):
        $msg = get_flash($type);
?>
<div class="alert <?= $meta['class'] ?> alert-dismissible fade show d-flex align-items-center mb-4 shadow-sm" role="alert" style="border-radius: 8px;">
    <i class="bi <?= $meta['icon'] ?> fs-5 me-2"></i>
    <div class="flex-grow-1">
        <?= e($msg) ?>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php
    endif;
endforeach;
?>
