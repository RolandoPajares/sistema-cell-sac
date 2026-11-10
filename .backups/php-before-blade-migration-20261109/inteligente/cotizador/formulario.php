<?php $__env->startSection('title', 'Optimizador de compra'); ?>
<?php $__env->startSection('body_class', 'modulo-optimizador'); ?>

<?php $__env->startSection('content'); ?>
    <section class="smart-page">
        <div class="container">
            <div class="smart-hero">
                <span class="eyebrow">Modo emprendedor</span>
                <h1><i class="bi bi-cash-coin" aria-hidden="true"></i> Optimizador por presupuesto</h1>
                <p>El sistema arma automáticamente una propuesta de compra respetando tu presupuesto y objetivo comercial.</p>
            </div>

            <form class="smart-form panel" method="get">
                <label>
                    Presupuesto
                    <input
                        class="input"
                        type="number"
                        name="presupuesto"
                        min="500"
                        step="100"
                        value="<?php echo e($budget > 0 ? $budget : ''); ?>"
                        placeholder="Ej. 10000"
                    >
                </label>

                <label>
                    Objetivo
                    <select name="objetivo">
                        <option value="variety" <?php if ($goal === 'variety') {
                            echo 'selected';
                        } ?>>Más variedad</option>
                        <option value="units" <?php if ($goal === 'units') {
                            echo 'selected';
                        } ?>>Más unidades</option>
                        <option value="margin" <?php if ($goal === 'margin') {
                            echo 'selected';
                        } ?>>Mayor margen estimado</option>
                        <option value="premium" <?php if ($goal === 'premium') {
                            echo 'selected';
                        } ?>>Equipos premium</option>
                    </select>
                </label>
                <button class="btn btn-primary" type="submit">Generar propuesta</button>
            </form>

            <?php if ($proposal) { ?>
                <div class="panel proposal">
                    <div class="proposal-kpis">
                        <div><span>Inversión</span><strong>S/ <?php echo e(number_format($proposal['investment'], 2)); ?></strong></div>
                        <div><span>Saldo disponible</span><strong>S/ <?php echo e(number_format($proposal['balance'], 2)); ?></strong></div>
                        <div><span>Margen referencial</span><strong>S/ <?php echo e(number_format($proposal['estimated_margin'], 2)); ?></strong></div>
                    </div>
                    <div class="table-wrap">
                        <table class="table">
                            <thead><tr><th>Producto</th><th>Cant.</th><th>Precio</th><th>Subtotal</th></tr></thead>
                            <tbody>
                                <?php $__empty_1 = true;
                $__currentLoopData = $proposal['items'];
                $__env->addLoop($__currentLoopData);
                foreach ($__currentLoopData as $item) {
                    $__env->incrementLoopIndices();
                    $loop = $__env->getLastLoop();
                    $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($item['product']->marca); ?> <?php echo e($item['product']->nombre); ?></td>
                                        <td><?php echo e($item['quantity']); ?></td>
                                        <td>S/ <?php echo e(number_format((float) $item['product']->effective_price, 2)); ?></td>
                                        <td>S/ <?php echo e(number_format($item['subtotal'], 2)); ?></td>
                                    </tr>
                                <?php } $__env->popLoop();
                $loop = $__env->getLastLoop();
                if ($__empty_1) { ?>
                                    <tr><td colspan="4">No hay productos disponibles para ese presupuesto.</td></tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <p class="product-note">Margen referencial calculado para simulación; no constituye una garantía de rentabilidad.</p>
                </div>
            <?php } ?>
        </div>
    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.tienda.principal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>