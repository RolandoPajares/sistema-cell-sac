@extends('layouts.panel.principal')


{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('title', 'Clientes')
{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('body_class', 'modulo-clientes')

{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('content')
    <section class="clientes-page" data-client-page data-open-on-load="{{ $errors->any() ? 'true' : 'false' }}">
        <header class="clientes-hero">
            <div>
                <span class="clientes-eyebrow">CRM</span>
                <h1>Clientes <i class="bi bi-people" aria-hidden="true"></i></h1>
                <p>Gestiona perfiles minoristas y mayoristas vinculados a la operación.</p>
            </div>
            <div class="clientes-actions"><details class="clientes-export-menu"><summary class="clientes-export-button"><i class="bi bi-download" aria-hidden="true"></i> Exportar</summary><div class="clientes-export-options"><a href="{{ route('management.customers.export', array_merge(request()->query(), ['format' => 'pdf'])) }}"><i class="bi bi-file-earmark-pdf" aria-hidden="true"></i><span><strong>Exportar PDF</strong><small>Documento imprimible</small></span></a><a href="{{ route('management.customers.export', array_merge(request()->query(), ['format' => 'excel'])) }}"><i class="bi bi-file-earmark-spreadsheet" aria-hidden="true"></i><span><strong>Exportar Excel</strong><small>Archivo compatible .xlsx</small></span></a><a href="{{ route('management.customers.export', array_merge(request()->query(), ['format' => 'csv'])) }}"><i class="bi bi-filetype-csv" aria-hidden="true"></i><span><strong>Exportar CSV</strong><small>Datos delimitados</small></span></a></div></details><button class="clientes-export-button" type="button" data-open-client-import><i class="bi bi-file-earmark-arrow-up" aria-hidden="true"></i> Importar CSV</button><button class="clientes-primary-button" type="button" data-open-client-create><i class="bi bi-plus-lg" aria-hidden="true"></i> Nuevo cliente</button></div>
        </header>

        <section class="clientes-kpi-grid" aria-label="Resumen de clientes">
            <article class="clientes-kpi clientes-kpi--blue"><span class="clientes-kpi-icon"><i class="bi bi-people" aria-hidden="true"></i></span><div><small>Total de clientes</small><strong>{{ number_format($totalCustomers) }}</strong><span>Registrados en el sistema</span></div></article>
            <article class="clientes-kpi clientes-kpi--green"><span class="clientes-kpi-icon"><i class="bi bi-person-check" aria-hidden="true"></i></span><div><small>Clientes activos</small><strong>{{ number_format($activeCustomers) }}</strong><span>Con cuenta habilitada</span></div></article>
            <article class="clientes-kpi clientes-kpi--purple"><span class="clientes-kpi-icon"><i class="bi bi-award" aria-hidden="true"></i></span><div><small>Mayoristas</small><strong>{{ number_format($wholesaleCustomers) }}</strong><span>{{ number_format($wholesalePercent, 1) }}% del total</span></div></article>
            <article class="clientes-kpi clientes-kpi--orange"><span class="clientes-kpi-icon"><i class="bi bi-person" aria-hidden="true"></i></span><div><small>Minoristas</small><strong>{{ number_format($retailCustomers) }}</strong><span>{{ number_format($retailPercent, 1) }}% del total</span></div></article>
        </section>

        <div class="clientes-layout">
            <main>
{{-- formulario que recopila datos y los envia a la ruta indicada. --}}
                <form class="clientes-filters" method="get" action="{{ route('management.customers') }}">
                    <label class="clientes-search"><i class="bi bi-search" aria-hidden="true"></i><span class="visually-hidden">Buscar clientes</span><input type="search" name="buscar" value="{{ $filters['buscar'] ?? '' }}" placeholder="Buscar clientes por nombre, documento, correo o teléfono..."></label>
                    <label><span class="visually-hidden">Filtrar por tipo</span><select name="tipo"><option value="">Tipo: Todos</option><option value="mayorista" @selected(($filters['tipo'] ?? null) === 'mayorista')>Mayorista</option><option value="minorista" @selected(($filters['tipo'] ?? null) === 'minorista')>Minorista</option></select></label>
                    <label><span class="visually-hidden">Filtrar por estado</span><select name="estado"><option value="">Estado: Todos</option><option value="activo" @selected(($filters['estado'] ?? null) === 'activo')>Activos</option><option value="inactivo" @selected(($filters['estado'] ?? null) === 'inactivo')>Inactivos</option></select></label>
                    <label><span class="visually-hidden">Filtrar por ciudad</span><select name="ciudad"><option value="">Ciudad: Todas</option>@foreach ($cities as $city)<option value="{{ $city }}" @selected(($filters['ciudad'] ?? null) === $city)>{{ $city }}</option>@endforeach</select></label>
                    <button class="clientes-filter-button" type="submit"><i class="bi bi-arrow-repeat" aria-hidden="true"></i> Aplicar filtros</button>
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
                    @if (($filters['buscar'] ?? null) || ($filters['tipo'] ?? null) || ($filters['estado'] ?? null) || ($filters['ciudad'] ?? null))<a class="clientes-clear-button" href="{{ route('management.customers') }}">Limpiar filtros</a>@endif
                </form>

                <section class="clientes-list-card">
                    <header class="clientes-card-header"><div class="clientes-card-title"><i class="bi bi-people" aria-hidden="true"></i><div><h2>Listado de clientes</h2><p>Mostrando {{ $customers->firstItem() ?? 0 }} a {{ $customers->lastItem() ?? 0 }} de {{ number_format($customers->total()) }} clientes</p></div></div><span class="clientes-sort-label">Ordenar por: <b>Nombre (A-Z)</b></span></header>
                    <div class="clientes-table-wrap">
                        <table class="clientes-table">
                            <thead><tr><th><span class="clientes-checkbox" aria-hidden="true"></span></th><th>#</th><th>Cliente</th><th>Tipo</th><th>Documento</th><th>Correo</th><th>Teléfono</th><th>Ciudad</th><th>Estado</th><th>Última compra</th><th>Acciones</th></tr></thead>
                            <tbody>
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
                                @forelse ($customers as $customer)
                                    <tr>
                                        <td><span class="clientes-checkbox" aria-hidden="true"></span></td>
                                        <td class="clientes-index">{{ $customer->id }}</td>
                                        <td><span class="clientes-identity"><b class="clientes-avatar clientes-avatar--{{ $customerPresentation[$customer->id]['type']['class'] }}">{{ $customerPresentation[$customer->id]['initials'] }}</b><span><strong>{{ $customer->nombre_contacto }}</strong><small>{{ $customer->correo }}</small></span></span></td>
                                        <td><span class="clientes-type clientes-type--{{ $customerPresentation[$customer->id]['type']['class'] }}">{{ $customerPresentation[$customer->id]['type']['label'] }}</span></td>
                                        <td class="clientes-document">{{ $customer->documento }}</td>
                                        <td class="clientes-email">{{ $customer->correo }}</td>
                                        <td>{{ $customer->telefono ?: '—' }}</td>
                                        <td>{{ $customer->ciudad ?: '—' }}</td>
                                        <td><span class="clientes-status clientes-status--{{ $customer->activo ? 'active' : 'inactive' }}"><i class="bi bi-circle-fill" aria-hidden="true"></i>{{ $customer->activo ? 'Activo' : 'Inactivo' }}</span></td>
                                        <td class="clientes-last-purchase">{{ $customerPresentation[$customer->id]['last_purchase_label'] }}</td>
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
                    <div class="clientes-commercial-list"><div><span class="clientes-commercial-icon clientes-commercial-icon--purple"><i class="bi bi-award" aria-hidden="true"></i></span><span><small>Segmentos</small><strong>{{ number_format($wholesaleCustomers) }} mayoristas · {{ number_format($retailCustomers) }} minoristas</strong></span></div><div><span class="clientes-commercial-icon clientes-commercial-icon--green"><i class="bi bi-bar-chart" aria-hidden="true"></i></span><span><small>Nivel de compras</small><strong>{{ $commercialLevel }}</strong></span></div><div><span class="clientes-commercial-icon clientes-commercial-icon--blue"><i class="bi bi-cart3" aria-hidden="true"></i></span><span><small>Total de compras</small><strong>S/ {{ number_format($totalPurchases, 2) }}</strong></span></div><div><span class="clientes-commercial-icon clientes-commercial-icon--blue"><i class="bi bi-calendar3" aria-hidden="true"></i></span><span><small>Última compra</small><strong>{{ $lastPurchaseLabel }}</strong></span></div><div><span class="clientes-commercial-icon clientes-commercial-icon--purple"><i class="bi bi-credit-card" aria-hidden="true"></i></span><span><small>Método de pago</small><strong>No registrado</strong></span></div><div><span class="clientes-commercial-icon clientes-commercial-icon--green"><i class="bi bi-person-check" aria-hidden="true"></i></span><span><small>Cuentas habilitadas</small><strong>{{ number_format($activeAccounts) }}</strong></span></div></div>
                </section>
                <section class="clientes-side-card clientes-activity-card"><header class="clientes-card-header"><div class="clientes-card-title"><i class="bi bi-clock-history" aria-hidden="true"></i><div><h2>Actividad reciente</h2></div></div><button class="clientes-view-all" type="button" data-refresh-clients>Actualizar</button></header><div class="clientes-activity-list">@forelse ($recentActivity as $activity)<div class="clientes-activity-row"><span class="clientes-activity-icon clientes-activity-icon--{{ $activity['color'] }}"><i class="bi bi-{{ $activity['icon'] }}" aria-hidden="true"></i></span><span><strong>{{ $activity['title'] }}</strong><small>{{ $activity['detail'] }}</small></span><time>{{ $activity['date']?->format('d M Y') }}</time></div>@empty<div class="clientes-secondary-empty">Todavía no hay actividad.</div>@endforelse</div></section>
            </aside>
        </div>
    </section>

    <dialog class="clientes-dialog" id="nuevo-cliente" aria-labelledby="cliente-crear-titulo"><div class="clientes-dialog-card"><header class="clientes-dialog-header"><div><span class="clientes-eyebrow">CRM</span><h2 id="cliente-crear-titulo">Registrar nuevo cliente</h2><p>Completa la información comercial y de contacto.</p></div><button class="clientes-dialog-close" type="button" data-close-client-dialog aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header>@include('gestion.comercial.clientes.registrar')</div></dialog>
    <dialog class="clientes-dialog clientes-import-dialog" id="importar-clientes" aria-labelledby="clientes-import-title" data-client-import-dialog>
        <div class="clientes-dialog-card">
            <header class="clientes-dialog-header clientes-import-header">
                <div class="clientes-import-heading"><span class="clientes-import-icon"><i class="bi bi-file-earmark-arrow-up" aria-hidden="true"></i></span><div><span class="clientes-eyebrow">CRM · Importación masiva</span><h2 id="clientes-import-title">Importar clientes</h2><p>Valida una plantilla y revisa las filas antes de guardarlas en MariaDB.</p></div></div>
                <button class="clientes-dialog-close" type="button" data-close-client-dialog aria-label="Cerrar importación"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
            </header>
{{-- formulario que recopila datos y los envia a la ruta indicada. --}}
            <form method="post" action="{{ route('management.customers.import') }}" enctype="multipart/form-data" data-client-import-form>
{{-- proteccion CSRF que valida el origen del formulario. --}}
            @csrf
            <div class="clientes-import-body">
                <div class="clientes-import-demo-note"><i class="bi bi-database-check" aria-hidden="true"></i><span>El CSV se valida en el servidor y se guarda en MariaDB dentro de una transacción. No se crean cuentas de usuario.</span></div>
                <nav class="clientes-import-steps" aria-label="Etapas de importación">
                    <span data-client-import-indicator="0"><b>1</b> Plantilla</span><i aria-hidden="true"></i><span data-client-import-indicator="1"><b>2</b> Archivo</span><i aria-hidden="true"></i><span data-client-import-indicator="2"><b>3</b> Vista previa</span><i aria-hidden="true"></i><span data-client-import-indicator="3"><b>4</b> Validaciones</span><i aria-hidden="true"></i><span data-client-import-indicator="4"><b>5</b> Confirmar</span>
                </nav>
                <section class="clientes-import-stage" data-client-import-stage="0">
                    <h3>1. Descargar plantilla</h3><p>CSV UTF-8 compatible con Excel. Requeridos: tipo, documento, nombre de contacto y correo. Opcionales: razón social, teléfono y ciudad. Importa perfiles de cliente; no crea cuentas de usuario.</p>
                    <button class="clientes-export-button clientes-import-download" type="button" data-client-import-download><i class="bi bi-download" aria-hidden="true"></i> Descargar plantilla CSV</button>
                </section>
                <section class="clientes-import-stage" data-client-import-stage="1" hidden>
                    <h3>2. Seleccionar archivo</h3><label class="clientes-import-field" for="clientes-import-file"><span>Archivo CSV UTF-8</span><input id="clientes-import-file" name="archivo" type="file" accept=".csv,.txt" data-client-import-file required></label>
                    <small class="clientes-import-hint">La cabecera debe coincidir con la plantilla y el archivo se validará nuevamente en el servidor.</small><p class="clientes-import-file-name" data-client-import-file-name>No se ha seleccionado un archivo.</p>
                </section>
                <section class="clientes-import-stage" data-client-import-stage="2" hidden>
                    <h3>3. Vista previa del archivo seleccionado</h3><p>Se muestran hasta 10 filas reales del CSV seleccionado; la validación definitiva ocurre en el servidor.</p>
                    <div class="clientes-import-table-wrap"><table><thead><tr><th>Tipo</th><th>Documento</th><th>Nombre de contacto</th><th>Razón social</th><th>Correo</th><th>Teléfono</th><th>Ciudad</th></tr></thead><tbody data-client-import-preview><tr><td colspan="7">Selecciona un CSV para consultar su contenido.</td></tr></tbody></table></div>
                </section>
                <section class="clientes-import-stage" data-client-import-stage="3" hidden>
                    <h3>4. Validaciones del archivo</h3><ul class="clientes-import-validation-list" data-client-import-validations><li><i class="bi bi-info-circle" aria-hidden="true"></i> Selecciona un archivo para validar sus columnas.</li></ul>
                </section>
                <section class="clientes-import-stage" data-client-import-stage="4" hidden>
                    <h3>5. Confirmar importación</h3><p>Archivo seleccionado: <strong data-client-import-final-file>—</strong></p><label class="clientes-import-ack"><input type="checkbox" name="confirmado" value="1" data-client-import-ack> Confirmo que revisé la vista previa y deseo guardar los clientes válidos en MariaDB.</label><p class="clientes-import-feedback" data-client-import-feedback role="status" aria-live="polite" hidden></p>
                </section>
                <p class="clientes-import-error" data-client-import-error role="alert" hidden></p>
            </div>
            <footer class="clientes-dialog-footer clientes-import-footer"><button class="clientes-cancel-button" type="button" data-close-client-dialog>Cancelar</button><button class="clientes-cancel-button" type="button" data-client-import-previous hidden>Anterior</button><button class="clientes-primary-button" type="button" data-client-import-next>Siguiente</button><button class="clientes-primary-button" type="submit" data-client-import-confirm hidden>Importar clientes</button></footer>
            </form>
        </div>
    </dialog>
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
    @foreach ($customers as $customer)
        <dialog class="clientes-dialog" id="cliente-editar-{{ $customer->id }}" aria-labelledby="cliente-editar-titulo-{{ $customer->id }}"><div class="clientes-dialog-card"><header class="clientes-dialog-header"><div><span class="clientes-eyebrow">CRM</span><h2 id="cliente-editar-titulo-{{ $customer->id }}">Editar cliente</h2><p>Actualiza la información de {{ $customer->nombre_contacto }}.</p></div><button class="clientes-dialog-close" type="button" data-close-client-dialog aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header>@include('gestion.comercial.clientes.editar', ['customer' => $customer])</div></dialog>
        <dialog class="clientes-dialog clientes-view-dialog" id="cliente-{{ $customer->id }}" aria-labelledby="cliente-detalle-titulo-{{ $customer->id }}"><div class="clientes-dialog-card"><header class="clientes-dialog-header"><div><span class="clientes-eyebrow">{{ $customerPresentation[$customer->id]['type']['label'] }}</span><h2 id="cliente-detalle-titulo-{{ $customer->id }}">{{ $customer->nombre_contacto }}</h2><p>{{ $customer->correo }} · {{ $customer->ciudad ?: 'Ciudad no registrada' }}</p></div><button class="clientes-dialog-close" type="button" data-close-client-dialog aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header><div class="clientes-view-body"><div class="clientes-view-identity"><b class="clientes-avatar clientes-avatar--{{ $customerPresentation[$customer->id]['type']['class'] }}">{{ $customerPresentation[$customer->id]['initials'] }}</b><div><h3>{{ $customer->nombre_contacto }}</h3><p>{{ $customer->razon_social ?: 'Perfil de cliente registrado en el sistema.' }}</p></div></div><div class="clientes-view-grid"><div><small>Tipo</small><strong>{{ $customerPresentation[$customer->id]['type']['label'] }}</strong></div><div><small>Documento</small><strong>{{ $customer->documento }}</strong></div><div><small>Teléfono</small><strong>{{ $customer->telefono ?: 'No registrado' }}</strong></div><div><small>Estado</small><span class="clientes-status clientes-status--{{ $customer->activo ? 'active' : 'inactive' }}">{{ $customer->activo ? 'Activo' : 'Inactivo' }}</span></div></div><div class="clientes-view-contact"><small>Dirección comercial</small><p>{{ $customer->ciudad ?: 'Ciudad no registrada' }}</p></div></div><footer class="clientes-dialog-footer"><button class="clientes-cancel-button" type="button" data-close-client-dialog>Cerrar</button><button class="clientes-primary-button" type="button" data-open-client-edit="cliente-editar-{{ $customer->id }}"><i class="bi bi-pencil" aria-hidden="true"></i> Editar cliente</button></footer></div></dialog>
    @endforeach
@endsection

{{-- bloque de estilos o scripts para el lugar reservado por la plantilla. --}}
@push('scripts')
    @vite('resources/js/gestion/comercial/clientes/index.js')
@endpush

{{-- bloque de estilos o scripts para el lugar reservado por la plantilla. --}}
@push('styles')
    @vite('resources/css/gestion/comercial/clientes/clientes.css')
@endpush









