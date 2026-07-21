<?php

require_once __DIR__ . '/conexion.php';


function registrarCliente($nombre, $correo, $telefono, $clave)
{
    global $conexion;
    $claveHash = password_hash($clave, PASSWORD_DEFAULT);
    $sql = $conexion->prepare("INSERT INTO usuarios (nombre, correo, telefono, clave_hash, rol) VALUES (?, ?, ?, ?, 'cliente')");
    $sql->bind_param('ssss', $nombre, $correo, $telefono, $claveHash);
    return $sql->execute() ? $conexion->insert_id : false;
}


function buscarUsuarioPorCorreo($correo)
{
    global $conexion;
    $sql = $conexion->prepare('SELECT id, nombre, correo, telefono, clave_hash, rol FROM usuarios WHERE correo = ?');
    $sql->bind_param('s', $correo);
    $sql->execute();
    $resultado = $sql->get_result();
    return $resultado ? $resultado->fetch_assoc() : null;
}


function obtenerUsuarioPorId($id)
{
    global $conexion;
    $sql = $conexion->prepare('SELECT id, nombre, correo, telefono, rol FROM usuarios WHERE id = ?');
    $sql->bind_param('i', $id);
    $sql->execute();
    $resultado = $sql->get_result();
    return $resultado ? $resultado->fetch_assoc() : null;
}

function existeCorreo($correo)
{
    return buscarUsuarioPorCorreo($correo) !== null;
}


function listarUsuarios()
{
    global $conexion;
    $resultado = $conexion->query('SELECT id, nombre, correo, telefono, rol, fecha_registro FROM usuarios ORDER BY fecha_registro DESC, id DESC');
    return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
}


function contarUsuariosPorRol($rol)
{
    global $conexion;
    $sql = $conexion->prepare('SELECT COUNT(*) AS total FROM usuarios WHERE rol = ?');
    $sql->bind_param('s', $rol);
    $sql->execute();
    $resultado = $sql->get_result();
    return $resultado ? (int) $resultado->fetch_assoc()['total'] : 0;
}


function crearUsuario($nombre, $correo, $telefono, $clave, $rol)
{
    global $conexion;
    $claveHash = password_hash($clave, PASSWORD_DEFAULT);
    $sql = $conexion->prepare('INSERT INTO usuarios (nombre, correo, telefono, clave_hash, rol) VALUES (?, ?, ?, ?, ?)');
    $sql->bind_param('sssss', $nombre, $correo, $telefono, $claveHash, $rol);
    return $sql->execute() ? $conexion->insert_id : false;
}


function actualizarRolUsuario($id, $rol)
{
    global $conexion;
    $sql = $conexion->prepare('UPDATE usuarios SET rol = ? WHERE id = ?');
    $sql->bind_param('si', $rol, $id);
    return $sql->execute();
}


function eliminarUsuario($id)
{
    global $conexion;
    $sql = $conexion->prepare('DELETE FROM usuarios WHERE id = ?');
    $sql->bind_param('i', $id);
    return $sql->execute();
}
