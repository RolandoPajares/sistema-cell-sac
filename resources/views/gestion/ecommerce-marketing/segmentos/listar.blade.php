@extends('layouts.panel.principal')

@section('title', 'Segmentación')
@php($isAdmin = auth()->user()?->can('gestion.seguridad.consultar'))
@section('body_class', $isAdmin ? 'modulo-segmentos' : '')

@section('content')
@if (! $isAdmin)
<section class="container content-page">
    <h1>Segmentación de clientes</h1>
    <p class="notice">Organiza audiencias para tus acciones comerciales.</p>
    @include('gestion.ecommerce-marketing.segmentos.crear', ['roles' => $roles])
    @foreach ($segments as $segment)
    <article class="data-row">
        <strong>{{ $segment->nombre }}</strong>
        <span>{{ ucfirst($segment->tipo ?: 'Manual') }}</span>
        <span>{{ $segment->activo ? 'Activo' : 'Inactivo' }}</span>
    </article>
    @endforeach
    {{ $segments->links() }}
</section>
@else
<section class="admin-marketing-module admin-segmentos-page" data-pending-module="segmentos" data-em-segment-page data-em-segment-base-url="{{ url('/gestion/segmentos') }}">
    <header class="admin-marketing-header">
        <nav class="admin-marketing-breadcrumb" aria-label="Ruta de navegación"><span>Administración</span><i class="bi bi-chevron-right" aria-hidden="true"></i><span>Marketing</span><i class="bi bi-chevron-right" aria-hidden="true"></i><strong>Segmentos de clientes</strong></nav>
        <div class="admin-marketing-heading-row">
            <div><span class="admin-marketing-eyebrow"><i class="bi bi-people" aria-hidden="true"></i> Marketing</span>
                <h1>Gestión de segmentos de clientes</h1>
                <p>Organiza audiencias por comportamiento y compras para acciones comerciales más precisas.</p>
            </div>
            <div class="admin-marketing-actions"><a class="admin-marketing-button admin-marketing-button--secondary" href="{{ route('management.segments', request()->query()) }}"><i class="bi bi-arrow-repeat" aria-hidden="true"></i> Actualizar</a><details class="admin-marketing-export-menu"><summary class="admin-marketing-button admin-marketing-button--secondary"><i class="bi bi-download" aria-hidden="true"></i> Exportar <i class="bi bi-chevron-down" aria-hidden="true"></i></summary><div class="admin-marketing-export-options"><a href="{{ route('management.segments.export', array_merge(request()->query(), ['format' => 'pdf'])) }}"><i class="bi bi-file-earmark-pdf" aria-hidden="true"></i><span><strong>Exportar PDF</strong><small>Documento imprimible</small></span></a><a href="{{ route('management.segments.export', array_merge(request()->query(), ['format' => 'excel'])) }}"><i class="bi bi-file-earmark-spreadsheet" aria-hidden="true"></i><span><strong>Exportar Excel</strong><small>Archivo compatible .xlsx</small></span></a><a href="{{ route('management.segments.export', array_merge(request()->query(), ['format' => 'csv'])) }}"><i class="bi bi-filetype-csv" aria-hidden="true"></i><span><strong>Exportar CSV</strong><small>Datos delimitados</small></span></a></div></details><button class="admin-marketing-button admin-marketing-button--primary" type="button" data-pending-open="crear"><i class="bi bi-plus-lg" aria-hidden="true"></i> Nuevo segmento</button></div>
        </div>
    </header>

    <section class="admin-marketing-kpis" aria-label="Indicadores de segmentación">
        <article class="admin-marketing-kpi admin-marketing-kpi--blue"><span><i class="bi bi-people" aria-hidden="true"></i></span>
            <div><small>Segmentos creados</small><strong>{{ number_format($segments->total()) }}</strong><em>Registros en el sistema</em></div>
        </article>
        <article class="admin-marketing-kpi admin-marketing-kpi--orange"><span><i class="bi bi-people-fill" aria-hidden="true"></i></span>
            <div><small>Clientes clasificados</small><strong>0</strong><em>No hay relación de clientes en este esquema</em></div>
        </article>
        <article class="admin-marketing-kpi admin-marketing-kpi--purple"><span><i class="bi bi-arrow-repeat" aria-hidden="true"></i></span>
            <div><small>Segmentos dinámicos</small><strong>{{ number_format($dynamicSegments) }}</strong><em>Según el tipo almacenado</em></div>
        </article>
        <article class="admin-marketing-kpi admin-marketing-kpi--green"><span><i class="bi bi-bullseye" aria-hidden="true"></i></span>
            <div><small>Segmentos activos</small><strong>{{ number_format($activeSegments) }}</strong><em>Estado almacenado en MariaDB</em></div>
        </article>
    </section>

    <form class="admin-marketing-filters" method="get" action="{{ route('management.segments') }}"><label class="admin-marketing-search"><i class="bi bi-search" aria-hidden="true"></i><span class="visually-hidden">Buscar segmentos</span><input type="search" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar segmento o tipo..."></label><label><span class="visually-hidden">Tipo</span><select name="tipo">
                <option value="">Tipo: Todos</option>
                <option value="roles" @selected(request('tipo') === 'roles')>Por roles</option>
            </select></label><label><span class="visually-hidden">Estado</span><select name="estado">
                <option value="">Estado: Todos</option>
                <option value="activo" @selected(request('estado') === 'activo')>Activo</option>
                <option value="inactivo" @selected(request('estado') === 'inactivo')>Inactivo</option>
            </select></label><button class="admin-marketing-filter-button" type="submit"><i class="bi bi-funnel" aria-hidden="true"></i> Filtrar</button><a class="admin-marketing-clear-button" href="{{ route('management.segments') }}">Limpiar filtros</a></form>

    <section class="admin-marketing-table-card">
        <header class="admin-marketing-card-header">
            <div><span class="admin-marketing-card-icon"><i class="bi bi-people" aria-hidden="true"></i></span>
                <div>
                    <h2>Listado de segmentos</h2>
                    <p>{{ number_format($segments->total()) }} segmentos disponibles para administrar.</p>
                </div>
            </div><span class="admin-marketing-count">{{ number_format($segments->total()) }} segmentos</span>
        </header>
        <div class="admin-marketing-table-wrap">
            <table class="admin-marketing-table">
                <thead>
                    <tr>
                        <th>Segmento</th>
                        <th>Criterio principal</th>
                        <th>Tipo</th>
                        <th>Clientes</th>
                        <th>Conversión</th>
                        <th>Actualización</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($segments as $segment)
                    <tr>
                        <td><strong>{{ $segment->nombre }}</strong><small>Audiencia administrada</small></td>
                        <td>{{ implode(', ', $segment->criterios['roles'] ?? []) ?: 'Criterios de rol' }}</td>
                        <td><span class="admin-marketing-status admin-marketing-status--blue">{{ ucfirst($segment->tipo ?: 'Manual') }}</span></td>
                <td>0</td>
                <td>No disponible</td>
                        <td>Sin actividad</td>
                        <td><span class="admin-marketing-status admin-marketing-status--active"><i class="bi bi-circle-fill" aria-hidden="true"></i>{{ $segment->activo ? 'Activo' : 'Inactivo' }}</span></td>
                        <td class="admin-marketing-row-actions" data-em-segment-row data-em-segment-id="{{ $segment->id }}" data-em-segment-name="{{ $segment->nombre }}" data-em-segment-type="{{ $segment->tipo ?: 'Manual' }}" data-em-segment-active="{{ $segment->activo ? 'Activo' : 'Inactivo' }}" data-em-segment-roles="{{ implode('|', array_map('strval', $segment->criterios['roles'] ?? [])) }}"><button type="button" data-em-segment-action="detail"><i class="bi bi-eye" aria-hidden="true"></i><span>Ver</span></button><button type="button" data-em-segment-action="edit"><i class="bi bi-pencil" aria-hidden="true"></i><span>Editar</span></button><button class="is-danger" type="button" data-em-segment-action="remove"><i class="bi bi-trash3" aria-hidden="true"></i><span>Desactivar</span></button></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8">
                            <div class="admin-marketing-empty"><span><i class="bi bi-people" aria-hidden="true"></i></span><strong>No hay segmentos para mostrar</strong>
                                <p>Crea un segmento para organizar audiencias y campañas con mayor precisión.</p><button type="button" class="admin-marketing-empty-button" data-pending-open="crear"><i class="bi bi-plus-lg" aria-hidden="true"></i> Crear segmento</button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <footer class="admin-marketing-table-footer"><span>Mostrando {{ $segments->firstItem() ?? 0 }} a {{ $segments->lastItem() ?? 0 }} de {{ number_format($segments->total()) }} segmentos</span>{{ $segments->links() }}</footer>
    </section>

    <section class="admin-marketing-bottom-grid">
        <article class="admin-marketing-summary-card">
            <header>
                <div><span class="admin-marketing-summary-icon admin-marketing-summary-icon--blue"><i class="bi bi-bar-chart" aria-hidden="true"></i></span>
                    <div>
                        <h2>Distribución de clientes</h2>
                        <p>Clasificación por comportamiento.</p>
                    </div>
                </div><span class="admin-marketing-summary-unavailable">Sin datos de clientes</span>
            </header>
            <div class="admin-marketing-summary-empty"><i class="bi bi-people" aria-hidden="true"></i><strong>Sin distribución disponible</strong><span>Los clientes aparecerán al integrar sus criterios.</span></div>
        </article>
        <article class="admin-marketing-summary-card">
            <header>
                <div><span class="admin-marketing-summary-icon admin-marketing-summary-icon--orange"><i class="bi bi-award" aria-hidden="true"></i></span>
                    <div>
                        <h2>Segmentos de alto valor</h2>
                        <p>Audiencias prioritarias.</p>
                    </div>
                </div><span class="admin-marketing-summary-unavailable">Sin datos de compras</span>
            </header>
            <div class="admin-marketing-summary-empty"><i class="bi bi-stars" aria-hidden="true"></i><strong>Sin ranking todavía</strong><span>El valor se calculará con datos comerciales.</span></div>
        </article>
        <article class="admin-marketing-summary-card">
            <header>
                <div><span class="admin-marketing-summary-icon admin-marketing-summary-icon--purple"><i class="bi bi-clock-history" aria-hidden="true"></i></span>
                    <div>
                        <h2>Actividad de segmentación</h2>
                        <p>Últimos cambios aplicados.</p>
                    </div>
                </div><span class="admin-marketing-summary-unavailable">Sin actividad registrada</span>
            </header>
            <div class="admin-marketing-summary-empty"><i class="bi bi-clock" aria-hidden="true"></i><strong>Sin actividad reciente</strong><span>Las actualizaciones aparecerán aquí.</span></div>
        </article>
    </section>

    <dialog class="admin-marketing-dialog gestion-pendiente-modal" data-pending-modal="crear" aria-labelledby="segmentos-crear-title">
        <div class="admin-marketing-dialog-card">
            <header>
                <div><span class="admin-marketing-dialog-icon"><i class="bi bi-people" aria-hidden="true"></i></span>
                    <div><small>Marketing</small>
                        <h2 id="segmentos-crear-title">Nuevo segmento</h2>
                        <p>Define una audiencia para tus campañas.</p>
                    </div>
                </div><button type="button" data-pending-close aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
            </header>
            <div class="admin-marketing-dialog-body">@include('gestion.ecommerce-marketing.segmentos.crear', ['roles' => $roles])</div>
        </div>
    </dialog>
    <dialog class="admin-marketing-dialog gestion-pendiente-modal em-marketing-dialog" data-em-segment-dialog="detail" aria-labelledby="em-segment-detail-title">
        <div class="admin-marketing-dialog-card">
            <header>
                <div><span class="admin-marketing-dialog-icon"><i class="bi bi-people" aria-hidden="true"></i></span>
                    <div><small>Marketing · Consulta</small>
                        <h2 id="em-segment-detail-title">Detalle del segmento</h2>
                        <p>Criterios configurados en el registro seleccionado.</p>
                    </div>
                </div><button type="button" data-em-segment-close aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
            </header>
            <div class="admin-marketing-dialog-body">
                <div class="em-marketing-dialog__notice">Solo se muestra la configuración guardada. No se consultan clientes ni se calcula el tamaño de la audiencia.</div>
                <dl class="em-marketing-dialog__details">
                    <div>
                        <dt>Nombre</dt>
                        <dd data-em-segment-detail="name">—</dd>
                    </div>
                    <div>
                        <dt>Tipo</dt>
                        <dd data-em-segment-detail="type">—</dd>
                    </div>
                    <div>
                        <dt>Roles incluidos</dt>
                        <dd data-em-segment-detail="roles">—</dd>
                    </div>
                    <div>
                        <dt>Estado</dt>
                        <dd data-em-segment-detail="active">—</dd>
                    </div>
                </dl>
                <p class="em-marketing-dialog__feedback" data-em-segment-detail-note role="status" aria-live="polite">Métricas y pertenencia de clientes pendientes de integración.</p>
            </div>
            <footer class="em-marketing-dialog__footer"><button type="button" class="admin-marketing-button admin-marketing-button--secondary" data-em-segment-close>Cerrar</button></footer>
        </div>
    </dialog>
    <dialog class="admin-marketing-dialog gestion-pendiente-modal em-marketing-dialog" data-em-segment-dialog="edit" aria-labelledby="em-segment-edit-title">
        <div class="admin-marketing-dialog-card">
            <header>
                <div><span class="admin-marketing-dialog-icon"><i class="bi bi-sliders" aria-hidden="true"></i></span>
                    <div><small>Marketing · Edición visual</small>
                        <h2 id="em-segment-edit-title">Editar reglas del segmento</h2>
                        <p>Los criterios disponibles reflejan los roles admitidos actualmente.</p>
                    </div>
                </div><button type="button" data-em-segment-close aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
            </header>
            <form method="post" class="admin-marketing-dialog-body" data-em-segment-edit-form>
                @csrf
                @method('PUT')
                <div class="em-marketing-dialog__notice">Los cambios se guardarán en segmentos_clientes y se registrarán en auditoría.</div>
                <div class="em-marketing-dialog__grid"><label class="em-marketing-dialog__full">Nombre del segmento<input type="text" maxlength="120" required data-em-segment-edit-name></label>
                        <fieldset class="em-marketing-dialog__roles">
                        <legend>Roles incluidos *</legend>@foreach ($roles as $role)<label><input type="checkbox" name="roles[]" value="{{ $role }}" data-em-segment-role> {{ str_replace('_', ' ', $role) }}</label>@endforeach
                    </fieldset>
                </div>
                <div class="em-marketing-dialog__preview"><strong>Vista previa ilustrativa</strong><span data-em-segment-preview>Selecciona uno o más roles para describir la regla. No se buscarán clientes.</span></div>
                <p class="em-marketing-dialog__error" data-em-segment-edit-error role="alert" hidden></p>
                <p class="em-marketing-dialog__feedback" data-em-segment-edit-feedback role="status" aria-live="polite" hidden></p>
            </form>
            <footer class="em-marketing-dialog__footer"><button type="button" class="admin-marketing-button admin-marketing-button--secondary" data-em-segment-close>Cancelar</button><button type="button" class="admin-marketing-button admin-marketing-button--primary" data-em-segment-edit-confirm>Guardar cambios</button></footer>
        </div>
    </dialog>
    <dialog class="admin-marketing-dialog gestion-pendiente-modal em-marketing-dialog" data-em-segment-dialog="remove" aria-labelledby="em-segment-remove-title">
        <div class="admin-marketing-dialog-card">
            <header>
                <div><span class="admin-marketing-dialog-icon"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i></span>
                    <div><small>Marketing · Confirmación</small>
                        <h2 id="em-segment-remove-title">Eliminar segmento</h2>
                        <p>Revisa el posible impacto antes de continuar.</p>
                    </div>
                </div><button type="button" data-em-segment-close aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
            </header>
            <form method="post" class="admin-marketing-dialog-body" data-em-segment-remove-form>
                @csrf
                @method('DELETE')
                <div class="em-marketing-dialog__warning"><strong>El segmento se desactivará, no se eliminará físicamente.</strong><span>Así se conserva la trazabilidad y se evita romper campañas asociadas.</span></div>
                <p class="em-marketing-dialog__record">Segmento seleccionado: <strong data-em-segment-remove-name>—</strong></p><label class="em-marketing-dialog__check"><input type="checkbox" data-em-segment-remove-ack> Entiendo que esta confirmación no elimina el segmento.</label>
                <p class="em-marketing-dialog__feedback" data-em-segment-remove-feedback role="status" aria-live="polite" hidden></p>
            </form>
            <footer class="em-marketing-dialog__footer"><button type="button" class="admin-marketing-button admin-marketing-button--secondary" data-em-segment-close>Volver</button><button type="button" class="admin-marketing-button admin-marketing-button--primary" data-em-segment-remove-confirm>Desactivar segmento</button></footer>
        </div>
    </dialog>
    <div class="gestion-pendiente-feedback" data-pending-feedback role="status" aria-live="polite" hidden></div>
</section>
@endif
@endsection
