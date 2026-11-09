<?php if ($isAdministrator) { ?>
    <a class="admin-topbar-icon" href="<?php echo e(route('management.account.notifications')); ?>" aria-label="Mis notificaciones"><i class="bi bi-bell" aria-hidden="true"></i></a>
<?php } else { ?>
    <span class="admin-topbar-icon" aria-label="Notificaciones"><i class="bi bi-bell" aria-hidden="true"></i></span>
<?php } ?>
