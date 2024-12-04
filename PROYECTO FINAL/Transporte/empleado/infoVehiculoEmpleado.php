<?php
session_start(); // Asegurarse de que la sesión está activa

require '../conexion/conexion.php'; // Conexión a la base de datos

// Verificar que el usuario es un empleado
if ($_SESSION['user_type'] !== 'empleado') {
    header("Location: ../login.php?error=" . urlencode("Acceso no autorizado."));
    exit();
}

// Verificar que se recibe el ID del empleado por URL
if (!isset($_GET['empleado']) || !is_numeric($_GET['empleado'])) {
    die("Acceso no autorizado: Falta el ID del empleado.");
}

$empleadoId = intval($_GET['empleado']); // Obtener el ID del empleado desde la URL

try {
    // Obtener el contrato de la empresa del empleado
    $stmtContrato = $conn->prepare("
        SELECT c.numContrato 
        FROM contrato c
        INNER JOIN empresa e ON c.empresa = e.numEmpresa
        INNER JOIN empleado emp ON e.numEmpresa = emp.empresa
        WHERE emp.id_usuario = :empleadoId
        LIMIT 1
    ");
    $stmtContrato->bindParam(':empleadoId', $empleadoId, PDO::PARAM_INT);
    $stmtContrato->execute();
    $contratoId = $stmtContrato->fetchColumn();

    if (!$contratoId) {
        throw new Exception("No se encontró un contrato asociado a tu empresa.");
    }

    // Obtener información del conductor, vehículo, ruta y paradas
    $stmtInfo = $conn->prepare("
        SELECT 
            c.nombre AS conductor_nombre,
            t.matricula AS vehiculo_matricula,
            mo.nombre AS vehiculo_modelo,
            r.nombreRuta AS ruta_nombre,
            r.distanciaKm AS ruta_distancia,
            p.nombreParada AS parada_nombre,
            pr.horaInicio AS parada_hora_inicio,
            pr.horaFinal AS parada_hora_final
        FROM servicio s
        INNER JOIN transporte t ON s.transporte_id = t.numTransporte
        INNER JOIN conductor c ON t.conductor = c.numConductor
        INNER JOIN modelo mo ON t.modelo = mo.codigo
        INNER JOIN rutas r ON t.numTransporte = r.conductor
        INNER JOIN paradas_rutas pr ON r.numRuta = pr.numRuta
        INNER JOIN parada p ON pr.numParada = p.numParada
        WHERE s.contrato = :contratoId
        ORDER BY r.numRuta, pr.horaInicio
    ");
    $stmtInfo->bindParam(':contratoId', $contratoId, PDO::PARAM_INT);
    $stmtInfo->execute();
    $informacion = $stmtInfo->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Información del Transporte</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="d-flex flex-column justify-content-center align-top gap-5">

    <header class="d-flex justify-content-between align-items-center container p-4 bg-body-secondary mt-2 rounded-4">
        <div class="d-flex gap-3 align-items-center">
            <a href="../admin_dashboard.php">
                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24">
                    <path fill="currentColor" d="m9.55 12l7.35 7.35q.375.375.363.875t-.388.875t-.875.375t-.875-.375l-7.7-7.675q-.3-.3-.45-.675t-.15-.75t-.15-.75t.45-.675l7.7-7.7q.375-.375.888-.363t.887.388t.375.875t-.375.875z"/>
                </svg>
            </a>

            <h3 class="">Información del Transporte</h3>
        </div>
    </header>


    <main class="container">
        <?php if (!empty($informacion)): ?>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th>Conductor</th>
                        <th>Vehículo (Matrícula)</th>
                        <th>Modelo del Vehículo</th>
                        <th>Ruta</th>
                        <th>Distancia (Km)</th>
                        <th>Parada</th>
                        <th>Hora de Inicio</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($informacion as $dato): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($dato['conductor_nombre']); ?></td>
                            <td><?php echo htmlspecialchars($dato['vehiculo_matricula']); ?></td>
                            <td><?php echo htmlspecialchars($dato['vehiculo_modelo']); ?></td>
                            <td><?php echo htmlspecialchars($dato['ruta_nombre']); ?></td>
                            <td><?php echo htmlspecialchars($dato['ruta_distancia']); ?></td>
                            <td><?php echo htmlspecialchars($dato['parada_nombre']); ?></td>
                            <td><?php echo htmlspecialchars($dato['parada_hora_inicio']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">No hay información disponible para tu empresa.</div>
        <?php endif; ?>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
