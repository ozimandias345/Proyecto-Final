<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../conexion/conexion.php'; // Incluir conexión a la base de datos

// Inicializar variables para edición o creación
$editing = isset($_GET['edit']);
$conductorData = [
    'nombre_usuario' => '',
    'password' => '',
    'nombre' => '',
    'apellido1' => '',
    'apellido2' => ''
];

if ($editing) {
    // Si estamos en modo edición, obtener los datos del conductor
    $id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT u.username AS nombre_usuario, u.password, c.nombre, c.primerApellido AS apellido1, c.segundoApellido AS apellido2 
                            FROM usuario u 
                            INNER JOIN conductor c ON u.id = c.id_usuario 
                            WHERE c.numConductor = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $conductorData = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conductores</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootswatch@4.5.2/dist/litera/bootstrap.min.css">
</head>
<body>
<div class="d-flex flex-column justify-content-center align-top  gap-5">
    <header class="d-flex justify-content-between align-items-center container p-4 bg-body-secondary mt-2 rounded-4">
        <div class="d-flex gap-3 align-items-center">
            <a href="../admin_dashboard.php" class="text-decoration-none">
                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24">
                    <path fill="currentColor" d="m9.55 12l7.35 7.35q.375.375.363.875t-.388.875t-.875.375t-.875-.375l-7.7-7.675q-.3-.3-.45-.675t-.15-.75t.15-.75t.45-.675l7.7-7.7q.375-.375.888-.363t.887.388t.375.875t-.375.875z"/>
                </svg>
            </a>
            <span class="fs-2">Conductores</span>
        </div>
    </header>

    <main class="d-flex gap-5 container-fluid">
        <div class="border border-start p-4 rounded-2 w-50 bg-white">
            <h4 class="mb-4"><?php echo $editing ? 'Editar Conductor' : 'Agregar Conductor'; ?></h4>

            <!-- Mensajes de confirmación o error -->
            <?php if (isset($_GET['success'])): ?>
                <div id="successMessage" class="alert alert-success">
                    <?php echo htmlspecialchars($_GET['success']); ?>
                </div>
            <?php elseif (isset($_GET['error'])): ?>
                <div id="errorMessage" class="alert alert-danger">
                    <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
            <?php elseif (isset($_GET['duplicate'])): ?>
                <div id="duplicateMessage" class="alert alert-warning">
                    <?php echo htmlspecialchars("El usuario ya existe. Por favor, elija otro nombre de usuario."); ?>
                </div>
            <?php endif; ?>

            <form action="servicios/<?php echo $editing ? 'updateConductor.php' : 'addConductor.php'; ?>" method="POST" class="d-flex flex-column gap-2">
                <?php if ($editing): ?>
                    <input type="hidden" name="id_conductor" value="<?php echo htmlspecialchars($id); ?>">
                <?php endif; ?>
                <div class="d-flex flex-row gap-2">
                    <div class="form-floating mb-3 w-100">
                        <input type="text" class="form-control" id="floatingUsername" name="nombre_usuario" placeholder="Nombre de usuario"
                               value="<?php echo htmlspecialchars($conductorData['nombre_usuario']); ?>" required>
                        <label for="floatingUsername">Nombre de Usuario</label>
                    </div>
                    <div class="form-floating mb-3 w-100">
                        <input type="password" class="form-control" id="floatingPassword" name="password" placeholder="Contraseña"
                               value="<?php echo htmlspecialchars($conductorData['password']); ?>" required>
                        <label for="floatingPassword">Contraseña</label>
                    </div>
                </div>

                <div class="d-flex flex-row gap-2">
                    <div class="form-floating mb-3 w-100">
                        <input type="text" class="form-control" id="floatingNombre" name="nombre" placeholder="Nombre Completo"
                               value="<?php echo htmlspecialchars($conductorData['nombre']); ?>" required>
                        <label for="floatingNombre">Nombre Completo</label>
                    </div>
                    <div class="form-floating mb-3 w-100">
                        <input type="text" class="form-control" id="floatingApellido1" name="apellido1" placeholder="Primer apellido"
                               value="<?php echo htmlspecialchars($conductorData['apellido1']); ?>">
                        <label for="floatingApellido1">Primer Apellido</label>
                    </div>
                </div>
                <div class="form-floating mb-3 w-100">
                    <input class="form-control" type="text" id="floatingApellido2" name="apellido2" placeholder="Segundo apellido"
                           value="<?php echo htmlspecialchars($conductorData['apellido2']); ?>">
                    <label for="floatingApellido2">Segundo Apellido</label>
                </div>
                <button type="submit" class="btn btn-primary"><?php echo $editing ? 'Actualizar' : 'Guardar'; ?></button>
            </form>
        </div>
        <div class="w-100">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>Apellido Paterno</th>
                    <th>Apellido Materno</th>
                    <th>Usuario</th>
                    <th>Acciones</th>
                </tr>
                </thead>
                <tbody>
                <?php
                // Obtener lista de conductores
                $stmt = $conn->prepare("
                    SELECT c.numConductor, c.nombre, c.primerApellido, c.segundoApellido, u.username 
                    FROM conductor c
                    INNER JOIN usuario u ON c.id_usuario = u.id
                    WHERE u.userType = 'conductor' order by numConductor desc
                ");
                $stmt->execute();
                $conductores = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($conductores as $conductor): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($conductor['numConductor']); ?></td>
                        <td><?php echo htmlspecialchars($conductor['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($conductor['primerApellido']); ?></td>
                        <td><?php echo htmlspecialchars($conductor['segundoApellido']); ?></td>
                        <td><?php echo htmlspecialchars($conductor['username']); ?></td>
                        <td>
                            <a href="?edit=<?php echo $conductor['numConductor']; ?>" class="btn btn-sm btn-warning">Editar</a>
                            <a href="servicios/deleteConductor.php?id=<?php echo $conductor['numConductor']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Seguro que deseas eliminar este conductor?');">Eliminar</a>
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
        const duplicateMessage = document.getElementById('duplicateMessage');
        if (successMessage) successMessage.style.display = 'none';
        if (errorMessage) errorMessage.style.display = 'none';
        if (duplicateMessage) duplicateMessage.style.display = 'none';
    }, 3000);
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
