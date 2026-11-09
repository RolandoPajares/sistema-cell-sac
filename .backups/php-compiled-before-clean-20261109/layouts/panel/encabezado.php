<?php
    $currentUser = auth()->user();
    $isAdministrator = $currentUser?->can('gestion.seguridad.consultar') ?? false;
    $adminBrandRoute = $isAdministrator ? 'management.dashboard' : 'dashboard';
    $adminAccountRoute = $isAdministrator ? 'management.account' : 'account.profile.show';
?>

<header class="admin-topbar">
    <a class="admin-topbar-brand" href="<?php echo e(route($adminBrandRoute)); ?>" title="MD Technology Digital Cell">
        <img class="admin-topbar-logo" src="<?php echo e(asset('assets/cell-legacy/img/marca/logoadmin.png')); ?>" alt="TECHNOLOGY CELL">
    </a>
    <button class="icon-btn admin-menu-toggle" type="button" aria-label="Abrir menú del panel" aria-controls="adminSidebar" aria-expanded="false" data-admin-sidebar-toggle>
        <i class="bi bi-list"></i>
    </button>
    <label class="admin-global-search">
        <i class="bi bi-search" aria-hidden="true"></i>
        <input type="search" data-admin-global-search placeholder="Buscar productos, clientes, campañas..." aria-label="Buscar productos, clientes o campañas">
    </label>
    <button class="admin-ai-button" type="button" data-admin-assistant-toggle aria-controls="adminAssistantPanel" aria-expanded="false"><i class="bi bi-stars" aria-hidden="true"></i><span>Asistente IA</span></button>
    <?php echo $__env->make('layouts.panel.notificaciones', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <button class="admin-topbar-icon" type="button" aria-label="Ayuda"><i class="bi bi-question-circle" aria-hidden="true"></i></button>
    <details class="admin-user-menu">
        <summary class="admin-user" aria-label="Abrir menú de usuario">
            <?php if($currentUser?->foto_perfil): ?>
                <img class="admin-avatar admin-avatar--photo" src="<?php echo e(\Illuminate\Support\Facades\Storage::disk('public')->url($currentUser->foto_perfil)); ?>" alt="">
            <?php else: ?>
                <span class="admin-avatar"><?php echo e(strtoupper(substr($currentUser->nombre, 0, 1))); ?></span>
            <?php endif; ?>
            <span class="admin-user-copy"><b><?php echo e($currentUser->nombre); ?></b><small>Cuenta activa</small></span>
            <i class="bi bi-chevron-down admin-user-chevron" aria-hidden="true"></i>
        </summary>
        <div class="admin-user-menu-panel">
            <a href="<?php echo e(route($adminAccountRoute)); ?>"><i class="bi bi-person-circle" aria-hidden="true"></i><span>Mi perfil</span></a>
            <?php if($isAdministrator): ?>
                <a href="<?php echo e(route('management.account.preferences')); ?>"><i class="bi bi-sliders" aria-hidden="true"></i><span>Preferencias</span></a>
                <a href="<?php echo e(route('management.account.security')); ?>"><i class="bi bi-shield-lock" aria-hidden="true"></i><span>Seguridad de la cuenta</span></a>
                <a href="<?php echo e(route('management.account.sessions')); ?>"><i class="bi bi-pc-display-horizontal" aria-hidden="true"></i><span>Sesiones y dispositivos</span></a>
                <a href="<?php echo e(route('management.account.notifications')); ?>"><i class="bi bi-bell" aria-hidden="true"></i><span>Mis notificaciones</span></a>
                <a href="<?php echo e(route('management.account.activity')); ?>"><i class="bi bi-clock-history" aria-hidden="true"></i><span>Mi actividad reciente</span></a>
            <?php endif; ?>
            <form method="post" action="<?php echo e(route('logout')); ?>">
                <?php echo csrf_field(); ?>
                <button type="submit"><i class="bi bi-box-arrow-right" aria-hidden="true"></i><span>Cerrar sesión</span></button>
            </form>
        </div>
    </details>
</header>
