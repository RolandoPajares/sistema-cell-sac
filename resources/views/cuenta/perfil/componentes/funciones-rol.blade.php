<section class="tc-profile-card tc-profile-role-functions">
    <header class="tc-profile-card-heading"><i class="bi bi-diagram-3" aria-hidden="true"></i><div><h2>Funciones disponibles</h2><p>Acciones autorizadas por tus roles y permisos vigentes.</p></div></header>
    <div class="tc-profile-role-list">
        @forelse ($roles as $role)
            <span class="tc-profile-role-chip"><i class="bi bi-award" aria-hidden="true"></i>{{ $role->nombre }}</span>
        @empty
            <span class="tc-profile-muted">No hay roles vigentes registrados.</span>
        @endforelse
    </div>
    <div class="tc-profile-function-list">
        @forelse ($functions as $function)
            <span><i class="bi bi-check-circle" aria-hidden="true"></i>{{ $function->nombre }}</span>
        @empty
            <span class="tc-profile-muted">No hay funciones adicionales habilitadas.</span>
        @endforelse
    </div>
</section>
