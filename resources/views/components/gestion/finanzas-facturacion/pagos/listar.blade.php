@php($pagina = (require config_path('gestion/finanzas-facturacion.php'))['pagos'])
@php($pagina['clave'] = 'pagos')
@include('components.gestion.finanzas-facturacion.pagina', ['pagina' => $pagina])
