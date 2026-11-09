<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar compra</title>
</head>
<body>
    
    <form method="post" action="{{ route('management.purchases.update', $purchase) }}" class="form-card compras-form" data-purchase-form>
        @csrf
        @method('PUT')

        {{-- Encabezado: identifica la compra pendiente que será actualizada. --}}
        <header class="compras-form-heading">
            <span class="compras-form-eyebrow">Compras</span>
            <h2>Editar compra pendiente</h2>
            <p>Actualiza los datos antes de aprobar o recibir la compra.</p>
        </header>

        {{-- Datos generales: modifica el proveedor, fecha, referencia y moneda del registro original. --}}
        <section class="compras-form-section">
            <h3>1. Datos generales</h3>
            <div class="compras-form-grid compras-form-grid--two">
                <label>Proveedor
                    <select name="proveedor_id" required>
                        <option value="">Seleccionar proveedor</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" @selected((string) old('proveedor_id', $purchase->proveedor_id) === (string) $supplier->id)>{{ $supplier->nombre }}@if ($supplier->ruc) · {{ $supplier->ruc }}@endif</option>
                        @endforeach
                    </select>
                </label>
                <label>Fecha de compra
                    <input type="date" name="fecha_compra" value="{{ old('fecha_compra', $purchase->fecha_compra?->format('Y-m-d')) }}" required>
                </label>
                <label>Número de referencia
                    <input name="numero_referencia" value="{{ old('numero_referencia', $purchase->numero_referencia) }}" maxlength="100" placeholder="Factura, boleta u orden">
                </label>
                <label>Moneda
                    <select name="moneda" required>
                        <option value="PEN" @selected(old('moneda', $purchase->moneda ?? 'PEN') === 'PEN')>Soles (PEN)</option>
                        <option value="USD" @selected(old('moneda', $purchase->moneda) === 'USD')>Dólares (USD)</option>
                    </select>
                </label>
            </div>
        </section>

        {{-- Detalle: permite actualizar productos, cantidades, costos, descuentos e impuestos. --}}
        <section class="compras-form-section">
            <div class="compras-form-section-heading"><h3>2. Detalle de productos</h3><button class="compras-secondary-button" type="button" data-purchase-add><i class="bi bi-plus-lg" aria-hidden="true"></i> Agregar producto</button></div>
            <div class="compras-items" data-purchase-items>
                @foreach ($purchase->details as $index => $detail)
                    <article class="compras-item" data-purchase-item>
                        <header><strong>Ítem <span data-purchase-item-number>{{ $index + 1 }}</span></strong><button type="button" class="compras-item-remove" data-purchase-remove>Quitar</button></header>
                        <div class="compras-form-grid compras-form-grid--two">
                            <label>Producto
                                <select name="items[{{ $index }}][producto_id]" required data-purchase-product>
                                    <option value="">Seleccionar producto</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}" @selected((string) $detail->producto_id === (string) $product->id)>{{ $product->nombre }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label>Variante
                                <select name="items[{{ $index }}][variante_id]" data-purchase-variant>
                                    <option value="">Sin variante</option>
                                    @foreach ($products as $product)
                                        @foreach ($product->variants->where('estado', 'activo') as $variant)
                                            <option value="{{ $variant->id }}" data-product-id="{{ $product->id }}" @selected((string) ($detail->variante_id ?? '') === (string) $variant->id)>{{ $product->nombre }} · {{ $variant->nombre_color }}@if ($variant->codigo_sku) · SKU {{ $variant->codigo_sku }}@endif</option>
                                        @endforeach
                                    @endforeach
                                </select>
                            </label>
                            <label>Cantidad
                                <input type="number" name="items[{{ $index }}][cantidad]" value="{{ old("items.$index.cantidad", $detail->cantidad) }}" min="1" max="1000" required data-purchase-quantity>
                            </label>
                            <label>Costo unitario
                                <input type="number" name="items[{{ $index }}][costo_unitario]" value="{{ old("items.$index.costo_unitario", $detail->costo_unitario) }}" min="0" step="0.01" required data-purchase-cost>
                            </label>
                            <label>Descuento
                                <input type="number" name="items[{{ $index }}][descuento]" value="{{ old("items.$index.descuento", $detail->descuento ?? 0) }}" min="0" step="0.01" data-purchase-discount>
                            </label>
                            <label>Impuesto
                                <input type="number" name="items[{{ $index }}][impuesto]" value="{{ old("items.$index.impuesto", $detail->impuesto ?? 0) }}" min="0" step="0.01" data-purchase-tax>
                            </label>
                        </div>
                        <p class="compras-item-total">Total de línea: <strong data-purchase-line-total>S/ 0.00</strong></p>
                    </article>
                @endforeach
            </div>
        </section>

        {{-- Resumen: muestra el cálculo previo; el servicio recalcula y valida antes de guardar. --}}
        <section class="compras-form-summary" aria-live="polite">
            <h3>3. Resumen de la compra</h3>
            <p>Subtotal <strong data-purchase-subtotal>S/ 0.00</strong></p>
            <p>Descuentos <strong data-purchase-discounts>S/ 0.00</strong></p>
            <p>Impuestos <strong data-purchase-taxes>S/ 0.00</strong></p>
            <p class="compras-form-total">Total <strong data-purchase-total>S/ 0.00</strong></p>
        </section>

        {{-- Acción: actualiza el registro original mediante PUT y protección CSRF. --}}
        <button class="compras-primary-button compras-form-submit" type="submit">Guardar cambios</button>

        {{-- Plantilla: agrega nuevas líneas sin depender del formulario de registro. --}}
        <template data-purchase-template>
            <article class="compras-item" data-purchase-item>
                <header><strong>Ítem <span data-purchase-item-number>__INDEX_NUMBER__</span></strong><button type="button" class="compras-item-remove" data-purchase-remove>Quitar</button></header>
                <div class="compras-form-grid compras-form-grid--two">
                    <label>Producto<select name="items[__INDEX__][producto_id]" required data-purchase-product><option value="">Seleccionar producto</option>@foreach ($products as $product)<option value="{{ $product->id }}">{{ $product->nombre }}</option>@endforeach</select></label>
                    <label>Variante<select name="items[__INDEX__][variante_id]" data-purchase-variant><option value="">Sin variante</option>@foreach ($products as $product)@foreach ($product->variants->where('estado', 'activo') as $variant)<option value="{{ $variant->id }}" data-product-id="{{ $product->id }}">{{ $product->nombre }} · {{ $variant->nombre_color }}</option>@endforeach @endforeach</select></label>
                    <label>Cantidad<input type="number" name="items[__INDEX__][cantidad]" value="1" min="1" max="1000" required data-purchase-quantity></label>
                    <label>Costo unitario<input type="number" name="items[__INDEX__][costo_unitario]" min="0" step="0.01" required data-purchase-cost></label>
                    <label>Descuento<input type="number" name="items[__INDEX__][descuento]" value="0" min="0" step="0.01" data-purchase-discount></label>
                    <label>Impuesto<input type="number" name="items[__INDEX__][impuesto]" value="0" min="0" step="0.01" data-purchase-tax></label>
                </div>
                <p class="compras-item-total">Total de línea: <strong data-purchase-line-total>S/ 0.00</strong></p>
            </article>
        </template>
    </form>
</body>
</html>