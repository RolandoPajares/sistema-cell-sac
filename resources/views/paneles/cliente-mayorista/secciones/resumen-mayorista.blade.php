{{-- Sección principal del panel del cliente mayorista (empresas) --}}
{{-- Responsable: Integrante 4 - Sebastián --}}
@php
    $usuario = auth()->user();
    $pedidos = $orders ?? collect();

    // sumamos el total comprado sin contar los pedidos cancelados
    $totalComprado = $pedidos->whereNotIn('estado', ['Cancelado'])->sum(fn ($pedido) => (float) $pedido->total);
    $pedidosEntregados = $pedidos->where('estado', 'Entregado')->count();

    $tarjetas = [
        ['titulo' => 'Pedidos realizados', 'valor' => $orderCount ?? 0, 'icono' => 'bi-receipt', 'texto' => 'Historial de tu empresa'],
        ['titulo' => 'Pedidos en proceso', 'valor' => $pendingCount ?? 0, 'icono' => 'bi-clock-history', 'texto' => 'Pendientes o en preparación'],
        ['titulo' => 'Pedidos entregados', 'valor' => $pedidosEntregados, 'icono' => 'bi-truck', 'texto' => 'Recibidos correctamente'],
        ['titulo' => 'Total comprado', 'valor' => 'S/ '.number_format($totalComprado, 2), 'icono' => 'bi-cash-stack', 'texto' => 'Monto acumulado'],
    ];
@endphp

<section class="mayorista-panel" aria-labelledby="mayorista-titulo">

    {{-- Cabecera del panel --}}
    <header class="mayorista-cabecera">
        <div class="mayorista-cabecera-texto">
            <span class="mayorista-etiqueta"><i class="bi bi-building"></i> Cliente mayorista</span>
            <h1 id="mayorista-titulo">Bienvenido, {{ $usuario->nombre }}</h1>
            <p>Desde aquí puedes revisar los pedidos de tu empresa, pedir cotizaciones por volumen y gestionar tu cuenta.</p>
            <div class="mayorista-cabecera-botones">
                <a class="mayorista-boton mayorista-boton--blanco" href="{{ route('quotations.index') }}">
                    <i class="bi bi-file-earmark-text"></i> Solicitar cotización
                </a>
                <a class="mayorista-boton mayorista-boton--borde" href="{{ route('smart.wholesale') }}">
                    <i class="bi bi-box-seam"></i> Portal mayorista
                </a>
            </div>
        </div>
        <div class="mayorista-cabecera-info">
            <i class="bi bi-briefcase"></i>
            <strong>Cuenta empresarial</strong>
            <small>{{ $usuario->correo }}</small>
            <span class="mayorista-estado-cuenta"><i class="bi bi-check-circle-fill"></i> Activa</span>
        </div>
    </header>

    {{-- Tarjetas de resumen --}}
    <div class="mayorista-tarjetas">
        @foreach ($tarjetas as $tarjeta)
            <article class="mayorista-tarjeta">
                <span class="mayorista-tarjeta-icono"><i class="bi {{ $tarjeta['icono'] }}"></i></span>
                <div>
                    <h2>{{ $tarjeta['titulo'] }}</h2>
                    <strong>{{ $tarjeta['valor'] }}</strong>
                    <small>{{ $tarjeta['texto'] }}</small>
                </div>
            </article>
        @endforeach
    </div>

    {{-- Tabla de pedidos con filtro por estado (el filtro se hace con JS) --}}
    <section class="mayorista-caja">
        <div class="mayorista-caja-titulo">
            <h2><i class="bi bi-list-check"></i> Pedidos de la empresa</h2>
            <div class="mayorista-filtros" role="group" aria-label="Filtrar pedidos">
                <button type="button" class="activo" data-filtro-mayorista="todos">Todos</button>
                <button type="button" data-filtro-mayorista="proceso">En proceso</button>
                <button type="button" data-filtro-mayorista="Entregado">Entregados</button>
            </div>
        </div>

        <div class="mayorista-tabla-contenedor">
            <table class="mayorista-tabla">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Fecha</th>
                        <th>Producto principal</th>
                        <th>Items</th>
                        <th>Estado</th>
                        <th>Total</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pedidos->take(8) as $pedido)
                        @php($primerProducto = $pedido->details->first()?->product)
                        <tr data-estado="{{ $pedido->estado }}">
                            <td><b>#{{ $pedido->id }}</b></td>
                            <td>{{ $pedido->creado_en?->format('d/m/Y') }}</td>
                            <td>{{ $primerProducto?->nombre ?: 'Pedido CELL SAC' }}</td>
                            <td>{{ $pedido->details->sum('cantidad') ?: $pedido->details->count() }}</td>
                            <td><span class="mayorista-estado mayorista-estado--{{ \Illuminate\Support\Str::slug($pedido->estado) }}">{{ $pedido->estado }}</span></td>
                            <td><b>S/ {{ number_format((float) $pedido->total, 2) }}</b></td>
                            <td><a class="mayorista-link" href="{{ route('orders.show', $pedido) }}">Ver <i class="bi bi-chevron-right"></i></a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="mayorista-vacio">
                                    <i class="bi bi-inbox"></i>
                                    <p>Tu empresa todavía no tiene pedidos registrados.</p>
                                    <a class="mayorista-boton mayorista-boton--azul" href="{{ route('quotations.index') }}">Pedir mi primera cotización</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    <tr class="mayorista-sin-resultados" hidden>
                        <td colspan="7">No hay pedidos con ese estado.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <div class="mayorista-dos-columnas">
        {{-- Accesos rápidos --}}
        <section class="mayorista-caja">
            <div class="mayorista-caja-titulo"><h2><i class="bi bi-lightning-charge"></i> Accesos rápidos</h2></div>
            <div class="mayorista-accesos">
                <a href="{{ route('quotations.index') }}"><i class="bi bi-file-earmark-text"></i><span>Cotizaciones</span></a>
                <a href="{{ route('smart.wholesale') }}"><i class="bi bi-boxes"></i><span>Compra por volumen</span></a>
                <a href="{{ route('comparison') }}"><i class="bi bi-columns-gap"></i><span>Comparar equipos</span></a>
                <a href="{{ route('pages.contact') }}"><i class="bi bi-headset"></i><span>Hablar con un asesor</span></a>
            </div>
        </section>

        {{-- Beneficios del cliente mayorista --}}
        <section class="mayorista-caja mayorista-beneficios">
            <div class="mayorista-caja-titulo"><h2><i class="bi bi-award"></i> Beneficios de tu cuenta</h2></div>
            <ul>
                <li><i class="bi bi-check2-circle"></i> Precios especiales por cantidad</li>
                <li><i class="bi bi-check2-circle"></i> Cotizaciones con validez para tu empresa</li>
                <li><i class="bi bi-check2-circle"></i> Atención de un asesor de ventas mayoristas</li>
                <li><i class="bi bi-check2-circle"></i> Seguimiento de todos tus pedidos</li>
            </ul>
        </section>
    </div>

    {{-- Datos de la cuenta y seguridad --}}
    <div class="mayorista-dos-columnas">
        <form method="post" action="{{ route('account.profile') }}" class="mayorista-caja mayorista-formulario">
            @csrf
            @method('PUT')
            <div class="mayorista-caja-titulo"><h2><i class="bi bi-person-vcard"></i> Datos de contacto</h2></div>
            <label>
                Nombre o razón social
                <input name="nombre" value="{{ old('nombre', $usuario->nombre) }}" required maxlength="120">
            </label>
            <label>
                Correo
                <input type="email" name="correo" value="{{ old('correo', $usuario->correo) }}" required maxlength="160">
            </label>
            <button class="mayorista-boton mayorista-boton--azul" type="submit"><i class="bi bi-save"></i> Guardar datos</button>
        </form>

        <form method="post" action="{{ route('account.password') }}" class="mayorista-caja mayorista-formulario">
            @csrf
            @method('PUT')
            <div class="mayorista-caja-titulo"><h2><i class="bi bi-shield-lock"></i> Seguridad</h2></div>
            <label>
                Contraseña actual
                <span class="admin-password-input">
                    <input type="password" name="password_actual" required>
                    <button type="button" data-password-toggle aria-label="Mostrar contraseña"><i class="bi bi-eye"></i></button>
                </span>
            </label>
            <label>
                Nueva contraseña
                <span class="admin-password-input">
                    <input type="password" name="password" required minlength="10">
                    <button type="button" data-password-toggle aria-label="Mostrar contraseña"><i class="bi bi-eye"></i></button>
                </span>
            </label>
            <label>
                Confirmar contraseña
                <input type="password" name="password_confirmation" required>
            </label>
            <button class="mayorista-boton mayorista-boton--negro" type="submit"><i class="bi bi-key"></i> Cambiar contraseña</button>
        </form>
    </div>
</section>
