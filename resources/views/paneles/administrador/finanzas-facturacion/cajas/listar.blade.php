@php($pagina = (require resource_path('views/paneles/administrador/finanzas-facturacion/componentes/datos.php'))['cajas'])
@php($pagina['clave'] = 'cajas')
@include('paneles.administrador.finanzas-facturacion.componentes.pagina', ['pagina' => $pagina])
