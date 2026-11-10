<?php $__env->startSection('title', 'Reembolsos'); ?>
<?php $__env->startSection('body_class', 'modulo-reembolsos'); ?>

<?php $__env->startSection('content'); ?>
<?php
$module = $module ?? require config_path('gestion/finanzas/reembolsos.php');
?>

<?php if ($module['connected'] ?? false) { ?>
    <?php
    $moduleKey = $module['key'];
    $filterOptions = $module['filter_options'] ?? ['types' => [], 'statuses' => []];
    $filterValues = $module['filters'] ?? [];
    $exportParameters = array_filter($filterValues, static fn ($value) => $value !== null && $value !== '');
    $formOptions = $module['form_options'] ?? [];
    $canSubmit = $moduleKey === 'pagos'
        ? ($formOptions['documentos'] ?? collect())->isNotEmpty() && ($formOptions['medios'] ?? collect())->isNotEmpty()
        : ($formOptions['devoluciones'] ?? collect())->isNotEmpty() && ($formOptions['cobros'] ?? collect())->isNotEmpty();
    ?>

<section class="admin-finance-module admin-finance-module--<?php echo e($moduleKey); ?>" data-finance-module="<?php echo e($moduleKey); ?>" data-finance-reopen="<?php echo e(old('_form') === 'crear' && $errors->any() ? 'crear' : ''); ?>">
    <header class="admin-finance-header">
        <nav class="admin-finance-breadcrumb" aria-label="Ruta de navegación">
            <span>Administración</span>
            <i class="bi bi-chevron-right" aria-hidden="true"></i>
            <span><?php echo e($module['area']); ?></span>
            <i class="bi bi-chevron-right" aria-hidden="true"></i>
            <strong><?php echo e($module['title']); ?></strong>
        </nav>

        <div class="admin-finance-heading-row">
            <div class="admin-finance-heading">
                <span class="admin-finance-eyebrow"><i class="bi <?php echo e($module['icon']); ?>" aria-hidden="true"></i> <?php echo e($module['area']); ?></span>
                <h1><?php echo e($module['title']); ?></h1>
                <p><?php echo e($module['description']); ?></p>
            </div>
            <div class="admin-finance-actions">
                <?php if ($module['export_route'] ?? false) { ?>
                    <details class="admin-finance-export-menu">
                        <summary class="admin-finance-button admin-finance-button--secondary"><i class="bi bi-download" aria-hidden="true"></i><span><?php echo e($module['export_label'] ?? 'Exportar'); ?></span><i class="bi bi-chevron-down admin-finance-export-chevron" aria-hidden="true"></i></summary>
                        <div class="admin-finance-export-options" role="menu">
                            <a role="menuitem" href="<?php echo e(route($module['export_route'], ['format' => 'excel'] + $exportParameters)); ?>"><i class="bi bi-file-earmark-excel" aria-hidden="true"></i><span><strong>Exportar Excel</strong><small>Archivo compatible .xlsx</small></span></a>
                            <a role="menuitem" href="<?php echo e(route($module['export_route'], ['format' => 'pdf'] + $exportParameters)); ?>"><i class="bi bi-file-earmark-pdf" aria-hidden="true"></i><span><strong>Exportar PDF</strong><small>Documento imprimible</small></span></a>
                            <a role="menuitem" href="<?php echo e(route($module['export_route'], ['format' => 'csv'] + $exportParameters)); ?>"><i class="bi bi-filetype-csv" aria-hidden="true"></i><span><strong>Exportar CSV</strong><small>Datos delimitados</small></span></a>
                        </div>
                    </details>
                <?php } ?>
                <?php if ($module['create_route'] ?? false) { ?>
                    <button class="admin-finance-button admin-finance-button--primary" type="button" data-finance-open="crear">
                        <i class="bi bi-plus-lg" aria-hidden="true"></i> <?php echo e($moduleKey === 'pagos' ? 'Registrar solicitud' : 'Nueva solicitud'); ?>

                    </button>
                <?php } ?>
            </div>
        </div>
    </header>
    <?php if (session('status')) { ?>
        <p class="alert alert-success" role="status"><?php echo e(session('status')); ?></p>
    <?php } ?>

    <section class="admin-finance-kpis" aria-label="Indicadores de <?php echo e($module['title']); ?>">
        <?php $__currentLoopData = $module['kpis'];
    $__env->addLoop($__currentLoopData);
    foreach ($__currentLoopData as $kpi) {
        $__env->incrementLoopIndices();
        $loop = $__env->getLastLoop(); ?>
            <article class="admin-finance-kpi admin-finance-kpi--<?php echo e($kpi['tone'] ?? 'blue'); ?>">
                <span class="admin-finance-kpi-icon"><i class="bi <?php echo e($kpi['icon']); ?>" aria-hidden="true"></i></span>
                <div class="admin-finance-kpi-copy">
                    <small><?php echo e($kpi['label']); ?></small>
                    <strong><?php echo e($kpi['value']); ?></strong>
                    <span><?php echo e($kpi['helper']); ?></span>
                </div>
                <i class="admin-finance-kpi-spark" aria-hidden="true"></i>
            </article>
        <?php } $__env->popLoop();
    $loop = $__env->getLastLoop(); ?>
    </section>

    <form class="admin-finance-filters" method="GET" action="<?php echo e(route($module['route'])); ?>">
        <label class="admin-finance-search">
            <i class="bi bi-search" aria-hidden="true"></i>
            <span class="visually-hidden">Buscar <?php echo e($module['record_plural']); ?></span>
            <input type="search" name="buscar" value="<?php echo e($filterValues['buscar'] ?? ''); ?>" maxlength="120" placeholder="Buscar por código, referencia o documento...">
        </label>
        <?php if (($filterOptions['types'] ?? []) !== []) { ?>
            <label>
                <span class="visually-hidden">Tipo de operación</span>
                <select name="tipo">
                    <option value="">Tipo: todos</option>
                    <?php $__currentLoopData = $filterOptions['types'];
            $__env->addLoop($__currentLoopData);
            foreach ($__currentLoopData as $value => $label) {
                $__env->incrementLoopIndices();
                $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($value); ?>" <?php if (($filterValues['tipo'] ?? '') === $value) {
                            echo 'selected';
                        } ?>><?php echo e($label); ?></option>
                    <?php } $__env->popLoop();
            $loop = $__env->getLastLoop(); ?>
                </select>
            </label>
        <?php } ?>
        <label>
            <span class="visually-hidden">Estado</span>
            <select name="estado">
                <option value="">Estado: todos</option>
                <?php $__currentLoopData = $filterOptions['statuses'];
    $__env->addLoop($__currentLoopData);
    foreach ($__currentLoopData as $status) {
        $__env->incrementLoopIndices();
        $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($status); ?>" <?php if (($filterValues['estado'] ?? '') === $status) {
                        echo 'selected';
                    } ?>><?php echo e($status); ?></option>
                <?php } $__env->popLoop();
    $loop = $__env->getLastLoop(); ?>
            </select>
        </label>
        <label>
            <span class="visually-hidden">Fecha inicial</span>
            <input type="date" name="desde" value="<?php echo e($filterValues['desde'] ?? ''); ?>" aria-label="Fecha inicial">
        </label>
        <label>
            <span class="visually-hidden">Fecha final</span>
            <input type="date" name="hasta" value="<?php echo e($filterValues['hasta'] ?? ''); ?>" aria-label="Fecha final">
        </label>
        <button class="admin-finance-filter-button" type="submit"><i class="bi bi-funnel" aria-hidden="true"></i> Filtrar</button>
        <a class="admin-finance-clear-button" href="<?php echo e(route($module['route'])); ?>">Limpiar filtros</a>
    </form>
    <?php $__currentLoopData = $errors->all();
    $__env->addLoop($__currentLoopData);
    foreach ($__currentLoopData as $error) {
        $__env->incrementLoopIndices();
        $loop = $__env->getLastLoop(); ?>
        <p class="text-danger" role="alert"><?php echo e($error); ?></p>
    <?php } $__env->popLoop();
    $loop = $__env->getLastLoop(); ?>

    <section class="admin-finance-table-card">
        <header class="admin-finance-card-header">
            <div>
                <span class="admin-finance-card-icon"><i class="bi <?php echo e($module['icon']); ?>" aria-hidden="true"></i></span>
                <div>
                    <h2><?php echo e($module['table_title']); ?></h2>
                    <p>Registros consultados desde las tablas financieras y el alcance autorizado.</p>
                </div>
            </div>
            <span class="admin-finance-count"><?php echo e(number_format($module['total_count'])); ?> registros</span>
        </header>
        <div class="admin-finance-table-wrap">
            <table class="admin-finance-table">
                <thead>
                    <tr>
                        <?php $__currentLoopData = $module['columns'];
    $__env->addLoop($__currentLoopData);
    foreach ($__currentLoopData as $column) {
        $__env->incrementLoopIndices();
        $loop = $__env->getLastLoop(); ?>
                            <th><?php echo e($column); ?></th>
                        <?php } $__env->popLoop();
    $loop = $__env->getLastLoop(); ?>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true;
    $__currentLoopData = $module['records'];
    $__env->addLoop($__currentLoopData);
    foreach ($__currentLoopData as $record) {
        $__env->incrementLoopIndices();
        $loop = $__env->getLastLoop();
        $__empty_1 = false; ?>
                        <tr>
                            <?php $__currentLoopData = $record['cells'];
        $__env->addLoop($__currentLoopData);
        foreach ($__currentLoopData as $cellIndex => $cell) {
            $__env->incrementLoopIndices();
            $loop = $__env->getLastLoop(); ?>
                                <td>
                                    <?php if ($cellIndex === 0 && isset($record['detail_url'])) { ?>
                                        <a href="<?php echo e($record['detail_url']); ?>"><?php echo e($cell); ?></a>
                                    <?php } else { ?>
                                        <?php echo e($cell); ?>

                                    <?php } ?>
                                </td>
                            <?php } $__env->popLoop();
        $loop = $__env->getLastLoop(); ?>
                        </tr>
                    <?php } $__env->popLoop();
    $loop = $__env->getLastLoop();
    if ($__empty_1) { ?>
                        <tr>
                            <td colspan="<?php echo e(count($module['columns'])); ?>">
                                <div class="admin-finance-empty">
                                    <span><i class="bi bi-inbox" aria-hidden="true"></i></span>
                                    <strong>No hay <?php echo e($module['record_plural']); ?> para mostrar</strong>
                                    <p>No existen registros que coincidan con los filtros y el alcance autorizados.</p>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <footer class="admin-finance-table-footer">
            <span>
                <?php if ($module['total_count'] > 0) { ?>
                    Mostrando <?php echo e($module['pagination']->firstItem()); ?>–<?php echo e($module['pagination']->lastItem()); ?> de <?php echo e(number_format($module['total_count'])); ?>

                <?php } else { ?>
                    Sin resultados para mostrar
                <?php } ?>
            </span>
            <?php if ($module['pagination']->hasPages()) { ?>
                <nav class="admin-finance-pagination" aria-label="Paginación">
                    <?php if ($module['pagination']->previousPageUrl()) { ?>
                        <a href="<?php echo e($module['pagination']->previousPageUrl()); ?>" rel="prev" aria-label="Página anterior"><i class="bi bi-chevron-left" aria-hidden="true"></i></a>
                    <?php } else { ?>
                        <button type="button" disabled aria-label="Página anterior"><i class="bi bi-chevron-left" aria-hidden="true"></i></button>
                    <?php } ?>
                    <span class="is-active" aria-current="page"><?php echo e($module['pagination']->currentPage()); ?> / <?php echo e($module['pagination']->lastPage()); ?></span>
                    <?php if ($module['pagination']->nextPageUrl()) { ?>
                        <a href="<?php echo e($module['pagination']->nextPageUrl()); ?>" rel="next" aria-label="Página siguiente"><i class="bi bi-chevron-right" aria-hidden="true"></i></a>
                    <?php } else { ?>
                        <button type="button" disabled aria-label="Página siguiente"><i class="bi bi-chevron-right" aria-hidden="true"></i></button>
                    <?php } ?>
                </nav>
            <?php } ?>
        </footer>
    </section>

    <?php if ($module['summary'] !== []) { ?>
        <section class="admin-finance-summary-grid" aria-label="Resumen por estado de <?php echo e($module['title']); ?>">
            <?php $__currentLoopData = $module['summary'];
        $__env->addLoop($__currentLoopData);
        foreach ($__currentLoopData as $summary) {
            $__env->incrementLoopIndices();
            $loop = $__env->getLastLoop(); ?>
                <article class="admin-finance-summary-card">
                    <header>
                        <div><span class="admin-finance-summary-icon admin-finance-summary-icon--<?php echo e($summary['tone']); ?>"><i class="bi <?php echo e($summary['icon']); ?>" aria-hidden="true"></i></span><h2><?php echo e($summary['title']); ?></h2></div>
                    </header>
                    <div class="admin-finance-summary-empty"><i class="bi bi-list-check" aria-hidden="true"></i><strong><?php echo e($summary['text']); ?></strong><span>Estado leído del registro</span></div>
                </article>
            <?php } $__env->popLoop();
        $loop = $__env->getLastLoop(); ?>
        </section>
    <?php } ?>

    <?php if ($module['create_route'] ?? false) { ?>
        <dialog class="admin-finance-dialog" data-finance-dialog="crear" aria-labelledby="<?php echo e($moduleKey); ?>-crear-title">
            <form method="POST" action="<?php echo e(route($module['create_route'])); ?>" class="gestion-pendiente-modal-shell tc-finance-demo">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="_form" value="crear">
                <?php if ($moduleKey === 'pagos') { ?>
                    <input type="hidden" name="token" value="<?php echo e(old('token', $module['request_token'])); ?>">
                <?php } ?>
                <header class="gestion-pendiente-modal-header">
                    <div>
                        <span class="gestion-pendiente-modal-icon"><i class="bi <?php echo e($module['icon']); ?>" aria-hidden="true"></i></span>
                        <div>
                            <span class="gestion-pendiente-eyebrow">Finanzas · <?php echo e($module['title']); ?></span>
                            <h2 id="<?php echo e($moduleKey); ?>-crear-title"><?php echo e($moduleKey === 'pagos' ? 'Registrar pago o cobro pendiente' : 'Solicitud de reembolso'); ?></h2>
                            <p><?php echo e($moduleKey === 'pagos' ? 'El documento y el medio se consultan en la sede activa.' : 'La aprobación y el desembolso son etapas separadas.'); ?></p>
                        </div>
                    </div>
                    <button type="button" class="gestion-pendiente-close" data-finance-close aria-label="Cerrar ventana"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
                </header>
                <div class="gestion-pendiente-modal-body tc-finance-demo__body">
                    <?php if ($moduleKey === 'pagos') { ?>
                        <div class="tc-finance-form-grid">
                            <label class="tc-finance-field">Pedido, venta o compra *
                                <select name="origen" required>
                                    <option value="">Selecciona un documento</option>
                                    <?php $__currentLoopData = $formOptions['documentos'];
                        $__env->addLoop($__currentLoopData);
                        foreach ($__currentLoopData as $document) {
                            $__env->incrementLoopIndices();
                            $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($document['value']); ?>" <?php if (old('origen') === $document['value']) {
                                            echo 'selected';
                                        } ?>><?php echo e($document['label']); ?></option>
                                    <?php } $__env->popLoop();
                        $loop = $__env->getLastLoop(); ?>
                                </select>
                            </label>
                            <label class="tc-finance-field">Medio de pago *
                                <select name="medio_pago_id" required data-finance-method>
                                    <option value="">Selecciona un medio</option>
                                    <?php $__currentLoopData = $formOptions['medios'];
                        $__env->addLoop($__currentLoopData);
                        foreach ($__currentLoopData as $method) {
                            $__env->incrementLoopIndices();
                            $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($method->id); ?>" data-requires-cash="<?php echo e($method->requiere_caja ? '1' : '0'); ?>" data-requires-bank="<?php echo e($method->requiere_banco ? '1' : '0'); ?>" <?php if ((string) old('medio_pago_id') === (string) $method->id) {
                                            echo 'selected';
                                        } ?> <?php if (($method->requiere_caja && $formOptions['sesiones']->isEmpty()) || ($method->requiere_banco && $formOptions['cuentas']->isEmpty())) {
                                            echo 'disabled';
                                        } ?>><?php echo e($method->nombre); ?></option>
                                    <?php } $__env->popLoop();
                        $loop = $__env->getLastLoop(); ?>
                                </select>
                            </label>
                            <label class="tc-finance-field" data-finance-cash-field hidden>Sesión de caja *
                                <select name="sesion_caja_id">
                                    <option value="">Selecciona una sesión abierta</option>
                                    <?php $__currentLoopData = $formOptions['sesiones'];
                        $__env->addLoop($__currentLoopData);
                        foreach ($__currentLoopData as $cashSession) {
                            $__env->incrementLoopIndices();
                            $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($cashSession->id); ?>" <?php if ((string) old('sesion_caja_id') === (string) $cashSession->id) {
                                            echo 'selected';
                                        } ?>><?php echo e($cashSession->caja?->codigo); ?> · Sesión #<?php echo e($cashSession->id); ?></option>
                                    <?php } $__env->popLoop();
                        $loop = $__env->getLastLoop(); ?>
                                </select>
                            </label>
                            <label class="tc-finance-field" data-finance-bank-field hidden>Cuenta bancaria de la empresa *
                                <select name="cuenta_bancaria_id">
                                    <option value="">Selecciona una cuenta</option>
                                    <?php $__currentLoopData = $formOptions['cuentas'];
                        $__env->addLoop($__currentLoopData);
                        foreach ($__currentLoopData as $account) {
                            $__env->incrementLoopIndices();
                            $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($account->id); ?>" <?php if ((string) old('cuenta_bancaria_id') === (string) $account->id) {
                                            echo 'selected';
                                        } ?>><?php echo e($account->codigo); ?> · <?php echo e($account->banco); ?> · <?php echo e($account->moneda); ?></option>
                                    <?php } $__env->popLoop();
                        $loop = $__env->getLastLoop(); ?>
                                </select>
                            </label>
                            <label class="tc-finance-field">Importe solicitado *
                                <input type="number" name="importe" value="<?php echo e(old('importe')); ?>" min="0.01" step="0.01" required placeholder="0.00">
                            </label>
                            <label class="tc-finance-field">Referencia
                                <input type="text" name="referencia" value="<?php echo e(old('referencia')); ?>" maxlength="120" placeholder="Referencia del documento o solicitud" data-finance-reference>
                            </label>
                        </div>
                        <div class="gestion-pendiente-modal-notice"><i class="bi bi-info-circle" aria-hidden="true"></i><div><strong>Registro pendiente</strong><p>Esta acción no confirma el pago, no carga una tarjeta y no modifica el estado del documento.</p></div></div>
                    <?php } else { ?>
                        <div class="tc-finance-form-grid">
                            <label class="tc-finance-field">Devolución resuelta *
                                <select name="devolucion_id" required>
                                    <option value="">Selecciona una devolución</option>
                                    <?php $__currentLoopData = $formOptions['devoluciones'];
                        $__env->addLoop($__currentLoopData);
                        foreach ($__currentLoopData as $return) {
                            $__env->incrementLoopIndices();
                            $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($return->id); ?>" <?php if ((string) old('devolucion_id') === (string) $return->id) {
                                            echo 'selected';
                                        } ?>><?php echo e($return->codigo); ?></option>
                                    <?php } $__env->popLoop();
                        $loop = $__env->getLastLoop(); ?>
                                </select>
                            </label>
                            <label class="tc-finance-field">Cobro original *
                                <select name="pago_id" required>
                                    <option value="">Selecciona un cobro</option>
                                    <?php $__currentLoopData = $formOptions['cobros'];
                        $__env->addLoop($__currentLoopData);
                        foreach ($__currentLoopData as $payment) {
                            $__env->incrementLoopIndices();
                            $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($payment->id); ?>" <?php if ((string) old('pago_id') === (string) $payment->id) {
                                            echo 'selected';
                                        } ?>><?php echo e($payment->codigo); ?> · <?php echo e($payment->moneda); ?> <?php echo e($payment->importe); ?></option>
                                    <?php } $__env->popLoop();
                        $loop = $__env->getLastLoop(); ?>
                                </select>
                            </label>
                            <label class="tc-finance-field">Importe solicitado *
                                <input type="number" name="importe" value="<?php echo e(old('importe')); ?>" min="0.01" step="0.01" required placeholder="0.00">
                            </label>
                        </div>
                        <div class="gestion-pendiente-modal-notice"><i class="bi bi-info-circle" aria-hidden="true"></i><div><strong>Solicitud pendiente</strong><p>Registrar o aprobar la solicitud no ejecuta una devolución de dinero.</p></div></div>
                    <?php } ?>
                    <?php if (! $canSubmit) { ?>
                        <p role="status">No hay documentos y medios elegibles en la sede seleccionada para esta operación.</p>
                    <?php } ?>
                    <?php if (old('_form') === 'crear') { ?>
                        <?php $__currentLoopData = $errors->all();
                        $__env->addLoop($__currentLoopData);
                        foreach ($__currentLoopData as $error) {
                            $__env->incrementLoopIndices();
                            $loop = $__env->getLastLoop(); ?>
                            <p class="text-danger" role="alert"><?php echo e($error); ?></p>
                        <?php } $__env->popLoop();
                        $loop = $__env->getLastLoop(); ?>
                    <?php } ?>
                </div>
                <footer class="gestion-pendiente-modal-footer">
                    <button type="button" class="gestion-pendiente-secondary" data-finance-close>Cancelar</button>
                    <button type="submit" class="gestion-pendiente-primary" <?php if (! $canSubmit) {
                        echo 'disabled';
                    } ?>>
                        <i class="bi bi-check2" aria-hidden="true"></i> Registrar solicitud pendiente
                    </button>
                </footer>
            </form>
        </dialog>
    <?php } ?>
</section>

<?php } elseif (auth()->user()?->can('gestion.seguridad.consultar')) { ?>
    <?php
    $moduleKey = $module['key'];
    $isCrud = ($module['mode'] ?? 'consult') === 'crud';
    $columns = $module['columns'] ?? [];
    $kpis = $module['kpis'] ?? [];
    $recordSingular = $module['record_singular'] ?? 'registro';
    $recordPlural = $module['record_plural'] ?? 'registros';

    $financeContent = [
        'pagos' => [
            'title' => 'Gestión de pagos',
            'description' => 'Supervisa cobros, confirma transacciones y controla la conciliación financiera.',
            'action' => 'Registrar pago',
            'secondary' => 'Conciliar pagos',
            'tableTitle' => 'Registro de pagos',
            'summary' => [
                ['title' => 'Pagos por conciliar', 'icon' => 'bi-hourglass-split', 'tone' => 'orange', 'text' => 'Sin movimientos por verificar.'],
                ['title' => 'Medios de pago', 'icon' => 'bi-bar-chart-line', 'tone' => 'blue', 'text' => 'La distribución aparecerá con pagos reales.'],
                ['title' => 'Actividad financiera reciente', 'icon' => 'bi-activity', 'tone' => 'green', 'text' => 'No hay actividad integrada todavía.'],
            ],
        ],
        'facturacion' => [
            'title' => 'Gestión de facturación',
            'description' => 'Controla comprobantes, revisa emisión y organiza documentos de venta.',
            'action' => 'Emitir comprobante',
            'secondary' => 'Configurar series',
            'tableTitle' => 'Comprobantes de venta',
            'summary' => [
                ['title' => 'Pendientes por gestionar', 'icon' => 'bi-hourglass-split', 'tone' => 'orange', 'text' => 'No hay comprobantes pendientes.'],
                ['title' => 'Resumen de facturación', 'icon' => 'bi-receipt', 'tone' => 'blue', 'text' => 'El resumen aparecerá con ventas emitidas.'],
                ['title' => 'Actividad reciente', 'icon' => 'bi-clock-history', 'tone' => 'green', 'text' => 'No hay emisiones registradas.'],
            ],
        ],
        'reembolsos' => [
            'title' => 'Gestión de reembolsos',
            'description' => 'Evalúa solicitudes, autoriza devoluciones y realiza seguimiento de importes y plazos.',
            'action' => 'Nueva solicitud',
            'secondary' => 'Revisar pendientes',
            'tableTitle' => 'Solicitudes de reembolso',
            'summary' => [
                ['title' => 'Casos prioritarios', 'icon' => 'bi-exclamation-triangle', 'tone' => 'orange', 'text' => 'No hay solicitudes prioritarias.'],
                ['title' => 'Seguimiento de reembolsos', 'icon' => 'bi-clock', 'tone' => 'blue', 'text' => 'El tiempo promedio aparecerá con datos reales.'],
                ['title' => 'Actividad reciente', 'icon' => 'bi-activity', 'tone' => 'green', 'text' => 'No hay reembolsos procesados.'],
            ],
        ],
    ][$moduleKey] ?? [
        'title' => $module['title'],
        'description' => $module['description'],
        'action' => 'Nuevo registro',
        'secondary' => 'Ver resumen',
        'tableTitle' => 'Listado de '.$recordPlural,
        'summary' => [],
    ];
    ?>

<section class="admin-finance-module admin-finance-module--<?php echo e($moduleKey); ?>" data-pending-module="<?php echo e($moduleKey); ?>" data-finance-module="<?php echo e($moduleKey); ?>">
    <header class="admin-finance-header">
        <nav class="admin-finance-breadcrumb" aria-label="Ruta de navegación">
            <span>Administración</span>
            <i class="bi bi-chevron-right" aria-hidden="true"></i>
            <span>Finanzas</span>
            <i class="bi bi-chevron-right" aria-hidden="true"></i>
            <strong><?php echo e($module['title']); ?></strong>
        </nav>

        <div class="admin-finance-heading-row">
            <div class="admin-finance-heading">
                <span class="admin-finance-eyebrow"><i class="bi <?php echo e($module['icon']); ?>" aria-hidden="true"></i> Finanzas</span>
                <h1><?php echo e($financeContent['title']); ?></h1>
                <p><?php echo e($financeContent['description']); ?></p>
            </div>
            <div class="admin-finance-actions">
                <button class="admin-finance-button admin-finance-button--secondary" type="button" disabled title="Exportación no disponible en esta pantalla"><i class="bi bi-download" aria-hidden="true"></i> Exportar</button>
                <button class="admin-finance-button admin-finance-button--secondary" type="button" disabled title="Operación no disponible en esta pantalla"><i class="bi bi-arrow-repeat" aria-hidden="true"></i> <?php echo e($financeContent['secondary']); ?></button>
                <?php if ($isCrud) { ?>
                    <button class="admin-finance-button admin-finance-button--primary" type="button" data-pending-open="crear"><i class="bi bi-plus-lg" aria-hidden="true"></i> <?php echo e($financeContent['action']); ?></button>
                <?php } ?>
            </div>
        </div>
    </header>

    <section class="admin-finance-kpis" aria-label="Indicadores de <?php echo e($financeContent['title']); ?>">
        <?php $__currentLoopData = $kpis;
    $__env->addLoop($__currentLoopData);
    foreach ($__currentLoopData as $kpi) {
        $__env->incrementLoopIndices();
        $loop = $__env->getLastLoop(); ?>
            <article class="admin-finance-kpi admin-finance-kpi--<?php echo e($kpi['tone'] ?? 'blue'); ?>">
                <span class="admin-finance-kpi-icon"><i class="bi <?php echo e($kpi['icon']); ?>" aria-hidden="true"></i></span>
                <div class="admin-finance-kpi-copy">
                    <small><?php echo e($kpi['label']); ?></small>
                    <strong><?php echo e($kpi['value'] ?? '—'); ?></strong>
                    <span><?php echo e($kpi['helper'] ?? 'Sin datos integrados'); ?></span>
                </div>
                <i class="admin-finance-kpi-spark" aria-hidden="true"></i>
            </article>
        <?php } $__env->popLoop();
    $loop = $__env->getLastLoop(); ?>
    </section>

    <form class="admin-finance-filters" method="GET" action="<?php echo e(url()->current()); ?>">
        <label class="admin-finance-search">
            <i class="bi bi-search" aria-hidden="true"></i>
            <span class="visually-hidden">Buscar <?php echo e($recordPlural); ?></span>
            <input type="search" name="buscar" value="<?php echo e($module['filters']['buscar'] ?? request('buscar')); ?>" placeholder="Buscar <?php echo e($recordSingular); ?>, cliente o referencia...">
        </label>
        <label><span class="visually-hidden">Tipo</span><select name="tipo"><option value="">Tipo: Todos</option><?php $__currentLoopData = ($module['filter_options']['types'] ?? []);
    $__env->addLoop($__currentLoopData);
    foreach ($__currentLoopData as $codigo => $nombre) {
        $__env->incrementLoopIndices();
        $loop = $__env->getLastLoop(); ?><option value="<?php echo e($codigo); ?>" <?php if (($module['filters']['tipo'] ?? request('tipo')) === $codigo) {
            echo 'selected';
        } ?>><?php echo e($nombre); ?></option><?php } $__env->popLoop();
    $loop = $__env->getLastLoop(); ?></select></label>
        <label><span class="visually-hidden">Estado</span><select name="estado"><option value="">Estado: Todos</option><?php $__currentLoopData = ($module['filter_options']['statuses'] ?? []);
    $__env->addLoop($__currentLoopData);
    foreach ($__currentLoopData as $estado) {
        $__env->incrementLoopIndices();
        $loop = $__env->getLastLoop(); ?><option value="<?php echo e($estado); ?>" <?php if (($module['filters']['estado'] ?? request('estado')) === $estado) {
            echo 'selected';
        } ?>><?php echo e($estado); ?></option><?php } $__env->popLoop();
    $loop = $__env->getLastLoop(); ?></select></label>
        <label><span class="visually-hidden">Periodo</span><select name="periodo"><option value="">Fecha: Todo el periodo</option><option value="mes" <?php if (($module['filters']['periodo'] ?? request('periodo')) === 'mes') {
            echo 'selected';
        } ?>>Este mes</option><option value="ultimos_30_dias" <?php if (($module['filters']['periodo'] ?? request('periodo')) === 'ultimos_30_dias') {
            echo 'selected';
        } ?>>Últimos 30 días</option><option value="anio" <?php if (($module['filters']['periodo'] ?? request('periodo')) === 'anio') {
            echo 'selected';
        } ?>>Este año</option></select></label>
        <button class="admin-finance-filter-button" type="submit"><i class="bi bi-funnel" aria-hidden="true"></i> Filtrar</button>
        <a class="admin-finance-clear-button" href="<?php echo e(url()->current()); ?>">Limpiar filtros</a>
    </form>
    <span class="admin-finance-filter-status" data-finance-filter-status role="status" aria-live="polite"></span>

    <section class="admin-finance-table-card">
        <header class="admin-finance-card-header">
            <div>
                <span class="admin-finance-card-icon"><i class="bi <?php echo e($module['icon']); ?>" aria-hidden="true"></i></span>
                <div>
                    <h2><?php echo e($financeContent['tableTitle']); ?></h2>
                    <p><?php echo e($module['description']); ?></p>
                </div>
            </div>
            <span class="admin-finance-count"><?php echo e(number_format($module['total_count'] ?? 0)); ?> registros</span>
        </header>
        <div class="admin-finance-table-wrap">
            <table class="admin-finance-table">
                <thead>
                    <tr>
                        <?php $__currentLoopData = $columns;
    $__env->addLoop($__currentLoopData);
    foreach ($__currentLoopData as $column) {
        $__env->incrementLoopIndices();
        $loop = $__env->getLastLoop(); ?>
                            <th><?php echo e($column); ?></th>
                        <?php } $__env->popLoop();
    $loop = $__env->getLastLoop(); ?>
                        <?php if ($isCrud) { ?>
                            <th>Acciones</th>
                        <?php } ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (! empty($module['records'])) { ?>
                        <?php $__currentLoopData = $module['records'];
                        $__env->addLoop($__currentLoopData);
                        foreach ($__currentLoopData as $registro) {
                            $__env->incrementLoopIndices();
                            $loop = $__env->getLastLoop(); ?>
                            <tr><?php $__currentLoopData = $registro['cells'];
                            $__env->addLoop($__currentLoopData);
                            foreach ($__currentLoopData as $celda) {
                                $__env->incrementLoopIndices();
                                $loop = $__env->getLastLoop(); ?><td><?php echo e($celda); ?></td><?php } $__env->popLoop();
                            $loop = $__env->getLastLoop(); ?></tr>
                        <?php } $__env->popLoop();
                        $loop = $__env->getLastLoop(); ?>
                    <?php } else { ?>
                    <tr>
                        <td colspan="<?php echo e(max(1, count($columns) + ($isCrud ? 1 : 0))); ?>">
                            <div class="admin-finance-empty">
                                <span><i class="bi bi-inbox" aria-hidden="true"></i></span>
                                <strong>No hay <?php echo e($recordPlural); ?> para mostrar</strong>
                                <p>La consulta a MariaDB no encontró registros para mostrar.</p>
                                <?php if ($isCrud) { ?>
                                    <button class="admin-finance-empty-button" type="button" data-pending-open="crear"><i class="bi bi-plus-lg" aria-hidden="true"></i> <?php echo e($financeContent['action']); ?></button>
                                <?php } ?>
                            </div>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <footer class="admin-finance-table-footer">
            <span>Mostrando <?php echo e(number_format(($module['pagination'] ?? null)?->firstItem() ?? 0)); ?>–<?php echo e(number_format(($module['pagination'] ?? null)?->lastItem() ?? 0)); ?> de <?php echo e(number_format($module['total_count'] ?? 0)); ?></span>
            <nav class="admin-finance-pagination" aria-label="Paginación">
                <?php if (($module['pagination'] ?? null)?->previousPageUrl()) { ?><a href="<?php echo e($module['pagination']->previousPageUrl()); ?>" aria-label="Página anterior"><i class="bi bi-chevron-left" aria-hidden="true"></i></a><?php } else { ?><button type="button" disabled aria-label="Página anterior"><i class="bi bi-chevron-left" aria-hidden="true"></i></button><?php } ?>
                <button type="button" class="is-active" disabled><?php echo e(($module['pagination'] ?? null)?->currentPage() ?? 1); ?></button>
                <?php if (($module['pagination'] ?? null)?->nextPageUrl()) { ?><a href="<?php echo e($module['pagination']->nextPageUrl()); ?>" aria-label="Página siguiente"><i class="bi bi-chevron-right" aria-hidden="true"></i></a><?php } else { ?><button type="button" disabled aria-label="Página siguiente"><i class="bi bi-chevron-right" aria-hidden="true"></i></button><?php } ?>
            </nav>
        </footer>
    </section>

    <section class="admin-finance-summary-grid" aria-label="Resumen de <?php echo e($financeContent['title']); ?>">
        <?php $__currentLoopData = ($module['summary'] ?? $financeContent['summary']);
    $__env->addLoop($__currentLoopData);
    foreach ($__currentLoopData as $summary) {
        $__env->incrementLoopIndices();
        $loop = $__env->getLastLoop(); ?>
            <article class="admin-finance-summary-card">
                <header>
                    <div><span class="admin-finance-summary-icon admin-finance-summary-icon--<?php echo e($summary['tone']); ?>"><i class="bi <?php echo e($summary['icon']); ?>" aria-hidden="true"></i></span><h2><?php echo e($summary['title']); ?></h2></div>
                    <button type="button" aria-label="Ver resumen: <?php echo e($summary['title']); ?>" disabled title="Solo consulta de resumen"><i class="bi bi-arrow-right" aria-hidden="true"></i></button>
                </header>
                <div class="admin-finance-summary-empty"><i class="bi bi-bar-chart-line" aria-hidden="true"></i><strong><?php echo e($summary['text']); ?></strong><span><?php echo e($module['summary'] ?? false ? 'Datos consultados en MariaDB' : 'Sin datos integrados'); ?></span></div>
            </article>
        <?php } $__env->popLoop();
    $loop = $__env->getLastLoop(); ?>
    </section>

    <?php if ($isCrud) { ?>
        <dialog class="admin-finance-dialog gestion-pendiente-modal" data-pending-modal="crear" aria-labelledby="<?php echo e($moduleKey); ?>-crear-title">
            <?php echo $__env->make('gestion.finanzas.'.$moduleKey.'.crear', ['module' => $module], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </dialog>
        <dialog class="admin-finance-dialog gestion-pendiente-modal" data-pending-modal="editar" aria-labelledby="<?php echo e($moduleKey); ?>-editar-title">
            <?php echo $__env->make('gestion.finanzas.'.$moduleKey.'.editar', ['module' => $module], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </dialog>
        <dialog class="admin-finance-dialog gestion-pendiente-modal" data-pending-modal="ver" aria-labelledby="<?php echo e($moduleKey); ?>-ver-title">
            <div class="gestion-pendiente-modal-shell">
    <header class="gestion-pendiente-modal-header">
        <div><span class="gestion-pendiente-modal-icon"><i class="bi <?php echo e($module['icon']); ?>" aria-hidden="true"></i></span><div><span class="gestion-pendiente-eyebrow"><?php echo e($module['area']); ?></span><h2 id="<?php echo e($module['key']); ?>-ver-title">Ver <?php echo e($module['record_singular'] ?? 'registro'); ?></h2><p>Detalle del registro seleccionado.</p></div></div>
        <button type="button" class="gestion-pendiente-close" data-pending-close aria-label="Cerrar ventana"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
    </header>
    <div class="gestion-pendiente-modal-body">
        <div class="gestion-pendiente-modal-notice"><i class="bi bi-info-circle" aria-hidden="true"></i><div><strong>Vista preparada para integración</strong><p>No hay un registro real seleccionado. El detalle se poblará desde el backend cuando se conecte este módulo.</p></div></div>
        <div class="gestion-pendiente-detail-grid">
            <?php $__currentLoopData = ($module['fields'] ?? []);
        $__env->addLoop($__currentLoopData);
        foreach ($__currentLoopData as $field) {
            $__env->incrementLoopIndices();
            $loop = $__env->getLastLoop(); ?>
                <div><small><?php echo e($field['label']); ?></small><strong>—</strong></div>
            <?php } $__env->popLoop();
        $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
    <footer class="gestion-pendiente-modal-footer"><button type="button" class="gestion-pendiente-secondary" data-pending-close>Cerrar</button></footer>
</div>

        </dialog>
        <dialog class="admin-finance-dialog gestion-pendiente-modal gestion-pendiente-modal--danger" data-pending-modal="eliminar" aria-labelledby="<?php echo e($moduleKey); ?>-eliminar-title">
            <div class="gestion-pendiente-modal-shell">
    <header class="gestion-pendiente-modal-header">
        <div><span class="gestion-pendiente-modal-icon gestion-pendiente-modal-icon--danger"><i class="bi bi-trash3" aria-hidden="true"></i></span><div><span class="gestion-pendiente-eyebrow"><?php echo e($module['area']); ?></span><h2 id="<?php echo e($module['key']); ?>-eliminar-title">Eliminar <?php echo e($module['record_singular'] ?? 'registro'); ?></h2><p>Confirma la acción antes de continuar.</p></div></div>
        <button type="button" class="gestion-pendiente-close" data-pending-close aria-label="Cerrar ventana"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
    </header>
    <div class="gestion-pendiente-modal-body">
        <div class="gestion-pendiente-modal-warning"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i><div><strong>Acción no disponible todavía</strong><p>No se eliminó ningún registro. Esta confirmación es visual y quedará conectada cuando exista una operación autorizada del backend.</p></div></div>
    </div>
    <footer class="gestion-pendiente-modal-footer"><button type="button" class="gestion-pendiente-secondary" data-pending-close>Cancelar</button><button type="button" class="gestion-pendiente-danger" data-pending-notice="La eliminación permanece pendiente de integración y no modificó ningún dato.">Eliminar cuando se integre</button></footer>
</div>

        </dialog>
    <?php } ?>

    <div class="gestion-pendiente-feedback" data-pending-feedback role="status" aria-live="polite" hidden></div>
</section>

<?php } else { ?>
    <?php
    $moduleKey = $module['key'];
    $isCrud = ($module['mode'] ?? 'consult') === 'crud';
    $columns = $module['columns'] ?? [];
    $kpis = $module['kpis'] ?? [];
    $analytics = $module['analytics'] ?? [];
    $fields = $module['fields'] ?? [];
    $recordSingular = $module['record_singular'] ?? 'registro';
    $recordPlural = $module['record_plural'] ?? 'registros';
    ?>

<section class="gestion-pendiente-page gestion-pendiente-page--<?php echo e($moduleKey); ?>" data-pending-module="<?php echo e($moduleKey); ?>">
    <header class="gestion-pendiente-hero">
        <div class="gestion-pendiente-heading">
            <span class="gestion-pendiente-eyebrow"><i class="bi <?php echo e($module['icon']); ?>" aria-hidden="true"></i><?php echo e($module['area']); ?></span>
            <h1><?php echo e($module['title']); ?></h1>
            <p><?php echo e($module['description']); ?></p>
        </div>
        <div class="gestion-pendiente-hero-actions">
            <span class="gestion-pendiente-status"><i class="bi bi-link-45deg" aria-hidden="true"></i> Pendiente de integración</span>
            <?php if ($isCrud) { ?>
                <button class="gestion-pendiente-primary" type="button" data-pending-open="crear">
                    <i class="bi bi-plus-lg" aria-hidden="true"></i>
                    Nuevo <?php echo e($recordSingular); ?>

                </button>
            <?php } else { ?>
                <button class="gestion-pendiente-secondary" type="button" data-pending-notice="La exportación quedará habilitada cuando este módulo tenga una fuente de datos integrada.">
                    <i class="bi bi-download" aria-hidden="true"></i>
                    Exportar consulta
                </button>
            <?php } ?>
        </div>
    </header>

    <div class="gestion-pendiente-kpis">
        <?php $__currentLoopData = $kpis;
    $__env->addLoop($__currentLoopData);
    foreach ($__currentLoopData as $kpi) {
        $__env->incrementLoopIndices();
        $loop = $__env->getLastLoop(); ?>
            <article class="gestion-pendiente-kpi gestion-pendiente-kpi--<?php echo e($kpi['tone'] ?? 'blue'); ?>">
                <span class="gestion-pendiente-kpi-icon"><i class="bi <?php echo e($kpi['icon']); ?>" aria-hidden="true"></i></span>
                <div>
                    <small><?php echo e($kpi['label']); ?></small>
                    <strong><?php echo e($kpi['value'] ?? '—'); ?></strong>
                    <span><?php echo e($kpi['helper'] ?? 'Sin datos integrados'); ?></span>
                </div>
            </article>
        <?php } $__env->popLoop();
    $loop = $__env->getLastLoop(); ?>
    </div>

    <?php if ($analytics !== []) { ?>
        <section class="gestion-pendiente-analytics" aria-label="Análisis visual de <?php echo e($module['title']); ?>">
            <?php $__currentLoopData = $analytics;
        $__env->addLoop($__currentLoopData);
        foreach ($__currentLoopData as $analyticsCard) {
            $__env->incrementLoopIndices();
            $loop = $__env->getLastLoop(); ?>
                <article class="gestion-pendiente-analytics-card">
                    <header class="gestion-pendiente-analytics-header">
                        <div>
                            <span class="gestion-pendiente-analytics-icon"><i class="bi <?php echo e($analyticsCard['icon'] ?? 'bi-bar-chart-line'); ?>" aria-hidden="true"></i></span>
                            <div>
                                <h2><?php echo e($analyticsCard['title']); ?></h2>
                                <p><?php echo e($analyticsCard['description']); ?></p>
                            </div>
                        </div>
                        <span class="gestion-pendiente-chart-status">Sin datos</span>
                    </header>

                    <?php if (($analyticsCard['type'] ?? 'line') === 'donut') { ?>
                        <div class="gestion-pendiente-donut-layout">
                            <div class="gestion-pendiente-donut" role="img" aria-label="Gráfica pendiente de <?php echo e($analyticsCard['title']); ?>">
                                <div><strong>—</strong><span>Sin datos</span></div>
                            </div>
                            <div class="gestion-pendiente-chart-legend">
                                <?php $__currentLoopData = ($analyticsCard['legend'] ?? []);
                        $__env->addLoop($__currentLoopData);
                        foreach ($__currentLoopData as $legendItem) {
                            $__env->incrementLoopIndices();
                            $loop = $__env->getLastLoop(); ?>
                                    <div><span class="gestion-pendiente-legend-dot gestion-pendiente-legend-dot--<?php echo e($loop->index % 4); ?>"></span><span><?php echo e($legendItem); ?></span><strong>—</strong></div>
                                <?php } $__env->popLoop();
                        $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    <?php } else { ?>
                        <div class="gestion-pendiente-line-chart" role="img" aria-label="Gráfica pendiente de <?php echo e($analyticsCard['title']); ?>">
                            <div class="gestion-pendiente-chart-y-axis"><span>—</span><span>—</span><span>—</span><span>—</span></div>
                            <div class="gestion-pendiente-chart-plot">
                                <svg viewBox="0 0 720 220" preserveAspectRatio="none" aria-hidden="true">
                                    <path d="M0 168 C75 158 93 174 150 138 S260 162 316 104 S420 132 478 88 S600 126 720 54" class="gestion-pendiente-placeholder-line"></path>
                                </svg>
                                <div class="gestion-pendiente-chart-empty"><i class="bi bi-bar-chart-line" aria-hidden="true"></i><strong>Sin datos integrados</strong><span>La tendencia aparecerá aquí.</span></div>
                            </div>
                            <div class="gestion-pendiente-chart-x-axis">
                                <?php $__currentLoopData = ($analyticsCard['labels'] ?? ['Periodo 1', 'Periodo 2', 'Periodo 3', 'Periodo 4', 'Periodo 5']);
                        $__env->addLoop($__currentLoopData);
                        foreach ($__currentLoopData as $label) {
                            $__env->incrementLoopIndices();
                            $loop = $__env->getLastLoop(); ?>
                                    <span><?php echo e($label); ?></span>
                                <?php } $__env->popLoop();
                        $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    <?php } ?>
                </article>
            <?php } $__env->popLoop();
        $loop = $__env->getLastLoop(); ?>
        </section>
    <?php } ?>

    <div class="gestion-pendiente-toolbar">
        <label class="gestion-pendiente-search">
            <i class="bi bi-search" aria-hidden="true"></i>
            <span class="visually-hidden">Buscar <?php echo e($recordPlural); ?></span>
            <input type="search" placeholder="Buscar <?php echo e($recordPlural); ?>..." data-pending-search>
        </label>
        <label>
            <span class="visually-hidden">Filtrar por estado</span>
            <select data-pending-filter>
                <option value="">Todos los estados</option>
                <option value="activo">Activos</option>
                <option value="pendiente">Pendientes</option>
                <option value="cerrado">Cerrados</option>
            </select>
        </label>
        <button class="gestion-pendiente-secondary" type="button" data-pending-notice="Los filtros quedarán disponibles cuando exista una fuente de datos para este módulo.">
            <i class="bi bi-arrow-repeat" aria-hidden="true"></i>
            Aplicar filtros
        </button>
    </div>

    <div class="gestion-pendiente-layout">
        <section class="gestion-pendiente-card gestion-pendiente-list-card">
            <header class="gestion-pendiente-card-header">
                <div>
                    <span class="gestion-pendiente-card-icon"><i class="bi <?php echo e($module['icon']); ?>" aria-hidden="true"></i></span>
                    <div>
                        <h2>Listado de <?php echo e($recordPlural); ?></h2>
                        <p>La información aparecerá cuando se conecte este módulo al backend.</p>
                    </div>
                </div>
                <span class="gestion-pendiente-count">Sin registros</span>
            </header>

            <div class="gestion-pendiente-table-wrap">
                <table class="gestion-pendiente-table">
                    <thead>
                        <tr>
                            <?php $__currentLoopData = $columns;
    $__env->addLoop($__currentLoopData);
    foreach ($__currentLoopData as $column) {
        $__env->incrementLoopIndices();
        $loop = $__env->getLastLoop(); ?>
                                <th><?php echo e($column); ?></th>
                            <?php } $__env->popLoop();
    $loop = $__env->getLastLoop(); ?>
                            <?php if ($isCrud) { ?>
                                <th>Acciones</th>
                            <?php } ?>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="<?php echo e(max(1, count($columns) + ($isCrud ? 1 : 0))); ?>">
                                <div class="gestion-pendiente-empty">
                                    <span><i class="bi bi-inbox" aria-hidden="true"></i></span>
                                    <strong>No hay datos disponibles</strong>
                                    <p>Este módulo está preparado visualmente y quedará disponible cuando se integre con datos reales.</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <footer class="gestion-pendiente-card-footer">
                <span>Sin resultados para mostrar</span>
                <div class="gestion-pendiente-pagination" aria-label="Paginación deshabilitada">
                    <button type="button" disabled aria-label="Página anterior"><i class="bi bi-chevron-left" aria-hidden="true"></i></button>
                    <button type="button" class="is-active" disabled>1</button>
                    <button type="button" disabled aria-label="Página siguiente"><i class="bi bi-chevron-right" aria-hidden="true"></i></button>
                </div>
            </footer>
        </section>

        <aside class="gestion-pendiente-card gestion-pendiente-side-card">
            <header class="gestion-pendiente-card-header">
                <div>
                    <span class="gestion-pendiente-card-icon gestion-pendiente-card-icon--purple"><i class="bi bi-stars" aria-hidden="true"></i></span>
                    <div>
                        <h2>Estado del módulo</h2>
                        <p>Preparación frontend</p>
                    </div>
                </div>
            </header>
            <div class="gestion-pendiente-integration">
                <div class="gestion-pendiente-integration-row"><i class="bi bi-check2-circle" aria-hidden="true"></i><span>Interfaz administrativa creada</span></div>
                <div class="gestion-pendiente-integration-row"><i class="bi bi-check2-circle" aria-hidden="true"></i><span>Diseño responsive preparado</span></div>
                <div class="gestion-pendiente-integration-row"><i class="bi bi-hourglass-split" aria-hidden="true"></i><span>Conexión de datos pendiente</span></div>
            </div>
            <?php if ($isCrud) { ?>
                <div class="gestion-pendiente-capabilities">
                    <h3>Interfaces preparadas</h3>
                    <button type="button" data-pending-open="crear"><i class="bi bi-plus-circle" aria-hidden="true"></i> Crear <?php echo e($recordSingular); ?></button>
                    <button type="button" data-pending-open="ver"><i class="bi bi-eye" aria-hidden="true"></i> Ver detalle</button>
                    <button type="button" data-pending-open="editar"><i class="bi bi-pencil-square" aria-hidden="true"></i> Editar registro</button>
                    <button type="button" data-pending-open="eliminar"><i class="bi bi-trash3" aria-hidden="true"></i> Confirmar eliminación</button>
                </div>
            <?php } else { ?>
                <button class="gestion-pendiente-secondary gestion-pendiente-secondary--full" type="button" data-pending-notice="La consulta quedará habilitada cuando exista el origen de datos correspondiente.">
                    <i class="bi bi-bar-chart-line" aria-hidden="true"></i>
                    Preparar consulta
                </button>
            <?php } ?>
        </aside>
    </div>

    <?php if ($isCrud) { ?>
        <dialog class="gestion-pendiente-modal" data-pending-modal="crear" aria-labelledby="<?php echo e($moduleKey); ?>-crear-title">
            <?php echo $__env->make('gestion.'.$moduleKey.'.crear', ['module' => $module], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </dialog>
        <dialog class="gestion-pendiente-modal" data-pending-modal="editar" aria-labelledby="<?php echo e($moduleKey); ?>-editar-title">
            <?php echo $__env->make('gestion.'.$moduleKey.'.editar', ['module' => $module], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </dialog>
        <dialog class="gestion-pendiente-modal" data-pending-modal="ver" aria-labelledby="<?php echo e($moduleKey); ?>-ver-title">
            <div class="gestion-pendiente-modal-shell">
    <header class="gestion-pendiente-modal-header">
        <div><span class="gestion-pendiente-modal-icon"><i class="bi <?php echo e($module['icon']); ?>" aria-hidden="true"></i></span><div><span class="gestion-pendiente-eyebrow"><?php echo e($module['area']); ?></span><h2 id="<?php echo e($module['key']); ?>-ver-title">Ver <?php echo e($module['record_singular'] ?? 'registro'); ?></h2><p>Detalle del registro seleccionado.</p></div></div>
        <button type="button" class="gestion-pendiente-close" data-pending-close aria-label="Cerrar ventana"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
    </header>
    <div class="gestion-pendiente-modal-body">
        <div class="gestion-pendiente-modal-notice"><i class="bi bi-info-circle" aria-hidden="true"></i><div><strong>Vista preparada para integración</strong><p>No hay un registro real seleccionado. El detalle se poblará desde el backend cuando se conecte este módulo.</p></div></div>
        <div class="gestion-pendiente-detail-grid">
            <?php $__currentLoopData = ($module['fields'] ?? []);
        $__env->addLoop($__currentLoopData);
        foreach ($__currentLoopData as $field) {
            $__env->incrementLoopIndices();
            $loop = $__env->getLastLoop(); ?>
                <div><small><?php echo e($field['label']); ?></small><strong>—</strong></div>
            <?php } $__env->popLoop();
        $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
    <footer class="gestion-pendiente-modal-footer"><button type="button" class="gestion-pendiente-secondary" data-pending-close>Cerrar</button></footer>
</div>

        </dialog>
        <dialog class="gestion-pendiente-modal gestion-pendiente-modal--danger" data-pending-modal="eliminar" aria-labelledby="<?php echo e($moduleKey); ?>-eliminar-title">
            <div class="gestion-pendiente-modal-shell">
    <header class="gestion-pendiente-modal-header">
        <div><span class="gestion-pendiente-modal-icon gestion-pendiente-modal-icon--danger"><i class="bi bi-trash3" aria-hidden="true"></i></span><div><span class="gestion-pendiente-eyebrow"><?php echo e($module['area']); ?></span><h2 id="<?php echo e($module['key']); ?>-eliminar-title">Eliminar <?php echo e($module['record_singular'] ?? 'registro'); ?></h2><p>Confirma la acción antes de continuar.</p></div></div>
        <button type="button" class="gestion-pendiente-close" data-pending-close aria-label="Cerrar ventana"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
    </header>
    <div class="gestion-pendiente-modal-body">
        <div class="gestion-pendiente-modal-warning"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i><div><strong>Acción no disponible todavía</strong><p>No se eliminó ningún registro. Esta confirmación es visual y quedará conectada cuando exista una operación autorizada del backend.</p></div></div>
    </div>
    <footer class="gestion-pendiente-modal-footer"><button type="button" class="gestion-pendiente-secondary" data-pending-close>Cancelar</button><button type="button" class="gestion-pendiente-danger" data-pending-notice="La eliminación permanece pendiente de integración y no modificó ningún dato.">Eliminar cuando se integre</button></footer>
</div>

        </dialog>
    <?php } ?>

    <div class="gestion-pendiente-feedback" data-pending-feedback role="status" aria-live="polite" hidden></div>
</section>

<?php } ?>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.panel.principal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>