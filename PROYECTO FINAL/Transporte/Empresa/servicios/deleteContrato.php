<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../../conexion/conexion.php'; // Incluir conexión a la base de datos

session_start();

// Verificar si el usuario está autenticado y es representante
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'representante') {
    header("Location: ../index.php");
    exit();
}

// Obtener el ID de la empresa desde la sesión
$empresaId = $_SESSION['empresa_id'];

// Verificar si se envió un ID de contrato para eliminar
if (isset($_GET['id'])) {
    $contratoId = $_GET['id'];

    try {
        // Verificar que el contrato pertenece a la empresa del representante
        $stmtCheck = $conn->prepare("
            SELECT numContrato 
            FROM contrato 
            WHERE numContrato = :contratoId AND empresa = :empresaId
        ");
        $stmtCheck->bindParam(':contratoId', $contratoId, PDO::PARAM_INT);
        $stmtCheck->bindParam(':empresaId', $empresaId, PDO::PARAM_INT);
        $stmtCheck->execute();

        if ($stmtCheck->rowCount() === 0) {
            throw new Exception("El contrato no pertenece a su empresa o no existe.");
        }

        // Eliminar el contrato
        $stmtDelete = $conn->prepare("
            DELETE FROM contrato 
            WHERE numContrato = :contratoId
        ");
        $stmtDelete->bindParam(':contratoId', $contratoId, PDO::PARAM_INT);
        $stmtDelete->execute();

        // Redirigir con un mensaje de éxito
        header("Location: ../contratos.php?success=El contrato se eliminó exitosamente.");
        exit();
    } catch (Exception $e) {
        // Redirigir con un mensaje de error
        header("Location: ../contratos.php?error=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    // Si no se proporciona un ID de contrato, redirigir con un error
    header("Location: ../contratos.php?error=No se proporcionó un ID de contrato para eliminar.");
    exit();
}
