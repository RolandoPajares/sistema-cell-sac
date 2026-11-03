{{-- Menú específico del rol Cliente Minorista. La autorización sigue en PermisosRoles. --}}
{{-- Ahora es desplegable, igual que el del administrador --}}
@vite('resources/css/paneles/cliente-minorista/index.css')
@include('paneles.cliente-minorista.componentes.menu-lateral-desplegable', ['links' => $links ?? []])
