-- Antes de nada eliminar la base de datos si existe para no duplicarla a crearla
DROP DATABASE IF EXISTS taskshield; -- Práctica que hacíamos en 1º
-- Crear la base de datos si no existe
CREATE DATABASE IF NOT EXISTS taskshield; -- La BD se llamará "taskshield" como el proyecto

-- Usar esta base de datos
USE taskshield;

-- **Tablas**
-- Tabla técnicos
CREATE TABLE tecnico(
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR (64) NOT NULL,
    tareas_activas INT
);

-- Tabla incidencias
CREATE TABLE incidencia(
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    titulo VARCHAR(256) NOT NULL,
    descripcion VARCHAR(512) NOT NULL,
    prioridad ENUM('baja', 'media', 'alta') NOT NULL DEFAULT "media", -- Como prueba, por defecto la gravedad será media
    estado ENUM('pendiente', 'en proceso', 'resuelta') NOT NULL DEFAULT "pendiente", -- Por defecto, la incidencia se marcará en pendiente
    email VARCHAR(256) NOT NULL,
    -- Foreign key para probar nombre del técnico a quien se le asignará la incidencia
    -- nombre INT,
    -- FOREIGN KEY (nombre) REFERENCES nombre(id),
    
    -- Foreign key para el id del técnico a quien se le asignará la incidencia
    id_tecnico INT,
    FOREIGN KEY (id_tecnico) REFERENCES tecnico(id)
);

-- **INSERTs de datos** (a modo de prueba)

-- Insertar 2 técnicos
INSERT INTO tecnico (nombre, tareas_activas) VALUES
("Hugo Sánchez", 2),
("Diana Gala", 2);

-- Insertar 4 incidencias por tener una en cada estado + 2 en proceso además de prioridad
INSERT INTO incidencia (titulo, descripcion, prioridad, estado, email, id_tecnico) VALUES
("Ordenador lento", "El equipo tarda demasiado en iniciar y ejecutar aplicaciones", "baja", "pendiente", "antondm11@gmail.com", 1),
("Solicitud de restablecimiento de contraseña", "Contraseña olvidada", "media", "en proceso", "carlos.fernandez@empresa.com", 1),
("Caída de la conexión a internet", "Acceso perdido a servicios online", "alta", "en proceso", "laura.martinez@empresa.com", 2),
("Pantalla azul en Windows", "El equipo muestra errores de pantalla azul", "alta", "resuelta", "marta.lopez@empresa.com", 2);

-- SELECTs de comprobación
SELECT * FROM tecnico;
SELECT * FROM incidencia;
