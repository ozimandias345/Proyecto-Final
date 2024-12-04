<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../../conexion/conexion.php'; // Conexión a la base de datos

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $idCoordinador = intval($_GET['id']); // ID del coordinador

    try {
        // Iniciar una transacción
        $conn->beginTransaction();

        // Obtener el ID del usuario asociado al coordinador
        $stmtCoordinador = $conn->prepare("SELECT id_usuario FROM coordinador WHERE numCoordinador = :idCoordinador");
        $stmtCoordinador->bindParam(':idCoordinador', $idCoordinador, PDO::PARAM_INT);
        $stmtCoordinador->execute();
        $usuario = $stmtCoordinador->fetch(PDO::FETCH_ASSOC);

        if (!$usuario) {
            throw new Exception("Coordinador no encontrado.");
        }

        $idUsuario = $usuario['id_usuario'];

        // Eliminar el coordinador
        $stmtDeleteCoordinador = $conn->prepare("DELETE FROM coordinador WHERE numCoordinador = :idCoordinador");
        $stmtDeleteCoordinador->bindParam(':idCoordinador', $idCoordinador, PDO::PARAM_INT);
        $stmtDeleteCoordinador->execute();

        // Eliminar el usuario asociado
        $stmtDeleteUsuario = $conn->prepare("DELETE FROM usuario WHERE id = :idUsuario");
        $stmtDeleteUsuario->bindParam(':idUsuario', $idUsuario, PDO::PARAM_INT);
        $stmtDeleteUsuario->execute();

        // Confirmar la transacción
        $conn->commit();

        // Redirigir con mensaje de éxito
        header("Location: ../coordinadores.php?deleted=1");
        exit();
    } catch (Exception $e) {
        // Revertir la transacción en caso de error
        $conn->rollBack();
        header("Location: ../coordinadores.php?error=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    // Redirigir si el archivo fue accedido directamente
    header("Location: ../coordinadores.php");
    exit();
}
