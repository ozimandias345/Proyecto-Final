<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../../conexion/conexion.php'; // Incluir conexión a la base de datos

session_start();

// Verificar si el usuario está autenticado y es representante
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'representante') {
    header("Location: ../../index.php");
    exit();
}

// Obtener el ID de la empresa desde la sesión
$empresaId = $_SESSION['empresa_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener los datos enviados desde el formulario
    $nombreUsuario = trim($_POST['nombre_usuario']);
    $password = trim($_POST['password']);
    $nombre = trim($_POST['nombre']);
    $primerApellido = trim($_POST['apellido1']);
    $segundoApellido = trim($_POST['apellido2']);

    try {
        // Verificar si el usuario ya existe
        $stmtCheck = $conn->prepare("SELECT id FROM usuario WHERE username = :username");
        $stmtCheck->bindParam(':username', $nombreUsuario, PDO::PARAM_STR);
        $stmtCheck->execute();

        if ($stmtCheck->rowCount() > 0) {
            // El usuario ya existe
            header("Location: ../empleados.php?duplicate=1");
            exit();
        }

        // Iniciar una transacción
        $conn->beginTransaction();

        // Insertar el usuario en la tabla `usuario`
        $stmtUsuario = $conn->prepare("
            INSERT INTO usuario (username, password, userType)
            VALUES (:username, :password, 'empleado')
        ");
        $stmtUsuario->bindParam(':username', $nombreUsuario, PDO::PARAM_STR);
        $stmtUsuario->bindParam(':password', $password, PDO::PARAM_STR);
        $stmtUsuario->execute();

        // Obtener el ID del usuario recién creado
        $idUsuario = $conn->lastInsertId();

        // Insertar los datos del empleado en la tabla `empleado`
        $stmtEmpleado = $conn->prepare("
            INSERT INTO empleado (nombre, primerApellido, segundoApellido, id_usuario, empresa)
            VALUES (:nombre, :primerApellido, :segundoApellido, :idUsuario, :empresaId)
        ");
        $stmtEmpleado->bindParam(':nombre', $nombre, PDO::PARAM_STR);
        $stmtEmpleado->bindParam(':primerApellido', $primerApellido, PDO::PARAM_STR);
        $stmtEmpleado->bindParam(':segundoApellido', $segundoApellido, PDO::PARAM_STR);
        $stmtEmpleado->bindParam(':idUsuario', $idUsuario, PDO::PARAM_INT);
        $stmtEmpleado->bindParam(':empresaId', $empresaId, PDO::PARAM_INT);
        $stmtEmpleado->execute();

        // Confirmar la transacción
        $conn->commit();

        // Redirigir al formulario con un mensaje de éxito
        header("Location: ../empleados.php?success=Empleado agregado correctamente");
        exit();
    } catch (Exception $e) {
        // Revertir la transacción en caso de error
        $conn->rollBack();
        header("Location: ../empleados.php?error=" . urlencode("Error al agregar el empleado: " . $e->getMessage()));
        exit();
    }
} else {
    // Redirigir si el archivo fue accedido directamente
    header("Location: ../empleados.php");
    exit();
}
