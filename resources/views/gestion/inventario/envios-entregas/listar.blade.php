@extends('layouts.panel.principal')

@section('title', 'Envíos y entregas')
@section('body_class', 'modulo-envios-entregas')

@section('content')
@include('components.gestion.tablero-operacion', [
    'module' => require config_path('gestion/inventario/envios-entregas.php'),
])
@endsection

