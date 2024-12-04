<?php
// Mostrar errores para depuración (en desarrollo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../../conexion/conexion.php'; // Conexión a la base de datos

// Validar que se recibe el ID del contrato
if (!isset($_GET['contrato']) || !is_numeric($_GET['contrato'])) {
    header("Location: ../asignarServicio.php?error=" . urlencode("Contrato no especificado."));
    exit();
}

$contratoId = intval($_GET['contrato']);

// Verificar si la solicitud es POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener datos del formulario
    $nombreServicio = trim($_POST['nombre_servicio']);
    $costoIndividual = floatval($_POST['costo_individual']);
    $numTransportes = intval($_POST['num_transportes']);
    $descripcion = trim($_POST['descripcion']);
    $coordinadorId = intval($_POST['coordinador']);

    // Validar los datos
    if (empty($nombreServicio) || $costoIndividual <= 0 || $numTransportes <= 0 || empty($descripcion) || $coordinadorId <= 0) {
        header("Location: ../asignarServicio.php?contrato=$contratoId&error=" . urlencode("Todos los campos son obligatorios y deben ser válidos."));
        exit();
    }

    try {
        // Insertar el nuevo servicio
        $stmt = $conn->prepare("
            INSERT INTO servicio (nombreServicio, costoIndividual, numTransportes, descripcion, contrato, coordinador)
            VALUES (:nombreServicio, :costoIndividual, :numTransportes, :descripcion, :contratoId, :coordinador)
        ");
        $stmt->bindParam(':nombreServicio', $nombreServicio, PDO::PARAM_STR);
        $stmt->bindParam(':costoIndividual', $costoIndividual, PDO::PARAM_STR);
        $stmt->bindParam(':numTransportes', $numTransportes, PDO::PARAM_INT);
        $stmt->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
        $stmt->bindParam(':contratoId', $contratoId, PDO::PARAM_INT);
        $stmt->bindParam(':coordinador', $coordinadorId, PDO::PARAM_INT);
        $stmt->execute();

        // Redirigir con un mensaje de éxito
        header("Location: ../asignarServicio.php?contrato=$contratoId&success=" . urlencode("Servicio agregado exitosamente."));
        exit();
    } catch (PDOException $e) {
        // Redirigir con un mensaje de error
        header("Location: ../asignarServicio.php?contrato=$contratoId&error=" . urlencode("Error al agregar el servicio: " . $e->getMessage()));
        exit();
    }
} else {
    // Si el archivo es accedido directamente, redirigir a la página anterior
    header("Location: ../asignarServicio.php?contrato=$contratoId");
    exit();
}
