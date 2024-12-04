<?php
session_start();
require '../conexion/conexion.php';



try {
    // Consultar las empresas
    $stmt = $conn->prepare("SELECT numEmpresa, nombre FROM empresa ORDER BY numEmpresa ASC");
    $stmt->execute();
    $empresas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $empresas = [];
    $error = "Error al obtener las empresas: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Empresas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootswatch@4.5.2/dist/litera/bootstrap.min.css" integrity="sha384-enpDwFISL6M3ZGZ50Tjo8m65q06uLVnyvkFO3rsoW0UC15ATBFz3QEhr3hmxpYsn" crossorigin="anonymous">
</head>
<body>
<div class="d-flex flex-column justify-content-center align-top container gap-5">
    <header class="d-flex justify-content-between align-items-center  p-4 bg-body-secondary mt-2 rounded-4">
        <div class="d-flex gap-3 align-items-center">
            <a href="../admin_dashboard.php" class="">
                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24"><path fill="currentColor" d="m9.55 12l7.35 7.35q.375.375.363.875t-.388.875t-.875.375t-.875-.375l-7.7-7.675q-.3-.3-.45-.675t-.15-.75t-.15-.75t.45-.675l7.7-7.7q.375-.375.888-.363t.887.388t.375.875t-.375.875z"/></svg>
            </a>
            <span class="fs-2">Empresas</span>
        </div>
    </header>

    <main class="container">
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if (empty($empresas)): ?>
                <div class="col">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">No hay empresas disponibles</h5>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($empresas as $empresa): ?>
                    <div class="col">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($empresa['nombre']); ?></h5>
                                <p class="card-text">ID Empresa: <?php echo htmlspecialchars($empresa['numEmpresa']); ?></p>
                            </div>
                            <div class="card-footer text-center">
                                <a href="contratosEmpresa.php?empresa=<?php echo htmlspecialchars($empresa['numEmpresa']); ?>" class="btn btn-primary">Ver Contratos</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
