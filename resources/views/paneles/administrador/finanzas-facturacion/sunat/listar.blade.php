@php($pagina = (require resource_path('views/paneles/administrador/finanzas-facturacion/componentes/datos.php'))['sunat'])
@php($pagina['clave'] = 'sunat')
@include('paneles.administrador.finanzas-facturacion.componentes.pagina', ['pagina' => $pagina])
