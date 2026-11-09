<form method="post" action="{{ route('management.customers.update', $customer) }}" class="form-card cliente-registro-form" data-client-form data-person-type="{{ old('tipo_documento', $customer->tipo_documento) === 'ruc' ? 'juridica' : 'natural' }}">
    @csrf
    @method('PUT')

    {{-- Encabezado: identifica el registro que se actualizará sin cambiar su identidad empresarial. --}}
    <div class="cliente-registro-heading">
        <span class="clientes-eyebrow">CRM</span>
        <h2>Editar cliente</h2>
        <p>Actualiza la información comercial y de contacto registrada.</p>
    </div>

    {{-- Selector visual: organiza la presentación según el documento guardado. --}}
    <fieldset class="cliente-person-switcher">
        <legend>Tipo de persona</legend>
        <div class="cliente-person-options" role="group" aria-label="Tipo de persona">
            <button type="button" class="cliente-person-option {{ old('tipo_documento', $customer->tipo_documento) !== 'ruc' ? 'is-active' : '' }}" data-client-person="natural" aria-pressed="{{ old('tipo_documento', $customer->tipo_documento) !== 'ruc' ? 'true' : 'false' }}">Persona natural</button>
            <button type="button" class="cliente-person-option {{ old('tipo_documento', $customer->tipo_documento) === 'ruc' ? 'is-active' : '' }}" data-client-person="juridica" aria-pressed="{{ old('tipo_documento', $customer->tipo_documento) === 'ruc' ? 'true' : 'false' }}">Persona jurídica</button>
        </div>
    </fieldset>

    {{-- Identificación: conserva los valores existentes y permite actualizarlos con validación. --}}
    <div class="cliente-registro-section">
        <h3>Identificación</h3>
        <div class="cliente-field-grid cliente-field-grid--two">
            <label>Tipo de documento
                <select name="tipo_documento" data-client-document-type>
                    <option value="">Seleccionar</option>
                    <option value="dni" @selected(old('tipo_documento', $customer->tipo_documento) === 'dni')>DNI</option>
                    <option value="ruc" @selected(old('tipo_documento', $customer->tipo_documento) === 'ruc')>RUC</option>
                </select>
            </label>
            <label>Documento de identificación
                <input name="documento" value="{{ old('documento', $customer->documento) }}" maxlength="20" required>
            </label>
        </div>
        <label data-legal-only>Razón social
            <input name="razon_social" value="{{ old('razon_social', $customer->razon_social) }}" maxlength="160" placeholder="Razón social de la empresa">
        </label>
        <label data-contact-name>
            <span data-natural-label>Nombre completo</span>
            <span data-legal-label>Persona de contacto o representante</span>
            <input name="nombre_contacto" value="{{ old('nombre_contacto', $customer->nombre_contacto) }}" maxlength="160" required placeholder="Ingresa nombres y apellidos">
        </label>
    </div>

    {{-- Segmento comercial: conserva la clasificación minorista o mayorista del cliente. --}}
    <div class="cliente-registro-section">
        <h3>Información comercial</h3>
        <label>Segmento del cliente
            <select name="tipo" required>
                <option value="minorista" @selected(old('tipo', $customer->tipo) === 'minorista')>Minorista</option>
                <option value="mayorista" @selected(old('tipo', $customer->tipo) === 'mayorista')>Mayorista</option>
            </select>
        </label>
    </div>

    {{-- Contacto: actualiza únicamente columnas existentes en la entidad Cliente. --}}
    <div class="cliente-registro-section">
        <h3>Información de contacto</h3>
        <div class="cliente-field-grid cliente-field-grid--two">
            <label>Teléfono
                <input name="telefono" value="{{ old('telefono', $customer->telefono) }}" maxlength="30" placeholder="Número de contacto">
            </label>
            <label>Correo electrónico
                <input type="email" name="correo" value="{{ old('correo', $customer->correo) }}" maxlength="160" required placeholder="Correo electrónico">
            </label>
        </div>
        <label>Ciudad
            <input name="ciudad" value="{{ old('ciudad', $customer->ciudad) }}" maxlength="80" placeholder="Ciudad del cliente">
        </label>
    </div>

    {{-- Acción principal: envía los cambios a update con CSRF y método PUT. --}}
    <button class="cliente-registro-submit" type="submit">Guardar cambios</button>
</form>
