@extends('layouts.administracion')
@section('title', 'Reportes globales')
@section('body_class', 'modulo-analisis-auditoria')
@section('content')
    @include('paneles.administrador.analisis-auditoria.componentes.pagina', ['tipo' => 'globales'])
@endsection
