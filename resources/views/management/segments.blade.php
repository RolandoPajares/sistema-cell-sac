
@extends('layouts.admin')

@section('title', 'Segmentación')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/cell-legacy/css/modulos/marketing/segmentos.css') }}">
@endpush

@section('content')
<section class="segments-page">

    <header class="segments-heading">
        <span class="segments-eyebrow">Marketing Center</span>

        <h1>
            Segmentación por rol
            <i class="bi bi-diagram-3"></i>
        </h1>

        <p>
            Administra qué perfiles pueden visualizar las
            campañas internas de CELL SAC.
        </p>
    </header>

    @if(session('status'))
        <div class="segments-success" role="status">
            <i class="bi bi-check-circle"></i>
            {{ session('status') }}
        </div>
    @endif

    <section class="segments-panel">

        <header class="segments-panel-header">
            <div>
                <h2>
                    <i class="bi bi-people"></i>
                    Segmentos registrados
                </h2>
                <p>
                    Organiza los grupos de usuarios según sus roles.
                </p>
            </div>

            <button type="button"
                    class="segments-primary-button"
                    onclick="document.getElementById('segment-create-modal').showModal()">
                <i class="bi bi-plus-lg"></i>
                Nuevo segmento
            </button>
        </header>

        <div class="segments-grid">

            @forelse($segments as $segment)

                <article class="segment-card">

                    <div class="segment-card-top">
                        <span class="segment-card-icon">
                            <i class="bi bi-people"></i>
                        </span>

                        <span class="segment-status {{ $segment->activo ? 'is-active' : 'is-inactive' }}">
                            <i class="bi bi-circle-fill"></i>
                            {{ $segment->activo ? 'Activo' : 'Inactivo' }}
                        </span>
                    </div>

                    <h3>{{ $segment->nombre }}</h3>

                    <div class="segment-roles-heading">
                        <i class="bi bi-person-check"></i>
                        Roles incluidos
                    </div>

                    <div class="segment-roles">
                        @forelse(($segment->criterios['roles'] ?? []) as $role)
                            <span class="segment-role-badge">
                                {{ str_replace('_', ' ', $role) }}
                            </span>
                        @empty
                            <span class="segment-no-roles">
                                Sin roles asignados
                            </span>
                        @endforelse
                    </div>

                    <footer class="segment-card-footer">
                        <i class="bi bi-info-circle"></i>
                        Segmentación utilizada para mostrar campañas
                        según el perfil del usuario.
                    </footer>

                </article>

            @empty

                <div class="segments-empty">
                    <i class="bi bi-people"></i>
                    <h3>No hay segmentos registrados</h3>
                    <p>
                        Crea tu primer segmento para organizar
                        los perfiles de las campañas.
                    </p>
                </div>

            @endforelse

        </div>

        <div class="segments-pagination">
            {{ $segments->links() }}
        </div>

    </section>
</section>

{{-- MODAL NUEVO SEGMENTO --}}

<dialog id="segment-create-modal"
        class="segment-modal"
        aria-labelledby="segment-modal-title">

    <div class="segment-modal-header">
        <div>
            <h2 id="segment-modal-title">
                Nuevo segmento
            </h2>
            <p>
                Selecciona los roles que formarán parte del segmento.
            </p>
        </div>

        <button type="button"
                class="segment-modal-close"
                aria-label="Cerrar"
                onclick="this.closest('dialog').close()">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <form method="post"
          class="segment-form"
          action="{{ route('management.segments.store') }}">

        @csrf

        <div class="segment-form-body">

            @if($errors->any())
                <div class="segment-form-errors" role="alert">
                    <strong>Revisa los siguientes errores:</strong>
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <label class="segment-field">
                <span>Nombre del segmento</span>

                <input type="text"
                       name="nombre"
                       maxlength="120"
                       value="{{ old('nombre') }}"
                       placeholder="Ej. Clientes frecuentes"
                       required>
            </label>

            <fieldset class="segment-roles-fieldset">
                <legend>Roles incluidos</legend>

                <div class="segment-checkbox-grid">

                    @foreach($roles as $role)

                        <label class="segment-checkbox">
                            <input type="checkbox"
                                   name="roles[]"
                                   value="{{ $role }}"
                                   @checked(in_array($role, old('roles', [])))>

                            <span>
                                {{ ucfirst(str_replace('_', ' ', $role)) }}
                            </span>
                        </label>

                    @endforeach

                </div>
            </fieldset>

        </div>

        <div class="segment-form-footer">

            <button type="button"
                    class="segment-cancel-button"
                    onclick="this.closest('dialog').close()">
                Cancelar
            </button>

            <button type="submit"
                    class="segment-submit-button">
                <i class="bi bi-check-circle"></i>
                Crear segmento
            </button>

        </div>

    </form>
</dialog>

@if($errors->any())
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('segment-create-modal')?.showModal();
    });
</script>
@endif

@endsection
