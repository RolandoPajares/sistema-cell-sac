@extends('layouts.administracion')

@section('title', 'Panel de cliente mayorista')
@section('body_class', 'modulo-cuenta rol-cliente-mayorista')

@section('content')
    {{-- ahora el panel usa su propia sección en vez del dashboard común --}}
    @include('paneles.cliente-mayorista.secciones.resumen-mayorista')
@endsection
