{{-- Sección "Mis cotizaciones" del cliente mayorista (se abre desde el menú con #cotizaciones) --}}
{{-- La nueva cotización se registra en un modal y usa la ruta quotations.store que ya existe --}}
@php
    // cotizaciones de la empresa del cliente (igual que en la página de cotizaciones)
    $cotizacionesEmpresa = \App\Models\Comercio\Cotizacion::with('details.product')
        ->whereHas('customer', fn ($consulta) => $consulta->where('usuario_id', auth()->id()))
        ->latest('creado_en')
        ->get()
        ->map(function ($cotizacion) {
            $estado = $cotizacion->estado;

            // una cotización pendiente con más de 30 días se considera vencida
            if (in_array($estado, ['Borrador', 'Pendiente', 'Enviada'], true)) {
                $grupo = $cotizacion->creado_en && $cotizacion->creado_en->lt(now()->subDays(30)) ? 'vencida' : 'pendiente';
            } elseif ($estado === 'Aprobada') {
                $grupo = 'aprobada';
            } elseif (in_array($estado, ['Vencida', 'Expirada'], true)) {
                $grupo = 'vencida';
            } else {
                $grupo = 'otra';
            }

            $cotizacion->grupo = $grupo;

            return $cotizacion;
        });

    $conteo = $cotizacionesEmpresa->countBy('grupo');

    // productos para el formulario de nueva cotización
    $productosCotizar = \App\Models\Catalogo\Producto::where('activo', true)->orderBy('nombre')->get();
    $errorCotizacion = $errors->hasAny(['items', 'items.*', 'items.0.producto_id', 'items.0.cantidad', 'notas']);
@endphp

<section class="cotiz-cliente" aria-labelledby="cotiz-titulo">
    <nav class="cliente-migas" aria-label="Ruta de navegación">
        <span>Panel</span><i class="bi bi-chevron-right"></i><span>Mis cotizaciones</span><i class="bi bi-chevron-right"></i><strong>Cotizaciones de la empresa</strong>
    </nav>

    <header class="cotiz-cliente-cabecera">
        <div>
            <span class="cotiz-cliente-etiqueta"><i class="bi bi-file-earmark-text"></i> Mis cotizaciones</span>
            <h1 id="cotiz-titulo">Cotizaciones de tu empresa</h1>
            <p>Sigue el estado de cada cotización y pide una nueva cuando la necesites.</p>
        </div>
        <button type="button" class="cotiz-cliente-boton" data-cliente-modal-abrir="modal-nueva-cotizacion" data-cliente-ancla="nueva">
            <i class="bi bi-plus-lg"></i> Solicitar nueva cotización
        </button>
    </header>

    <div class="cotiz-cliente-resumen">
        <article><span><i class="bi bi-collection"></i></span><div><small>Total</small><strong>{{ $cotizacionesEmpresa->count() }}</strong></div></article>
        <article><span><i class="bi bi-hourglass-split"></i></span><div><small>Pendientes</small><strong>{{ $conteo['pendiente'] ?? 0 }}</strong></div></article>
        <article><span><i class="bi bi-check2-circle"></i></span><div><small>Aprobadas</small><strong>{{ $conteo['aprobada'] ?? 0 }}</strong></div></article>
        <article><span><i class="bi bi-calendar-x"></i></span><div><small>Vencidas</small><strong>{{ $conteo['vencida'] ?? 0 }}</strong></div></article>
    </div>

    <article class="cotiz-cliente-caja">
        <div class="cotiz-cliente-herramientas">
            <div class="cotiz-cliente-filtros" role="group" aria-label="Filtrar cotizaciones">
                <button type="button" class="activo" data-filtro-cotizacion="todas" data-cliente-ancla="todas">Historial</button>
                <button type="button" data-filtro-cotizacion="pendiente" data-cliente-ancla="pendiente">Pendientes</button>
                <button type="button" data-filtro-cotizacion="aprobada" data-cliente-ancla="aprobada">Aprobadas</button>
                <button type="button" data-filtro-cotizacion="vencida" data-cliente-ancla="vencida">Vencidas</button>
            </div>
            <label class="cliente-buscador">
                <i class="bi bi-search"></i>
                <input type="search" placeholder="Buscar por código o nota..." data-buscar-cotizacion aria-label="Buscar cotización">
            </label>
        </div>

        <div class="cotiz-cliente-tabla-contenedor">
            <table class="cotiz-cliente-tabla">
                <thead>
                    <tr><th>Código</th><th>Fecha</th><th>Estado</th><th>Notas</th><th>Total</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse ($cotizacionesEmpresa as $cotizacion)
                        <tr data-grupo-cotizacion="{{ $cotizacion->grupo }}" data-texto-cotizacion="{{ \Illuminate\Support\Str::lower('#'.$cotizacion->id.' '.$cotizacion->notas) }}">
                            <td><b>#{{ $cotizacion->id }}</b></td>
                            <td>{{ $cotizacion->creado_en?->format('d/m/Y') }}</td>
                            <td><span class="cotiz-cliente-estado cotiz-cliente-estado--{{ $cotizacion->grupo }}">{{ $cotizacion->grupo === 'vencida' ? 'Vencida' : $cotizacion->estado }}</span></td>
                            <td>{{ \Illuminate\Support\Str::limit($cotizacion->notas ?: '—', 40) }}</td>
                            <td><b>S/ {{ number_format((float) $cotizacion->total, 2) }}</b></td>
                            <td>
                                <button type="button" class="cotiz-cliente-ver" data-cliente-modal-abrir="modal-cotizacion-{{ $cotizacion->id }}">
                                    <i class="bi bi-eye"></i> Ver
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="cotiz-cliente-vacio">
                                <i class="bi bi-file-earmark-plus"></i>
                                <p>Tu empresa aún no tiene cotizaciones.</p>
                                <button type="button" class="cotiz-cliente-ver" data-cliente-modal-abrir="modal-nueva-cotizacion">Solicitar la primera</button>
                            </td>
                        </tr>
                    @endforelse
                    <tr class="cotiz-cliente-sin-resultados" hidden>
                        <td colspan="6" class="cotiz-cliente-vacio">No hay cotizaciones que coincidan.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </article>
</section>

{{-- Modal: nueva cotización (se guarda de verdad) --}}
<dialog class="cliente-modal cliente-modal--ancho" id="modal-nueva-cotizacion" aria-labelledby="modal-nueva-cotizacion-titulo" @if ($errorCotizacion) data-cliente-modal-auto="1" @endif>
    <form class="cliente-modal__contenido" method="post" action="{{ route('quotations.store') }}" data-form-cotizacion>
        @csrf
        <header class="cliente-modal__cabecera">
            <span class="cliente-modal__icono"><i class="bi bi-file-earmark-plus"></i></span>
            <div class="cliente-modal__titulos">
                <h2 id="modal-nueva-cotizacion-titulo">Solicitar nueva cotización</h2>
                <p>Elige los productos y cantidades. Un asesor revisará tu solicitud.</p>
            </div>
            <button type="button" class="cliente-modal__cerrar" data-cliente-modal-cerrar aria-label="Cerrar"><i class="bi bi-x-lg"></i></button>
        </header>
        <div class="cliente-modal__cuerpo">
            <div class="cliente-modal__campo">
                <div class="cliente-modal__item cliente-modal__item--titulos" aria-hidden="true">
                    <span>Producto</span><span>Cantidad</span><span></span>
                </div>
                <div data-items-cotizacion>
                    <div class="cliente-modal__item" data-item-cotizacion>
                        <label>
                            <span class="visually-hidden">Producto</span>
                            <select name="items[0][producto_id]" required>
                                <option value="">Elige un producto...</option>
                                @foreach ($productosCotizar as $producto)
                                    <option value="{{ $producto->id }}" data-precio="{{ $producto->effective_price }}">{{ $producto->nombre }} — S/ {{ number_format((float) $producto->effective_price, 2) }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label>
                            <span class="visually-hidden">Cantidad</span>
                            <input type="number" name="items[0][cantidad]" min="1" value="10" required>
                        </label>
                        <button type="button" class="cliente-modal__quitar" data-quitar-item aria-label="Quitar producto"><i class="bi bi-trash"></i></button>
                    </div>
                </div>
            </div>
            <button type="button" class="cliente-modal__agregar" data-agregar-item><i class="bi bi-plus-lg"></i> Agregar otro producto</button>
            <label>
                Notas para el asesor (opcional)
                <textarea name="notas" maxlength="500" placeholder="Ej.: necesitamos la entrega antes del 15 de noviembre">{{ old('notas') }}</textarea>
            </label>
            <div class="cliente-modal__totales">
                <div><span>Total estimado</span><strong data-total-cotizacion>S/ 0.00</strong></div>
            </div>
            <p class="cliente-modal__ayuda">El total es referencial con los precios actuales. Los descuentos por volumen se aplican al aprobar.</p>
        </div>
        <footer class="cliente-modal__pie">
            <button type="button" class="cliente-modal__boton cliente-modal__boton--suave" data-cliente-modal-cerrar>Cancelar</button>
            <button type="submit" class="cliente-modal__boton"><i class="bi bi-send"></i> Enviar solicitud</button>
        </footer>
    </form>
</dialog>

{{-- Modales: detalle de cada cotización --}}
@foreach ($cotizacionesEmpresa as $cotizacion)
    <dialog class="cliente-modal" id="modal-cotizacion-{{ $cotizacion->id }}" aria-labelledby="modal-cotizacion-titulo-{{ $cotizacion->id }}">
        <div class="cliente-modal__contenido">
            <header class="cliente-modal__cabecera">
                <span class="cliente-modal__icono"><i class="bi bi-file-earmark-text"></i></span>
                <div class="cliente-modal__titulos">
                    <h2 id="modal-cotizacion-titulo-{{ $cotizacion->id }}">Cotización #{{ $cotizacion->id }}</h2>
                    <p>{{ $cotizacion->creado_en?->format('d/m/Y') }} · {{ $cotizacion->grupo === 'vencida' ? 'Vencida' : $cotizacion->estado }}</p>
                </div>
                <button type="button" class="cliente-modal__cerrar" data-cliente-modal-cerrar aria-label="Cerrar"><i class="bi bi-x-lg"></i></button>
            </header>
            <div class="cliente-modal__cuerpo">
                <ul class="cliente-modal__lista">
                    @forelse ($cotizacion->details as $detalle)
                        <li>
                            <i class="bi bi-box-seam"></i>
                            <div>
                                <b>{{ $detalle->product?->nombre ?? 'Producto' }}</b>
                                <small>{{ $detalle->cantidad }} unidades × S/ {{ number_format((float) $detalle->precio_unitario, 2) }}</small>
                            </div>
                            <b>S/ {{ number_format($detalle->cantidad * (float) $detalle->precio_unitario, 2) }}</b>
                        </li>
                    @empty
                        <li><i class="bi bi-info-circle"></i><div><small>Esta cotización no tiene productos registrados.</small></div></li>
                    @endforelse
                </ul>
                @if ($cotizacion->notas)
                    <p class="cliente-modal__ayuda"><b>Notas:</b> {{ $cotizacion->notas }}</p>
                @endif
                <div class="cliente-modal__totales">
                    <div><span>Total cotizado</span><strong>S/ {{ number_format((float) $cotizacion->total, 2) }}</strong></div>
                </div>
            </div>
            <footer class="cliente-modal__pie">
                <button type="button" class="cliente-modal__boton cliente-modal__boton--suave" data-cliente-modal-cerrar>Cerrar</button>
                <a class="cliente-modal__boton" href="{{ route('quotations.index') }}"><i class="bi bi-box-arrow-up-right"></i> Ir a cotizaciones</a>
            </footer>
        </div>
    </dialog>
@endforeach
