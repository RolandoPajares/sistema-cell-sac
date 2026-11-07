@php($pagina = $paginaReal ?? (require config_path('gestion/finanzas-facturacion.php'))['cuentas'])
@php($pagina['clave'] = 'cuentas')
@include('components.gestion.finanzas-facturacion.pagina', ['pagina' => $pagina])
