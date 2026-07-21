<?php

require_once __DIR__ . '/../modelo/auth.php';
require_once __DIR__ . '/../modelo/usuario.php';

requerirRol('administrador');

$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'crear':
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre = trim($_POST['nombre'] ?? '');
            $correo = trim($_POST['correo'] ?? '');
            $telefono = trim($_POST['telefono'] ?? '');
            $clave = $_POST['clave'] ?? '';
            $rol = $_POST['rol'] ?? '';

            if (
                $nombre !== ''
                && filter_var($correo, FILTER_VALIDATE_EMAIL)
                && strlen($clave) >= 6
                && in_array($rol, ['cliente', 'administrador'], true)
                && !existeCorreo($correo)
            ) {
                crearUsuario($nombre, $correo, $telefono ?: null, $clave, $rol);
            }
        }
        header('Location: ../vista/dashboard.php#usuarios');
        exit;

    case 'actualizar_rol':
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['rol'])) {
            $id = (int) $_POST['id'];
            $rol = $_POST['rol'];

            if (in_array($rol, ['cliente', 'administrador'], true) && $id !== idUsuarioActual()) {
                actualizarRolUsuario($id, $rol);
            }
        }
        header('Location: ../vista/dashboard.php#usuarios');
        exit;

    case 'eliminar':
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar'])) {
            $id = (int) $_POST['eliminar'];
            if ($id !== idUsuarioActual()) {
                eliminarUsuario($id);
            }
        }
        header('Location: ../vista/dashboard.php#usuarios');
        exit;

    default:
        http_response_code(400);
        echo 'Acción no válida o no proporcionada.';
        exit;
}