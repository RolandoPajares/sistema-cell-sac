<form method="post" class="form-card" action="{{ route('management.suppliers.store') }}">
    @csrf
    <h2>Registrar proveedor</h2>
    @include('gestion.proveedores.formulario', ['supplier' => null])
    <button class="btn btn-primary">Guardar proveedor</button>
</form>
