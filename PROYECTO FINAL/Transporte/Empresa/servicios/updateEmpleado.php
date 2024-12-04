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
    $idEmpleado = intval($_POST['id_empleado']);
    $nombreUsuario = trim($_POST['nombre_usuario']);
    $password = trim($_POST['password']);
    $nombre = trim($_POST['nombre']);
    $primerApellido = trim($_POST['apellido1']);
    $segundoApellido = trim($_POST['apellido2']);

    try {
        // Verificar si el usuario ya existe para otro empleado
        $stmtCheck = $conn->prepare("
            SELECT id 
            FROM usuario 
            WHERE username = :username AND id NOT IN (
                SELECT id_usuario FROM empleado WHERE numEmpleado = :idEmpleado
            )
        ");
        $stmtCheck->bindParam(':username', $nombreUsuario, PDO::PARAM_STR);
        $stmtCheck->bindParam(':idEmpleado', $idEmpleado, PDO::PARAM_INT);
        $stmtCheck->execute();

        if ($stmtCheck->rowCount() > 0) {
            // El usuario ya existe para otro registro
            header("Location: ../empleados.php?duplicate=1");
            exit();
        }

        // Iniciar una transacción
        $conn->beginTransaction();

        // Actualizar el usuario en la tabla `usuario`
        $stmtUsuario = $conn->prepare("
            UPDATE usuario 
            SET username = :username, password = :password 
            WHERE id = (
                SELECT id_usuario 
                FROM empleado 
                WHERE numEmpleado = :idEmpleado AND empresa = :empresaId
            )
        ");
        $stmtUsuario->bindParam(':username', $nombreUsuario, PDO::PARAM_STR);
        $stmtUsuario->bindParam(':password', $password, PDO::PARAM_STR);
        $stmtUsuario->bindParam(':idEmpleado', $idEmpleado, PDO::PARAM_INT);
        $stmtUsuario->bindParam(':empresaId', $empresaId, PDO::PARAM_INT);
        $stmtUsuario->execute();

        // Actualizar los datos del empleado en la tabla `empleado`
        $stmtEmpleado = $conn->prepare("
            UPDATE empleado 
            SET nombre = :nombre, primerApellido = :primerApellido, segundoApellido = :segundoApellido
            WHERE numEmpleado = :idEmpleado AND empresa = :empresaId
        ");
        $stmtEmpleado->bindParam(':nombre', $nombre, PDO::PARAM_STR);
        $stmtEmpleado->bindParam(':primerApellido', $primerApellido, PDO::PARAM_STR);
        $stmtEmpleado->bindParam(':segundoApellido', $segundoApellido, PDO::PARAM_STR);
        $stmtEmpleado->bindParam(':idEmpleado', $idEmpleado, PDO::PARAM_INT);
        $stmtEmpleado->bindParam(':empresaId', $empresaId, PDO::PARAM_INT);
        $stmtEmpleado->execute();

        // Confirmar la transacción
        $conn->commit();

        // Redirigir al formulario con un mensaje de éxito
        header("Location: ../empleados.php?success=Empleado actualizado correctamente");
        exit();
    } catch (Exception $e) {
        // Revertir la transacción en caso de error
        $conn->rollBack();
        header("Location: ../empleados.php?error=" . urlencode("Error al actualizar el empleado: " . $e->getMessage()));
        exit();
    }
} else {
    // Redirigir si el archivo fue accedido directamente
    header("Location: ../empleados.php");
    exit();
}
