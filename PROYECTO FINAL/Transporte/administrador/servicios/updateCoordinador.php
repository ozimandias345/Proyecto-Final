<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../../conexion/conexion.php'; // Conexión a la base de datos

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_coordinador'])) {
    $idCoordinador = intval($_POST['id_coordinador']);
    $nombreUsuario = trim($_POST['nombre_usuario']);
    $password = trim($_POST['password']); // Contraseña en texto plano
    $nombre = trim($_POST['nombre']);
    $primerApellido = trim($_POST['apellido1']);
    $segundoApellido = trim($_POST['apellido2']);

    try {
        // Obtener el ID del usuario asociado
        $stmtUsuario = $conn->prepare("SELECT id_usuario FROM coordinador WHERE numCoordinador = :idCoordinador");
        $stmtUsuario->bindParam(':idCoordinador', $idCoordinador, PDO::PARAM_INT);
        $stmtUsuario->execute();
        $usuario = $stmtUsuario->fetch(PDO::FETCH_ASSOC);

        if (!$usuario) {
            throw new Exception("Coordinador no encontrado.");
        }

        $idUsuario = $usuario['id_usuario'];

        // Iniciar una transacción
        $conn->beginTransaction();

        // Actualizar la tabla usuario
        $stmtUpdateUsuario = $conn->prepare("
            UPDATE usuario 
            SET username = :username, password = :password
            WHERE id = :idUsuario
        ");
        $stmtUpdateUsuario->bindParam(':username', $nombreUsuario, PDO::PARAM_STR);
        $stmtUpdateUsuario->bindParam(':password', $password, PDO::PARAM_STR); // Guardar sin cifrar
        $stmtUpdateUsuario->bindParam(':idUsuario', $idUsuario, PDO::PARAM_INT);
        $stmtUpdateUsuario->execute();

        // Actualizar la tabla coordinador
        $stmtUpdateCoordinador = $conn->prepare("
            UPDATE coordinador 
            SET nombre = :nombre, primerApellido = :primerApellido, segundoApellido = :segundoApellido
            WHERE numCoordinador = :idCoordinador
        ");
        $stmtUpdateCoordinador->bindParam(':nombre', $nombre, PDO::PARAM_STR);
        $stmtUpdateCoordinador->bindParam(':primerApellido', $primerApellido, PDO::PARAM_STR);
        $stmtUpdateCoordinador->bindParam(':segundoApellido', $segundoApellido, PDO::PARAM_STR);
        $stmtUpdateCoordinador->bindParam(':idCoordinador', $idCoordinador, PDO::PARAM_INT);
        $stmtUpdateCoordinador->execute();

        // Confirmar la transacción
        $conn->commit();

        // Redirigir con mensaje de éxito
        header("Location: ../coordinadores.php?updated=1");
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
