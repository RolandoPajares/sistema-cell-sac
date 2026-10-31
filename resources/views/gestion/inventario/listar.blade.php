@extends('layouts.administracion')

@section('title', 'Inventario')
@section('body_class', 'modulo-inventario')

@section('content')
    @php
        $productImage = static function (?string $path): ?string {
            if (!$path) {
                return null;
            }

            return str_starts_with($path, 'http') ? $path : asset(ltrim($path, '/'));
        };
        $statusForStock = static function (int $stock): array {
            return match (true) {
                $stock > 10 => ['label' => 'Óptimo', 'class' => 'optimal'],
                $stock > 0 => ['label' => 'Bajo', 'class' => 'low'],
                default => ['label' => 'Crítico', 'class' => 'critical'],
            };
        };
        $monthNames = [1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr', 5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic'];
        $today = now();
        $todayLabel = $today->format('d').' de '.$monthNames[(int) $today->format('n')].', '.$today->format('Y');
    @endphp

    <section class="inventario-page" data-inventory-page>
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
                <div><small>Productos en stock</small><strong>{{ number_format($activeProducts) }}</strong><span>Productos disponibles para la venta</span></div>
                <b class="inventario-kpi-trend is-positive"><i class="bi bi-arrow-up-right" aria-hidden="true"></i> Stock disponible</b>
            </article>
            <article class="inventario-kpi inventario-kpi--orange">
                <span class="inventario-kpi-icon"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i></span>
                <div><small>Stock bajo</small><strong>{{ number_format($lowStock) }}</strong><span>Productos por debajo del mínimo</span></div>
                <b class="inventario-kpi-trend {{ $lowStock > 0 ? 'is-negative' : 'is-positive' }}"><i class="bi bi-{{ $lowStock > 0 ? 'arrow-up-right' : 'check' }}" aria-hidden="true"></i> {{ $lowStock > 0 ? 'Revisar' : 'Correcto' }}</b>
            </article>
            <article class="inventario-kpi inventario-kpi--indigo">
                <span class="inventario-kpi-icon"><i class="bi bi-arrow-left-right" aria-hidden="true"></i></span>
                <div><small>Movimientos hoy</small><strong>{{ number_format($movementsToday) }}</strong><span>Entradas, salidas y ajustes</span></div>
                @if ($movementsTrend !== null)
                    <b class="inventario-kpi-trend {{ $movementsTrend >= 0 ? 'is-positive' : 'is-negative' }}"><i class="bi bi-arrow-{{ $movementsTrend >= 0 ? 'up' : 'down' }}-right" aria-hidden="true"></i> {{ number_format(abs($movementsTrend), 1) }}%</b>
                @else
                    <b class="inventario-kpi-trend">Sin periodo anterior</b>
                @endif
            </article>
            <article class="inventario-kpi inventario-kpi--green">
                <span class="inventario-kpi-icon"><i class="bi bi-cash-stack" aria-hidden="true"></i></span>
                <div><small>Valor inventario</small><strong>S/ {{ number_format($inventoryValue, 2) }}</strong><span>Valor registrado en productos activos</span></div>
                <b class="inventario-kpi-trend is-positive"><i class="bi bi-graph-up-arrow" aria-hidden="true"></i> Valor actual</b>
            </article>
        </section>

        <div class="inventario-toolbar">
            <button class="inventario-primary-button" type="button" data-open-inventory-movement><i class="bi bi-plus-lg" aria-hidden="true"></i> Registrar movimiento <i class="bi bi-chevron-down" aria-hidden="true"></i></button>
            <button class="inventario-toolbar-button inventario-toolbar-button--green" type="button" data-open-inventory-movement data-movement-type="entrada"><i class="bi bi-arrow-down" aria-hidden="true"></i> Entrada</button>
            <button class="inventario-toolbar-button inventario-toolbar-button--red" type="button" data-open-inventory-movement data-movement-type="salida"><i class="bi bi-arrow-up" aria-hidden="true"></i> Salida</button>
            <button class="inventario-toolbar-button inventario-toolbar-button--purple" type="button" data-open-inventory-movement data-movement-type="ajuste"><i class="bi bi-pencil" aria-hidden="true"></i> Ajuste</button>
            <button class="inventario-toolbar-button" type="button" disabled><i class="bi bi-file-earmark-excel" aria-hidden="true"></i> Importar Excel</button>
        </div>

        <form class="inventario-filters" method="get" action="{{ route('management.inventory') }}">
            <label class="inventario-search"><i class="bi bi-search" aria-hidden="true"></i><span class="visually-hidden">Buscar producto</span><input type="search" name="buscar" value="{{ $filters['buscar'] ?? '' }}" placeholder="Buscar producto, SKU..."></label>
            <label><span class="visually-hidden">Filtrar por categoría</span><select name="categoria"><option value="">Todas las categorías</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected(($filters['categoria'] ?? null) === $category->id)>{{ $category->nombre }}</option>@endforeach</select></label>
            <label><span class="visually-hidden">Filtrar por estado</span><select name="estado"><option value="">Todos los estados</option><option value="optimo" @selected(($filters['estado'] ?? null) === 'optimo')>Óptimo</option><option value="bajo" @selected(($filters['estado'] ?? null) === 'bajo')>Bajo</option><option value="critico" @selected(($filters['estado'] ?? null) === 'critico')>Crítico</option></select></label>
            <label><span class="visually-hidden">Filtrar por proveedor</span><select name="proveedor"><option value="">Todos los proveedores</option>@foreach ($suppliers as $supplier)<option value="{{ $supplier->id }}" @selected(($filters['proveedor'] ?? null) === $supplier->id)>{{ $supplier->nombre }}</option>@endforeach</select></label>
            <button class="inventario-filter-button" type="submit"><i class="bi bi-search" aria-hidden="true"></i> Buscar</button>
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
                    <thead><tr><th><span class="inventario-checkbox" aria-hidden="true"></span></th><th>Producto</th><th>SKU</th><th>Categoría</th><th>Stock actual</th><th>Stock mínimo</th><th>Proveedor</th><th>Último movimiento</th><th>Estado</th><th>Acción</th></tr></thead>
                    <tbody>
                        @forelse ($products as $product)
                            @php
                                $image = $productImage($product->url_imagen);
                                $status = $statusForStock((int) $product->existencias);
                                $movement = $latestMovements->get($product->id);
                                $movementLabel = match ($movement?->tipo_movimiento) { 'entrada' => 'Entrada', 'salida' => 'Salida', 'ajuste' => 'Ajuste', default => 'Sin movimientos' };
                                $movementClass = match ($movement?->tipo_movimiento) { 'entrada' => 'entry', 'salida' => 'exit', 'ajuste' => 'adjustment', default => 'empty' };
                            @endphp
                            <tr>
                                <td><span class="inventario-checkbox" aria-hidden="true"></span></td>
                                <td class="inventario-product-cell">
                                    @if ($image)<img src="{{ $image }}" alt="{{ $product->nombre }}">@else<span class="inventario-product-image"><i class="bi bi-box-seam" aria-hidden="true"></i></span>@endif
                                    <span><strong>{{ $product->marca }} {{ $product->nombre }}</strong><small>{{ $product->nombre }}</small></span>
                                </td>
                                <td class="inventario-sku">{{ $product->etiqueta ?: 'SKU-'.str_pad((string) $product->id, 4, '0', STR_PAD_LEFT) }}</td>
                                <td>{{ $product->category?->nombre ?: ($product->categoria ?: 'Sin categoría') }}</td>
                                <td><span class="inventario-stock inventario-stock--{{ $status['class'] }}">{{ number_format($product->existencias) }} unidades</span></td>
                                <td class="inventario-minimum">10</td>
                                <td>{{ $product->suppliers->first()?->nombre ?: 'Sin proveedor' }}</td>
                                <td class="inventario-movement inventario-movement--{{ $movementClass }}">
                                    @if ($movement)
                                        <strong><i class="bi bi-arrow-{{ $movement->tipo_movimiento === 'entrada' ? 'down' : 'up' }}" aria-hidden="true"></i> {{ $movementLabel }}</strong><small>{{ $movement->creado_en?->format('d/m/Y, H:i') }}</small>
                                    @else
                                        <span>Sin movimientos</span>
                                    @endif
                                </td>
                                <td><span class="inventario-status inventario-status--{{ $status['class'] }}"><i class="bi bi-circle-fill" aria-hidden="true"></i>{{ $status['label'] }}</span></td>
                                <td class="inventario-actions-cell"><button class="inventario-view-button" type="button" data-open-inventory-movement data-inventory-product="{{ $product->id }}" title="Registrar movimiento para {{ $product->nombre }}"><i class="bi bi-eye" aria-hidden="true"></i><span>Ver</span></button><button class="inventario-more-button" type="button" disabled aria-label="Más opciones"><i class="bi bi-three-dots-vertical" aria-hidden="true"></i></button></td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="inventario-empty"><i class="bi bi-inbox" aria-hidden="true"></i><strong>No hay productos para mostrar.</strong><span>Prueba con otros filtros de inventario.</span></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <footer class="inventario-pagination">
                <span>Mostrando {{ $products->firstItem() ?? 0 }} a {{ $products->lastItem() ?? 0 }} de {{ $products->total() }} productos</span>
                <form class="inventario-per-page-form" method="get" action="{{ route('management.inventory') }}">
                    @foreach (request()->except(['page', 'por_pagina']) as $key => $value)
                        @if (is_scalar($value))<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endif
                    @endforeach
                    <label for="inventario-por-pagina">Productos por página</label>
                    <select id="inventario-por-pagina" name="por_pagina" onchange="this.form.submit()" aria-label="Productos por página">@foreach ([7, 8, 10, 20, 50] as $option)<option value="{{ $option }}" @selected(($filters['por_pagina'] ?? 7) === $option)>{{ $option }}</option>@endforeach</select>
                </form>
                {{ $products->onEachSide(1)->links() }}
            </footer>
        </section>

        <div class="inventario-bottom-grid">
            <section class="inventario-secondary-card">
                <header class="inventario-card-header"><div class="inventario-card-title"><i class="bi bi-exclamation-triangle-fill inventario-title-icon--orange" aria-hidden="true"></i><div><h2>Alertas de inventario</h2><p>Productos con stock igual o menor al mínimo.</p></div></div><a class="inventario-view-all" href="{{ route('management.inventory', ['estado' => 'critico']) }}">Ver todas</a></header>
                <div class="inventario-alert-list">
                    @forelse ($lowStockProducts as $product)
                        @php
                            $status = $statusForStock((int) $product->existencias);
                        @endphp
                        <div class="inventario-alert-row"><span class="inventario-alert-icon inventario-alert-icon--{{ $status['class'] }}"><i class="bi bi-{{ $status['class'] === 'critical' ? 'box' : 'exclamation-triangle' }}" aria-hidden="true"></i></span><span><strong>{{ $product->nombre }}</strong><small>{{ $product->suppliers->first()?->nombre ?: 'Sin proveedor' }}</small></span><b class="inventario-alert-stock">{{ number_format($product->existencias) }}</b><span class="inventario-status inventario-status--{{ $status['class'] }}">{{ $status['label'] }}</span><button type="button" class="inventario-register-alert" data-open-inventory-movement data-inventory-product="{{ $product->id }}">Registrar</button></div>
                    @empty
                        <div class="inventario-secondary-empty"><i class="bi bi-shield-check" aria-hidden="true"></i><strong>Sin alertas de inventario</strong><span>Los productos activos están sobre el mínimo.</span></div>
                    @endforelse
                </div>
            </section>

            <section class="inventario-secondary-card">
                <header class="inventario-card-header"><div class="inventario-card-title"><i class="bi bi-clock-history" aria-hidden="true"></i><div><h2>Movimientos recientes</h2><p>Últimos movimientos de inventario.</p></div></div><a class="inventario-view-all" href="{{ route('management.inventory') }}">Ver todos</a></header>
                <div class="inventario-recent-table-wrap">
                    <table class="inventario-recent-table"><thead><tr><th>Tipo</th><th>Producto</th><th>Cantidad</th><th>Motivo</th><th>Usuario</th><th>Fecha</th></tr></thead><tbody>
                        @forelse ($recentMovements as $movement)
                            @php
                                $movementClass = $movement->tipo_movimiento === 'entrada' ? 'entry' : ($movement->tipo_movimiento === 'salida' ? 'exit' : 'adjustment');
                                $movementQuantity = $movement->tipo_movimiento === 'salida' ? -abs((int) $movement->cantidad) : (int) $movement->cantidad;
                            @endphp
                            <tr><td><span class="inventario-recent-type inventario-recent-type--{{ $movementClass }}"><i class="bi bi-{{ $movement->tipo_movimiento === 'ajuste' ? 'pencil' : 'arrow-'.($movement->tipo_movimiento === 'entrada' ? 'down' : 'up') }}" aria-hidden="true"></i>{{ ucfirst($movement->tipo_movimiento) }}</span></td><td>{{ $movement->product?->marca }} {{ $movement->product?->nombre ?: 'Producto eliminado' }}</td><td class="inventario-quantity {{ $movementQuantity >= 0 ? 'is-positive' : 'is-negative' }}">{{ $movementQuantity >= 0 ? '+' : '' }}{{ $movementQuantity }}</td><td>{{ \Illuminate\Support\Str::limit($movement->notas, 24) }}</td><td>{{ $movement->user?->nombre ?: 'Sistema' }}</td><td>{{ $movement->creado_en?->format('d/m/Y H:i') }}</td></tr>
                        @empty
                            <tr><td colspan="6" class="inventario-recent-empty">Todavía no hay movimientos registrados.</td></tr>
                        @endforelse
                    </tbody></table>
                </div>
            </section>
        </div>
    </section>

    <dialog class="inventario-dialog" id="registrar-movimiento-inventario">
        <div class="inventario-dialog-card">
            <header class="inventario-dialog-header"><div class="inventario-card-title"><span class="inventario-dialog-icon"><i class="bi bi-arrow-left-right" aria-hidden="true"></i></span><div><h2>Registrar movimiento</h2><p>Actualiza las existencias y conserva la trazabilidad.</p></div></div><button class="inventario-dialog-close" type="button" data-close-inventory-movement aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header>
            <form method="post" action="{{ route('legacy.admin.inventory.store') }}" class="inventario-movement-form">
                @csrf
                <label><span>Producto <b>*</b></span><select name="producto_id" data-inventory-product-select required><option value="">Selecciona un producto</option>@foreach ($movementProducts as $product)<option value="{{ $product->id }}" @selected((string) old('producto_id') === (string) $product->id)>{{ $product->marca }} {{ $product->nombre }} · Stock: {{ $product->existencias }}</option>@endforeach</select></label>
                <label><span>Tipo de movimiento <b>*</b></span><select name="tipo_movimiento" data-inventory-type-select required><option value="entrada" @selected(old('tipo_movimiento') === 'entrada')>Entrada</option><option value="salida" @selected(old('tipo_movimiento') === 'salida')>Salida</option><option value="ajuste" @selected(old('tipo_movimiento') === 'ajuste')>Ajuste</option></select></label>
                <label><span>Cantidad <b>*</b></span><input type="number" name="cantidad" min="1" value="{{ old('cantidad') }}" required></label>
                <label class="inventario-form-wide"><span>Motivo u observación <b>*</b></span><textarea name="notas" maxlength="500" rows="3" required>{{ old('notas') }}</textarea></label>
                <footer class="inventario-dialog-footer"><button class="inventario-cancel-button" type="button" data-close-inventory-movement>Cancelar</button><button class="inventario-primary-button" type="submit"><i class="bi bi-check2" aria-hidden="true"></i> Guardar movimiento</button></footer>
            </form>
        </div>
    </dialog>
@endsection

@push('scripts')
    <script>
        (() => {
            const dialog = document.querySelector('#registrar-movimiento-inventario');
            const page = document.querySelector('[data-inventory-page]');
            const productSelect = dialog?.querySelector('[data-inventory-product-select]');
            const typeSelect = dialog?.querySelector('[data-inventory-type-select]');

            if (!dialog || !page || !productSelect || !typeSelect) {
                return;
            }

            page.querySelectorAll('[data-open-inventory-movement]').forEach((button) => {
                button.addEventListener('click', () => {
                    if (button.dataset.inventoryProduct) {
                        productSelect.value = button.dataset.inventoryProduct;
                    }
                    if (button.dataset.movementType) {
                        typeSelect.value = button.dataset.movementType;
                    }
                    dialog.showModal();
                });
            });

            document.querySelectorAll('[data-close-inventory-movement]').forEach((button) => button.addEventListener('click', () => dialog.close()));
            dialog.addEventListener('click', (event) => {
                if (event.target === dialog) {
                    dialog.close();
                }
            });

            @if ($errors->any())
                dialog.showModal();
            @endif
        })();
    </script>
@endpush
