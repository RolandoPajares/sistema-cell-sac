{{-- Sección "Configuración" del cliente minorista (se abre desde el menú con #configuracion) --}}
{{-- Las preferencias se guardan en el navegador con JS (localStorage) --}}
<section class="config-cliente" data-config-cliente="minorista" aria-labelledby="config-titulo-minorista">
    <header class="config-cliente-cabecera">
        <div>
            <span class="config-cliente-etiqueta"><i class="bi bi-gear"></i> Configuración</span>
            <h1 id="config-titulo-minorista">Preferencias de tu cuenta</h1>
            <p>Elige cómo quieres recibir avisos y cómo se muestra tu panel.</p>
        </div>
    </header>

    <div class="config-cliente-columnas">
        <article class="config-cliente-caja">
            <h2><i class="bi bi-bell"></i> Notificaciones</h2>
            <label class="config-cliente-switch">
                <span><b>Estado de pedidos</b><small>Avisarme cuando un pedido cambie de estado</small></span>
                <input type="checkbox" name="aviso_pedidos" checked>
            </label>
            <label class="config-cliente-switch">
                <span><b>Ofertas y promociones</b><small>Recibir cupones y ofertas de la tienda</small></span>
                <input type="checkbox" name="aviso_ofertas" checked>
            </label>
            <label class="config-cliente-switch">
                <span><b>Productos recomendados</b><small>Sugerencias según lo que compro</small></span>
                <input type="checkbox" name="aviso_recomendados">
            </label>
        </article>

        <article class="config-cliente-caja">
            <h2><i class="bi bi-bag"></i> Mis compras</h2>
            <label class="config-cliente-campo">
                Forma de entrega preferida
                <select name="entrega">
                    <option value="envio">Envío a domicilio</option>
                    <option value="recojo">Recojo en tienda</option>
                </select>
            </label>
            <label class="config-cliente-campo">
                Pedidos a mostrar en el resumen
                <select name="pedidos_resumen">
                    <option value="4">4 pedidos</option>
                    <option value="2">2 pedidos</option>
                </select>
            </label>
            <label class="config-cliente-switch">
                <span><b>Vista compacta</b><small>Reduce los espacios del panel</small></span>
                <input type="checkbox" name="compacto">
            </label>
        </article>
    </div>

    <div class="config-cliente-acciones">
        <button type="button" class="config-cliente-boton" data-config-guardar><i class="bi bi-save"></i> Guardar configuración</button>
        <button type="button" class="config-cliente-boton config-cliente-boton--borde" data-config-restablecer><i class="bi bi-arrow-counterclockwise"></i> Restablecer</button>
        <span class="config-cliente-mensaje" data-config-mensaje role="status"></span>
    </div>
    <p class="config-cliente-nota"><i class="bi bi-info-circle"></i> Estas preferencias se guardan en este navegador.</p>

    <article class="config-cliente-caja config-cliente-sesion">
        <div>
            <h2><i class="bi bi-box-arrow-right"></i> Sesión</h2>
            <p>Cierra tu sesión si estás usando una computadora compartida.</p>
        </div>
        <form method="post" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="config-cliente-boton config-cliente-boton--negro">Cerrar sesión</button>
        </form>
    </article>
</section>
