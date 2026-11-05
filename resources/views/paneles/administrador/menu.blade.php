{{-- Menú exclusivo del Administrador. Los enlaces habilitados conservan la navegación autorizada existente. --}}
@vite('resources/css/paneles/administrador/index.css')
@include('paneles.administrador.componentes.menu-lateral-desplegable', ['links' => $links ?? []])
