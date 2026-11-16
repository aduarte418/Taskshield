<?php
    // En primer lugar, incluir el fichero conexion.php
    require_once __DIR__ . '/includes/conexion.php';

    // Luego recoger el dato del ID de la incidencia que el usuario quiere eliminar
    // Comprobar si viene por POST (al confirmar), y si no, por GET (al entrar desde la tabla)
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    // Variables para mensajes y estado
    $mensaje = "";
    $error = "";
    $eliminado = false;
    $incidencia = null;

    // Verificar si se ha recibido un ID válido
    if (!$id) {
        $error = "ID de incidencia no proporcionado o inválido.";
    } else {
        // Primero comprobar que los datos se enviaron correctamente por POST (el usuario confirmó el borrado)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Ejecución de la eliminación en un try catch
            try {
                // Preparar sentencia
                $sqlEliminarIncidencia = "DELETE FROM incidencia WHERE id = :id";
                $stmtEliminarIncidencia = $pdo->prepare($sqlEliminarIncidencia);
                $stmtEliminarIncidencia->execute([':id' => $id]);

                // Verificar filas eliminadas con rowCount()
                if ($stmtEliminarIncidencia->rowCount() > 0) {
                    $mensaje = "Registro eliminado correctamente";
                    $eliminado = true;
                } else {
                    $error = "No se encontró el registro o ya fue eliminado";
                }
            } catch (PDOException $e) {
                error_log('Error al eliminar incidencia' . $e->getMessage());
                $error = "No se ha podido eliminar incidencia. Inténtalo de nuevo.";
            }
        } else {
            // Si la petición viene por GET (enlace desde index.php), consultar la incidencia para mostrar la confirmación
            try {
                $consulta = $pdo->prepare('SELECT id, titulo FROM incidencia WHERE id = :id');
                $consulta->execute([':id' => $id]);
                $incidencia = $consulta->fetch(PDO::FETCH_ASSOC);

                if (!$incidencia) {
                    $error = "La incidencia con ID $id no existe en la base de datos.";
                }
            } catch (PDOException $e) {
                error_log('Error al consultar incidencia para eliminar: ' . $e->getMessage());
                $error = "Error al consultar la base de datos.";
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eliminar Incidencia</title>
    <link rel="stylesheet" href="styles/styles.css">
</head>
<body>

    <h1>Eliminar Incidencia</h1>

    <!-- Mostrar mensajes de éxito o de error si existen -->
    <?php if (!empty($mensaje)){ ?>
        <p><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></p>
        <a href="index.php"><button type="button">Volver al panel principal</button></a>
    <?php } ?>

    <?php if (!empty($error)){ ?>
        <p><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
        <a href="index.php"><button type="button">Volver al panel principal</button></a>
    <?php } ?>

    <!-- Si la incidencia existe y todavía no ha sido eliminada, mostrar la confirmación -->
    <?php if (!$eliminado && $incidencia){ ?>
        <p>¿Estás seguro de que deseas eliminar la incidencia <strong>#<?= htmlspecialchars($incidencia['id'], ENT_QUOTES, 'UTF-8') ?> - <?= htmlspecialchars($incidencia['titulo'], ENT_QUOTES, 'UTF-8') ?></strong>?</p>
        
        <!-- Formulario que envía la orden de borrado mediante método POST -->
        <form action="" method="POST">
            <!-- Pasamos el ID de forma oculta para que llegue por POST -->
            <input type="hidden" name="id" value="<?= htmlspecialchars($incidencia['id'], ENT_QUOTES, 'UTF-8') ?>">
            
            <button type="submit">Sí, eliminar definitivamente</button>
            <a href="index.php"><button type="button">Cancelar y volver</button></a>
        </form>
    <?php } ?>

</body>
</html>