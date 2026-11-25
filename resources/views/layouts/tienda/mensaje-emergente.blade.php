<div class="mensaje-emergente" data-popup hidden>
    <div class="mensaje-emergente__backdrop" data-popup-close></div>
    <section class="mensaje-emergente__dialog" role="dialog" aria-modal="true" aria-labelledby="popup-title" aria-describedby="popup-message">
        <button class="mensaje-emergente__close" type="button" data-popup-close aria-label="Cerrar mensaje">
            <i class="bi bi-x-lg" aria-hidden="true"></i>
        </button>
        <span class="mensaje-emergente__icon" data-popup-icon aria-hidden="true"><i class="bi bi-info-circle"></i></span>
        <h2 id="popup-title" data-popup-title>Mensaje</h2>
        <p id="popup-message" data-popup-message></p>
        <div class="mensaje-emergente__actions" data-popup-actions></div>
    </section>
</div>

@if (session('status'))
    <div data-popup-flash data-popup-type="success" data-popup-message="{{ session('status') }}"></div>
@elseif ($errors->any())
    <div data-popup-flash data-popup-type="error" data-popup-message="{{ $errors->first() }}"></div>
@elseif (session('error'))
    <div data-popup-flash data-popup-type="error" data-popup-message="{{ session('error') }}"></div>
@endif
