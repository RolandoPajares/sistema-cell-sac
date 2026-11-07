@php($editing = isset($customer) && $customer !== null)

<label>
    Tipo
    <select name="tipo">
        <option value="minorista" @selected($editing && $customer->tipo === 'minorista')>Minorista</option>
        <option value="mayorista" @selected($editing && $customer->tipo === 'mayorista')>Mayorista</option>
    </select>
</label>

<label>
    Documento
    <input
        name="documento"
        @if($editing) value="{{ $customer->documento }}" @endif
        required
        maxlength="20"
    >
</label>

<label>
    Nombre de contacto
    <input
        name="nombre_contacto"
        @if($editing) value="{{ $customer->nombre_contacto }}" @endif
        required
        maxlength="160"
    >
</label>

<label>
    Razón social
    <input
        name="razon_social"
        @if($editing) value="{{ $customer->razon_social }}" @endif
        maxlength="160"
    >
</label>

<label>
    Correo
    <input
        type="email"
        name="correo"
        @if($editing) value="{{ $customer->correo }}" @endif
        required
        maxlength="160"
    >
</label>

<label>
    Teléfono
    <input
        name="telefono"
        @if($editing) value="{{ $customer->telefono }}" @endif
        maxlength="30"
    >
</label>

<label>
    Ciudad
    <input
        name="ciudad"
        @if($editing) value="{{ $customer->ciudad }}" @endif
        maxlength="80"
    >
</label>
