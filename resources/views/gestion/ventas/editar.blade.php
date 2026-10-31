<form class="ventas-modal-form" method="post" action="{{ route('management.sales.update', $order) }}" data-sales-form>
    @csrf
    @method('PUT')

    <div class="ventas-modal-steps" aria-label="Pasos de edición">
        <span class="is-current"><b>1</b> Información</span>
        <i aria-hidden="true"></i>
        <span><b>2</b> Productos</span>
        <i aria-hidden="true"></i>
        <span><b>3</b> Pago y comprobante</span>
    </div>

    <section class="ventas-modal-section">
        <header class="ventas-modal-section-header"><i class="bi bi-pencil-square" aria-hidden="true"></i><h3>Información de la venta</h3></header>
        <div class="ventas-modal-grid ventas-modal-grid--two">
            <label class="ventas-modal-field">
                <span>Cliente <b>*</b></span>
                <select name="cliente_id" required data-sales-customer>
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}" @selected($customer->id === $order->usuario_id)>{{ $customer->nombre }} · {{ $customer->correo }}</option>
                    @endforeach
                </select>
            </label>
            <label class="ventas-modal-field">
                <span>Tipo de venta <b>*</b></span>
                <select name="tipo_venta" required>
                    <option value="tienda_online">Tienda online</option>
                    <option value="fisica">Venta física</option>
                </select>
            </label>
            <label class="ventas-modal-field">
                <span>Fecha de venta <b>*</b></span>
                <input type="date" name="fecha_venta" value="{{ $order->creado_en?->toDateString() ?: now()->toDateString() }}" required>
            </label>
            <label class="ventas-modal-field">
                <span>Vendedor</span>
                <input type="text" value="{{ auth()->user()->nombre }}" readonly>
            </label>
        </div>
        <label class="ventas-modal-field">
            <span>Observaciones</span>
            <textarea name="observaciones" maxlength="500" placeholder="Observaciones adicionales..."></textarea>
        </label>
    </section>

    <section class="ventas-modal-section">
        <header class="ventas-modal-section-header"><i class="bi bi-bag-check" aria-hidden="true"></i><h3>Productos</h3></header>
        <div class="ventas-product-picker">
            <label class="ventas-modal-search"><i class="bi bi-search" aria-hidden="true"></i><span class="visually-hidden">Buscar producto</span><input type="search" placeholder="Buscar producto por nombre, código o categoría..." data-sales-product-search></label>
            <select class="ventas-product-select" aria-label="Producto para agregar" data-sales-product-picker>
                <option value="">Selecciona un producto</option>
                @foreach ($products as $product)
                    <option value="{{ $product->id }}" data-price="{{ number_format((float) $product->effective_price, 2, '.', '') }}" data-stock="{{ $product->existencias }}">{{ $product->nombre }} · S/ {{ number_format((float) $product->effective_price, 2) }} · Stock {{ $product->existencias }}</option>
                @endforeach
            </select>
            <button class="ventas-modal-add" type="button" data-sales-add-product><i class="bi bi-plus-lg" aria-hidden="true"></i> Agregar</button>
        </div>
        <div class="ventas-modal-table-wrap">
            <table class="ventas-modal-table">
                <thead><tr><th>#</th><th>Producto</th><th>Precio (S/)</th><th>Cantidad</th><th>Subtotal (S/)</th><th>Acciones</th></tr></thead>
                <tbody data-sales-lines>
                    @foreach ($order->details as $index => $detail)
                        @php($product = $detail->product)
                        <tr class="ventas-modal-line" data-sales-line data-product-id="{{ $product?->id }}" data-price="{{ number_format((float) ($detail->precio_unitario ?? $product?->effective_price ?? 0), 2, '.', '') }}">
                            <td class="ventas-modal-line-number">{{ $index + 1 }}</td>
                            <td><span class="ventas-modal-product"><span class="ventas-modal-product-image">@if ($product?->url_imagen)<img src="{{ str_starts_with($product->url_imagen, 'http') ? $product->url_imagen : asset(ltrim($product->url_imagen, '/')) }}" alt="">@else<i class="bi bi-box-seam" aria-hidden="true"></i>@endif</span><span><strong>{{ $product?->nombre ?: 'Producto no disponible' }}</strong><small>Stock actual: {{ $product?->existencias ?? 0 }}</small></span></span><input type="hidden" name="items[{{ $index }}][producto_id]" value="{{ $product?->id }}"></td>
                            <td><strong>{{ number_format((float) ($detail->precio_unitario ?? $product?->effective_price ?? 0), 2) }}</strong></td>
                            <td><span class="ventas-quantity"><button type="button" data-sales-quantity="down" aria-label="Disminuir cantidad">−</button><input class="ventas-modal-quantity" type="number" name="items[{{ $index }}][cantidad]" value="{{ $detail->cantidad }}" min="1" max="{{ max(1, (int) ($product?->existencias ?? 1)) }}" data-sales-quantity-input><button type="button" data-sales-quantity="up" aria-label="Aumentar cantidad">+</button></span></td>
                            <td><strong class="ventas-line-subtotal" data-sales-line-subtotal>S/ {{ number_format((float) ($detail->precio_unitario ?? 0) * (int) $detail->cantidad, 2) }}</strong></td>
                            <td><button class="ventas-line-remove" type="button" data-sales-remove aria-label="Quitar producto"><i class="bi bi-trash3" aria-hidden="true"></i></button></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="ventas-modal-summary">
            <span>Subtotal <strong data-sales-subtotal>S/ 0.00</strong></span>
            <span>Descuento <strong>S/ {{ number_format((float) $order->descuento, 2) }}</strong></span>
            <span class="ventas-modal-total">Total <strong data-sales-total>S/ {{ number_format((float) $order->total, 2) }}</strong></span>
        </div>
    </section>

    <footer class="ventas-modal-footer">
        <button class="ventas-modal-clear" type="button" data-sales-clear><i class="bi bi-trash3" aria-hidden="true"></i> Limpiar</button>
        <span></span>
        <a class="ventas-modal-cancel" href="{{ route('management.sales') }}">Cancelar</a>
        <button class="ventas-primary-button" type="submit"><i class="bi bi-check2" aria-hidden="true"></i> Guardar cambios</button>
    </footer>
</form>
