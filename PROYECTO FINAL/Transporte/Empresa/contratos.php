<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../conexion/conexion.php'; // Incluir conexión a la base de datos

session_start();

// Verificar si el usuario está autenticado y es representante
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'representante') {
    header("Location: ../index.php");
    exit();
}

// Obtener el ID de la empresa desde la sesión
$empresaId = $_SESSION['empresa_id'];

// Inicializar variables para edición o creación
$editing = isset($_GET['edit']);
$contratoData = [
    'fechaInicio' => '',
    'fechaFin' => '',
    'cantEmpleados' => '',
    'estado' => 'activo' // Por defecto, el estado es "activo"
];

if ($editing) {
    // Si estamos en modo edición, obtener los datos del contrato
    $id = $_GET['edit'];
    try {
        $stmt = $conn->prepare("
            SELECT fechaInicio, fechaFin, cantEmpleados, estado
            FROM contrato
            WHERE numContrato = :id AND empresa = :empresaId
        ");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':empresaId', $empresaId, PDO::PARAM_INT);
        $stmt->execute();
        $contratoData = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$contratoData) {
            throw new Exception("El contrato no existe o no está asociado con esta empresa.");
        }
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contratos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootswatch@4.5.2/dist/litera/bootstrap.min.css">
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
            <span class="fs-2">Contratos</span>
        </div>
    </header>

    <main class="d-flex gap-5 container-fluid">
        <div class="border border-start p-4 rounded-2 w-50 bg-white">
            <h4 class="mb-4"><?php echo $editing ? 'Editar Contrato' : 'Agregar Contrato'; ?></h4>

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

            <form action="servicios/<?php echo $editing ? 'updateContrato.php' : 'addContrato.php'; ?>" method="POST" class="d-flex flex-column gap-2">
                <?php if ($editing): ?>
                    <input type="hidden" name="id_contrato" value="<?php echo htmlspecialchars($id); ?>">
                <?php endif; ?>
                <div class="d-flex flex-row gap-2">
                    <div class="form-floating mb-3 w-100">
                        <input type="date" class="form-control" id="floatingFechaInicio" name="fechaInicio"
                               value="<?php echo htmlspecialchars($contratoData['fechaInicio']); ?>" required>
                        <label for="floatingFechaInicio">Fecha de Inicio</label>
                    </div>
                    <div class="form-floating mb-3 w-100">
                        <input type="date" class="form-control" id="floatingFechaFin" name="fechaFin"
                               value="<?php echo htmlspecialchars($contratoData['fechaFin']); ?>" required>
                        <label for="floatingFechaFin">Fecha de Fin</label>
                    </div>
                </div>
                <div class="form-floating mb-3">
                    <input type="number" class="form-control" id="floatingCantEmpleados" name="cantEmpleados" placeholder="Cantidad de empleados"
                           value="<?php echo htmlspecialchars($contratoData['cantEmpleados']); ?>" required>
                    <label for="floatingCantEmpleados">Cantidad de Empleados</label>
                </div>
                <button type="submit" class="btn btn-primary"><?php echo $editing ? 'Actualizar' : 'Guardar'; ?></button>
            </form>
        </div>
        <div class="w-100">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Fecha Inicio</th>
                    <th>Fecha Fin</th>
                    <th>Cant. Empleados</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
                </thead>
                <tbody>
                <?php
                try {
                    // Obtener lista de contratos asociados a la empresa
                    $stmt = $conn->prepare("
        SELECT numContrato, fechaInicio, fechaFin, cantEmpleados, estado
        FROM contrato
        WHERE empresa = :empresaId
        ORDER BY numContrato DESC
    ");
                    $stmt->bindParam(':empresaId', $empresaId, PDO::PARAM_INT);
                    $stmt->execute();
                    $contratos = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($contratos as $contrato): ?>
                        <tr>
                            <td><?php echo htmlspecialchars(isset($contrato['numContrato']) ? $contrato['numContrato'] : 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars(isset($contrato['fechaInicio']) ? date('Y-m-d', strtotime($contrato['fechaInicio'])) : 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars(isset($contrato['fechaFin']) ? date('Y-m-d', strtotime($contrato['fechaFin'])) : 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars(isset($contrato['cantEmpleados']) ? $contrato['cantEmpleados'] : '0'); ?></td>
                            <td><?php echo htmlspecialchars(isset($contrato['estado']) ? $contrato['estado'] : 'N/A'); ?></td>
                            <td>
                                <a href="?edit=<?php echo htmlspecialchars(isset($contrato['numContrato']) ? $contrato['numContrato'] : ''); ?>" class="btn btn-sm btn-warning">Editar</a>
                                <a href="servicios/deleteContrato.php?id=<?php echo htmlspecialchars(isset($contrato['numContrato']) ? $contrato['numContrato'] : ''); ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Seguro que deseas eliminar este contrato?');">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach;
                } catch (Exception $e) {
                    echo "<tr><td colspan='6' class='text-center'>Error: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                }
                ?>
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
