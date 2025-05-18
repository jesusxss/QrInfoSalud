<?php
session_start();
include 'db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo     = trim($_POST['correo'] ?? '');
    $contraseña = $_POST['contraseña'] ?? '';
    if ($correo === '' || $contraseña === '') {
        $error = 'Por favor ingrese correo y contraseña.';
    } else {
        $stmt = $conn->prepare("SELECT id,nombre_usuario,correo,contrasena FROM usuarios WHERE correo = ?");
        $stmt->bind_param('s', $correo);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($contraseña, $user['contrasena'])) {
                $_SESSION['user_id']    = $user['id'];
                $_SESSION['user_name']  = $user['nombre_usuario'];
                $_SESSION['user_email'] = $user['correo'];
                header('Location: dashboard.php');
                exit();
            } else {
                $error = 'Contraseña incorrecta.';
            }
        } else {
            $error = 'El correo no está registrado.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Iniciar Sesión</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.4.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <style>
    :root {
      --primary-color: #667eea;
      --primary-gradient: linear-gradient(135deg,#667eea,#764ba2);
      --bg-light: #f9fafb;
      --text-color: #111827;
      --card-bg: rgba(255,255,255,0.9);
    }
    body {
      margin: 0;
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: var(--primary-gradient);
      font-family: 'Segoe UI', sans-serif;
      color: var(--text-color);
    }
    .container-mobile {
      width: 100%;
      max-width: 360px;
      padding: 1rem;
      box-sizing: border-box;
      background: var(--bg-light);
    }
    .card-custom {
      background: var(--card-bg);
      border-radius: 12px;
      box-shadow: 0 8px 24px rgba(0,0,0,0.15);
      overflow: hidden;
    }
    .card-header-custom {
      background: var(--primary-gradient);
      text-align: center;
      padding: 1rem;
    }
    .card-header-custom i {
      color: #fff;
      font-size: 2.5rem;
    }
    .card-header-custom h5 {
      color: #fff;
      margin: 0.5rem 0 0;
      font-size: 1.25rem;
      font-weight: 600;
    }
    .card-body-custom {
      padding: 1rem;
    }
    .form-label {
      display: block;
      margin-bottom: 0.25rem;
      font-weight: 500;
    }
    .form-control {
      width: 100%;
      height: 3rem;
      border-radius: 8px;
      font-size: 1rem;
      padding: 0 0.75rem;
      margin-bottom: 1rem;
      border: 1px solid #ccc;
      box-sizing: border-box;
    }
    .btn-action {
      width: 100%;
      height: 3rem;
      background: var(--primary-color);
      color: #fff;
      border: none;
      border-radius: 8px;
      font-size: 1rem;
      font-weight: 500;
      transition: background-color 0.3s;
    }
    .btn-action:hover {
      background: #5563c1;
    }
    .alert {
      font-size: 0.9rem;
      margin-bottom: 1rem;
    }
    .register-link {
      text-align: center;
      margin-top: 1rem;
      font-size: 0.9rem;
    }
    /* Ahora el enlace es visible sobre el fondo claro */
    .register-link a {
      color: var(--primary-color);
      text-decoration: none;
      font-weight: 600;
    }
    .register-link a:hover {
      text-decoration: underline;
    }
    @media (max-width: 400px) {
      .container-mobile,
      .card-header-custom,
      .card-body-custom {
        padding: 0.75rem;
      }
      .form-control {
        height: 2.5rem;
        font-size: 0.9rem;
      }
      .btn-action {
        height: 2.5rem;
        font-size: 0.9rem;
      }
    }
  </style>
</head>
<body>
  <div class="container-mobile">
    <div class="card-custom">
      <div class="card-header-custom">
        <i class="fas fa-user-circle"></i>
        <h5>Iniciar Sesión</h5>
      </div>
      <div class="card-body-custom">
        <?php if ($error): ?>
          <div class="alert alert-danger" role="alert">
            <?= htmlspecialchars($error) ?>
          </div>
        <?php endif; ?>
        <form method="POST" novalidate>
          <label for="correo" class="form-label">Correo electrónico</label>
          <input type="email" id="correo" name="correo" class="form-control" placeholder="usuario@ejemplo.com" required>

          <label for="contraseña" class="form-label">Contraseña</label>
          <input type="password" id="contraseña" name="contraseña" class="form-control" placeholder="********" required>

          <button type="submit" class="btn-action">Acceder</button>
        </form>
        <div class="register-link">
          ¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
