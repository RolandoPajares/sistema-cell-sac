@extends('layouts.panel.principal')
@section('title', 'Integraciones')
@section('body_class', 'modulo-configuracion-sistema')
@section('content')
    @include('components.configuracion.pagina', ['tipo' => 'integraciones'])
@endsection
