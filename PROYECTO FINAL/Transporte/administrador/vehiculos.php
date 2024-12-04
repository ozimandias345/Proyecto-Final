<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../conexion/conexion.php'; // Incluir conexión a la base de datos

// Inicializar variables para edición o creación
$editing = isset($_GET['edit']);
$vehiculoData = [
    'matricula' => '',
    'capacidad' => '',
    'disponibilidad' => 'disponible',
    'marca' => '',
    'modelo' => '',
    'conductor' => ''
];

if ($editing) {
    // Si estamos en modo edición, obtener los datos del vehículo
    $id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT matricula, capacidad, disponibilidad, marca, modelo, conductor FROM transporte WHERE numTransporte = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $vehiculoData = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Obtener las marcas disponibles
$stmtMarcas = $conn->prepare("SELECT codigo, nombre FROM marca");
$stmtMarcas->execute();
$marcas = $stmtMarcas->fetchAll(PDO::FETCH_ASSOC);

// Obtener los conductores disponibles
$stmtConductores = $conn->prepare("
    SELECT c.numConductor, CONCAT(c.nombre, ' ', c.primerApellido, ' ', c.segundoApellido) AS nombre
    FROM conductor c
    WHERE c.numConductor NOT IN (SELECT conductor FROM transporte WHERE conductor IS NOT NULL)
");
$stmtConductores->execute();
$conductores = $stmtConductores->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehículos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootswatch@4.5.2/dist/litera/bootstrap.min.css">
</head>
<body>
<div class="d-flex flex-column justify-content-center align-top gap-5">
    <header class="d-flex justify-content-between align-items-center container p-4 bg-body-secondary mt-2 rounded-4">
        <div class="d-flex gap-3 align-items-center">
            <a href="../admin_dashboard.php" class="text-decoration-none">
                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24">
                    <path fill="currentColor" d="m9.55 12l7.35 7.35q.375.375.363.875t-.388.875t-.875.375t-.875-.375l-7.7-7.675q-.3-.3-.45-.675t-.15-.75t.15-.75t.45-.675l7.7-7.7q.375-.375.888-.363t.887.388t.375.875t-.375.875z"/>
                </svg>
            </a>
            <span class="fs-2">Vehículos</span>
        </div>
    </header>

    <main class="d-flex gap-5 container-fluid">
        <div class="border border-start p-4 rounded-2 w-50 bg-white">
            <h4 class="mb-4"><?php echo $editing ? 'Editar Vehículo' : 'Agregar Vehículo'; ?></h4>

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

            <form action="servicios/<?php echo $editing ? 'updateVehiculo.php' : 'addVehiculo.php'; ?>" method="POST" class="d-flex flex-column gap-2">
                <?php if ($editing): ?>
                    <input type="hidden" name="id_vehiculo" value="<?php echo htmlspecialchars($id); ?>">
                <?php endif; ?>
                <div class="form-floating mb-3 w-100">
                    <input type="text" class="form-control" id="floatingMatricula" name="matricula" placeholder="Matrícula"
                           value="<?php echo htmlspecialchars($vehiculoData['matricula']); ?>" required>
                    <label for="floatingMatricula">Matrícula</label>
                </div>
                <div class="form-floating mb-3 w-100">
                    <input type="number" class="form-control" id="floatingCapacidad" name="capacidad" placeholder="Capacidad"
                           value="<?php echo htmlspecialchars($vehiculoData['capacidad']); ?>" required>
                    <label for="floatingCapacidad">Capacidad</label>
                </div>
                <div class="form-floating mb-3 w-100">
                    <select class="form-select" id="floatingDisponibilidad" name="disponibilidad" required>
                        <option value="disponible" <?php echo $vehiculoData['disponibilidad'] === 'disponible' ? 'selected' : ''; ?>>Disponible</option>
                        <option value="en_servicio" <?php echo $vehiculoData['disponibilidad'] === 'en_servicio' ? 'selected' : ''; ?>>En Servicio</option>
                        <option value="mantenimiento" <?php echo $vehiculoData['disponibilidad'] === 'mantenimiento' ? 'selected' : ''; ?>>Mantenimiento</option>
                        <option value="fuera_servicio" <?php echo $vehiculoData['disponibilidad'] === 'fuera_servicio' ? 'selected' : ''; ?>>Fuera de Servicio</option>
                    </select>
                    <label for="floatingDisponibilidad">Disponibilidad</label>
                </div>
                <div class="form-floating mb-3 w-100">
                    <select class="form-select" id="marcaSelect" name="marca" required>
                        <option value="">Seleccione una Marca</option>
                        <?php foreach ($marcas as $marca): ?>
                            <option value="<?php echo $marca['codigo']; ?>" <?php echo $vehiculoData['marca'] == $marca['codigo'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($marca['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <label for="marcaSelect">Marca</label>
                </div>
                <div class="form-floating mb-3 w-100">
                    <select class="form-select" id="modeloSelect" name="modelo" required>
                        <option value="">Seleccione un Modelo</option>
                    </select>
                    <label for="modeloSelect">Modelo</label>
                </div>
                <div class="form-floating mb-3 w-100">
                    <select class="form-select" id="conductorSelect" name="conductor">
                        <option value="">Seleccione un Conductor</option>
                        <?php foreach ($conductores as $conductor): ?>
                            <option value="<?php echo $conductor['numConductor']; ?>" <?php echo $vehiculoData['conductor'] == $conductor['numConductor'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($conductor['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <label for="conductorSelect">Conductor</label>
                </div>
                <button type="submit" class="btn btn-primary"><?php echo $editing ? 'Actualizar' : 'Guardar'; ?></button>
            </form>
        </div>
        <div class="w-100">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Matrícula</th>
                    <th>Capacidad</th>
                    <th>Disponibilidad</th>
                    <th>Marca</th>
                    <th>Modelo</th>
                    <th>Conductor</th>
                    <th>Acciones</th>
                </tr>
                </thead>
                <tbody>
                <?php
                // Obtener lista de vehículos
                $stmt = $conn->prepare("
                    SELECT t.numTransporte, t.matricula, t.capacidad, t.disponibilidad, m.nombre AS marca, mo.nombre AS modelo,
                           CONCAT(c.nombre, ' ', c.primerApellido, ' ', c.segundoApellido) AS conductor
                    FROM transporte t
                    INNER JOIN marca m ON t.marca = m.codigo
                    INNER JOIN modelo mo ON t.modelo = mo.codigo
                    LEFT JOIN conductor c ON t.conductor = c.numConductor
                    ORDER BY t.numTransporte DESC
                ");
                $stmt->execute();
                $vehiculos = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($vehiculos as $vehiculo): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($vehiculo['numTransporte']); ?></td>
                        <td><?php echo htmlspecialchars($vehiculo['matricula']); ?></td>
                        <td><?php echo htmlspecialchars($vehiculo['capacidad']); ?></td>
                        <td><?php echo htmlspecialchars($vehiculo['disponibilidad']); ?></td>
                        <td><?php echo htmlspecialchars($vehiculo['marca']); ?></td>
                        <td><?php echo htmlspecialchars($vehiculo['modelo']); ?></td>
                        <td><?php echo htmlspecialchars($vehiculo['conductor'] ?: 'Sin Asignar'); ?></td>
                        <td>
                            <a href="?edit=<?php echo $vehiculo['numTransporte']; ?>" class="btn btn-sm btn-warning">Editar</a>
                            <a href="servicios/deleteVehiculo.php?id=<?php echo $vehiculo['numTransporte']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Seguro que deseas eliminar este vehículo?');">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
<script>
    // Cargar modelos dinámicamente según la marca seleccionada
    document.getElementById('marcaSelect').addEventListener('change', function() {
        const marcaId = this.value;
        const modeloSelect = document.getElementById('modeloSelect');

        // Limpiar los modelos actuales
        modeloSelect.innerHTML = '<option value="">Seleccione un Modelo</option>';

        if (marcaId) {
            // Realizar la solicitud AJAX
            fetch('servicios/getModelos.php?marca=' + marcaId)
                .then(response => response.json())
                .then(data => {
                    data.forEach(modelo => {
                        const option = document.createElement('option');
                        option.value = modelo.codigo;
                        option.textContent = modelo.nombre;
                        modeloSelect.appendChild(option);
                    });
                })
                .catch(error => console.error('Error:', error));
        }
    });
</script>
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
