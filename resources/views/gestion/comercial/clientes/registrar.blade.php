<form method="post" action="{{ route('management.customers.store') }}" class="form-card cliente-registro-form" data-client-form data-person-type="natural">
    @csrf

    {{-- Encabezado: presenta el propósito del formulario y la información que se registrará. --}}
    <div class="cliente-registro-heading">
        <span class="clientes-eyebrow">CRM</span>
        <h2>Registro de clientes</h2>
        <p>Completa la información comercial y de contacto del cliente.</p>
    </div>

    {{-- Selector visual: cambia los campos de presentación sin enviar un dato que no existe en clientes. --}}
    <fieldset class="cliente-person-switcher">
        <legend>Tipo de persona</legend>
        <div class="cliente-person-options" role="group" aria-label="Tipo de persona">
            <button type="button" class="cliente-person-option is-active" data-client-person="natural" aria-pressed="true">Persona natural</button>
            <button type="button" class="cliente-person-option" data-client-person="juridica" aria-pressed="false">Persona jurídica</button>
        </div>
    </fieldset>

    {{-- Identificación: sus valores se validan y guardan mediante el controlador de clientes. --}}
    <div class="cliente-registro-section">
        <h3>Identificación</h3>
        <div class="cliente-field-grid cliente-field-grid--two">
            <label>Tipo de documento
                <select name="tipo_documento" data-client-document-type>
                    <option value="">Seleccionar</option>
                    <option value="dni" @selected(old('tipo_documento') === 'dni')>DNI</option>
                    <option value="ruc" @selected(old('tipo_documento') === 'ruc')>RUC</option>
                </select>
            </label>
            <label>Documento de identificación
                <input name="documento" value="{{ old('documento') }}" maxlength="20" required>
            </label>
        </div>
        <label data-legal-only>Razón social
            <input name="razon_social" value="{{ old('razon_social') }}" maxlength="160" placeholder="Razón social de la empresa">
        </label>
        <label data-contact-name>
            <span data-natural-label>Nombre completo</span>
            <span data-legal-label>Persona de contacto o representante</span>
            <input name="nombre_contacto" value="{{ old('nombre_contacto') }}" maxlength="160" required placeholder="Ingresa nombres y apellidos">
        </label>
    </div>

    {{-- Segmento comercial: conserva el catálogo minorista o mayorista usado por el módulo. --}}
    <div class="cliente-registro-section">
        <h3>Información comercial</h3>
        <label>Segmento del cliente
            <select name="tipo" required>
                <option value="minorista" @selected(old('tipo', 'minorista') === 'minorista')>Minorista</option>
                <option value="mayorista" @selected(old('tipo') === 'mayorista')>Mayorista</option>
            </select>
        </label>
    </div>

    {{-- Contacto: se almacena en las columnas existentes de la entidad Cliente. --}}
    <div class="cliente-registro-section">
        <h3>Información de contacto</h3>
        <div class="cliente-field-grid cliente-field-grid--two">
            <label>Teléfono
                <input name="telefono" value="{{ old('telefono') }}" maxlength="30" placeholder="Número de contacto">
            </label>
            <label>Correo electrónico
                <input type="email" name="correo" value="{{ old('correo') }}" maxlength="160" required placeholder="Correo electrónico">
            </label>
        </div>
        <label>Ciudad
            <input name="ciudad" value="{{ old('ciudad') }}" maxlength="80" placeholder="Ciudad del cliente">
        </label>
    </div>

    {{-- Acción principal: envía los datos a store con protección CSRF. --}}
    <button class="cliente-registro-submit" type="submit">Registrar cliente</button>
</form>


