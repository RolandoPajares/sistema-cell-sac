
{{-- Formulario completo e independiente para editar una categoría existente. --}}
{{-- formulario que recopila datos y los envia a la ruta indicada. --}}
<form class="categorias-modal-form" method="post" action="{{ route('management.categories.update', $category) }}">
{{-- proteccion CSRF que valida el origen del formulario. --}}
    @csrf
{{-- metodo HTTP que permite simular PUT, PATCH o DELETE desde el formulario. --}}
    @method('PUT')

    {{-- Datos actuales de la categoría; old() conserva los valores tras una validación fallida. --}}
    <div class="categorias-modal-layout">
        <section class="categorias-modal-main">
            <div class="categorias-modal-section">
                <header class="categorias-modal-section-header">
                    <i class="bi bi-file-earmark-text" aria-hidden="true"></i>
                    <h3>Información de la categoría</h3>
                </header>
                <div class="categorias-form-fields">
                    <label class="categorias-form-field">
                        <span>Nombre de la categoría <b>*</b></span>
                        <input name="nombre" value="{{ old('nombre', $category->nombre) }}" maxlength="120" required>
                    </label>
                    <label class="categorias-form-field">
                        <span>Descripción</span>
                        <textarea name="descripcion" maxlength="500" rows="4">{{ old('descripcion', $category->descripcion) }}</textarea>
                        <small>Máximo 500 caracteres</small>
                    </label>
                    <label class="categorias-form-field">
                        <span>Estado <b>*</b></span>
                        <select name="activo" required>
                            <option value="1" @selected((string) old('activo', (int) $category->activo) === '1')>Activa</option>
                            <option value="0" @selected((string) old('activo', (int) $category->activo) === '0')>Inactiva</option>
                        </select>
                    </label>
                </div>
            </div>
        </section>
    </div>

    {{-- Acciones de actualización con método HTTP y protección CSRF. --}}
    <footer class="categorias-form-actions">
        <button class="categorias-button categorias-button--secondary" type="button" data-close-category-edit>Cancelar</button>
        <button class="categorias-button categorias-button--primary" type="submit"><i class="bi bi-save" aria-hidden="true"></i> Guardar cambios</button>
    </footer>
</form>