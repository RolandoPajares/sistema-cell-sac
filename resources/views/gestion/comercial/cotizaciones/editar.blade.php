{{--
    Formulario de edición de una cotización existente.
    Se mantienen los identificadores de las líneas para que el servidor revalide
    y reconstruya los detalles dentro de una transacción sin crear otra cotización.
--}}
@extends('layouts.panel.principal')

@section('title', 'Editar cotización')
@section('body_class', 'modulo-cotizaciones')

@section('content')
<main class="cotizaciones-page">
    <header class="cotizaciones-hero">
        <div>
            <nav class="cotizaciones-breadcrumb" aria-label="Ruta de navegación">
                <a href="{{ route('management.quotations') }}">Cotizaciones</a>
                <span aria-hidden="true">/</span>
                <span aria-current="page">Editar cotización</span>
            </nav>
            <h1>Editar cotización COT-{{ $quotation->id }}</h1>
            <p>Solo se editan cotizaciones activas en borrador o pendientes.</p>
        </div>
    </header>

    {{-- El formulario conserva cliente, líneas y observaciones cargados desde MariaDB. --}}
    <form method="POST" action="{{ route('management.quotations.update', $quotation) }}" class="cotizaciones-form" data-quotation-form>
        @csrf
        @method('PUT')

        {{-- Cliente seleccionado; el servicio vuelve a comprobar empresa, tipo y estado. --}}
        <fieldset class="cotizaciones-form-section">
            <legend>1. Datos del cliente</legend>
            <label for="cliente_id">Cliente</label>
            <select id="cliente_id" name="cliente_id" required>
                @foreach ($customers as $customer)
                    <option value="{{ $customer->id }}" @selected((int) old('cliente_id', $quotation->cliente_id) === (int) $customer->id)>
                        {{ $customer->nombre_contacto ?: $customer->razon_social }} · {{ $customer->documento }}
                    </option>
                @endforeach
            </select>
            @error('cliente_id')<small class="form-error">{{ $message }}</small>@enderror
        </fieldset>

        {{-- Detalles existentes editables; agregar y quitar filas no afecta otras cotizaciones. --}}
        <fieldset class="cotizaciones-form-section">
            <legend>2. Productos o servicios</legend>
            <div class="cotizaciones-items" data-quotation-items>
                @foreach ($quotation->details as $index => $detail)
                    <div class="cotizaciones-item" data-quotation-item>
                        <label>Producto
                            <select name="items[{{ $index }}][producto_id]" required data-product-select>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}" data-price="{{ $product->effective_price }}" @selected((int) old("items.$index.producto_id", $detail->producto_id) === (int) $product->id)>{{ $product->nombre }} · S/ {{ number_format((float) $product->effective_price, 2) }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label>Cantidad
                            <input type="number" name="items[{{ $index }}][cantidad]" min="1" max="100000" value="{{ old("items.$index.cantidad", $detail->cantidad) }}" required data-quantity>
                        </label>
                        <output data-line-total>S/ {{ number_format((float) $detail->precio_unitario * (int) $detail->cantidad, 2) }}</output>
                        <button type="button" class="cotizaciones-remove-item" data-remove-item>Quitar</button>
                    </div>
                @endforeach
            </div>
            <button type="button" class="cotizaciones-add-item" data-add-item>Agregar producto</button>
        </fieldset>

        {{-- Notas persistidas de la cotización, sin reglas comerciales dentro de Blade. --}}
        <fieldset class="cotizaciones-form-section">
            <legend>3. Condiciones y observaciones</legend>
            <label for="notas">Observaciones</label>
            <textarea id="notas" name="notas" maxlength="500" rows="4">{{ old('notas', $quotation->notas) }}</textarea>
            @error('notas')<small class="form-error">{{ $message }}</small>@enderror
        </fieldset>

        {{-- Acciones HTTP: PUT actualiza la cotización original y no crea otra. --}}
        <footer class="cotizaciones-form-actions">
            <a class="cotizaciones-secondary-button" href="{{ route('management.quotations') }}">Cancelar</a>
            <button class="cotizaciones-primary-button" type="submit">Guardar cambios</button>
        </footer>
    </form>
</main>
@endsection

@push('scripts')
    @vite('resources/js/gestion/comercial/cotizaciones/formulario.js')
@endpush
