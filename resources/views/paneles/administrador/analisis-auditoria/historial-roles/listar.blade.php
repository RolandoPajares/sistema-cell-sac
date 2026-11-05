@extends('layouts.administracion')
@section('title', 'Historial de roles y permisos')
@section('body_class', 'modulo-analisis-auditoria')
@section('content')
    @include('paneles.administrador.analisis-auditoria.componentes.pagina', ['tipo' => 'roles'])
@endsection
