<?php

use App\Mail\ConfirmacionMensajeContacto;

test('la confirmación de contacto define asunto y contenido renderizable', function (): void {
    $mail = new ConfirmacionMensajeContacto('Cliente Demo', 'Consulta de prueba');

    expect($mail->envelope()->subject)->toBe('Recibimos tu consulta - Technology Cell');

    expect($mail->content()->view)->toBe('emails.contacto.confirmacion');
});
