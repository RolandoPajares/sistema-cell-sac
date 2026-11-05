@php($pagina = (require resource_path('views/paneles/administrador/finanzas-facturacion/componentes/datos.php'))['notas'])
@php($pagina['clave'] = 'notas')
@include('paneles.administrador.finanzas-facturacion.componentes.pagina', ['pagina' => $pagina])
