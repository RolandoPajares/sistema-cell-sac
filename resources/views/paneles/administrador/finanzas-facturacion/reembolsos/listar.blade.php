@php($pagina = (require resource_path('views/paneles/administrador/finanzas-facturacion/componentes/datos.php'))['reembolsos'])
@php($pagina['clave'] = 'reembolsos')
@include('paneles.administrador.finanzas-facturacion.componentes.pagina', ['pagina' => $pagina])
