<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar cliente</title>
</head>

<body>
    <h1>Editar Cliente</h1>
    <form method="post" action="<?= BASE_URL ?>/clientes/guardar_edit" autocomplete="off">
        <input type="hidden" name="id_cliente" value="<?= $data["id"]  ?>">
        <input type="hidden" name="id_usuario" value="<?= $data["id_usuario"]  ?>">

        <label for="apellidos">Apellidos:</label>
        <input type="text" name="apellidos" value="<?= $data["apellidos"]; ?>" required>
        <br>

        <label for="nombres">Nombres:</label>
        <input type="text" name="nombres" value="<?= $data["nombres"]; ?>" required>
        <br>

        <label for="direccion">Direccion:</label>
        <input type="text" name="direccion" value="<?= $data["direccion"]; ?>">
        <br>

        <label for="telefono">Teléfono:</label>
        <input type="text" name="telefono" value="<?= $data["telefono"]; ?>">
        <br>

        <label for="dni">DNI:</label>
        <input type="text" name="dni" value="<?= $data["dni"]; ?>" required>
        <br>

        <label for="correo">Correo:</label>
        <input type="email" name="correo" value="<?= $data["email"]; ?>" required>
        <br>

        <label for="usuario">Usuario:</label>
        <input type="text" name="usuario" value="<?= $data["usuario"]; ?>" required>
        <br>

        <label for="clave">Contraseña:</label>
        <input type="password" name="clave">
        <br>

        <label for="clave_2">Confirmar contraseña:</label>
        <input type="password" name="clave_2">
        <br>

        <input type="reset" value="Restablecer">
        <input type="submit" value="Guardar">
    </form>

    <a href="<?= BASE_URL; ?>/clientes">Volver</a>
</body>

</html>