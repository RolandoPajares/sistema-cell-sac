@extends('layouts.panel.principal')

@section('title', 'Ventas mayoristas')
@section('body_class', 'modulo-operaciones-comerciales')
@section('content')
    @include('gestion.comercial.ventas.componentes.pagina-ventas', ['datos' => $datos])
@endsection



