@extends('layouts.administracion')

@section('title', 'Auditoría')

@section('content')
<section class="container content-page">
    <h1>Auditoría</h1>

    {{-- Formulario de filtros --}}
    <form method="GET" class="filter-form">
        <label>
            Entidad
            <input type="text" name="entidad" value="{{ request('entidad') }}">
        </label>

        <label>
            Desde
            <input type="date" name="desde" value="{{ request('desde') }}">
        </label>

        <label>
            Hasta
            <input type="date" name="hasta" value="{{ request('hasta') }}">
        </label>

        <button type="submit" class="btn btn-primary">Filtrar</button>
    </form>

    {{-- Listado de registros de auditoría --}}
    <div class="logs-list">
        @foreach($logs as $log)
            <article class="data-row">
                <div>
                    <b>{{ $log->accion }} · {{ $log->entidad }} #{{ $log->entidad_id }}</b>
                    <small>Usuario {{ $log->usuario_id ?? 'sistema' }} · {{ $log->creado_en }} · {{ $log->direccion_ip }}</small>
                </div>

                @include('gestion.auditoria.ver', ['log' => $log])
            </article>
        @endforeach
    </div>

    {{-- Paginación --}}
    <div class="pagination-container">
        {{ $logs->links() }}
    </div>
</section>
@endsection