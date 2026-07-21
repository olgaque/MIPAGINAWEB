<?php

require_once __DIR__ . '/usuario.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function iniciarSesion($correo, $clave)
{
    $usuario = buscarUsuarioPorCorreo($correo);
    if (!$usuario || !password_verify($clave, $usuario['clave_hash'])) {
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['usuario_rol'] = $usuario['rol'];
    $_SESSION['usuario_nombre'] = $usuario['nombre'];

    unset($usuario['clave_hash']);
    return $usuario;
}

function estaAutenticado()
{
    return isset($_SESSION['usuario_id']);
}

function rolActual()
{
    return $_SESSION['usuario_rol'] ?? null;
}

function idUsuarioActual()
{
    return $_SESSION['usuario_id'] ?? null;
}


function requerirRol($rol)
{
    if (!estaAutenticado() || rolActual() !== $rol) {
        header('Location: ../index.html');
        exit;
    }
}

function cerrarSesionUsuario()
{
    $_SESSION = [];
    session_destroy();
}