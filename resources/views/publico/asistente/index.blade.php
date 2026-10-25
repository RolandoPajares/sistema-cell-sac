@extends('layouts.tienda')

@section('body_class', 'modulo-asistente')
@section('title', 'MD Assistant')

@section('content')
    <section class="smart-page">
        <div class="container narrow">
            <div class="smart-hero">
                <span class="eyebrow">Asistente virtual del catálogo</span>
                <h1><i class="bi bi-robot" aria-hidden="true"></i> MD Assistant</h1>
                <p>Conversa con el asistente de forma continua. Puedes preguntar por precios, presupuesto, gaming, cámara, batería, estudio, trabajo o pedir recomendaciones del catálogo.</p>
            </div>

            <x-inteligencia.asistente.chat />
        </div>
    </section>
@endsection
