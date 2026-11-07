@php($pagina = (require config_path('gestion/finanzas-facturacion.php'))['reembolsos'])
@php($pagina['clave'] = 'reembolsos')
@include('components.gestion.finanzas-facturacion.pagina', ['pagina' => $pagina])
