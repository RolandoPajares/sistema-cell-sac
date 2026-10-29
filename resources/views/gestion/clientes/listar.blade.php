@extends('layouts.administracion')

@section('title', 'Clientes')
@section('body_class', 'modulo-clientes')

@section('content')
    @php
        $typeFor = static fn (string $type): array => $type === 'mayorista' ? ['label' => 'Mayorista', 'class' => 'wholesale'] : ['label' => 'Minorista', 'class' => 'retail'];
        $initialsFor = static function (?string $name): string {
            $parts = preg_split('/\s+/', trim((string) $name), -1, PREG_SPLIT_NO_EMPTY);

            return collect(array_slice($parts ?: ['CL'], 0, 2))->map(static fn (string $part): string => mb_strtoupper(mb_substr($part, 0, 1)))->implode('');
        };
        $months = [1 => 'ene.', 2 => 'feb.', 3 => 'mar.', 4 => 'abr.', 5 => 'may.', 6 => 'jun.', 7 => 'jul.', 8 => 'ago.', 9 => 'sep.', 10 => 'oct.', 11 => 'nov.', 12 => 'dic.'];
        $dateLabel = static fn ($date): string => $date ? $date->format('d').' '.$months[(int) $date->format('n')].' '.$date->format('Y') : 'Sin compras';
        $wholesalePercent = $totalCustomers > 0 ? round(($wholesaleCustomers / $totalCustomers) * 100, 1) : 0;
        $retailPercent = $totalCustomers > 0 ? round(($retailCustomers / $totalCustomers) * 100, 1) : 0;
        $commercialLevel = $totalPurchases > 0 ? 'Activo' : 'Sin compras';
    @endphp

    <section class="clientes-page" data-client-page>
        <header class="clientes-hero">
            <div>
                <span class="clientes-eyebrow">CRM</span>
                <h1>Clientes <i class="bi bi-people" aria-hidden="true"></i></h1>
                <p>Gestiona perfiles minoristas y mayoristas vinculados a la operación.</p>
            </div>
            <div class="clientes-actions"><button class="clientes-export-button" type="button" disabled><i class="bi bi-upload" aria-hidden="true"></i> Exportar</button><button class="clientes-primary-button" type="button" data-open-client-create><i class="bi bi-plus-lg" aria-hidden="true"></i> Nuevo cliente</button></div>
        </header>

        <section class="clientes-kpi-grid" aria-label="Resumen de clientes">
            <article class="clientes-kpi clientes-kpi--blue"><span class="clientes-kpi-icon"><i class="bi bi-people" aria-hidden="true"></i></span><div><small>Total de clientes</small><strong>{{ number_format($totalCustomers) }}</strong><span>Registrados en el sistema</span></div></article>
            <article class="clientes-kpi clientes-kpi--green"><span class="clientes-kpi-icon"><i class="bi bi-person-check" aria-hidden="true"></i></span><div><small>Clientes activos</small><strong>{{ number_format($activeCustomers) }}</strong><span>Con cuenta habilitada</span></div></article>
            <article class="clientes-kpi clientes-kpi--purple"><span class="clientes-kpi-icon"><i class="bi bi-award" aria-hidden="true"></i></span><div><small>Mayoristas</small><strong>{{ number_format($wholesaleCustomers) }}</strong><span>{{ number_format($wholesalePercent, 1) }}% del total</span></div></article>
            <article class="clientes-kpi clientes-kpi--orange"><span class="clientes-kpi-icon"><i class="bi bi-person" aria-hidden="true"></i></span><div><small>Minoristas</small><strong>{{ number_format($retailCustomers) }}</strong><span>{{ number_format($retailPercent, 1) }}% del total</span></div></article>
        </section>

        <div class="clientes-layout">
            <main>
                <form class="clientes-filters" method="get" action="{{ route('management.customers') }}">
                    <label class="clientes-search"><i class="bi bi-search" aria-hidden="true"></i><span class="visually-hidden">Buscar clientes</span><input type="search" name="buscar" value="{{ $filters['buscar'] ?? '' }}" placeholder="Buscar clientes por nombre, documento, correo o teléfono..."></label>
                    <label><span class="visually-hidden">Filtrar por tipo</span><select name="tipo"><option value="">Tipo: Todos</option><option value="mayorista" @selected(($filters['tipo'] ?? null) === 'mayorista')>Mayorista</option><option value="minorista" @selected(($filters['tipo'] ?? null) === 'minorista')>Minorista</option></select></label>
                    <label><span class="visually-hidden">Filtrar por estado</span><select name="estado"><option value="">Estado: Todos</option><option value="activo" @selected(($filters['estado'] ?? null) === 'activo')>Activos</option><option value="inactivo" @selected(($filters['estado'] ?? null) === 'inactivo')>Inactivos</option></select></label>
                    <label><span class="visually-hidden">Filtrar por ciudad</span><select name="ciudad"><option value="">Ciudad: Todas</option>@foreach ($cities as $city)<option value="{{ $city }}" @selected(($filters['ciudad'] ?? null) === $city)>{{ $city }}</option>@endforeach</select></label>
                    <button class="clientes-filter-button" type="submit"><i class="bi bi-arrow-repeat" aria-hidden="true"></i> Aplicar filtros</button>
                    @if (($filters['buscar'] ?? null) || ($filters['tipo'] ?? null) || ($filters['estado'] ?? null) || ($filters['ciudad'] ?? null))<a class="clientes-clear-button" href="{{ route('management.customers') }}">Limpiar filtros</a>@endif
                </form>

                <section class="clientes-list-card">
                    <header class="clientes-card-header"><div class="clientes-card-title"><i class="bi bi-people" aria-hidden="true"></i><div><h2>Listado de clientes</h2><p>Mostrando {{ $customers->firstItem() ?? 0 }} a {{ $customers->lastItem() ?? 0 }} de {{ number_format($customers->total()) }} clientes</p></div></div><span class="clientes-sort-label">Ordenar por: <b>Nombre (A-Z)</b></span></header>
                    <div class="clientes-table-wrap">
                        <table class="clientes-table">
                            <thead><tr><th><span class="clientes-checkbox" aria-hidden="true"></span></th><th>#</th><th>Cliente</th><th>Tipo</th><th>Documento</th><th>Correo</th><th>Teléfono</th><th>Ciudad</th><th>Estado</th><th>Última compra</th><th>Acciones</th></tr></thead>
                            <tbody>
                                @forelse ($customers as $customer)
                                    @php $type = $typeFor($customer->tipo); $lastPurchase = $lastPurchases->get($customer->usuario_id); @endphp
                                    <tr>
                                        <td><span class="clientes-checkbox" aria-hidden="true"></span></td>
                                        <td class="clientes-index">{{ $customer->id }}</td>
                                        <td><span class="clientes-identity"><b class="clientes-avatar clientes-avatar--{{ $type['class'] }}">{{ $initialsFor($customer->nombre_contacto) }}</b><span><strong>{{ $customer->nombre_contacto }}</strong><small>{{ $customer->correo }}</small></span></span></td>
                                        <td><span class="clientes-type clientes-type--{{ $type['class'] }}">{{ $type['label'] }}</span></td>
                                        <td class="clientes-document">{{ $customer->documento }}</td>
                                        <td class="clientes-email">{{ $customer->correo }}</td>
                                        <td>{{ $customer->telefono ?: '—' }}</td>
                                        <td>{{ $customer->ciudad ?: '—' }}</td>
                                        <td><span class="clientes-status clientes-status--{{ $customer->activo ? 'active' : 'inactive' }}"><i class="bi bi-circle-fill" aria-hidden="true"></i>{{ $customer->activo ? 'Activo' : 'Inactivo' }}</span></td>
                                        <td class="clientes-last-purchase">@if ($lastPurchase){{ $dateLabel($lastPurchase->creado_en) }}<small>S/ {{ number_format((float) $lastPurchase->total, 2) }}</small>@else—@endif</td>
                                        <td class="clientes-actions-cell"><button class="clientes-action" type="button" data-open-client-view="cliente-{{ $customer->id }}" title="Ver cliente" aria-label="Ver {{ $customer->nombre_contacto }}"><i class="bi bi-eye" aria-hidden="true"></i></button><button class="clientes-action" type="button" data-open-client-edit="cliente-editar-{{ $customer->id }}" title="Editar cliente" aria-label="Editar {{ $customer->nombre_contacto }}"><i class="bi bi-pencil" aria-hidden="true"></i></button>@if ($customer->activo)<form method="post" action="{{ route('management.customers.deactivate', $customer) }}">@csrf @method('DELETE')<button class="clientes-action clientes-action--danger" type="submit" data-confirm="¿Desactivar este cliente?" title="Desactivar cliente" aria-label="Desactivar {{ $customer->nombre_contacto }}"><i class="bi bi-trash3" aria-hidden="true"></i></button></form>@endif</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="11" class="clientes-empty"><i class="bi bi-people" aria-hidden="true"></i><strong>No hay clientes que coincidan con los filtros.</strong><span>Prueba con otros criterios de búsqueda.</span></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <footer class="clientes-pagination"><span>Mostrando {{ $customers->firstItem() ?? 0 }} a {{ $customers->lastItem() ?? 0 }} de {{ number_format($customers->total()) }} clientes</span><form class="clientes-per-page" method="get" action="{{ route('management.customers') }}">@foreach (request()->except(['page', 'por_pagina']) as $key => $value)@if(is_scalar($value))<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endif @endforeach<label for="clientes-por-pagina">Clientes por página</label><select id="clientes-por-pagina" name="por_pagina" onchange="this.form.submit()" aria-label="Clientes por página">@foreach ([10, 20, 50] as $option)<option value="{{ $option }}" @selected(($filters['por_pagina'] ?? 10) === $option)>{{ $option }}</option>@endforeach</select></form>{{ $customers->onEachSide(1)->links() }}</footer>
                </section>
            </main>

            <aside class="clientes-sidebar">
                <section class="clientes-side-card">
                    <header class="clientes-side-title"><h2>Datos comerciales</h2></header>
                    <div class="clientes-commercial-list"><div><span class="clientes-commercial-icon clientes-commercial-icon--purple"><i class="bi bi-award" aria-hidden="true"></i></span><span><small>Segmentos</small><strong>{{ number_format($wholesaleCustomers) }} mayoristas · {{ number_format($retailCustomers) }} minoristas</strong></span></div><div><span class="clientes-commercial-icon clientes-commercial-icon--green"><i class="bi bi-bar-chart" aria-hidden="true"></i></span><span><small>Nivel de compras</small><strong>{{ $commercialLevel }}</strong></span></div><div><span class="clientes-commercial-icon clientes-commercial-icon--blue"><i class="bi bi-cart3" aria-hidden="true"></i></span><span><small>Total de compras</small><strong>S/ {{ number_format($totalPurchases, 2) }}</strong></span></div><div><span class="clientes-commercial-icon clientes-commercial-icon--blue"><i class="bi bi-calendar3" aria-hidden="true"></i></span><span><small>Última compra</small><strong>{{ $lastPurchase ? $dateLabel($lastPurchase->creado_en) : 'Sin compras' }}</strong></span></div><div><span class="clientes-commercial-icon clientes-commercial-icon--purple"><i class="bi bi-credit-card" aria-hidden="true"></i></span><span><small>Método de pago</small><strong>No registrado</strong></span></div><div><span class="clientes-commercial-icon clientes-commercial-icon--green"><i class="bi bi-person-check" aria-hidden="true"></i></span><span><small>Cuentas habilitadas</small><strong>{{ number_format($activeAccounts) }}</strong></span></div></div>
                </section>
                <section class="clientes-side-card clientes-activity-card"><header class="clientes-card-header"><div class="clientes-card-title"><i class="bi bi-clock-history" aria-hidden="true"></i><div><h2>Actividad reciente</h2></div></div><button class="clientes-view-all" type="button" data-refresh-clients>Actualizar</button></header><div class="clientes-activity-list">@forelse ($recentActivity as $activity)<div class="clientes-activity-row"><span class="clientes-activity-icon clientes-activity-icon--{{ $activity['color'] }}"><i class="bi bi-{{ $activity['icon'] }}" aria-hidden="true"></i></span><span><strong>{{ $activity['title'] }}</strong><small>{{ $activity['detail'] }}</small></span><time>{{ $activity['date']?->format('d M Y') }}</time></div>@empty<div class="clientes-secondary-empty">Todavía no hay actividad.</div>@endforelse</div></section>
            </aside>
        </div>
    </section>

    <dialog class="clientes-dialog" id="nuevo-cliente"><div class="clientes-dialog-card"><header class="clientes-dialog-header"><div><span class="clientes-eyebrow">CRM</span><h2>Registrar nuevo cliente</h2><p>Completa la información comercial y de contacto.</p></div><button class="clientes-dialog-close" type="button" data-close-client-dialog aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header>@include('gestion.clientes.crear')</div></dialog>
    @foreach ($customers as $customer)
        @php $type = $typeFor($customer->tipo); @endphp
        <dialog class="clientes-dialog" id="cliente-editar-{{ $customer->id }}"><div class="clientes-dialog-card"><header class="clientes-dialog-header"><div><span class="clientes-eyebrow">CRM</span><h2>Editar cliente</h2><p>Actualiza la información de {{ $customer->nombre_contacto }}.</p></div><button class="clientes-dialog-close" type="button" data-close-client-dialog aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header>@include('gestion.clientes.editar', ['customer' => $customer])</div></dialog>
        <dialog class="clientes-dialog clientes-view-dialog" id="cliente-{{ $customer->id }}"><div class="clientes-dialog-card"><header class="clientes-dialog-header"><div><span class="clientes-eyebrow">{{ $type['label'] }}</span><h2>{{ $customer->nombre_contacto }}</h2><p>{{ $customer->correo }} · {{ $customer->ciudad ?: 'Ciudad no registrada' }}</p></div><button class="clientes-dialog-close" type="button" data-close-client-dialog aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header><div class="clientes-view-body"><div class="clientes-view-identity"><b class="clientes-avatar clientes-avatar--{{ $type['class'] }}">{{ $initialsFor($customer->nombre_contacto) }}</b><div><h3>{{ $customer->nombre_contacto }}</h3><p>{{ $customer->razon_social ?: 'Perfil de cliente registrado en el sistema.' }}</p></div></div><div class="clientes-view-grid"><div><small>Tipo</small><strong>{{ $type['label'] }}</strong></div><div><small>Documento</small><strong>{{ $customer->documento }}</strong></div><div><small>Teléfono</small><strong>{{ $customer->telefono ?: 'No registrado' }}</strong></div><div><small>Estado</small><span class="clientes-status clientes-status--{{ $customer->activo ? 'active' : 'inactive' }}">{{ $customer->activo ? 'Activo' : 'Inactivo' }}</span></div></div><div class="clientes-view-contact"><small>Dirección comercial</small><p>{{ $customer->ciudad ?: 'Ciudad no registrada' }}</p></div></div><footer class="clientes-dialog-footer"><button class="clientes-cancel-button" type="button" data-close-client-dialog>Cerrar</button><button class="clientes-primary-button" type="button" data-open-client-edit="cliente-editar-{{ $customer->id }}"><i class="bi bi-pencil" aria-hidden="true"></i> Editar cliente</button></footer></div></dialog>
    @endforeach
@endsection

@push('scripts')
    <script>
        (() => {
            const page = document.querySelector('[data-client-page]');
            const createDialog = document.querySelector('#nuevo-cliente');

            if (!page || !createDialog) {
                return;
            }

            page.querySelectorAll('[data-open-client-create]').forEach((button) => button.addEventListener('click', () => createDialog.showModal()));
            document.querySelectorAll('[data-open-client-edit]').forEach((button) => button.addEventListener('click', () => document.getElementById(button.dataset.openClientEdit)?.showModal()));
            document.querySelectorAll('[data-open-client-view]').forEach((button) => button.addEventListener('click', () => document.getElementById(button.dataset.openClientView)?.showModal()));
            document.querySelectorAll('.clientes-dialog').forEach((dialog) => {
                dialog.querySelectorAll('[data-close-client-dialog]').forEach((button) => button.addEventListener('click', () => dialog.close()));
                dialog.addEventListener('click', (event) => {
                    if (event.target === dialog) {
                        dialog.close();
                    }
                });
            });
            page.querySelectorAll('[data-refresh-clients]').forEach((button) => button.addEventListener('click', () => window.location.reload()));

            @if ($errors->any())
                createDialog.showModal();
            @endif
        })();
    </script>
@endpush
