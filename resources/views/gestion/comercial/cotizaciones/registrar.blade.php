{{--
    Formulario de registro de cotizaciones.
    Esta vista solo presenta datos entregados por el controlador; la autorización,
    validación, cálculo definitivo y persistencia se ejecutan en el servidor.
--}}
<form method="POST" action="{{ route('quotations.store') }}" class="cotizaciones-form" data-quotation-form>
    @csrf

    {{-- Selección del cliente existente dentro del alcance empresarial autorizado. --}}
    <fieldset class="cotizaciones-form-section">
        <legend>1. Datos del cliente</legend>
        <fieldset class="cotizaciones-client-origin">
            <legend>Origen del cliente</legend>
            <label>
                <input type="radio" name="cliente_origen" value="registrado" checked>
                Cliente registrado
            </label>
            @can('gestion.clientes.crear')
                <label>
                    <input type="radio" name="cliente_origen" value="nuevo">
                    Nuevo cliente
                </label>
                <p>
                    ¿El cliente todavía no existe?
                    <a href="{{ route('management.customers') }}" data-new-customer-link>Ir al módulo de clientes para registrarlo</a>.
                </p>
            @endcan
        </fieldset>
        <label for="cliente_id">Cliente</label>
        <select id="cliente_id" name="cliente_id" required>
            <option value="">Seleccionar cliente</option>
            @foreach ($customers as $customer)
                <option value="{{ $customer->id }}" @selected((string) old('cliente_id') === (string) $customer->id)>
                    {{ $customer->nombre_contacto ?: $customer->razon_social }} · {{ $customer->documento }}
                </option>
            @endforeach
        </select>
        @error('cliente_id')<small class="form-error">{{ $message }}</small>@enderror
    </fieldset>

    {{-- Líneas de productos; los precios visibles son referenciales y el servidor los verifica. --}}
    <fieldset class="cotizaciones-form-section">
        <legend>2. Productos o servicios</legend>
        <div class="cotizaciones-items" data-quotation-items>
            <div class="cotizaciones-item" data-quotation-item>
                <label>Producto
                    <select name="items[0][producto_id]" required data-product-select>
                        <option value="">Seleccionar producto</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" data-price="{{ $product->effective_price }}">{{ $product->nombre }} · S/ {{ number_format((float) $product->effective_price, 2) }}</option>
                        @endforeach
                    </select>
                </label>
                <label>Cantidad
                    <input type="number" name="items[0][cantidad]" min="1" max="100000" value="{{ old('items.0.cantidad', 1) }}" required data-quantity>
                </label>
                <output data-line-total>S/ 0.00</output>
                <button type="button" class="cotizaciones-remove-item" data-remove-item disabled>Quitar</button>
            </div>
        </div>
        <button type="button" class="cotizaciones-add-item" data-add-item>Agregar producto</button>
    </fieldset>

    {{-- Observaciones comerciales almacenadas junto con la cotización. --}}
    <fieldset class="cotizaciones-form-section">
        <legend>3. Condiciones y observaciones</legend>
        <label for="notas">Observaciones</label>
        <textarea id="notas" name="notas" maxlength="500" rows="4">{{ old('notas') }}</textarea>
        @error('notas')<small class="form-error">{{ $message }}</small>@enderror
    </fieldset>

    {{-- Acciones del formulario; el estado inicial lo define el servicio de cotizaciones. --}}
    <footer class="cotizaciones-form-actions">
        <a class="cotizaciones-secondary-button" href="{{ route('quotations.index') }}">Cancelar</a>
        <button class="cotizaciones-primary-button" type="submit">Guardar cotización</button>
    </footer>
</form>

@push('scripts')
    @vite('resources/js/gestion/comercial/cotizaciones/formulario.js')
@endpush
