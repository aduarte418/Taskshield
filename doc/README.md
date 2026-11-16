# Taskshield - Intranet de Soporte Informático

## 1. Descripción del Proyecto
Taskshield es una aplicación web tipo intranet desarrollada para el departamento de soporte informático de una empresa. Permite registrar averías o tareas, asignarlas a técnicos especializados, actualizar sus estados y eliminarlas de forma controlada, garantizando la seguridad frente a inyecciones SQL y la integridad de los datos.

---

## 2. Arquitectura del Proyecto

El proyecto sigue una arquitectura con PHP estructurada en archivos para separar responsabilidades:

- **`config/`**: Contiene la configuración centralizada de credenciales (`config.php`).
- **`includes/`**: Contiene el archivo de conexión (`conexion.php`), el cual instancia PDO con:
  - Manejo de excepciones: `PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION`.
  - Desactivación de sentencias emuladas: `PDO::ATTR_EMULATE_PREPARES => false`.
  - Modo de obtención por defecto en array asociativo: `PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC`.
- **`index.php`**: Panel principal. Realiza consultas mediante sentencias preparadas y pinta en una tabla HTML todas las incidencias y técnicos mediante bucles `foreach`. Cada fila dispone de botones de acción y formulario de asignación individual.
- **`nueva_incidencia.php`**: Formulario para añadir nueva incidencia. Valida y sanitiza las entradas con `filter_input` (`FILTER_SANITIZE_SPECIAL_CHARS` y `FILTER_VALIDATE_EMAIL`), realiza la inserción con sentencias preparadas y redirige usando el ID obtenido con `$pdo->lastInsertId()`.
- **`editar_incidencia.php`**: Formulario para modificar el estado de una incidencia (`pendiente`, `en proceso`, `resuelta`). Valida la existencia del ID en la base de datos antes de permitir la edición y comprueba la actualización con `rowCount()`.
- **`eliminar_incidencia.php`**: Borrado controlado en dos fases: petición GET de confirmación visual y eliminación por POST con verificación mediante `rowCount()`.
- **`asignar_incidencia.php`**: Módulo de integridad crítica con Transacciones PDO (`beginTransaction`, `commit`, `rollBack`). Ejecuta dos pasos para realizar la operación:
  1. `UPDATE` sobre la incidencia (cambio a `'en proceso'` y asignación del `id_tecnico`).
  2. `UPDATE` sobre la tabla técnico incrementando en `+1` el contador `tareas_activas`. Si el técnico no existe, aborta con `rollBack()`.
- **`doc/`**: Documentación del proyecto, casos de prueba y script SQL (`schema.sql`).

---

## 3. Instrucciones de Instalación y Pruebas

1. **Base de Datos**:
   - Abrir phpMyAdmin o la consola de MySQL en XAMPP.
   - Importar o ejecutar el script ubicado en `doc/schema.sql`. Este creará la base de datos `taskshield`, las tablas `tecnico` e `incidencia`, e insertará datos iniciales de prueba.
2. **Configuración de Conexión**:
   - Asegurar que las credenciales en `config/config.php` coincidan con el entorno local (por defecto en XAMPP: usuario `root` y contraseña vacía).
3. **Ejecución**:
   - Ubicar la carpeta del proyecto en `C:/xampp/htdocs/Taskshield/` . También se puede clonar el repositorio a través de Git Bash en la ruta C:/xampp/htdocs/ con el comando "git clone https://github.com/aduarte418/Taskshield", habiéndose ubicado previamente por la terminal en dicha ruta mediante cd C:/xampp/htdocs.
   - Iniciar los servicios de Apache y MySQL en el panel de control de XAMPP.
   - Acceder desde el navegador a: `http://localhost/Taskshield/index.php`.

---

## 4. Tabla de Casos de Prueba Ejecutados

| ID | Módulo / Funcionalidad | Objetivo de la Prueba | Pasos a Ejecutar | Datos de Entrada | Resultado Esperado | Resultado Obtenido | Estado |
|---|---|---|---|---|---|---|---|
| **CP-01** | **Alta de Incidencia** (`nueva_incidencia.php`) |   Validar que el sistema rechaza email con formato inválido mediante `filter_input(..., FILTER_VALIDATE_EMAIL)`. | 1. Acceder a `nueva_incidencia.php`.<br>2. Rellenar título y descripción.<br>3. Introducir un correo inválido en el campo email.<br>4. Pulsar "Añadir incidencia". | **Título:** Fallo red<br>**Descripción:** Sin conexión<br>**Prioridad:** Alta<br>**Email:** `correo_invalido_sin_arroba` | El sistema no inserta el registro en la BD. Se detiene la inserción y muestra el mensaje: *"Datos inválidos o incompletos. Revisa el formulario (especialmente el correo)."* | Inserción bloqueada con éxito y mensaje de advertencia mostrado. No se crea ningún registro en la base de datos. | **APROBADO** |
| **CP-02** | **Edición de Incidencia** (`editar_incidencia.php`) | Comprobar que el sistema controla el error cuando se intenta editar un ID que no existe en la base de datos mediante la URL (`$_GET`). | 1. Introducir en el navegador una URL con un ID inexistente: `editar_incidencia.php?id=9999`.<br>2. Observar la respuesta de la aplicación. | Parámetro URL: `?id=9999` (ID inexistente en tabla `incidencia`) | La aplicación no muestra el formulario de edición. Muestra el mensaje en rojo: *"La incidencia con ID 9999 no existe en la base de datos."* y ofrece un botón para volver al panel. | Se bloquea el acceso al formulario y se informa con claridad del error. | **APROBADO** |
| **CP-03** | **Asignación Avanzada - Transacción** (`asignar_incidencia.php`) | Verificar la integridad transaccional: si falla la 2ª consulta (`UPDATE tecnico`), se debe ejecutar `rollBack()` y anular la 1ª consulta (`UPDATE incidencia`). | 1. Desde `index.php` inspeccionar el formulario de asignación o enviar por POST un `id_tecnico` que no existe (ej. 9999) con una incidencia pendiente (ej. ID 1).<br>2. El sistema intenta el Paso 1 (`UPDATE incidencia`) y luego el Paso 2 (`UPDATE tecnico`). | **ID Incidencia:** 1 (en estado "pendiente")<br>**ID Técnico:** 9999 (inexistente) | `UPDATE tecnico` devuelve `rowCount() = 0`. El sistema lanza una excepción, entra al `catch` y ejecuta `$pdo->rollBack()`. La incidencia 1 debe seguir en estado "pendiente" y sin técnico asignado. | Mensaje mostrado: *"Error en la asignación... Cambios anulados"*. Se comprueba en MySQL que la incidencia no cambió a "en proceso". Ningún dato huérfano guardado. | **APROBADO** |

