
{{-- logica auxiliar de la vista que prepara valores para la presentacion. --}}
@php
    $pagina = $paginaReal ?? (require config_path('gestion/finanzas-facturacion.php'))['cuentas'];
@endphp
{{-- logica auxiliar de la vista que prepara valores para la presentacion. --}}
@php($pagina['clave'] = 'cuentas')
{{-- vista parcial reutilizada con los datos necesarios para este bloque. --}}
@include('gestion.finanzas.finanzas-facturacion.pagina', ['pagina' => $pagina])
