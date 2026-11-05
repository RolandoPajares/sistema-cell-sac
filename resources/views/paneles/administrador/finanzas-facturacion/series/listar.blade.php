@php($pagina = (require resource_path('views/paneles/administrador/finanzas-facturacion/componentes/datos.php'))['series'])
@php($pagina['clave'] = 'series')
@include('paneles.administrador.finanzas-facturacion.componentes.pagina', ['pagina' => $pagina])
