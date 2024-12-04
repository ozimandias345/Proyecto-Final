<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../../conexion/conexion.php'; // Incluir conexión a la base de datos

if (isset($_GET['id'])) {
    $idConductor = $_GET['id']; // Obtener el ID del conductor a eliminar

    try {
        // Iniciar una transacción
        $conn->beginTransaction();

        // Obtener el ID de usuario relacionado al conductor
        $stmtUsuario = $conn->prepare("
            SELECT id_usuario FROM conductor WHERE numConductor = :idConductor
        ");
        $stmtUsuario->bindParam(':idConductor', $idConductor, PDO::PARAM_INT);
        $stmtUsuario->execute();
        $usuario = $stmtUsuario->fetch(PDO::FETCH_ASSOC);

        if (!$usuario) {
            throw new Exception("Conductor no encontrado.");
        }

        $idUsuario = $usuario['id_usuario'];

        // Eliminar al conductor de la tabla `conductor`
        $stmtConductor = $conn->prepare("
            DELETE FROM conductor WHERE numConductor = :idConductor
        ");
        $stmtConductor->bindParam(':idConductor', $idConductor, PDO::PARAM_INT);
        $stmtConductor->execute();

        // Eliminar al usuario de la tabla `usuario`
        $stmtUsuario = $conn->prepare("
            DELETE FROM usuario WHERE id = :idUsuario
        ");
        $stmtUsuario->bindParam(':idUsuario', $idUsuario, PDO::PARAM_INT);
        $stmtUsuario->execute();

        // Confirmar la transacción
        $conn->commit();

        // Redirigir con mensaje de éxito
        header("Location: ../conductores.php?success=Conductor eliminado correctamente.");
        exit();
    } catch (Exception $e) {
        // Revertir la transacción en caso de error
        $conn->rollBack();
        header("Location: ../conductores.php?error=" . urlencode("Error al eliminar el conductor: " . $e->getMessage()));
        exit();
    }
} else {
    // Redirigir si se accede sin un ID válido
    header("Location: ../conductores.php?error=" . urlencode("ID de conductor no especificado."));
    exit();
}
