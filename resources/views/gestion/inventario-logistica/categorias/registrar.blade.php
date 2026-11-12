
{{-- Formulario completo e independiente para registrar una categoría. --}}
{{-- formulario que recopila datos y los envia a la ruta indicada. --}}
<form class="categorias-modal-form" method="post" action="{{ route('management.categories.store') }}">
{{-- proteccion CSRF que valida el origen del formulario. --}}
    @csrf

    {{-- Datos principales que serán validados y almacenados por el controlador. --}}
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
                        <input name="nombre" value="{{ old('nombre') }}" maxlength="120" placeholder="Ej. Accesorios, Audio, Celulares..." required>
                    </label>
                    <label class="categorias-form-field">
                        <span>Descripción</span>
                        <textarea name="descripcion" maxlength="500" rows="4" placeholder="Describe brevemente esta categoría...">{{ old('descripcion') }}</textarea>
                        <small>Máximo 500 caracteres</small>
                    </label>
                </div>
            </div>
        </section>
    </div>

    {{-- Acciones del registro; el token CSRF protege el envío. --}}
    <footer class="categorias-form-actions">
        <button class="categorias-button categorias-button--secondary" type="button" data-close-category-create>Cancelar</button>
        <button class="categorias-button categorias-button--primary" type="submit"><i class="bi bi-plus-lg" aria-hidden="true"></i> Registrar categoría</button>
    </footer>
</form>