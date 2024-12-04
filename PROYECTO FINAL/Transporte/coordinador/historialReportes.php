<?php
session_start();

// Verificar si el usuario es coordinador
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'coordinador') {
    header("Location: ../login.php?error=" . urlencode("Acceso no autorizado."));
    exit();
}

require '../conexion/conexion.php';

try {
    // Obtener el ID del coordinador
    $stmtCoordinador = $conn->prepare("
        SELECT numCoordinador 
        FROM coordinador 
        WHERE id_usuario = :userId
        LIMIT 1
    ");
    $stmtCoordinador->bindParam(':userId', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmtCoordinador->execute();
    $numCoordinador = $stmtCoordinador->fetchColumn();

    if (!$numCoordinador) {
        throw new Exception("No se encontró un coordinador asociado a este usuario.");
    }

    // Obtener los contratos asociados a este coordinador
    $stmtContrato = $conn->prepare("
        SELECT c.empresa 
        FROM contrato c
        WHERE c.administrador = :coordinadorId
    ");
    $stmtContrato->bindParam(':coordinadorId', $numCoordinador, PDO::PARAM_INT);
    $stmtContrato->execute();
    $empresaId = $stmtContrato->fetchColumn();

    if (!$empresaId) {
        throw new Exception("Este coordinador no tiene contratos asociados a ninguna empresa.");
    }

    // Obtener los reportes de los conductores asociados a la empresa
    $stmtReportes = $conn->prepare("
        SELECT DISTINCT r.numreporte, r.descripcion, r.fecha, c.nombre AS conductorNombre
        FROM reporte_conductor r
        INNER JOIN conductor c ON r.numConductor = c.numConductor
        INNER JOIN transporte t ON t.conductor = c.numConductor
        INNER JOIN contrato ctr ON t.empresa = ctr.empresa
        WHERE ctr.empresa = :empresaId
    ");
    $stmtReportes->bindParam(':empresaId', $empresaId, PDO::PARAM_INT);
    $stmtReportes->execute();

    $reportes = $stmtReportes->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Reportes</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Historial de Reportes del Coordinador</h1>

    <?php if (count($reportes) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Descripción</th>
                    <th>Fecha</th>
                    <th>Conductor</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reportes as $reporte): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($reporte['descripcion']); ?></td>
                        <td><?php echo htmlspecialchars($reporte['fecha']); ?></td>
                        <td><?php echo htmlspecialchars($reporte['conductorNombre']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No hay reportes para esta empresa.</p>
    <?php endif; ?>
</body>
</html>
