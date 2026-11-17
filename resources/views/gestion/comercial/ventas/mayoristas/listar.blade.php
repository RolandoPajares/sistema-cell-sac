@extends('layouts.panel.principal')


{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('title', 'Ventas mayoristas')
{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('body_class', 'modulo-operaciones-comerciales')
{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('content')
{{-- vista parcial reutilizada con los datos necesarios para este bloque. --}}
    @include('gestion.comercial.ventas.componentes.pagina-ventas', ['datos' => $datos])
@endsection



