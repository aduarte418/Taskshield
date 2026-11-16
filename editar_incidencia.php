<!--Archivo para editar incidencia-->

<?php 
    // En primer lugar, incluir el fichero conexion.php
    require_once __DIR__ . '/includes/conexion.php';

    // Variable id que se recupera de la URL usando $_GET con el filter input
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    // Variables para mensajes y datos de la incidencia
    $mensaje = "";
    $error = "";
    //$incidencia = null;

    // Verificar si el ID es válido y si el registro existe en la BD
    if (!$id) {
        $error = "ID de incidencia no proporcionado o inválido.";
    } else {
        try {
            // Comprobar que el registro existe usando el parámetro id con sentencia preparada
            $consulta = $pdo->prepare('SELECT id, titulo, descripcion, estado FROM incidencia WHERE id = :id');
            $consulta->execute([':id' => $id]);
            $incidencia = $consulta->fetch(PDO::FETCH_ASSOC);

            // Si no se encuentra el registro en la base de datos
            if (!$incidencia) {
                $error = "La incidencia con ID $id no existe en la base de datos.";
            }
        } catch (PDOException $e) {
            error_log('Error al buscar incidencia: ' . $e->getMessage());
            $error = "Error al consultar la base de datos.";
        }
    }

    // Primero comprobar que los datos del formulario se enviaron correctamente por POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $incidencia) {

        // Filtrar y validar datos de estado con filter_input
        $estado = filter_input(INPUT_POST, 'estado', FILTER_SANITIZE_SPECIAL_CHARS); 

        // Comprobar que lo que ha escrito el usuario coincide con los valores permitidos de estado
        if ($estado != "pendiente" && $estado != "en proceso" && $estado != "resuelta") {
            $error = 'Datos incorrectos. El estado debe ser "pendiente", "en proceso" o "resuelta".';
        } else {
            try {
                // Preparar la consulta
                $sqlEditarIncidencia = "UPDATE incidencia SET estado = :estado WHERE id = :id";
                $stmtUpdateIncidencia = $pdo->prepare($sqlEditarIncidencia);
                $stmtUpdateIncidencia->execute([':estado' => $estado, ':id' => $id]);
                
                // Verificar filas afectadas
                $filasAfectadas = $stmtUpdateIncidencia->rowCount();
                $mensaje = "$filasAfectadas registro(s) actualizado(s) correctamente.";

                // Actualizar la variable local para que el select muestre el nuevo estado
                $incidencia['estado'] = $estado;

            } catch (PDOException $e) {
                error_log('Error al editar incidencia: ' . $e->getMessage());
                $error = "No se ha podido editar la incidencia. Inténtalo de nuevo.";
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Incidencia</title>
    <link rel="stylesheet" href="styles/styles.css">
</head>
<body>

    <h1>Editar Incidencia</h1>    

    <!-- Mostrar mensajes de éxito o de error si existen -->
    <?php if (!empty($mensaje)){ ?>
        <p><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></p>
    <?php } ?>

    <?php if (!empty($error)){ ?>
        <p><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php } ?>

    <!-- Solo mostrar el formulario si la incidencia existe -->
    <?php if ($incidencia){ ?>
        <p><strong>Incidencia:</strong> #<?= htmlspecialchars($incidencia['id'], ENT_QUOTES, 'UTF-8') ?> - <?= htmlspecialchars($incidencia['titulo'], ENT_QUOTES, 'UTF-8') ?></p>

        <!--Formulario para insertar datos para editar la incidencia-->
        <form action="" method="POST">
            <!--Seleccionar estado al que se cambia la incidencia-->
            <p>Selecciona estado:</p>
            <select id="estado" name="estado">
                <option value="pendiente" <?= ($incidencia['estado'] === 'pendiente') ? 'selected' : '' ?>>Pendiente</option>
                <option value="en proceso" <?= ($incidencia['estado'] === 'en proceso') ? 'selected' : '' ?>>En proceso</option>
                <option value="resuelta" <?= ($incidencia['estado'] === 'resuelta') ? 'selected' : '' ?>>Resuelta</option>
            </select>

            <br><br>
            <!--Botón que envía el formulario-->
            <button type="submit">Guardar datos modificados</button>
            <!--Botón que permite regresar a index.php-->
            <a href="index.php"><button type="button">Volver</button></a>
        </form>
    <?php }else{ ?>
        <!-- Botón para volver a la pantalla principal-->
        <a href="index.php"><button type="button">Volver a la pantalla principal</button></a>
    <?php } ?>

</body>
</html>