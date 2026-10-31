@php($editing = isset($category) && $category !== null)

<div class="categorias-modal-layout">
    <section class="categorias-modal-main">
        <div class="categorias-modal-section">
            <header class="categorias-modal-section-header"><i class="bi bi-file-earmark-text" aria-hidden="true"></i><h3>Información de la categoría</h3></header>
            <div class="categorias-form-fields">
                <label class="categorias-form-field">
                    <span>Nombre de la categoría <b>*</b></span>
                    <input name="nombre" value="{{ old('nombre', $editing ? $category->nombre : '') }}" maxlength="120" placeholder="Ej. Accesorios, Audio, Celulares..." required>
                </label>
                <label class="categorias-form-field">
                    <span>Descripción</span>
                    <textarea name="descripcion" maxlength="500" rows="4" placeholder="Describe brevemente esta categoría...">{{ old('descripcion', $editing ? $category->descripcion : '') }}</textarea>
                    <small>Máximo 500 caracteres</small>
                </label>
                <div class="categorias-form-row">
                    <label class="categorias-form-field">
                        <span>Estado <b>*</b></span>
                        <span class="categorias-status-select"><i class="bi bi-circle-fill" aria-hidden="true"></i><select name="activo"><option value="1" @selected((string) old('activo', $editing ? (int) $category->activo : 1) === '1')>Activa</option><option value="0" @selected((string) old('activo', $editing ? (int) $category->activo : 1) === '0')>Inactiva</option></select></span>
                    </label>
                    <label class="categorias-form-field">
                        <span>Categoría padre <em>(opcional)</em></span>
                        <select disabled><option>Sin categoría padre</option></select>
                    </label>
                </div>
                <div class="categorias-form-field">
                    <span>Icono o color identificador <em>(opcional)</em></span>
                    <div class="categorias-icon-options" aria-label="Icono identificador de la categoría">
                        <button type="button" class="is-selected" aria-label="Caja"><i class="bi bi-box-seam" aria-hidden="true"></i></button>
                        <button type="button" aria-label="Videojuegos"><i class="bi bi-controller" aria-hidden="true"></i></button>
                        <button type="button" aria-label="Celular"><i class="bi bi-phone" aria-hidden="true"></i></button>
                        <button type="button" aria-label="Audio"><i class="bi bi-headphones" aria-hidden="true"></i></button>
                        <button type="button" aria-label="Computadora"><i class="bi bi-display" aria-hidden="true"></i></button>
                        <button type="button" aria-label="Hogar"><i class="bi bi-house" aria-hidden="true"></i></button>
                        <button type="button" aria-label="Redes"><i class="bi bi-wifi" aria-hidden="true"></i></button>
                        <button type="button" aria-label="Más opciones"><i class="bi bi-three-dots" aria-hidden="true"></i></button>
                    </div>
                    <small>Selecciona un icono o color para identificar la categoría.</small>
                </div>
            </div>
        </div>
    </section>

    <aside class="categorias-modal-aside">
        <section class="categorias-tips-card">
            <header><span><i class="bi bi-lightbulb" aria-hidden="true"></i></span><div><h3>Consejos y ejemplos</h3><p>Una buena organización facilita la gestión de tus productos.</p></div></header>
            <strong>Ejemplos de categorías:</strong>
            <ul>
                <li><span class="is-purple"><i class="bi bi-box-seam" aria-hidden="true"></i></span><div><b>Accesorios</b><small>Cables, cargadores, fundas...</small></div></li>
                <li><span class="is-green"><i class="bi bi-phone" aria-hidden="true"></i></span><div><b>Celulares</b><small>Smartphones y tablets...</small></div></li>
                <li><span class="is-orange"><i class="bi bi-headphones" aria-hidden="true"></i></span><div><b>Audio</b><small>Audífonos, bocinas, micrófonos...</small></div></li>
                <li><span class="is-blue"><i class="bi bi-display" aria-hidden="true"></i></span><div><b>Cómputo</b><small>Laptops, PCs, componentes...</small></div></li>
            </ul>
            <div class="categorias-tip"><i class="bi bi-info-circle" aria-hidden="true"></i><div><b>Tip</b><p>Puedes crear subcategorías usando la opción de categoría padre para tener una mejor estructura.</p></div></div>
        </section>
    </aside>
</div>
