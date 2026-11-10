<?php if(session('status')): ?><div class="container flash-success" role="status"><?php echo e(session('status')); ?></div><?php endif; ?>
<?php if($errors->any()): ?><div class="container flash-error" role="alert"><?php echo e($errors->first()); ?></div><?php endif; ?>
