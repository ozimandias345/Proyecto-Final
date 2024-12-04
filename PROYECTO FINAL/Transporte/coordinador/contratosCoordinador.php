<?php
session_start(); // Asegurarse de que la sesión está activa

require '../conexion/conexion.php'; // Conexión a la base de datos

// Verificar que se recibe el ID del coordinador
if (!isset($_GET['coordinador']) || !is_numeric($_GET['coordinador'])) {
    header("Location: ../login.php?error=" . urlencode("Acceso no autorizado."));
    exit();
}

$coordinadorId = intval($_GET['coordinador']); // Sanitizar el ID del coordinador

try {
    // Consultar los contratos y servicios relacionados con el coordinador
    $stmt = $conn->prepare("
        SELECT 
            c.numContrato AS id_contrato,
            c.fechaInicio AS contrato_inicio,
            c.fechaFin AS contrato_fin,
            c.estado AS contrato_estado,
            e.nombre AS empresa,
            s.clave AS id_servicio,
            s.nombreServicio AS servicio_nombre,
            s.costoIndividual AS servicio_costo,
            s.descripcion AS servicio_descripcion,
            t.numTransporte AS transporte_id,
            t.matricula AS transporte_matricula
        FROM contrato c
        INNER JOIN empresa e ON c.empresa = e.numEmpresa
        INNER JOIN servicio s ON c.numContrato = s.contrato
        LEFT JOIN transporte t ON s.transporte_id = t.numTransporte
        WHERE s.coordinador = :coordinadorId
        ORDER BY c.numContrato DESC, s.clave DESC
    ");
    $stmt->bindParam(':coordinadorId', $coordinadorId, PDO::PARAM_INT);
    $stmt->execute();
    $contratosServicios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Consultar transportes disponibles
    $stmtTransportes = $conn->prepare("
        SELECT numTransporte, matricula 
        FROM transporte 
        WHERE disponibilidad = 'disponible'
    ");
    $stmtTransportes->execute();
    $transportesDisponibles = $stmtTransportes->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}

// Asignar o desasignar transporte
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $servicioId = intval($_POST['servicio_id']);

    if (isset($_POST['transporte_id'])) { // Asignar o actualizar transporte
        $transporteId = intval($_POST['transporte_id']);

        try {
            $conn->beginTransaction();

            // Actualizar asignación del transporte
            $stmtAsignar = $conn->prepare("
                UPDATE servicio 
                SET transporte_id = :transporteId 
                WHERE clave = :servicioId
            ");
            $stmtAsignar->bindParam(':transporteId', $transporteId, PDO::PARAM_INT);
            $stmtAsignar->bindParam(':servicioId', $servicioId, PDO::PARAM_INT);
            $stmtAsignar->execute();

            // Marcar transporte como en servicio
            $stmtActualizarTransporte = $conn->prepare("
                UPDATE transporte 
                SET disponibilidad = 'en_servicio' 
                WHERE numTransporte = :transporteId
            ");
            $stmtActualizarTransporte->bindParam(':transporteId', $transporteId, PDO::PARAM_INT);
            $stmtActualizarTransporte->execute();

            // Liberar transporte anterior (si lo hay)
            if (isset($_POST['transporte_actual']) && is_numeric($_POST['transporte_actual'])) {
                $transporteActual = intval($_POST['transporte_actual']);
                $stmtLiberar = $conn->prepare("
                    UPDATE transporte 
                    SET disponibilidad = 'disponible' 
                    WHERE numTransporte = :transporteActual
                ");
                $stmtLiberar->bindParam(':transporteActual', $transporteActual, PDO::PARAM_INT);
                $stmtLiberar->execute();
            }

            // Obtener el contrato vinculado al servicio
            $stmtContrato = $conn->prepare("
                SELECT contrato 
                FROM servicio 
                WHERE clave = :servicioId
            ");
            $stmtContrato->bindParam(':servicioId', $servicioId, PDO::PARAM_INT);
            $stmtContrato->execute();
            $contratoId = $stmtContrato->fetchColumn();

            if (!$contratoId) {
                throw new Exception("No se encontró un contrato asociado al servicio.");
            }

            // Obtener la empresa asociada al contrato
            $stmtEmpresa = $conn->prepare("
                SELECT empresa 
                FROM contrato 
                WHERE numContrato = :contratoId
            ");
            $stmtEmpresa->bindParam(':contratoId', $contratoId, PDO::PARAM_INT);
            $stmtEmpresa->execute();
            $empresaId = $stmtEmpresa->fetchColumn();

            if (!$empresaId) {
                throw new Exception("No se encontró una empresa asociada al contrato.");
            }

            // Obtener los empleados de la empresa
            $stmtEmpleados = $conn->prepare("
                SELECT numEmpleado 
                FROM empleado 
                WHERE empresa = :empresaId
            ");
            $stmtEmpleados->bindParam(':empresaId', $empresaId, PDO::PARAM_INT);
            $stmtEmpleados->execute();
            $empleados = $stmtEmpleados->fetchAll(PDO::FETCH_COLUMN);

            // Vincular empleados al transporte en la tabla empleado_transporte
            $stmtInsertEmpleadoTransporte = $conn->prepare("
                INSERT IGNORE INTO empleado_transporte (empleado, transporte)
                VALUES (:empleado, :transporte)
            ");
            foreach ($empleados as $empleadoId) {
                $stmtInsertEmpleadoTransporte->bindParam(':empleado', $empleadoId, PDO::PARAM_INT);
                $stmtInsertEmpleadoTransporte->bindParam(':transporte', $transporteId, PDO::PARAM_INT);
                $stmtInsertEmpleadoTransporte->execute();
            }

            $conn->commit();

            header("Location: contratosCoordinador.php?coordinador=$coordinadorId&success=" . urlencode("Transporte actualizado y empleados vinculados correctamente."));
            exit();
        } catch (PDOException $e) {
            $conn->rollBack();
            die("Error al asignar transporte: " . $e->getMessage());
        }
    } elseif (isset($_POST['desasignar'])) { // Desasignar transporte
        try {
            $conn->beginTransaction();

            // Obtener transporte asignado al servicio
            $stmtTransporteActual = $conn->prepare("
                SELECT transporte_id 
                FROM servicio 
                WHERE clave = :servicioId
            ");
            $stmtTransporteActual->bindParam(':servicioId', $servicioId, PDO::PARAM_INT);
            $stmtTransporteActual->execute();
            $transporteActual = $stmtTransporteActual->fetchColumn();

            if ($transporteActual) {
                // Liberar transporte
                $stmtLiberar = $conn->prepare("
                    UPDATE transporte 
                    SET disponibilidad = 'disponible' 
                    WHERE numTransporte = :transporteActual
                ");
                $stmtLiberar->bindParam(':transporteActual', $transporteActual, PDO::PARAM_INT);
                $stmtLiberar->execute();

                // Eliminar asignación en el servicio
                $stmtDesasignar = $conn->prepare("
                    UPDATE servicio 
                    SET transporte_id = NULL 
                    WHERE clave = :servicioId
                ");
                $stmtDesasignar->bindParam(':servicioId', $servicioId, PDO::PARAM_INT);
                $stmtDesasignar->execute();

                // Eliminar empleados asociados al transporte
                $stmtEliminarEmpleados = $conn->prepare("
                    DELETE FROM empleado_transporte 
                    WHERE transporte = :transporteActual
                ");
                $stmtEliminarEmpleados->bindParam(':transporteActual', $transporteActual, PDO::PARAM_INT);
                $stmtEliminarEmpleados->execute();
            }

            $conn->commit();

            header("Location: contratosCoordinador.php?coordinador=$coordinadorId&success=" . urlencode("Transporte desasignado correctamente."));
            exit();
        } catch (PDOException $e) {
            $conn->rollBack();
            die("Error al desasignar transporte: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contratos y Servicios</title>
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
            <h3 class="">Contratos y Servicios Relacionados</h3>
        </div>
    </header>
    <main class="container">
        <?php if (!empty($contratosServicios)): ?>
            <table class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th># Contrato</th>
                    <th>Inicio</th>
                    <th>Fin</th>
                    <th>Estado</th>
                    <th>Empresa</th>
                    <th>Nombre del Servicio</th>
                    <th>Descripción</th>
                    <th>Transporte Asignado</th>
                    <th>Opciones</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($contratosServicios as $registro): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($registro['id_contrato']); ?></td>
                        <td><?php echo htmlspecialchars($registro['contrato_inicio']); ?></td>
                        <td><?php echo htmlspecialchars($registro['contrato_fin']); ?></td>
                        <td><?php echo htmlspecialchars($registro['contrato_estado']); ?></td>
                        <td><?php echo htmlspecialchars($registro['empresa']); ?></td>
                        <td><?php echo htmlspecialchars($registro['servicio_nombre']); ?></td>
                        <td><?php echo htmlspecialchars($registro['servicio_descripcion']); ?></td>
                        <td><?php echo $registro['transporte_matricula'] ? htmlspecialchars($registro['transporte_matricula']) : "Sin asignar"; ?></td>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="servicio_id" value="<?php echo htmlspecialchars($registro['id_servicio']); ?>">
                                <?php if ($registro['transporte_id']): ?>
                                    <input type="hidden" name="transporte_actual" value="<?php echo htmlspecialchars($registro['transporte_id']); ?>">

                                <?php endif; ?>
                                <select name="transporte_id" class="form-select mb-2" required>
                                    <option value="">Seleccione un transporte</option>
                                    <?php foreach ($transportesDisponibles as $transporte): ?>
                                        <option value="<?php echo $transporte['numTransporte']; ?>">
                                            <?php echo htmlspecialchars($transporte['matricula']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn btn-primary btn-sm">Asignar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-warning">No hay contratos ni servicios relacionados para este coordinador.</div>
        <?php endif; ?>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
