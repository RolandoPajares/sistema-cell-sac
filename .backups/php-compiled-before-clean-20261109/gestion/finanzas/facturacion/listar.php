<?php $__env->startSection('title', 'Facturación'); ?>
<?php $__env->startSection('body_class', 'modulo-facturacion'); ?>

<?php $__env->startSection('content'); ?>

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
                <?php if($module['export_route'] ?? false): ?>
                    <details class="admin-finance-export-menu">
                        <summary class="admin-finance-button admin-finance-button--secondary"><i class="bi bi-download" aria-hidden="true"></i><span><?php echo e($module['export_label'] ?? 'Exportar'); ?></span><i class="bi bi-chevron-down admin-finance-export-chevron" aria-hidden="true"></i></summary>
                        <div class="admin-finance-export-options" role="menu">
                            <a role="menuitem" href="<?php echo e(route($module['export_route'], ['format' => 'excel'] + $exportParameters)); ?>"><i class="bi bi-file-earmark-excel" aria-hidden="true"></i><span><strong>Exportar Excel</strong><small>Archivo compatible .xlsx</small></span></a>
                            <a role="menuitem" href="<?php echo e(route($module['export_route'], ['format' => 'pdf'] + $exportParameters)); ?>"><i class="bi bi-file-earmark-pdf" aria-hidden="true"></i><span><strong>Exportar PDF</strong><small>Documento imprimible</small></span></a>
                            <a role="menuitem" href="<?php echo e(route($module['export_route'], ['format' => 'csv'] + $exportParameters)); ?>"><i class="bi bi-filetype-csv" aria-hidden="true"></i><span><strong>Exportar CSV</strong><small>Datos delimitados</small></span></a>
                        </div>
                    </details>
                <?php endif; ?>
                <?php if($module['create_route'] ?? false): ?>
                    <button class="admin-finance-button admin-finance-button--primary" type="button" data-finance-open="crear">
                        <i class="bi bi-plus-lg" aria-hidden="true"></i> <?php echo e($moduleKey === 'pagos' ? 'Registrar solicitud' : 'Nueva solicitud'); ?>

                    </button>
                <?php endif; ?>
            </div>
        </div>
    </header>
    <?php if(session('status')): ?>
        <p class="alert alert-success" role="status"><?php echo e(session('status')); ?></p>
    <?php endif; ?>

    <section class="admin-finance-kpis" aria-label="Indicadores de <?php echo e($module['title']); ?>">
        <?php $__currentLoopData = $module['kpis']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kpi): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <article class="admin-finance-kpi admin-finance-kpi--<?php echo e($kpi['tone'] ?? 'blue'); ?>">
                <span class="admin-finance-kpi-icon"><i class="bi <?php echo e($kpi['icon']); ?>" aria-hidden="true"></i></span>
                <div class="admin-finance-kpi-copy">
                    <small><?php echo e($kpi['label']); ?></small>
                    <strong><?php echo e($kpi['value']); ?></strong>
                    <span><?php echo e($kpi['helper']); ?></span>
                </div>
                <i class="admin-finance-kpi-spark" aria-hidden="true"></i>
            </article>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </section>

    <form class="admin-finance-filters" method="GET" action="<?php echo e(route($module['route'])); ?>">
        <label class="admin-finance-search">
            <i class="bi bi-search" aria-hidden="true"></i>
            <span class="visually-hidden">Buscar <?php echo e($module['record_plural']); ?></span>
            <input type="search" name="buscar" value="<?php echo e($filterValues['buscar'] ?? ''); ?>" maxlength="120" placeholder="Buscar por código, referencia o documento...">
        </label>
        <?php if(($filterOptions['types'] ?? []) !== []): ?>
            <label>
                <span class="visually-hidden">Tipo de operación</span>
                <select name="tipo">
                    <option value="">Tipo: todos</option>
                    <?php $__currentLoopData = $filterOptions['types']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($value); ?>" <?php if(($filterValues['tipo'] ?? '') === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </label>
        <?php endif; ?>
        <label>
            <span class="visually-hidden">Estado</span>
            <select name="estado">
                <option value="">Estado: todos</option>
                <?php $__currentLoopData = $filterOptions['statuses']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($status); ?>" <?php if(($filterValues['estado'] ?? '') === $status): echo 'selected'; endif; ?>><?php echo e($status); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <p class="text-danger" role="alert"><?php echo e($error); ?></p>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

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
                        <?php $__currentLoopData = $module['columns']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $column): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <th><?php echo e($column); ?></th>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $module['records']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <?php $__currentLoopData = $record['cells']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cellIndex => $cell): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <td>
                                    <?php if($cellIndex === 0 && isset($record['detail_url'])): ?>
                                        <a href="<?php echo e($record['detail_url']); ?>"><?php echo e($cell); ?></a>
                                    <?php else: ?>
                                        <?php echo e($cell); ?>

                                    <?php endif; ?>
                                </td>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="<?php echo e(count($module['columns'])); ?>">
                                <div class="admin-finance-empty">
                                    <span><i class="bi bi-inbox" aria-hidden="true"></i></span>
                                    <strong>No hay <?php echo e($module['record_plural']); ?> para mostrar</strong>
                                    <p>No existen registros que coincidan con los filtros y el alcance autorizados.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <footer class="admin-finance-table-footer">
            <span>
                <?php if($module['total_count'] > 0): ?>
                    Mostrando <?php echo e($module['pagination']->firstItem()); ?>–<?php echo e($module['pagination']->lastItem()); ?> de <?php echo e(number_format($module['total_count'])); ?>

                <?php else: ?>
                    Sin resultados para mostrar
                <?php endif; ?>
            </span>
            <?php if($module['pagination']->hasPages()): ?>
                <nav class="admin-finance-pagination" aria-label="Paginación">
                    <?php if($module['pagination']->previousPageUrl()): ?>
                        <a href="<?php echo e($module['pagination']->previousPageUrl()); ?>" rel="prev" aria-label="Página anterior"><i class="bi bi-chevron-left" aria-hidden="true"></i></a>
                    <?php else: ?>
                        <button type="button" disabled aria-label="Página anterior"><i class="bi bi-chevron-left" aria-hidden="true"></i></button>
                    <?php endif; ?>
                    <span class="is-active" aria-current="page"><?php echo e($module['pagination']->currentPage()); ?> / <?php echo e($module['pagination']->lastPage()); ?></span>
                    <?php if($module['pagination']->nextPageUrl()): ?>
                        <a href="<?php echo e($module['pagination']->nextPageUrl()); ?>" rel="next" aria-label="Página siguiente"><i class="bi bi-chevron-right" aria-hidden="true"></i></a>
                    <?php else: ?>
                        <button type="button" disabled aria-label="Página siguiente"><i class="bi bi-chevron-right" aria-hidden="true"></i></button>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        </footer>
    </section>

    <?php if($module['summary'] !== []): ?>
        <section class="admin-finance-summary-grid" aria-label="Resumen por estado de <?php echo e($module['title']); ?>">
            <?php $__currentLoopData = $module['summary']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $summary): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <article class="admin-finance-summary-card">
                    <header>
                        <div><span class="admin-finance-summary-icon admin-finance-summary-icon--<?php echo e($summary['tone']); ?>"><i class="bi <?php echo e($summary['icon']); ?>" aria-hidden="true"></i></span><h2><?php echo e($summary['title']); ?></h2></div>
                    </header>
                    <div class="admin-finance-summary-empty"><i class="bi bi-list-check" aria-hidden="true"></i><strong><?php echo e($summary['text']); ?></strong><span>Estado leído del registro</span></div>
                </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </section>
    <?php endif; ?>

    <?php if($module['create_route'] ?? false): ?>
        <dialog class="admin-finance-dialog" data-finance-dialog="crear" aria-labelledby="<?php echo e($moduleKey); ?>-crear-title">
            <form method="POST" action="<?php echo e(route($module['create_route'])); ?>" class="gestion-pendiente-modal-shell tc-finance-demo">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="_form" value="crear">
                <?php if($moduleKey === 'pagos'): ?>
                    <input type="hidden" name="token" value="<?php echo e(old('token', $module['request_token'])); ?>">
                <?php endif; ?>
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
                    <?php if($moduleKey === 'pagos'): ?>
                        <div class="tc-finance-form-grid">
                            <label class="tc-finance-field">Pedido, venta o compra *
                                <select name="origen" required>
                                    <option value="">Selecciona un documento</option>
                                    <?php $__currentLoopData = $formOptions['documentos']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $document): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($document['value']); ?>" <?php if(old('origen') === $document['value']): echo 'selected'; endif; ?>><?php echo e($document['label']); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </label>
                            <label class="tc-finance-field">Medio de pago *
                                <select name="medio_pago_id" required data-finance-method>
                                    <option value="">Selecciona un medio</option>
                                    <?php $__currentLoopData = $formOptions['medios']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $method): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($method->id); ?>" data-requires-cash="<?php echo e($method->requiere_caja ? '1' : '0'); ?>" data-requires-bank="<?php echo e($method->requiere_banco ? '1' : '0'); ?>" <?php if((string) old('medio_pago_id') === (string) $method->id): echo 'selected'; endif; ?> <?php if(($method->requiere_caja && $formOptions['sesiones']->isEmpty()) || ($method->requiere_banco && $formOptions['cuentas']->isEmpty())): echo 'disabled'; endif; ?>><?php echo e($method->nombre); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </label>
                            <label class="tc-finance-field" data-finance-cash-field hidden>Sesión de caja *
                                <select name="sesion_caja_id">
                                    <option value="">Selecciona una sesión abierta</option>
                                    <?php $__currentLoopData = $formOptions['sesiones']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cashSession): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($cashSession->id); ?>" <?php if((string) old('sesion_caja_id') === (string) $cashSession->id): echo 'selected'; endif; ?>><?php echo e($cashSession->caja?->codigo); ?> · Sesión #<?php echo e($cashSession->id); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </label>
                            <label class="tc-finance-field" data-finance-bank-field hidden>Cuenta bancaria de la empresa *
                                <select name="cuenta_bancaria_id">
                                    <option value="">Selecciona una cuenta</option>
                                    <?php $__currentLoopData = $formOptions['cuentas']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $account): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($account->id); ?>" <?php if((string) old('cuenta_bancaria_id') === (string) $account->id): echo 'selected'; endif; ?>><?php echo e($account->codigo); ?> · <?php echo e($account->banco); ?> · <?php echo e($account->moneda); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                    <?php else: ?>
                        <div class="tc-finance-form-grid">
                            <label class="tc-finance-field">Devolución resuelta *
                                <select name="devolucion_id" required>
                                    <option value="">Selecciona una devolución</option>
                                    <?php $__currentLoopData = $formOptions['devoluciones']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $return): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($return->id); ?>" <?php if((string) old('devolucion_id') === (string) $return->id): echo 'selected'; endif; ?>><?php echo e($return->codigo); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </label>
                            <label class="tc-finance-field">Cobro original *
                                <select name="pago_id" required>
                                    <option value="">Selecciona un cobro</option>
                                    <?php $__currentLoopData = $formOptions['cobros']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($payment->id); ?>" <?php if((string) old('pago_id') === (string) $payment->id): echo 'selected'; endif; ?>><?php echo e($payment->codigo); ?> · <?php echo e($payment->moneda); ?> <?php echo e($payment->importe); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </label>
                            <label class="tc-finance-field">Importe solicitado *
                                <input type="number" name="importe" value="<?php echo e(old('importe')); ?>" min="0.01" step="0.01" required placeholder="0.00">
                            </label>
                        </div>
                        <div class="gestion-pendiente-modal-notice"><i class="bi bi-info-circle" aria-hidden="true"></i><div><strong>Solicitud pendiente</strong><p>Registrar o aprobar la solicitud no ejecuta una devolución de dinero.</p></div></div>
                    <?php endif; ?>
                    <?php if(! $canSubmit): ?>
                        <p role="status">No hay documentos y medios elegibles en la sede seleccionada para esta operación.</p>
                    <?php endif; ?>
                    <?php if(old('_form') === 'crear'): ?>
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <p class="text-danger" role="alert"><?php echo e($error); ?></p>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endif; ?>
                </div>
                <footer class="gestion-pendiente-modal-footer">
                    <button type="button" class="gestion-pendiente-secondary" data-finance-close>Cancelar</button>
                    <button type="submit" class="gestion-pendiente-primary" <?php if(! $canSubmit): echo 'disabled'; endif; ?>>
                        <i class="bi bi-check2" aria-hidden="true"></i> Registrar solicitud pendiente
                    </button>
                </footer>
            </form>
        </dialog>
    <?php endif; ?>
</section>


<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.panel.principal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>