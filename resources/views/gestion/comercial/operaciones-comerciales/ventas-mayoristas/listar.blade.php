@extends('layouts.panel.principal')

@section('title', 'Ventas mayoristas')
@section('body_class', 'modulo-operaciones-comerciales')
@section('content')
    @include('gestion.comercial.operaciones-comerciales.componentes.pagina-ventas', ['datos' => $datos])
@endsection
