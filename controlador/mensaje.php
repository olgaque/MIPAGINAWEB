<?php

require_once __DIR__ . '/../modelo/auth.php';
require_once __DIR__ . '/../modelo/mensaje.php';

$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'guardar':
        
        header('Content-Type: application/json; charset=utf-8');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            exit;
        }

        $nombre  = trim($_POST['nombre'] ?? '');
        $correo  = trim($_POST['correo'] ?? '');
        $mensaje = trim($_POST['mensaje'] ?? '');

        if ($nombre === '' || $correo === '' || $mensaje === '') {
            http_response_code(400);
            echo json_encode(['error' => 'Faltan datos: nombre, correo y mensaje son obligatorios.']);
            exit;
        }

        $id = guardarMensaje($nombre, $correo, $mensaje);

        if ($id !== false) {
            http_response_code(201);
            echo json_encode(['ok' => true, 'id' => $id]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'No se pudo guardar el mensaje.']);
        }
        exit;

    case 'eliminar':
        
        requerirRol('administrador');
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar'])) {
            eliminarMensaje((int) $_POST['eliminar']);
        }
        header('Location: ../vista/dashboard.php#mensajes');
        exit;

    case 'marcar_leido':
        
        requerirRol('administrador');
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
            marcarMensajeLeido((int) $_POST['id']);
        }
        header('Location: ../vista/dashboard.php#mensajes');
        exit;

    default:
        http_response_code(400);
        echo 'Acción no válida o no proporcionada.';
        exit;
}
