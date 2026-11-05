@extends('layouts.administracion')

@section('title', 'Ventas minoristas')
@section('body_class', 'modulo-operaciones-comerciales')
@section('content')
    @include('paneles.administrador.operaciones-comerciales.componentes.pagina-ventas', ['datos' => $datos])
@endsection
