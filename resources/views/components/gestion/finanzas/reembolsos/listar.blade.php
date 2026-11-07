@php($module = require config_path('gestion/finanzas/reembolsos.php'))

@include('components.gestion.finanzas-modulo', ['module' => $module])
