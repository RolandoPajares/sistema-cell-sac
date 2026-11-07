<section class="admin-page">
    <header class="admin-page-head">
        <div>
            <span class="eyebrow">{{ str_replace('_', ' ', $role) }}</span>
            <h1>Hola, {{ auth()->user()->nombre }}</h1>
            <p>Administra tu operación desde un solo lugar.</p>
        </div>
    </header>

    <div class="admin-kpi-grid">
        <article class="admin-kpi-card">
            <span class="admin-kpi-icon"><i class="bi bi-person-badge"></i></span>
            <div><small>Rol activo</small><strong>{{ str_replace('_', ' ', $role) }}</strong><span>Perfil autorizado</span></div>
        </article>
        <article class="admin-kpi-card admin-kpi-card--verde">
            <span class="admin-kpi-icon"><i class="bi bi-shield-check"></i></span>
            <div><small>Seguridad</small><strong>Protegida</strong><span>Sesión validada</span></div>
        </article>
        <article class="admin-kpi-card admin-kpi-card--violeta">
            <span class="admin-kpi-icon"><i class="bi bi-clock"></i></span>
            <div><small>Sesión</small><strong>Activa</strong><span>Acceso vigente</span></div>
        </article>
    </div>
</section>
