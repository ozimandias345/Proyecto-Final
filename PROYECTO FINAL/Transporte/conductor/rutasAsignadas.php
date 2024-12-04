<?php
session_start();

// Verificar si el usuario es conductor
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'conductor') {
    header("Location: ../login.php?error=" . urlencode("Acceso no autorizado."));
    exit();
}

require '../conexion/conexion.php';

try {
    // Obtener el numConductor basado en el user_id del conductor
    $stmtConductor = $conn->prepare("
        SELECT numConductor 
        FROM conductor 
        WHERE id_usuario = :userId
        LIMIT 1
    ");
    $stmtConductor->bindParam(':userId', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmtConductor->execute();
    $numConductor = $stmtConductor->fetchColumn();

    if (!$numConductor) {
        throw new Exception("No se encontró un conductor asociado a este usuario.");
    }

    // Consultar rutas, pasajeros y vehículos asignados al conductor usando empleado_transporte
    $stmt = $conn->prepare("
        SELECT 
            r.numRuta AS ruta_id,
            r.nombreRuta AS ruta_nombre,
            r.distanciaKm AS ruta_distancia,
            r.costoKm AS ruta_costo,
            t.matricula AS vehiculo_matricula,
            t.capacidad AS vehiculo_capacidad,
            e.numEmpleado AS empleado_id,
            CONCAT(e.nombre, ' ', e.primerApellido, ' ', e.segundoApellido) AS empleado_nombre
        FROM transporte t
        INNER JOIN rutas r ON t.numTransporte = r.numRuta
        LEFT JOIN empleado_transporte et ON et.transporte = t.numTransporte
        LEFT JOIN empleado e ON et.empleado = e.numEmpleado
        WHERE t.conductor = :numConductor
        ORDER BY r.numRuta, e.nombre
    ");
    $stmt->bindParam(':numConductor', $numConductor, PDO::PARAM_INT);
    $stmt->execute();
    $rutasData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Agrupar empleados por rutas
    $rutas = [];
    foreach ($rutasData as $row) {
        $rutaId = $row['ruta_id'];
        if (!isset($rutas[$rutaId])) {
            $rutas[$rutaId] = [
                'ruta_nombre' => $row['ruta_nombre'],
                'ruta_distancia' => $row['ruta_distancia'],
                'ruta_costo' => $row['ruta_costo'],
                'vehiculo_matricula' => $row['vehiculo_matricula'],
                'vehiculo_capacidad' => $row['vehiculo_capacidad'],
                'empleados' => []
            ];
        }

        // Agregar empleados a la ruta
        if ($row['empleado_id']) {
            $rutas[$rutaId]['empleados'][] = $row['empleado_nombre'];
        }
    }
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rutas Asignadas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="d-flex flex-column justify-content-center align-top gap-5">

    <header class="d-flex justify-content-between align-items-center container p-4 bg-body-secondary mt-2 rounded-4">
        <div class="d-flex gap-3 align-items-center">
            <a href="../admin_dashboard.php" class="text-decoration-none">
                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24">
                    <path fill="currentColor" d="m9.55 12l7.35 7.35q.375.375.363.875t-.388.875t-.875.375t-.875-.375l-7.7-7.675q-.3-.3-.45-.675t-.15-.75t-.15-.75t.45-.675l7.7-7.7q.375-.375.888-.363t.887.388t.375.875t-.375.875z"/>
                </svg>
            </a>

            <span class="h3">Rutas</span>
        </div>
    </header>

    <main class="container">
        <?php if (!empty($rutas)): ?>
            <?php foreach ($rutas as $rutaId => $ruta): ?>
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5><?php echo htmlspecialchars($ruta['ruta_nombre']); ?> (Ruta ID: <?php echo $rutaId; ?>)</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Distancia:</strong> <?php echo htmlspecialchars($ruta['ruta_distancia']); ?> Km</p>
                        <p><strong>Costo por Km:</strong> <?php echo htmlspecialchars($ruta['ruta_costo']); ?></p>
                        <p><strong>Vehículo Matrícula:</strong> <?php echo htmlspecialchars($ruta['vehiculo_matricula']); ?></p>
                        <p><strong>Capacidad del Vehículo:</strong> <?php echo htmlspecialchars($ruta['vehiculo_capacidad']); ?></p>
                        <p><strong>Pasajeros:</strong></p>
                        <?php if (!empty($ruta['empleados'])): ?>
                            <ul>
                                <?php foreach ($ruta['empleados'] as $empleado): ?>
                                    <li><?php echo htmlspecialchars($empleado); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-muted">No hay pasajeros asignados a esta ruta.</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="alert alert-warning">No tienes rutas asignadas actualmente.</div>
        <?php endif; ?>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
