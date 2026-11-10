@php
    $pagina = (require config_path('gestion/finanzas-facturacion.php'))['cajas'];
@endphp
@php($pagina['clave'] = 'cajas')
@include('gestion.finanzas.finanzas-facturacion.pagina', ['pagina' => $pagina])
