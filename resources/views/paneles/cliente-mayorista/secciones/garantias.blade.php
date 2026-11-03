{{-- Sección "Mis garantías" del cliente mayorista (se abre desde el menú con #garantias) --}}
{{-- La garantía se cuenta desde la fecha del pedido: 12 meses celulares, 6 meses lo demás --}}
@php
    $equipos = collect();

    foreach (($orders ?? collect())->where('estado', '!=', 'Cancelado') as $pedido) {
        foreach ($pedido->details as $detalle) {
            $producto = $detalle->product;
            $esCelular = ($producto?->categoria ?? '') === 'Celulares';
            $meses = $esCelular ? 12 : 6;
            $inicio = $pedido->creado_en ?? now();
            $vence = $inicio->copy()->addMonths($meses);
            $diasTotales = max($inicio->diffInDays($vence), 1);
            $diasQuedan = (int) max(now()->diffInDays($vence, false), 0);

            $equipos->push([
                'nombre' => $producto?->nombre ?? 'Producto',
                'marca' => $producto?->marca ?? '',
                'pedido' => $pedido->id,
                'meses' => $meses,
                'vence' => $vence,
                'dias' => $diasQuedan,
                'porcentaje' => round($diasQuedan / $diasTotales * 100),
                'icono' => $esCelular ? 'bi-phone' : 'bi-plug',
            ]);
        }
    }

    // primero las que están por vencer
    $equipos = $equipos->sortBy('dias')->values();
    $vigentes = $equipos->where('dias', '>', 0)->count();
@endphp

<section class="garantia-cliente" aria-labelledby="garantia-titulo">
    <nav class="cliente-migas" aria-label="Ruta de navegación">
        <span>Panel</span><i class="bi bi-chevron-right"></i><span>Postventa</span><i class="bi bi-chevron-right"></i><strong>Garantías de productos</strong>
    </nav>

    <header class="garantia-cliente-cabecera">
        <div>
            <span class="garantia-cliente-etiqueta"><i class="bi bi-shield-check"></i> Postventa</span>
            <h1 id="garantia-titulo">Garantías de productos</h1>
            <p>Revisa cuánto tiempo de garantía le queda a cada producto que compró tu empresa.</p>
        </div>
        <div class="garantia-cliente-derecha">
            <div class="garantia-cliente-contador">
                <strong>{{ $vigentes }}</strong>
                <span>garantías vigentes</span>
            </div>
            <button type="button" class="garantia-cliente-boton" data-cliente-modal-abrir="modal-solicitud" data-solicitud-tipo="Devolución" data-cliente-ancla="devolucion">
                <i class="bi bi-arrow-counterclockwise"></i> Solicitar devolución
            </button>
        </div>
    </header>

    <div class="garantia-cliente-lista">
        @forelse ($equipos as $equipo)
            <article class="garantia-cliente-item {{ $equipo['dias'] === 0 ? 'is-vencida' : ($equipo['dias'] <= 30 ? 'is-por-vencer' : '') }}">
                <span class="garantia-cliente-icono"><i class="bi {{ $equipo['icono'] }}"></i></span>
                <div class="garantia-cliente-info">
                    <b>{{ $equipo['nombre'] }}</b>
                    <small>{{ $equipo['marca'] }} · Pedido #{{ $equipo['pedido'] }} · Garantía de {{ $equipo['meses'] }} meses</small>
                    <div class="garantia-cliente-barra"><span style="width: {{ $equipo['porcentaje'] }}%"></span></div>
                </div>
                <div class="garantia-cliente-estado">
                    @if ($equipo['dias'] === 0)
                        <strong>Vencida</strong>
                        <small>desde el {{ $equipo['vence']->format('d/m/Y') }}</small>
                    @else
                        <strong>{{ $equipo['dias'] }} días</strong>
                        <small>vence el {{ $equipo['vence']->format('d/m/Y') }}</small>
                        <button type="button" class="garantia-cliente-revisar" data-cliente-modal-abrir="modal-solicitud" data-solicitud-tipo="Revisión de garantía" data-solicitud-producto="{{ $loop->index }}">
                            <i class="bi bi-tools"></i> Solicitar revisión
                        </button>
                    @endif
                </div>
            </article>
        @empty
            <p class="garantia-cliente-vacio"><i class="bi bi-shield"></i> Cuando tu empresa compre productos, su garantía aparecerá aquí.</p>
        @endforelse
    </div>

    <p class="garantia-cliente-nota">
        <i class="bi bi-info-circle"></i>
        Al enviar una solicitud, nuestro equipo de soporte te responderá a tu correo.
    </p>
</section>

{{-- Modal: solicitud de revisión o devolución (se envía con el formulario de contacto que ya existe) --}}
<dialog class="cliente-modal" id="modal-solicitud" aria-labelledby="modal-solicitud-titulo" @if ($errors->has('mensaje')) data-cliente-modal-auto="1" @endif>
    <form class="cliente-modal__contenido" method="post" action="{{ route('pages.contact.store') }}" data-form-solicitud>
        @csrf
        <input type="hidden" name="nombre" value="{{ auth()->user()->nombre }}">
        <input type="hidden" name="contacto" value="{{ auth()->user()->correo }}">
        <header class="cliente-modal__cabecera">
            <span class="cliente-modal__icono"><i class="bi bi-shield-check"></i></span>
            <div class="cliente-modal__titulos">
                <h2 id="modal-solicitud-titulo">Nueva solicitud de postventa</h2>
                <p>Cuéntanos qué pasó con el producto de tu empresa.</p>
            </div>
            <button type="button" class="cliente-modal__cerrar" data-cliente-modal-cerrar aria-label="Cerrar"><i class="bi bi-x-lg"></i></button>
        </header>
        <div class="cliente-modal__cuerpo">
            <div class="cliente-modal__dos">
                <label>
                    Tipo de solicitud
                    <select data-solicitud-campo="tipo">
                        <option>Revisión de garantía</option>
                        <option>Devolución</option>
                    </select>
                </label>
                <label>
                    Motivo
                    <select data-solicitud-campo="motivo">
                        <option>No enciende</option>
                        <option>Falla de pantalla</option>
                        <option>Falla de batería o carga</option>
                        <option>Producto incorrecto</option>
                        <option>Llegó dañado</option>
                        <option>Otro</option>
                    </select>
                </label>
            </div>
            <label>
                Producto
                <select data-solicitud-campo="producto" required>
                    @forelse ($equipos as $equipo)
                        <option value="{{ $equipo['nombre'] }} (pedido #{{ $equipo['pedido'] }})">{{ $equipo['nombre'] }} — pedido #{{ $equipo['pedido'] }}</option>
                    @empty
                        <option value="">No tienes productos comprados</option>
                    @endforelse
                </select>
            </label>
            <label>
                Describe el problema
                <textarea name="mensaje" required maxlength="1500" placeholder="Ej.: desde ayer el equipo se apaga solo cuando la batería está al 30%">{{ old('mensaje') }}</textarea>
            </label>
            <p class="cliente-modal__ayuda">Te responderemos a {{ auth()->user()->correo }}.</p>
        </div>
        <footer class="cliente-modal__pie">
            <button type="button" class="cliente-modal__boton cliente-modal__boton--suave" data-cliente-modal-cerrar>Cancelar</button>
            <button type="submit" class="cliente-modal__boton"><i class="bi bi-send"></i> Enviar solicitud</button>
        </footer>
    </form>
</dialog>
