<form method="post" class="form-card proveedores-form" action="{{ route('management.suppliers.update', $supplier) }}">
    @csrf
    @method('PUT')

    {{-- Encabezado y contexto del formulario de edición. --}}
    <header class="proveedores-form-heading">
        <div><span>Compras y logística</span><h2>Editar proveedor</h2><p>Actualiza la información comercial y de contacto.</p></div>
        <small>Edición de registro</small>
    </header>

    {{-- Identificación legal y comercial previamente registrada. --}}
    <fieldset class="proveedores-form-section">
        <legend>1. Identificación</legend>
        <label class="proveedores-form-field proveedores-form-field--full"><span>Tipo de persona</span><select name="tipo_persona" data-provider-person-type><option value="natural">Persona natural</option><option value="juridica" selected>Persona jurídica</option></select></label>
        <div class="proveedores-form-grid">
            <label class="proveedores-form-field"><span data-provider-document-type-label>Tipo de documento</span><select name="tipo_documento" data-provider-document-type><option value="ruc" selected>RUC</option><option value="dni">DNI</option></select></label>
            <label class="proveedores-form-field"><span data-provider-document-label>Número de documento <b>*</b></span><input name="ruc" required maxlength="20" placeholder="Ingresar documento" value="{{ old('ruc', $supplier->ruc) }}" data-provider-document-input></label>
        </div>
        <label class="proveedores-form-field proveedores-form-field--full"><span data-provider-name-label>Razón social <b>*</b></span><input name="nombre" required maxlength="160" placeholder="Razón social del proveedor" value="{{ old('nombre', $supplier->nombre) }}" data-provider-name-input></label>
        <label class="proveedores-form-field proveedores-form-field--full"><span>Nombre comercial <small>(opcional)</small></span><input name="nombre_comercial" maxlength="160" placeholder="Nombre comercial" value="{{ old('nombre_comercial') }}"></label>
    </fieldset>

    {{-- Datos de contacto del registro seleccionado. --}}
    <fieldset class="proveedores-form-section">
        <legend>2. Contacto</legend>
        <div class="proveedores-form-grid">
            <label class="proveedores-form-field"><span>Teléfono</span><input name="telefono" maxlength="30" placeholder="Número" value="{{ old('telefono', $supplier->telefono) }}"></label>
            <label class="proveedores-form-field"><span>Correo electrónico <b>*</b></span><input type="email" name="correo" required maxlength="160" placeholder="proveedor@correo.com" value="{{ old('correo', $supplier->correo) }}"></label>
        </div>
        <label class="proveedores-form-field proveedores-form-field--full"><span>Dirección</span><input name="ciudad" maxlength="80" placeholder="Dirección del proveedor" value="{{ old('ciudad', $supplier->ciudad) }}"></label>
    </fieldset>

    {{-- Condiciones comerciales editables de presentación. --}}
    <fieldset class="proveedores-form-section">
        <legend>3. Condiciones comerciales</legend>
        <label class="proveedores-form-field proveedores-form-field--full"><span>Tipo de suministro</span><select name="tipo_suministro"><option value="productos" selected>Productos</option><option value="servicios">Servicios</option><option value="mixto">Productos y servicios</option></select></label>
        <div class="proveedores-form-grid">
            <label class="proveedores-form-field"><span>Moneda habitual</span><select name="moneda"><option value="pen" selected>Soles (PEN)</option><option value="usd">Dólares (USD)</option></select></label>
            <label class="proveedores-form-field"><span>Condición de pago</span><select name="condicion_pago"><option value="contado" selected>Contado</option><option value="credito">Crédito</option></select></label>
        </div>
        <label class="proveedores-form-check"><input type="checkbox" name="informacion_bancaria" value="1"><span>Agregar información bancaria ahora</span></label>
    </fieldset>

    {{-- Observaciones y acción para guardar la actualización. --}}
    <label class="proveedores-form-field proveedores-form-field--full"><span>Observaciones</span><textarea name="observaciones" rows="3" maxlength="5000" placeholder="Información adicional del proveedor">{{ old('observaciones') }}</textarea></label>
    <p class="proveedores-form-note">Los campos identificatorios y de contacto se actualizan en el registro existente.</p>
    <button class="btn btn-primary proveedores-form-submit" type="submit">Guardar cambios</button>
</form>
