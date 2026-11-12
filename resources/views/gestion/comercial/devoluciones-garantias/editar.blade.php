
<div class="gestion-pendiente-modal-shell">
    <header class="gestion-pendiente-modal-header">
        <div><span class="gestion-pendiente-modal-icon"><i class="bi bi-pencil-square" aria-hidden="true"></i></span><div><span class="gestion-pendiente-eyebrow">Operaciones comerciales</span><h2 id="devoluciones-garantias-editar-title">Editar solicitud</h2><p>Actualiza los datos y el estado del caso seleccionado.</p></div></div>
        <button type="button" class="gestion-pendiente-close" data-pending-close aria-label="Cerrar ventana"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
    </header>
{{-- formulario que recopila datos y los envia a la ruta indicada. --}}
    <form method="POST" action="#" data-pending-backend-form>
{{-- proteccion CSRF que valida el origen del formulario. --}}
        @csrf
{{-- metodo HTTP que permite simular PUT, PATCH o DELETE desde el formulario. --}}
        @method('PUT')
        <div class="gestion-pendiente-modal-body">
            {{-- Explica el propósito del formulario de actualización y conserva la trazabilidad. --}}
            <div class="gestion-pendiente-modal-notice"><i class="bi bi-info-circle" aria-hidden="true"></i>
                <div><strong>Actualización de la solicitud</strong><p>Revisa y modifica únicamente la información permitida del caso seleccionado.</p></div>
            </div>

            {{-- Carga los valores del registro mediante los atributos data-pending-field. --}}
            <div class="gestion-pendiente-form-fields">
                <label class="gestion-pendiente-field" for="devolucion-editar-codigo"><span>Código de solicitud <b>*</b></span><input id="devolucion-editar-codigo" name="codigo" type="text" maxlength="40" required placeholder="Ej. DEV-2026-0001" data-pending-field="codigo"></label>
                <label class="gestion-pendiente-field" for="devolucion-editar-pedido"><span>Pedido relacionado <b>*</b></span><select id="devolucion-editar-pedido" name="pedido_id" required data-pending-field="pedido_id"><option value="">Seleccionar pedido</option>@foreach (($module['form_data']['orders'] ?? []) as $order)<option value="{{ $order->id }}">#{{ $order->id }} · {{ $order->user?->nombre ?? 'Cliente' }} · {{ $order->creado_en?->format('d/m/Y') }}</option>@endforeach</select></label>
                <label class="gestion-pendiente-field" for="devolucion-editar-cliente"><span>Cliente <b>*</b></span><select id="devolucion-editar-cliente" name="cliente_id" required data-pending-field="cliente_id"><option value="">Seleccionar cliente</option>@foreach (($module['form_data']['customers'] ?? []) as $customer)<option value="{{ $customer->id }}">{{ $customer->nombre_contacto ?: $customer->razon_social }}</option>@endforeach</select></label>
                <label class="gestion-pendiente-field" for="devolucion-editar-producto"><span>Producto <small>(opcional)</small></span><select id="devolucion-editar-producto" name="producto_id" data-pending-field="producto_id"><option value="">Seleccionar producto</option>@foreach (($module['form_data']['products'] ?? []) as $product)<option value="{{ $product->id }}">{{ $product->nombre }}</option>@endforeach</select></label>
                <label class="gestion-pendiente-field" for="devolucion-editar-tipo"><span>Tipo <b>*</b></span><select id="devolucion-editar-tipo" name="tipo" required data-pending-field="tipo"><option value="">Seleccionar tipo</option>@foreach (['Devolución', 'Garantía', 'Cambio'] as $type)<option value="{{ $type }}">{{ $type }}</option>@endforeach</select></label>
                <label class="gestion-pendiente-field" for="devolucion-editar-estado"><span>Estado <b>*</b></span><select id="devolucion-editar-estado" name="estado" required data-pending-field="estado"><option value="">Seleccionar estado</option>@foreach (['Recibida', 'En evaluación', 'Aprobada', 'Rechazada', 'Resuelta'] as $status)<option value="{{ $status }}">{{ $status }}</option>@endforeach</select></label>
                <label class="gestion-pendiente-field" for="devolucion-editar-fecha"><span>Fecha de solicitud <b>*</b></span><input id="devolucion-editar-fecha" name="fecha_solicitud" type="date" required data-pending-field="fecha_solicitud"></label>
                <label class="gestion-pendiente-field" for="devolucion-editar-limite"><span>Fecha límite</span><input id="devolucion-editar-limite" name="fecha_limite" type="date" data-pending-field="fecha_limite"></label>

                {{-- Permite actualizar la justificación y la solución registrada. --}}
                <label class="gestion-pendiente-field gestion-pendiente-field--wide" for="devolucion-editar-motivo"><span>Motivo <b>*</b></span><textarea id="devolucion-editar-motivo" name="motivo" rows="4" maxlength="5000" required placeholder="Describe el motivo del caso..." data-pending-field="motivo"></textarea></label>
                <label class="gestion-pendiente-field gestion-pendiente-field--wide" for="devolucion-editar-resolucion"><span>Resolución <small>(opcional)</small></span><textarea id="devolucion-editar-resolucion" name="resolucion" rows="3" maxlength="5000" placeholder="Registra la decisión o solución aplicada..." data-pending-field="resolucion"></textarea></label>
            </div>
        </div>
        <footer class="gestion-pendiente-modal-footer"><button type="button" class="gestion-pendiente-secondary" data-pending-close>Cancelar</button><button type="submit" class="gestion-pendiente-primary"><i class="bi bi-save" aria-hidden="true"></i> Guardar cambios</button></footer>
    </form>
</div>
