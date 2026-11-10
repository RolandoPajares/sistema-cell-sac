@php
    $pagina = $paginaReal ?? (require config_path('gestion/finanzas-facturacion.php'))['cuentas'];
@endphp
@php($pagina['clave'] = 'cuentas')
@include('gestion.finanzas.finanzas-facturacion.pagina', ['pagina' => $pagina])
