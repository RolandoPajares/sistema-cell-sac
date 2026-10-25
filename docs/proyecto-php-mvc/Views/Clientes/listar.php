<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=}, initial-scale=1.0">
    <title>Gestionar Clientes</title>
</head>

<body>
    <h1>
        Gestionar Clientes
    </h1>

    <?php if (!empty($_GET["e"])) : ?>
        <p><?= htmlspecialchars($_GET["e"], ENT_QUOTES, "UTF-8") ?></p>
    <?php endif; ?>

    <a href="<?= BASE_URL ?>/clientes/registrar">Nuevo Cliente</a>
    <table border="1">
        <thead>
            <tr>
                <th>Nombre Completo</th>
                <th>Correo</th>
                <th>Opciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data as $c) : ?>
                <tr>
                    <td><?= $c["nombres"] . " " . $c["apellidos"] ?></td>
                    <td><?= $c["email"] ?></td>
                    <td>
                        <a href="<?= BASE_URL . "/clientes/ver/" . $c["id"] ?>">Ver</a>
                        <a href="<?= BASE_URL . "/clientes/editar/" . $c["id"] ?>">Editar</a>
                        <a href="<?= BASE_URL . "/clientes/eliminar/" . $c["id"] ?>">Eliminar</a>
                    </td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>
</body>

</html>
