@php($module = require config_path('gestion/finanzas/pagos.php'))

@include('components.gestion.finanzas-modulo', ['module' => $module])
