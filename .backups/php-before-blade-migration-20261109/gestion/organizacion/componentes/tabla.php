<article class="tc-org__card tc-org__table-card">
    <div class="tc-org__card-title"><div><h2><?php echo e($titulo); ?> (<?php echo e($total ?? count($filas)); ?>)</h2><p>Mostrando <?php echo e(count($filas)); ?> registros de ejemplo. No corresponden a datos reales.</p></div></div>
    <div class="tc-org__table-wrap"><table><thead><tr><th>#</th><?php $__currentLoopData = $columnas;
    $__env->addLoop($__currentLoopData);
    foreach ($__currentLoopData as $columna) {
        $__env->incrementLoopIndices();
        $loop = $__env->getLastLoop(); ?><th><?php echo e($columna); ?></th><?php } $__env->popLoop();
    $loop = $__env->getLastLoop(); ?><th>Acciones</th></tr></thead>
        <tbody>
            <?php $__currentLoopData = $filas;
    $__env->addLoop($__currentLoopData);
    foreach ($__currentLoopData as $fila) {
        $__env->incrementLoopIndices();
        $loop = $__env->getLastLoop(); ?>
                <tr data-org-row <?php $__currentLoopData = $fila['filtros'] ?? [];
        $__env->addLoop($__currentLoopData);
        foreach ($__currentLoopData as $campo => $valor) {
            $__env->incrementLoopIndices();
            $loop = $__env->getLastLoop(); ?> data-org-<?php echo e($campo); ?>="<?php echo e($valor); ?>" <?php } $__env->popLoop();
        $loop = $__env->getLastLoop(); ?>>
                    <td><?php echo e($loop->iteration); ?></td>
                    <?php $__currentLoopData = $fila['celdas'];
        $__env->addLoop($__currentLoopData);
        foreach ($__currentLoopData as $celda) {
            $__env->incrementLoopIndices();
            $loop = $__env->getLastLoop(); ?>
                        <td><?php echo e($celda); ?></td>
                    <?php } $__env->popLoop();
        $loop = $__env->getLastLoop(); ?>
                    <td class="tc-org__row-actions"><button type="button" <?php if (isset($accionesFila['ver'])) { ?> data-org-open="<?php echo e($accionesFila['ver']); ?>" aria-label="Ver detalle de <?php echo e($fila['celdas'][0]); ?>" <?php } else { ?> disabled title="Detalles pendientes" <?php } ?>><i class="bi bi-eye"></i></button><button type="button" <?php if (isset($accionesFila['editar'])) { ?> data-org-open="<?php echo e($accionesFila['editar']); ?>" aria-label="Editar <?php echo e($fila['celdas'][0]); ?>" <?php } else { ?> disabled title="Edición pendiente" <?php } ?>><i class="bi bi-pencil"></i></button><button type="button" <?php if (isset($accionesFila['mas'])) { ?> data-org-open="<?php echo e($accionesFila['mas']); ?>" aria-label="Más acciones para <?php echo e($fila['celdas'][0]); ?>" <?php } else { ?> disabled title="Más acciones pendientes" <?php } ?>><i class="bi bi-three-dots-vertical"></i></button></td>
                </tr>
            <?php } $__env->popLoop();
    $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table></div>
    <p class="tc-org__no-results" data-org-empty hidden>No hay ejemplos que coincidan con la búsqueda.</p>
</article>
