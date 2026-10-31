@extends('layouts.administracion')

@section('title', 'Envíos y entregas')
@section('body_class', 'modulo-envios-entregas')

@section('content')
@include('gestion.componentes.tablero-operacion', [
    'module' => require resource_path('views/gestion/envios-entregas/datos.php'),
])
@endsection

