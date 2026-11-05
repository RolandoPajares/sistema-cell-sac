@extends('layouts.administracion')
@section('title', 'Configuración de seguridad')
@section('body_class', 'modulo-configuracion-sistema')
@section('content')
    @include('paneles.administrador.configuracion-sistema.componentes.pagina', ['tipo' => 'seguridad'])
@endsection
