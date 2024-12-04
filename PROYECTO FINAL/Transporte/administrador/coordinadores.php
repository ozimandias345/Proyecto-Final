<?php
require '../conexion/conexion.php'; // Incluir conexión a la base de datos

// Consultar los datos de los coordinadores
try {
    $stmt = $conn->prepare("
        SELECT 
            c.numCoordinador AS id_coordinador,
            c.nombre AS nombre,
            c.primerApellido AS apellido_paterno,
            c.segundoApellido AS apellido_materno,
            u.username AS usuario,
            u.password AS contrasena
        FROM coordinador c
        INNER JOIN usuario u ON c.id_usuario = u.id order by c.numCoordinador DESC 
    ");
    $stmt->execute();
    $coordinadores = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error al obtener coordinadores: " . $e->getMessage();
    $coordinadores = [];
}


// Rellenar datos si se viene desde la opción de editar
$editing = false;
$coordinadorData = [
    'id_coordinador' => '',
    'nombre_usuario' => '',
    'password' => '',
    'nombre' => '',
    'apellido1' => '',
    'apellido2' => ''
];

if (isset($_GET['edit']) && intval($_GET['edit'])) {
    $editing = true;
    $idToEdit = intval($_GET['edit']);

    $stmtEdit = $conn->prepare("
        SELECT 
            c.numCoordinador AS id_coordinador,
            u.username AS nombre_usuario,
            u.password AS password,
            c.nombre AS nombre,
            c.primerApellido AS apellido1,
            c.segundoApellido AS apellido2
        FROM coordinador c
        INNER JOIN usuario u ON c.id_usuario = u.id
        WHERE c.numCoordinador = :id
    ");
    $stmtEdit->bindParam(':id', $idToEdit, PDO::PARAM_INT);
    $stmtEdit->execute();
    $coordinadorData = $stmtEdit->fetch(PDO::FETCH_ASSOC);
}
?>
<html>
<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootswatch@4.5.2/dist/litera/bootstrap.min.css" integrity="sha384-enpDwFISL6M3ZGZ50Tjo8m65q06uLVnyvkFO3rsoW0UC15ATBFz3QEhr3hmxpYsn" crossorigin="anonymous">

</head>
<body>
<div class="d-flex flex-column justify-content-center align-top  gap-5">
    <header class="d-flex justify-content-between align-items-center container  p-4 bg-body-secondary mt-2 rounded-4">
        <div class="d-flex gap-3 align-items-center">
            <a href="../admin_dashboard.php" class=""> 
                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24"><path fill="currentColor" d="m9.55 12l7.35 7.35q.375.375.363.875t-.388.875t-.875.375t-.875-.375l-7.7-7.675q-.3-.3-.45-.675t-.15-.75t.15-.75t.45-.675l7.7-7.7q.375-.375.888-.363t.887.388t.375.875t-.375.875z"/></svg>
            </a>
            <span class="fs-3 bold">Coordinadores</span>
        </div>
    </header>

    <main class="d-flex  gap-5 container-fluid">
        <div class="border border-start p-4 rounded-2 w-50 bg-white">
            <h4 class="mb-4">Formulario coordinadores</h4>
            <!-- Mensaje de éxito al crear un coordinador -->
            <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
                <div id="successMessage" class="alert alert-success" role="alert">
                    ¡Coordinador creado exitosamente!
                </div>
            <?php endif; ?>

            <!-- Mensaje de éxito al eliminar un coordinador -->
            <?php if (isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
                <div id="deletedMessage" class="alert alert-success" role="alert">
                    ¡Coordinador eliminado correctamente!
                </div>
            <?php endif; ?>

            <!-- Mensaje de error -->
            <?php if (isset($_GET['error'])): ?>
                <div id="errorMessage" class="alert alert-danger" role="alert">
                    Error: <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>
            <?php if (isset($_GET['updated']) && $_GET['updated'] == 1): ?>
                <div id="updatedMessage" class="alert alert-success" role="alert">
                    ¡Coordinador actualizado correctamente!
                </div>
            <?php endif; ?>

            <form action="<?php echo $editing ? 'servicios/updateCoordinador.php' : 'servicios/addCoordinador.php'; ?>" method="POST" class="d-flex flex-column gap-2">
                <?php if ($editing): ?>
                    <input type="hidden" name="id_coordinador" value="<?php echo htmlspecialchars($coordinadorData['id_coordinador']); ?>">
                <?php endif; ?>
                <div class="d-flex flex-row gap-2">
                    <div class="form-floating mb-3 w-100">
                        <input type="text" class="form-control" id="floatingUsername" name="nombre_usuario" placeholder="Nombre de usuario"
                               value="<?php echo htmlspecialchars($coordinadorData['nombre_usuario']); ?>" required>
                        <label for="floatingUsername">Nombre de Usuario</label>
                    </div>
                    <div class="form-floating mb-3 w-100">
                        <input type="password" class="form-control" id="floatingPassword" name="password" placeholder="Contraseña"
                               value="<?php echo htmlspecialchars($coordinadorData['password']); ?>" required>
                        <label for="floatingPassword">Contraseña</label>
                    </div>
                </div>

                <div class="d-flex flex-row gap-2">
                    <div class="form-floating mb-3 w-100">
                        <input type="text" class="form-control" id="floatingNombre" name="nombre" placeholder="Nombre Completo"
                               value="<?php echo htmlspecialchars($coordinadorData['nombre']); ?>" required>
                        <label for="floatingNombre">Nombre Completo</label>
                    </div>
                    <div class="form-floating mb-3 w-100">
                        <input type="text" class="form-control" id="floatingTelefono" name="apellido1" placeholder="Primer apellido"
                               value="<?php echo htmlspecialchars($coordinadorData['apellido1']); ?>">
                        <label for="floatingTelefono">Primer Apellido</label>
                    </div>
                </div>
                <div class="form-floating mb-3 w-100">
                    <input class="form-control" type="text" id="floatingDireccion" name="apellido2" placeholder="Segundo apellido"
                           value="<?php echo htmlspecialchars($coordinadorData['apellido2']); ?>">
                    <label for="floatingDireccion">Segundo Apellido</label>
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
                    <th>Contraseña</th>
                    <th>Acciones</th>
                </tr>
                </thead>
                <tbody>
                <?php if (count($coordinadores) > 0): ?>
                    <?php foreach ($coordinadores as $coordinador): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($coordinador['id_coordinador']); ?></td>
                            <td><?php echo htmlspecialchars($coordinador['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($coordinador['apellido_paterno']); ?></td>
                            <td><?php echo htmlspecialchars($coordinador['apellido_materno']); ?></td>
                            <td><?php echo htmlspecialchars($coordinador['usuario']); ?></td>
                            <td><?php echo htmlspecialchars($coordinador['contrasena']); ?></td>
                            <td>
                                <!-- Botón para editar -->
                                <a href="coordinadores.php?edit=<?php echo $coordinador['id_coordinador']; ?>" class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil-square"></i> Editar
                                </a>
                                <!-- Botón para eliminar -->
                                <a href="servicios/deleteCoordinador.php?id=<?php echo $coordinador['id_coordinador']; ?>"
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('¿Estás seguro de eliminar este coordinador?');">
                                    <i class="bi bi-trash"></i> Eliminar
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">No hay coordinadores registrados.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
<script>
    // Función para ocultar los mensajes después de 3 segundos
    setTimeout(() => {
        const successMessage = document.getElementById('successMessage');
        const deletedMessage = document.getElementById('deletedMessage');
        const errorMessage = document.getElementById('errorMessage');
        const updatedMessage = document.getElementById('updatedMessage'); // Nuevo mensaje de actualización

        if (successMessage) successMessage.style.display = 'none';
        if (deletedMessage) deletedMessage.style.display = 'none';
        if (errorMessage) errorMessage.style.display = 'none';
        if (updatedMessage) updatedMessage.style.display = 'none'; // Ocultar mensaje de actualización
    }, 3000); // 3000 ms = 3 segundos
</script>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
