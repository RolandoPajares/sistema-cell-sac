<section class="tc-oc" data-ventas-diseno>
    <header class="tc-oc__header">
        <div><span class="tc-oc__breadcrumb"><i class="bi bi-house" aria-hidden="true"></i> Operaciones Comerciales <i class="bi bi-chevron-right" aria-hidden="true"></i> <?php echo e($datos['titulo']); ?></span><h1><i class="bi <?php echo e($datos['icono']); ?>" aria-hidden="true"></i><?php echo e($datos['titulo']); ?></h1><p><?php echo e($datos['descripcion']); ?></p></div>
        <div class="tc-oc__header-actions"><?php $__currentLoopData = $datos['acciones'];
        $__env->addLoop($__currentLoopData);
        foreach ($__currentLoopData as $accion) {
            $__env->incrementLoopIndices();
            $loop = $__env->getLastLoop(); ?><?php if (! empty($accion['ruta'])) { ?><a class="tc-oc__button <?php echo e(($accion['primaria'] ?? false) ? 'tc-oc__button--primary' : ''); ?>" href="<?php echo e($accion['ruta']); ?>"><i class="bi <?php echo e($accion['icono']); ?>" aria-hidden="true"></i><?php echo e($accion['texto']); ?></a><?php } elseif (! empty($accion['modal'])) { ?><button type="button" class="tc-oc__button <?php echo e(($accion['primaria'] ?? false) ? 'tc-oc__button--primary' : ''); ?>" data-oc-open-modal="<?php echo e($accion['modal']); ?>"><i class="bi <?php echo e($accion['icono']); ?>" aria-hidden="true"></i><?php echo e($accion['texto']); ?></button><?php } else { ?><button type="button" class="tc-oc__button <?php echo e(($accion['primaria'] ?? false) ? 'tc-oc__button--primary' : ''); ?>" disabled title="Esta operación no cuenta con una gestión real disponible"><i class="bi <?php echo e($accion['icono']); ?>" aria-hidden="true"></i><?php echo e($accion['texto']); ?></button><?php } ?> <?php } $__env->popLoop();
        $loop = $__env->getLastLoop(); ?></div>
    </header>
    <?php if (! empty($datos['datos_reales'])) { ?>
        <div class="tc-oc__notice" role="status"><i class="bi bi-database-check" aria-hidden="true"></i><strong>Datos reales</strong><span>Listado e indicadores consultados desde MariaDB. No hay condición de pago registrada para estas ventas.</span><a href="<?php echo e(route('management.sales')); ?>">Abrir gestión de ventas <i class="bi bi-arrow-right" aria-hidden="true"></i></a></div>
    <?php } else { ?>
        <div class="tc-oc__notice" role="status"><i class="bi bi-eye" aria-hidden="true"></i><strong>Vista de diseño</strong><span>Las cifras y ventas son ejemplos; los formularios no guardan operaciones.</span><a href="<?php echo e(route('management.sales')); ?>">Abrir gestión de ventas actual <i class="bi bi-arrow-right" aria-hidden="true"></i></a></div>
    <?php } ?>
    <div class="tc-oc__metrics"><?php $__currentLoopData = $datos['metricas'];
        $__env->addLoop($__currentLoopData);
        foreach ($__currentLoopData as $metrica) {
            $__env->incrementLoopIndices();
            $loop = $__env->getLastLoop(); ?><article class="tc-oc__metric tc-oc__metric--<?php echo e($metrica['tono'] ?? 'azul'); ?>"><span class="tc-oc__metric-icon"><i class="bi <?php echo e($metrica['icono']); ?>" aria-hidden="true"></i></span><div><small><?php echo e($metrica['titulo']); ?></small><strong><?php echo e($metrica['valor']); ?></strong></div><span class="tc-oc__metric-trend"><?php echo e($metrica['tendencia']); ?></span></article><?php } $__env->popLoop();
        $loop = $__env->getLastLoop(); ?></div>
    <?php if (! empty($datos['datos_reales'])) { ?>
        <form class="tc-oc__filters" method="GET" action="<?php echo e(request()->url()); ?>"><label class="tc-oc__search"><i class="bi bi-search" aria-hidden="true"></i><input type="search" name="buscar" value="<?php echo e($filtros['buscar'] ?? ''); ?>" data-oc-search placeholder="<?php echo e($datos['buscar']); ?>" aria-label="<?php echo e($datos['buscar']); ?>"></label><div class="tc-oc__filter-fields"><?php $__currentLoopData = $datos['filtros'];
        $__env->addLoop($__currentLoopData);
        foreach ($__currentLoopData as $filtro) {
            $__env->incrementLoopIndices();
            $loop = $__env->getLastLoop(); ?><label><span><?php echo e($filtro['titulo']); ?></span><select name="<?php echo e($filtro['campo']); ?>" data-oc-filter="<?php echo e($filtro['campo']); ?>"><option value=""><?php echo e($filtro['todos']); ?></option><?php $__currentLoopData = $filtro['opciones'];
            $__env->addLoop($__currentLoopData);
            foreach ($__currentLoopData as $opcion) {
                $__env->incrementLoopIndices();
                $loop = $__env->getLastLoop(); ?><?php ($valorOpcion = is_array($opcion) ? $opcion['valor'] : $opcion); ?><?php ($etiquetaOpcion = is_array($opcion) ? $opcion['etiqueta'] : $opcion); ?><option value="<?php echo e($valorOpcion); ?>" <?php if ((string) ($filtros[$filtro['campo']] ?? '') === (string) $valorOpcion) {
                    echo 'selected';
                } ?>><?php echo e($etiquetaOpcion); ?></option><?php } $__env->popLoop();
            $loop = $__env->getLastLoop(); ?></select></label><?php } $__env->popLoop();
        $loop = $__env->getLastLoop(); ?><button type="submit" class="tc-oc__button tc-oc__button--primary"><i class="bi bi-funnel" aria-hidden="true"></i>Aplicar filtros</button></div></form>
    <?php } else { ?>
        <div class="tc-oc__filters"><label class="tc-oc__search"><i class="bi bi-search" aria-hidden="true"></i><input type="search" data-oc-search placeholder="<?php echo e($datos['buscar']); ?>" aria-label="<?php echo e($datos['buscar']); ?>"></label><div class="tc-oc__filter-fields"><?php $__currentLoopData = $datos['filtros'];
        $__env->addLoop($__currentLoopData);
        foreach ($__currentLoopData as $filtro) {
            $__env->incrementLoopIndices();
            $loop = $__env->getLastLoop(); ?><label><span><?php echo e($filtro['titulo']); ?></span><select data-oc-filter="<?php echo e($filtro['campo']); ?>"><option value=""><?php echo e($filtro['todos']); ?></option><?php $__currentLoopData = $filtro['opciones'];
            $__env->addLoop($__currentLoopData);
            foreach ($__currentLoopData as $opcion) {
                $__env->incrementLoopIndices();
                $loop = $__env->getLastLoop(); ?><option value="<?php echo e($opcion); ?>"><?php echo e($opcion); ?></option><?php } $__env->popLoop();
            $loop = $__env->getLastLoop(); ?></select></label><?php } $__env->popLoop();
        $loop = $__env->getLastLoop(); ?><button type="button" class="tc-oc__button tc-oc__button--primary" data-oc-apply><i class="bi bi-funnel" aria-hidden="true"></i>Aplicar filtros</button></div></div>
    <?php } ?>
    <div class="tc-oc__main-grid"><article class="tc-oc__card tc-oc__table-card"><div class="tc-oc__card-heading"><h2><i class="bi <?php echo e($datos['tablaIcono']); ?>" aria-hidden="true"></i><?php echo e($datos['tablaTitulo']); ?></h2><small>Mostrando <?php echo e(count($datos['filas'])); ?> de <?php echo e($datos['total']); ?> <?php echo e(! empty($datos['datos_reales']) ? 'ventas' : 'ejemplos'); ?></small></div><div class="tc-oc__table-wrap"><table><thead><tr><?php $__currentLoopData = $datos['columnas'];
        $__env->addLoop($__currentLoopData);
        foreach ($__currentLoopData as $columna) {
            $__env->incrementLoopIndices();
            $loop = $__env->getLastLoop(); ?><th><?php echo e($columna); ?></th><?php } $__env->popLoop();
        $loop = $__env->getLastLoop(); ?><th>Acciones</th></tr></thead><tbody><?php $__empty_1 = true;
        $__currentLoopData = $datos['filas'];
        $__env->addLoop($__currentLoopData);
        foreach ($__currentLoopData as $fila) {
            $__env->incrementLoopIndices();
            $loop = $__env->getLastLoop();
            $__empty_1 = false; ?><tr data-oc-row <?php $__currentLoopData = $fila['filtros'];
            $__env->addLoop($__currentLoopData);
            foreach ($__currentLoopData as $campo => $valor) {
                $__env->incrementLoopIndices();
                $loop = $__env->getLastLoop(); ?> data-oc-<?php echo e($campo); ?>="<?php echo e($valor); ?>" <?php } $__env->popLoop();
            $loop = $__env->getLastLoop(); ?>><?php $__currentLoopData = $fila['celdas'];
            $__env->addLoop($__currentLoopData);
            foreach ($__currentLoopData as $celda) {
                $__env->incrementLoopIndices();
                $loop = $__env->getLastLoop(); ?><td><?php if (is_array($celda)) { ?><span class="tc-oc__pill tc-oc__pill--<?php echo e($celda['tono'] ?? 'azul'); ?>"><?php echo e($celda['texto']); ?></span><?php } else { ?><?php echo e($celda); ?><?php } ?></td><?php } $__env->popLoop();
            $loop = $__env->getLastLoop(); ?><td><div class="tc-oc__row-actions"><?php if (! empty($datos['ver_detalle_demo'])) { ?><button type="button" data-oc-open-detail aria-haspopup="dialog"><i class="bi bi-eye" aria-hidden="true"></i>Ver</button><?php } else { ?><button type="button" disabled title="La consulta detallada no está disponible en esta pantalla"><i class="bi bi-eye" aria-hidden="true"></i>Ver</button><?php } ?><button type="button" disabled title="Edición pendiente"><i class="bi bi-three-dots" aria-hidden="true"></i></button></div></td></tr><?php } $__env->popLoop();
        $loop = $__env->getLastLoop();
        if ($__empty_1) { ?><tr><td colspan="<?php echo e(count($datos['columnas']) + 1); ?>" class="tc-oc__empty">No hay ventas registradas para estos filtros.</td></tr><?php } ?></tbody></table></div><p class="tc-oc__empty" data-oc-empty hidden>No hay ventas que coincidan con los filtros.</p></article>
        <aside class="tc-oc__card tc-oc__quick-form"><h2><i class="bi <?php echo e($datos['formIcono']); ?>" aria-hidden="true"></i><?php echo e($datos['formTitulo']); ?></h2><p><?php if (! empty($datos['datos_reales'])) { ?>Completa el registro en el formulario de ventas autorizado.<?php } else { ?> Vista previa del formulario. Usa la gestión actual para registrar ventas.<?php } ?></p><?php $__currentLoopData = $datos['formCampos'];
        $__env->addLoop($__currentLoopData);
        foreach ($__currentLoopData as $campo) {
            $__env->incrementLoopIndices();
            $loop = $__env->getLastLoop(); ?><label><?php echo e($campo['etiqueta']); ?> <?php if ($campo['requerido'] ?? false) { ?><span>*</span><?php } ?> <?php if (($campo['tipo'] ?? 'select') === 'select') { ?><select disabled><option><?php echo e($campo['ayuda']); ?></option></select><?php } else { ?><input type="text" placeholder="<?php echo e($campo['ayuda']); ?>" disabled><?php } ?></label><?php } $__env->popLoop();
        $loop = $__env->getLastLoop(); ?><a href="<?php echo e($datos['rutaFormulario'] ?? route('management.sales')); ?>" class="tc-oc__button tc-oc__button--primary tc-oc__form-submit"><i class="bi <?php echo e($datos['formIcono']); ?>" aria-hidden="true"></i><?php echo e($datos['formAccion']); ?></a><?php if (! empty($datos['condiciones_demo'])) { ?><button type="button" class="tc-oc__button tc-oc__terms-button" data-oc-open-modal="condiciones-mayoristas"><i class="bi bi-card-checklist" aria-hidden="true"></i>Definir condiciones comerciales</button><?php } ?></aside></div>
    <div class="tc-oc__bottom-grid">
        <article class="tc-oc__card"><div class="tc-oc__card-heading"><h2><i class="bi <?php echo e($datos['anillo']['icono']); ?>" aria-hidden="true"></i><?php echo e($datos['anillo']['titulo']); ?></h2></div><div class="tc-oc__donut-layout"><div class="tc-oc__donut" style="--tc-oc-donut: <?php echo e($datos['anillo']['gradiente']); ?>"><span><strong><?php echo e($datos['anillo']['valor']); ?></strong><small><?php echo e($datos['anillo']['subtitulo']); ?></small></span></div><ul class="tc-oc__legend"><?php $__currentLoopData = $datos['anillo']['filas'];
        $__env->addLoop($__currentLoopData);
        foreach ($__currentLoopData as $fila) {
            $__env->incrementLoopIndices();
            $loop = $__env->getLastLoop(); ?><li><i></i><span><?php echo e($fila[0]); ?></span><strong><?php echo e($fila[1]); ?></strong></li><?php } $__env->popLoop();
        $loop = $__env->getLastLoop(); ?></ul></div></article>
        <article class="tc-oc__card"><div class="tc-oc__card-heading"><h2><i class="bi <?php echo e($datos['grafico']['icono']); ?>" aria-hidden="true"></i><?php echo e($datos['grafico']['titulo']); ?></h2></div><div class="tc-oc__chart" role="img" aria-label="Gráfico de datos consultados para <?php echo e($datos['grafico']['titulo']); ?>"><?php $__currentLoopData = $datos['grafico']['barras'];
        $__env->addLoop($__currentLoopData);
        foreach ($__currentLoopData as $barra) {
            $__env->incrementLoopIndices();
            $loop = $__env->getLastLoop(); ?><div><strong><?php echo e($barra[1]); ?></strong><i style="height: <?php echo e($barra[2]); ?>%"></i><small><?php echo e($barra[0]); ?></small></div><?php } $__env->popLoop();
        $loop = $__env->getLastLoop(); ?></div></article>
        <article class="tc-oc__card"><div class="tc-oc__card-heading"><h2><i class="bi <?php echo e($datos['ranking']['icono']); ?>" aria-hidden="true"></i><?php echo e($datos['ranking']['titulo']); ?></h2></div><div class="tc-oc__ranking"><?php $__currentLoopData = $datos['ranking']['filas'];
        $__env->addLoop($__currentLoopData);
        foreach ($__currentLoopData as $fila) {
            $__env->incrementLoopIndices();
            $loop = $__env->getLastLoop(); ?><div><b><?php echo e($loop->iteration); ?></b><span><?php echo e($fila[0]); ?></span><strong><?php echo e($fila[1]); ?></strong></div><?php } $__env->popLoop();
        $loop = $__env->getLastLoop(); ?></div></article>
    </div>
<?php if (($datos['titulo'] ?? '') === 'Ventas minoristas' && empty($datos['datos_reales'])) { ?>
    <dialog class="tc-oc__dialog" data-oc-visual-dialog="apertura-caja" aria-labelledby="tc-oc-cash-title">
        <header class="tc-oc__dialog-header"><span class="tc-oc__dialog-icon"><i class="bi bi-cash-stack" aria-hidden="true"></i></span><div><h2 id="tc-oc-cash-title">Apertura de caja</h2><p>Completa los datos de ejemplo para revisar la interfaz.</p></div><button type="button" class="tc-oc__dialog-close" data-oc-close-modal aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header>
        <div class="tc-oc__dialog-body"><div class="tc-oc__dialog-warning"><i class="bi bi-info-circle" aria-hidden="true"></i><p>Demostración visual: no abre una caja, no registra un saldo inicial y no genera movimientos.</p></div><div class="tc-oc__dialog-fields"><label class="tc-oc__dialog-field">Caja <b>*</b><input type="text" required maxlength="100" placeholder="Nombre de la caja"></label><label class="tc-oc__dialog-field">Saldo inicial (S/) <b>*</b><input type="number" required min="0" step="0.01" inputmode="decimal" placeholder="0.00"></label><label class="tc-oc__dialog-field tc-oc__dialog-field--wide">Observaciones<textarea rows="3" maxlength="500" placeholder="Notas de apertura"></textarea></label></div><p class="tc-oc__dialog-error" data-oc-demo-error role="alert" hidden></p><p class="tc-oc__dialog-feedback" data-oc-demo-status role="status" aria-live="polite" hidden></p></div>
        <footer class="tc-oc__dialog-footer"><button type="button" class="tc-oc__button" data-oc-close-modal>Cancelar</button><button type="button" class="tc-oc__button tc-oc__button--primary" data-oc-confirm-demo>Confirmar apertura (demostración)</button></footer>
    </dialog>
<?php } ?>

<?php if (($datos['titulo'] ?? '') === 'Ventas mayoristas' && empty($datos['datos_reales'])) { ?>
    <dialog class="tc-oc__dialog" data-oc-visual-dialog="condiciones-mayoristas" aria-labelledby="tc-oc-terms-title">
        <header class="tc-oc__dialog-header"><span class="tc-oc__dialog-icon"><i class="bi bi-card-checklist" aria-hidden="true"></i></span><div><h2 id="tc-oc-terms-title">Condiciones comerciales</h2><p>Prepara una propuesta ilustrativa para un cliente mayorista.</p></div><button type="button" class="tc-oc__dialog-close" data-oc-close-modal aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header>
        <div class="tc-oc__dialog-body"><div class="tc-oc__dialog-warning"><i class="bi bi-info-circle" aria-hidden="true"></i><p>Los valores ingresados no se guardan ni representan políticas comerciales vigentes. Su integración y aprobación están pendientes.</p></div><div class="tc-oc__dialog-fields"><label class="tc-oc__dialog-field">Cliente mayorista <b>*</b><input type="text" required maxlength="160" placeholder="Nombre de la empresa"></label><label class="tc-oc__dialog-field">Volumen estimado (unidades) <b>*</b><input type="number" required min="1" step="1" inputmode="numeric" placeholder="Cantidad estimada"></label><label class="tc-oc__dialog-field">Condición de pago propuesta<input type="text" maxlength="100" placeholder="Pendiente de definir"></label><label class="tc-oc__dialog-field">Descuento solicitado (%)<input type="number" min="0" max="100" step="0.01" inputmode="decimal" placeholder="Sin definir"></label><label class="tc-oc__dialog-field">Vigencia propuesta<input type="date"></label><label class="tc-oc__dialog-field tc-oc__dialog-field--wide">Observaciones<textarea rows="3" maxlength="1000" placeholder="Detalles de la propuesta"></textarea></label></div><p class="tc-oc__dialog-error" data-oc-demo-error role="alert" hidden></p><p class="tc-oc__dialog-feedback" data-oc-demo-status role="status" aria-live="polite" hidden></p></div>
        <footer class="tc-oc__dialog-footer"><button type="button" class="tc-oc__button" data-oc-close-modal>Cancelar</button><button type="button" class="tc-oc__button tc-oc__button--primary" data-oc-confirm-demo>Solicitar condición (demostración)</button></footer>
    </dialog>
    <dialog class="tc-oc__dialog" data-oc-detail-dialog aria-labelledby="tc-oc-detail-title">
        <header class="tc-oc__dialog-header"><span class="tc-oc__dialog-icon"><i class="bi bi-eye" aria-hidden="true"></i></span><div><h2 id="tc-oc-detail-title">Consulta de pedido mayorista</h2><p>Detalle ilustrativo del registro de ejemplo seleccionado.</p></div><button type="button" class="tc-oc__dialog-close" data-oc-close-modal aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header>
        <div class="tc-oc__dialog-body"><div class="tc-oc__dialog-warning"><i class="bi bi-info-circle" aria-hidden="true"></i><p>Los registros de esta pantalla son ejemplos y no corresponden a pedidos reales.</p></div><dl class="tc-oc__detail-list" data-oc-detail-fields></dl></div>
        <footer class="tc-oc__dialog-footer"><button type="button" class="tc-oc__button tc-oc__button--primary" data-oc-close-modal>Cerrar</button></footer>
    </dialog>
<?php } ?>

</section>

<?php $__env->startPush('scripts'); ?>
    <script>
        (() => {
            const root = document.querySelector('[data-ventas-diseno]');
            if (!root) {
                return;
            }

            const resetDialog = (dialog) => {
                const status = dialog.querySelector('[data-oc-demo-status]');
                const error = dialog.querySelector('[data-oc-demo-error]');
                const confirm = dialog.querySelector('[data-oc-confirm-demo]');
                if (status) {
                    status.hidden = true;
                }
                if (error) {
                    error.hidden = true;
                }
                if (confirm) {
                    confirm.disabled = false;
                }
                dialog.querySelectorAll('[aria-invalid="true"]').forEach((field) => field.removeAttribute('aria-invalid'));
            };

            root.querySelectorAll('[data-oc-open-modal]').forEach((button) => {
                button.addEventListener('click', () => {
                    const dialog = root.querySelector(`[data-oc-visual-dialog="${button.dataset.ocOpenModal}"]`);
                    if (dialog) {
                        resetDialog(dialog);
                        dialog.showModal();
                    }
                });
            });

            root.querySelectorAll('[data-oc-open-detail]').forEach((button) => {
                button.addEventListener('click', () => {
                    const dialog = root.querySelector('[data-oc-detail-dialog]');
                    const fields = dialog?.querySelector('[data-oc-detail-fields]');
                    const row = button.closest('tr');
                    if (!dialog || !fields || !row) {
                        return;
                    }
                    fields.replaceChildren();
                    const headings = [...root.querySelectorAll('.tc-oc__table-wrap thead th')].map((heading) => heading.textContent.trim()).filter(Boolean);
                    [...row.cells].slice(0, Math.max(0, row.cells.length - 1)).forEach((cell, index) => {
                        if (!headings[index]) {
                            return;
                        }
                        const term = document.createElement('dt');
                        const value = document.createElement('dd');
                        term.textContent = headings[index];
                        value.textContent = cell.textContent.trim();
                        fields.append(term, value);
                    });
                    dialog.showModal();
                });
            });

            root.querySelectorAll('[data-oc-confirm-demo]').forEach((button) => {
                const dialog = button.closest('dialog');
                const error = dialog?.querySelector('[data-oc-demo-error]');
                const status = dialog?.querySelector('[data-oc-demo-status]');
                dialog?.querySelectorAll('[required]').forEach((field) => {
                    field.addEventListener('input', () => field.removeAttribute('aria-invalid'));
                    field.addEventListener('change', () => field.removeAttribute('aria-invalid'));
                });
                button.addEventListener('click', () => {
                    const invalidFields = [...(dialog?.querySelectorAll('[required]') ?? [])].filter((field) => !field.checkValidity());
                    if (invalidFields.length > 0) {
                        invalidFields.forEach((field) => field.setAttribute('aria-invalid', 'true'));
                        error.textContent = 'Completa los campos obligatorios para continuar con esta demostración.';
                        error.hidden = false;
                        status.hidden = true;
                        invalidFields[0].focus();
                        return;
                    }
                    status.textContent = 'Integración pendiente: no se guardó la información ni se ejecutó ninguna operación comercial.';
                    status.hidden = false;
                    error.hidden = true;
                    button.disabled = true;
                });
            });

            root.querySelectorAll('[data-oc-close-modal]').forEach((button) => button.addEventListener('click', () => button.closest('dialog')?.close()));
            root.querySelectorAll('.tc-oc__dialog').forEach((dialog) => dialog.addEventListener('click', (event) => {
                if (event.target === dialog) {
                    dialog.close();
                }
            }));
        })();
    </script>
<?php $__env->stopPush(); ?>
