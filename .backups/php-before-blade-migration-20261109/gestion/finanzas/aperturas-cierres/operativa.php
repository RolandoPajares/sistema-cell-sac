<?php
use Illuminate\Support\Str;

$__env->startSection('title', 'Cajas, aperturas y cierres'); ?>
<?php $__env->startSection('body_class', 'modulo-finanzas-facturacion'); ?>

<?php $__env->startSection('content'); ?>
<section class="tc-ff" data-cajas-operativas data-cajas-reopen="<?php echo e(old('_form') === 'crear' && $errors->any() ? 'crear' : ''); ?>">
    <header class="tc-ff__header">
        <div>
            <div class="tc-ff__breadcrumb"><i class="bi bi-house" aria-hidden="true"></i><span>Finanzas y Facturación</span><i class="bi bi-chevron-right" aria-hidden="true"></i><strong>Cajas, aperturas y cierres</strong></div>
            <h1><i class="bi bi-safe" aria-hidden="true"></i>Cajas, aperturas y cierres</h1>
            <p>Sesiones reales de las cajas autorizadas, con saldos y movimientos auditados.</p>
        </div>
        <div class="tc-ff__header-actions">
            <?php if ($canOpen) { ?>
                <button type="button" class="tc-ff__button tc-ff__button--primary" data-box-open="crear"><i class="bi bi-plus-lg" aria-hidden="true"></i>Nueva apertura</button>
            <?php } ?>
            <a class="tc-ff__button" href="<?php echo e(route('management.payments.preview.cash_registers')); ?>">Gestión de cajas</a>
        </div>
    </header>

    <?php if (session('status')) { ?> <p class="alert alert-success" role="status"><?php echo e(session('status')); ?></p> <?php } ?>
    <?php $__currentLoopData = $errors->all();
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $error) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop(); ?> <p class="text-danger" role="alert"><?php echo e($error); ?></p> <?php } $__env->popLoop();
$loop = $__env->getLastLoop(); ?>

    <div class="tc-ff__metrics">
        <article class="tc-ff__metric tc-ff__metric--azul"><span class="tc-ff__metric-icon"><i class="bi bi-safe" aria-hidden="true"></i></span><div><small>Sesiones registradas</small><strong><?php echo e($openCount + $closedCount); ?></strong><span>En sedes autorizadas</span></div></article>
        <article class="tc-ff__metric tc-ff__metric--verde"><span class="tc-ff__metric-icon"><i class="bi bi-unlock" aria-hidden="true"></i></span><div><small>Sesiones abiertas</small><strong><?php echo e($openCount); ?></strong><span>Admiten operaciones ordinarias</span></div></article>
        <article class="tc-ff__metric tc-ff__metric--azul"><span class="tc-ff__metric-icon"><i class="bi bi-lock" aria-hidden="true"></i></span><div><small>Sesiones cerradas</small><strong><?php echo e($closedCount); ?></strong><span>Con arqueo conforme</span></div></article>
        <article class="tc-ff__metric tc-ff__metric--ambar"><span class="tc-ff__metric-icon"><i class="bi bi-cash-stack" aria-hidden="true"></i></span><div><small>Cajas disponibles</small><strong><?php echo e($boxes->count()); ?></strong><span>Sin sesión abierta</span></div></article>
    </div>

    <form class="tc-ff__filters" method="GET" action="<?php echo e(route('management.payments.preview.cash_sessions')); ?>">
        <label class="tc-ff__search"><i class="bi bi-search" aria-hidden="true"></i><input type="search" name="buscar" value="<?php echo e($filters['buscar'] ?? ''); ?>" maxlength="120" placeholder="Buscar caja por código o nombre" aria-label="Buscar sesiones"></label>
        <div class="tc-ff__filter-fields">
            <label><span>Sede</span><select name="sede_id"><option value="">Todas las sedes</option><?php $__currentLoopData = $sites;
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $site) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop(); ?><option value="<?php echo e($site->id); ?>" <?php if ((string) ($filters['sede_id'] ?? '') === (string) $site->id) {
        echo 'selected';
    } ?>><?php echo e($site->nombre); ?></option><?php } $__env->popLoop();
$loop = $__env->getLastLoop(); ?></select></label>
            <label><span>Estado</span><select name="estado"><option value="">Todos</option><option value="abierta" <?php if (($filters['estado'] ?? '') === 'abierta') {
                echo 'selected';
            } ?>>Abierta</option><option value="cerrada" <?php if (($filters['estado'] ?? '') === 'cerrada') {
                echo 'selected';
            } ?>>Cerrada</option></select></label>
            <label><span>Desde</span><input type="date" name="desde" value="<?php echo e($filters['desde'] ?? ''); ?>"></label>
            <label><span>Hasta</span><input type="date" name="hasta" value="<?php echo e($filters['hasta'] ?? ''); ?>"></label>
            <button class="tc-ff__button tc-ff__button--primary" type="submit">Aplicar filtros</button>
            <a class="tc-ff__button" href="<?php echo e(route('management.payments.preview.cash_sessions')); ?>">Limpiar</a>
        </div>
    </form>

    <div class="tc-ff__main-grid">
        <article class="tc-ff__card tc-ff__table-card">
            <div class="tc-ff__card-heading"><h2><i class="bi bi-list-ul" aria-hidden="true"></i>Historial de sesiones</h2><small><?php echo e($sessions->total()); ?> registros</small></div>
            <div class="tc-ff__table-wrap">
                <table>
                    <thead><tr><th>Caja</th><th>Sede</th><th>Responsable</th><th>Saldo inicial</th><th>Apertura</th><th>Cierre</th><th>Estado</th><th>Acción</th></tr></thead>
                    <tbody>
                        <?php $__empty_1 = true;
$__currentLoopData = $sessions;
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $session) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop();
    $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($session->caja?->codigo); ?></td>
                                <td><?php echo e($session->caja?->sede?->nombre); ?></td>
                                <td><?php echo e($session->usuarioApertura?->nombre); ?></td>
                                <td>S/ <?php echo e($session->saldo_inicial); ?></td>
                                <td><?php echo e($session->abierta_en?->format('d/m/Y H:i')); ?></td>
                                <td><?php echo e($session->cerrada_en?->format('d/m/Y H:i') ?? '—'); ?></td>
                                <td><span class="tc-ff__pill tc-ff__pill--<?php echo e($session->estado === 'abierta' ? 'verde' : 'azul'); ?>"><?php echo e(ucfirst($session->estado)); ?></span></td>
                                <td><a href="<?php echo e(route('management.payments.cash_sessions.show', $session)); ?>">Ver arqueo</a></td>
                            </tr>
                        <?php } $__env->popLoop();
$loop = $__env->getLastLoop();
if ($__empty_1) { ?>
                            <tr><td colspan="8">No hay sesiones que coincidan con los filtros.</td></tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            <?php if ($sessions->hasPages()) { ?>
                <div class="tc-ff__card-heading"><small>Página <?php echo e($sessions->currentPage()); ?> de <?php echo e($sessions->lastPage()); ?></small><div class="tc-ff__header-actions"><?php if ($sessions->previousPageUrl()) { ?> <a class="tc-ff__button" href="<?php echo e($sessions->previousPageUrl()); ?>">Anterior</a> <?php } ?> <?php if ($sessions->nextPageUrl()) { ?> <a class="tc-ff__button" href="<?php echo e($sessions->nextPageUrl()); ?>">Siguiente</a> <?php } ?></div></div>
            <?php } ?>
        </article>
        <aside class="tc-ff__card tc-ff__aside">
            <h2><i class="bi bi-shield-check" aria-hidden="true"></i>Control de caja</h2>
            <p>Una caja admite una sola sesión abierta. El cierre requiere que el efectivo declarado coincida con el saldo calculado a partir de las entradas y salidas registradas.</p>
            <div class="tc-ff__summary"><strong><?php echo e($openCount); ?> abiertas</strong><div><span>Disponibles para apertura</span><b><?php echo e($boxes->count()); ?></b></div><div><span>Sesiones cerradas</span><b><?php echo e($closedCount); ?></b></div></div>
        </aside>
    </div>

    <?php if ($canOpen) { ?>
        <dialog class="tc-ff__dialog" data-box-dialog="crear" aria-labelledby="cash-open-title">
            <form class="tc-ff__dialog-card" method="POST" action="<?php echo e(route('management.payments.cash_sessions.store')); ?>">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="_form" value="crear">
                <input type="hidden" name="token" value="<?php echo e((string) Str::uuid()); ?>">
                <header class="tc-ff__dialog-header"><div><span class="tc-ff__dialog-icon"><i class="bi bi-unlock" aria-hidden="true"></i></span><div><h2 id="cash-open-title">Nueva apertura</h2><p>Declara el efectivo inicial contado.</p></div></div><button type="button" class="tc-ff__dialog-close" data-box-close aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header>
                <div class="tc-ff__dialog-body"><div class="tc-ff__field-grid">
                    <label class="tc-ff__field tc-ff__field--full">Caja *<select name="caja_id" required><option value="">Selecciona una caja</option><?php $__currentLoopData = $boxes;
        $__env->addLoop($__currentLoopData);
        foreach ($__currentLoopData as $box) {
            $__env->incrementLoopIndices();
            $loop = $__env->getLastLoop(); ?><option value="<?php echo e($box->id); ?>" <?php if ((string) old('caja_id') === (string) $box->id) {
                echo 'selected';
            } ?>><?php echo e($box->codigo); ?> · <?php echo e($box->sede?->nombre); ?></option><?php } $__env->popLoop();
        $loop = $__env->getLastLoop(); ?></select></label>
                    <label class="tc-ff__field tc-ff__field--full">Efectivo inicial contado (S/) *<input type="text" name="saldo_inicial" inputmode="decimal" value="<?php echo e(old('saldo_inicial', '0.00')); ?>" required></label>
                </div></div>
                <footer class="tc-ff__dialog-footer"><button type="button" class="tc-ff__button" data-box-close>Cancelar</button><button type="submit" class="tc-ff__button tc-ff__button--primary">Abrir caja</button></footer>
            </form>
        </dialog>
    <?php } ?>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.panel.principal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>