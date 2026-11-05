@extends('layouts.administracion')

@section('title', 'Menús dinámicos')
@section('content')
    <section class="admin-page">
        <header class="admin-page-head"><div><span class="eyebrow">Seguridad y delegación</span><h1>Menús y submenús</h1><p>La navegación se guarda en MySQL y se filtra por permisos en cada petición.</p></div></header>
        @unless ($ready)
            <div class="admin-alert admin-alert--error" role="status">Las tablas RBAC aún no existen. La navegación permanece en el esquema de compatibilidad hasta aplicar las migraciones.</div>
        @else
            @foreach ($menus as $menu)
                <section class="form-card menu-editor-card">
                    <header><div><h2>{{ $menu->nombre }}</h2><small>{{ $menu->codigo }} · {{ $menu->contexto }}</small></div></header>
                    <form method="post" action="{{ route('management.menus.roles', $menu) }}" class="menu-role-form">
                        @csrf @method('PUT')
                        <fieldset><legend>Roles con acceso a esta navegación</legend>
                            @foreach ($roles as $role)
                                <label><input type="checkbox" name="roles[]" value="{{ $role->id }}" @checked($menu->roles->contains('id', $role->id))> {{ $role->nombre }}</label>
                            @endforeach
                        </fieldset>
                        <button class="btn btn-primary btn-sm" type="submit">Guardar roles</button>
                    </form>
                    <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Opción / grupo</th><th>Ruta</th><th>Orden</th><th>Estado</th><th></th></tr></thead><tbody>
                        @foreach ($menu->elementos as $element)
                            <tr class="menu-editor-group"><td><i class="bi {{ $element->icono }}" aria-hidden="true"></i> {{ $element->etiqueta }} <small>{{ $element->tipo }}</small></td><td>—</td><td>{{ $element->orden }}</td><td>{{ $element->habilitado ? 'Activo' : 'Inactivo' }}</td><td>Grupo</td></tr>
                            @foreach ($element->elementosHijos as $child)
                                <tr><td>↳ {{ $child->etiqueta }}</td><td>{{ $child->pantalla?->ruta ?? 'Sin ruta' }}</td><td>{{ $child->orden }}</td><td>{{ $child->habilitado ? 'Activo' : 'Inactivo' }}@if (! $child->pantalla?->habilitada || ! $child->pantalla?->ruta)<small> · Pendiente</small>@endif</td><td>
                                <form method="post" action="{{ route('management.menus.elements.update', $child) }}" class="menu-element-form">
                                        @csrf @method('PATCH')
                                        <label class="visually-hidden" for="orden-{{ $child->id }}">Orden de {{ $child->etiqueta }}</label><input id="orden-{{ $child->id }}" type="number" name="orden" min="0" max="10000" value="{{ $child->orden }}" required>
                                        <input type="hidden" name="habilitado" value="0">
                                        <label><input type="checkbox" name="habilitado" value="1" @checked($child->habilitado)> Habilitada</label>
                                        <button class="btn btn-outline-primary btn-sm" type="submit">Guardar</button>
                                    </form>
                                </td></tr>
                            @endforeach
                        @endforeach
                    </tbody></table></div>
                </section>
            @endforeach
        @endunless
    </section>
@endsection
