<?php
session_start();

// Verificar si la sesión está activa
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Deshabilitar el almacenamiento en caché
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

require 'conexion/conexion.php';

// Obtener el tipo de usuario desde la sesión
$userType = $_SESSION['user_type'];
$userName = ""; // Variable para almacenar el nombre del usuario
$empresaName = ""; // Variable para almacenar el nombre de la empresa
$coordinadorId = null; // Inicializar el ID del coordinador como null

try {
    if ($userType === 'coordinador') {
        // Obtener nombre del coordinador y su ID
        $stmt = $conn->prepare("
            SELECT c.nombre, c.numCoordinador 
            FROM coordinador c 
            INNER JOIN usuario u ON c.id_usuario = u.id 
            WHERE u.id = :id
        ");
        $stmt->bindParam(':id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $userName = $result ? $result['nombre'] : "Coordinador";
        $coordinadorId = $result['numCoordinador']; // Asignar el ID del coordinador
    } elseif ($userType === 'representante') {
        // Obtener nombre del representante y la empresa
        $stmt = $conn->prepare("
            SELECT r.nombre AS representante, e.nombre AS empresa
            FROM representante r
            INNER JOIN usuario u ON r.id_usuario = u.id
            INNER JOIN empresa e ON r.empresa = e.numEmpresa
            WHERE u.id = :id
        ");
        $stmt->bindParam(':id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $userName = $result ? $result['representante'] : "Representante";
        $empresaName = $result ? $result['empresa'] : "Empresa Desconocida";
    } elseif ($userType === 'empleado') {
        // Obtener nombre del empleado y la empresa
        $stmt = $conn->prepare("
            SELECT e.nombre AS empleado, emp.nombre AS empresa
            FROM empleado e
            INNER JOIN usuario u ON e.id_usuario = u.id
            INNER JOIN empresa emp ON e.empresa = emp.numEmpresa
            WHERE u.id = :id
        ");
        $stmt->bindParam(':id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $userName = $result ? $result['empleado'] : "Empleado";
        $empresaName = $result ? $result['empresa'] : "Empresa Desconocida";
    } elseif ($userType === 'conductor') {
        // Obtener el nombre del conductor
        $stmt = $conn->prepare("
            SELECT c.nombre 
            FROM conductor c
            INNER JOIN usuario u ON c.id_usuario = u.id 
            WHERE u.id = :id
        ");
        $stmt->bindParam(':id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $userName = $result ? $result['nombre'] : "Conductor";
    } else {
        $userName = "Administrador";
    }
} catch (PDOException $e) {
    $userName = $userType === 'coordinador' ? "Coordinador" :
        ($userType === 'representante' ? "Representante" :
            ($userType === 'empleado' ? "Empleado" : "Administrador"));
    $empresaName = ($userType === 'representante' || $userType === 'empleado') ? "Empresa Desconocida" : "";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootswatch@4.5.2/dist/litera/bootstrap.min.css">
</head>
<body>
<div class="d-flex flex-column justify-content-center align-top container gap-5">
    <header class="d-flex justify-content-between align-items-center p-4 bg-body-secondary mt-2 rounded-4">
        <div class="d-flex gap-3 flex-row align-items-center">
            <span class="fs-5">Bienvenido, <?php echo htmlspecialchars($userName); ?></span>
            <?php if ($userType === 'representante' || $userType === 'empleado'): ?>
                <span class="fs-6 text-muted">Empresa: <?php echo htmlspecialchars($empresaName); ?></span>
            <?php endif; ?>
        </div>
        <a href="servicios/logout.php" class="btn btn-danger">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-logout">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M14 8v-2a2 2 0 0 0 -2 -2h-7a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h7a2 2 0 0 0 2 -2v-2"/>
                <path d="M9 12h12l-3 -3"/>
                <path d="M18 15l3 -3"/>
            </svg>
            Cerrar Sesión
        </a>
    </header>

    <?php if ($userType === 'administrador'): ?>
        <!-- Dashboard para Administrador -->
        <main class="d-grid" style="grid-template-columns: repeat(2, 1fr); gap: 1rem;">
            <a class="text-decoration-none btn btn-outline-dark flex-grow-1" href="administrador/empresas.php">
                <svg xmlns="http://www.w3.org/2000/svg" width="128" height="128" viewBox="0 0 32 32"><path fill="currentColor" d="M8 8h2v4H8zm0 6h2v4H8zm6-6h2v4h-2zm0 6h2v4h-2zm-6 6h2v4H8zm6 0h2v4h-2z"/><path fill="currentColor" d="M30 14a2 2 0 0 0-2-2h-6V4a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v26h28ZM4 4h16v24H4Zm18 24V14h6v14Z"/></svg>
                <h2>Gestionar Empresas</h2>
            </a>
            <a class="text-decoration-none btn btn-outline-dark flex-grow-1" href="administrador/coordinadores.php">
                <svg xmlns="http://www.w3.org/2000/svg" width="128" height="128" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.5"><path d="M1 20v-1a7 7 0 0 1 7-7v0a7 7 0 0 1 7 7v1"/><path d="M13 14v0a5 5 0 0 1 5-5v0a5 5 0 0 1 5 5v.5"/><path stroke-linejoin="round" d="M8 12a4 4 0 1 0 0-8a4 4 0 0 0 0 8m10-3a3 3 0 1 0 0-6a3 3 0 0 0 0 6"/></g></svg>
                <h2>Gestionar Coordinadores</h2>
            </a>
            <a class="text-decoration-none btn btn-outline-dark flex-grow-1" href="administrador/conductores.php">
                <svg xmlns="http://www.w3.org/2000/svg" width="128" height="128" viewBox="0 0 48 48"><path fill="currentColor" fill-rule="evenodd" d="M15 9.5c0-.437 4.516-3.5 9-3.5s9 3.063 9 3.5c0 1.56-.166 2.484-.306 2.987c-.093.33-.402.513-.745.513H16.051c-.343 0-.652-.183-.745-.513C15.166 11.984 15 11.06 15 9.5m7.5-.5a1 1 0 1 0 0 2h3a1 1 0 0 0 0-2zm-6.462 10.218c-3.33-1.03-2.49-2.87-1.22-4.218H33.46c1.016 1.298 1.561 3.049-1.51 4.097q.05.445.05.903a8 8 0 1 1-15.962-.782m7.69.782c2.642 0 4.69-.14 6.26-.384q.012.19.012.384a6 6 0 1 1-11.992-.315c1.463.202 3.338.315 5.72.315m8.689 14.6A9.99 9.99 0 0 0 24 30a9.99 9.99 0 0 0-8.42 4.602a2.5 2.5 0 0 0-1.447-1.05l-1.932-.517a2.5 2.5 0 0 0-3.062 1.767L8.363 37.7a2.5 2.5 0 0 0 1.768 3.062l1.931.518A2.5 2.5 0 0 0 14 41.006A1 1 0 0 0 16 41v-1q0-.572.078-1.123l5.204 1.395a3 3 0 0 0 5.436 0l5.204-1.395q.077.551.078 1.123v1a1 1 0 0 0 2 .01c.56.336 1.252.453 1.933.27l1.932-.517a2.5 2.5 0 0 0 1.768-3.062l-.777-2.898a2.5 2.5 0 0 0-3.062-1.767l-1.932.517a2.5 2.5 0 0 0-1.445 1.046m-15.814 2.347A8.01 8.01 0 0 1 23 32.062v4.109a3 3 0 0 0-1.88 1.987zm14.794 0A8.01 8.01 0 0 0 25 32.062v4.109c.904.32 1.61 1.06 1.88 1.987zM24 40a1 1 0 1 0 0-2a1 1 0 0 0 0 2" clip-rule="evenodd"/></svg>
                <h2>Gestionar Conductores</h2>
            </a>
            <a class="text-decoration-none btn btn-outline-dark flex-grow-1" href="administrador/vehiculos.php">
                <svg xmlns="http://www.w3.org/2000/svg" width="128" height="128" viewBox="0 0 24 24"><path fill="currentColor" d="M0 5a2 2 0 0 1 2-2h11a2 2 0 0 1 2 2v2h-2V5H2v7h6v2H2v4h6v2H5.414L3.5 21.914L2.086 20.5l.5-.5H2a2 2 0 0 1-2-2zm11.323 3h10.354L24 13.807V21.5h-2V20H11v1.5H9v-7.693zM11 18h11v-3.807L21.923 14H11.077l-.077.193zm.877-6h9.246l-.8-2h-7.646zM3 15h2.004v2.004H3zm9 0h2.004v2.004H12zm7 0h2.004v2.004H19z"/></svg>
                <h2>Gestionar Vehículos</h2>
            </a>
        </main>
    <?php elseif ($userType === 'coordinador' && $coordinadorId): ?>
        <!-- Dashboard para Coordinador -->
        <main class="coordinadores">
            <a class="text-decoration-none btn btn-outline-dark flex-grow-1" href="coordinador/contratosCoordinador.php?coordinador=<?php echo htmlspecialchars($coordinadorId); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="128" height="128" viewBox="0 0 24 24"><path fill="currentColor" d="M8 13h8v-2H8zm0 3h8v-2H8zm0 3h5v-2H8zm-2 3q-.825 0-1.412-.587T4 20V4q0-.825.588-1.412T6 2h8l6 6v12q0 .825-.587 1.413T18 22zm7-13V4H6v16h12V9zM6 4v5zv16z"/></svg>
                <h2>Contratos relacionados</h2>
            </a>
            <a class="text-decoration-none btn btn-outline-dark flex-grow-1"
               href="coordinador/historialReportes.php?conductor=<?php echo htmlspecialchars($_SESSION['user_id']); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="128" height="128" viewBox="0 0 32 32"><path fill="currentColor" d="M10 18h8v2h-8zm0-5h12v2H10zm0 10h5v2h-5z"/><path fill="currentColor" d="M25 5h-3V4a2 2 0 0 0-2-2h-8a2 2 0 0 0-2 2v1H7a2 2 0 0 0-2 2v21a2 2 0 0 0 2 2h18a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2M12 4h8v4h-8Zm13 24H7V7h3v3h12V7h3Z"/></svg>
                <h2>Historial Reportes</h2>
            </a>
        </main>
    <?php elseif ($userType === 'representante'): ?>
        <!-- Dashboard para Representante -->
        <main class="d-grid" style="grid-template-columns: repeat(2, 1fr); gap: 1rem;">
            <a class="text-decoration-none btn btn-outline-dark flex-grow-1" href="empresa/contratos.php">
                <svg xmlns="http://www.w3.org/2000/svg" width="128" height="128" viewBox="0 0 24 24"><path fill="currentColor" d="M8 13h8v-2H8zm0 3h8v-2H8zm0 3h5v-2H8zm-2 3q-.825 0-1.412-.587T4 20V4q0-.825.588-1.412T6 2h8l6 6v12q0 .825-.587 1.413T18 22zm7-13V4H6v16h12V9zM6 4v5zv16z"/></svg>
                <h2>Contrato</h2>
            </a>
            <a class="text-decoration-none btn btn-outline-dark flex-grow-1" href="empresa/empleados.php">
                <svg xmlns="http://www.w3.org/2000/svg" width="128" height="128" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 7a4 4 0 1 0 8 0a4 4 0 1 0-8 0M3 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2m1-17.87a4 4 0 0 1 0 7.75M21 21v-2a4 4 0 0 0-3-3.85"/></svg>
                <h2>Empleados</h2>
            </a>
        </main>
    <?php elseif ($userType === 'empleado'): ?>
        <!-- Dashboard para Empleado -->
        <main class="empleado">
            <h2>Bienvenido al sistema</h2>
            <p>Aquí podrás visualizar información relacionada con tu empresa.</p>
            <a class="text-decoration-none btn btn-outline-dark flex-grow-1" href="empleado/infoVehiculoEmpleado.php?empleado=<?php echo htmlspecialchars($_SESSION['user_id']); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="128" height="128" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" color="currentColor"><path d="M10.08 2C5.47 2.936 2 7.012 2 11.899C2 17.478 6.522 22 12.101 22c4.887 0 8.963-3.47 9.899-8.08"/><path d="M18.938 18A3.8 3.8 0 0 0 20 17.603m-5.312-.262q.895.39 1.717.58m-5.55-2.973c.413.29.855.638 1.285.938M3 13.826c.322-.157.67-.338 1.063-.493M6.45 13c.562.062 1.192.223 1.906.523M18 7.5a1.5 1.5 0 1 0-3 0a1.5 1.5 0 0 0 3 0"/><path d="M17.488 13.62a1.46 1.46 0 0 1-.988.38a1.46 1.46 0 0 1-.988-.38c-2.427-2.244-5.679-4.752-4.093-8.392C12.277 3.259 14.335 2 16.5 2s4.223 1.26 5.08 3.228c1.585 3.636-1.66 6.155-4.092 8.392"/></g></svg>
                <h2>Ver Paradas</h2>
            </a>
        </main>
    <?php elseif ($userType === 'conductor'): ?>
        <!-- Dashboard para Conductor -->
        <main class="conductor">
            <h2>Bienvenido al Sistema de Transporte</h2>
            <p>Aquí podrás consultar tus rutas y vehículos asignados.</p>
            <a class="text-decoration-none btn btn-outline-dark flex-grow-1"
               href="conductor/rutasAsignadas.php?conductor=<?php echo htmlspecialchars($_SESSION['user_id']); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="128" height="128" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" color="currentColor"><path d="M22 12.089V9.236c0-1.944 0-2.916-.586-3.52S19.886 5.112 18 5.112h-2.079c-.917 0-.925-.002-1.75-.416l-3.331-1.67c-1.391-.698-2.087-1.047-2.828-1.023S6.6 2.421 5.253 3.208l-1.227.719c-.989.578-1.483.867-1.754 1.348C2 5.756 2 6.342 2 7.513v8.236c0 1.539 0 2.309.342 2.737c.228.285.547.476.9.54c.53.095 1.18-.284 2.478-1.044c.882-.516 1.73-1.052 2.785-.907c.884.122 1.705.681 2.495 1.077M8 2.002v15.034m7-12.027v6.013"/><path d="m20.107 20.175l1.845 1.823m-.783-4.36a3.56 3.56 0 1 1-7.121.001a3.56 3.56 0 0 1 7.121 0"/></g></svg>
                <h2>Mis Rutas</h2>
            </a>
            <a class="text-decoration-none btn btn-outline-dark flex-grow-1"
               href="conductor/reportesConductor.php?conductor=<?php echo htmlspecialchars($_SESSION['user_id']); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="128" height="128" viewBox="0 0 32 32"><path fill="currentColor" d="M10 18h8v2h-8zm0-5h12v2H10zm0 10h5v2h-5z"/><path fill="currentColor" d="M25 5h-3V4a2 2 0 0 0-2-2h-8a2 2 0 0 0-2 2v1H7a2 2 0 0 0-2 2v21a2 2 0 0 0 2 2h18a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2M12 4h8v4h-8Zm13 24H7V7h3v3h12V7h3Z"/></svg>
                <h2>Generar Reporte</h2>
            </a>
        </main>

    <?php endif; ?>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
