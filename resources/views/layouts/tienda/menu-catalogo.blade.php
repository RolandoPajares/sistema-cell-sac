<div class="catalog-menu-backdrop" data-catalog-menu-close hidden></div>
<aside class="catalog-menu-drawer" id="catalog-menu-drawer" data-catalog-menu data-menu-url="{{ route('catalog.menu') }}" aria-hidden="true" hidden>
    <header class="catalog-menu-header">
        <div class="catalog-menu-user"><i class="bi bi-person-circle" aria-hidden="true"></i><span>{{ $headerAuthenticated ? 'Hola, '.$headerUser->nombre : 'Hola, visitante' }}</span></div>
        <button type="button" class="catalog-menu-close" data-catalog-menu-close aria-label="Cerrar menú"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
    </header>
    <div class="catalog-menu-toolbar"><button type="button" class="catalog-menu-back" data-catalog-menu-back hidden><i class="bi bi-arrow-left" aria-hidden="true"></i><span>Regresar</span></button><h2 data-catalog-menu-title>Explorar catálogo</h2></div>
    <p class="catalog-menu-status" data-catalog-menu-status role="status" hidden></p>
    <nav class="catalog-menu-options" data-catalog-menu-options aria-label="Opciones del catálogo"></nav>
</aside>
