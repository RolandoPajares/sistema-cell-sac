<?php $__env->startSection('title', 'Roles y permisos'); ?>
<?php $__env->startSection('body_class', 'modulo-seguridad-rbac'); ?>
<?php $__env->startSection('content'); ?>
<section class="tc-rbac-page" data-rbac-page>
    <header class="tc-rbac-hero"><div><span class="tc-rbac-eyebrow">Seguridad y delegación</span><h1>Roles y permisos</h1><p>Crea roles, edítalos y administra sus privilegios desde la matriz.</p></div><button class="tc-rbac-primary" type="button" data-open-dialog="nuevo-rol"><i class="bi bi-plus-lg"></i> Nuevo rol</button></header>
    <?php if (! ($ready)) { ?>
        <div class="tc-rbac-alert" role="alert">El esquema RBAC no está disponible.</div>
    <?php } else { ?>
        <nav class="tc-rbac-tabs" aria-label="Administración de seguridad"><a href="<?php echo e(route('management.users')); ?>">Gestión de usuarios</a><a class="is-active" href="<?php echo e(route('management.roles_permissions')); ?>">Roles y permisos</a><a href="<?php echo e(route('management.permissions_matrix')); ?>">Matriz de privilegios</a><a href="<?php echo e(route('management.user_assignments')); ?>">Asignaciones globales y por sede</a><a href="<?php echo e(route('management.menus')); ?>">Menús y submenús dinámicos</a><a href="<?php echo e(route('management.modules')); ?>">Módulos y pantallas</a></nav>
        <form class="tc-rbac-toolbar" method="get" action="<?php echo e(route('management.roles_permissions')); ?>"><label class="tc-rbac-search"><i class="bi bi-search"></i><input type="search" name="buscar" value="<?php echo e($search); ?>" placeholder="Buscar por rol o descripción"></label><label class="tc-rbac-select"><select name="estado"><option value="">Todos</option><option value="activo" <?php if ($status === 'activo') {
            echo 'selected';
        } ?>>Activos</option><option value="inactivo" <?php if ($status === 'inactivo') {
            echo 'selected';
        } ?>>Inactivos</option></select></label><button class="tc-rbac-filter-button" type="submit">Filtrar</button></form>
        <section class="tc-rbac-card tc-rbac-role-table"><div class="tc-rbac-table-wrap"><table><thead><tr><th>Rol</th><th>Descripción</th><th>Usuarios</th><th>Estado</th><th>Acciones</th></tr></thead><tbody>
            <?php $__empty_1 = true;
        $__currentLoopData = $roles;
        $__env->addLoop($__currentLoopData);
        foreach ($__currentLoopData as $role) {
            $__env->incrementLoopIndices();
            $loop = $__env->getLastLoop();
            $__empty_1 = false; ?>
                <tr><td><strong><?php echo e($role->nombre); ?></strong><small><?php echo e($role->codigo); ?></small></td><td><?php echo e($role->descripcion ?: 'Sin descripción registrada.'); ?></td><td><?php echo e($role->usuarios_count); ?></td><td><?php echo e($role->activo ? 'Activo' : 'Inactivo'); ?></td><td class="tc-rbac-actions"><button class="tc-rbac-action" type="button" data-open-dialog="ver-rol-<?php echo e($role->id); ?>">Ver</button><?php if (! $role->es_sistema) { ?><button class="tc-rbac-action tc-rbac-action--primary" type="button" data-open-dialog="editar-rol-<?php echo e($role->id); ?>">Editar</button><?php } ?><a class="tc-rbac-action tc-rbac-action--primary" href="<?php echo e(route('management.permissions_matrix', ['rol' => $role->id])); ?>">Matriz</a></td></tr>
            <?php } $__env->popLoop();
        $loop = $__env->getLastLoop();
        if ($__empty_1) { ?>
                <tr><td colspan="5">No hay roles que coincidan con los filtros.</td></tr>
            <?php } ?>
        </tbody></table></div></section>
    <?php } ?>
</section>
<?php if ($ready) { ?>
    <dialog class="tc-rbac-dialog" id="nuevo-rol"><div class="tc-rbac-dialog-card"><header><h2>Nuevo rol</h2><button type="button" data-close-dialog aria-label="Cerrar">×</button></header><form method="post" action="<?php echo e(route('management.roles_permissions.store')); ?>" class="tc-rbac-dialog-body"><?php echo csrf_field(); ?><label>Código interno<input name="codigo" pattern="[a-z][a-z0-9_]*" maxlength="40" required></label><label>Nombre<input name="nombre" maxlength="120" required></label><label>Descripción<textarea name="descripcion" maxlength="255"></textarea></label><label>Alcance<select name="alcance_permitido" required><option value="sede">Sede</option><option value="propio">Propio</option><option value="empresa">Empresa</option></select></label><footer><button type="button" data-close-dialog>Cancelar</button><button class="tc-rbac-primary" type="submit">Guardar rol</button></footer></form></div></dialog>
<?php } ?>
<?php $__currentLoopData = $roles;
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $role) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop(); ?>
    <dialog class="tc-rbac-dialog" id="ver-rol-<?php echo e($role->id); ?>"><div class="tc-rbac-dialog-card"><header><h2><?php echo e($role->nombre); ?></h2><button type="button" data-close-dialog aria-label="Cerrar">×</button></header><div class="tc-rbac-dialog-body"><p><strong>Código:</strong> <?php echo e($role->codigo); ?></p><p><strong>Alcance:</strong> <?php echo e(ucfirst($role->alcance_permitido)); ?></p><p><strong>Permisos:</strong> <?php echo e($role->permisos_count); ?></p><p><strong>Usuarios:</strong> <?php echo e($role->usuarios_count); ?></p><button type="button" data-close-dialog>Cerrar</button></div></div></dialog>
    <?php if (! $role->es_sistema) { ?>
        <dialog class="tc-rbac-dialog" id="editar-rol-<?php echo e($role->id); ?>"><div class="tc-rbac-dialog-card"><header><h2>Editar <?php echo e($role->nombre); ?></h2><button type="button" data-close-dialog aria-label="Cerrar">×</button></header><form method="post" action="<?php echo e(route('management.roles_permissions.update', $role)); ?>" class="tc-rbac-dialog-body"><?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?><label>Nombre<input name="nombre" value="<?php echo e($role->nombre); ?>" maxlength="120" required></label><label>Descripción<textarea name="descripcion" maxlength="255"><?php echo e($role->descripcion); ?></textarea></label><label>Alcance<select name="alcance_permitido" required><option value="empresa" <?php if ($role->alcance_permitido === 'empresa') {
            echo 'selected';
        } ?>>Empresa</option><option value="sede" <?php if ($role->alcance_permitido === 'sede') {
            echo 'selected';
        } ?>>Sede</option><option value="propio" <?php if ($role->alcance_permitido === 'propio') {
            echo 'selected';
        } ?>>Propio</option></select></label><footer><button type="button" data-close-dialog>Cancelar</button><button class="tc-rbac-primary" type="submit">Guardar cambios</button></footer></form></div></dialog>
    <?php } ?>
<?php } $__env->popLoop();
$loop = $__env->getLastLoop(); ?>
<?php $__env->stopSection(); ?>
<?php $__env->startPush('scripts'); ?>
<script>
    (() => { const page = document.querySelector('[data-rbac-page]'); if (!page) return; page.querySelectorAll('[data-open-dialog]').forEach((button) => button.addEventListener('click', () => document.getElementById(button.dataset.openDialog)?.showModal())); document.querySelectorAll('[data-close-dialog]').forEach((button) => button.addEventListener('click', () => button.closest('dialog')?.close())); })();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.panel.principal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>