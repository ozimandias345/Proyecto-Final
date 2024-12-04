<?php
require '../../conexion/conexion.php'; // Ruta a tu archivo de conexión

if (isset($_GET['marca'])) {
    $marcaId = $_GET['marca'];

    $stmt = $conn->prepare("SELECT codigo, nombre FROM modelo WHERE marca = :marcaId");
    $stmt->bindParam(':marcaId', $marcaId, PDO::PARAM_INT);
    $stmt->execute();
    $modelos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($modelos);
}
