<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar cliente</title>
</head>

<body>
    <h1>Registar Cliente</h1>
    <form method="post" action="<?= BASE_URL ?>/clientes/guardar" autocomplete="off">
        <label for="apellidos">Apellidos:</label>
        <input type="text" name="apellidos" required>
        <br>

        <label for="nombres">Nombres:</label>
        <input type="text" name="nombres" required>
        <br>

        <label for="direccion">Direccion:</label>
        <input type="text" name="direccion">
        <br>

        <label for="telefono">Teléfono:</label>
        <input type="text" name="telefono">
        <br>

        <label for="dni">DNI:</label>
        <input type="text" name="dni" required>
        <br>

        <label for="correo">Correo:</label>
        <input type="email" name="correo" required>
        <br>

        <label for="usuario">Usuario:</label>
        <input type="text" name="usuario" required>
        <br>

        <label for="clave">Contraseña:</label>
        <input type="password" name="clave" required>
        <br>

        <label for="clave_2">Confirmar contraseña:</label>
        <input type="password" name="clave_2" required>
        <br>

        <input type="submit" value="Guardar">
    </form>

    <a href="<?= BASE_URL; ?>/clientes">Volver</a>
</body>

</html>