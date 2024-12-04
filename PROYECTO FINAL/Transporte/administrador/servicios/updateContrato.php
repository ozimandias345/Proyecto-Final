<?php
require '../../conexion/conexion.php';
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'representante') {
    header("Location: ../index.php");
    exit();
}

// Validar y sanitizar entradas
$idContrato = filter_input(INPUT_POST, 'id_contrato', FILTER_VALIDATE_INT);
$estado = filter_input(INPUT_POST, 'estado', FILTER_SANITIZE_STRING);

// Validar estado permitido
$estadosPermitidos = ['activo', 'finalizado', 'cancelado'];
if (!$idContrato || !in_array($estado, $estadosPermitidos)) {
    header("Location: ../contratos.php?error=" . urlencode("Datos inválidos."));
    exit();
}

try {
    // Actualizar el estado del contrato
    $stmt = $conn->prepare("
        UPDATE contrato
        SET estado = :estado
        WHERE numContrato = :idContrato
          AND empresa = :empresaId
    ");
    $stmt->bindParam(':estado', $estado, PDO::PARAM_STR);
    $stmt->bindParam(':idContrato', $idContrato, PDO::PARAM_INT);
    $stmt->bindParam(':empresaId', $_SESSION['empresa_id'], PDO::PARAM_INT);
    $stmt->execute();

    header("Location: ../contratos.php?success=Estado actualizado exitosamente.");
    exit();
} catch (PDOException $e) {
    header("Location: ../contratos.php?error=" . urlencode("Error al actualizar el estado: " . $e->getMessage()));
    exit();
}
