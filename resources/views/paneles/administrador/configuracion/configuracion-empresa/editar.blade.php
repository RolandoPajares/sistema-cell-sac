<div class="admin-settings-layout admin-settings-layout--company">
    <section class="admin-settings-card admin-settings-card--identity">
        <header class="admin-settings-card-header">
            <div><span class="admin-settings-card-icon"><i class="bi bi-image" aria-hidden="true"></i></span><div><h2>Identidad de la empresa</h2><p>Logo corporativo para documentos, cotizaciones y comunicaciones.</p></div></div>
        </header>
        <div class="admin-settings-identity-grid">
            <div class="admin-settings-logo-preview"><img src="{{ asset('assets/cell-legacy/img/marca/logoadmin.png') }}" alt="Logo de TECHNOLOGY CELL"><span>Vista previa del logotipo</span></div>
            <div class="admin-settings-upload"><input type="file" accept="image/png,image/jpeg,image/svg+xml" data-settings-file><i class="bi bi-cloud-arrow-up" aria-hidden="true"></i><strong>Subir nuevo logo</strong><span>PNG, JPG o SVG · máximo 2 MB</span><button class="admin-settings-inline-button" type="button" data-settings-file-trigger>Seleccionar archivo</button><small data-settings-file-name>Sin archivo seleccionado</small></div>
        </div>
    </section>

    <section class="admin-settings-card">
        <header class="admin-settings-card-header"><div><span class="admin-settings-card-icon"><i class="bi bi-building" aria-hidden="true"></i></span><div><h2>Información básica</h2><p>Datos legales y de contacto de la empresa.</p></div></div></header>
        <div class="admin-settings-fields admin-settings-fields--two">
            <label class="admin-settings-field"><span>Razón social <b>*</b></span><input name="razon_social" placeholder="CELL SAC" value=""></label>
            <label class="admin-settings-field"><span>Nombre comercial <b>*</b></span><input name="nombre_comercial" placeholder="TECHNOLOGY CELL" value=""></label>
            <label class="admin-settings-field"><span>RUC <b>*</b></span><input name="ruc" placeholder="Número de RUC" value=""></label>
            <label class="admin-settings-field"><span>Correo corporativo <b>*</b></span><input type="email" name="correo" placeholder="contacto@technologycell.pe" value=""></label>
            <label class="admin-settings-field"><span>Teléfono <b>*</b></span><input name="telefono" placeholder="+51 999 000 000" value=""></label>
            <label class="admin-settings-field"><span>Sitio web</span><input name="sitio_web" placeholder="www.technologycell.pe" value=""></label>
        </div>
    </section>

    <section class="admin-settings-card admin-settings-card--wide">
        <header class="admin-settings-card-header"><div><span class="admin-settings-card-icon"><i class="bi bi-geo-alt" aria-hidden="true"></i></span><div><h2>Domicilio fiscal y contacto</h2><p>Ubicación registrada y datos para las comunicaciones.</p></div></div></header>
        <div class="admin-settings-fields">
            <label class="admin-settings-field admin-settings-field--wide"><span>Dirección <b>*</b></span><input name="direccion" placeholder="Av. Javier Prado Este 1234, Of. 501" value=""></label>
            <div class="admin-settings-fields admin-settings-fields--three">
                <label class="admin-settings-field"><span>Departamento <b>*</b></span><select name="departamento"><option value="">Seleccionar departamento</option><option>Lima</option><option>Arequipa</option></select></label>
                <label class="admin-settings-field"><span>Provincia <b>*</b></span><select name="provincia"><option value="">Seleccionar provincia</option><option>Lima</option><option>Arequipa</option></select></label>
                <label class="admin-settings-field"><span>Distrito <b>*</b></span><select name="distrito"><option value="">Seleccionar distrito</option><option>San Isidro</option><option>Miraflores</option></select></label>
            </div>
            <label class="admin-settings-field admin-settings-field--compact"><span>Código postal</span><input name="codigo_postal" placeholder="15073" value=""></label>
        </div>
    </section>

    <aside class="admin-settings-card admin-settings-card--preview">
        <header class="admin-settings-card-header"><div><span class="admin-settings-card-icon"><i class="bi bi-eye" aria-hidden="true"></i></span><div><h2>Vista previa de identidad</h2><p>Así se verá tu marca en el sistema.</p></div></div></header>
        <div class="admin-settings-brand-preview"><div class="admin-settings-brand-bar"><img src="{{ asset('assets/cell-legacy/img/marca/logoadmin.png') }}" alt="TECHNOLOGY CELL"></div><div class="admin-settings-brand-details"><span>Colores de marca</span><div><i style="--brand-color:#0d5bd7"></i><i style="--brand-color:#00a8ff"></i><i style="--brand-color:#e8f4ff"></i></div></div></div>
    </aside>

    <section class="admin-settings-card admin-settings-card--wide">
        <header class="admin-settings-card-header"><div><span class="admin-settings-card-icon"><i class="bi bi-megaphone" aria-hidden="true"></i></span><div><h2>Identidad comercial</h2><p>Descripción pública y canales digitales de la empresa.</p></div></div></header>
        <div class="admin-settings-fields admin-settings-fields--two">
            <label class="admin-settings-field"><span>Descripción de la empresa</span><textarea name="descripcion" rows="5" placeholder="Describe brevemente la actividad de CELL SAC..."></textarea></label>
            <div class="admin-settings-social-fields"><span class="admin-settings-field-title">Redes sociales</span><label class="admin-settings-field"><span>Instagram</span><input name="instagram" placeholder="https://www.instagram.com/technologycell.pe"></label><label class="admin-settings-field"><span>Facebook</span><input name="facebook" placeholder="https://www.facebook.com/technologycell.pe"></label></div>
        </div>
    </section>

    <aside class="admin-settings-card admin-settings-card--status"><header class="admin-settings-card-header"><div><span class="admin-settings-card-icon"><i class="bi bi-check2-circle" aria-hidden="true"></i></span><div><h2>Estado de información</h2><p>Completa los campos requeridos para mejorar el funcionamiento.</p></div></div></header><div class="admin-settings-progress"><div class="admin-settings-progress-ring"><strong>—</strong></div><div><strong>Sin datos integrados</strong><span>El porcentaje de completitud aparecerá cuando exista una fuente autorizada.</span></div></div></aside>
</div>
