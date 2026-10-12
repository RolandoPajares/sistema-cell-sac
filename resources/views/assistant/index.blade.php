@extends('layouts.store')

@section('body_class', 'modulo-asistente')
@section('title', 'MD Assistant')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/cell-legacy/css/modulos/comercio-inteligente/base.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/cell-legacy/css/modulos/comercio-inteligente/asistente-ia.css') }}">
@endpush

@section('content')
    <section class="smart-page">
        <div class="container narrow">
            <div class="smart-hero">
                <span class="eyebrow">Asistente virtual del catálogo</span>
                <h1><i class="bi bi-robot" aria-hidden="true"></i> MD Assistant</h1>
                <p>Conversa con el asistente de forma continua. Puedes preguntar por precios, presupuesto, gaming, cámara, batería, estudio, trabajo o pedir recomendaciones del catálogo.</p>
            </div>

            <section class="assistant-chat panel" id="assistantChat" data-endpoint="{{ route('assistant.ask') }}">
                <div class="assistant-chat-head">
                    <div class="assistant-avatar">MD</div>
                    <div>
                        <strong>MD Assistant</strong>
                        <small><span class="assistant-online-dot"></span> Asistente del catálogo disponible</small>
                    </div>
                    <button type="button" class="btn btn-ghost assistant-clear" id="assistantClear">Nueva conversación</button>
                </div>

                <div class="assistant-messages" id="assistantMessages" aria-live="polite">
                    <article class="assistant-message assistant-message-bot">
                        <div class="assistant-message-avatar">MD</div>
                        <div class="assistant-bubble">
                            <b>MD Assistant</b>
                            <p>¡Hola! Puedo ayudarte a elegir un celular, buscar opciones por presupuesto, comparar necesidades o revisar productos disponibles. Escríbeme, por ejemplo: <em>“quiero un celular para gaming menor a S/1500”</em>.</p>
                        </div>
                    </article>
                </div>

                <div class="assistant-quick-actions" aria-label="Preguntas rápidas">
                    <button type="button" data-assistant-prompt="Quiero un celular para gaming menor a S/1500"><i class="bi bi-controller"></i> Gaming &lt; S/1500</button>
                    <button type="button" data-assistant-prompt="Recomiéndame un celular con buena cámara"><i class="bi bi-camera"></i> Buena cámara</button>
                    <button type="button" data-assistant-prompt="¿Qué celulares económicos tienes?"><i class="bi bi-cash-coin"></i> Económicos</button>
                    <button type="button" data-assistant-prompt="Necesito un celular para estudiar"><i class="bi bi-mortarboard"></i> Para estudiar</button>
                </div>

                <form class="assistant-composer" id="assistantForm">
                    @csrf
                    <input class="input" id="assistantInput" name="message" autocomplete="off" maxlength="300" placeholder="Escribe otro mensaje..." required>
                    <button class="btn btn-primary" id="assistantSend" type="submit"><i class="bi bi-send-fill"></i> Enviar</button>
                </form>
                <small class="assistant-disclaimer">Las recomendaciones se generan con los productos y datos disponibles en el catálogo.</small>
            </section>
        </div>
    </section>
@endsection
