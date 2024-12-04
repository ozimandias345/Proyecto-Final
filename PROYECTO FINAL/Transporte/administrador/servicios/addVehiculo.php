<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../../conexion/conexion.php'; // Incluir conexión a la base de datos

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener los datos enviados desde el formulario
    $matricula = trim($_POST['matricula']);
    $capacidad = intval($_POST['capacidad']);
    $disponibilidad = trim($_POST['disponibilidad']);
    $marca = intval($_POST['marca']);
    $modelo = intval($_POST['modelo']);
    $conductor = !empty($_POST['conductor']) ? intval($_POST['conductor']) : null;

    try {
        // Iniciar una transacción
        $conn->beginTransaction();

        // Insertar el nuevo vehículo en la tabla transporte
        $stmtVehiculo = $conn->prepare("
            INSERT INTO transporte (matricula, capacidad, disponibilidad, marca, modelo, conductor)
            VALUES (:matricula, :capacidad, :disponibilidad, :marca, :modelo, :conductor)
        ");
        $stmtVehiculo->bindParam(':matricula', $matricula, PDO::PARAM_STR);
        $stmtVehiculo->bindParam(':capacidad', $capacidad, PDO::PARAM_INT);
        $stmtVehiculo->bindParam(':disponibilidad', $disponibilidad, PDO::PARAM_STR);
        $stmtVehiculo->bindParam(':marca', $marca, PDO::PARAM_INT);
        $stmtVehiculo->bindParam(':modelo', $modelo, PDO::PARAM_INT);

        // Si hay un conductor asignado, vincularlo; si no, establecer como NULL
        if ($conductor) {
            $stmtVehiculo->bindParam(':conductor', $conductor, PDO::PARAM_INT);
        } else {
            $stmtVehiculo->bindValue(':conductor', null, PDO::PARAM_NULL);
        }

        $stmtVehiculo->execute();

        // Confirmar la transacción
        $conn->commit();

        // Redirigir al formulario con un mensaje de éxito
        header("Location: ../vehiculos.php?success=Vehículo agregado correctamente");
        exit();
    } catch (Exception $e) {
        // Revertir la transacción en caso de error
        $conn->rollBack();
        header("Location: ../vehiculos.php?error=" . urlencode("Error al agregar el vehículo: " . $e->getMessage()));
        exit();
    }
} else {
    // Redirigir si el archivo fue accedido directamente
    header("Location: ../vehiculos.php");
    exit();
}
