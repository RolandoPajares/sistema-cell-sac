@extends('layouts.panel.principal')
@section('title', 'Reportes globales')
@section('body_class', 'modulo-analisis-auditoria')
@section('content')
    @include('gestion.analitica.analisis-auditoria.componentes.pagina', ['tipo' => 'globales'])
@endsection
