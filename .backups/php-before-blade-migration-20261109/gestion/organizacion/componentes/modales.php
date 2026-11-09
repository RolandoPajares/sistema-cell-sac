<dialog class="tc-org__dialog" data-org-dialog aria-labelledby="tc-org-modal-title">
    <div class="tc-org__dialog-card">
        <header class="tc-org__dialog-header">
            <span class="tc-org__dialog-icon"><i class="bi bi-<?php echo e(['empresa' => 'buildings', 'sedes' => 'shop', 'almacenes' => 'box-seam', 'personal' => 'people', 'responsables' => 'person-check'][$tipo]); ?>" aria-hidden="true"></i></span>
            <div><small><?php echo e(in_array($tipo, ['empresa', 'sedes', 'almacenes'], true) ? 'Organización · Datos de MariaDB' : 'Organización y Sedes · Vista de diseño'); ?></small><h2 id="tc-org-modal-title" data-org-modal-title></h2><p data-org-modal-description></p></div>
            <button type="button" class="tc-org__dialog-close" data-org-close aria-label="Cerrar formulario"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
        </header>
        <form class="tc-org__dialog-form" data-org-form <?php if (in_array($tipo, ['empresa', 'almacenes', 'sedes', 'responsables'], true)) { ?> data-org-real-form method="post" action="<?php echo e($tipo === 'almacenes' ? route('management.company_settings.warehouses.store') : ($tipo === 'sedes' ? route('management.company_settings.sites.store') : ($tipo === 'responsables' ? route('management.company_settings.responsibles.store') : route('management.company_settings.data.update')))); ?>" <?php } else { ?> novalidate <?php } ?>>
            <div class="tc-org__dialog-steps" data-org-steps aria-label="Pasos del formulario"><span data-org-step-indicator="1">1 · Información</span><span data-org-step-indicator="2">2 · Detalles</span><span data-org-step-indicator="3">3 · Confirmación</span></div>
            <div class="tc-org__dialog-body">
                <?php if (in_array($tipo, ['empresa', 'almacenes', 'sedes', 'responsables'], true)) { ?>
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="_method" value="POST" data-org-method>
                <?php } ?>
                <?php if ($tipo === 'empresa') { ?>
                    <section data-org-step="1"><h3><i class="bi bi-buildings"></i> Identidad empresarial</h3><div class="tc-org__field-grid">
                        <label>Razón social <b>*</b><input name="razon_social" required maxlength="180" value="<?php echo e(old('razon_social', $empresa?->razon_social)); ?>"></label>
                        <label>Nombre comercial<input name="nombre_comercial" maxlength="180" value="<?php echo e(old('nombre_comercial', $empresa?->nombre_comercial)); ?>"></label>
                    </div></section>
                    <section data-org-step="2" hidden><h3><i class="bi bi-file-earmark-text"></i> Identificación y moneda</h3><div class="tc-org__field-grid">
                        <label>RUC<input name="ruc" inputmode="numeric" pattern="[0-9]{11}" maxlength="11" value="<?php echo e(old('ruc', $empresa?->ruc)); ?>"><small>Si se registra, debe contener 11 dígitos.</small></label>
                        <label>Moneda base <b>*</b><input name="moneda_base" required pattern="[A-Z]{3}" maxlength="3" value="<?php echo e(old('moneda_base', $empresa?->moneda_base)); ?>"><small>Código de tres letras guardado para la empresa.</small></label>
                    </div></section>
                    <section data-org-step="3" hidden><h3><i class="bi bi-check2-circle"></i> Confirmación</h3><p class="tc-org__dialog-note">Se actualizarán únicamente las columnas empresariales vigentes y el cambio quedará registrado en auditoría.</p></section>
                <?php } elseif ($tipo === 'sedes') { ?>
                    <section data-org-step="1"><h3><i class="bi bi-shop"></i> Identificación de la sede</h3><div class="tc-org__field-grid">
                        <label>Nombre de la sede <b>*</b><input name="nombre" required maxlength="140" value="<?php echo e(old('nombre')); ?>"></label>
                        <label>Código de sede <b>*</b><input name="codigo" required maxlength="30" pattern="[A-Za-z0-9_-]+" value="<?php echo e(old('codigo')); ?>"></label>
                        <label>Zona horaria <b>*</b><input name="zona_horaria" required maxlength="64" value="<?php echo e(old('zona_horaria', 'America/Lima')); ?>"><small>Debe ser una zona horaria reconocida por PHP.</small></label>
                    </div></section>
                    <section data-org-step="2" hidden><h3><i class="bi bi-geo-alt"></i> Ubicación y contacto</h3><div class="tc-org__field-grid">
                        <label>Dirección<input name="direccion" maxlength="255" value="<?php echo e(old('direccion')); ?>"></label>
                        <label>Teléfono<input name="telefono" type="tel" maxlength="30" value="<?php echo e(old('telefono')); ?>"></label>
                    </div></section>
                <?php } elseif ($tipo === 'almacenes') { ?>
                    <section data-org-step="1"><h3><i class="bi bi-box-seam"></i> Identificación del almacén</h3><div class="tc-org__field-grid">
                        <label>Nombre del almacén <b>*</b><input name="nombre" required maxlength="140" value="<?php echo e(old('nombre')); ?>"></label>
                        <label>Código <b>*</b><input name="codigo" required maxlength="30" pattern="[A-Za-z0-9_-]+" value="<?php echo e(old('codigo')); ?>"></label>
                        <label>Sede <b>*</b><select name="sede_id" required><option value="">Seleccionar sede…</option><?php $__currentLoopData = $sedesActivas;
                    $__env->addLoop($__currentLoopData);
                    foreach ($__currentLoopData as $sede) {
                        $__env->incrementLoopIndices();
                        $loop = $__env->getLastLoop(); ?><option value="<?php echo e($sede->id); ?>" <?php if ((string) old('sede_id') === (string) $sede->id) {
                            echo 'selected';
                        } ?>><?php echo e($sede->nombre); ?> · <?php echo e($sede->codigo); ?></option><?php } $__env->popLoop();
                    $loop = $__env->getLastLoop(); ?></select></label>
                        <label>Tipo de almacén <b>*</b><input name="tipo" required maxlength="30" value="<?php echo e(old('tipo', 'general')); ?>"></label>
                        <label class="tc-org__field-check"><input type="hidden" name="permite_venta" value="0"><input name="permite_venta" type="checkbox" value="1" checked> Este almacén permite ventas</label>
                    </div></section>
                <?php } elseif ($tipo === 'personal') { ?>
                    <section data-org-step="1"><h3><i class="bi bi-person-check"></i> Usuario existente</h3><div class="tc-org__field-grid">
                        <label>Colaborador <b>*</b><select name="colaborador" required><option value="">Seleccionar usuario existente…</option><option>Carlos Mendoza</option><option>Lucía Ramírez</option><option>José Vargas</option><option>Paola Aguilar</option><option>Diego Rojas</option></select><small>Lista ilustrativa; se cargará desde la gestión de usuarios.</small></label>
                        <label>Sede <b>*</b><select name="sede" required><option value="">Seleccionar sede…</option><option>Sede Central</option><option>Sede Norte</option><option>Sede Sur</option><option>Sede Este</option><option>Sede Oeste</option></select></label>
                        <label>Área<input name="area" maxlength="100"></label><label>Cargo<input name="cargo" maxlength="100"></label>
                        <label>Fecha de inicio <b>*</b><input name="inicio" type="date" required></label><label>Fecha de fin<input name="fin" type="date"></label>
                    </div></section>
                <?php } else { ?>
                    <section data-org-step="1"><h3><i class="bi bi-diagram-3"></i> Recurso y responsable</h3><div class="tc-org__field-grid">
                        <label>Tipo de recurso <b>*</b><select name="tipo_recurso" required><option value="">Seleccionar…</option><option value="sede">Sede</option><option value="almacen">Almacén</option><option value="" disabled>Área (sin entidad en el esquema)</option></select></label>
                        <label data-org-resource-field="almacen_id">Almacén<select name="almacen_id"><option value="">Seleccionar almacén…</option><?php $__currentLoopData = $almacenes ?? [];
                    $__env->addLoop($__currentLoopData);
                    foreach ($__currentLoopData as $almacen) {
                        $__env->incrementLoopIndices();
                        $loop = $__env->getLastLoop(); ?><option value="<?php echo e($almacen->id); ?>" data-sede-id="<?php echo e($almacen->sede_id); ?>"><?php echo e($almacen->nombre); ?> · <?php echo e($almacen->codigo); ?></option><?php } $__env->popLoop();
                    $loop = $__env->getLastLoop(); ?></select></label>
                        <label data-org-resource-field="area_nombre">Área<input name="area_nombre" maxlength="160" placeholder="No disponible en el esquema" disabled></label>
                        <label>Sede <b>*</b><select name="sede_id" required><option value="">Seleccionar sede…</option><?php $__currentLoopData = $sedes ?? [];
                    $__env->addLoop($__currentLoopData);
                    foreach ($__currentLoopData as $sede) {
                        $__env->incrementLoopIndices();
                        $loop = $__env->getLastLoop(); ?><option value="<?php echo e($sede->id); ?>"><?php echo e($sede->nombre); ?> · <?php echo e($sede->codigo); ?></option><?php } $__env->popLoop();
                    $loop = $__env->getLastLoop(); ?></select></label>
                        <label>Nuevo responsable <b>*</b><select name="responsable_usuario_id" required><option value="">Seleccionar usuario existente…</option><?php $__currentLoopData = $usuarios ?? [];
                    $__env->addLoop($__currentLoopData);
                    foreach ($__currentLoopData as $usuario) {
                        $__env->incrementLoopIndices();
                        $loop = $__env->getLastLoop(); ?><option value="<?php echo e($usuario->id); ?>" data-sede-id="<?php echo e($usuario->sede_id); ?>"><?php echo e($usuario->nombre); ?></option><?php } $__env->popLoop();
                    $loop = $__env->getLastLoop(); ?></select></label>
                        <label>Cargo <b>*</b><input name="cargo" required maxlength="100"></label>
                    </div></section>
                    <section data-org-step="2" hidden><h3><i class="bi bi-calendar-check"></i> Vigencia y relevo</h3><div class="tc-org__field-grid">
                        <label>Fecha de inicio <b>*</b><input name="inicio" type="date" required></label><label>Fecha de fin<input name="fin" type="date"></label>
                        <label>Motivo <b>*</b><textarea name="motivo" required minlength="10" maxlength="500" rows="3" placeholder="Explica el motivo de la asignación o relevo"></textarea></label>
                        <label>Responsable anterior<input name="anterior" readonly placeholder="Se mostrará al seleccionar una asignación"></label>
                    </div><p class="tc-org__dialog-note">El relevo conserva la vigencia anterior y enlaza la asignación precedente en el historial.</p></section>
                <?php } ?>
            </div>
            <p class="tc-org__dialog-feedback" data-org-feedback role="status" aria-live="polite"></p>
            <footer class="tc-org__dialog-footer"><button type="button" class="tc-org__button" data-org-close>Cancelar</button><button type="button" class="tc-org__button" data-org-prev hidden>Anterior</button><button type="button" class="tc-org__button tc-org__button--primary" data-org-next>Siguiente</button><button type="<?php echo e(in_array($tipo, ['empresa', 'almacenes', 'sedes', 'responsables'], true) ? 'submit' : 'button'); ?>" class="tc-org__button tc-org__button--primary" data-org-save hidden>Guardar</button></footer>
        </form>
    </div>
</dialog>
<dialog class="tc-org__dialog tc-org__dialog--small" data-org-detail-dialog aria-labelledby="tc-org-detail-title"><div class="tc-org__dialog-card"><header class="tc-org__dialog-header"><span class="tc-org__dialog-icon"><i class="bi bi-eye" aria-hidden="true"></i></span><div><small><?php echo e(in_array($tipo, ['almacenes', 'sedes'], true) ? 'Organización · Registro almacenado' : 'Organización y Sedes · Datos ilustrativos'); ?></small><h2 id="tc-org-detail-title">Detalle</h2><p>Consulta del registro mostrado en esta pantalla.</p></div><button type="button" class="tc-org__dialog-close" data-org-close aria-label="Cerrar detalle"><i class="bi bi-x-lg"></i></button></header><div class="tc-org__dialog-body"><dl class="tc-org__detail-list" data-org-detail-list></dl></div><footer class="tc-org__dialog-footer"><button type="button" class="tc-org__button tc-org__button--primary" data-org-close>Cerrar</button></footer></div></dialog>
<?php if (in_array($tipo, ['almacenes', 'sedes'], true)) { ?>
    <dialog class="tc-org__dialog tc-org__dialog--small" data-org-confirm-dialog aria-labelledby="tc-org-confirm-title"><div class="tc-org__dialog-card"><header class="tc-org__dialog-header"><span class="tc-org__dialog-icon"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i></span><div><small>Organización · <?php echo e($tipo === 'almacenes' ? 'Almacenes registrados' : 'Sedes registradas'); ?></small><h2 id="tc-org-confirm-title">Confirmar cambio de estado</h2><p data-org-confirm-description></p></div><button type="button" class="tc-org__dialog-close" data-org-close aria-label="Cerrar confirmación"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header><form method="post" action="" data-org-status-form data-org-real-status-form><?php echo csrf_field(); ?><input type="hidden" name="_method" value="PATCH"><input type="hidden" name="<?php echo e($tipo === 'almacenes' ? 'activo' : 'activa'); ?>" value="0" data-org-status-value><div class="tc-org__dialog-body"><label class="tc-org__confirm-label">Motivo del cambio <b>*</b><textarea name="motivo" required minlength="10" maxlength="500" rows="3" placeholder="Explica el motivo del cambio de estado"></textarea></label></div><footer class="tc-org__dialog-footer"><button type="button" class="tc-org__button" data-org-close>Cancelar</button><button type="submit" class="tc-org__button tc-org__button--primary">Confirmar</button></footer></form></div></dialog>
<?php } else { ?>
    <dialog class="tc-org__dialog tc-org__dialog--small" data-org-confirm-dialog aria-labelledby="tc-org-confirm-title"><div class="tc-org__dialog-card"><header class="tc-org__dialog-header"><span class="tc-org__dialog-icon"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i></span><div><small>Organización y Sedes · Vista de diseño</small><h2 id="tc-org-confirm-title">Confirmar cambio de estado</h2><p data-org-confirm-description></p></div><button type="button" class="tc-org__dialog-close" data-org-close aria-label="Cerrar confirmación"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header><div class="tc-org__dialog-body"><label class="tc-org__confirm-label">Motivo del cambio <b>*</b><textarea required minlength="10" maxlength="500" rows="3" data-org-confirm-reason placeholder="Explica el motivo del cambio de estado"></textarea></label><p class="tc-org__dialog-note">Esta confirmación es visual; no modificará el estado del registro.</p><p class="tc-org__dialog-feedback" data-org-confirm-feedback role="status" aria-live="polite"></p></div><footer class="tc-org__dialog-footer"><button type="button" class="tc-org__button" data-org-close>Cancelar</button><button type="button" class="tc-org__button tc-org__button--primary" data-org-confirm>Confirmar</button></footer></div></dialog>
<?php } ?>
