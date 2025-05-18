<?php
ob_start();
session_start();
include 'db.php';

require 'vendor/autoload.php';
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

// Configurar respuesta como JSON
header('Content-Type: application/json');

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Sesión no válida']);
    exit();
}

// Recoger datos del formulario
$user_id = $_SESSION['user_id'];
$dni = trim($_POST['dni'] ?? '');
$nombre_completo = trim($_POST['nombre_completo'] ?? '');
$foto = $_FILES['foto'] ?? null;
$enfermedad_actual = trim($_POST['enfermedad_actual'] ?? '');
$alergias = trim($_POST['alergias'] ?? '');
$seguro_medico = $_POST['seguro_medico'] ?? '';
$otro_seguro = trim($_POST['otro_seguro'] ?? '');
$tipo_sangre = $_POST['tipo_sangre'] ?? '';
$donador_org = $_POST['donador_org'] ?? '';
$fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';
$contacto_emergencia = trim($_POST['contacto_emergencia'] ?? '');
$parentesco_emergencia = trim($_POST['parentesco_emergencia'] ?? '');
$intervenciones_quirurgicas = trim($_POST['intervenciones_quirurgicas'] ?? '');
$direccion_actual = trim($_POST['direccion_actual'] ?? '');

// Inicializar respuesta
$response = ['success' => false, 'message' => ''];

// Validar campos requeridos
$required = [$dni, $nombre_completo, $seguro_medico, $tipo_sangre, $donador_org, $fecha_nacimiento, $contacto_emergencia, $parentesco_emergencia];
if (in_array('', $required, true)) {
    $response['message'] = 'Complete todos los campos obligatorios';
    echo json_encode($response);
    exit();
}

if ($seguro_medico === 'Otros' && empty($otro_seguro)) {
    $response['message'] = 'Especifique el nombre del seguro médico';
    echo json_encode($response);
    exit();
}

if (!$foto || $foto['error'] !== UPLOAD_ERR_OK) {
    $response['message'] = 'La foto es obligatoria';
    echo json_encode($response);
    exit();
}

// Validar tipo de imagen
$allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
if (!in_array($foto['type'], $allowed_types)) {
    $response['message'] = 'Formato de imagen no válido (solo JPEG, PNG o GIF)';
    echo json_encode($response);
    exit();
}

// Procesar imagen
$upload_dir = 'uploads/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$foto_ext = pathinfo($foto['name'], PATHINFO_EXTENSION);
$foto_name = uniqid('img_', true) . '.' . $foto_ext;
$foto_path = $upload_dir . $foto_name;

if (!move_uploaded_file($foto['tmp_name'], $foto_path)) {
    $response['message'] = 'Error al subir la imagen';
    echo json_encode($response);
    exit();
}

// Procesar seguro médico
if ($seguro_medico === 'Otros') {
    $seguro_medico = $otro_seguro;
}

// Insertar datos en la base de datos
try {
    $conn->begin_transaction();

    // Insertar persona
    $stmt = $conn->prepare(
        "INSERT INTO personas 
        (dni, nombre_completo, foto, enfermedad_actual, alergias, seguro_medico, tipo_sangre, donador_org, fecha_nacimiento, contacto_emergencia, parentesco_emergencia, intervenciones_quirurgicas, direccion_actual) 
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)"
    );
    
    $stmt->bind_param(
        "sssssssssssss",
        $dni,
        $nombre_completo,
        $foto_path,
        $enfermedad_actual,
        $alergias,
        $seguro_medico,
        $tipo_sangre,
        $donador_org,
        $fecha_nacimiento,
        $contacto_emergencia,
        $parentesco_emergencia,
        $intervenciones_quirurgicas,
        $direccion_actual
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Error al guardar persona: " . $stmt->error);
    }

    // Generar QR
    $qr_dir = 'qrcodes/';
    if (!file_exists($qr_dir)) {
        mkdir($qr_dir, 0777, true);
    }
    
    $url = "https://www.qrinfolock.iceiy.com/apk/view.php?dni=$dni";
    $qr = QrCode::create($url);
    $writer = new PngWriter();
    $qr_path = $qr_dir . $dni . '.png';
    $writer->write($qr)->saveToFile($qr_path);

    // Guardar QR en la base de datos
    $stmt2 = $conn->prepare("INSERT INTO qr_codes (user_id, dni, qr_path) VALUES (?,?,?)");
    $stmt2->bind_param("iss", $user_id, $dni, $qr_path);
    
    if (!$stmt2->execute()) {
        throw new Exception("Error al guardar QR: " . $stmt2->error);
    }

    $conn->commit();
    
    $response['success'] = true;
    $response['message'] = 'Registro completado exitosamente';
    $response['redirect'] = 'dashboard.php';

} catch (Exception $e) {
    $conn->rollback();
    
    // Eliminar archivos en caso de error
    if (isset($foto_path) && file_exists($foto_path)) unlink($foto_path);
    if (isset($qr_path) && file_exists($qr_path)) unlink($qr_path);
    
    $response['message'] = 'Error en el sistema: ' . $e->getMessage();
}

echo json_encode($response);
ob_end_flush();
?>