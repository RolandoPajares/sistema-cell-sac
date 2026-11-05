<form method="post" class="form-card compact-form" action="{{ route('management.suppliers.update', $supplier) }}">
    @csrf
    @method('PUT')
    @include('gestion.proveedores.formulario', ['supplier' => $supplier])
    <button class="btn btn-primary">Actualizar proveedor</button>
</form>
