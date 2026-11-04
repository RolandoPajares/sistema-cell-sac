@extends('layouts.administracion')

@section('title', 'Módulos y pantallas')
@section('content')
    <section class="admin-page">
        <header class="admin-page-head"><div><span class="eyebrow">Seguridad y delegación</span><h1>Módulos y pantallas</h1><p>El catálogo central controla qué pantallas registradas se muestran en la navegación.</p></div></header>
        @unless ($ready)
            <div class="admin-alert admin-alert--error" role="status">El catálogo dinámico queda pendiente hasta aplicar las migraciones RBAC.</div>
        @else
            @foreach ($modules as $module)
                <section class="form-card module-registry-card"><header><div><h2><i class="bi {{ $module->icono }}" aria-hidden="true"></i> {{ $module->nombre }}</h2><small>{{ $module->codigo }} · {{ $module->habilitado ? 'Activo' : 'Inactivo' }}</small></div><form method="post" action="{{ route('management.modules.update', $module->id) }}" data-confirm="¿Cambiar el estado del módulo?">@csrf @method('PATCH')<input type="hidden" name="habilitado" value="{{ $module->habilitado ? 0 : 1 }}"><button class="btn btn-outline-primary btn-sm" type="submit" @disabled($module->codigo === 'seguridad' && $module->habilitado)>{{ $module->habilitado ? 'Desactivar módulo' : 'Activar módulo' }}</button></form></header>
                    <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Pantalla</th><th>Ruta registrada</th><th>Estado</th><th>Acción</th></tr></thead><tbody>
                        @foreach ($module->screens as $screen)
                            <tr><td>{{ $screen->nombre }}</td><td>{{ $screen->ruta ?? 'Sin ruta' }}</td><td>{{ $screen->habilitada ? 'Activa' : 'Pendiente / inactiva' }}</td><td>
                                <form method="post" action="{{ route('management.modules.screens.update', $screen->id) }}" data-confirm="¿Cambiar el estado de esta pantalla?">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="habilitada" value="{{ $screen->habilitada ? 0 : 1 }}">
                                    <button class="btn btn-outline-primary btn-sm" type="submit">{{ $screen->habilitada ? 'Desactivar' : 'Activar' }}</button>
                                </form>
                            </td></tr>
                        @endforeach
                    </tbody></table></div>
                </section>
            @endforeach
        @endunless
    </section>
@endsection
