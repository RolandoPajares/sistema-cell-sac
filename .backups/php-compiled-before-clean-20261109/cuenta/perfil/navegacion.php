<?php ($profileHomeRoute = auth()->user()?->can('gestion.seguridad.consultar') ? 'management.account' : 'account.profile.show'); ?>
<nav class="profile-account-links" aria-label="Secciones de mi cuenta">
    <a href="<?php echo e(route($profileHomeRoute)); ?>">Mi perfil</a>
    <a href="<?php echo e(route('management.account.security')); ?>">Seguridad</a>
    <a href="<?php echo e(route('management.account.sessions')); ?>">Sesiones activas</a>
    <a href="<?php echo e(route('management.account.preferences')); ?>">Preferencias</a>
    <a href="<?php echo e(route('management.account.notifications')); ?>">Notificaciones</a>
    <a href="<?php echo e(route('management.account.activity')); ?>">Actividad reciente</a>
</nav>
