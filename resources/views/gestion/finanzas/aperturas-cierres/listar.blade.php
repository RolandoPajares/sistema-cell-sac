
{{-- logica auxiliar de la vista que prepara valores para la presentacion. --}}
@php
    $pagina = (require config_path('gestion/finanzas-facturacion.php'))['aperturas'];
@endphp
{{-- logica auxiliar de la vista que prepara valores para la presentacion. --}}
@php($pagina['clave'] = 'aperturas')
{{-- vista parcial reutilizada con los datos necesarios para este bloque. --}}
@include('gestion.finanzas.finanzas-facturacion.pagina', ['pagina' => $pagina])
