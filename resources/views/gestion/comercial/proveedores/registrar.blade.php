<form method="post" class="form-card proveedores-form proveedores-form-redisenado" action="{{ route('management.suppliers.store') }}">
    @csrf
    <header class="proveedores-form-heading proveedores-form-heading-modern">
        <div><span>Compras y logística</span><h2>Nuevo proveedor</h2><p>Completa la información del proveedor para registrarlo en el sistema.</p></div>
    </header>

    <section class="proveedores-modern-type"><i class="bi bi-people" aria-hidden="true"></i><div><strong>Tipo de proveedor</strong><small>Selecciona la naturaleza del proveedor.</small></div><select name="tipo_persona" data-provider-person-type><option value="natural" @selected(old('tipo_persona', 'natural') === 'natural')>Persona natural</option><option value="juridica" @selected(old('tipo_persona') === 'juridica')>Persona jurídica</option></select></section>

    <fieldset class="proveedores-modern-section" data-provider-natural-fields hidden>
        <legend><i class="bi bi-person" aria-hidden="true"></i> Datos del proveedor</legend>
        <div class="proveedores-modern-grid"><label>Nombres <b>*</b><input data-provider-first-name placeholder="Ingresa los nombres" autocomplete="given-name"></label><label>Apellidos <b>*</b><input data-provider-last-name placeholder="Ingresa los apellidos" autocomplete="family-name"></label></div>
    </fieldset>

    <fieldset class="proveedores-modern-section" data-provider-juridica-fields hidden>
        <legend><i class="bi bi-building" aria-hidden="true"></i> Información empresarial</legend>
        <div class="proveedores-modern-grid"><label>Razón social <b>*</b><input data-provider-legal-name placeholder="Ej. Tecnología Cell S.A.C."></label><label>Nombre comercial <small>(opcional)</small><input name="nombre_comercial" placeholder="Ej. Technology Cell"></label></div>
    </fieldset>

    <fieldset class="proveedores-modern-section">
        <legend><i class="bi bi-card-text" aria-hidden="true"></i> Identificación</legend>
        <div class="proveedores-modern-grid"><label>Tipo de documento <b>*</b><select name="tipo_documento" data-provider-document-type><option value="dni" @selected(old('tipo_documento', 'dni') === 'dni')>DNI</option><option value="ruc" @selected(old('tipo_documento') === 'ruc')>RUC</option></select></label><label><span data-provider-document-type-label>Número de documento</span> <b>*</b><input name="ruc" required maxlength="8" placeholder="Ingresa el número de documento" value="{{ old('ruc') }}" data-provider-document-input></label></div>
    </fieldset>

    <fieldset class="proveedores-modern-section">
        <legend><i class="bi bi-telephone" aria-hidden="true"></i> Información de contacto</legend>
        <div class="proveedores-modern-grid"><label>Teléfono<input name="telefono" maxlength="30" placeholder="Ingresa el teléfono" value="{{ old('telefono') }}"></label><label>Correo electrónico <b>*</b><input type="email" name="correo" required maxlength="160" placeholder="Ingresa el correo electrónico" value="{{ old('correo') }}"></label></div>
        <label>Dirección <b>*</b><input name="ciudad" maxlength="80" placeholder="Ingresa la dirección del proveedor" value="{{ old('ciudad') }}"></label>
    </fieldset>

    <input type="hidden" name="nombre" data-provider-name-input value="{{ old('nombre') }}" required>
    <p class="proveedores-modern-note"><i class="bi bi-info-circle" aria-hidden="true"></i> Los datos identificatorios y de contacto se validan antes de guardar el proveedor.</p>
    <footer class="proveedores-modern-actions"><button type="button" class="proveedores-modern-cancel" data-close-supplier-dialog>Cancelar</button><button class="proveedores-modern-submit" type="submit"><i class="bi bi-save" aria-hidden="true"></i> Guardar proveedor</button></footer>
</form>
