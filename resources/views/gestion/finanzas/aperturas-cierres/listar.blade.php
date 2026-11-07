@php($pagina = (require config_path('gestion/finanzas-facturacion.php'))['aperturas'])
@php($pagina['clave'] = 'aperturas')
@include('components.gestion.finanzas-facturacion.pagina', ['pagina' => $pagina])
