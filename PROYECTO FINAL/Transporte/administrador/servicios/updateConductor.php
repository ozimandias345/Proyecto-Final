<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../../conexion/conexion.php'; // Incluir conexión a la base de datos

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener datos enviados desde el formulario
    $idConductor = $_POST['id_conductor'];
    $nombreUsuario = trim($_POST['nombre_usuario']);
    $password = trim($_POST['password']);
    $nombre = trim($_POST['nombre']);
    $primerApellido = trim($_POST['apellido1']);
    $segundoApellido = trim($_POST['apellido2']);

    try {
        // Iniciar una transacción
        $conn->beginTransaction();

        // Actualizar datos en la tabla usuario
        $stmtUsuario = $conn->prepare("
            UPDATE usuario
            SET username = :username, password = :password
            WHERE id = (SELECT id_usuario FROM conductor WHERE numConductor = :idConductor)
        ");
        $stmtUsuario->bindParam(':username', $nombreUsuario, PDO::PARAM_STR);
        $stmtUsuario->bindParam(':password', $password, PDO::PARAM_STR); // Guardar sin hash (como se solicitó)
        $stmtUsuario->bindParam(':idConductor', $idConductor, PDO::PARAM_INT);
        $stmtUsuario->execute();

        // Actualizar datos en la tabla conductor
        $stmtConductor = $conn->prepare("
            UPDATE conductor
            SET nombre = :nombre, primerApellido = :primerApellido, segundoApellido = :segundoApellido
            WHERE numConductor = :idConductor
        ");
        $stmtConductor->bindParam(':nombre', $nombre, PDO::PARAM_STR);
        $stmtConductor->bindParam(':primerApellido', $primerApellido, PDO::PARAM_STR);
        $stmtConductor->bindParam(':segundoApellido', $segundoApellido, PDO::PARAM_STR);
        $stmtConductor->bindParam(':idConductor', $idConductor, PDO::PARAM_INT);
        $stmtConductor->execute();

        // Confirmar la transacción
        $conn->commit();

        // Redirigir con un mensaje de éxito
        header("Location: ../conductores.php?success=Conductor actualizado correctamente.");
        exit();
    } catch (Exception $e) {
        // Revertir la transacción en caso de error
        $conn->rollBack();
        header("Location: ../conductores.php?error=" . urlencode("Error al actualizar el conductor: " . $e->getMessage()));
        exit();
    }
} else {
    // Si se accede al archivo directamente, redirigir al listado de conductores
    header("Location: ../conductores.php");
    exit();
}
