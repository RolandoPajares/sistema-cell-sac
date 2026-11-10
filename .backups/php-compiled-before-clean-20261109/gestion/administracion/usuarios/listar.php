<?php $__env->startSection('title', 'Usuarios y roles'); ?>
<?php $__env->startSection('body_class', 'modulo-usuarios'); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $roleLabels = $roleLabels ?? [];
        $initialsFor = static fn (string $name): string => mb_strtoupper(mb_substr(trim($name), 0, 1));
        $formatRoles = static fn ($account): string => $account->rolesVigentes->concat($account->rolesPorSedeVigentes)->pluck('nombre')->unique()->join(', ') ?: 'Sin rol vigente';
        $hasFilters = ($filters['buscar'] ?? null) || ($filters['rol'] ?? null) || ($filters['estado'] ?? null) || ($filters['ciudad'] ?? null) || ($filters['fecha'] ?? null);
    ?>

    <section class="usuarios-page" data-usuarios-page>
        <header class="usuarios-hero">
            <div>
                <span class="usuarios-eyebrow">Gestión de usuarios</span>
                <h1>Usuarios y roles <i class="bi bi-people-fill" aria-hidden="true"></i></h1>
                <p>Administra las cuentas de acceso al sistema y sus permisos.</p>
            </div>
            <div class="usuarios-hero-actions"><button class="usuarios-primary-button" type="button" data-open-user-create><i class="bi bi-person-plus" aria-hidden="true"></i> Nuevo usuario</button><details class="usuarios-export usuarios-export--hero"><summary><i class="bi bi-download" aria-hidden="true"></i> Exportar</summary><div class="usuarios-export-menu"><a href="<?php echo e(route('management.users.export', array_merge(request()->query(), ['format' => 'pdf']))); ?>">PDF (.pdf)</a><a href="<?php echo e(route('management.users.export', array_merge(request()->query(), ['format' => 'csv']))); ?>">CSV (.csv)</a><a href="<?php echo e(route('management.users.export', array_merge(request()->query(), ['format' => 'excel']))); ?>">Excel (.xlsx)</a></div></details></div>
        </header>

        <section class="usuarios-kpi-grid" aria-label="Resumen de usuarios">
            <article class="usuarios-kpi usuarios-kpi--blue"><span class="usuarios-kpi-icon"><i class="bi bi-people" aria-hidden="true"></i></span><div><small>Total de usuarios</small><strong><?php echo e(number_format($totalUsers)); ?></strong><span><i class="bi bi-arrow-up" aria-hidden="true"></i> +<?php echo e(number_format($currentMonthUsers)); ?> este mes</span></div></article>
            <article class="usuarios-kpi usuarios-kpi--green"><span class="usuarios-kpi-icon"><i class="bi bi-shield-check" aria-hidden="true"></i></span><div><small>Usuarios activos</small><strong><?php echo e($accountStatusAvailable ? number_format($activeUsers) : '—'); ?></strong><span><?php echo e($accountStatusAvailable && $totalUsers > 0 ? number_format(($activeUsers / $totalUsers) * 100, 0).'%' : 'Control de estado pendiente de migración'); ?></span></div></article>
            <article class="usuarios-kpi usuarios-kpi--purple"><span class="usuarios-kpi-icon"><i class="bi bi-person-lock" aria-hidden="true"></i></span><div><small>Usuarios inactivos</small><strong><?php echo e($accountStatusAvailable ? number_format($inactiveUsers) : '—'); ?></strong><span><?php echo e($accountStatusAvailable && $totalUsers > 0 ? number_format(($inactiveUsers / $totalUsers) * 100, 0).'%' : 'Control de estado pendiente de migración'); ?></span></div></article>
            <article class="usuarios-kpi usuarios-kpi--cyan"><span class="usuarios-kpi-icon"><i class="bi bi-people-fill" aria-hidden="true"></i></span><div><small>Roles configurados</small><strong><?php echo e(number_format($rolesConfigured)); ?></strong><span>Roles configurables activos</span></div></article>
        </section>

        <form class="usuarios-filters" method="get" action="<?php echo e(route('management.users')); ?>">
            <label class="usuarios-search"><i class="bi bi-search" aria-hidden="true"></i><span class="visually-hidden">Buscar usuarios</span><input type="search" name="buscar" value="<?php echo e($filters['buscar'] ?? ''); ?>" placeholder="Buscar por nombre, correo, rol o razón social..."></label>
            <button class="usuarios-filter-button" type="submit"><i class="bi bi-funnel" aria-hidden="true"></i> Filtros</button>
            <div class="usuarios-filter-row">
                <label><span>Rol</span><select name="rol"><option value="">Todos</option><?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($role); ?>" <?php if(($filters['rol'] ?? null) === $role): echo 'selected'; endif; ?>><?php echo e($roleLabels[$role] ?? $role); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label>
                <label><span>Estado</span><select name="estado"><option value="">Todos</option><option value="activo" <?php if(($filters['estado'] ?? null) === 'activo'): echo 'selected'; endif; ?>>Activos</option><option value="inactivo" <?php if(($filters['estado'] ?? null) === 'inactivo'): echo 'selected'; endif; ?>>Inactivos</option></select></label>
                <label><span>Ciudad</span><select name="ciudad"><option value="">Todas</option><?php $__currentLoopData = $cities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $city): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($city); ?>" <?php if(($filters['ciudad'] ?? null) === $city): echo 'selected'; endif; ?>><?php echo e($city); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label>
                <label><span>Fecha de registro</span><select name="fecha"><option value="">Todos</option><option value="hoy" <?php if(($filters['fecha'] ?? null) === 'hoy'): echo 'selected'; endif; ?>>Hoy</option><option value="ultimos_30" <?php if(($filters['fecha'] ?? null) === 'ultimos_30'): echo 'selected'; endif; ?>>Últimos 30 días</option><option value="ultimos_90" <?php if(($filters['fecha'] ?? null) === 'ultimos_90'): echo 'selected'; endif; ?>>Últimos 90 días</option></select></label>
                <?php if($hasFilters): ?><a class="usuarios-clear-button" href="<?php echo e(route('management.users')); ?>"><i class="bi bi-arrow-clockwise" aria-hidden="true"></i> Limpiar filtros</a><?php else: ?><button class="usuarios-clear-button" type="submit"><i class="bi bi-arrow-clockwise" aria-hidden="true"></i> Aplicar filtros</button><?php endif; ?>
            </div>
        </form>

        <section class="usuarios-list-card">
            <header class="usuarios-card-header"><div><h2>Usuarios registrados (<?php echo e(number_format($users->total())); ?>)</h2><p>Mostrando <?php echo e($users->firstItem() ?? 0); ?> a <?php echo e($users->lastItem() ?? 0); ?> usuarios</p></div><div class="usuarios-card-tools"><span>Ordenar por: <b>Fecha de registro</b></span></div></header>
            <div class="usuarios-table-wrap">
                <table class="usuarios-table">
                    <thead><tr><th>#</th><th>Usuario</th><th>Correo</th><th>Rol</th><th>Ciudad</th><th>Estado</th><th>Registro</th><th>Acciones</th></tr></thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="usuarios-index"><?php echo e(($users->firstItem() ?? 1) + $loop->index); ?>.</td>
                                <td><div class="usuarios-identity"><span class="usuarios-avatar usuarios-avatar--<?php echo e($loop->index % 5); ?>"><?php echo e($initialsFor($user->nombre)); ?></span><span><strong><?php echo e($user->nombre); ?></strong><small><?php echo e($formatRoles($user)); ?></small></span></div></td>
                                <td class="usuarios-email"><?php echo e($user->correo); ?></td>
                                <td><span class="usuarios-role usuarios-role--<?php echo e($loop->index % 7); ?>"><?php echo e($formatRoles($user)); ?></span></td>
                                <td><?php echo e($user->customer?->ciudad ?: '—'); ?></td>
                                <td><?php if($accountStatusAvailable): ?><span class="usuarios-status usuarios-status--<?php echo e($user->activo ? 'active' : 'inactive'); ?>"><i class="bi bi-circle-fill" aria-hidden="true"></i><?php echo e($user->activo ? 'Activo' : 'Inactivo'); ?></span><?php else: ?><span class="usuarios-status">Sin control de estado</span><?php endif; ?></td>
                                <td class="usuarios-date"><?php echo e($user->creado_en?->format('d/m/Y')); ?><small><?php echo e($user->creado_en?->format('H:i')); ?></small></td>
                                <td class="usuarios-actions"><button class="usuarios-action" type="button" data-open-user-view="<?php echo e($user->id); ?>" title="Ver usuario" aria-label="Ver <?php echo e($user->nombre); ?>"><i class="bi bi-eye" aria-hidden="true"></i></button><button class="usuarios-action" type="button" data-open-user-edit="<?php echo e($user->id); ?>" title="Editar usuario" aria-label="Editar <?php echo e($user->nombre); ?>"><i class="bi bi-pencil" aria-hidden="true"></i></button><?php if($accountStatusAvailable): ?><form method="post" action="<?php echo e(route('management.users.activate', $user)); ?>" data-confirm="¿<?php echo e($user->activo ? 'Desactivar' : 'Reactivar'); ?> esta cuenta?"><?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?><input type="hidden" name="activo" value="<?php echo e($user->activo ? 0 : 1); ?>"><button class="usuarios-action" type="submit" title="<?php echo e($user->activo ? 'Desactivar' : 'Reactivar'); ?>" aria-label="<?php echo e($user->activo ? 'Desactivar' : 'Reactivar'); ?> <?php echo e($user->nombre); ?>"><i class="bi bi-<?php echo e($user->activo ? 'person-lock' : 'person-check'); ?>" aria-hidden="true"></i></button></form><?php endif; ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="8" class="usuarios-empty"><i class="bi bi-people" aria-hidden="true"></i><strong>No hay usuarios que coincidan.</strong><span>Prueba con otros filtros o registra un nuevo usuario.</span></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <footer class="usuarios-pagination"><span>Mostrando <?php echo e($users->firstItem() ?? 0); ?> a <?php echo e($users->lastItem() ?? 0); ?> de <?php echo e(number_format($users->total())); ?> usuarios</span><div class="usuarios-pagination-controls"><?php echo e($users->onEachSide(1)->links()); ?><form method="get" action="<?php echo e(route('management.users')); ?>" class="usuarios-per-page"><?php $__currentLoopData = request()->except(['page', 'por_pagina']); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <?php if(is_scalar($value)): ?><input type="hidden" name="<?php echo e($key); ?>" value="<?php echo e($value); ?>"><?php endif; ?> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><select name="por_pagina" aria-label="Usuarios por página" onchange="this.form.submit()"><?php $__currentLoopData = [10, 20, 50]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($option); ?>" <?php if(($filters['por_pagina'] ?? 10) === $option): echo 'selected'; endif; ?>><?php echo e($option); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select><span>por página</span></form></div></footer>
        </section>

        <div class="usuarios-bottom-grid">
            <section class="usuarios-bottom-card"><header class="usuarios-card-header"><div><h2>Distribución por roles</h2><p>Usuarios agrupados por permisos.</p></div></header><div class="usuarios-distribution"><div class="usuarios-donut"><strong><?php echo e(number_format($totalUsers)); ?></strong><span>Usuarios</span></div><div class="usuarios-role-list"><?php $__empty_1 = true; $__currentLoopData = $roleDistribution; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><div><span><i class="usuarios-dot usuarios-dot--<?php echo e($loop->index % 7); ?>" aria-hidden="true"></i><?php echo e($roleLabels[$item->rol] ?? $item->rol); ?></span><b><?php echo e($item->total); ?></b><small><?php echo e($totalUsers > 0 ? number_format(($item->total / $totalUsers) * 100, 1) : 0); ?>%</small></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><span class="usuarios-muted">Sin distribución disponible.</span><?php endif; ?></div></div></section>
            <section class="usuarios-bottom-card"><header class="usuarios-card-header"><div><h2>Actividad reciente</h2><p>Últimos usuarios registrados.</p></div><a href="<?php echo e(route('management.audit')); ?>">Ver todas</a></header><div class="usuarios-activity-list"><?php $__empty_1 = true; $__currentLoopData = $recentUsers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $recentUser): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><div class="usuarios-activity-row"><span class="usuarios-activity-icon usuarios-activity-icon--<?php echo e($loop->index % 2 ? 'purple' : 'blue'); ?>"><i class="bi bi-person-plus" aria-hidden="true"></i></span><span><strong>Nuevo usuario creado</strong><small><?php echo e($recentUser->nombre); ?> · <?php echo e($formatRoles($recentUser)); ?></small></span><time><?php echo e($recentUser->creado_en?->diffForHumans()); ?></time></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><div class="usuarios-secondary-empty"><i class="bi bi-clock-history" aria-hidden="true"></i><strong>Sin actividad reciente</strong><span>Los nuevos registros aparecerán aquí.</span></div><?php endif; ?></div></section>
        </div>
    </section>

    <dialog class="usuarios-dialog" id="nuevo-usuario" aria-labelledby="usuario-crear-titulo">
            <div class="usuarios-dialog-card"><header class="usuarios-dialog-header"><div><span class="usuarios-dialog-icon"><i class="bi bi-person-plus" aria-hidden="true"></i></span><span><small>Gestión de usuarios</small><h2 id="usuario-crear-titulo">Registrar nuevo usuario</h2><p>Crea una cuenta y asígnale un rol configurable.</p></span></div><button type="button" class="usuarios-dialog-close" data-close-user-dialog aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header><div class="usuarios-dialog-body"><?php echo $__env->make('gestion.administracion.usuarios.crear', ['roles' => $roles, 'roleLabels' => $roleLabels, 'roleScopes' => $roleScopes, 'sites' => $sites], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div></div>
    </dialog>

    <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <dialog class="usuarios-dialog usuarios-view-dialog" id="ver-usuario-<?php echo e($user->id); ?>" aria-labelledby="usuario-detalle-titulo-<?php echo e($user->id); ?>">
            <div class="usuarios-dialog-card"><header class="usuarios-dialog-header"><div><span class="usuarios-dialog-icon"><i class="bi bi-person-vcard" aria-hidden="true"></i></span><span><small><?php echo e($formatRoles($user)); ?></small><h2 id="usuario-detalle-titulo-<?php echo e($user->id); ?>"><?php echo e($user->nombre); ?></h2><p><?php echo e($user->correo); ?></p></span></div><button type="button" class="usuarios-dialog-close" data-close-user-dialog aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header><div class="usuarios-view-body"><div class="usuarios-view-identity"><span class="usuarios-avatar usuarios-avatar--<?php echo e($loop->index % 5); ?>"><?php echo e($initialsFor($user->nombre)); ?></span><div><h3><?php echo e($user->nombre); ?></h3><p>Cuenta registrada en CELL SAC.</p></div></div><div class="usuarios-view-grid"><div><small>Correo electrónico</small><strong><?php echo e($user->correo); ?></strong></div><div><small>Rol asignado</small><strong><?php echo e($formatRoles($user)); ?></strong></div><div><small>Ciudad</small><strong><?php echo e($user->customer?->ciudad ?: 'No registrada'); ?></strong></div><div><small>Estado</small><span class="usuarios-status usuarios-status--<?php echo e($user->activo ? 'active' : 'inactive'); ?>"><i class="bi bi-circle-fill" aria-hidden="true"></i><?php echo e($user->activo ? 'Activo' : 'Inactivo'); ?></span></div><div><small>Fecha de registro</small><strong><?php echo e($user->creado_en?->format('d/m/Y H:i')); ?></strong></div><div><small>Documento</small><strong><?php echo e($user->customer?->documento ?: 'No registrado'); ?></strong></div></div></div><footer class="usuarios-dialog-footer"><button class="usuarios-cancel-button" type="button" data-close-user-dialog>Cerrar</button></footer></div>
        </dialog>
        <dialog class="usuarios-dialog" id="editar-usuario-<?php echo e($user->id); ?>" aria-labelledby="usuario-editar-titulo-<?php echo e($user->id); ?>">
            <div class="usuarios-dialog-card"><header class="usuarios-dialog-header"><div><span class="usuarios-dialog-icon"><i class="bi bi-pencil" aria-hidden="true"></i></span><span><small>Gestión de usuarios</small><h2 id="usuario-editar-titulo-<?php echo e($user->id); ?>">Editar usuario</h2><p>Actualiza datos sin cambiar las asignaciones RBAC.</p></span></div><button type="button" class="usuarios-dialog-close" data-close-user-dialog aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header><form method="POST" action="<?php echo e(route('management.users.update', $user)); ?>" class="usuarios-dialog-body"><?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?><label>Nombre completo<input name="nombre" value="<?php echo e($user->nombre); ?>" maxlength="120" required></label><label>Correo electrónico<input type="email" name="correo" value="<?php echo e($user->correo); ?>" maxlength="160" required></label><label>Nueva contraseña <small>(opcional)</small><input type="password" name="password" minlength="12" autocomplete="new-password"></label><label>Confirmar contraseña<input type="password" name="password_confirmation" minlength="12" autocomplete="new-password"></label><footer class="usuarios-dialog-footer"><button class="usuarios-cancel-button" type="button" data-close-user-dialog>Cancelar</button><button class="usuarios-primary-button" type="submit">Guardar cambios</button></footer></form></div>
        </dialog>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        (() => {
            const page = document.querySelector('[data-usuarios-page]');
            if (!page) {
                return;
            }

            const openDialog = (dialog) => {
                if (dialog && !dialog.open) {
                    dialog.showModal();
                }
            };

            page.querySelectorAll('[data-open-user-create]').forEach((button) => button.addEventListener('click', () => openDialog(document.querySelector('#nuevo-usuario'))));
            page.querySelectorAll('[data-open-user-view]').forEach((button) => button.addEventListener('click', () => openDialog(document.querySelector(`#ver-usuario-${button.dataset.openUserView}`))));
            page.querySelectorAll('[data-open-user-edit]').forEach((button) => button.addEventListener('click', () => openDialog(document.querySelector(`#editar-usuario-${button.dataset.openUserEdit}`))));
            document.querySelectorAll('[data-close-user-dialog]').forEach((button) => button.addEventListener('click', () => button.closest('dialog')?.close()));
            document.querySelectorAll('.usuarios-dialog').forEach((dialog) => dialog.addEventListener('click', (event) => { if (event.target === dialog) { dialog.close(); } }));

            const form = document.querySelector('[data-user-form]');
            const role = form?.querySelector('[data-user-role]');
            const documentField = form?.querySelector('[data-user-document]');
            const documentType = form?.querySelector('[data-user-document-type]');
            const documentLabel = form?.querySelector('[data-user-document-label]');
            const rucFields = form?.querySelectorAll('[data-user-ruc-field]');
            const roleNote = form?.querySelector('[data-user-role-note]');
            const customerSection = form?.querySelector('[data-user-customer-section]');
            const customerFields = form?.querySelectorAll('[data-user-customer-field]');
            const customerRequired = form?.querySelector('[data-user-customer-required]');
            const siteField = form?.querySelector('[data-user-site-field]');
            const siteSelect = form?.querySelector('[data-user-site]');

            const updateCustomerFields = () => {
                const isCustomer = role?.value.startsWith('cliente_');
                if (documentField) {
                    documentField.required = Boolean(isCustomer);
                }
                if (roleNote) {
                    roleNote.hidden = !isCustomer;
                }
                if (customerSection) {
                    customerSection.hidden = !isCustomer;
                }
                customerFields?.forEach((field) => { field.hidden = !isCustomer; });
                if (customerRequired) {
                    customerRequired.hidden = !isCustomer;
                }
                const isRuc = documentType?.value === 'ruc';
                rucFields?.forEach((field) => { field.hidden = !isRuc; const input = field.querySelector('input'); if (input) input.required = isRuc; });
                if (documentType) {
                    documentType.required = isCustomer;
                }
                if (documentField) {
                    documentField.maxLength = isRuc ? 11 : 8;
                    documentField.placeholder = isRuc ? '11 dígitos del RUC' : '8 dígitos del DNI';
                    documentField.pattern = isRuc ? '(10|15|17|20)[0-9]{9}' : '[0-9]{8}';
                    documentField.inputMode = 'numeric';
                }
                if (documentLabel) {
                    documentLabel.firstChild.textContent = isRuc ? 'RUC ' : 'DNI ';
                }
                const needsSite = role?.selectedOptions[0]?.dataset.scope === 'sede';
                if (siteField) {
                    siteField.hidden = !needsSite;
                }
                if (siteSelect) {
                    siteSelect.required = needsSite;
                }
            };

            role?.addEventListener('change', updateCustomerFields);
            documentType?.addEventListener('change', updateCustomerFields);
            updateCustomerFields();

            <?php if($errors->any()): ?>
                openDialog(document.querySelector('#nuevo-usuario'));
            <?php endif; ?>
        })();
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.panel.principal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>