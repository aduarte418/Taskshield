<!-- Fichero de conexión -->
<?php 
    // Implementar y requerir el archivo de configuración config.php
    require_once __DIR__ . '/../config/config.php';
    // try catch para comprobar que la conexión (con su configuración específica dentro) se establece correctamente
    try{
        // Definir el DSN (Data Souce Name)
        $dsn = 'mysql:host=localhost;dbname=taskshield;charset=utf8mb4';
        // Opciones de configuración del driver PDO
        $opciones = [
            PDO::ATTR_ERRMODE => PDO:: ERRMODE_EXCEPTION, // Opción para lanzar expeciones
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO:: FETCH_ASSOC, // Opción para el fetch por defecto
            PDO::ATTR_EMULATE_PREPARES => false, // Opción de prepare nativo
        ];
        // Instanciación de la conexión
        $pdo = new PDO ($dsn, 'root', '', $opciones);

    }catch(PDOException $e){
        // Si se detecta error, capturarlo y registrarlo en el log del servidor
        error_log('Error de conexión: ' . $e->getMessage());
        
        // Mostrar al usuario un mensaje genérico sobre que no se ha establecido la conexión
        die('No se ha podido establecer la conexión. Inténtalo más tarde.');
        // Después, se detiene completamente la ejecución del script
    }
?>
