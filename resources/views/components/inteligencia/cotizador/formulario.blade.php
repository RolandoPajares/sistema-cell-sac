@props(['customers', 'products'])

<form method="post" class="form-card compact-form" action="{{ route('quotations.store') }}">
    @csrf
    @if(in_array(auth()->user()->rol, ['administrador', 'ventas_mayoristas'], true))
        <label>
            Cliente mayorista
            <select name="cliente_id" required>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}">{{ $customer->nombre_contacto }} — {{ $customer->documento }}</option>
                @endforeach
            </select>
        </label>
    @endif

    <label>
        Producto
        <select name="items[0][producto_id]" required>
            @foreach($products as $product)
                <option value="{{ $product->id }}">{{ $product->nombre }} — S/ {{ $product->effective_price }}</option>
            @endforeach
        </select>
    </label>

    <label>
        Cantidad
        <input type="number" name="items[0][cantidad]" min="1" value="1" required>
    </label>

    <label>
        Notas
        <textarea name="notas"></textarea>
    </label>

    <button class="btn btn-primary">Guardar borrador</button>
</form>
