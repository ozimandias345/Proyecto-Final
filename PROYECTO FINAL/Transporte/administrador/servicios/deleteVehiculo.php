<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../../conexion/conexion.php'; // Incluir conexión a la base de datos

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $idVehiculo = intval($_GET['id']); // Obtener el ID del vehículo desde la URL

    try {
        // Iniciar una transacción
        $conn->beginTransaction();

        // Eliminar el vehículo de la tabla transporte
        $stmt = $conn->prepare("DELETE FROM transporte WHERE numTransporte = :id");
        $stmt->bindParam(':id', $idVehiculo, PDO::PARAM_INT);
        $stmt->execute();

        // Confirmar la transacción
        $conn->commit();

        // Redirigir al formulario con un mensaje de éxito
        header("Location: ../vehiculos.php?success=Vehículo eliminado correctamente");
        exit();
    } catch (Exception $e) {
        // Revertir la transacción en caso de error
        $conn->rollBack();
        header("Location: ../vehiculos.php?error=" . urlencode("Error al eliminar el vehículo: " . $e->getMessage()));
        exit();
    }
} else {
    // Redirigir si el archivo fue accedido sin un ID válido
    header("Location: ../vehiculos.php?error=" . urlencode("Acceso no autorizado"));
    exit();
}
