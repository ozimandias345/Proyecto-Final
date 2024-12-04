-- Active: 1724871262035@@127.0.0.1@3306@prueba2

CREATE DATABASE prueba2;

use prueba2;
-- Crear la tabla de usuarios
CREATE TABLE usuario (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(20) NOT NULL UNIQUE,
    password VARCHAR(30) NOT NULL,
    userType ENUM('administrador', 'empleado', 'representante', 'conductor', 'coordinador') NOT NULL
);

SELECT * from usuario

-- Crear la tabla de empresas
CREATE TABLE empresa (
    numEmpresa INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100),
    direccion VARCHAR(200),
    telefono VARCHAR(20),
    totalSolicitudes INT DEFAULT 0,
    totalReportes INT DEFAULT 0
);

-- Crear la tabla de administradores
CREATE TABLE administrador (
    numAdministrador INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(50),
    primerApellido VARCHAR(50),
    segundoApellido VARCHAR(50),
    id_usuario INT UNIQUE,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id) ON DELETE CASCADE
);

-- Crear la tabla de coordinadores
CREATE TABLE coordinador (
    numCoordinador INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(50),
    primerApellido VARCHAR(50),
    segundoApellido VARCHAR(50),
    id_usuario INT UNIQUE,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id) ON DELETE CASCADE
);

-- Crear la tabla de empleados
CREATE TABLE empleado (
    numEmpleado INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(50),
    primerApellido VARCHAR(50),
    segundoApellido VARCHAR(50),
    empresa INT,
    id_usuario INT UNIQUE,
    FOREIGN KEY (empresa) REFERENCES empresa(numEmpresa),
    FOREIGN KEY (id_usuario) REFERENCES usuario(id) ON DELETE CASCADE
);

-- Crear la tabla de representantes
CREATE TABLE representante (
    numRepresentante INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(50),
    primerApellido VARCHAR(50),
    segundoApellido VARCHAR(50),
    empresa INT,
    id_usuario INT UNIQUE,
    FOREIGN KEY (empresa) REFERENCES empresa(numEmpresa),
    FOREIGN KEY (id_usuario) REFERENCES usuario(id) ON DELETE CASCADE
);

-- Crear la tabla de contratos
CREATE TABLE contrato (
    numContrato INT PRIMARY KEY AUTO_INCREMENT,
    fechaInicio DATE,
    fechaFin DATE,
    cantEmpleados INT DEFAULT 0,
    estado ENUM('activo', 'finalizado', 'cancelado') DEFAULT 'activo',
    administrador INT,
    empresa INT,
    FOREIGN KEY (administrador) REFERENCES administrador(numAdministrador),
    FOREIGN KEY (empresa) REFERENCES empresa(numEmpresa)
);

ALTER TABLE contrato
MODIFY COLUMN estado ENUM('activo', 'finalizado', 'cancelado', 'inactivo') DEFAULT 'activo';


-- Crear la tabla de servicios
CREATE TABLE servicio (
    clave INT PRIMARY KEY AUTO_INCREMENT,
    nombreServicio VARCHAR(50),
    costoIndividual DECIMAL(10, 2),
    numTransportes INT,
    descripcion TEXT,
    contrato INT,
    coordinador INT,
    FOREIGN KEY (contrato) REFERENCES contrato(numContrato),
    FOREIGN KEY (coordinador) REFERENCES coordinador(numCoordinador)
);

-- Crear la tabla de pagos
CREATE TABLE pagos (
    numPago INT PRIMARY KEY AUTO_INCREMENT,
    monto DECIMAL(10, 2),
    fechaPago DATE,
    contrato INT,
    FOREIGN KEY (contrato) REFERENCES contrato(numContrato)
);

-- Crear la tabla de solicitudes
CREATE TABLE solicitud (
    numSolicitud INT PRIMARY KEY AUTO_INCREMENT,
    descripcion TEXT,
    cantEmpleados INT,
    administrador INT,
    FOREIGN KEY (administrador) REFERENCES administrador(numAdministrador)
);

-- Crear la tabla de marcas
CREATE TABLE marca (
    codigo INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(50)
);

-- Crear la tabla de modelos
CREATE TABLE modelo (
    codigo INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(50),
    anio INT,
    marca INT,
    FOREIGN KEY (marca) REFERENCES marca(codigo)
);

-- Crear la tabla de conductores
CREATE TABLE conductor (
    numConductor INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(50),
    primerApellido VARCHAR(50),
    segundoApellido VARCHAR(50),
    id_usuario INT UNIQUE,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id) ON DELETE CASCADE
);
ALTER TABLE servicio
ADD COLUMN transporte_id INT,
ADD CONSTRAINT fk_transporte_servicio FOREIGN KEY (transporte_id) REFERENCES transporte(numTransporte);
ALTER TABLE usuario MODIFY username VARCHAR(255);


-- Crear la tabla de transportes
CREATE TABLE transporte (
    numTransporte INT PRIMARY KEY AUTO_INCREMENT,
    matricula VARCHAR(10),
    capacidad INT,
    disponibilidad ENUM('disponible', 'en_servicio', 'mantenimiento', 'fuera_servicio') NOT NULL DEFAULT 'disponible',
    marca INT,
    modelo INT,
    conductor INT,
    FOREIGN KEY (marca) REFERENCES marca(codigo),
    FOREIGN KEY (modelo) REFERENCES modelo(codigo),
    FOREIGN KEY (conductor) REFERENCES conductor(numConductor)
);

-- Crear la tabla de rutas
CREATE TABLE rutas (
    numRuta INT PRIMARY KEY AUTO_INCREMENT,
    nombreRuta VARCHAR(50),
    distanciaKm DECIMAL(10, 2),
    costoKm DECIMAL(10, 2),
    totalPasajeros INT DEFAULT 0,
    conductor INT,
    FOREIGN KEY (conductor) REFERENCES conductor(numConductor)
);

-- Crear la tabla de paradas
CREATE TABLE parada (
    numParada INT PRIMARY KEY AUTO_INCREMENT,
    nombreParada VARCHAR(50)
);

-- Crear la tabla de paradas_rutas (relación muchos a muchos)
CREATE TABLE paradas_rutas (
    numRuta INT,
    numParada INT,
    horaInicio TIME,
    horaFinal TIME,
    fechaInicio DATE,
    fechaFinal DATE,
    PRIMARY KEY (numRuta, numParada),
    FOREIGN KEY (numRuta) REFERENCES rutas(numRuta),
    FOREIGN KEY (numParada) REFERENCES parada(numParada)
);

-- Crear la tabla de empleado_transporte (relación muchos a muchos)
CREATE TABLE empleado_transporte (
    empleado INT,
    transporte INT,
    fechaInicio DATE,
    fechaFinal DATE,
    PRIMARY KEY (empleado, transporte),
    FOREIGN KEY (empleado) REFERENCES empleado(numEmpleado),
    FOREIGN KEY (transporte) REFERENCES transporte(numTransporte)
);
-- Crear la tabla de reportes
CREATE TABLE reportes (
    numReporte INT PRIMARY KEY AUTO_INCREMENT,
    descripcion TEXT,
    fechaReporte DATE,
    numCoordinador INT,
    FOREIGN KEY (numCoordinador) REFERENCES coordinador(numCoordinador)
);


CREATE TABLE reporte_conductor (
    numreporte INT NOT NULL AUTO_INCREMENT primary key,
    descripcion VARCHAR(200) NOT NULL,
    fecha DATE,
    numConductor INT,
    FOREIGN KEY (numConductor) REFERENCES conductor(numConductor)
);



/*      D A T O S   P A R A   P R U E B A S     */
-- Insertar datos de prueba en la tabla reporte conductor
INSERT INTO reporte_conductor (descripcion, fecha, numconductor)
VALUES 
('Reporte de conducción imprudente', '2024-11-10', 1),
('Reporte de mantenimiento del vehículo atrasado', '2024-11-15', 2);


-- Insertar datos de prueba en la tabla usuario
INSERT INTO usuario (username, password, userType) VALUES
('admin1', 'password123', 'administrador'),
('emp1', 'password123', 'empleado'),
('rep1', 'password123', 'representante'),
('cond1', 'password123', 'conductor'),
('coord1', 'password123', 'coordinador');

-- Insertar datos de prueba en la tabla empresa
INSERT INTO empresa (nombre, direccion, telefono, totalSolicitudes, totalReportes) VALUES
('Empresa A', 'Av. Principal 123, Ciudad A', '555-1234', 2, 1),
('Empresa B', 'Calle Secundaria 45, Ciudad B', '555-5678', 1, 1);

-- Insertar datos de prueba en la tabla administrador
INSERT INTO administrador (nombre, primerApellido, segundoApellido, id_usuario) VALUES
('Juan', 'Pérez', 'García', 1);

-- Insertar datos de prueba en la tabla coordinador
INSERT INTO coordinador (nombre, primerApellido, segundoApellido, id_usuario) VALUES
('Dana', 'Gonzalez', 'Rodriguez', 5);

-- Insertar datos de prueba en la tabla empleado
INSERT INTO empleado (nombre, primerApellido, segundoApellido, empresa, id_usuario) VALUES
('Luis', 'Martínez', 'López', 1, 2);

-- Insertar datos de prueba en la tabla representante
INSERT INTO representante (nombre, primerApellido, segundoApellido, empresa, id_usuario) VALUES
('Ana', 'Hernández', 'Ruiz', 2, 3);

-- Insertar datos de prueba en la tabla contrato
INSERT INTO contrato (fechaInicio, fechaFin, administrador, empresa, cantEmpleados) VALUES
('2024-01-01', '2024-12-31', 1, 1, 35),
('2024-02-01', '2024-12-31', 1, 2, 30);

-- Insertar datos de prueba en la tabla servicio
INSERT INTO servicio (nombreServicio, costoIndividual, numTransportes, descripcion, contrato) VALUES
('Transporte Ejecutivo', 500.00, 5, 'Servicio de transporte para ejecutivos.', 1),
('Transporte de Personal', 300.00, 10, 'Transporte para empleados.', 2);

-- Insertar datos de prueba en la tabla pagos
INSERT INTO pagos (monto, fechaPago, contrato) VALUES
(10000.00, '2024-01-15', 1),
(8000.00, '2024-02-15', 2);

-- Insertar datos de prueba en la tabla marca
INSERT INTO marca (nombre) VALUES
('Toyota'),
('Ford'),
('Chevrolet');

-- Insertar datos de prueba en la tabla modelo
INSERT INTO modelo (nombre, anio, marca) VALUES
('Corolla', 2020, 1),
('Fiesta', 2019, 2),
('Spark', 2021, 3);

-- Insertar datos de prueba en la tabla conductor
INSERT INTO conductor (nombre, primerApellido, segundoApellido, id_usuario) VALUES
('Carlos', 'Gómez', 'Sánchez', 4);

-- Insertar datos de prueba en la tabla transporte
INSERT INTO transporte (matricula, capacidad, disponibilidad, marca, modelo, conductor) VALUES
('ABC123', 45, 'disponible', 1, 1, 1),
('XYZ789', 35, 'disponible', 2, 2, 1);

-- Insertar datos de prueba en la tabla rutas
INSERT INTO rutas (nombreRuta, distanciaKm, costoKm, conductor, totalPasajeros) VALUES
('Ruta Norte', 50.00, 2.00, 1, 35),
('Ruta Sur', 30.00, 1.50, 1, 30);

-- Insertar datos de prueba en la tabla parada
INSERT INTO parada (nombreParada) VALUES
('Parada 1'),
('Parada 2'),
('Parada 3');

-- Insertar datos de prueba en la tabla paradas_rutas
INSERT INTO paradas_rutas (numRuta, numParada, horaInicio, horaFinal, fechaInicio, fechaFinal) VALUES
(1, 1, '08:00:00', '08:30:00', '2024-01-01', '2024-12-31'),
(1, 2, '08:30:00', '09:00:00', '2024-01-01', '2024-12-31'),
(2, 3, '10:00:00', '10:30:00', '2024-01-01', '2024-12-31');

-- Insertar datos de prueba en la tabla empleado_transporte
INSERT INTO empleado_transporte (empleado, transporte, fechaInicio, fechaFinal) VALUES
(1, 1, '2024-01-01', '2024-06-30'),
(1, 2, '2024-07-01', '2024-12-31');

-- Insertar datos de prueba en la tabla reportes
INSERT INTO reportes (descripcion, fechaReporte, numCoordinador) VALUES 
('Reporte de incidentes en Ruta Norte.', '2024-01-15', 1),
('Reporte de mantenimiento para transporte.', '2024-02-20', 1);




/*  C O N S U L T A S */



-- Calcular el costo total de cada ruta en función de la distancia recorrida y el costo por kilómetro.
SELECT 
    numRuta,
    nombreRuta,
    distanciaKm,
    costoKm,
    (distanciaKm * costoKm) AS costoTotal
FROM rutas;


-- Almacenar información de los contratos de servicios realizados con cada cliente, 
-- incluyendo los detalles y el costo total de los servicios asociados.
SELECT 
    c.numContrato AS Contrato,
    c.fechaInicio AS FechaInicio,
    c.fechaFin AS FechaFin,
    IFNULL(SUM(s.costoIndividual * s.numTransportes), 0) AS CostoTotalServicios
FROM contrato c
LEFT JOIN servicio s ON c.numContrato = s.contrato
GROUP BY c.numContrato;


-- Almacenar el número de pasajeros transportados por cada ruta
SELECT 
    r.numRuta AS Ruta,
    r.nombreRuta AS NombreRuta,
    COUNT(DISTINCT et.empleado) AS TotalPasajeros
FROM rutas r
LEFT JOIN paradas_rutas pr ON r.numRuta = pr.numRuta
LEFT JOIN transporte t ON t.numTransporte = pr.numRuta
LEFT JOIN empleado_transporte et ON et.transporte = t.numTransporte
GROUP BY r.numRuta;



-- Calcular el total de pasajeros transportados en todas las rutas de un contrato específico
SELECT 
    c.numContrato AS Contrato,
    COALESCE(SUM(et.numPasajeros), 0) AS TotalPasajeros
FROM contrato c
INNER JOIN servicio s ON c.numContrato = s.contrato
INNER JOIN rutas r ON r.numRuta = s.clave -- Asumiendo que clave en servicio está relacionado con rutas
INNER JOIN transporte t ON t.numTransporte = r.numRuta
INNER JOIN (
    SELECT 
        transporte, 
        COUNT(DISTINCT empleado) AS numPasajeros
    FROM empleado_transporte
    GROUP BY transporte
) et ON et.transporte = t.numTransporte
WHERE c.numContrato = 1 -- Reemplaza 1 por el contrato específico que desees consultar
GROUP BY c.numContrato;


-- Determinar el costo unitario de cada servicio

SELECT 
    s.clave AS ClaveServicio,
    s.nombreServicio AS NombreServicio,
    s.costoIndividual AS CostoIndividual,
    s.numTransportes AS NumTransportes,
    (s.costoIndividual / s.numTransportes) AS CostoUnitario
FROM servicio s
WHERE s.numTransportes > 0;  
 


-- Registrar el total de reportes generados para cada empresa

SELECT 
    e.nombre AS Empresa,
    COUNT(r.numReporte) AS TotalReportes
FROM empresa e
JOIN contrato c ON e.numEmpresa = c.empresa
JOIN reportes r ON c.administrador = r.numCoordinador
GROUP BY e.nombre;

-- Indice

CREATE INDEX idx_empresa_numEmpresa ON empresa(numEmpresa);
CREATE INDEX idx_contrato_administrador ON contrato(administrador);

-- Consulta con indice 

SELECT 
    e.nombre AS Empresa,
    COUNT(r.numReporte) AS TotalReportes
FROM empresa e
JOIN contrato c ON e.numEmpresa = c.empresa
JOIN reportes r ON c.administrador = r.numCoordinador
GROUP BY e.nombre;

SHOW INDEXES FROM empresa;
SHOW INDEXES FROM contrato;

EXPLAIN SELECT 
    e.nombre AS Empresa,
    COUNT(r.numReporte) AS TotalReportes
FROM empresa e
JOIN contrato c ON e.numEmpresa = c.empresa
JOIN reportes r ON c.administrador = r.numCoordinador
GROUP BY e.nombre;




-- Detalles de los pagos realizados, asociados a un contrato y la empresa

SELECT 
    p.numPago AS NumPago, 
    p.monto AS Monto, 
    p.fechaPago AS FechaPago, 
    c.numContrato AS Contrato, 
    e.nombre AS Empresa
FROM pagos p
INNER JOIN contrato c ON p.contrato = c.numContrato
INNER JOIN empresa e ON c.empresa = e.numEmpresa;


/* I N D I C E S */

-- Crear índice en la columna 'contrato' de la tabla 'pagos'
CREATE INDEX idx_pagos_contrato ON pagos(contrato);

-- Crear índice en la columna 'empresa' de la tabla 'contrato'
CREATE INDEX idx_contrato_empresa ON contrato(empresa);

SHOW INDEXES FROM pagos;





/*      V I S T A S     */

CREATE VIEW Costo_Total_Rutas AS
SELECT 
    numRuta,
    nombreRuta,
    distanciaKm,
    costoKm,
    (distanciaKm * costoKm) AS costoTotal
FROM rutas;

SELECT * FROM Costo_Total_Rutas;

--

CREATE VIEW Costo_Total_Contratos AS
SELECT 
    c.numContrato,
    c.fechaInicio,
    c.fechaFin,
    IFNULL(SUM(s.costoIndividual * s.numTransportes), 0) AS costoTotalServicios
FROM contrato c
LEFT JOIN servicio s ON c.numContrato = s.contrato
GROUP BY c.numContrato;

SELECT * FROM Costo_Total_Contratos;

-- Crear una vista para costos unitarios

CREATE VIEW VistaCostoUnitario AS
SELECT 
    s.clave AS ClaveServicio,
    s.nombreServicio AS NombreServicio,
    s.costoIndividual AS CostoIndividual,
    s.numTransportes AS NumTransportes,
    (s.costoIndividual / s.numTransportes) AS CostoUnitario
FROM servicio s
WHERE s.numTransportes > 0;

SELECT * FROM VistaCostoUnitario;

-- T R I G G E R S 



-- Trigger para Validar Fechas de Contrato 

DELIMITER $$

CREATE TRIGGER before_insert_contrato_fecha
BEFORE INSERT ON contrato
FOR EACH ROW
BEGIN
    IF NEW.fechaFin <= NEW.fechaInicio THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'La fecha de fin debe ser posterior a la fecha de inicio.';
    END IF;
END $$

DELIMITER ;


-- P R U E B A S TRIGGER FECHAS

SELECT * FROM CONTRATO;

INSERT INTO contrato (fechaInicio, fechaFin, cantEmpleados, estado, administrador, empresa)
VALUES ('2024-12-01', '2024-12-31', 50, 'activo', 1, 1);

INSERT INTO contrato (fechaInicio, fechaFin, cantEmpleados, estado, administrador, empresa)
VALUES ('2024-12-01', '2024-11-30', 50, 'activo', 1, 1);





-- T R I G G E R Se cancela si el pago es igual a 0

DELIMITER $$

CREATE TRIGGER cancelar_contrato_pago_cero
AFTER INSERT ON pagos
FOR EACH ROW
BEGIN
    -- Si el monto del pago es 0, cambiar el estado del contrato a 'cancelado'
    IF NEW.monto = 0 THEN
        UPDATE contrato
        SET estado = 'cancelado'
        WHERE numContrato = NEW.contrato;
    END IF;
END$$

DELIMITER ;

-- Prueba pago 0

INSERT INTO contrato (fechaInicio, fechaFin, cantEmpleados, estado, administrador, empresa)
VALUES ('2024-01-01', '2024-12-31', 25, 'activo', 1, 2);

SELECT * FROM contrato WHERE numContrato = LAST_INSERT_ID();

INSERT INTO pagos (monto, fechaPago, contrato)
VALUES (0, '2024-11-27', LAST_INSERT_ID());

SELECT * FROM contrato WHERE numContrato = LAST_INSERT_ID();

INSERT INTO pagos (monto, fechaPago, contrato)
VALUES (100.00, '2024-11-28', LAST_INSERT_ID()); 

SELECT * FROM contrato WHERE numContrato = LAST_INSERT_ID();






-- Procedimiento  Actualizar estado de un contrato FUNCIONA
DELIMITER $$

CREATE PROCEDURE actualizar_estado_contratoo(
    IN p_numContrato INT,
    IN p_nuevoEstado ENUM('activo', 'finalizado', 'cancelado', 'inactivo')
)
BEGIN
    -- Actualizar el estado del contrato
    UPDATE contrato
    SET estado = p_nuevoEstado
    WHERE numContrato = p_numContrato;

    -- Si el contrato finaliza, marcar los transportes asociados como disponibles
    IF p_nuevoEstado = 'finalizado' THEN
        UPDATE transporte
        SET disponibilidad = 'disponible'
        WHERE numTransporte IN (
            SELECT matricula
            FROM servicio
            WHERE contrato = p_numContrato
        );
    END IF;
END $$

DELIMITER ;

-- P R U E B A procedimiento actualizar estados

CALL actualizar_estado_contratoo(1, 'activo');
SELECT * FROM contrato;
SELECT * FROM transporte;



-- Procedimiento agregar parada a ruta
DELIMITER $$

CREATE PROCEDURE agregar_parada_a_ruta(
    IN p_numRuta INT,
    IN p_numParada INT,
    IN p_horaInicio TIME,
    IN p_horaFinal TIME,
    IN p_fechaInicio DATE,
    IN p_fechaFinal DATE
)
BEGIN
    INSERT INTO paradas_rutas (numRuta, numParada, horaInicio, horaFinal, fechaInicio, fechaFinal)
    VALUES (p_numRuta, p_numParada, p_horaInicio, p_horaFinal, p_fechaInicio, p_fechaFinal);
END$$

DELIMITER ;


-- Insertar una ruta de prueba
INSERT INTO rutas (nombreRuta, distanciaKm, costoKm, totalPasajeros) 
VALUES ('Ruta Centro', 15.5, 1.25, 0);

-- Insertar una parada de prueba
INSERT INTO parada (nombreParada) 
VALUES ('Parada Central');

CALL agregar_parada_a_ruta(
    3,                -- numRuta (ID de la ruta creada)
    1,                -- numParada (ID de la parada creada)
    '08:00:00',       -- horaInicio
    '08:30:00',       -- horaFinal
    '2024-12-01',     -- fechaInicio
    '2024-12-31'      -- fechaFinal
);

SELECT * from paradas_rutas;



-- Procedimiento registrar `empresa`

DELIMITER $$
CREATE PROCEDURE registrarEmpresa(
    IN p_nombre VARCHAR(100),
    IN p_direccion VARCHAR(200),
    IN p_telefono VARCHAR(20)
)
BEGIN
    INSERT INTO empresa (nombre, direccion, telefono)
    VALUES (p_nombre, p_direccion, p_telefono);
END$$
DELIMITER ;

CALL registrarEmpresa('Transportes XYZ', 'Calle Falsa 123', '5551234567');
SELECT * FROM empresa;



-- Procedimiento para registrar un contrato

DELIMITER //
CREATE PROCEDURE registrarContrato(
    IN p_fechaInicio DATE,
    IN p_fechaFin DATE,
    IN p_cantEmpleados INT,
    IN p_administrador INT,
    IN p_empresa INT
)
BEGIN
    INSERT INTO contrato (fechaInicio, fechaFin, cantEmpleados, administrador, empresa)
    VALUES (p_fechaInicio, p_fechaFin, p_cantEmpleados, p_administrador, p_empresa);
END //
DELIMITER ;

-- Prueba registro `contrato`

CALL registrarContrato('2024-01-01', '2024-12-31', 50, 1, 1);




SELECT * FROM contrato;




select * from usuario;

INSERT INTO usuario (username, password, userType)
VALUES
('coord2', 'password123', 'coordinador'),
('coord3', 'password123', 'coordinador'),
('coord4', 'password123', 'coordinador'),
('coord5', 'password123', 'coordinador'),
('coord6', 'password123', 'coordinador');

INSERT INTO coordinador (nombre, primerApellido, segundoApellido, id_usuario)
VALUES
('Ana', 'González', 'Martínez', 10),
('Carlos', 'Ramírez', 'Hernández', 11),
('Luisa', 'Torres', 'Pérez', 12),
('Pedro', 'López', 'Díaz', 13),
('María', 'Sánchez', 'Morales', 14);

SELECT * FROM coordinador;

INSERT INTO usuario (username, password, userType)
VALUES
('admin2', 'password123', 'administrador'),
('admin3', 'password123', 'administrador'),
('admin4', 'password123', 'administrador'),
('admin5', 'password123', 'administrador'),
('admin6', 'password123', 'administrador');

INSERT INTO coordinador (nombre, primerApellido, segundoApellido, id_usuario)
VALUES
('Cristina', 'Pérez', 'Gómez', 15),
('María', 'López', 'Martínez', 16),
('Carlos', 'Rodríguez', 'Sánchez', 17),
('Laura', 'Fernández', 'Gutiérrez', 18),
('Pedro', 'Jiménez', 'Martín', 19);

SELECT * from empresa;

INSERT INTO empresa (nombre, direccion, telefono, totalSolicitudes, totalReportes)
VALUES
('Empresa C', 'Avenida Libertad 78, Ciudad C', '555-9101', 3, 2),
('Empresa D', 'Calle del Sol 56, Ciudad D', '555-1122', 4, 3),
('Empresa E', 'Boulevard Norte 98, Ciudad E', '555-3344', 5, 4),
('Empresa F', 'Calle del Río 34, Ciudad F', '555-5566', 6, 5),
('Empresa G', 'Avenida Central 12, Ciudad G', '555-7788', 7, 6),
('Empresa H', 'Plaza Mayor 87, Ciudad H', '555-9900', 8, 7);

INSERT INTO representante (nombre, primerApellido, segundoApellido, empresa, id_usuario)
VALUES
('Ana', 'Hernández', 'Ruiz', 8, 36),
('Carlos', 'Martínez', 'González', 9, 37),
('Luisa', 'Pérez', 'Torres', 10, 38),
('Pedro', 'Rodríguez', 'Díaz', 11, 39),
('María', 'Sánchez', 'Morales', 12, 40);

INSERT INTO usuario (username, password, userType)
VALUES
('rep2', 'password123', 'representante'),
('rep3', 'password123', 'representante'),
('rep4', 'password123', 'representante'),
('rep5', 'password123', 'representante'),
('rep6', 'password123', 'representante');

INSERT INTO contrato (fechaInicio, fechaFin, cantEmpleados, estado, administrador, empresa)
VALUES
('2024-12-01', '2024-12-31', 50, 'activo', 2, 8),
('2024-12-05', '2024-12-31', 40, 'activo', 3, 9),
('2024-12-10', '2024-12-31', 60, 'activo', 4, 10),
('2024-12-15', '2024-12-31', 55, 'activo', 5, 11),
('2024-12-20', '2024-12-31', 45, 'activo', 6, 12);



SELECT * from empresa;

SELECT * from empleado;

SELECT * from usuario;

INSERT INTO administrador (nombre, primerApellido, segundoApellido, id_usuario)
VALUES
('Danae', 'Gomez', 'García', 11),
('María', 'López', 'Martínez', 12),
('Carlos', 'Rodríguez', 'Sánchez', 13),
('Laura', 'Fernández', 'Gutiérrez', 14),
('Pedro', 'Jiménez', 'Morales', 15);

INSERT INTO usuario (username, password, userType)
VALUES
('emp2', 'password123', 'empleado'),
('emp3', 'password123', 'empleado'),
('emp4', 'password123', 'empleado'),
('emp5', 'password123', 'empleado'),
('emp6', 'password123', 'empleado');

INSERT INTO empleado (nombre, primerApellido, segundoApellido, empresa, id_usuario) VALUES
('Angela', 'Portilo', 'López', 8, 31),
('Daniel', 'Sanchez', 'Jimenez', 9, 32),
('Marcos', 'Martínez', 'Cortes', 10, 33),
('Jesus', 'Garcia', 'Villa', 11, 34),
('Brayan', 'Soto', 'Luna', 12, 35);

SELECT * from empresa;

SELECT * from conductor;

SELECT nombre from empleado 
WHERe empresa = 9;


SELECT * FROM reporte_conductor WHERE numConductor = 1;

SELECT * from empresa

SELECT * from reporte_conductor

SELECT * from transporte

SELECT * from conductor

SELECT * from usuario

INSERT INTO usuario (username, password, userType)
VALUES
('cond2', 'password123', 'conductor'),
('cond3', 'password123', 'conductor'),
('cond4', 'password123', 'conductor'),
('cond5', 'password123', 'conductor'),
('cond6', 'password123', 'conductor');

INSERT INTO conductor (nombre, primerApellido, segundoApellido, id_usuario) VALUES
('Daniel', 'Soto', 'Villa', 41),
('Neyzer', 'Hernandez', 'Sanchez', 42),
('Andres', 'Martínez', 'Morales', 43),
('Arturo', 'Villarreal', 'Gomez', 44),
('Brayan', 'Soto', 'Soto', 45);

INSERT INTO transporte (matricula, capacidad, disponibilidad, marca, modelo, conductor) VALUES
('DEF456', 30, 'disponible', 3, 4, 2),
('GHI789', 30, 'disponible', 4, 5, 3),
('JKL111', 30, 'disponible', 5, 6, 4),
('MNO112', 30, 'disponible', 6, 7, 5),
('PQR103', 30, 'disponible', 7, 8, 6);


-- Insertar datos de prueba en la tabla marca
INSERT INTO marca (nombre) VALUES
('DAF'),
('RENAULT'),
('Volvo'),
('SCANIA'),
('Mercedes-Benz');


-- Insertar datos de prueba en la tabla modelo
INSERT INTO modelo (nombre, anio, marca) VALUES
('Corolla', 2020, 4),
('Fiesta', 2019, 5),
('Spark', 2020, 6),
('Spark', 2018, 7),
('Spark', 2017, 8);

SELECT * FROM marca


SELECT * from coordinador