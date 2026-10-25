<form method="post" class="admin-edit-popover" action="{{ route('management.products.update', $product) }}">
    @csrf
    @method('PUT')
    <div class="admin-form-grid">
        @include('gestion.productos.formulario', ['product' => $product, 'categories' => $categories])
    </div>
    <div class="admin-form-actions"><button class="admin-primary-button" type="submit">Guardar cambios</button></div>
</form>
