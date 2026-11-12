@extends('layouts.panel.principal')

{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('title', 'Notificaciones')
{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('body_class', 'modulo-configuracion-sistema')
{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('content')
{{-- vista parcial reutilizada con los datos necesarios para este bloque. --}}
    @include('components.configuracion.pagina', ['tipo' => 'notificaciones'])
@endsection
