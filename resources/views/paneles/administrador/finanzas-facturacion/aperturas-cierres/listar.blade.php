@php($pagina = (require resource_path('views/paneles/administrador/finanzas-facturacion/componentes/datos.php'))['aperturas'])
@php($pagina['clave'] = 'aperturas')
@include('paneles.administrador.finanzas-facturacion.componentes.pagina', ['pagina' => $pagina])
