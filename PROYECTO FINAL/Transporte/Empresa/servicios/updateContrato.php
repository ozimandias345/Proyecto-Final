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

// Validar que el ID de la empresa exista en la sesión
if (!$empresaId) {
    header("Location: ../contratos.php?error=" . urlencode("No se encontró el ID de la empresa en la sesión."));
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar y sanitizar entradas
    $idContrato = filter_input(INPUT_POST, 'id_contrato', FILTER_VALIDATE_INT);
    $fechaInicio = filter_input(INPUT_POST, 'fechaInicio', FILTER_SANITIZE_STRING);
    $fechaFin = filter_input(INPUT_POST, 'fechaFin', FILTER_SANITIZE_STRING);
    $cantEmpleados = filter_input(INPUT_POST, 'cantEmpleados', FILTER_VALIDATE_INT);

    // Validar entradas obligatorias
    if (!$idContrato || !$fechaInicio || !$fechaFin || !$cantEmpleados || $cantEmpleados <= 0) {
        header("Location: ../contratos.php?error=" . urlencode("Por favor, complete todos los campos correctamente."));
        exit();
    }

    try {
        // Preparar la consulta de actualización
        $stmt = $conn->prepare("
            UPDATE contrato
            SET fechaInicio = :fechaInicio,
                fechaFin = :fechaFin,
                cantEmpleados = :cantEmpleados
            WHERE numContrato = :idContrato
              AND empresa = :empresaId
        ");

        // Vincular parámetros
        $stmt->bindParam(':fechaInicio', $fechaInicio, PDO::PARAM_STR);
        $stmt->bindParam(':fechaFin', $fechaFin, PDO::PARAM_STR);
        $stmt->bindParam(':cantEmpleados', $cantEmpleados, PDO::PARAM_INT);
        $stmt->bindParam(':idContrato', $idContrato, PDO::PARAM_INT);
        $stmt->bindParam(':empresaId', $empresaId, PDO::PARAM_INT);

        // Ejecutar la consulta
        if ($stmt->execute()) {
            // Redirigir con un mensaje de éxito
            header("Location: ../contratos.php?success=Contrato actualizado exitosamente.");
        } else {
            // Redirigir con un mensaje de error
            header("Location: ../contratos.php?error=" . urlencode("Error al actualizar el contrato."));
        }
        exit();
    } catch (PDOException $e) {
        // Redirigir con un mensaje de error detallado
        header("Location: ../contratos.php?error=" . urlencode("Error al actualizar el contrato: " . $e->getMessage()));
        exit();
    }
} else {
    // Si se accede directamente al archivo
    header("Location: ../contratos.php");
    exit();
}
