<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../conexion/conexion.php'; // Incluir conexión a la base de datos

$empresaId = intval($_GET['empresa']);

try {
    // Consultar los contratos asociados a la empresa
    $stmt = $conn->prepare("
        SELECT numContrato, fechaInicio, fechaFin, cantEmpleados, estado
        FROM contrato
        WHERE empresa = :empresaId
        ORDER BY numContrato DESC
    ");
    $stmt->bindParam(':empresaId', $empresaId, PDO::PARAM_INT);
    $stmt->execute();
    $contratos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Consultar el nombre de la empresa
    $stmtEmpresa = $conn->prepare("SELECT nombre FROM empresa WHERE numEmpresa = :empresaId");
    $stmtEmpresa->bindParam(':empresaId', $empresaId, PDO::PARAM_INT);
    $stmtEmpresa->execute();
    $empresa = $stmtEmpresa->fetch(PDO::FETCH_ASSOC);

    if (!$empresa) {
        throw new Exception("La empresa no existe.");
    }
} catch (Exception $e) {
    $contratos = [];
    $error = "Error al obtener los contratos: " . $e->getMessage();
}

// Procesar la actualización del estado del contrato
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contrato_id'], $_POST['nuevo_estado'])) {
    $contratoId = intval($_POST['contrato_id']);
    $nuevoEstado = $_POST['nuevo_estado'];

    // Validar que el estado sea válido
    $estadosValidos = ['activo', 'finalizado', 'cancelado', 'inactivo'];
    if (!in_array($nuevoEstado, $estadosValidos, true)) {
        header("Location: contratosEmpresa.php?empresa=$empresaId&error=" . urlencode("Estado no válido."));
        exit();
    }

    try {
        $stmtUpdate = $conn->prepare("
            UPDATE contrato
            SET estado = :nuevoEstado
            WHERE numContrato = :contratoId AND empresa = :empresaId
        ");
        $stmtUpdate->bindParam(':nuevoEstado', $nuevoEstado, PDO::PARAM_STR);
        $stmtUpdate->bindParam(':contratoId', $contratoId, PDO::PARAM_INT);
        $stmtUpdate->bindParam(':empresaId', $empresaId, PDO::PARAM_INT);
        $stmtUpdate->execute();

        header("Location: contratosEmpresa.php?empresa=$empresaId&success=Estado actualizado exitosamente.");
        exit();
    } catch (PDOException $e) {
        header("Location: contratosEmpresa.php?empresa=$empresaId&error=" . urlencode("Error al actualizar el estado: " . $e->getMessage()));
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contratos de Empresa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootswatch@4.5.2/dist/litera/bootstrap.min.css">
</head>
<body>
<div class="d-flex flex-column justify-content-center align-top container gap-5">
    <header class="d-flex justify-content-between align-items-center p-4 bg-body-secondary mt-2 rounded-4">
        <div class="d-flex gap-3 align-items-center">
            <a href="empresas.php" class="btn btn-secondary">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M20 11H7.83l5.59-5.59L12 4l-8 8l8 8l1.41-1.41L7.83 13H20z"/></svg>
            </a>
            <span class="fs-2">Contratos de: <?php echo htmlspecialchars($empresa['nombre']); ?></span>
        </div>
    </header>

    <main class="container">
        <div class="table-responsive">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
            <?php endif; ?>
            <?php if (empty($contratos)): ?>
                <div class="alert alert-warning">No hay contratos asociados a esta empresa.</div>
            <?php else: ?>
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                    <tr>
                        <th># Contrato</th>
                        <th>Fecha Inicio</th>
                        <th>Fecha Fin</th>
                        <th>Cantidad Empleados</th>
                        <th>Estado</th>
                        <th>Actualizar Estado</th>
                        <th>Asignar Servicios</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($contratos as $contrato): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($contrato['numContrato']); ?></td>
                            <td><?php echo htmlspecialchars($contrato['fechaInicio']); ?></td>
                            <td><?php echo htmlspecialchars($contrato['fechaFin']); ?></td>
                            <td><?php echo htmlspecialchars($contrato['cantEmpleados']); ?></td>
                            <td><?php echo htmlspecialchars($contrato['estado']); ?></td>
                            <td>
                                <form action="contratosEmpresa.php?empresa=<?php echo $empresaId; ?>" method="POST" class="d-flex gap-2">
                                    <input type="hidden" name="contrato_id" value="<?php echo $contrato['numContrato']; ?>">
                                    <select name="nuevo_estado" class="form-select" required>
                                        <option value="activo" <?php echo $contrato['estado'] === 'activo' ? 'selected' : ''; ?>>Activo</option>
                                        <option value="finalizado" <?php echo $contrato['estado'] === 'finalizado' ? 'selected' : ''; ?>>Finalizado</option>
                                        <option value="cancelado" <?php echo $contrato['estado'] === 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                                        <option value="inactivo" <?php echo $contrato['estado'] === 'inactivo' ? 'selected' : ''; ?>>Inactivo</option>
                                    </select>
                                    <button type="submit" class="btn btn-primary btn-sm">Cambiar</button>
                                </form>
                            </td>
                            <td>
                                <a href="asignarServicio.php?contrato=<?php echo $contrato['numContrato']; ?>" class="btn btn-success btn-sm">Asignar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
