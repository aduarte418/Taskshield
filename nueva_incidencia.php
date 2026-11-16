<?php
// En primer lugar, incluir el fichero conexion.php
require_once __DIR__ . '/includes/conexion.php';

// Variable para almacenar error si falla la validación
$error = "";

// Comprobar que los datos se enviaron correctamente mediante POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Validar titulo, descripcion y prioridad con Sanitize Special Chars
    $titulo = filter_input(INPUT_POST, 'titulo', FILTER_SANITIZE_SPECIAL_CHARS);
    $descripcion = filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_SPECIAL_CHARS);
    $prioridad = filter_input(INPUT_POST, 'prioridad', FILTER_SANITIZE_SPECIAL_CHARS);
    // Validar email con Validate Email
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

    // Si no existe algún campo, mostrar mensaje de error
    if (!$titulo || !$descripcion || !$prioridad || !$email) {
        $error = "Datos inválidos o incompletos. Revisa el formulario (especialmente el correo).";
    } else {
        // Después, insertar nueva incidencia
        try {
            // Insertar valores
            $sqlInsertarIncidencia = 'INSERT INTO incidencia (titulo, descripcion, prioridad, email) VALUES
            (:titulo, :descripcion, :prioridad, :email)';

            // Preparar sentencia
            $stmtNuevaIncidencia = $pdo->prepare($sqlInsertarIncidencia);

            // Ejecutar
            $stmtNuevaIncidencia->execute([
                ':titulo' => $titulo,
                ':descripcion' => $descripcion,
                ':prioridad' => $prioridad,
                ':email' => $email,
            ]);

            // Recuperar el ID generado automáticamente (con AUTO_INCREMENT)
            $nuevoId = $pdo->lastInsertId();

            // Redirigir hacia index.php pasando el ID de la nueva incidencia añadida
            header('Location: index.php?creada=' . $nuevoId);
            exit;

        } catch (PDOException $e) {
            error_log('Error al añadir incidencia: ' . $e->getMessage());
            $error = "No se ha podido añadir la incidencia. Inténtalo de nuevo.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Incidencia</title>
    <link rel="stylesheet" href="styles/styles.css">
</head>
<body>
    <h1>Nueva Incidencia</h1>

    <!-- Si se ha detectado error de validación, mostrarlo al usuario -->
    <?php if (!empty($error)){ ?>
        <p><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php } ?>
        
    <!--Formulario para insertar datos para añadir nueva incidencia-->
    <form action="" method="POST">
        <!--Campos pedidos-->
        <p>
            <label for="titulo">Título:</label><br>
            <input type="text" id="titulo" name="titulo" placeholder="Escribe la nueva incidencia" required>
        </p>

        <p>
            <label for="descripcion">Descripción:</label><br>
            <textarea id="descripcion" name="descripcion" placeholder="Descripción de la incidencia" required></textarea>
        </p>
        
        <!--Desplegable para opciones de prioridad (baja, media, alta según el ENUM de la Base de Datos)-->
        <p>
            <label for="prioridad">Prioridad:</label><br>
            <select id="prioridad" name="prioridad" required>
                <option value="baja">Baja</option>
                <option value="media" selected>Media</option>
                <option value="alta">Alta</option>
            </select>
        </p>

        <p>
            <label for="email">Email:</label><br>
            <input type="email" id="email" name="email" placeholder="Escribe tu email" required>
        </p>

        <!--Botón que envía el formulario y añade incidencia-->
        <button type="submit">Añadir incidencia</button>
        <!--Botón que permite volver a index.php-->
        <a href="index.php"><button type="button">Volver</button></a>
    </form>
</body>
</html>
