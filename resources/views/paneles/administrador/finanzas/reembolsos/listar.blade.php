@php($module = require resource_path('views/gestion/reembolsos/datos.php'))

@include('paneles.administrador.componentes.finanzas-modulo', ['module' => $module])
