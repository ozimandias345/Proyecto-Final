<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../conexion/conexion.php'; // Incluir conexión a la base de datos

// Verificar si se recibe el ID del contrato
if (!isset($_GET['contrato']) || !is_numeric($_GET['contrato'])) {
    header("Location: contratosEmpresa.php?error=" . urlencode("Contrato no especificado."));
    exit();
}

$contratoId = intval($_GET['contrato']);
$editing = isset($_GET['edit']);
$servicioData = [
    'nombreServicio' => '',
    'costoIndividual' => '',
    'numTransportes' => '',
    'descripcion' => '',
    'coordinador' => ''
];

if ($editing) {
    // Si estamos en modo edición, obtener los datos del servicio
    $id = $_GET['edit'];
    $stmt = $conn->prepare("
        SELECT s.nombreServicio, s.costoIndividual, s.numTransportes, s.descripcion, s.coordinador 
        FROM servicio s
        WHERE s.clave = :id
    ");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $servicioData = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Obtener lista de coordinadores
$stmtCoordinadores = $conn->prepare("SELECT numCoordinador, nombre FROM coordinador");
$stmtCoordinadores->execute();
$coordinadores = $stmtCoordinadores->fetchAll(PDO::FETCH_ASSOC);

// Obtener información de la empresa asociada al contrato
$stmtEmpresa = $conn->prepare("SELECT empresa FROM contrato WHERE numContrato = :contratoId");
$stmtEmpresa->bindParam(':contratoId', $contratoId, PDO::PARAM_INT);
$stmtEmpresa->execute();
$empresaId = $stmtEmpresa->fetchColumn();

if (!$empresaId) {
    header("Location: contratosEmpresa.php?error=" . urlencode("No se encontró una empresa asociada al contrato."));
    exit();
}


?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Servicios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootswatch@4.5.2/dist/litera/bootstrap.min.css">
</head>
<body>
<div class="d-flex flex-column justify-content-center align-top gap-5">
    <header class="d-flex justify-content-between align-items-center container p-4 bg-body-secondary mt-2 rounded-4">
        <div class="d-flex gap-3 align-items-center">
            <a href="contratosEmpresa.php?empresa=<?php echo htmlspecialchars($empresaId); ?>" class="text-decoration-none">
                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24">
                    <path fill="currentColor" d="m9.55 12l7.35 7.35q.375.375.363.875t-.388.875t-.875.375t-.875-.375l-7.7-7.675q-.3-.3-.45-.675t-.15-.75t-.15-.75t.45-.675l7.7-7.7q.375-.375.888-.363t.887.388t.375.875t-.375.875z"/>
                </svg>
            </a>

            <span class="fs-2">Servicios</span>
        </div>
    </header>

    <main class="d-flex gap-5 container-fluid">
        <div class="border border-start p-4 rounded-2 w-50 bg-white">
            <h4 class="mb-4"><?php echo $editing ? 'Editar Servicio' : 'Agregar Servicio'; ?></h4>

            <!-- Mensajes de confirmación o error -->
            <?php if (isset($_GET['success'])): ?>
                <div id="successMessage" class="alert alert-success">
                    <?php echo htmlspecialchars($_GET['success']); ?>
                </div>
            <?php elseif (isset($_GET['error'])): ?>
                <div id="errorMessage" class="alert alert-danger">
                    <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>

            <form action="servicios/<?php echo $editing ? 'updateServicioContrato.php' : 'addServicioContrato.php'; ?>?contrato=<?php echo htmlspecialchars($contratoId); ?>" method="POST" class="d-flex flex-column gap-2">
                <?php if ($editing): ?>
                    <!-- Campo oculto para identificar el servicio en modo edición -->
                    <input type="hidden" name="id_servicio" value="<?php echo htmlspecialchars($id); ?>">
                <?php endif; ?>

                <!-- Campo oculto para el contrato asociado -->
                <input type="hidden" name="contrato" value="<?php echo htmlspecialchars($contratoId); ?>">

                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="floatingNombreServicio" name="nombre_servicio" placeholder="Nombre del servicio"
                           value="<?php echo htmlspecialchars($servicioData['nombreServicio'] ?? ''); ?>" required>
                    <label for="floatingNombreServicio">Nombre del Servicio</label>
                </div>
                <div class="form-floating mb-3">
                    <input type="number" step="0.01" class="form-control" id="floatingCostoIndividual" name="costo_individual" placeholder="Costo Individual"
                           value="<?php echo htmlspecialchars($servicioData['costoIndividual'] ?? ''); ?>" required>
                    <label for="floatingCostoIndividual">Costo Individual</label>
                </div>
                <div class="form-floating mb-3">
                    <input type="number" class="form-control" id="floatingNumTransportes" name="num_transportes" placeholder="Número de Transportes"
                           value="<?php echo htmlspecialchars($servicioData['numTransportes'] ?? ''); ?>" required>
                    <label for="floatingNumTransportes">Número de Transportes</label>
                </div>
                <div class="form-floating mb-3">
                    <textarea class="form-control" id="floatingDescripcion" name="descripcion" placeholder="Descripción del servicio" required><?php echo htmlspecialchars($servicioData['descripcion'] ?? ''); ?></textarea>
                    <label for="floatingDescripcion">Descripción</label>
                </div>
                <div class="form-floating mb-3">
                    <select class="form-select" id="floatingCoordinador" name="coordinador" required>
                        <option value="">Seleccione un coordinador</option>
                        <?php foreach ($coordinadores as $coordinador): ?>
                            <option value="<?php echo $coordinador['numCoordinador']; ?>" <?php echo (isset($servicioData['coordinador']) && $servicioData['coordinador'] == $coordinador['numCoordinador']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($coordinador['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <label for="floatingCoordinador">Coordinador</label>
                </div>
                <button type="submit" class="btn btn-primary"><?php echo $editing ? 'Actualizar Servicio' : 'Agregar Servicio'; ?></button>
            </form>


        </div>
        <div class="w-100">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>Costo</th>
                    <th>Transportes</th>
                    <th>Descripción</th>
                    <th>Coordinador</th>
                    <th>Acciones</th>
                </tr>
                </thead>
                <tbody>
                <?php
                // Obtener lista de servicios filtrados por contrato
                $stmt = $conn->prepare("
                    SELECT s.clave, s.nombreServicio, s.costoIndividual, s.numTransportes, s.descripcion, c.nombre AS coordinador
                    FROM servicio s
                    LEFT JOIN coordinador c ON s.coordinador = c.numCoordinador
                    WHERE s.contrato = :contratoId
                    ORDER BY s.clave DESC
                ");
                $stmt->bindParam(':contratoId', $contratoId, PDO::PARAM_INT);
                $stmt->execute();
                $servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($servicios as $servicio): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($servicio['clave']); ?></td>
                        <td><?php echo htmlspecialchars($servicio['nombreServicio']); ?></td>
                        <td><?php echo htmlspecialchars($servicio['costoIndividual']); ?></td>
                        <td><?php echo htmlspecialchars($servicio['numTransportes']); ?></td>
                        <td><?php echo htmlspecialchars($servicio['descripcion']); ?></td>
                        <td><?php echo htmlspecialchars($servicio['coordinador'] ?? 'Sin asignar'); ?></td>
                        <td>
                            <a href="?contrato=<?php echo $contratoId; ?>&edit=<?php echo $servicio['clave']; ?>" class="btn btn-sm btn-warning">Editar</a>
                            <a href="servicios/deleteServicioContrato.php?id_servicio=<?php echo $servicio['clave']; ?>&contrato=<?php echo $contratoId; ?>"
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('¿Seguro que deseas eliminar este servicio?');">
                                Eliminar
                            </a>

                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
<script>
    // Desaparecer mensajes después de 3 segundos
    setTimeout(() => {
        const successMessage = document.getElementById('successMessage');
        const errorMessage = document.getElementById('errorMessage');
        if (successMessage) successMessage.style.display = 'none';
        if (errorMessage) errorMessage.style.display = 'none';
    }, 3000);
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
