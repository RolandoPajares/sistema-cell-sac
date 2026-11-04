<dialog class="tc-sec-modal" data-sec-dialog aria-labelledby="tc-sec-modal-title">
    <div class="tc-sec-modal__card">
        <header class="tc-sec-modal__header">
            <div>
                <span class="tc-sec-modal__icon" data-sec-modal-icon><i class="bi bi-shield-lock" aria-hidden="true"></i></span>
                <span class="tc-sec-modal__heading">
                    <small data-sec-modal-kicker>Seguridad y Delegación</small>
                    <h2 id="tc-sec-modal-title" data-sec-modal-title>Formulario demostrativo</h2>
                    <p data-sec-modal-description>Los datos no se guardan.</p>
                </span>
            </div>
            <button class="tc-sec-modal__close" type="button" data-sec-modal-close aria-label="Cerrar formulario"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
        </header>
        <form class="tc-sec-modal__form" data-sec-demo-form novalidate>
            <div class="tc-sec-modal__steps" data-sec-modal-steps hidden>
                <span data-sec-step-indicator="0"><b>1</b> Archivo</span><i></i>
                <span data-sec-step-indicator="1"><b>2</b> Vista previa</span><i></i>
                <span data-sec-step-indicator="2"><b>3</b> Confirmación</span>
            </div>
            <div class="tc-sec-modal__demo-note"><i class="bi bi-info-circle" aria-hidden="true"></i><span>Modo demostrativo: no se enviarán datos ni se aplicarán cambios.</span></div>
            <div class="tc-sec-modal__warning" data-sec-modal-warning role="alert" hidden></div>
            <div class="tc-sec-modal__fields" data-sec-modal-fields></div>
            <div class="tc-sec-modal__preview" data-sec-modal-preview hidden></div>
            <div class="tc-sec-modal__errors" data-sec-modal-errors role="alert" hidden></div>
            <footer class="tc-sec-modal__footer">
                <button class="tc-sec__button" type="button" data-sec-modal-close>Cancelar</button>
                <span class="tc-sec-modal__footer-spacer"></span>
                <button class="tc-sec__button" type="button" data-sec-modal-previous hidden>Anterior</button>
                <button class="tc-sec__button tc-sec__button--primary" type="button" data-sec-modal-next hidden>Siguiente</button>
                <button class="tc-sec__button tc-sec__button--primary" type="button" data-sec-modal-submit>Guardar (demostración)</button>
            </footer>
        </form>
    </div>
</dialog>
