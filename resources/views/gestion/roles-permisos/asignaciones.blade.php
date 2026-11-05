@extends('layouts.administracion')

@section('title', 'Asignación de roles')
@section('content')
    <section class="admin-page">
        <header class="admin-page-head"><div><span class="eyebrow">Seguridad y acceso</span><h1>Asignaciones de roles</h1><p>Asigna y revoca roles globales o para la sede interna autorizada.</p></div></header>

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
                                    <div class="role-assignment-row"><span>{{ $role->nombre }} · Sede interna</span>@if ($role->pivot->activo)<form method="post" action="{{ route('management.user_assignments.revoke_site', [$user->id, $role->id, $role->pivot->sede_id]) }}" data-confirm="¿Revocar este rol en la sede interna?">@csrf @method('DELETE')<button class="btn btn-outline-danger btn-sm" type="submit">Revocar</button></form>@else<span>Revocado</span>@endif</div>
                                @endforeach
                                @if ($user->roles->isEmpty() && $user->rolesPorSede->isEmpty())<span>Sin asignaciones RBAC</span>@endif
                            </td>
                            <td>
                                <form method="post" action="{{ route('management.user_assignments.store', $user) }}" class="assignment-form">
                                    @csrf
                                    <label>Rol<select name="rol_id" required><option value="">Selecciona</option>@foreach ($roles as $role)<option value="{{ $role->id }}">{{ $role->nombre }} · {{ $role->alcance_permitido }}</option>@endforeach</select></label>
                                    <label>Ámbito<select name="alcance" required><option value="empresa">Cuenta / empresa</option><option value="sede">Sede interna</option></select></label>
                                    <div class="form-text">Para roles con alcance de sede, se utiliza automáticamente la sede interna autorizada.</div>
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
