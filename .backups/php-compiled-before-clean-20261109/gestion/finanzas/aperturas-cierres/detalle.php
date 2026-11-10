<?php $__env->startSection('title', 'Arqueo de caja'); ?>
<?php $__env->startSection('body_class', 'modulo-finanzas-facturacion'); ?>

<?php $__env->startSection('content'); ?>
<section class="tc-ff" data-cajas-operativas data-cajas-reopen="<?php echo e(old('_form') === 'movimiento' && $errors->any() ? 'crear' : ''); ?>">
    <header class="tc-ff__header">
        <div>
            <div class="tc-ff__breadcrumb"><a href="<?php echo e(route('management.payments.preview.cash_sessions')); ?>">Apertura y cierre</a><i class="bi bi-chevron-right" aria-hidden="true"></i><strong>Arqueo de caja</strong></div>
            <h1><i class="bi bi-safe" aria-hidden="true"></i>Arqueo de <?php echo e($session->caja->codigo); ?></h1>
            <p><?php echo e($session->caja->sede->nombre); ?> · Apertura <?php echo e($session->abierta_en?->format('d/m/Y H:i')); ?> · <?php echo e($session->usuarioApertura?->nombre); ?></p>
        </div>
        <div class="tc-ff__header-actions">
            <?php if($canMove): ?><button type="button" class="tc-ff__button tc-ff__button--primary" data-box-open="crear"><i class="bi bi-plus-lg" aria-hidden="true"></i>Registrar movimiento</button><?php endif; ?>
            <a class="tc-ff__button" href="<?php echo e(route('management.payments.preview.cash_sessions')); ?>">Historial</a>
        </div>
    </header>

    <?php if(session('status')): ?> <p class="alert alert-success" role="status"><?php echo e(session('status')); ?></p> <?php endif; ?>
    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <p class="text-danger" role="alert"><?php echo e($error); ?></p> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    <div class="tc-ff__metrics">
        <article class="tc-ff__metric tc-ff__metric--azul"><span class="tc-ff__metric-icon"><i class="bi bi-wallet" aria-hidden="true"></i></span><div><small>Saldo inicial contado</small><strong>S/ <?php echo e($session->saldo_inicial); ?></strong><span>Al abrir la sesión</span></div></article>
        <article class="tc-ff__metric tc-ff__metric--verde"><span class="tc-ff__metric-icon"><i class="bi bi-arrow-down-left" aria-hidden="true"></i></span><div><small>Entradas registradas</small><strong>S/ <?php echo e($balance['entradas']); ?></strong><span>Movimientos de caja</span></div></article>
        <article class="tc-ff__metric tc-ff__metric--ambar"><span class="tc-ff__metric-icon"><i class="bi bi-arrow-up-right" aria-hidden="true"></i></span><div><small>Salidas registradas</small><strong>S/ <?php echo e($balance['salidas']); ?></strong><span>Movimientos de caja</span></div></article>
        <article class="tc-ff__metric tc-ff__metric--azul"><span class="tc-ff__metric-icon"><i class="bi bi-calculator" aria-hidden="true"></i></span><div><small>Saldo esperado</small><strong>S/ <?php echo e($balance['saldo_esperado']); ?></strong><span>Inicial + entradas − salidas</span></div></article>
    </div>

    <div class="tc-ff__main-grid">
        <article class="tc-ff__card tc-ff__table-card">
            <div class="tc-ff__card-heading"><h2><i class="bi bi-list-ul" aria-hidden="true"></i>Movimientos</h2><small><?php echo e($movements->total()); ?> registros</small></div>
            <div class="tc-ff__table-wrap">
                <table>
                    <thead><tr><th>Fecha</th><th>Dirección</th><th>Tipo</th><th>Importe</th><th>Descripción</th><th>Usuario</th></tr></thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $movements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $movement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr><td><?php echo e($movement->creado_en?->format('d/m/Y H:i')); ?></td><td><span class="tc-ff__pill tc-ff__pill--<?php echo e($movement->direccion === 'entrada' ? 'verde' : 'rojo'); ?>"><?php echo e(ucfirst($movement->direccion)); ?></span></td><td><?php echo e($movement->tipo); ?></td><td>S/ <?php echo e($movement->importe); ?></td><td><?php echo e($movement->descripcion ?? '—'); ?></td><td><?php echo e($movement->creador?->nombre ?? 'Sistema'); ?></td></tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="6">Esta sesión todavía no tiene movimientos.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if($movements->hasPages()): ?><div class="tc-ff__card-heading"><small>Página <?php echo e($movements->currentPage()); ?> de <?php echo e($movements->lastPage()); ?></small><div class="tc-ff__header-actions"><?php if($movements->previousPageUrl()): ?> <a class="tc-ff__button" href="<?php echo e($movements->previousPageUrl()); ?>">Anterior</a> <?php endif; ?> <?php if($movements->nextPageUrl()): ?> <a class="tc-ff__button" href="<?php echo e($movements->nextPageUrl()); ?>">Siguiente</a> <?php endif; ?></div></div><?php endif; ?>
        </article>
        <aside class="tc-ff__card tc-ff__aside">
            <h2><i class="bi bi-clipboard-check" aria-hidden="true"></i>Estado y cierre</h2>
            <p><span class="tc-ff__pill tc-ff__pill--<?php echo e($session->estado === 'abierta' ? 'verde' : 'azul'); ?>"><?php echo e(ucfirst($session->estado)); ?></span></p>
            <?php if($session->estado === 'cerrada'): ?>
                <div class="tc-ff__summary"><div><span>Efectivo declarado</span><b>S/ <?php echo e($session->saldo_cierre_declarado); ?></b></div><div><span>Diferencia</span><b>S/ <?php echo e($balance['diferencia']); ?></b></div><div><span>Responsable del cierre</span><b><?php echo e($session->usuarioCierre?->nombre); ?></b></div><div><span>Fecha de cierre</span><b><?php echo e($session->cerrada_en?->format('d/m/Y H:i')); ?></b></div></div>
            <?php elseif($canClose): ?>
                <p>Cuenta físicamente el efectivo. Si difiere del saldo esperado, el cierre se bloquea hasta documentar y conciliar la diferencia.</p>
                <form method="POST" action="<?php echo e(route('management.payments.cash_sessions.update', $session)); ?>">
                    <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                    <input type="hidden" name="token" value="<?php echo e((string) \Illuminate\Support\Str::uuid()); ?>">
                    <div class="tc-ff__form-grid"><label>Efectivo contado al cierre (S/) *<input type="text" name="saldo_cierre_declarado" inputmode="decimal" value="<?php echo e(old('saldo_cierre_declarado')); ?>" required></label></div>
                    <button class="tc-ff__button tc-ff__button--primary tc-ff__submit" type="submit" data-confirm="¿Confirmas el arqueo y cierre de esta sesión?">Comprobar y cerrar</button>
                </form>
            <?php endif; ?>
        </aside>
    </div>

    <?php if($canMove): ?>
        <dialog class="tc-ff__dialog" data-box-dialog="crear" aria-labelledby="cash-movement-title">
            <form class="tc-ff__dialog-card" method="POST" action="<?php echo e(route('management.payments.cash_sessions.movements.store', $session)); ?>">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="_form" value="movimiento">
                <input type="hidden" name="token" value="<?php echo e((string) \Illuminate\Support\Str::uuid()); ?>">
                <header class="tc-ff__dialog-header"><div><span class="tc-ff__dialog-icon"><i class="bi bi-arrow-left-right" aria-hidden="true"></i></span><div><h2 id="cash-movement-title">Registrar movimiento manual</h2><p>La operación quedará auditada en esta sesión.</p></div></div><button type="button" class="tc-ff__dialog-close" data-box-close aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header>
                <div class="tc-ff__dialog-body"><div class="tc-ff__field-grid">
                    <label class="tc-ff__field">Dirección *<select name="direccion" required><option value="">Selecciona</option><option value="entrada" <?php if(old('direccion') === 'entrada'): echo 'selected'; endif; ?>>Entrada</option><option value="salida" <?php if(old('direccion') === 'salida'): echo 'selected'; endif; ?>>Salida</option></select></label>
                    <label class="tc-ff__field">Importe (S/) *<input type="text" name="importe" inputmode="decimal" value="<?php echo e(old('importe')); ?>" required></label>
                    <label class="tc-ff__field tc-ff__field--full">Motivo *<textarea name="descripcion" maxlength="500" required><?php echo e(old('descripcion')); ?></textarea></label>
                </div></div>
                <footer class="tc-ff__dialog-footer"><button type="button" class="tc-ff__button" data-box-close>Cancelar</button><button type="submit" class="tc-ff__button tc-ff__button--primary">Registrar movimiento</button></footer>
            </form>
        </dialog>
    <?php endif; ?>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.panel.principal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>