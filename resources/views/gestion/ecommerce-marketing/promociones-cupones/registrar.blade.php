
{{-- formulario que recopila datos y los envia a la ruta indicada. --}}
<form method="post" class="form-card compact-form" action="{{ route('management.coupons.store') }}">
{{-- proteccion CSRF que valida el origen del formulario. --}}
    @csrf

    <label>
        Código
        <input name="codigo" value="{{ old('codigo') }}" maxlength="50" required>
{{-- mensaje de validacion para el campo que contiene errores. --}}
        @error('codigo')<small role="alert">{{ $message }}</small>@enderror
    </label>

    <label>
        Tipo
        <select name="tipo_descuento" required>
            <option value="porcentaje">Porcentaje</option>
            <option value="fijo">Monto fijo</option>
        </select>
    </label>

    <label>
        Valor
        <input
            type="number"
            name="valor"
            step="0.01"
            min="0.01"
            required
            max="99999999.99"
        >
{{-- mensaje de validacion para el campo que contiene errores. --}}
        @error('valor')<small role="alert">{{ $message }}</small>@enderror
    </label>

    <label>
        Compra mínima
        <input
            type="number"
            name="minimo_compra"
            step="0.01"
            min="0"
            value="0"
            required
            max="99999999.99"
        >
{{-- mensaje de validacion para el campo que contiene errores. --}}
        @error('minimo_compra')<small role="alert">{{ $message }}</small>@enderror
    </label>

    <label>
        Límite usos
        <input type="number" name="limite_usos" min="1" max="4294967295">
{{-- mensaje de validacion para el campo que contiene errores. --}}
        @error('limite_usos')<small role="alert">{{ $message }}</small>@enderror
    </label>

    <label>
        Inicio
        <input type="datetime-local" name="inicia_en" value="{{ old('inicia_en') }}" required>
{{-- mensaje de validacion para el campo que contiene errores. --}}
        @error('inicia_en')<small role="alert">{{ $message }}</small>@enderror
    </label>

    <label>
        Fin
        <input type="datetime-local" name="finaliza_en" value="{{ old('finaliza_en') }}" required>
{{-- mensaje de validacion para el campo que contiene errores. --}}
        @error('finaliza_en')<small role="alert">{{ $message }}</small>@enderror
    </label>

    <button class="btn btn-primary" @disabled(!$empresaCuponDisponible)>Guardar cupón</button>
</form>
