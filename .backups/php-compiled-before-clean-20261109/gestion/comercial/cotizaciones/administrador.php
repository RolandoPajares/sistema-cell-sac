<?php $__env->startSection('title', 'Gestión de cotizaciones'); ?>
<?php $__env->startSection('body_class', 'modulo-cotizaciones'); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $statusClass = static fn (string $status): string => match ($status) {
            'Borrador' => 'draft',
            'Pendiente', 'Enviada' => 'pending',
            'Aprobada' => 'approved',
            'Convertida' => 'converted',
            'Rechazada', 'Vencida' => 'rejected',
            default => 'muted',
        };
    ?>

    <section class="cotizaciones-page">
        <header class="cotizaciones-hero">
            <div>
                <nav class="cotizaciones-breadcrumb" aria-label="Ruta de navegación">
                    <a href="<?php echo e(route('management.dashboard')); ?>">Administración</a>
                    <span aria-hidden="true">/</span>
                    <span>Operaciones comerciales</span>
                    <span aria-hidden="true">/</span>
                    <span aria-current="page">Cotizaciones</span>
                </nav>
                <h1>Gestión de cotizaciones</h1>
                <p>Administra las propuestas comerciales y da seguimiento a cada cotización mayorista.</p>
            </div>
            <div class="cotizaciones-hero-actions">
                <details class="cotizaciones-export-menu">
                    <summary class="cotizaciones-filter-button"><i class="bi bi-download" aria-hidden="true"></i> Exportar</summary>
                    <div class="cotizaciones-export-options">
                        <a href="<?php echo e(route('management.quotations.export.list', ['format' => 'pdf'] + request()->query())); ?>"><i class="bi bi-file-earmark-pdf" aria-hidden="true"></i><span><strong>Exportar PDF</strong><small>Documento imprimible</small></span></a>
                        <a href="<?php echo e(route('management.quotations.export.list', ['format' => 'excel'] + request()->query())); ?>"><i class="bi bi-file-earmark-spreadsheet" aria-hidden="true"></i><span><strong>Exportar Excel</strong><small>Archivo compatible .xlsx</small></span></a>
                        <a href="<?php echo e(route('management.quotations.export.list', ['format' => 'csv'] + request()->query())); ?>"><i class="bi bi-filetype-csv" aria-hidden="true"></i><span><strong>Exportar CSV</strong><small>Datos delimitados</small></span></a>
                    </div>
                </details>
                <button class="cotizaciones-primary-button" type="button" data-admin-dialog-open="nueva-cotizacion" <?php if($customers->isEmpty() || $products->isEmpty()): echo 'disabled'; endif; ?>>
                    <i class="bi bi-plus-lg" aria-hidden="true"></i> Nueva cotización
                </button>
            </div>
        </header>

        <section class="cotizaciones-kpi-grid" aria-label="Resumen de cotizaciones">
            <article class="cotizaciones-kpi cotizaciones-kpi--blue">
                <span class="cotizaciones-kpi-icon"><i class="bi bi-file-earmark-text" aria-hidden="true"></i></span>
                <div><small>Cotizaciones generadas</small><strong><?php echo e(number_format($totalQuotations)); ?></strong><span>Registros activos</span></div>
            </article>
            <article class="cotizaciones-kpi cotizaciones-kpi--orange">
                <span class="cotizaciones-kpi-icon"><i class="bi bi-clock-history" aria-hidden="true"></i></span>
                <div><small>Pendientes</small><strong><?php echo e(number_format($pendingQuotations)); ?></strong><span>Borradores y enviadas</span></div>
            </article>
            <article class="cotizaciones-kpi cotizaciones-kpi--green">
                <span class="cotizaciones-kpi-icon"><i class="bi bi-check-circle" aria-hidden="true"></i></span>
                <div><small>Aprobadas</small><strong><?php echo e(number_format($approvedQuotations)); ?></strong><span>Propuestas aprobadas</span></div>
            </article>
            <article class="cotizaciones-kpi cotizaciones-kpi--purple">
                <span class="cotizaciones-kpi-icon"><i class="bi bi-cash-stack" aria-hidden="true"></i></span>
                <div><small>Monto cotizado</small><strong>S/ <?php echo e(number_format($quotedTotal, 2)); ?></strong><span>Importe de cotizaciones activas</span></div>
            </article>
        </section>

        <form class="cotizaciones-filters" method="GET" action="<?php echo e(route('management.quotations')); ?>">
            <label class="cotizaciones-search">
                <i class="bi bi-search" aria-hidden="true"></i>
                <input type="search" name="buscar" value="<?php echo e($filters['buscar'] ?? ''); ?>" placeholder="Buscar cliente, código o producto..." aria-label="Buscar cotización, cliente o producto">
            </label>
            <label>
                <select name="cliente" aria-label="Cliente mayorista">
                    <option value="">Cliente: todos</option>
                    <?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($customer->id); ?>" <?php if((string) ($filters['cliente'] ?? '') === (string) $customer->id): echo 'selected'; endif; ?>><?php echo e($customer->nombre_contacto); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </label>
            <label>
                <select name="estado" aria-label="Estado de cotización">
                    <option value="">Estado: todos</option>
                    <?php $__currentLoopData = $statusSummary; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($status['label']); ?>" <?php if(($filters['estado'] ?? '') === $status['label']): echo 'selected'; endif; ?>><?php echo e($status['label']); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </label>
            <label class="cotizaciones-date-filter">
                <input type="date" name="desde" value="<?php echo e($filters['desde'] ?? ''); ?>" aria-label="Fecha desde">
            </label>
            <label class="cotizaciones-date-filter">
                <input type="date" name="hasta" value="<?php echo e($filters['hasta'] ?? ''); ?>" aria-label="Fecha hasta">
            </label>
            <button class="cotizaciones-filter-button" type="submit"><i class="bi bi-funnel" aria-hidden="true"></i> Filtrar</button>
            <a class="cotizaciones-clear-button" href="<?php echo e(route('management.quotations')); ?>">Limpiar</a>
        </form>

        <section class="cotizaciones-table-card" aria-labelledby="cotizaciones-list-title">
            <header class="cotizaciones-table-header">
                <div><h2 id="cotizaciones-list-title">Listado de cotizaciones</h2><span><?php echo e(number_format($quotations->total())); ?> registros</span></div>
            </header>
            <div class="cotizaciones-table-scroll">
                <table class="cotizaciones-table">
                    <thead>
                        <tr>
                            <th scope="col">Cotización</th>
                            <th scope="col">Cliente</th>
                            <th scope="col">Tipo</th>
                            <th scope="col">Emisión</th>
                            <th scope="col">Productos</th>
                            <th scope="col">Total</th>
                            <th scope="col">Estado</th>
                            <th scope="col"><span class="visually-hidden">Acciones</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $quotations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $quotation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $status = (string) $quotation->estado;
                                $customerName = $quotation->customer?->nombre_contacto ?? 'Cliente no disponible';
                                $quotationReference = 'COT-' . ($quotation->creado_en?->format('Y') ?? now()->format('Y')) . '-' . str_pad((string) $quotation->id, 4, '0', STR_PAD_LEFT);
                            ?>
                            <tr>
                                <td><strong class="cotizaciones-code">COT-<?php echo e($quotation->creado_en?->format('Y') ?? now()->format('Y')); ?>-<?php echo e(str_pad((string) $quotation->id, 4, '0', STR_PAD_LEFT)); ?></strong></td>
                                <td><span class="cotizaciones-customer"><?php echo e($customerName); ?></span><small><?php echo e($quotation->customer?->documento ?? '—'); ?></small></td>
                                <td><span class="cotizaciones-type">Mayorista</span></td>
                                <td><?php echo e($quotation->creado_en?->format('d/m/Y') ?? '—'); ?></td>
                                <td><?php echo e($quotation->details_count); ?></td>
                                <td><strong>S/ <?php echo e(number_format((float) $quotation->total, 2)); ?></strong></td>
                                <td><span class="cotizaciones-status cotizaciones-status--<?php echo e($statusClass($status)); ?>"><i class="bi bi-circle-fill" aria-hidden="true"></i><?php echo e($status); ?></span></td>
                                <td>
                                    <details class="cotizaciones-row-details">
                                        <summary aria-label="Ver detalle de cotización <?php echo e($quotation->id); ?>" title="Ver detalle"><i class="bi bi-eye" aria-hidden="true"></i></summary>
                                        <div class="cotizaciones-detail-popover">
                                            <strong>Detalle de la cotización</strong>
                                            <span><?php echo e($customerName); ?> · <?php echo e($quotation->creado_en?->format('d/m/Y H:i') ?? 'Fecha no disponible'); ?></span>
                                            <ul>
                                                <?php $__currentLoopData = $quotation->details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <li><span><?php echo e($detail->product?->nombre ?? 'Producto no disponible'); ?> × <?php echo e($detail->cantidad); ?></span><b>S/ <?php echo e(number_format((float) $detail->precio_unitario * (int) $detail->cantidad, 2)); ?></b></li>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </ul>
                                            <?php if($quotation->notas): ?>
                                                <p><?php echo e($quotation->notas); ?></p>
                                            <?php endif; ?>
                                            <?php if(in_array($status, ['Borrador', 'Pendiente'], true) && $puedeEnviar): ?>
                                                <details class="cotizaciones-edit-details">
                                                    <summary>Editar cotización</summary>
                                                    <form method="POST" action="<?php echo e(route('management.quotations.update', $quotation)); ?>" class="cotizaciones-edit-form">
                                                        <?php echo csrf_field(); ?>
                                                        <?php echo method_field('PUT'); ?>
                                                        <label>Cliente
                                                            <select name="cliente_id" required>
                                                                <?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                    <option value="<?php echo e($customer->id); ?>" <?php if((int) $customer->id === (int) $quotation->cliente_id): echo 'selected'; endif; ?>><?php echo e($customer->nombre_contacto); ?> · <?php echo e($customer->documento); ?></option>
                                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                            </select>
                                                        </label>
                                                        <?php $__currentLoopData = $quotation->details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <label>Producto <?php echo e($index + 1); ?>

                                                                <select name="items[<?php echo e($index); ?>][producto_id]" required>
                                                                    <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                        <option value="<?php echo e($product->id); ?>" <?php if((int) $product->id === (int) $detail->producto_id): echo 'selected'; endif; ?>><?php echo e($product->nombre); ?></option>
                                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                                </select>
                                                            </label>
                                                            <label>Cantidad
                                                                <input type="number" name="items[<?php echo e($index); ?>][cantidad]" min="1" max="100000" value="<?php echo e($detail->cantidad); ?>" required>
                                                            </label>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                        <label>Notas<textarea name="notas" maxlength="500" rows="2"><?php echo e($quotation->notas); ?></textarea></label>
                                                        <button type="submit">Guardar cambios</button>
                                                    </form>
                                                </details>
                                            <?php endif; ?>
                                            <div class="cotizaciones-demo-actions" aria-label="Acciones de cotizacion">
    <?php if(in_array($status, ['Borrador', 'Pendiente'], true)): ?>
        <?php if($puedeEnviar && $puedeDescargar && filled($quotation->customer?->correo)): ?>
            <button type="button" data-quotation-action="enviar" data-reference="<?php echo e($quotationReference); ?>" data-customer="<?php echo e($customerName); ?>" data-recipient="<?php echo e($quotation->customer->correo); ?>" data-download="<?php echo e(route('management.quotations.export', $quotation)); ?>" data-send="<?php echo e(route('management.quotations.update.send', $quotation)); ?>">Enviar cotizacion</button>
        <?php else: ?>
            <button type="button" disabled title="Se requieren permisos de actualizacion y exportacion, y un correo valido del cliente">Enviar cotizacion</button>
        <?php endif; ?>
    <?php endif; ?>
    <?php if(in_array($quotation->id, $aprobables, true)): ?>
        <form method="POST" action="<?php echo e(route('management.quotations.approve', $quotation)); ?>" style="display: contents">
            <?php echo csrf_field(); ?>
            <button type="submit">Aprobar cotizacion</button>
        </form>
    <?php endif; ?>
    <?php if($puedeEnviar && in_array($status, ['Borrador', 'Pendiente'], true)): ?>
        <form method="POST" action="<?php echo e(route('management.quotations.deactivate', $quotation)); ?>" style="display: contents" data-confirm="¿Desactivar esta cotización? Se conservará el historial.">
            <?php echo csrf_field(); ?>
            <?php echo method_field('DELETE'); ?>
            <button type="submit">Desactivar cotizacion</button>
        </form>
    <?php endif; ?>
    <?php if($status === 'Aprobada'): ?>
        <?php if($puedeCrearVenta): ?>
            <button type="button" data-quotation-action="convertir" data-reference="<?php echo e($quotationReference); ?>" data-customer="<?php echo e($customerName); ?>" data-convert="<?php echo e(route('management.sales.convert_quotation', $quotation)); ?>" data-site-id="<?php echo e($quotation->sede_id); ?>">Convertir en venta</button>
        <?php else: ?>
            <button type="button" disabled title="Se requiere el permiso de creacion de ventas">Convertir en venta</button>
        <?php endif; ?>
    <?php endif; ?>
</div>
                                        </div>
                                    </details>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td class="cotizaciones-empty" colspan="8"><i class="bi bi-inbox" aria-hidden="true"></i><strong>No hay cotizaciones para mostrar</strong><span>Prueba cambiando los filtros o crea una nueva cotización.</span></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <footer class="cotizaciones-table-footer">
                <span>
                    <?php if($quotations->total() > 0): ?>
                        Mostrando <?php echo e($quotations->firstItem()); ?>–<?php echo e($quotations->lastItem()); ?> de <?php echo e($quotations->total()); ?> cotizaciones
                    <?php else: ?>
                        Sin registros
                    <?php endif; ?>
                </span>
                <?php echo e($quotations->links()); ?>

            </footer>
        </section>

        <div class="cotizaciones-bottom-grid">
            <section class="cotizaciones-summary-card">
                <header><span class="cotizaciones-summary-icon"><i class="bi bi-activity" aria-hidden="true"></i></span><div><h2>Actividad comercial reciente</h2><p>Últimas cotizaciones registradas</p></div></header>
                <?php $__empty_1 = true; $__currentLoopData = $recentQuotations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $recent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="cotizaciones-recent-row">
                        <span class="cotizaciones-recent-dot"></span>
                        <div><strong>COT-<?php echo e($recent->creado_en?->format('Y') ?? now()->format('Y')); ?>-<?php echo e(str_pad((string) $recent->id, 4, '0', STR_PAD_LEFT)); ?> · <?php echo e($recent->customer?->nombre_contacto ?? 'Cliente no disponible'); ?></strong><small><?php echo e($recent->creado_en?->format('d/m/Y') ?? 'Fecha no disponible'); ?> · S/ <?php echo e(number_format((float) $recent->total, 2)); ?></small></div>
                        <span class="cotizaciones-status cotizaciones-status--<?php echo e($statusClass((string) $recent->estado)); ?>"><?php echo e($recent->estado); ?></span>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="cotizaciones-summary-empty">Las cotizaciones recientes aparecerán aquí.</p>
                <?php endif; ?>
            </section>

            <section class="cotizaciones-summary-card">
                <header><span class="cotizaciones-summary-icon cotizaciones-summary-icon--green"><i class="bi bi-pie-chart" aria-hidden="true"></i></span><div><h2>Cotizaciones por estado</h2><p>Distribución de los registros activos</p></div></header>
                <?php $__empty_1 = true; $__currentLoopData = $statusSummary; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="cotizaciones-status-row">
                        <div><span><?php echo e($status['label']); ?></span><strong><?php echo e(number_format($status['count'])); ?></strong></div>
                        <div class="cotizaciones-progress" role="progressbar" aria-label="<?php echo e($status['label']); ?>" aria-valuenow="<?php echo e($status['percent']); ?>" aria-valuemin="0" aria-valuemax="100"><span style="width: <?php echo e($status['percent']); ?>%"></span></div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="cotizaciones-summary-empty">Aún no hay cotizaciones registradas.</p>
                <?php endif; ?>
            </section>
        </div>

        <dialog class="admin-dialog cotizaciones-create-dialog" id="nueva-cotizacion" aria-labelledby="nueva-cotizacion-title">
            <header class="admin-dialog-header">
                <div><h2 id="nueva-cotizacion-title">Nueva cotización</h2><p>Registra una propuesta para un cliente mayorista.</p></div>
                <button class="admin-dialog-close" type="button" data-admin-dialog-close aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
            </header>
            <div class="admin-dialog-body">
                <?php if($customers->isNotEmpty() && $products->isNotEmpty()): ?>
                    <?php echo $__env->make('gestion.comercial.cotizaciones.crear', ['customers' => $customers, 'products' => $products], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php else: ?>
                    <p class="cotizaciones-summary-empty">Para crear una cotización, primero registra un cliente mayorista y al menos un producto activo.</p>
                <?php endif; ?>
            </div>
        </dialog>
        <dialog class="admin-dialog cotizaciones-demo-dialog" id="accion-demostrativa-cotizacion" aria-labelledby="accion-cotizacion-title" data-quotation-dialog>
            <header class="admin-dialog-header">
                <div><h2 id="accion-cotizacion-title" data-quotation-title>Accion de cotizacion</h2><p data-quotation-description>Revisa la accion seleccionada.</p></div>
                <button class="admin-dialog-close" type="button" data-close-quotation-dialog aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
            </header>
            <div class="admin-dialog-body cotizaciones-demo-body">
                <div class="cotizaciones-demo-warning"><i class="bi bi-info-circle" aria-hidden="true"></i><p data-quotation-message></p></div>
                <dl><div><dt>Cotizacion</dt><dd data-quotation-reference>—</dd></div><div><dt>Cliente</dt><dd data-quotation-customer>—</dd></div></dl>
                <div data-manual-send hidden>
                    <a class="cotizaciones-filter-button" data-quotation-download href="#" download>Descargar PDF</a>
                    <a class="cotizaciones-filter-button" data-quotation-mailto href="#">Abrir correo para envio manual</a>
                    <form id="confirmar-envio-cotizacion" method="POST" action="#" data-manual-send-form>
                        <?php echo csrf_field(); ?>
                        <label><input type="checkbox" name="confirmar_envio_manual" value="1" required> Confirmo que envie manualmente el PDF al destinatario indicado.</label>
                    </form>
                </div>
                <form id="convertir-cotizacion" method="POST" action="#" data-conversion-form hidden>
                    <?php echo csrf_field(); ?>
                    <label for="almacen-conversion-cotizacion">Almacén autorizado para la venta</label>
                    <select id="almacen-conversion-cotizacion" name="almacen_id" required>
                        <option value="">Seleccionar almacén...</option>
                        <?php $__currentLoopData = $almacenesVenta; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $almacen): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($almacen->id); ?>" data-site-id="<?php echo e($almacen->sede_id); ?>"><?php echo e($almacen->sede_nombre); ?> · <?php echo e($almacen->nombre); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </form>
                <p class="cotizaciones-demo-feedback" data-quotation-blocked role="status" aria-live="polite" hidden></p>
            </div>
            <footer class="cotizaciones-demo-footer">
                <button class="cotizaciones-filter-button" type="button" data-close-quotation-dialog>Cerrar</button>
                <button class="cotizaciones-primary-button" type="submit" form="confirmar-envio-cotizacion" data-record-manual-send hidden>Registrar envio manual</button>
                <button class="cotizaciones-primary-button" type="submit" form="convertir-cotizacion" data-confirm-conversion hidden>Crear venta en borrador</button>
            </footer>
        </dialog>
    </section>
<?php $__env->stopSection(); ?>
<?php $__env->startPush('scripts'); ?>
    <script>
        (() => {
            const dialog = document.querySelector('[data-quotation-dialog]');
            if (!dialog) {
                return;
            }

            const contenido = {
                enviar: {
                    titulo: 'Enviar cotizacion manualmente',
                    descripcion: 'Descarga el PDF y envialo desde tu correo.',
                    mensaje: 'Technology Cell no envia correos automaticamente. Descarga el PDF, adjuntalo al mensaje y envialo al destinatario mostrado. Despues confirma aqui el envio realizado.',
                },
                convertir: {
                    titulo: 'Convertir cotizacion en venta',
                    descripcion: 'La venta se creara como borrador y no descontara existencias.',
                    mensaje: 'Selecciona el almacen autorizado. La venta quedara pendiente de confirmacion; el inventario se descontara solo al confirmar la venta.',
                },
            };
            const titulo = dialog.querySelector('[data-quotation-title]');
            const descripcion = dialog.querySelector('[data-quotation-description]');
            const mensaje = dialog.querySelector('[data-quotation-message]');
            const referencia = dialog.querySelector('[data-quotation-reference]');
            const cliente = dialog.querySelector('[data-quotation-customer]');
            const envioManual = dialog.querySelector('[data-manual-send]');
            const formulario = dialog.querySelector('[data-manual-send-form]');
            const descarga = dialog.querySelector('[data-quotation-download]');
            const correo = dialog.querySelector('[data-quotation-mailto]');
            const registro = dialog.querySelector('[data-record-manual-send]');
            const conversion = dialog.querySelector('[data-conversion-form]');
            const confirmarConversion = dialog.querySelector('[data-confirm-conversion]');
            const almacenConversion = dialog.querySelector('#almacen-conversion-cotizacion');
            const bloqueo = dialog.querySelector('[data-quotation-blocked]');

            document.querySelectorAll('[data-quotation-action]').forEach((boton) => {
                boton.addEventListener('click', () => {
                    const accion = boton.dataset.quotationAction ?? '';
                    const textos = contenido[accion];
                    if (!textos) {
                        return;
                    }
                    titulo.textContent = textos.titulo;
                    descripcion.textContent = textos.descripcion;
                    mensaje.textContent = textos.mensaje;
                    referencia.textContent = boton.dataset.reference ?? '—';
                    cliente.textContent = boton.dataset.customer ?? 'Cliente no disponible';
                    const esEnvio = accion === 'enviar';
                    envioManual.hidden = !esEnvio;
                    registro.hidden = !esEnvio;
                    conversion.hidden = esEnvio;
                    confirmarConversion.hidden = esEnvio;
                    bloqueo.hidden = true;
                    formulario.action = boton.dataset.send ?? '#';
                    conversion.action = boton.dataset.convert ?? '#';
                    const sedeId = boton.dataset.siteId ?? '';
                    Array.from(almacenConversion.options).forEach((opcion) => {
                        const visible = opcion.value === '' || opcion.dataset.siteId === sedeId;
                        opcion.hidden = !visible;
                        opcion.disabled = !visible;
                    });
                    if (almacenConversion.selectedOptions[0]?.hidden) {
                        almacenConversion.value = '';
                    }
                    descarga.href = boton.dataset.download ?? '#';
                    const asunto = encodeURIComponent(`Cotizacion ${boton.dataset.reference ?? ''}`);
                    const cuerpo = encodeURIComponent(`Adjunto la cotizacion ${boton.dataset.reference ?? ''}.`);
                    correo.href = `mailto:${encodeURIComponent(boton.dataset.recipient ?? '')}?subject=${asunto}&body=${cuerpo}`;
                    formulario.reset();
                    dialog.showModal();
                });
            });

            dialog.querySelectorAll('[data-close-quotation-dialog]').forEach((boton) => boton.addEventListener('click', () => dialog.close()));
            dialog.addEventListener('click', (evento) => {
                if (evento.target === dialog) {
                    dialog.close();
                }
            });
        })();
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.panel.principal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>