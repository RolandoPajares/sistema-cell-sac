@php($pagina = (require config_path('gestion/finanzas-facturacion.php'))['cajas'])
@php($pagina['clave'] = 'cajas')
@include('components.gestion.finanzas-facturacion.pagina', ['pagina' => $pagina])
