<?php 
session_start();

// Verificar si el usuario es conductor
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'conductor') {
    header("Location: ../login.php?error=" . urlencode("Acceso no autorizado."));
    exit();
}

require '../conexion/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Obtener el numConductor basado en el user_id del conductor
        $stmtConductor = $conn->prepare("SELECT numConductor FROM conductor WHERE id_usuario = :userId LIMIT 1");
        $stmtConductor->bindParam(':userId', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmtConductor->execute();
        $numConductor = $stmtConductor->fetchColumn();

        if (!$numConductor) {
            throw new Exception("No se encontró un conductor asociado a este usuario.");
        }

        // Insertar el reporte en la base de datos
        $descripcion = $_POST['descripcion'];
        $fecha = $_POST['fecha'];

        $stmtInsert = $conn->prepare(
            "INSERT INTO reporte_conductor (descripcion, fecha, numConductor) 
             VALUES (:descripcion, :fecha, :numConductor)"
        );
        $stmtInsert->bindParam(':descripcion', $descripcion);
        $stmtInsert->bindParam(':fecha', $fecha);
        $stmtInsert->bindParam(':numConductor', $numConductor);
        $stmtInsert->execute();

        // Verificar que la inserción fue exitosa
        if ($stmtInsert->rowCount() > 0) {
            echo "<div class='alert alert-success'>Reporte enviado exitosamente.</div>";
        } else {
            echo "<div class='alert alert-danger'>Error al enviar el reporte.</div>";
        }
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generador de reportes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <header class="d-flex justify-content-between align-items-center p-4 bg-body-secondary rounded-4 shadow-sm">
        <div class="d-flex gap-3 align-items-center">
            <a href="../admin_dashboard.php" class="text-decoration-none">
                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24">
                    <path fill="currentColor" d="m9.55 12l7.35 7.35q.375.375.363.875t-.388.875t-.875.375t-.875-.375l-7.7-7.675q-.3-.3-.45-.675t-.15-.75t-.15-.75t.45-.675l7.7-7.7q.375-.375.888-.363t.887.388t.375.875t-.375.875z"/>
                </svg>
            </a>
            <span class="h3">Generador de Reportes</span>
        </div>
    </header>

    <div class="card mt-4 shadow-sm">
        <div class="card-body">
            <h2 class="card-title">Enviar Reporte</h2>
            <form method="POST" action="reportesConductor.php">
                <div class="mb-3">
                    <label for="descripcion" class="form-label">Descripción del reporte</label>
                    <textarea id="descripcion" name="descripcion" class="form-control" placeholder="Escribe la descripción del reporte" required></textarea>
                </div>

                <div class="mb-3">
                    <label for="fecha" class="form-label">Fecha</label>
                    <input type="date" id="fecha" name="fecha" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary w-100">Enviar Reporte</button>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
