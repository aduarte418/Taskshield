<?php
    // En primer lugar, incluir el fichero conexion.php
    require_once __DIR__ . '/includes/conexion.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pantalla principal</title>
    <link rel="stylesheet" href="styles/styles.css">
</head>
<body>
    <!--Pantalla principal-->
    <h1>Taskshield</h1>
    <br>
    <h2>Registro de incidencias</h2>
    <br>
    <!--Mostrar la tabla incidencias, ofreciendo datos de la BD y los botones para editar -->
    
    <!--Botón para añadir incidencia-->
    <a href="nueva_incidencia.php"><button type="button">Añadir nueva incidencia</button></a>

    <!--Tabla-->
    <table id="tabla" style="width:100%">
        <!--Agrupar la fila con los encabezados de cada columna en el head de la tabla-->    
        <thead>
            <!--Fila con el título de cada columna -->
            <tr>
                <th>ID</th>
                <th>Título</th>
                <th>Descripción</th>
                <th>Prioridad</th>
                <th>Estado</th>
                <th>Email</th>
                <th>ID Técnico</th>
                <!--Columna para localizar los botones de editar y eliminar-->
                <th class="botones">Acciones</th>
                <th>Asignar incidencia</th>
            </tr>
        </thead>
        
        <!--Agrupar las filas con los datos en el cuerpo (body) de la tabla-->
        <tbody> 
        <!--Filas para cada incidencia (se generarán con los datos mediante PHP)-->
        <?php 
            // Try Catch para detectar y registrar errores durante el proceso
            try{
                // Primero preparar la sentencia
                $consultaIncidencias = $pdo->prepare('SELECT id, titulo, descripcion, prioridad, estado, email, id_tecnico FROM incidencia ORDER BY id ASC' /*WHERE estado = :estado*/);          

                // Ejecutar
                $consultaIncidencias->execute(/*':estado' => "en proceso"*/); // WHERE estado comentado por pruebas anteriores para comprobar el filtrado de datos

                // Recuperar los datos como array asociativo
                $incidencias = $consultaIncidencias->fetchAll(PDO::FETCH_ASSOC);

                // Recuperar los técnicos con sentencia preparada
                $consultaTecnicos = $pdo->prepare('SELECT id, nombre FROM tecnico ORDER BY id ASC');
                $consultaTecnicos->execute();
                $tecnicos = $consultaTecnicos->fetchAll(PDO::FETCH_ASSOC);
            }catch(PDOException $e){
                error_log('Error al recuperar datos: ' . $e->getMessage());
                echo htmlspecialchars("No se han podido recuperar los datos. Inténtalo de nuevo. ", ENT_QUOTES, 'UTF-8');
            }

            // Condición para que si el array no está vacío, pinte la tabla
            if (!empty($incidencias)){
                // Una vez recuperados, probar a pintarlos en la tabla
                foreach ($incidencias as $fila){
                    // Crear una fila e ir insertando datos en cada celda
                    ?> <tr>
                        <td><?= htmlspecialchars($fila["id"], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($fila["titulo"], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($fila["descripcion"], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($fila["prioridad"], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($fila["estado"], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($fila["email"], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($fila["id_tecnico"], ENT_QUOTES, 'UTF-8') ?></td>
                        <!--Celda adicional para almacenar los botones que permitirán añadir, editar y eliminar incidencia-->
                        <td>
                            <a href="editar_incidencia.php?id=<?= htmlspecialchars($fila['id'], ENT_QUOTES, 'UTF-8') ?>"><button type="button" class="editar">Editar</button></a>
                            <a href="eliminar_incidencia.php?id=<?= htmlspecialchars($fila['id'], ENT_QUOTES, 'UTF-8') ?>"><button type="button" class="eliminar">Eliminar</button></a>
                        </td>
                        <!--Celda para almacenar el desplegable y botón de asignación-->
                        <td>
                            <form action="asignar_incidencia.php" method="POST">
                                <!-- Enviamos el ID de la incidencia que queremos asignar (oculto) -->
                                <input type="hidden" name="id_incidencia" value="<?= htmlspecialchars($fila['id'], ENT_QUOTES, 'UTF-8') ?>">

                                <!-- Desplegable con los técnicos que ya consultamos arriba -->
                                <select name="id_tecnico" required>
                                    <option value="">Selecciona técnico...</option>
                                    <?php foreach ($tecnicos as $tec){ ?>
                                        <option value="<?= htmlspecialchars($tec['id'], ENT_QUOTES, 'UTF-8') ?>" 
                                            <?= ($fila['id_tecnico'] == $tec['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($tec['nombre'], ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                    <?php } ?>
                                </select>

                                <!-- Botón para asignar incidencia que envía el formulario hacia asignar_incidencia.php -->
                                <button type="submit">Asignar incidencia</button>
                            </form>
                        </td>
                    </tr>
                    <?php }
            // Si no se cumple, es decir, el array está vacío porque no hay incidencias en proceso, mostrar mensaje
            }
            else{
                // Mensaje si no se encuentran incidencias aplicables (si no hay o no coinciden con los criterios de búsqueda)
                ?> <tr>
                    <td colspan="10"><?= htmlspecialchars("No se encontraron incidencias en proceso", ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
            <?php
            }
        ?>
        </tbody>
    </table>

    

</body>
</html>