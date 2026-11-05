@extends('layouts.administracion')

@section('title', 'Asignación de roles')
@section('content')
    <section class="admin-page">
        <header class="admin-page-head"><div><span class="eyebrow">Seguridad y delegación</span><h1>Asignaciones de roles</h1><p>Asigna acceso global o por sede sin sustituir las asignaciones existentes.</p></div></header>

        @unless ($ready)
            <div class="admin-alert admin-alert--error" role="status">Las tablas RBAC aún no existen en esta base. Aplica y revisa la migración aditiva antes de administrar asignaciones.</div>
        @else
            <div class="form-card">
                <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Usuario</th><th>Asignaciones actuales</th><th>Nueva asignación</th></tr></thead><tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td><strong>{{ $user->nombre }}</strong><br><small>{{ $user->correo }}</small></td>
                            <td>
                                @foreach ($user->roles as $role)
                                    <div class="role-assignment-row"><span>{{ $role->nombre }} · Empresa</span>@if ($role->pivot->activo)<form method="post" action="{{ route('management.user_assignments.revoke', [$user->id, $role->id]) }}" data-confirm="¿Revocar este rol global?">@csrf @method('DELETE')<button class="btn btn-outline-danger btn-sm" type="submit">Revocar</button></form>@else<span>Revocado</span>@endif</div>
                                @endforeach
                                @foreach ($user->rolesPorSede as $role)
                                    <div class="role-assignment-row"><span>{{ $role->nombre }} · Sede #{{ $role->pivot->sede_id }}</span>@if ($role->pivot->activo)<form method="post" action="{{ route('management.user_assignments.revoke_site', [$user->id, $role->id, $role->pivot->sede_id]) }}" data-confirm="¿Revocar este rol en la sede?">@csrf @method('DELETE')<button class="btn btn-outline-danger btn-sm" type="submit">Revocar</button></form>@else<span>Revocado</span>@endif</div>
                                @endforeach
                                @if ($user->roles->isEmpty() && $user->rolesPorSede->isEmpty())<span>Sin asignaciones RBAC</span>@endif
                            </td>
                            <td>
                                <form method="post" action="{{ route('management.user_assignments.store', $user) }}" class="assignment-form">
                                    @csrf
                                    <label>Rol<select name="rol_id" required><option value="">Selecciona</option>@foreach ($roles as $role)<option value="{{ $role->id }}">{{ $role->nombre }} · {{ $role->alcance_permitido }}</option>@endforeach</select></label>
                                    <label>Asignación<select name="alcance" required><option value="empresa">Cuenta / empresa</option><option value="sede">Sede</option></select></label>
                                    <label>Sede<select name="sede_id"><option value="">Selecciona si corresponde</option>@foreach ($sites as $site)<option value="{{ $site->id }}">{{ $site->nombre }}</option>@endforeach</select></label>
                                    <label>Vigente hasta<input type="date" name="vigente_hasta" min="{{ now()->addDay()->toDateString() }}"></label>
                                    <button class="btn btn-primary btn-sm" type="submit">Asignar rol</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3">No hay usuarios en el sistema.</td></tr>
                    @endforelse
                </tbody></table></div>
                {{ $users->links() }}
            </div>
        @endunless
    </section>
@endsection
