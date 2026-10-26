@extends('layouts.administracion')

@section('title', 'Panel de cliente minorista')
@section('body_class', 'modulo-cuenta rol-cliente-minorista')

@section('content')
    {{-- ahora el panel usa su propia sección en vez del dashboard común --}}
    @include('paneles.cliente-minorista.secciones.resumen-minorista')
@endsection
