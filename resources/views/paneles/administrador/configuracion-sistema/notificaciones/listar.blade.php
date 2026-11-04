@extends('layouts.administracion')
@section('title', 'Notificaciones · Vista de diseño')
@section('body_class', 'modulo-configuracion-sistema')
@section('content')
    @include('paneles.administrador.configuracion-sistema.componentes.pagina', ['tipo' => 'notificaciones'])
@endsection
