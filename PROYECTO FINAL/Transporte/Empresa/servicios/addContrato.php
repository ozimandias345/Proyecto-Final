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
    $fechaInicio = filter_input(INPUT_POST, 'fechaInicio', FILTER_SANITIZE_STRING);
    $fechaFin = filter_input(INPUT_POST, 'fechaFin', FILTER_SANITIZE_STRING);
    $cantEmpleados = filter_input(INPUT_POST, 'cantEmpleados', FILTER_VALIDATE_INT);
    $estado = 'inactivo'; // Valor por defecto para estado
    $administrador = 1; // Administrador por defecto

    // Validar entradas
    if (!$fechaInicio || !$fechaFin || !$cantEmpleados || $cantEmpleados <= 0) {
        header("Location: ../contratos.php?error=" . urlencode("Por favor, complete todos los campos correctamente."));
        exit();
    }

    try {
        // Preparar la consulta de inserción
        $stmt = $conn->prepare("
            INSERT INTO contrato (fechaInicio, fechaFin, cantEmpleados, estado, administrador, empresa)
            VALUES (:fechaInicio, :fechaFin, :cantEmpleados, :estado, :administrador, :empresa)
        ");

        // Vincular parámetros
        $stmt->bindParam(':fechaInicio', $fechaInicio, PDO::PARAM_STR);
        $stmt->bindParam(':fechaFin', $fechaFin, PDO::PARAM_STR);
        $stmt->bindParam(':cantEmpleados', $cantEmpleados, PDO::PARAM_INT);
        $stmt->bindParam(':estado', $estado, PDO::PARAM_STR);
        $stmt->bindParam(':administrador', $administrador, PDO::PARAM_INT);
        $stmt->bindParam(':empresa', $empresaId, PDO::PARAM_INT);

        // Ejecutar la consulta
        $stmt->execute();

        // Redirigir con un mensaje de éxito
        header("Location: ../contratos.php?success=Contrato agregado exitosamente.");
        exit();
    } catch (PDOException $e) {
        // Redirigir con un mensaje de error
        header("Location: ../contratos.php?error=" . urlencode("Error al agregar el contrato: " . $e->getMessage()));
        exit();
    }
} else {
    // Si se accede directamente al archivo
    header("Location: ../contratos.php");
    exit();
}
