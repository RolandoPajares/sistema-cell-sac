{{-- Menú específico del rol Cliente Mayorista. La autorización sigue en PermisosRoles. --}}
{{-- Ahora es desplegable, igual que el del administrador --}}
@vite('resources/css/paneles/cliente-mayorista/index.css')
@include('paneles.cliente-mayorista.componentes.menu-lateral-desplegable', ['links' => $links ?? []])
