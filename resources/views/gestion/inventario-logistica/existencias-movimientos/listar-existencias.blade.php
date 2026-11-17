@extends('layouts.panel.principal')


{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('title', 'Inventario')
{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('body_class', 'modulo-inventario')

{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('content')
    <section class="inventario-page" data-inventory-page data-has-errors="{{ $errors->any() ? '1' : '0' }}">
        <header class="inventario-hero">
            <div>
                <span class="inventario-eyebrow">Operaciones</span>
                <h1>Inventario <i class="bi bi-boxes" aria-hidden="true"></i></h1>
                <p>Controla existencias, entradas, salidas y ajustes con trazabilidad completa.</p>
            </div>
            <div class="inventario-date-card">
                <i class="bi bi-calendar3" aria-hidden="true"></i>
                <span><small>Hoy</small><strong>{{ $todayLabel }}</strong></span>
                <i class="bi bi-chevron-down" aria-hidden="true"></i>
            </div>
        </header>

        <section class="inventario-kpi-grid" aria-label="Resumen del inventario">
            <article class="inventario-kpi inventario-kpi--blue">
                <span class="inventario-kpi-icon"><i class="bi bi-box-seam" aria-hidden="true"></i></span>
                <div><small>Productos con existencias</small><strong>{{ number_format($activeProducts) }}</strong><span>Productos con saldo físico en el alcance autorizado</span></div>
                <b class="inventario-kpi-trend is-positive"><i class="bi bi-box-seam" aria-hidden="true"></i> Saldo físico</b>
            </article>
            <article class="inventario-kpi inventario-kpi--orange">
                <span class="inventario-kpi-icon"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i></span>
                <div><small>Productos sin existencias</small><strong>{{ number_format($sinExistencias) }}</strong><span>Productos con saldo físico igual a cero</span></div>
                <b class="inventario-kpi-trend {{ $sinExistencias > 0 ? 'is-negative' : 'is-positive' }}"><i class="bi bi-{{ $sinExistencias > 0 ? 'exclamation-triangle' : 'check' }}" aria-hidden="true"></i> {{ $sinExistencias > 0 ? 'Revisar' : 'Sin faltantes' }}</b>
            </article>
            <article class="inventario-kpi inventario-kpi--indigo">
                <span class="inventario-kpi-icon"><i class="bi bi-arrow-left-right" aria-hidden="true"></i></span>
                <div><small>Movimientos hoy</small><strong>{{ number_format($movementsToday) }}</strong><span>Entradas, salidas y ajustes</span></div>
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
                @if ($movementsTrend !== null)
                    <b class="inventario-kpi-trend {{ $movementsTrend >= 0 ? 'is-positive' : 'is-negative' }}"><i class="bi bi-arrow-{{ $movementsTrend >= 0 ? 'up' : 'down' }}-right" aria-hidden="true"></i> {{ number_format(abs($movementsTrend), 1) }}%</b>
{{-- alternativa del condicional cuando la condicion anterior no se cumple. --}}
                @else
                    <b class="inventario-kpi-trend">Sin periodo anterior</b>
                @endif
            </article>
            <article class="inventario-kpi inventario-kpi--green">
                <span class="inventario-kpi-icon"><i class="bi bi-cash-stack" aria-hidden="true"></i></span>
                <div><small>Valor inventario</small><strong>S/ {{ number_format($inventoryValue, 2) }}</strong><span>Valor a precio de catálogo del stock autorizado</span></div>
                <b class="inventario-kpi-trend is-positive"><i class="bi bi-graph-up-arrow" aria-hidden="true"></i> Valor actual</b>
            </article>
        </section>

        <div class="inventario-toolbar">
            <button class="inventario-primary-button" type="button" data-open-inventory-movement><i class="bi bi-plus-lg" aria-hidden="true"></i> Registrar movimiento <i class="bi bi-chevron-down" aria-hidden="true"></i></button>
            <button class="inventario-toolbar-button inventario-toolbar-button--green" type="button" data-open-inventory-movement data-movement-type="entrada"><i class="bi bi-arrow-down" aria-hidden="true"></i> Entrada</button>
            <button class="inventario-toolbar-button inventario-toolbar-button--red" type="button" data-open-inventory-movement data-movement-type="salida"><i class="bi bi-arrow-up" aria-hidden="true"></i> Salida</button>
            <button class="inventario-toolbar-button inventario-toolbar-button--purple" type="button" data-open-inventory-movement data-movement-type="ajuste"><i class="bi bi-pencil" aria-hidden="true"></i> Ajuste</button>
            <button class="inventario-toolbar-button" type="button" data-open-inventory-movement data-movement-type="ajuste"><i class="bi bi-clipboard-check" aria-hidden="true"></i> Conteo físico</button>
        </div>

{{-- formulario que recopila datos y los envia a la ruta indicada. --}}
        <form class="inventario-filters" method="get" action="{{ route('management.inventory') }}">
            <label class="inventario-search"><i class="bi bi-search" aria-hidden="true"></i><span class="visually-hidden">Buscar producto</span><input type="search" name="buscar" value="{{ $filters['buscar'] ?? '' }}" placeholder="Buscar producto, SKU..."></label>
            <label><span class="visually-hidden">Filtrar por categoría</span><select name="categoria"><option value="">Todas las categorías</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected(($filters['categoria'] ?? null) === $category->id)>{{ $category->nombre }}</option>@endforeach</select></label>
            <label><span class="visually-hidden">Filtrar por estado</span><select name="estado"><option value="">Todos los estados</option><option value="con_existencias" @selected(($filters['estado'] ?? null) === 'con_existencias')>Con existencias</option><option value="sin_existencias" @selected(($filters['estado'] ?? null) === 'sin_existencias')>Sin existencias</option></select></label>
            <label><span class="visually-hidden">Filtrar por proveedor</span><select name="proveedor"><option value="">Todos los proveedores</option>@foreach ($suppliers as $supplier)<option value="{{ $supplier->id }}" @selected(($filters['proveedor'] ?? null) === $supplier->id)>{{ $supplier->nombre }}</option>@endforeach</select></label>
            <button class="inventario-filter-button" type="submit"><i class="bi bi-search" aria-hidden="true"></i> Buscar</button>
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
            @if (($filters['buscar'] ?? null) || ($filters['categoria'] ?? null) || ($filters['estado'] ?? null) || ($filters['proveedor'] ?? null))
                <a class="inventario-clear-button" href="{{ route('management.inventory') }}">Limpiar filtros</a>
            @endif
        </form>

        <section class="inventario-table-card">
            <header class="inventario-card-header">
                <div class="inventario-card-title"><i class="bi bi-boxes" aria-hidden="true"></i><div><h2>Lista de inventario</h2><p>{{ number_format($products->total()) }} productos en total</p></div></div>
                <button class="inventario-view-all" type="button" data-open-inventory-movement>Registrar movimiento</button>
            </header>
            <div class="inventario-table-wrap">
                <table class="inventario-table">
                    <thead><tr><th>Producto</th><th>SKU</th><th>Categoría</th><th>Existencias físicas</th><th>Reservado</th><th>Proveedor</th><th>Último movimiento</th><th>Estado</th><th>Acción</th></tr></thead>
                    <tbody>
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
                        @forelse ($products as $product)
                            <tr>
                                <td class="inventario-product-cell">
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
                                    @if ($productPresentation[$product->id]['image'])<img src="{{ $productPresentation[$product->id]['image'] }}" alt="{{ $product->nombre }}">@else<span class="inventario-product-image"><i class="bi bi-box-seam" aria-hidden="true"></i></span>@endif
                                    <span><strong>{{ $product->marca }} {{ $product->nombre }}</strong><small>{{ $product->nombre }}</small></span>
                                </td>
                                <td class="inventario-sku">{{ $productPresentation[$product->id]['sku'] }}</td>
                                <td>{{ $product->category?->nombre ?: ($product->categoria ?: 'Sin categoría') }}</td>
                                <td><span class="inventario-stock inventario-stock--{{ $productPresentation[$product->id]['status']['class'] }}">{{ number_format($product->existencias_alcance) }} unidades</span></td>
                                <td class="inventario-minimum">{{ number_format($product->existencias_reservadas) }}</td>
                                <td>{{ $product->suppliers->first()?->nombre ?: 'Sin proveedor' }}</td>
                                <td class="inventario-movement inventario-movement--{{ $productPresentation[$product->id]['movement']['class'] }}">
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
                                    @if ($latestMovements->get($product->id))
                                        <strong><i class="bi bi-arrow-{{ $productPresentation[$product->id]['movement']['icon'] }}" aria-hidden="true"></i> {{ $productPresentation[$product->id]['movement']['label'] }}</strong><small>{{ $latestMovements->get($product->id)?->creado_en?->format('d/m/Y, H:i') }}</small>
{{-- alternativa del condicional cuando la condicion anterior no se cumple. --}}
                                    @else
                                        <span>Sin movimientos</span>
                                    @endif
                                </td>
                                <td><span class="inventario-status inventario-status--{{ $productPresentation[$product->id]['status']['class'] }}"><i class="bi bi-circle-fill" aria-hidden="true"></i>{{ $productPresentation[$product->id]['status']['label'] }}</span></td>
                                <td class="inventario-actions-cell"><button class="inventario-view-button" type="button" data-open-inventory-movement data-movement-type="ajuste" data-inventory-product="{{ $product->id }}" title="Registrar conteo físico para {{ $product->nombre }}"><i class="bi bi-clipboard-check" aria-hidden="true"></i><span>Ajustar</span></button><button class="inventario-more-button" type="button" data-open-inventory-detail aria-label="Consultar información de inventario"><i class="bi bi-three-dots-vertical" aria-hidden="true"></i></button></td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="inventario-empty"><i class="bi bi-inbox" aria-hidden="true"></i>@if ($products->total() === 0 && !($filters['buscar'] || $filters['categoria'] || $filters['estado'] || $filters['proveedor']))<strong>No hay productos registrados.</strong><span>Los productos aparecerán aquí cuando se registren en el catálogo.</span>@else<strong>No hay productos para los filtros aplicados.</strong><span>Prueba con otros filtros de inventario.</span>@endif</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <footer class="inventario-pagination">
                <span>Mostrando {{ $products->firstItem() ?? 0 }} a {{ $products->lastItem() ?? 0 }} de {{ $products->total() }} productos</span>
{{-- formulario que recopila datos y los envia a la ruta indicada. --}}
                <form class="inventario-per-page-form" method="get" action="{{ route('management.inventory') }}">
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
                    @foreach ($inventoryQueryParameters as $key => $value)
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
                        @if (is_scalar($value))<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endif
                    @endforeach
                    <label for="inventario-por-pagina">Productos por página</label>
                    <select id="inventario-por-pagina" name="por_pagina" data-inventory-per-page aria-label="Productos por página">@foreach ($perPageOptions as $option)<option value="{{ $option }}" @selected(($filters['por_pagina'] ?? 7) === $option)>{{ $option }}</option>@endforeach</select>
                </form>
                {{ $products->onEachSide(1)->links() }}
            </footer>
        </section>

        <div class="inventario-bottom-grid">
            <section class="inventario-secondary-card">
                <header class="inventario-card-header"><div class="inventario-card-title"><i class="bi bi-exclamation-triangle-fill inventario-title-icon--orange" aria-hidden="true"></i><div><h2>Productos sin existencias</h2><p>Saldo físico igual a cero en el alcance autorizado.</p></div></div><a class="inventario-view-all" href="{{ route('management.inventory', ['estado' => 'sin_existencias']) }}">Ver todas</a></header>
                <div class="inventario-alert-list">
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
                    @forelse ($productosSinExistencias as $product)
                        <div class="inventario-alert-row"><span class="inventario-alert-icon inventario-alert-icon--{{ $alertPresentation[$product->id]['class'] }}"><i class="bi bi-{{ $alertPresentation[$product->id]['class'] === 'critical' ? 'box' : 'exclamation-triangle' }}" aria-hidden="true"></i></span><span><strong>{{ $product->nombre }}</strong><small>{{ $product->suppliers->first()?->nombre ?: 'Sin proveedor' }}</small></span><b class="inventario-alert-stock">{{ number_format($product->existencias_alcance) }}</b><span class="inventario-status inventario-status--{{ $alertPresentation[$product->id]['class'] }}">{{ $alertPresentation[$product->id]['label'] }}</span><button type="button" class="inventario-register-alert" data-open-inventory-movement data-inventory-product="{{ $product->id }}">Registrar</button></div>
                    @empty
                        <div class="inventario-secondary-empty"><i class="bi bi-shield-check" aria-hidden="true"></i><strong>No hay productos sin existencias</strong><span>Los saldos físicos consultados son mayores que cero.</span></div>
                    @endforelse
                </div>
            </section>

            <section class="inventario-secondary-card">
                <header class="inventario-card-header"><div class="inventario-card-title"><i class="bi bi-clock-history" aria-hidden="true"></i><div><h2>{{ $verHistorialMovimientos ? 'Historial de movimientos' : 'Movimientos recientes' }}</h2><p>{{ $verHistorialMovimientos ? 'Movimientos registrados en las sedes autorizadas.' : 'Últimos movimientos de inventario.' }}</p></div></div><a class="inventario-view-all" href="{{ $verHistorialMovimientos ? route('management.inventory') : route('management.inventory.movements') }}">{{ $verHistorialMovimientos ? 'Volver al inventario' : 'Ver todos' }}</a></header>
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
                @if ($verHistorialMovimientos)
{{-- formulario que recopila datos y los envia a la ruta indicada. --}}
                    <form class="inventario-filters" method="get" action="{{ route('management.inventory.movements') }}">
                        <label class="inventario-search"><i class="bi bi-search" aria-hidden="true"></i><span class="visually-hidden">Buscar movimientos</span><input type="search" name="mov_buscar" value="{{ $filtrosMovimientos['buscar'] }}" maxlength="100" placeholder="Producto, motivo o documento"></label>
                        <label><span class="visually-hidden">Tipo de movimiento</span><select name="mov_tipo"><option value="">Todos los tipos</option>@foreach ($tiposMovimiento as $tipo)<option value="{{ $tipo }}" @selected($filtrosMovimientos['tipo'] === $tipo)>{{ ucfirst($tipo) }}</option>@endforeach</select></label>
                        <label><span class="visually-hidden">Almacén</span><select name="mov_almacen"><option value="">Todos los almacenes autorizados</option>@foreach ($almacenesMovimiento as $almacen)<option value="{{ $almacen->id }}" @selected($filtrosMovimientos['almacen'] === (int) $almacen->id)>{{ $almacen->sede_nombre }} · {{ $almacen->nombre }}</option>@endforeach</select></label>
                        <button class="inventario-filter-button" type="submit"><i class="bi bi-funnel" aria-hidden="true"></i> Filtrar</button>
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
                        @if ($hayFiltrosMovimientos)<a class="inventario-clear-button" href="{{ route('management.inventory.movements') }}">Limpiar</a>@endif
                    </form>
                @endif
                <div class="inventario-recent-table-wrap">
                    <table class="inventario-recent-table"><thead><tr><th>Tipo</th><th>Producto</th><th>Cantidad</th><th>Motivo</th><th>Usuario</th><th>Fecha</th>@if ($verHistorialMovimientos)<th>Detalle</th>@endif</tr></thead><tbody>
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
                        @forelse ($recentMovements as $movement)
                            <tr><td><span class="inventario-recent-type inventario-recent-type--{{ $movementPresentation[$movement->id]['class'] }}"><i class="bi bi-{{ $movement->tipo_movimiento === 'ajuste' ? 'pencil' : 'arrow-'.($movement->tipo_movimiento === 'entrada' ? 'down' : 'up') }}" aria-hidden="true"></i>{{ ucfirst($movement->tipo_movimiento) }}</span></td><td>{{ $movement->product?->marca }} {{ $movement->product?->nombre ?: 'Producto eliminado' }}</td><td class="inventario-quantity {{ $movementPresentation[$movement->id]['sign'] === '+' ? 'is-positive' : 'is-negative' }}">{{ $movementPresentation[$movement->id]['sign'] }}{{ $movementPresentation[$movement->id]['quantity'] }}</td><td>{{ \Illuminate\Support\Str::limit($movement->notas, 24) }}</td><td>{{ $movement->user?->nombre ?: 'Sistema' }}</td><td>{{ $movement->creado_en?->format('d/m/Y H:i') }}</td>@if ($verHistorialMovimientos)<td><button class="inventario-more-button" type="button" data-open-movement-detail data-tipo="{{ $movement->tipo_movimiento }}" data-cantidad="{{ $movementPresentation[$movement->id]['sign'] }}{{ $movementPresentation[$movement->id]['quantity'] }}" data-fecha="{{ $movement->creado_en?->format('d/m/Y H:i') }}" data-usuario="{{ $movement->user?->nombre ?: 'Sistema' }}" data-notas="{{ $movement->notas }}" data-producto="{{ trim(($movement->product?->marca ?? '').' '.($movement->product?->nombre ?? 'Producto eliminado')) }}" data-variante="{{ $movement->variante?->codigo_sku ?: $movement->variante?->nombre_color ?: 'Sin variante' }}" data-almacen="{{ $movement->almacen?->nombre ?: 'Sin almacén' }}" data-documento="{{ trim(($movement->documento_tipo ?? '').' '.($movement->documento_id ?? '')) ?: 'Sin documento asociado' }}" aria-label="Ver detalle del movimiento"><i class="bi bi-three-dots-vertical" aria-hidden="true"></i></button></td>@endif</tr>
                        @empty
                            <tr><td colspan="{{ $verHistorialMovimientos ? 7 : 6 }}" class="inventario-recent-empty">@if ($hayFiltrosMovimientos)No se encontraron movimientos con los filtros seleccionados.@else Todavía no hay movimientos registrados.@endif</td></tr>
                        @endforelse
                    </tbody></table>
                </div>
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
                @if ($verHistorialMovimientos && $recentMovements->hasPages())
                    <div class="inventario-pagination">{{ $recentMovements->links() }}</div>
                @endif
            </section>
        </div>
    </section>

    <dialog class="inventario-dialog" id="registrar-movimiento-inventario" aria-labelledby="inventario-movimiento-titulo">
        <div class="inventario-dialog-card">
            <header class="inventario-dialog-header"><div class="inventario-card-title"><span class="inventario-dialog-icon"><i class="bi bi-arrow-left-right" aria-hidden="true"></i></span><div><h2 id="inventario-movimiento-titulo">Registrar movimiento</h2><p>Actualiza las existencias y conserva la trazabilidad.</p></div></div><button class="inventario-dialog-close" type="button" data-close-inventory-movement aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header>
{{-- formulario que recopila datos y los envia a la ruta indicada. --}}
            <form method="post" action="{{ route('management.inventory.adjust', ['product' => 0]) }}" data-ruta-base="{{ route('management.inventory.adjust', ['product' => 0]) }}" data-hay-productos="{{ $movementProducts->isNotEmpty() ? '1' : '0' }}" data-hay-almacenes="{{ $almacenes->isNotEmpty() ? '1' : '0' }}" data-inventory-movement-form class="inventario-movement-form">
{{-- proteccion CSRF que valida el origen del formulario. --}}
                @csrf
                <input type="hidden" name="producto_id" value="{{ old('producto_id') }}" data-inventory-product-input>
                <label><span>Producto <b>*</b></span><select data-inventory-product-select required><option value="">Selecciona un producto</option>@foreach ($movementProducts as $product)<option value="{{ $product->id }}" @selected((string) old('producto_id') === (string) $product->id)>{{ $product->marca }} {{ $product->nombre }} · Existencias: {{ $product->existencias_alcance }}</option>@endforeach</select></label>
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
                @if ($movementProducts->isEmpty())
                    <p class="inventario-count-notice"><i class="bi bi-info-circle" aria-hidden="true"></i>No hay productos registrados para esta empresa. Registra primero un producto en <a href="{{ route('management.products') }}">Gestión de productos</a>.</p>
                @endif
                <label><span>Almacén <b>*</b></span><select name="almacen_id" data-inventory-warehouse-select required><option value="">Selecciona un almacén</option>@foreach ($almacenes as $almacen)<option value="{{ $almacen->id }}" @selected((string) old('almacen_id') === (string) $almacen->id)>{{ $almacen->sede_nombre }} · {{ $almacen->nombre }}</option>@endforeach</select></label>
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
                @if ($almacenes->isEmpty())
                    <p class="inventario-count-notice"><i class="bi bi-info-circle" aria-hidden="true"></i>No hay almacenes activos autorizados para esta empresa y sede. Configura primero un almacén para registrar existencias.</p>
                @endif
                <label><span>Variante</span><select name="variante_id" data-inventory-variant-select><option value="" @selected(old('variante_id') === null)>Producto sin variante</option>@foreach ($movementProducts as $product)@foreach ($product->variants as $variante)<option value="{{ $variante->id }}" data-producto-id="{{ $product->id }}" @selected((string) old('variante_id') === (string) $variante->id)>{{ $product->nombre }} · {{ $variante->codigo_sku ?: $variante->nombre_color }}</option>@endforeach @endforeach</select></label>
                <label><span>Tipo de movimiento <b>*</b></span><select name="tipo_movimiento" data-inventory-type-select required><option value="entrada" @selected(old('tipo_movimiento') === 'entrada')>Entrada</option><option value="salida" @selected(old('tipo_movimiento') === 'salida')>Salida</option><option value="ajuste" @selected(old('tipo_movimiento') === 'ajuste')>Ajuste</option></select></label>
                <label><span><span data-inventory-quantity-label>Cantidad del movimiento</span> <b>*</b></span><input type="number" name="cantidad" min="1" value="{{ old('cantidad') }}" data-inventory-quantity required></label>
                <label class="inventario-form-wide"><span>Motivo u observación <b>*</b></span><textarea name="notas" maxlength="500" rows="3" required>{{ old('notas') }}</textarea></label>
                <footer class="inventario-dialog-footer"><button class="inventario-cancel-button" type="button" data-close-inventory-movement>Cancelar</button><button class="inventario-primary-button" type="submit" @disabled($movementProducts->isEmpty() || $almacenes->isEmpty())><i class="bi bi-check2" aria-hidden="true"></i> Guardar movimiento</button></footer>
            </form>
        </div>
    </dialog>

    <dialog class="inventario-dialog inventario-detail-dialog" data-inventory-detail-dialog aria-labelledby="inventory-detail-title">
        <div class="inventario-dialog-card"><header class="inventario-dialog-header"><div class="inventario-card-title"><span class="inventario-dialog-icon"><i class="bi bi-box-seam" aria-hidden="true"></i></span><div><h2 id="inventory-detail-title">Detalle de inventario</h2><p>Consulta de solo lectura con la información de esta pantalla.</p></div></div><button class="inventario-dialog-close" type="button" data-close-inventory-detail aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header><dl class="inventario-detail-list" data-inventory-detail-fields></dl><footer class="inventario-dialog-footer"><button class="inventario-cancel-button" type="button" data-close-inventory-detail>Cerrar</button></footer></div>
    </dialog>
@endsection

@vite('resources/js/gestion/inventario-logistica/existencias-movimientos/listar-existencias.js')
