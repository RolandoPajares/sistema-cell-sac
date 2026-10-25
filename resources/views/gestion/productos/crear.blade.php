<form method="post" action="{{ route('management.products.store') }}">
    @csrf
    <div class="admin-form-grid">
        @include('gestion.productos.formulario', ['product' => null, 'categories' => $categories])
    </div>
    <div class="admin-form-actions">
        <button class="admin-primary-button" type="submit">
            <i class="bi bi-check2-circle"></i>
            Crear producto
        </button>
    </div>
</form>
