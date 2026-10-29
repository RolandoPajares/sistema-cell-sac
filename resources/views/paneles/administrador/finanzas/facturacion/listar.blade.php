@php($module = require resource_path('views/gestion/facturacion/datos.php'))

@include('paneles.administrador.componentes.finanzas-modulo', ['module' => $module])
