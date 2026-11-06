@extends('layouts.administracion')

@section('title', 'Transferencias entre almacenes')
@section('body_class', 'modulo-inventario-logistica')

@section('content')
<section class="tc-il" data-il-transfer-manager>
    <header class="tc-il__header">
        <div>
            <span class="tc-il__breadcrumb"><i class="bi bi-house" aria-hidden="true"></i> Inventario y Logística <i class="bi bi-chevron-right" aria-hidden="true"></i> Transferencias</span>
            <h1><i class="bi bi-arrow-left-right" aria-hidden="true"></i>Transferencias entre sedes</h1>
            <p>Solicita, aprueba, despacha y recibe existencias entre almacenes de la empresa.</p>
        </div>
        <div class="tc-il__header-actions">
            <details class="productos-export-menu tc-il__export-menu">
                <summary><i class="bi bi-download" aria-hidden="true"></i><span>Exportar</span><i class="bi bi-chevron-down" aria-hidden="true"></i></summary>
                <div class="productos-export-options">
                    <a href="{{ route('management.inventory.transfers_export', ['format' => 'pdf'] + request()->query()) }}"><i class="bi bi-file-earmark-pdf" aria-hidden="true"></i><span>Exportar PDF<small>Documento imprimible</small></span></a>
                    <a href="{{ route('management.inventory.transfers_export', ['format' => 'excel'] + request()->query()) }}"><i class="bi bi-file-earmark-spreadsheet" aria-hidden="true"></i><span>Exportar Excel<small>Archivo compatible .xlsx</small></span></a>
                    <a href="{{ route('management.inventory.transfers_export', ['format' => 'csv'] + request()->query()) }}"><i class="bi bi-filetype-csv" aria-hidden="true"></i><span>Exportar CSV<small>Datos delimitados</small></span></a>
                </div>
            </details>
            <button type="button" class="tc-il__button tc-il__button--primary" data-transfer-open><i class="bi bi-plus-lg" aria-hidden="true"></i>Nueva solicitud</button>
        </div>
    </header>

    @if (session('status'))
        <div class="tc-il__notice" role="status"><i class="bi bi-check-circle" aria-hidden="true"></i>{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="tc-il__notice" role="alert"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i><span>{{ $errors->first() }}</span></div>
    @endif
    @unless ($supports_difference_reconciliation)
        <div class="tc-il__notice" role="status"><i class="bi bi-info-circle" aria-hidden="true"></i><strong>Conciliación pendiente</strong><span>El esquema aún no registra cantidades devueltas; la migración independiente sigue sin ejecutarse.</span></div>
    @endunless

    <div class="tc-il__metrics">
        <article class="tc-il__metric tc-il__metric--ambar"><span class="tc-il__metric-icon"><i class="bi bi-clock" aria-hidden="true"></i></span><div><small>Solicitadas</small><strong>{{ $metrics['solicitadas'] }}</strong><span>Esperan aprobación</span></div></article>
        <article class="tc-il__metric"><span class="tc-il__metric-icon"><i class="bi bi-truck" aria-hidden="true"></i></span><div><small>En tránsito</small><strong>{{ $metrics['en_transito'] }}</strong><span>Despachadas sin recepción</span></div></article>
        <article class="tc-il__metric tc-il__metric--ambar"><span class="tc-il__metric-icon"><i class="bi bi-box-seam" aria-hidden="true"></i></span><div><small>Recepción parcial</small><strong>{{ $metrics['recepcion_parcial'] }}</strong><span>Requieren completar o conciliar</span></div></article>
        <article class="tc-il__metric tc-il__metric--verde"><span class="tc-il__metric-icon"><i class="bi bi-check-circle" aria-hidden="true"></i></span><div><small>Recibidas, por cerrar</small><strong>{{ $metrics['pendientes_cierre'] }}</strong><span>Listas para cierre</span></div></article>
    </div>

    <form method="GET" action="{{ route('management.inventory.preview.transfers') }}" class="tc-il__filters">
        <label class="tc-il__search"><i class="bi bi-search" aria-hidden="true"></i><input type="search" name="buscar" value="{{ $filters['buscar'] ?? '' }}" placeholder="Buscar por código o producto…" aria-label="Buscar transferencias"></label>
        <div class="tc-il__filter-fields">
            <label><span>Estado</span><select name="estado"><option value="">Todos</option>@foreach (['solicitada' => 'Solicitada', 'aprobada' => 'Aprobada', 'en tránsito' => 'En tránsito', 'recibida parcialmente' => 'Recibida parcialmente', 'recibida totalmente' => 'Recibida totalmente', 'cerrada' => 'Cerrada', 'rechazada' => 'Rechazada', 'cancelada' => 'Cancelada'] as $value => $label)<option value="{{ $value }}" @selected(($filters['estado'] ?? '') === $value)>{{ $label }}</option>@endforeach</select></label>
            <button type="submit" class="tc-il__button tc-il__button--primary"><i class="bi bi-funnel" aria-hidden="true"></i>Aplicar filtros</button>
            <a class="tc-il__button" href="{{ route('management.inventory.preview.transfers') }}">Limpiar</a>
        </div>
    </form>

    <article class="tc-il__card tc-il__table-card">
        <div class="tc-il__card-heading"><h2><i class="bi bi-layers" aria-hidden="true"></i>Transferencias registradas</h2><small>{{ $transfers->total() }} registros</small></div>
        <div class="tc-il__table-wrap">
            <table>
                <thead><tr><th>Código</th><th>Origen</th><th>Destino</th><th>Solicitó</th><th>Fecha</th><th>Unidades solicitadas</th><th>Estado</th><th>Operaciones</th></tr></thead>
                <tbody>
                    @forelse ($transfers as $transfer)
                        @php
                            $tone = match ($transfer->estado) {
                                'solicitada', 'recibida parcialmente' => 'ambar',
                                'recibida totalmente', 'cerrada' => 'verde',
                                'rechazada', 'cancelada' => 'rojo',
                                default => 'azul',
                            };
                            $lineCount = $transfer->detalles->sum(fn ($line) => (int) $line->cantidad_solicitada);
                        @endphp
                        <tr>
                            <td>{{ $transfer->codigo }}</td>
                            <td>{{ $transfer->sedeOrigen?->nombre }} · {{ $transfer->almacenOrigen?->nombre }}</td>
                            <td>{{ $transfer->sedeDestino?->nombre }} · {{ $transfer->almacenDestino?->nombre }}</td>
                            <td>{{ $transfer->solicitante?->nombre ?? '—' }}</td>
                            <td>{{ $transfer->creado_en?->format('d/m/Y H:i') }}</td>
                            <td>{{ $lineCount }}</td>
                            <td><span class="tc-il__pill tc-il__pill--{{ $tone }}">{{ ucfirst($transfer->estado) }}</span></td>
                            <td>
                                <details class="tc-il__transfer-detail">
                                    <summary class="tc-il__button">Detalle / acciones</summary>
                                    <div class="tc-il__transfer-panel">
                                        <h3>Productos y cantidades</h3>
                                        <div class="tc-il__table-wrap"><table><thead><tr><th>Producto</th><th>Variante</th><th>Solicitada</th><th>Enviada</th><th>Recibida</th><th>Devuelta</th><th>Conciliada</th></tr></thead><tbody>
                                            @foreach ($transfer->detalles as $detail)
                                                <tr><td>{{ $detail->producto?->nombre }}</td><td>{{ $detail->variante?->nombre_color ?? '—' }}</td><td>{{ (int) $detail->cantidad_solicitada }}</td><td>{{ (int) $detail->cantidad_enviada }}</td><td>{{ (int) $detail->cantidad_recibida }}</td><td>{{ $supports_return_reconciliation ? (int) $detail->cantidad_devuelta : '—' }}</td><td>{{ $supports_difference_reconciliation ? (int) $detail->cantidad_conciliada : '—' }}</td></tr>
                                            @endforeach
                                        </tbody></table></div>

                                        @if (in_array($transfer->estado, ['solicitada', 'aprobada'], true))
                                            <div class="tc-il__transfer-actions">
                                                @if ($transfer->estado === 'solicitada')
                                                    <form method="POST" action="{{ route('management.inventory.transfer_approve', $transfer) }}">@csrf<input type="hidden" name="clave_idempotencia" value="{{ \Illuminate\Support\Str::uuid() }}"><button class="tc-il__button tc-il__button--primary" type="submit">Aprobar</button></form>
                                                @endif
                                                @foreach ([['reject', 'Rechazar'], ['cancel', 'Cancelar']] as [$action, $label])
                                                    <form method="POST" action="{{ route('management.inventory.transfer_'.$action, $transfer) }}">@csrf<input type="hidden" name="clave_idempotencia" value="{{ \Illuminate\Support\Str::uuid() }}"><input type="text" name="motivo" maxlength="500" required placeholder="Motivo"><button class="tc-il__button" type="submit">{{ $label }}</button></form>
                                                @endforeach
                                            </div>
                                        @endif

                                        @if (in_array($transfer->estado, ['aprobada', 'en tránsito', 'recibida parcialmente'], true))
                                            <div class="tc-il__transfer-actions">
                                                @foreach ($transfer->detalles as $detail)
                                                    @php
                                                        $pendingDispatch = (int) $detail->cantidad_solicitada - (int) $detail->cantidad_enviada;
                                                        $returned = $supports_return_reconciliation ? (int) $detail->cantidad_devuelta : 0;
                                                        $reconciled = $supports_difference_reconciliation ? (int) $detail->cantidad_conciliada : 0;
                                                        $pendingReceipt = (int) $detail->cantidad_enviada - (int) $detail->cantidad_recibida - $returned - $reconciled;
                                                    @endphp
                                                    @if ($pendingDispatch > 0)
                                                        <form method="POST" action="{{ route('management.inventory.transfer_dispatch', $transfer) }}" class="tc-il__transfer-line-form">@csrf<input type="hidden" name="clave_idempotencia" value="{{ \Illuminate\Support\Str::uuid() }}"><input type="hidden" name="items[0][detalle_id]" value="{{ $detail->id }}"><strong>Despachar · {{ $detail->producto?->nombre }}</strong><label>Cantidad<input type="number" name="items[0][cantidad]" min="1" max="{{ $pendingDispatch }}" required></label><label>IMEI / serie, uno por línea si aplica<textarea name="items[0][series]" rows="2" maxlength="4000"></textarea></label><button class="tc-il__button tc-il__button--primary" type="submit">Registrar despacho</button></form>
                                                    @endif
                                                    @if ($pendingReceipt > 0 && $supports_difference_reconciliation)
                                                        <form method="POST" action="{{ route('management.inventory.transfer_reconcile', $transfer) }}" class="tc-il__transfer-line-form">@csrf<input type="hidden" name="clave_idempotencia" value="{{ \Illuminate\Support\Str::uuid() }}"><input type="hidden" name="items[0][detalle_id]" value="{{ $detail->id }}"><strong>Conciliar faltante: {{ $detail->producto?->nombre }}</strong><label>Cantidad con diferencia<input type="number" name="items[0][cantidad]" min="1" max="{{ $pendingReceipt }}" required></label><label>IMEI / serie de la diferencia, uno por linea si aplica<textarea name="items[0][series]" rows="2" maxlength="4000"></textarea></label><label>Motivo documentado<input type="text" name="motivo" minlength="5" maxlength="500" required></label><button class="tc-il__button" type="submit">Registrar conciliacion</button></form>
                                                    @endif
                                                    @if ($pendingReceipt > 0)
                                                        <form method="POST" action="{{ route('management.inventory.transfer_receive', $transfer) }}" class="tc-il__transfer-line-form">@csrf<input type="hidden" name="clave_idempotencia" value="{{ \Illuminate\Support\Str::uuid() }}"><input type="hidden" name="items[0][detalle_id]" value="{{ $detail->id }}"><strong>Recibir · {{ $detail->producto?->nombre }}</strong><label>Cantidad recibida<input type="number" name="items[0][cantidad]" min="1" max="{{ $pendingReceipt }}" required></label><label>IMEI / serie recibida, uno por línea si aplica<textarea name="items[0][series]" rows="2" maxlength="4000"></textarea></label><button class="tc-il__button tc-il__button--primary" type="submit">Registrar recepción</button></form>
                                                        @if ($supports_return_reconciliation)
                                                            <form method="POST" action="{{ route('management.inventory.transfer_return', $transfer) }}" class="tc-il__transfer-line-form">@csrf<input type="hidden" name="clave_idempotencia" value="{{ \Illuminate\Support\Str::uuid() }}"><input type="hidden" name="items[0][detalle_id]" value="{{ $detail->id }}"><strong>Devolver al origen · {{ $detail->producto?->nombre }}</strong><label>Cantidad devuelta<input type="number" name="items[0][cantidad]" min="1" max="{{ $pendingReceipt }}" required></label><label>IMEI / serie devuelta, uno por línea si aplica<textarea name="items[0][series]" rows="2" maxlength="4000"></textarea></label><label>Motivo documentado<input type="text" name="motivo" maxlength="500" required></label><button class="tc-il__button" type="submit">Registrar devolución</button></form>
                                                        @endif
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endif

                                        @if (in_array($transfer->estado, ['en tránsito', 'recibida totalmente', 'recibida parcialmente'], true))
                                            <form method="POST" action="{{ route('management.inventory.transfer_close', $transfer) }}" class="tc-il__transfer-actions">@csrf<input type="hidden" name="clave_idempotencia" value="{{ \Illuminate\Support\Str::uuid() }}"><label>Nota de cierre si hay diferencias<input type="text" name="motivo" minlength="5" maxlength="500"></label><button class="tc-il__button tc-il__button--primary" type="submit">Cerrar transferencia</button></form>
                                        @endif

                                        <h3>Movimientos registrados</h3>
                                        <div class="tc-il__table-wrap"><table><thead><tr><th>Movimiento</th><th>Producto</th><th>Cantidad</th><th>Almacén</th><th>Usuario</th><th>Fecha</th></tr></thead><tbody>
                                            @forelse ($movements->get((string) $transfer->id, collect()) as $movement)
                                                <tr><td>{{ str_replace('transferencia_', '', $movement->documento_tipo) }}</td><td>{{ $movement->product?->nombre }}</td><td>{{ abs((float) $movement->cantidad) }}</td><td>{{ $movement->almacen?->nombre ?? $movement->almacen_id }}</td><td>{{ $movement->user?->nombre ?? '—' }}</td><td>{{ $movement->creado_en?->format('d/m/Y H:i:s') }}</td></tr>
                                            @empty
                                                <tr><td colspan="6">Aún no hay movimientos de inventario para esta transferencia.</td></tr>
                                            @endforelse
                                        </tbody></table></div>
                                    </div>
                                </details>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8">No hay transferencias que coincidan con los filtros.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="tc-il__pagination">{{ $transfers->links() }}</div>
    </article>

    <dialog class="tc-il__dialog" data-transfer-dialog aria-labelledby="transfer-create-title">
        <form method="POST" action="{{ route('management.inventory.transfer_request') }}" class="tc-il__dialog-card" data-transfer-create-form>
            @csrf
            <input type="hidden" name="clave_idempotencia" value="{{ \Illuminate\Support\Str::uuid() }}">
            <header class="tc-il__dialog-header"><div><span class="tc-il__dialog-icon"><i class="bi bi-arrow-left-right" aria-hidden="true"></i></span><div><h2 id="transfer-create-title">Nueva solicitud</h2><p>La solicitud no reserva ni descuenta existencias.</p></div></div><button type="button" class="tc-il__dialog-close" data-transfer-close aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header>
            <div class="tc-il__dialog-body">
                <div class="tc-il__field-grid">
                    <label class="tc-il__field">Almacén de origen *<select name="almacen_origen_id" required><option value="">Selecciona</option>@foreach ($source_warehouses as $warehouse)<option value="{{ $warehouse->id }}">{{ $warehouse->sede_nombre }} · {{ $warehouse->nombre }}</option>@endforeach</select></label>
                    <label class="tc-il__field">Almacén de destino *<select name="almacen_destino_id" required><option value="">Selecciona</option>@foreach ($warehouses as $warehouse)<option value="{{ $warehouse->id }}">{{ $warehouse->sede_nombre }} · {{ $warehouse->nombre }}</option>@endforeach</select></label>
                </div>
                <div class="tc-il__transfer-line-list" data-transfer-lines>
                    <div class="tc-il__transfer-line-form" data-transfer-line>
                        <label class="tc-il__field">Producto / variante *<select data-transfer-product required><option value="">Selecciona un producto</option>@foreach ($products as $product)<option value="{{ $product->producto_id }}:{{ $product->variante_id ?? 'null' }}">{{ $product->marca }} {{ $product->nombre }}{{ $product->nombre_color ? ' · '.$product->nombre_color : '' }}{{ $product->codigo_sku ? ' · '.$product->codigo_sku : '' }}</option>@endforeach</select></label>
                        <label class="tc-il__field">Cantidad *<input type="number" data-transfer-quantity min="1" max="1000000" step="1" value="1" required></label>
                        <input type="hidden" data-transfer-product-id><input type="hidden" data-transfer-variant-id>
                    </div>
                </div>
                <template data-transfer-line-template><div class="tc-il__transfer-line-form" data-transfer-line><label class="tc-il__field">Producto / variante *<select data-transfer-product required><option value="">Selecciona un producto</option>@foreach ($products as $product)<option value="{{ $product->producto_id }}:{{ $product->variante_id ?? 'null' }}">{{ $product->marca }} {{ $product->nombre }}{{ $product->nombre_color ? ' · '.$product->nombre_color : '' }}{{ $product->codigo_sku ? ' · '.$product->codigo_sku : '' }}</option>@endforeach</select></label><label class="tc-il__field">Cantidad *<input type="number" data-transfer-quantity min="1" max="1000000" step="1" value="1" required></label><input type="hidden" data-transfer-product-id><input type="hidden" data-transfer-variant-id><button type="button" class="tc-il__remove-row" data-transfer-remove aria-label="Quitar producto"><i class="bi bi-trash" aria-hidden="true"></i></button></div></template>
                <button type="button" class="tc-il__button" data-transfer-add><i class="bi bi-plus-lg" aria-hidden="true"></i>Agregar producto</button>
                <label class="tc-il__field">Observaciones<textarea name="observaciones" rows="3" maxlength="500"></textarea></label>
            </div>
            <footer class="tc-il__dialog-footer"><button type="button" class="tc-il__button" data-transfer-close>Cancelar</button><button type="submit" class="tc-il__button tc-il__button--primary">Registrar solicitud</button></footer>
        </form>
    </dialog>
</section>
@endsection
