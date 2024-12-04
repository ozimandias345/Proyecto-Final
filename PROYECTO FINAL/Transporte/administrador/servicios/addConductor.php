<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../../conexion/conexion.php'; // Ruta a tu archivo de conexión

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener los datos enviados desde el formulario
    $nombreUsuario = trim($_POST['nombre_usuario']);
    $password = trim($_POST['password']);
    $nombre = trim($_POST['nombre']);
    $primerApellido = trim($_POST['apellido1']);
    $segundoApellido = trim($_POST['apellido2']);

    try {
        // Iniciar una transacción
        $conn->beginTransaction();

        // Verificar si el nombre de usuario ya existe
        $stmtCheckUsuario = $conn->prepare("SELECT id FROM usuario WHERE username = :username");
        $stmtCheckUsuario->bindParam(':username', $nombreUsuario, PDO::PARAM_STR);
        $stmtCheckUsuario->execute();

        if ($stmtCheckUsuario->rowCount() > 0) {
            // Nombre de usuario duplicado, redirigir con mensaje de error
            header("Location: ../conductores.php?error=" . urlencode("El nombre de usuario ya existe. Por favor, elige otro."));
            exit();
        }

        // Insertar el usuario en la tabla `usuario`
        $stmtUsuario = $conn->prepare("
            INSERT INTO usuario (username, password, userType)
            VALUES (:username, :password, 'conductor')
        ");
        $stmtUsuario->bindParam(':username', $nombreUsuario, PDO::PARAM_STR);
        $stmtUsuario->bindParam(':password', $password, PDO::PARAM_STR);
        $stmtUsuario->execute();

        // Obtener el ID del usuario recién creado
        $idUsuario = $conn->lastInsertId();

        // Insertar los datos del conductor en la tabla `conductor`
        $stmtConductor = $conn->prepare("
            INSERT INTO conductor (nombre, primerApellido, segundoApellido, id_usuario)
            VALUES (:nombre, :primerApellido, :segundoApellido, :idUsuario)
        ");
        $stmtConductor->bindParam(':nombre', $nombre, PDO::PARAM_STR);
        $stmtConductor->bindParam(':primerApellido', $primerApellido, PDO::PARAM_STR);
        $stmtConductor->bindParam(':segundoApellido', $segundoApellido, PDO::PARAM_STR);
        $stmtConductor->bindParam(':idUsuario', $idUsuario, PDO::PARAM_INT);
        $stmtConductor->execute();

        // Confirmar la transacción
        $conn->commit();

        // Redirigir al formulario con un mensaje de éxito
        header("Location: ../conductores.php?success=Conductor agregado correctamente.");
        exit();
    } catch (Exception $e) {
        // Revertir la transacción en caso de error
        $conn->rollBack();
        header("Location: ../conductores.php?error=" . urlencode("Error al agregar conductor: " . $e->getMessage()));
        exit();
    }
} else {
    // Redirigir si el archivo fue accedido directamente
    header("Location: ../conductores.php");
    exit();
}
