<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../conexion/conexion.php'; // Incluir conexión a la base de datos

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener datos del formulario y sanitizar entradas
    $nombreEmpresa = trim($_POST['nombre_empresa']);
    $direccion = trim($_POST['direccion']);
    $telefono = trim($_POST['telefono']);
    $emailRepresentante = trim($_POST['email']);
    $password = trim($_POST['password']);
    $nombreRepresentante = trim($_POST['nombre']);
    $apellidoPaterno = trim($_POST['apellido']);
    $apellidoMaterno = trim($_POST['apellido_materno']);

    // Validar campos requeridos
    if (empty($nombreEmpresa) || empty($direccion) || empty($telefono) || empty($emailRepresentante) || empty($password) || empty($nombreRepresentante) || empty($apellidoPaterno)) {
        header("Location: ../index.php?error=" . urlencode("Todos los campos son obligatorios."));
        exit();
    }

    try {
        // Iniciar una transacción
        $conn->beginTransaction();

        // Insertar el usuario (representante) en la tabla `usuario`
        $stmtUsuario = $conn->prepare("
            INSERT INTO usuario (username, password, userType)
            VALUES (:username, :password, 'representante')
        ");
        $stmtUsuario->bindParam(':username', $emailRepresentante, PDO::PARAM_STR);
        $stmtUsuario->bindParam(':password', $password, PDO::PARAM_STR); // Aquí se recomienda usar un hash de contraseña
        $stmtUsuario->execute();
        $idUsuario = $conn->lastInsertId();

        // Insertar la empresa en la tabla `empresa`
        $stmtEmpresa = $conn->prepare("
            INSERT INTO empresa (nombre, direccion, telefono)
            VALUES (:nombre, :direccion, :telefono)
        ");
        $stmtEmpresa->bindParam(':nombre', $nombreEmpresa, PDO::PARAM_STR);
        $stmtEmpresa->bindParam(':direccion', $direccion, PDO::PARAM_STR);
        $stmtEmpresa->bindParam(':telefono', $telefono, PDO::PARAM_STR);
        $stmtEmpresa->execute();
        $idEmpresa = $conn->lastInsertId();

        // Insertar el representante en la tabla `representante`
        $stmtRepresentante = $conn->prepare("
            INSERT INTO representante (nombre, primerApellido, segundoApellido, empresa, id_usuario)
            VALUES (:nombre, :primerApellido, :segundoApellido, :empresa, :idUsuario)
        ");
        $stmtRepresentante->bindParam(':nombre', $nombreRepresentante, PDO::PARAM_STR);
        $stmtRepresentante->bindParam(':primerApellido', $apellidoPaterno, PDO::PARAM_STR);
        $stmtRepresentante->bindParam(':segundoApellido', $apellidoMaterno, PDO::PARAM_STR);
        $stmtRepresentante->bindParam(':empresa', $idEmpresa, PDO::PARAM_INT);
        $stmtRepresentante->bindParam(':idUsuario', $idUsuario, PDO::PARAM_INT);
        $stmtRepresentante->execute();

        // Confirmar la transacción
        $conn->commit();

        // Redirigir con mensaje de éxito
        header("Location: ../index.php?success=Registro completado exitosamente");
        exit();
    } catch (Exception $e) {
        // Revertir la transacción en caso de error
        $conn->rollBack();
        header("Location: ../index.php?error=" . urlencode("Error al registrar la empresa: " . $e->getMessage()));
        exit();
    }
} else {
    // Redirigir si el archivo fue accedido directamente
    header("Location: ../index.php");
    exit();
}
