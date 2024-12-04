<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../../conexion/conexion.php'; // Conexión a la base de datos

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener datos del formulario
    $nombreUsuario = trim($_POST['nombre_usuario']);
    $password = trim($_POST['password']); // Contraseña en texto plano
    $nombre = trim($_POST['nombre']);
    $primerApellido = trim($_POST['apellido1']);
    $segundoApellido = trim($_POST['apellido2']);

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    require '../../conexion/conexion.php'; // Conexión a la base de datos

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Obtener datos del formulario
        $nombreUsuario = trim($_POST['nombre_usuario']);
        $password = trim($_POST['password']); // Contraseña en texto plano
        $nombre = trim($_POST['nombre']);
        $primerApellido = trim($_POST['apellido1']);
        $segundoApellido = trim($_POST['apellido2']);

        try {
            // Verificar si el nombre de usuario ya existe
            $stmtCheckUsuario = $conn->prepare("SELECT COUNT(*) FROM usuario WHERE username = :username");
            $stmtCheckUsuario->bindParam(':username', $nombreUsuario, PDO::PARAM_STR);
            $stmtCheckUsuario->execute();
            $usuarioExistente = $stmtCheckUsuario->fetchColumn();

            if ($usuarioExistente > 0) {
                // Redirigir con mensaje de error si el nombre de usuario ya existe
                header("Location: ../coordinadores.php?error=" . urlencode("Nombre de usuario ya tomado"));
                exit();
            }

            // Iniciar una transacción
            $conn->beginTransaction();

            // Insertar el usuario en la tabla usuario
            $stmtUsuario = $conn->prepare("
            INSERT INTO usuario (username, password, userType)
            VALUES (:username, :password, 'coordinador')
        ");
            $stmtUsuario->bindParam(':username', $nombreUsuario, PDO::PARAM_STR);
            $stmtUsuario->bindParam(':password', $password, PDO::PARAM_STR); // Guardar sin cifrar
            $stmtUsuario->execute();

            // Obtener el ID del usuario recién creado
            $idUsuario = $stmtUsuario->rowCount() > 0 ? $conn->lastInsertId() : null;

            if (!$idUsuario) {
                throw new Exception("Error al insertar el usuario.");
            }

            // Insertar el coordinador en la tabla coordinador
            $stmtCoordinador = $conn->prepare("
            INSERT INTO coordinador (nombre, primerApellido, segundoApellido, id_usuario)
            VALUES (:nombre, :primerApellido, :segundoApellido, :idUsuario)
        ");
            $stmtCoordinador->bindParam(':nombre', $nombre, PDO::PARAM_STR);
            $stmtCoordinador->bindParam(':primerApellido', $primerApellido, PDO::PARAM_STR);
            $stmtCoordinador->bindParam(':segundoApellido', $segundoApellido, PDO::PARAM_STR);
            $stmtCoordinador->bindParam(':idUsuario', $idUsuario, PDO::PARAM_INT);
            $stmtCoordinador->execute();

            // Confirmar la transacción
            $conn->commit();

            // Redirigir al formulario con mensaje de éxito
            header("Location: ../coordinadores.php?success=1");
            exit();
        } catch (Exception $e) {
            // Revertir la transacción en caso de error
            $conn->rollBack();
            header("Location: ../coordinadores.php?error=" . urlencode($e->getMessage()));
            exit();
        }
    } else {
        // Redirigir si el archivo fue accedido directamente
        header("Location: ../coordinadores.php");
        exit();
    }


    try {
        // Verificar si el nombre de usuario ya existe
        $stmtCheckUsuario = $conn->prepare("SELECT COUNT(*) FROM usuario WHERE username = :username");
        $stmtCheckUsuario->bindParam(':username', $nombreUsuario, PDO::PARAM_STR);
        $stmtCheckUsuario->execute();
        $usuarioExistente = $stmtCheckUsuario->fetchColumn();

        if ($usuarioExistente > 0) {
            // Redirigir con mensaje de error si el nombre de usuario ya existe
            header("Location: ../coordinadores.php?error=" . urlencode("Nombre de usuario ya tomado"));
            exit();
        }

        // Iniciar una transacción
        $conn->beginTransaction();

        // Insertar el usuario en la tabla usuario
        $stmtUsuario = $conn->prepare("
            INSERT INTO usuario (username, password, userType)
            VALUES (:username, :password, 'coordinador')
        ");
        $stmtUsuario->bindParam(':username', $nombreUsuario, PDO::PARAM_STR);
        $stmtUsuario->bindParam(':password', $password, PDO::PARAM_STR); // Guardar sin cifrar
        $stmtUsuario->execute();

        // Obtener el ID del usuario recién creado
        $idUsuario = $stmtUsuario->rowCount() > 0 ? $conn->lastInsertId() : null;

        if (!$idUsuario) {
            throw new Exception("Error al insertar el usuario.");
        }

        // Insertar el coordinador en la tabla coordinador
        $stmtCoordinador = $conn->prepare("
            INSERT INTO coordinador (nombre, primerApellido, segundoApellido, id_usuario)
            VALUES (:nombre, :primerApellido, :segundoApellido, :idUsuario)
        ");
        $stmtCoordinador->bindParam(':nombre', $nombre, PDO::PARAM_STR);
        $stmtCoordinador->bindParam(':primerApellido', $primerApellido, PDO::PARAM_STR);
        $stmtCoordinador->bindParam(':segundoApellido', $segundoApellido, PDO::PARAM_STR);
        $stmtCoordinador->bindParam(':idUsuario', $idUsuario, PDO::PARAM_INT);
        $stmtCoordinador->execute();

        // Confirmar la transacción
        $conn->commit();

        // Redirigir al formulario con mensaje de éxito
        header("Location: ../coordinadores.php?success=1");
        exit();
    } catch (Exception $e) {
        // Revertir la transacción en caso de error
        $conn->rollBack();
        header("Location: ../coordinadores.php?error=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    // Redirigir si el archivo fue accedido directamente
    header("Location: ../coordinadores.php");
    exit();
}
