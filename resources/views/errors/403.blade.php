{{-- Muestra el mensaje cuando el usuario no tiene permisos para realizar una accion. --}}


<!doctype html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Acceso no permitido</title>
    </head>
    <body style="font-family:Arial,sans-serif;background:#eef6ff;color:#142b5f;margin:0;padding:3rem">
        <main style="max-width:34rem;margin:auto;background:#fff;border-radius:1rem;padding:2rem;box-shadow:0 1rem 3rem #142b5f1f;text-align:center">
            <p style="color:#0878e8;font-weight:700">CELL SAC</p>
            <h1>Acceso no permitido</h1>
            <p>No tienes permiso para realizar este cambio. Si necesitas acceso, solicita la autorización correspondiente.</p>
            <a href="{{ url()->previous() }}" style="display:inline-block;background:#0878e8;color:#fff;padding:.8rem 1.2rem;border-radius:.6rem;text-decoration:none">Volver</a>
        </main>
    </body>
</html>
