<?php
// Mostrar errores para depuración (en desarrollo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../../conexion/conexion.php'; // Conexión a la base de datos

// Validar que se recibe el ID del servicio y el contrato
if (!isset($_GET['id_servicio']) || !is_numeric($_GET['id_servicio']) ||
    !isset($_GET['contrato']) || !is_numeric($_GET['contrato'])) {
    header("Location: ../asignarServicio.php?error=" . urlencode("Datos insuficientes para eliminar el servicio."));
    exit();
}

$idServicio = intval($_GET['id_servicio']);
$contratoId = intval($_GET['contrato']);

try {
    // Preparar la consulta para eliminar el servicio
    $stmt = $conn->prepare("DELETE FROM servicio WHERE clave = :idServicio");
    $stmt->bindParam(':idServicio', $idServicio, PDO::PARAM_INT);
    $stmt->execute();

    // Redirigir con un mensaje de éxito
    header("Location: ../asignarServicio.php?contrato=$contratoId&success=" . urlencode("Servicio eliminado correctamente."));
    exit();
} catch (PDOException $e) {
    // Redirigir con un mensaje de error
    header("Location: ../asignarServicio.php?contrato=$contratoId&error=" . urlencode("Error al eliminar el servicio: " . $e->getMessage()));
    exit();
}
