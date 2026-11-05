@props(['suggestions' => null])

@php
    $suggestions ??= [
        ['icon' => 'bi-controller', 'label' => 'Gaming < S/1500', 'prompt' => 'Quiero un celular para gaming menor a S/1500'],
        ['icon' => 'bi-camera', 'label' => 'Buena cámara', 'prompt' => 'Recomiéndame un celular con buena cámara'],
        ['icon' => 'bi-cash-coin', 'label' => 'Económicos', 'prompt' => '¿Qué celulares económicos tienes?'],
        ['icon' => 'bi-mortarboard', 'label' => 'Para estudiar', 'prompt' => 'Necesito un celular para estudiar'],
    ];
@endphp

<div class="assistant-quick-actions" aria-label="Preguntas rápidas">
    @foreach($suggestions as $suggestion)
        <button type="button" data-assistant-prompt="{{ $suggestion['prompt'] }}"><i class="bi {{ $suggestion['icon'] }}"></i> {{ $suggestion['label'] }}</button>
    @endforeach
</div>
