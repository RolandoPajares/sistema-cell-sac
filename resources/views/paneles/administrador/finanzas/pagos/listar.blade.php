@php($module = require resource_path('views/gestion/pagos/datos.php'))

@include('paneles.administrador.componentes.finanzas-modulo', ['module' => $module])
