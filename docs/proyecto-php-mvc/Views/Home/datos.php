<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Datos en PHP</title>
</head>
<body>
    <h1><?php echo $data["titulo"] ?></h1>
    <h2><?= $data["subtitulo"] ?></h2>
    <p>Parámetros:</p>
    <pre>
        <?php
            var_dump($data["params"]);
        ?>
    </pre>
</body>
</html>