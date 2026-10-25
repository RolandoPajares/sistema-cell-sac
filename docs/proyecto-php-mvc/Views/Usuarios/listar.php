<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Usuarios</title>
</head>

<body>
    <h1>
        Listado de Usuarios
    </h1>

    <?php if (!empty($_GET["e"])) : ?>
        <p><?= htmlspecialchars($_GET["e"], ENT_QUOTES, "UTF-8") ?></p>
    <?php endif; ?>

    <table border=1>
        <thead>
            <tr>
                <th>N°</th>
                <th>Usuario</th>
                <th>Correo</th>
                <th>Opciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data as $indice => $usu) { ?>
                <tr>
                    <td><?= $indice + 1; ?></td>
                    <td><?= $usu["usuario"] ?></td>
                    <td><?= $usu["email"] ?></td>
                    <td>
                        <a href="<?= BASE_URL . '/usuarios/ver/' . $usu["id"] ?> ">Ver</a>
                        <a href="<?= BASE_URL . '/usuarios/editar/' . $usu["id"] ?>">Editar</a>
                        <a href="<?= BASE_URL . '/usuarios/eliminar/' . $usu["id"] ?>">Eliminar</a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <a href="<?= BASE_URL ?>">Volver al inicio</a>
</body>

</html>
