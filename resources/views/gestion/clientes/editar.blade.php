<form method="post" action="{{ route('management.customers.update', $customer) }}" class="form-card compact-form">
    @csrf
    @method('PUT')
    @include('gestion.clientes.formulario', ['customer' => $customer])
    <button class="btn btn-primary">Guardar cambios</button>
</form>
