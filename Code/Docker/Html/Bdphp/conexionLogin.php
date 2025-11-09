<?php
session_start();

// ==================== CONFIGURACIÓN DE BASE DE DATOS ====================
define('DB_HOST', 'localhost');
define('DB_PORT', '80'); // Puerto correcto de MariaDB (normalmente 3306, no 80)
define('DB_NAME', 'sistema_login');
define('DB_USER', 'zarucoo');
define('DB_PASS', 'root');

// ⚠️ MODO DESARROLLO: Permite contraseñas sin encriptar
define('DEV_MODE', true); // Cambiar a false en producción

function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch(PDOException $e) {
        return null;
    }
}

// ==================== PROCESAR LOGOUT ====================
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// ==================== PROCESAR LOGIN (AJAX) ====================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    header('Content-Type: application/json');
    
    $username = isset($_POST['username']) ? $_POST['username'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    if (empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Por favor completa todos los campos']);
        exit;
    }
    
    $pdo = getDBConnection();
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT id, username, email, password, activo FROM usuarios WHERE username = :username OR email = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
            exit;
        }
        
        if (!$user['activo']) {
            echo json_encode(['success' => false, 'message' => 'Usuario desactivado']);
            exit;
        }
        
        // ⚠️ VERIFICACIÓN EN MODO DESARROLLO
        $passwordValid = false;
        
        if (DEV_MODE) {
            // En modo desarrollo: acepta contraseñas sin encriptar Y hasheadas
            if ($password === $user['password']) {
                $passwordValid = true; // Contraseña sin encriptar coincide
            } elseif (password_verify($password, $user['password'])) {
                $passwordValid = true; // Contraseña hasheada coincide
            }
        } else {
            // En producción: SOLO contraseñas hasheadas
            $passwordValid = password_verify($password, $user['password']);
        }
        
        if ($passwordValid) {
            $updateStmt = $pdo->prepare("UPDATE usuarios SET ultimo_acceso = CURRENT_TIMESTAMP WHERE id = :id");
            $updateStmt->execute(['id' => $user['id']]);
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['logged_in'] = true;
            
            echo json_encode(['success' => true, 'message' => 'Login exitoso']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Contraseña incorrecta']);
        }
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error en el servidor: ' . $e->getMessage()]);
    }
    exit;
}

// ==================== VERIFICAR SI ESTÁ LOGUEADO ====================
$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Usuario';
$email = isset($_SESSION['email']) ? $_SESSION['email'] : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isLoggedIn ? 'Dashboard' : 'Login'; ?> - Sistema</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        /* ===== ESTILOS LOGIN ===== */
        .login-page {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
            max-width: 400px;
            width: 100%;
        }
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .login-header i {
            font-size: 50px;
            margin-bottom: 15px;
        }
        .login-body {
            padding: 40px;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px;
            font-weight: 600;
            transition: transform 0.3s;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .input-group-text {
            background-color: #f8f9fa;
            border-right: none;
        }
        .form-control {
            border-left: none;
        }
        .dev-warning {
            background: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 10px;
            padding: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        /* ===== ESTILOS DASHBOARD ===== */
        .dashboard-page {
            background: #f8f9fa;
            min-height: 100vh;
        }
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .welcome-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-top: 30px;
        }
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s;
            text-align: center;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-icon {
            font-size: 40px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body class="<?php echo $isLoggedIn ? 'dashboard-page' : 'login-page'; ?>">

<?php if (!$isLoggedIn): ?>
    <!-- ==================== PÁGINA DE LOGIN ==================== -->
    <div class="login-container">
        <div class="login-header">
            <i class="fas fa-user-circle"></i>
            <h3 class="mb-0">Iniciar Sesión</h3>
        </div>
        <div class="login-body">
            <?php if (DEV_MODE): ?>
            <div class="dev-warning">
                
            </div>
            <?php endif; ?>
            
            <div id="alertContainer"></div>
            
            <form id="loginForm">
                <div class="mb-3">
                    <label for="username" class="form-label">Usuario</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-user"></i>
                        </span>
                        <input type="text" class="form-control" id="username" name="username" 
                               placeholder="Ingresa tu usuario" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="Ingresa tu contraseña" required>
                        <span class="input-group-text" style="cursor: pointer;" onclick="togglePassword()">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </span>
                    </div>
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="remember">
                    <label class="form-check-label" for="remember">
                        Recordarme
                    </label>
                </div>

                <button type="submit" class="btn btn-primary btn-login w-100 mb-3">
                    <i class="fas fa-sign-in-alt me-2"></i>Ingresar
                </button>

                <div class="text-center">
                    <small class="text-muted">
                        <strong>Usuarios de prueba:</strong><br>
                        testuser / password123<br>
                        admin / admin123
                    </small>
                </div>
            </form>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        function showAlert(message, type) {
            const alertContainer = document.getElementById('alertContainer');
            alertContainer.innerHTML = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
        }

        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'login');
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('¡Login exitoso! Redirigiendo...', 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showAlert(data.message || 'Usuario o contraseña incorrectos', 'danger');
                }
            })
            .catch(error => {
                showAlert('Error de conexión. Intenta nuevamente.', 'danger');
                console.error('Error:', error);
            });
        });
    </script>

<?php else: ?>
    <!-- ==================== PÁGINA DE DASHBOARD ==================== -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-chart-line me-2"></i>Mi Sistema
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-2"></i><?php echo htmlspecialchars($username); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Perfil</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Configuración</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="?logout=1"><i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="welcome-card">
            <h2><i class="fas fa-home text-primary me-2"></i>Bienvenido, <?php echo htmlspecialchars($username); ?>!</h2>
            <p class="text-muted mb-0">
                <i class="fas fa-envelope me-2"></i><?php echo htmlspecialchars($email); ?>
            </p>
            <p class="text-muted">
                <i class="fas fa-clock me-2"></i>Último acceso: <?php echo date('d/m/Y H:i:s'); ?>
            </p>
        </div>

        <div class="row mt-4">
            <div class="col-md-3 mb-4">
                <div class="stat-card">
                    <div class="stat-icon text-primary">
                        <i class="fas fa-users"></i>
                    </div>
                    <h4>125</h4>
                    <p class="text-muted mb-0">Usuarios</p>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="stat-card">
                    <div class="stat-icon text-success">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h4>$45,230</h4>
                    <p class="text-muted mb-0">Ingresos</p>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="stat-card">
                    <div class="stat-icon text-warning">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <h4>89</h4>
                    <p class="text-muted mb-0">Pedidos</p>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="stat-card">
                    <div class="stat-icon text-danger">
                        <i class="fas fa-star"></i>
                    </div>
                    <h4>4.8</h4>
                    <p class="text-muted mb-0">Calificación</p>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="alert alert-success">
                    <h5 class="alert-heading"><i class="fas fa-check-circle me-2"></i>¡Sistema funcionando!</h5>
                    <p>Tu sistema de login está completamente operativo.</p>
                    <?php if (DEV_MODE): ?>
                    <hr>
                    <p class="mb-0"><strong>⚠️ MODO DESARROLLO ACTIVO:</strong> Las contraseñas sin encriptar son aceptadas.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>