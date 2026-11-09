<?php
use Illuminate\Support\Carbon;

$__env->startSection('title', 'Personal por sede'); ?>
<?php $__env->startSection('body_class', 'modulo-organizacion'); ?>

<?php $__env->startSection('content'); ?>
<section class="tc-org" data-organizacion="personal">
    <?php echo $__env->make('gestion.organizacion.componentes.cabecera', [
        'titulo' => 'Personal por sede',
        'icono' => 'bi-people-fill',
        'descripcion' => 'Consulta las asignaciones vigentes de usuarios a roles y sedes autorizadas.',
        'acciones' => [
            ['texto' => 'Asignar rol a una sede', 'icono' => 'bi-diagram-3', 'ruta' => route('management.user_assignments')],
            ['texto' => 'Nuevo usuario', 'icono' => 'bi-person-plus', 'primaria' => true, 'ruta' => route('management.users')],
        ],
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php if (session('status')) { ?>
        <div class="tc-org__alert tc-org__alert--success" role="status"><?php echo e(session('status')); ?></div>
    <?php } ?>

    <div class="tc-org__metrics">
        <?php echo $__env->make('gestion.organizacion.componentes.metrica', ['titulo' => 'Usuarios asignados', 'valor' => $resumen['personal_total'], 'icono' => 'bi-people', 'detalle' => 'Registros reales de usuarios por sede'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('gestion.organizacion.componentes.metrica', ['titulo' => 'Asignaciones vigentes', 'valor' => $resumen['asignaciones_vigentes'], 'icono' => 'bi-shield-check', 'tono' => 'verde', 'detalle' => 'Cuenta, rol, sede y vigencia activos'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('gestion.organizacion.componentes.metrica', ['titulo' => 'Sedes con personal', 'valor' => $resumen['sedes_con_personal'], 'icono' => 'bi-buildings', 'tono' => 'violeta', 'detalle' => 'Dentro del alcance autorizado'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('gestion.organizacion.componentes.metrica', ['titulo' => 'Roles asignados', 'valor' => $resumen['roles_vigentes'], 'icono' => 'bi-person-badge', 'detalle' => 'Roles existentes del catálogo RBAC'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>

    <form class="tc-org__filters" method="get" action="<?php echo e(route('management.company_settings.staff')); ?>">
        <div class="tc-org__filter-top">
            <label class="tc-org__search">
                <i class="bi bi-search" aria-hidden="true"></i>
                <input type="search" name="buscar" value="<?php echo e($filtros['buscar'] ?? ''); ?>" placeholder="Buscar usuario, correo, rol o sede" aria-label="Buscar personal por sede">
            </label>
            <button class="tc-org__button tc-org__button--apply" type="submit"><i class="bi bi-funnel" aria-hidden="true"></i> Aplicar filtros</button>
            <a class="tc-org__button" href="<?php echo e(route('management.company_settings.staff')); ?>"><i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i> Limpiar</a>
        </div>
        <div class="tc-org__filter-fields">
            <label><span>Sede autorizada</span><select name="sede_id"><option value="">Todas las sedes autorizadas</option><?php $__currentLoopData = $sedesAutorizadas;
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $sede) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop(); ?><option value="<?php echo e($sede->id); ?>" <?php if ((string) ($filtros['sede_id'] ?? '') === (string) $sede->id) {
        echo 'selected';
    } ?>><?php echo e($sede->nombre); ?> · <?php echo e($sede->codigo); ?></option><?php } $__env->popLoop();
$loop = $__env->getLastLoop(); ?></select></label>
            <label><span>Vigencia</span><select name="estado"><option value="">Todas</option><option value="vigente" <?php if (($filtros['estado'] ?? '') === 'vigente') {
                echo 'selected';
            } ?>>Vigentes</option><option value="no_vigente" <?php if (($filtros['estado'] ?? '') === 'no_vigente') {
                echo 'selected';
            } ?>>No vigentes</option></select></label>
            <label><span>Filas por página</span><select name="por_pagina"><option value="15" <?php if ((int) ($filtros['por_pagina'] ?? 15) === 15) {
                echo 'selected';
            } ?>>15</option><option value="30" <?php if ((int) ($filtros['por_pagina'] ?? 15) === 30) {
                echo 'selected';
            } ?>>30</option><option value="50" <?php if ((int) ($filtros['por_pagina'] ?? 15) === 50) {
                echo 'selected';
            } ?>>50</option></select></label>
        </div>
    </form>

    <article class="tc-org__card tc-org__table-card">
        <div class="tc-org__card-title"><div><h2>Asignaciones de personal (<?php echo e($asignaciones->total()); ?>)</h2><p>Mostrando <?php echo e($asignaciones->count()); ?> asignaciones consultadas en MariaDB.</p></div></div>
        <div class="tc-org__table-wrap"><table><thead><tr><th>#</th><th>Usuario</th><th>Correo</th><th>Rol</th><th>Sede</th><th>Asignado desde</th><th>Vigente hasta</th><th>Estado</th><th>Acciones</th></tr></thead>
            <tbody>
                <?php $__empty_1 = true;
$__currentLoopData = $asignaciones;
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $asignacion) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop();
    $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($asignaciones->firstItem() + $loop->index); ?></td>
                        <td><?php echo e($asignacion->usuario_nombre); ?></td>
                        <td><?php echo e($asignacion->usuario_correo); ?></td>
                        <td><?php echo e($asignacion->rol_nombre); ?></td>
                        <td><?php echo e($asignacion->sede_nombre); ?></td>
                        <td><?php echo e($asignacion->asignado_en ? Carbon::parse($asignacion->asignado_en)->format('d/m/Y') : '—'); ?></td>
                        <td><?php echo e($asignacion->vigente_hasta ? Carbon::parse($asignacion->vigente_hasta)->format('d/m/Y') : 'Sin vencimiento'); ?></td>
                        <td><?php echo e($asignacion->vigente ? 'Vigente' : 'No vigente'); ?></td>
                        <td class="tc-org__row-actions"><button type="button" data-org-link="<?php echo e(route('management.user_assignments')); ?>" aria-label="Administrar asignaciones de <?php echo e($asignacion->usuario_nombre); ?>"><i class="bi bi-person-gear" aria-hidden="true"></i></button></td>
                    </tr>
                <?php } $__env->popLoop();
$loop = $__env->getLastLoop();
if ($__empty_1) { ?>
                    <tr><td colspan="9"><p class="tc-org__no-results">No hay asignaciones de personal que coincidan con los filtros. Puedes crear usuarios y asignarlos desde los módulos de usuarios y asignaciones.</p></td></tr>
                <?php } ?>
            </tbody>
        </table></div>
        <?php if ($asignaciones->hasPages()) { ?>
            <nav class="pagination" aria-label="Paginación de personal por sede"><?php echo e($asignaciones->links()); ?></nav>
        <?php } ?>
    </article>

    <div class="tc-org__grid tc-org__grid--halves">
        <article class="tc-org__card"><h2 class="tc-org__section-heading"><i class="bi bi-buildings" aria-hidden="true"></i> Personal vigente por sede</h2><p class="tc-org__section-copy">Conteos derivados de las asignaciones activas de usuarios.</p>
            <?php $__empty_1 = true;
$__currentLoopData = $personalPorSede;
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $resumenSede) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop();
    $__empty_1 = false; ?>
                <div class="tc-org__key-list"><div><span><?php echo e($resumenSede->nombre); ?></span><strong><?php echo e((int) $resumenSede->total_personal); ?></strong></div></div>
            <?php } $__env->popLoop();
$loop = $__env->getLastLoop();
if ($__empty_1) { ?>
                <p class="tc-org__no-results">No hay personal asignado en las sedes autorizadas.</p>
            <?php } ?>
        </article>
        <article class="tc-org__card"><h2 class="tc-org__section-heading"><i class="bi bi-person-badge" aria-hidden="true"></i> Roles vigentes</h2><p class="tc-org__section-copy">Distribución de usuarios por los roles reales del catálogo.</p>
            <?php $__empty_1 = true;
$__currentLoopData = $personalPorRol;
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $resumenRol) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop();
    $__empty_1 = false; ?>
                <div class="tc-org__key-list"><div><span><?php echo e($resumenRol->nombre); ?></span><strong><?php echo e((int) $resumenRol->total_personal); ?></strong></div></div>
            <?php } $__env->popLoop();
$loop = $__env->getLastLoop();
if ($__empty_1) { ?>
                <p class="tc-org__no-results">Todavía no existen asignaciones vigentes de roles por sede.</p>
            <?php } ?>
        </article>
    </div>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.panel.principal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>