<?php

use Illuminate\Support\Str;

    $currentUser = auth()->user();
$hasManagementAccess = $currentUser?->can('gestion.seguridad.consultar') ?? false;
$brandRoute = $hasManagementAccess ? 'management.dashboard' : 'dashboard';
$accountRoute = $hasManagementAccess ? 'management.account' : 'account.profile.show';
?>

<aside class="admin-sidebar" id="adminSidebar" aria-label="Navegación del panel">
    <div class="admin-sidebar-brand">
        <a href="<?php echo e(route($brandRoute)); ?>" title="MD Technology Digital Cell">
            <span class="admin-brand-mark"><i class="bi bi-phone" aria-hidden="true"></i></span>
            <span class="admin-brand-copy"><strong>TECHNOLOGY <b>CELL</b></strong><small>MD Technology Digital Cell S.A.C.</small></span>
        </a>
    </div>

    <nav class="admin-sidebar-nav">
        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/paneles/dashboard.css']); ?>
        <div class="admin-menu-desplegable" data-admin-menu-desplegable><div class="admin-menu-desplegable__groups">
            <?php $__currentLoopData = $links['grupos'] ?? [];
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $group) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop(); ?>
                <?php ($hasActiveItem = collect($group['items'])->contains(fn (array $item): bool => $item['active'] ?? false)); ?>
                <?php ($submenuId = 'admin-menu-submenu-'.Str::slug($group['id'])); ?>
                <?php if ($group['direct'] ?? false) { ?>
                    <?php $__currentLoopData = $group['items'];
                    $__env->addLoop($__currentLoopData);
                    foreach ($__currentLoopData as $item) {
                        $__env->incrementLoopIndices();
                        $loop = $__env->getLastLoop(); ?>
                        <?php if ($item['enabled'] && $item['route']) { ?>
                            <a class="admin-menu-desplegable__dashboard <?php echo e($item['active'] ? 'is-active' : ''); ?>" href="<?php echo e(route($item['route'])); ?>" <?php if ($item['active']) { ?> aria-current="page" <?php } ?>><i class="bi <?php echo e($item['icon']); ?>" aria-hidden="true"></i><span class="admin-menu-desplegable__label"><?php echo e($item['label']); ?></span></a>
                        <?php } ?>
                    <?php } $__env->popLoop();
                    $loop = $__env->getLastLoop(); ?>
                <?php } elseif ($group['items'] !== []) { ?>
                    <section class="admin-menu-desplegable__group <?php echo e($hasActiveItem ? 'is-active is-open' : ''); ?>" data-admin-menu-group>
                        <button class="admin-menu-desplegable__toggle" type="button" aria-expanded="<?php echo e($hasActiveItem ? 'true' : 'false'); ?>" aria-controls="<?php echo e($submenuId); ?>" data-admin-menu-toggle><span class="admin-menu-desplegable__toggle-main"><i class="bi <?php echo e($group['icon']); ?>" aria-hidden="true"></i><span class="admin-menu-desplegable__label"><?php echo e($group['label']); ?></span></span><i class="bi bi-chevron-down admin-menu-desplegable__chevron" aria-hidden="true"></i></button>
                        <div class="admin-menu-desplegable__submenu" id="<?php echo e($submenuId); ?>" data-admin-menu-submenu <?php if (! $hasActiveItem) { ?> hidden <?php } ?>>
                            <?php $__currentLoopData = $group['items'];
                    $__env->addLoop($__currentLoopData);
                    foreach ($__currentLoopData as $item) {
                        $__env->incrementLoopIndices();
                        $loop = $__env->getLastLoop(); ?>
                                <?php if ($item['enabled'] && $item['route']) { ?>
                                    <a class="admin-menu-desplegable__item <?php echo e($item['active'] ? 'is-active' : ''); ?>" href="<?php echo e(route($item['route'])); ?>" title="<?php echo e($item['label']); ?>" <?php if ($item['active']) { ?> aria-current="page" <?php } ?>><i class="bi <?php echo e($item['icon']); ?>" aria-hidden="true"></i><span class="admin-menu-desplegable__label"><?php echo e($item['label']); ?></span></a>
                                <?php } ?>
                            <?php } $__env->popLoop();
                    $loop = $__env->getLastLoop(); ?>
                        </div>
                    </section>
                <?php } ?>
            <?php } $__env->popLoop();
$loop = $__env->getLastLoop(); ?>
        </div></div>
    </nav>

    <div class="admin-sidebar-footer">
        <a class="<?php echo e(request()->routeIs($accountRoute) ? 'is-active' : ''); ?>" href="<?php echo e(route($accountRoute)); ?>" title="Mi perfil"><i class="bi bi-gear" aria-hidden="true"></i><span>Mi perfil</span></a>
        <form method="post" action="<?php echo e(route('logout')); ?>"><?php echo csrf_field(); ?><button type="submit" title="Cerrar sesión"><i class="bi bi-box-arrow-right" aria-hidden="true"></i><span>Cerrar sesión</span></button></form>
    </div>
</aside>
