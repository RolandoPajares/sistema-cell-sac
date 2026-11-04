<dialog class="tc-em__dialog" data-em-store-dialog aria-labelledby="tc-em-store-dialog-title">
    <div class="tc-em__dialog-card">
        <header>
            <span class="tc-em__dialog-icon"><i class="bi bi-shop"></i></span>
            <div><h2 id="tc-em-store-dialog-title">Editar configuración de la tienda</h2><p>Formulario ilustrativo de la sección seleccionada.</p></div>
            <button type="button" class="tc-em__close" data-em-store-close aria-label="Cerrar"><i class="bi bi-x-lg"></i></button>
        </header>
        <div class="tc-em__dialog-body">
            <div class="tc-em__modal-notice"><i class="bi bi-info-circle"></i> Esta vista no guarda cambios ni modifica el ecommerce.</div>
            <div class="tc-em__form-grid" data-em-store-fields="general">
                <label>Nombre de la tienda <em>*</em><input type="text" value="Technology CELL Store"></label>
                <label>Dominio / URL<input type="url" placeholder="https://tienda.ejemplo.pe"></label>
                <label>Correo de contacto<input type="email" placeholder="ventas@ejemplo.pe"></label>
                <label>Teléfono de contacto<input type="tel" placeholder="+51 987 654 321"></label>
                <label class="tc-em__form-full">Descripción de la tienda<textarea placeholder="Describe la tienda virtual…"></textarea></label>
            </div>
            <div class="tc-em__form-grid" data-em-store-fields="apariencia" hidden>
                <label>Logo de la tienda<input type="file" accept="image/png,image/jpeg,image/webp"></label>
                <label>Banner principal<input type="file" accept="image/png,image/jpeg,image/webp"></label>
                <label>Color principal<input type="color" value="#0867ed"></label>
                <label>Estilo de portada<select><option>Banner principal</option><option>Productos destacados</option></select></label>
            </div>
            <div class="tc-em__form-grid" data-em-store-fields="pagos" hidden>
                <label>Moneda principal<select><option>PEN · Sol peruano (S/)</option></select></label>
                <label>Tipo de precios<select><option>Minorista y mayorista</option><option>Solo minorista</option></select></label>
                <label>Método de pago<select><option>Transferencia</option><option>Tarjeta</option><option>Pago en tienda</option></select></label>
                <label>Estado<select><option>Pendiente de integración</option><option>Habilitado</option></select></label>
            </div>
            <div class="tc-em__form-grid" data-em-store-fields="envios" hidden>
                <label>Cobertura<select><option>Perú</option><option>Lima</option></select></label>
                <label>Costo de envío estándar (S/)<input type="number" min="0" step="0.01" placeholder="0,00"></label>
                <label>Recojo en tienda<select><option>Pendiente de configuración</option><option>Habilitado</option></select></label>
                <label>Plazo estimado (días)<input type="number" min="0" step="1" placeholder="0"></label>
            </div>
            <div class="tc-em__form-grid" data-em-store-fields="notificaciones" hidden>
                <label>Confirmación de pedido<select><option>Pendiente de integración</option><option>Correo electrónico</option></select></label>
                <label>Cambio de estado<select><option>Pendiente de integración</option><option>Correo electrónico</option></select></label>
                <label class="tc-em__form-full">Correo remitente<input type="email" placeholder="notificaciones@ejemplo.pe"></label>
            </div>
            <div class="tc-em__form-grid" data-em-store-fields="seo" hidden>
                <label class="tc-em__form-full">Título SEO<input type="text" placeholder="Technology CELL"></label>
                <label class="tc-em__form-full">Descripción SEO<textarea placeholder="Describe la tienda para buscadores…"></textarea></label>
                <label>ID de analítica<input type="text" placeholder="Pendiente de integración"></label>
                <label>ID de píxel<input type="text" placeholder="Pendiente de integración"></label>
            </div>
            <div class="tc-em__form-grid" data-em-store-fields="integraciones" hidden>
                <label>Pasarela de pagos<select><option>Pendiente</option><option>Por configurar</option></select></label>
                <label>Transportista<select><option>Pendiente</option><option>Por configurar</option></select></label>
                <label class="tc-em__form-full">Canales de marketing<input type="text" placeholder="Pendiente de integración"></label>
            </div>
        </div>
        <footer>
            <button type="button" class="tc-em__button" data-em-store-close>Cancelar</button>
            <button type="button" class="tc-em__button tc-em__button--primary" data-em-demo-save>Guardar cuando se integre</button>
        </footer>
    </div>
</dialog>
