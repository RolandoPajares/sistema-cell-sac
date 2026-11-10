<div class="marcas-form-grid">
    <!-- Campo Nombre -->
    <label class="marcas-field">
        <span class="marcas-label">Nombre de la marca <span class="marcas-required">*</span></span>
        <input 
            type="text" 
            name="nombre" 
            value="{{ old('nombre', $marca?->nombre) }}" 
            maxlength="120" 
            required 
            class="marcas-input"
        >
    </label>

    <!-- Campo Descripción -->
    <label class="marcas-field">
        <span class="marcas-label">Descripción</span>
        <textarea 
            name="descripcion" 
            maxlength="500" 
            placeholder="Ej. Celulares, cómputo y accesorios."
            class="marcas-textarea"
        >{{ old('descripcion', $marca?->descripcion) }}</textarea>
    </label>

    <!-- Checkbox Activo -->
    <label class="marcas-toggle">
        <input 
            type="checkbox" 
            name="activo" 
            value="1" 
            @checked(old('activo', $marca?->activo ?? true))
            class="marcas-checkbox"
        >
        <span class="marcas-toggle-text">Marca activa</span>
    </label>
</div>