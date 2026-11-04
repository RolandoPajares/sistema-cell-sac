@php($pagina = (require resource_path('views/paneles/administrador/finanzas-facturacion/componentes/datos.php'))['cuentas'])
@php($pagina['clave'] = 'cuentas')
@include('paneles.administrador.finanzas-facturacion.componentes.pagina', ['pagina' => $pagina])
