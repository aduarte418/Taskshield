<?php 
// En primer lugar, incluir el fichero conexion.php
require_once __DIR__ . '/includes/conexion.php';

// Variables para almacenar mensajes de respuesta
$mensaje = "";
$error = "";

// Comprobar que los datos se enviaron por POST desde el formulario de index.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Recoger y validar los IDs enviados
    $id_incidencia = filter_input(INPUT_POST, 'id_incidencia', FILTER_VALIDATE_INT);
    $id_tecnico = filter_input(INPUT_POST, 'id_tecnico', FILTER_VALIDATE_INT);

    // Verificar que ambos datos existen y son válidos
    if (!$id_incidencia || !$id_tecnico) {
        $error = "Datos incompletos o inválidos para asignar la incidencia.";
    } else {
        try {
            // Iniciar la transacción dentro del try catch
            $pdo->beginTransaction();

            // Paso 1: actualizar estado a 'en proceso' y asignar id del técnico a la incidencia
            $sqlUpdateEstado = 'UPDATE incidencia SET estado = "en proceso", id_tecnico = :id_tecnico WHERE id = :id_incidencia';
            // Preparar la actualizacion de registro
            $stmtActualizarEstado = $pdo->prepare($sqlUpdateEstado);
            // Ejecutar
            $stmtActualizarEstado->execute([
                ':id_tecnico' => $id_tecnico,
                ':id_incidencia' => $id_incidencia
            ]);

            // Paso 2: Actualizar el número de tareas activas de ese técnico sumando 1
            $sqlUpdateTarea = 'UPDATE tecnico SET tareas_activas = tareas_activas + 1 WHERE id = :id';
            // Preparar la actualización de registro
            $stmtActualizarTarea = $pdo->prepare($sqlUpdateTarea);
            // Ejecutar
            $stmtActualizarTarea->execute([':id' => $id_tecnico]);

            // Recoger filas afectadas
            $filasAfectadas = $stmtActualizarTarea->rowCount();
            // Si no se actualizaron filas
            if ($filasAfectadas === 0) {
                throw new Exception("El técnico con ID $id_tecnico no existe en la base de datos.");
            }

            // Mostrar mensaje si se actualizaron filas
            echo "Se actualizaron: " . $filasAfectadas . " registro(s). ";

            // Confirmar que la operación se realizó correctamente e informar al usuario
            $pdo->commit();
            $mensaje = "Asignación de incidencia realizada correctamente con transacción.";

        } catch (Exception $e) {
            // Si alguno de los pasos anteriores falló, deshacer la transacción y no aplicar nada
            $pdo->rollBack();
            // Registrar el error internamente y mostrar al usuario mensaje genérico
            error_log('Error en la asignación: ' . $e->getMessage());
            $error = "Error en la asignación de incidencia. Cambios anulados: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asignar Incidencia</title>
    <link rel="stylesheet" href="styles/styles.css">
</head>
<body>

    <h1>Asignación de Incidencia</h1>

    <!-- Mostrar mensaje de éxito -->
    <?php if (!empty($mensaje)){ ?>
        <p><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></p>
    <?php } ?>

    <!-- Mostrar mensaje de error -->
    <?php if (!empty($error)){ ?>
        <p><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php } ?>

    <!-- Botón que permite regresar a index.php -->
    <a href="index.php"><button type="button">Volver al panel principal</button></a>

</body>
</html>