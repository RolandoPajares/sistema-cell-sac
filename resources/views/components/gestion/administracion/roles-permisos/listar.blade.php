@php($module = require config_path('gestion/administracion/roles-permisos.php'))
@include('components.gestion.administracion-modulo', ['module' => $module])
