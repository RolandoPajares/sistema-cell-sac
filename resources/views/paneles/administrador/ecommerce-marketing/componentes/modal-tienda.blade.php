<dialog class="tc-em__dialog" data-em-store-dialog aria-labelledby="tc-em-store-dialog-title">
    <div class="tc-em__dialog-card">
        <header>
            <span class="tc-em__dialog-icon"><i class="bi bi-shop"></i></span>
            <div><h2 id="tc-em-store-dialog-title">Editar configuración de la tienda</h2><p>@if ($configuracionTiendaDisponible) Los cambios se guardan por empresa en MariaDB. @else Los cambios requieren la estructura persistente de tienda propuesta. @endif</p></div>
            <button type="button" class="tc-em__close" data-em-store-close aria-label="Cerrar"><i class="bi bi-x-lg"></i></button>
        </header>
        <form method="POST" action="{{ route('management.commercial_settings.store.update') }}" data-em-store-form>
        @csrf
        @method('PUT')
        <input type="hidden" name="seccion" value="general" data-em-store-section-value>
        <div class="tc-em__dialog-body">
            <div class="tc-em__modal-notice"><i class="bi bi-info-circle"></i> @if ($configuracionTiendaDisponible) Las preferencias se guardarán en MariaDB bajo la empresa activa; no activan pasarelas ni notificaciones externas. @else Esta vista no guardará cambios hasta aplicar la estructura SQL aprobada. @endif</div>
            <div class="tc-em__form-grid" data-em-store-fields="general">
                <label>Nombre de la tienda <em>*</em><input type="text" name="configuracion[general][nombre_tienda]" value="{{ data_get($configuracionTienda, 'general.nombre_tienda', $empresa->nombre_comercial ?: $empresa->razon_social) }}" required maxlength="180"></label>
                <label>Dominio / URL<input type="url" name="configuracion[general][dominio]" value="{{ data_get($configuracionTienda, 'general.dominio') }}" placeholder="Dominio no registrado en MariaDB"></label>
                <label>Correo de contacto<input type="email" name="configuracion[general][correo]" value="{{ data_get($configuracionTienda, 'general.correo') }}" placeholder="Correo no registrado"></label>
                <label>Teléfono de contacto<input type="tel" name="configuracion[general][telefono]" value="{{ data_get($configuracionTienda, 'general.telefono') }}" placeholder="Teléfono no registrado"></label>
                <label class="tc-em__form-full">Descripción de la tienda<textarea name="configuracion[general][descripcion]" placeholder="Descripción no registrada en MariaDB">{{ data_get($configuracionTienda, 'general.descripcion') }}</textarea></label>
            </div>
            <div class="tc-em__form-grid" data-em-store-fields="apariencia" hidden>
                <label>Logo de la tienda<input type="file" accept="image/png,image/jpeg,image/webp" disabled title="El esquema actual no incluye persistencia para archivos de tienda."></label>
                <label>Banner principal<input type="file" accept="image/png,image/jpeg,image/webp" disabled title="El esquema actual no incluye persistencia para archivos de tienda."></label>
                <label>Color principal<input type="color" name="configuracion[apariencia][color_principal]" value="{{ data_get($configuracionTienda, 'apariencia.color_principal', '#0867ed') }}"></label>
                <label>Estilo de portada<select name="configuracion[apariencia][estilo_portada]"><option value="banner" @selected(data_get($configuracionTienda, 'apariencia.estilo_portada', 'banner') === 'banner')>Banner principal</option><option value="productos_destacados" @selected(data_get($configuracionTienda, 'apariencia.estilo_portada') === 'productos_destacados')>Productos destacados</option></select></label>
            </div>
            <div class="tc-em__form-grid" data-em-store-fields="pagos" hidden>
                <label>Moneda principal<select><option value="{{ $empresa->moneda_base }}">{{ $empresa->moneda_base }}</option></select></label>
                <label>Tipo de precios<select name="configuracion[pagos][tipo_precios]"><option value="">Selecciona una opción</option><option value="minorista_mayorista" @selected(data_get($configuracionTienda, 'pagos.tipo_precios') === 'minorista_mayorista')>Minorista y mayorista</option><option value="minorista" @selected(data_get($configuracionTienda, 'pagos.tipo_precios') === 'minorista')>Solo minorista</option></select></label>
                <label>Método de pago<select name="configuracion[pagos][medios_pago][]" multiple>@foreach ($mediosPago as $medioPago)<option value="{{ $medioPago->id }}" @selected(in_array((string) $medioPago->id, array_map('strval', data_get($configuracionTienda, 'pagos.medios_pago', [])), true))>{{ $medioPago->nombre }}</option>@endforeach</select></label>
                <label>Estado<select disabled><option>No persistido en MariaDB</option></select></label>
            </div>
            <div class="tc-em__form-grid" data-em-store-fields="envios" hidden>
                <label>Cobertura<select disabled><option>No hay cobertura persistida en MariaDB</option></select></label>
                <label>Costo de envío estándar (S/)<input type="number" name="configuracion[envios][costo_estandar]" min="0" step="0.01" value="{{ data_get($configuracionTienda, 'envios.costo_estandar') }}" placeholder="0,00"></label>
                <label>Recojo en tienda<select name="configuracion[envios][recojo_tienda]"><option value="">Selecciona una opción</option><option value="0" @selected(data_get($configuracionTienda, 'envios.recojo_tienda') === false)>No</option><option value="1" @selected(data_get($configuracionTienda, 'envios.recojo_tienda') === true)>Sí</option></select></label>
                <label>Plazo estimado (días)<input type="number" name="configuracion[envios][plazo_estimado]" min="0" max="365" step="1" value="{{ data_get($configuracionTienda, 'envios.plazo_estimado') }}" placeholder="0"></label>
            </div>
            <div class="tc-em__form-grid" data-em-store-fields="notificaciones" hidden>
                <label>Confirmación de pedido<select name="configuracion[notificaciones][confirmacion_pedido]"><option value="">Selecciona una opción</option><option value="pendiente" @selected(data_get($configuracionTienda, 'notificaciones.confirmacion_pedido') === 'pendiente')>Pendiente de integración</option><option value="correo" @selected(data_get($configuracionTienda, 'notificaciones.confirmacion_pedido') === 'correo')>Correo electrónico</option></select></label>
                <label>Cambio de estado<select name="configuracion[notificaciones][cambio_estado]"><option value="">Selecciona una opción</option><option value="pendiente" @selected(data_get($configuracionTienda, 'notificaciones.cambio_estado') === 'pendiente')>Pendiente de integración</option><option value="correo" @selected(data_get($configuracionTienda, 'notificaciones.cambio_estado') === 'correo')>Correo electrónico</option></select></label>
                <label class="tc-em__form-full">Correo remitente<input type="email" name="configuracion[notificaciones][correo_remitente]" value="{{ data_get($configuracionTienda, 'notificaciones.correo_remitente') }}" placeholder="notificaciones@ejemplo.pe"></label>
            </div>
            <div class="tc-em__form-grid" data-em-store-fields="seo" hidden>
                <label class="tc-em__form-full">Título SEO<input type="text" name="configuracion[seo][titulo]" value="{{ data_get($configuracionTienda, 'seo.titulo') }}" placeholder="{{ $empresa->nombre_comercial ?: $empresa->razon_social }}"></label>
                <label class="tc-em__form-full">Descripción SEO<textarea name="configuracion[seo][descripcion]" placeholder="Describe la tienda para buscadores…">{{ data_get($configuracionTienda, 'seo.descripcion') }}</textarea></label>
                <label>ID de analítica<input type="text" name="configuracion[seo][analitica_id]" value="{{ data_get($configuracionTienda, 'seo.analitica_id') }}" placeholder="No configurado en MariaDB"></label>
                <label>ID de píxel<input type="text" name="configuracion[seo][pixel_id]" value="{{ data_get($configuracionTienda, 'seo.pixel_id') }}" placeholder="No configurado en MariaDB"></label>
            </div>
            <div class="tc-em__form-grid" data-em-store-fields="integraciones" hidden>
                <label>Pasarela de pagos<select disabled><option>No configurado en MariaDB</option></select></label>
                <label>Transportista<select disabled><option>No configurado en MariaDB</option></select></label>
                <label class="tc-em__form-full">Canales de marketing<input type="text" placeholder="No configurado en MariaDB" disabled></label>
            </div>
        </div>
        <footer>
            <button type="button" class="tc-em__button" data-em-store-close>Cancelar</button>
            <button type="submit" class="tc-em__button tc-em__button--primary" data-em-store-submit @disabled(!($configuracionTiendaDisponible && $puedeActualizarConfiguracion))>Guardar configuración</button>
        </footer>
        </form>
    </div>
</dialog>
