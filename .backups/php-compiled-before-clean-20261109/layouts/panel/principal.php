<?php ($userPreferences = (array) (auth()->user()?->preferencias ?? [])); ?>
<?php ($panelUser = auth()->user()); ?>
<!doctype html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
        <title><?php echo $__env->yieldContent('title', 'Panel CELL SAC'); ?></title>
        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/layouts/panel/principal.css', 'resources/js/layouts/panel/principal.js']); ?>
        <link rel="stylesheet" href="<?php echo e(asset('assets/vendor/bootstrap-icons/bootstrap-icons.min.css')); ?>">
        <?php echo $__env->yieldPushContent('styles'); ?>
    </head>
    <body class="admin-body admin-theme <?php echo $__env->yieldContent('body_class'); ?>">
        <div class="admin-layout admin-app" data-admin-app data-user-compact-menu="<?php echo e(array_key_exists('menu_compacto', $userPreferences) ? ($userPreferences['menu_compacto'] ? '1' : '0') : ''); ?>">
            <?php echo $__env->make('layouts.panel.menu-dinamico', ['links' => $sidebarLinks ?? []], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <div class="admin-shell admin-content-shell">
                <?php echo $__env->make('layouts.panel.encabezado', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <main class="admin-main">
                    <?php if(session('status')): ?><div class="admin-alert admin-alert--success" role="status"><?php echo e(session('status')); ?></div><?php endif; ?>
                    <?php if($errors->any()): ?><div class="admin-alert admin-alert--error" role="alert"><?php echo e($errors->first()); ?></div><?php endif; ?>
                    <?php echo $__env->yieldContent('content'); ?>
                </main>
            </div>
        </div>
        <div class="sidebar-overlay admin-sidebar-backdrop" data-sidebar-close data-admin-sidebar-close></div>
        <?php echo $__env->make('inteligente.asistente.flotante', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <?php echo $__env->yieldPushContent('scripts'); ?>
    </body>
</html>
