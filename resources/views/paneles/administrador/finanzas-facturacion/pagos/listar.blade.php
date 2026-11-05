@php($pagina = (require resource_path('views/paneles/administrador/finanzas-facturacion/componentes/datos.php'))['pagos'])
@php($pagina['clave'] = 'pagos')
@include('paneles.administrador.finanzas-facturacion.componentes.pagina', ['pagina' => $pagina])
