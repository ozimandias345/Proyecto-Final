<?php
// Incluir la conexión a la base de datos
require 'conexion/conexion.php';
session_start();

// Verificar si el usuario ya está autenticado
if (isset($_SESSION['user_id'])) {
    // Redirigir al dashboard según el tipo de usuario
    if ($_SESSION['user_type'] === 'administrador') {
        header("Location: admin_dashboard.php");
    } elseif ($_SESSION['user_type'] === 'representante') {
        header("Location: admin_dashboard.php");
    } elseif ($_SESSION['user_type'] === 'coordinador') {
        header("Location: admin_dashboard.php");
    } elseif ($_SESSION['user_type'] === 'empleado') {
        header("Location: admin_dashboard.php");
    } elseif ($_SESSION['user_type'] === 'conductor') {
        header("Location: admin_dashboard.php");
    }
    exit();
}

$error = ""; // Variable para almacenar errores
$success = ""; // Variable para almacenar éxito

// Verificar si se envió un mensaje de éxito desde addEmpresa.php
if (isset($_GET['success'])) {
    $success = "Registro completado exitosamente. Inicie sesión para continuar.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener datos del formulario de inicio de sesión
    $username = trim($_POST['email']); // El campo "email" actúa como username
    $password = trim($_POST['password']); // Contraseña ingresada por el usuario

    try {
        // Preparar la consulta para buscar usuarios de cualquier tipo
        $stmt = $conn->prepare("SELECT id, password, userType FROM usuario WHERE username = :username LIMIT 1");
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();

        // Verificar si se encontró un registro
        if ($stmt->rowCount() === 1) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Validar la contraseña directamente (sin hash)
            if ($password === $user['password']) {
                // Guardar ID y tipo de usuario en sesión
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_type'] = $user['userType'];

                // Si el usuario es representante, obtener su empresa y guardarla en la sesión
                if ($user['userType'] === 'representante') {
                    $stmtRepresentante = $conn->prepare("
                        SELECT e.numEmpresa AS empresa_id
                        FROM representante r
                        INNER JOIN empresa e ON r.empresa = e.numEmpresa
                        WHERE r.id_usuario = :userId
                    ");
                    $stmtRepresentante->bindParam(':userId', $user['id'], PDO::PARAM_INT);
                    $stmtRepresentante->execute();
                    $resultRepresentante = $stmtRepresentante->fetch(PDO::FETCH_ASSOC);

                    if ($resultRepresentante) {
                        $_SESSION['empresa_id'] = $resultRepresentante['empresa_id'];
                    }
                }

                // Si el usuario es empleado, obtener su empresa y guardarla en la sesión
                if ($user['userType'] === 'empleado') {
                    $stmtEmpleado = $conn->prepare("
                        SELECT e.empresa AS empresa_id
                        FROM empleado e
                        WHERE e.id_usuario = :userId
                    ");
                    $stmtEmpleado->bindParam(':userId', $user['id'], PDO::PARAM_INT);
                    $stmtEmpleado->execute();
                    $resultEmpleado = $stmtEmpleado->fetch(PDO::FETCH_ASSOC);

                    if ($resultEmpleado) {
                        $_SESSION['empresa_id'] = $resultEmpleado['empresa_id'];
                    }
                }

                // Si el usuario es conductor, obtener su información y guardarla en la sesión
                if ($user['userType'] === 'conductor') {
                    $stmtConductor = $conn->prepare("
                        SELECT numConductor 
                        FROM conductor 
                        WHERE id_usuario = :userId
                    ");
                    $stmtConductor->bindParam(':userId', $user['id'], PDO::PARAM_INT);
                    $stmtConductor->execute();
                    $resultConductor = $stmtConductor->fetch(PDO::FETCH_ASSOC);

                    if ($resultConductor) {
                        $_SESSION['conductor_id'] = $resultConductor['numConductor'];
                    }
                }

                // Redirigir según el tipo de usuario
                if ($user['userType'] === 'administrador') {
                    header("Location: admin_dashboard.php");
                } elseif ($user['userType'] === 'representante') {
                    header("Location: admin_dashboard.php");
                } elseif ($user['userType'] === 'coordinador') {
                    header("Location: admin_dashboard.php");
                } elseif ($user['userType'] === 'empleado') {
                    header("Location: admin_dashboard.php");
                } elseif ($user['userType'] === 'conductor') {
                    header("Location: admin_dashboard.php");
                }
                exit();
            } else {
                $error = "Credenciales incorrectas. Verifique su contraseña.";
            }
        } else {
            $error = "Credenciales incorrectas. Verifique su usuario.";
        }
    } catch (PDOException $e) {
        $error = "Error en el sistema. Inténtalo más tarde.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
<div class="container">
    <div class="form-box">
        <div class="form-panel">
            <!-- Formulario de inicio de sesión -->
            <form id="loginForm" class="form active" method="POST" action="">
                <h2>Iniciar Sesión</h2>
                <?php if (!empty($error)): ?>
                    <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
                <?php endif; ?>
                <?php if (!empty($success)): ?>
                    <p style="color: green;"><?php echo htmlspecialchars($success); ?></p>
                <?php endif; ?>
                <input type="text" name="email" placeholder="Nombre de Usuario" required>
                <input type="password" name="password" placeholder="Contraseña" required>
                <button type="submit">Entrar</button>
            </form>

            <!-- Formulario de registro -->
            <form id="registerForm" class="form" method="POST" action="servicios/addEmpresa.php">
                <h2>Registro de Empresa</h2>
                <h3>Datos de la Empresa</h3>
                <input type="text" name="nombre_empresa" placeholder="Nombre de la Empresa" required>
                <input type="text" name="direccion" placeholder="Dirección" required>
                <input type="text" name="telefono" placeholder="Teléfono" required>
                <h3>Datos del Representante</h3>
                <input type="email" name="email" placeholder="Correo Electrónico" required>
                <input type="password" name="password" placeholder="Contraseña" required>
                <input type="text" name="nombre" placeholder="Nombre del Representante" required>
                <input type="text" name="apellido" placeholder="Apellido Paterno" required>
                <input type="text" name="apellido_materno" placeholder="Apellido Materno" required>
                <button type="submit" name="register">Registrar</button>
            </form>
        </div>
        <div class="side-panel">
            <div class="panel-content">
                <h3>¿Aún no tienes una cuenta?</h3>
                <p>Regístrate para que puedas iniciar sesión</p>
                <button id="showRegister">Registrarse</button>
                <button id="showLogin" class="hidden">Volver a Login</button>
            </div>
        </div>
    </div>
</div>
<script>
    // Mostrar y ocultar formularios de inicio de sesión y registro
    document.getElementById('showRegister').addEventListener('click', function () {
        document.getElementById('loginForm').classList.remove('active');
        document.getElementById('registerForm').classList.add('active');
        this.classList.add('hidden');
        document.getElementById('showLogin').classList.remove('hidden');
    });

    document.getElementById('showLogin').addEventListener('click', function () {
        document.getElementById('registerForm').classList.remove('active');
        document.getElementById('loginForm').classList.add('active');
        this.classList.add('hidden');
        document.getElementById('showRegister').classList.remove('hidden');
    });
</script>
</body>
</html>
