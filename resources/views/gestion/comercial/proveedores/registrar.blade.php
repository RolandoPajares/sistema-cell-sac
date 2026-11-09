<form method="post" class="form-card" action="{{ route('management.suppliers.store') }}">
    @csrf
    <h2>Registrar proveedor</h2>
    @php($editing = isset($supplier) && $supplier !== null)

<label>
    Nombre
    <input
        name="nombre"
        required
        maxlength="160"
        value="{{ $editing ? $supplier->nombre : old('nombre') }}"
    >
</label>

<label>
    RUC
    <input
        name="ruc"
        required
        maxlength="20"
        value="{{ $editing ? $supplier->ruc : old('ruc') }}"
    >
</label>

<label>
    Correo
    <input
        type="email"
        name="correo"
        required
        maxlength="160"
        value="{{ $editing ? $supplier->correo : old('correo') }}"
    >
</label>

<label>
    Teléfono
    <input
        name="telefono"
        maxlength="30"
        value="{{ $editing ? $supplier->telefono : old('telefono') }}"
    >
</label>

<label>
    Ciudad
    <input
        name="ciudad"
        maxlength="80"
        value="{{ $editing ? $supplier->ciudad : old('ciudad') }}"
    >
</label>
    <button class="btn btn-primary">Guardar proveedor</button>
</form>


