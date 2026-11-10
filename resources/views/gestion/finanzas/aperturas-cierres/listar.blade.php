@php
    $pagina = (require config_path('gestion/finanzas-facturacion.php'))['aperturas'];
@endphp
@php($pagina['clave'] = 'aperturas')
@include('gestion.finanzas.finanzas-facturacion.pagina', ['pagina' => $pagina])
