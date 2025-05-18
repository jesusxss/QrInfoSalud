<?php
session_start();
include 'db.php';

// Obtener DNI del GET (del registro a visualizar)
$dni_param = $_GET['dni'] ?? '';

// Función para verificar DNI con la API
function verifyDniWithApi($dni) {
    $url = 'https://qrinfolock.iceiy.com/apk/api-proxy.php';
    
    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query(['dni' => $dni]),
            'timeout' => 10
        ]
    ];
    
    $context = stream_context_create($options);
    $response = @file_get_contents($url, false, $context);
    
    if ($response === FALSE) {
        return ['success' => false, 'error' => 'No se pudo conectar al servicio de verificación'];
    }
    
    $data = json_decode($response, true);
    
    if (isset($data['error'])) {
        return ['success' => false, 'error' => $data['error']];
    }
    
    if (isset($data['prenombres'])) {
        return [
            'success' => true,
            'nombre_completo' => $data['prenombres']
        ];
    }
    
    return ['success' => false, 'error' => 'DNI no encontrado en el sistema'];
}

// Procesar formulario de acceso si se envió
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dni_ingresado'])) {
    $dni_ingresado = trim($_POST['dni_ingresado']);
    
    // Verificar si el DNI tiene 8 dígitos
    if (strlen($dni_ingresado) !== 8 || !ctype_digit($dni_ingresado)) {
        $error = "El DNI debe contener exactamente 8 números";
    } else {
        // Verificar el DNI mediante la API
        $api_response = verifyDniWithApi($dni_ingresado);
        
        if ($api_response['success']) {
            // Registrar el acceso en la base de datos
            $ip = $_SERVER['REMOTE_ADDR'];
            $hora = date('H:i:s');
            $fecha = date('Y-m-d');
            $cookies = json_encode($_COOKIE);
            $navegador = $_SERVER['HTTP_USER_AGENT'];
            $nombre_completo = $api_response['nombre_completo'] ?? 'Desconocido';
            
            $stmt = $conn->prepare(
                "INSERT INTO acceso_confidencial 
                (ip, dni, nombre_completo, hora, fecha, cookies, navegador, user_agent, dni_consultado) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param('sssssssss', $ip, $dni_ingresado, $nombre_completo, $hora, $fecha, $cookies, $navegador, $navegador, $dni_param);
            $stmt->execute();
            
            // Marcar como verificado en sesión
            $_SESSION['acceso_permitido_'.$dni_param] = true;
            $_SESSION['dni_visitante'] = $dni_ingresado;
            $_SESSION['nombre_visitante'] = $nombre_completo;
        } else {
            $error = $api_response['error'] ?? "Error al verificar el DNI";
        }
    }
}

// Verificar si el acceso ya fue autorizado
$mostrar_contenido = isset($_SESSION['acceso_permitido_'.$dni_param]);

// Si no está autorizado, mostrar pantalla de acceso
if (!$mostrar_contenido) {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Verificación de Acceso</title>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
        <style>
            :root {
                --primary-gradient: linear-gradient(135deg, #667eea, #764ba2);
                --bg-light: #f9fafb;
                --text-color: #1f2937;
                --error-color: #e53e3e;
                --success-color: #38a169;
            }
            body {
                margin: 0;
                padding: 0;
                font-family: 'Segoe UI', sans-serif;
                background: var(--bg-light);
                color: var(--text-color);
            }
            .access-container {
                max-width: 400px;
                margin: 0 auto;
                padding: 2rem;
                text-align: center;
            }
            .access-card {
                background: white;
                border-radius: 12px;
                padding: 2rem;
                box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            }
            .access-icon {
                font-size: 3rem;
                color: #667eea;
                margin-bottom: 1rem;
            }
            .access-title {
                font-size: 1.5rem;
                margin-bottom: 1rem;
                color: #1a365d;
            }
            .access-text {
                margin-bottom: 2rem;
                line-height: 1.6;
            }
            .form-group {
                margin-bottom: 1.5rem;
                text-align: left;
            }
            .form-label {
                display: block;
                margin-bottom: 0.5rem;
                font-weight: 500;
            }
            .form-control {
                width: 100%;
                padding: 0.75rem;
                border: 1px solid #e2e8f0;
                border-radius: 8px;
                font-size: 1rem;
            }
            .btn-access {
                background: var(--primary-gradient);
                color: white;
                border: none;
                border-radius: 8px;
                padding: 0.75rem 1.5rem;
                font-size: 1rem;
                font-weight: 500;
                cursor: pointer;
                width: 100%;
                transition: opacity 0.3s;
            }
            .btn-access:hover {
                opacity: 0.9;
            }
            .error-message {
                color: var(--error-color);
                margin-top: 0.5rem;
                font-size: 0.9rem;
            }
            .success-message {
                color: var(--success-color);
                margin-top: 0.5rem;
                font-size: 0.9rem;
            }
            .checkbox-group {
                display: flex;
                align-items: center;
                margin-bottom: 1.5rem;
            }
            .checkbox-group input {
                margin-right: 0.5rem;
            }
            .loading {
                display: inline-block;
                width: 20px;
                height: 20px;
                border: 3px solid rgba(255,255,255,.3);
                border-radius: 50%;
                border-top-color: #fff;
                animation: spin 1s ease-in-out infinite;
                margin-right: 10px;
            }
            @keyframes spin {
                to { transform: rotate(360deg); }
            }
        </style>
    </head>
    <body>
        <div class="access-container">
            <div class="access-card">
                <i class="fas fa-user-shield access-icon"></i>
                <h1 class="access-title">Aviso de Confianza: Usted está a punto de acceder a información sensible.</h1>
    
    <div class="access-text">
        <p style="text-align: justify; margin: 1em 0;">
            Este sistema está diseñado para ayudar en la devolución de objetos perdidos y brindar asistencia en situaciones de emergencia(accidentes). Al ingresar tu DNI, aceptas que esta información será utilizada exclusivamente para estos fines humanitarios.
            Nos comprometemos a manejar esta información con responsabilidad, ética y total confidencialidad, en cumplimiento con la <strong>Ley de Protección de Datos Personales (Ley N° 29733)</strong> del Perú.
        </p>
        
        <p style="text-align: left; margin: 1.2em 0 0.5em 0;">Al continuar, aceptas que:</p>
        
        <ul style="
            margin: 0.5em 0 1em 0;
            padding-left: 1.5em;
            text-align: left;
        ">
            <li style="margin-bottom: 0.5em;">Te comprometes a hacer un uso ético y responsable de la información a la que tengas acceso</li>
            <li style="margin-bottom: 0.5em;">Tu DNI será utilizado exclusivamente para fines de identificación dentro de la app</li>
        </ul>
        
        <p style="
            text-align: justify;
            margin: 1.5em 0 0 0;
            padding: 0.8em;
            background-color: #f8f9fa;
            border-left: 3px solid #667eea;
            font-size: 0.9em;
        ">
            <strong>Nota legal:</strong> Este sistema cumple con los principios de la Ley de Protección de Datos Personales: licitud, consentimiento, finalidad, proporcionalidad, calidad, seguridad y disposición de recurso.
        </p>
    </div>
                <form method="POST" id="accessForm">
                    <div class="form-group">
                        <label for="dni_ingresado" class="form-label">Su Número de DNI</label>
                        <input type="text" id="dni_ingresado" name="dni_ingresado" 
                               class="form-control" required maxlength="8" pattern="\d{8}"
                               placeholder="Ingrese su DNI de 8 dígitos">
                        <?php if (isset($error)): ?>
                            <div class="error-message"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        <div id="nombreVerificado" class="success-message" style="display:none;"></div>
                    </div>
                    
                    <div class="checkbox-group">
                        <input type="checkbox" id="acepto" name="acepto" required>
                        <label for="acepto">Acepto los términos de confidencialidad</label>
                    </div>
                    
                    <button type="submit" class="btn-access" id="submitBtn">
                        <span id="btnText">Verificar y Acceder</span>
                        <span id="btnSpinner" style="display:none;">
                            <span class="loading"></span> Verificando...
                        </span>
                    </button>
                </form>
            </div>
        </div>

        <script>
            document.getElementById('dni_ingresado').addEventListener('input', function() {
                const dni = this.value.trim();
                if (dni.length === 8 && /^\d+$/.test(dni)) {
                    // Mostrar spinner
                    document.getElementById('btnText').style.display = 'none';
                    document.getElementById('btnSpinner').style.display = 'inline';
                    document.getElementById('nombreVerificado').style.display = 'none';
                    
                    // Verificar DNI con la API
                    fetch('https://qrinfolock.iceiy.com/apk/api-proxy.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({ dni })
                    })
                    .then(res => {
                        if (!res.ok) throw new Error(res.statusText);
                        return res.json();
                    })
                    .then(data => {
                        if (data.prenombres) {
                            document.getElementById('nombreVerificado').textContent = 
                                'Verificado: ' + data.prenombres;
                            document.getElementById('nombreVerificado').style.display = 'block';
                        } else if (data.error) {
                            throw new Error(data.error);
                        } else {
                            throw new Error('DNI no reconocido');
                        }
                    })
                    .catch(err => {
                        console.error('Error al verificar DNI:', err);
                        document.getElementById('nombreVerificado').style.display = 'none';
                    })
                    .finally(() => {
                        document.getElementById('btnText').style.display = 'inline';
                        document.getElementById('btnSpinner').style.display = 'none';
                    });
                } else {
                    document.getElementById('nombreVerificado').style.display = 'none';
                }
            });
        </script>
    </body>
    </html>
    <?php
    exit();
}

// Si llegamos aquí, el acceso está autorizado - Mostrar el contenido
$sql = "SELECT * FROM personas WHERE dni = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $dni_param);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

// Obtener información del visitante desde la sesión
$dni_visitante = $_SESSION['dni_visitante'] ?? 'Desconocido';
$nombre_visitante = $_SESSION['nombre_visitante'] ?? 'Visitante';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Información de Persona</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea, #764ba2);
            --bg-light: #f9fafb;
            --text-color: #1f2937;
        }
        .container-mobile {
            padding: 1rem;
            max-width: 400px;
            margin: 0 auto;
            background: var(--bg-light);
        }
        .card-custom {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 1rem;
        }
        .card-header-custom {
            background: var(--primary-gradient);
            text-align: center;
            padding: 1rem;
            color: white;
        }
        .card-body-custom {
            padding: 1rem;
        }
        .list-group-item {
            border: none;
            padding: 0.75rem 1rem;
            font-size: 0.9rem;
            color: var(--text-color);
        }
        .list-group-item + .list-group-item {
            border-top: 1px solid #e5e7eb;
        }
        .label-bold {
            font-weight: 600;
        }
        .img-preview {
            display: block;
            width: 100%;
            max-height: 300px;
            object-fit: cover;
            border-radius: 8px;
            margin: 0.5rem 0 1rem;
        }
        .qr-preview {
            display: block;
            width: 60%;
            max-width: 200px;
            margin: 1rem auto 0;
        }
        .btn-action {
            background: var(--primary-gradient);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            font-weight: 500;
            width: 100%;
            transition: opacity 0.3s;
            margin-top: 1rem;
        }
        .btn-action:hover {
            opacity: 0.9;
        }
        .access-info {
            background: #f0f4f8;
            padding: 0.75rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container-mobile">
        <div class="card-custom">
            <div class="card-header-custom">
                <h2>Información de Persona</h2>
            </div>
            <div class="card-body-custom">
                <div class="access-info">
                    <i class="fas fa-user-check"></i> Accediendo como: <?= htmlspecialchars($nombre_visitante) ?> (DNI: <?= htmlspecialchars($dni_visitante) ?>)
                </div>
                
                <?php if (!$data): ?>
                    <p class="label-bold text-center">
                        No se encontró persona con DNI <?= htmlspecialchars($dni_param) ?>
                    </p>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <span class="label-bold">DNI:</span> <?= htmlspecialchars($data['dni']) ?>
                        </li>
                        <li class="list-group-item">
                            <span class="label-bold">Foto:</span>
                            <img src="<?= htmlspecialchars($data['foto']) ?>"
                                alt="Foto de <?= htmlspecialchars($data['nombre_completo']) ?>"
                                class="img-preview">
                        </li>
                        <li class="list-group-item">
                            <span class="label-bold">Nombre:</span> <?= htmlspecialchars($data['nombre_completo']) ?>
                        </li>
                        <li class="list-group-item">
                            <span class="label-bold">Nacimiento:</span> <?= htmlspecialchars($data['fecha_nacimiento']) ?>
                        </li>
                        <li class="list-group-item">
                            <span class="label-bold">Tipo de Sangre:</span> <?= htmlspecialchars($data['tipo_sangre']) ?>
                        </li>
                        <li class="list-group-item">
                            <span class="label-bold">Alergias:</span> <?= htmlspecialchars($data['alergias']) ?>
                        </li>
                        <li class="list-group-item">
                            <span class="label-bold">Enfermedad:</span> <?= htmlspecialchars($data['enfermedad_actual']) ?>
                        </li>
                        <li class="list-group-item">
                            <span class="label-bold">Seguro Médico:</span> <?= htmlspecialchars($data['seguro_medico']) ?>
                        </li>
                        <li class="list-group-item">
                            <span class="label-bold">Intervenciones quirurgicas:</span> <?= htmlspecialchars($data['intervenciones_quirurgicas']) ?>
                        </li>
                        <li class="list-group-item">
                            <span class="label-bold">Donador de Órganos:</span> <?= htmlspecialchars($data['donador_org']) ?>
                        </li>
                        <li class="list-group-item">
                            <span class="label-bold">Contacto Emergencia:</span> <?= htmlspecialchars($data['contacto_emergencia']) ?>
                        </li>
                        <li class="list-group-item">
                            <span class="label-bold">Parentesco:</span> <?= htmlspecialchars($data['parentesco_emergencia']) ?>
                        </li>
                        <li class="list-group-item">
                            <span class="label-bold">Dirección Actual:</span> <?= htmlspecialchars($data['direccion_actual']) ?>
                        </li>
                    </ul>
                    <img src="qrcodes/<?= urlencode($dni_param) ?>.png"
                        alt="QR de <?= htmlspecialchars($dni_param) ?>"
                        class="qr-preview">
                <?php endif; ?>
               <!-- <button onclick="history.back()" class="btn-action">Volver</button>-->
            </div>
        </div>
    </div>
</body>
</html>