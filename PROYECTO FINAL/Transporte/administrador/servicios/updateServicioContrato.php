<?php
// Mostrar errores para depuración (en desarrollo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../../conexion/conexion.php'; // Conexión a la base de datos

// Validar que se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar que se recibió el ID del servicio y el contrato
    if (!isset($_POST['id_servicio']) || !is_numeric($_POST['id_servicio']) ||
        !isset($_POST['contrato']) || !is_numeric($_POST['contrato'])) {
        header("Location: ../asignarServicio.php?error=" . urlencode("Datos insuficientes para actualizar el servicio."));
        exit();
    }

    // Obtener los datos del formulario
    $idServicio = intval($_POST['id_servicio']);
    $contratoId = intval($_POST['contrato']); // ID del contrato
    $nombreServicio = trim($_POST['nombre_servicio']);
    $costoIndividual = floatval($_POST['costo_individual']);
    $numTransportes = intval($_POST['num_transportes']);
    $descripcion = trim($_POST['descripcion']);
    $coordinador = intval($_POST['coordinador']);

    // Validaciones básicas
    if (empty($nombreServicio) || $costoIndividual <= 0 || $numTransportes <= 0 || empty($descripcion) || $coordinador <= 0) {
        header("Location: ../asignarServicio.php?contrato={$contratoId}&edit={$idServicio}&error=" . urlencode("Todos los campos son obligatorios y deben ser válidos."));
        exit();
    }

    try {
        // Preparar la consulta de actualización
        $stmt = $conn->prepare("
            UPDATE servicio 
            SET nombreServicio = :nombreServicio, 
                costoIndividual = :costoIndividual, 
                numTransportes = :numTransportes, 
                descripcion = :descripcion, 
                coordinador = :coordinador
            WHERE clave = :idServicio
        ");
        $stmt->bindParam(':nombreServicio', $nombreServicio, PDO::PARAM_STR);
        $stmt->bindParam(':costoIndividual', $costoIndividual, PDO::PARAM_STR);
        $stmt->bindParam(':numTransportes', $numTransportes, PDO::PARAM_INT);
        $stmt->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
        $stmt->bindParam(':coordinador', $coordinador, PDO::PARAM_INT);
        $stmt->bindParam(':idServicio', $idServicio, PDO::PARAM_INT);

        // Ejecutar la consulta
        $stmt->execute();

        // Redirigir con éxito
        header("Location: ../asignarServicio.php?contrato={$contratoId}&success=" . urlencode("Servicio actualizado correctamente."));
        exit();
    } catch (PDOException $e) {
        // Manejar errores de la base de datos
        header("Location: ../asignarServicio.php?contrato={$contratoId}&edit={$idServicio}&error=" . urlencode("Error al actualizar el servicio: " . $e->getMessage()));
        exit();
    }
} else {
    // Si no es POST, redirigir
    header("Location: ../asignarServicio.php?error=" . urlencode("Método de solicitud no permitido."));
    exit();
}
