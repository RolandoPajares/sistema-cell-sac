<form method="post" action="{{ route('management.customers.store') }}" class="form-card">
    @csrf
    <h2>Nuevo cliente</h2>
    @include('gestion.clientes.formulario', ['customer' => null])
    <button class="btn btn-primary">Registrar cliente</button>
</form>
