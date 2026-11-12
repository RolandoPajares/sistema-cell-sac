
<div class="gestion-pendiente-modal-shell">
    <header class="gestion-pendiente-modal-header">
        <div><span class="gestion-pendiente-modal-icon"><i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i></span><div><span class="gestion-pendiente-eyebrow">Operaciones comerciales</span><h2 id="devoluciones-garantias-crear-title">Nueva solicitud</h2><p>Registra una devolución o garantía y asigna su seguimiento.</p></div></div>
        <button type="button" class="gestion-pendiente-close" data-pending-close aria-label="Cerrar ventana"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
    </header>
{{-- formulario que recopila datos y los envia a la ruta indicada. --}}
    <form method="POST" action="{{ route('management.returns.store') }}" data-pending-backend-form>
{{-- proteccion CSRF que valida el origen del formulario. --}}
        @csrf
        <div class="gestion-pendiente-modal-body">
            {{-- Presenta los datos relacionados que se necesitan para registrar la solicitud. --}}
            <div class="gestion-pendiente-modal-notice"><i class="bi bi-info-circle" aria-hidden="true"></i>
                <div><strong>Seguimiento de la solicitud</strong><p>Selecciona el pedido, cliente y producto relacionados para conservar la trazabilidad del caso.</p></div>
            </div>

            {{-- Captura la identificación, relación comercial, tipo y estado inicial del caso. --}}
            <div class="gestion-pendiente-form-fields">
                <label class="gestion-pendiente-field" for="devolucion-registrar-codigo"><span>Código de solicitud <b>*</b></span><input id="devolucion-registrar-codigo" name="codigo" type="text" maxlength="40" required placeholder="Ej. DEV-2026-0001" data-pending-field="codigo" value="{{ old('codigo') }}"></label>
                <label class="gestion-pendiente-field" for="devolucion-registrar-pedido"><span>Pedido relacionado <b>*</b></span><select id="devolucion-registrar-pedido" name="pedido_id" required data-pending-field="pedido_id"><option value="">Seleccionar pedido</option>@foreach (($module['form_data']['orders'] ?? []) as $order)<option value="{{ $order->id }}" @selected((string) old('pedido_id') === (string) $order->id)>#{{ $order->id }} · {{ $order->user?->nombre ?? 'Cliente' }} · {{ $order->creado_en?->format('d/m/Y') }}</option>@endforeach</select></label>
                <label class="gestion-pendiente-field" for="devolucion-registrar-cliente"><span>Cliente <b>*</b></span><select id="devolucion-registrar-cliente" name="cliente_id" required data-pending-field="cliente_id"><option value="">Seleccionar cliente</option>@foreach (($module['form_data']['customers'] ?? []) as $customer)<option value="{{ $customer->id }}" @selected((string) old('cliente_id') === (string) $customer->id)>{{ $customer->nombre_contacto ?: $customer->razon_social }}</option>@endforeach</select></label>
                <label class="gestion-pendiente-field" for="devolucion-registrar-producto"><span>Producto <small>(opcional)</small></span><select id="devolucion-registrar-producto" name="producto_id" data-pending-field="producto_id"><option value="">Seleccionar producto</option>@foreach (($module['form_data']['products'] ?? []) as $product)<option value="{{ $product->id }}" @selected((string) old('producto_id') === (string) $product->id)>{{ $product->nombre }}</option>@endforeach</select></label>
                <label class="gestion-pendiente-field" for="devolucion-registrar-tipo"><span>Tipo <b>*</b></span><select id="devolucion-registrar-tipo" name="tipo" required data-pending-field="tipo"><option value="">Seleccionar tipo</option>@foreach (['Devolución', 'Garantía', 'Cambio'] as $type)<option value="{{ $type }}" @selected(old('tipo') === $type)>{{ $type }}</option>@endforeach</select></label>
                <label class="gestion-pendiente-field" for="devolucion-registrar-estado"><span>Estado <b>*</b></span><select id="devolucion-registrar-estado" name="estado" required data-pending-field="estado"><option value="">Seleccionar estado</option>@foreach (['Recibida', 'En evaluación', 'Aprobada', 'Rechazada', 'Resuelta'] as $status)<option value="{{ $status }}" @selected(old('estado') === $status)>{{ $status }}</option>@endforeach</select></label>
                <label class="gestion-pendiente-field" for="devolucion-registrar-fecha"><span>Fecha de solicitud <b>*</b></span><input id="devolucion-registrar-fecha" name="fecha_solicitud" type="date" required data-pending-field="fecha_solicitud" value="{{ old('fecha_solicitud', now()->toDateString()) }}"></label>
                <label class="gestion-pendiente-field" for="devolucion-registrar-limite"><span>Fecha límite</span><input id="devolucion-registrar-limite" name="fecha_limite" type="date" data-pending-field="fecha_limite" value="{{ old('fecha_limite') }}"></label>

                {{-- Permite documentar el motivo y la resolución sin alterar reglas empresariales. --}}
                <label class="gestion-pendiente-field gestion-pendiente-field--wide" for="devolucion-registrar-motivo"><span>Motivo <b>*</b></span><textarea id="devolucion-registrar-motivo" name="motivo" rows="4" maxlength="5000" required placeholder="Describe el motivo del caso..." data-pending-field="motivo">{{ old('motivo') }}</textarea></label>
                <label class="gestion-pendiente-field gestion-pendiente-field--wide" for="devolucion-registrar-resolucion"><span>Resolución <small>(opcional)</small></span><textarea id="devolucion-registrar-resolucion" name="resolucion" rows="3" maxlength="5000" placeholder="Registra la decisión o solución aplicada..." data-pending-field="resolucion">{{ old('resolucion') }}</textarea></label>
            </div>
        </div>
        <footer class="gestion-pendiente-modal-footer"><button type="button" class="gestion-pendiente-secondary" data-pending-close>Cancelar</button><button type="submit" class="gestion-pendiente-primary"><i class="bi bi-check2" aria-hidden="true"></i> Registrar solicitud</button></footer>
    </form>
</div>
