{{-- Sección "Beneficios mayoristas" del cliente mayorista (se abre desde el menú con #beneficios) --}}
@php
    // cupones activos y vigentes
    $cuponesEmpresa = \App\Models\Comercio\Cupon::where('activo', true)
        ->where('inicia_en', '<=', now())
        ->where('finaliza_en', '>=', now())
        ->orderBy('finaliza_en')
        ->get();

    // descuentos según la cantidad que se compra (información comercial de la tienda)
    $escalas = [
        ['desde' => '10 a 49 unidades', 'descuento' => '5%'],
        ['desde' => '50 a 99 unidades', 'descuento' => '8%'],
        ['desde' => '100 unidades a más', 'descuento' => '12%'],
    ];
@endphp

<section class="benef-cliente" aria-labelledby="benef-titulo">
    <nav class="cliente-migas" aria-label="Ruta de navegación">
        <span>Panel</span><i class="bi bi-chevron-right"></i><span>Beneficios mayoristas</span><i class="bi bi-chevron-right"></i><strong>Ventajas por volumen</strong>
    </nav>

    <header class="benef-cliente-cabecera">
        <div>
            <span class="benef-cliente-etiqueta"><i class="bi bi-tag"></i> Beneficios mayoristas</span>
            <h1 id="benef-titulo">Ventajas de comprar por volumen</h1>
            <p>Mientras más compra tu empresa, mejores precios y condiciones obtiene.</p>
        </div>
        <a class="benef-cliente-boton" href="{{ route('smart.wholesale') }}"><i class="bi bi-box-seam"></i> Ver productos por volumen</a>
    </header>

    <div class="benef-cliente-columnas">
        <article class="benef-cliente-caja" data-cliente-ancla="volumen">
            <h2><i class="bi bi-boxes"></i> Ofertas por volumen</h2>
            <p>Precios especiales en celulares y accesorios cuando pides varias unidades del mismo modelo.</p>
            <a href="{{ route('smart.wholesale') }}">Ir al portal mayorista <i class="bi bi-arrow-right"></i></a>
        </article>

        <article class="benef-cliente-caja" data-cliente-ancla="descuentos">
            <h2><i class="bi bi-percent"></i> Descuentos empresariales</h2>
            <div class="benef-cliente-escalas">
                @foreach ($escalas as $escala)
                    <div><span>{{ $escala['desde'] }}</span><strong>{{ $escala['descuento'] }}</strong></div>
                @endforeach
            </div>
            <small>Referencial. El descuento final se confirma en la cotización.</small>
        </article>
    </div>

    <article class="benef-cliente-caja" data-cliente-ancla="cupones">
        <h2><i class="bi bi-ticket-perforated"></i> Cupones disponibles</h2>
        <div class="benef-cliente-cupones">
            @forelse ($cuponesEmpresa as $cupon)
                <div class="benef-cliente-cupon">
                    <strong>{{ $cupon->tipo_descuento === 'porcentaje' ? number_format((float) $cupon->valor, 0).'%' : 'S/ '.number_format((float) $cupon->valor, 0) }}</strong>
                    <div>
                        <code>{{ $cupon->codigo }}</code>
                        <small>Vence el {{ $cupon->finaliza_en->format('d/m/Y') }}</small>
                    </div>
                    <button type="button" data-copiar-cupon="{{ $cupon->codigo }}"><i class="bi bi-clipboard"></i> Copiar</button>
                </div>
            @empty
                <p class="benef-cliente-vacio">Por ahora no hay cupones activos.</p>
            @endforelse
        </div>
    </article>

    <article class="benef-cliente-caja" data-cliente-ancla="condiciones">
        <h2><i class="bi bi-journal-check"></i> Condiciones comerciales</h2>
        <ul class="benef-cliente-condiciones">
            <li><i class="bi bi-check2-circle"></i> Las cotizaciones tienen una validez de 30 días.</li>
            <li><i class="bi bi-check2-circle"></i> Los precios por volumen aplican desde 10 unidades del mismo producto.</li>
            <li><i class="bi bi-check2-circle"></i> Los productos tienen garantía: 12 meses celulares y 6 meses accesorios.</li>
            <li><i class="bi bi-check2-circle"></i> Un asesor de ventas mayoristas atiende cada pedido de la empresa.</li>
        </ul>
    </article>
</section>
