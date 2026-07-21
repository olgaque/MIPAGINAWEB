<?php


require_once __DIR__ . '/conexion.php';


function guardarMensaje($nombre, $correo, $mensaje)
{
    global $conexion;
    $sql = $conexion->prepare('INSERT INTO mensajes (nombre, correo, mensaje) VALUES (?, ?, ?)');
    $sql->bind_param('sss', $nombre, $correo, $mensaje);
    return $sql->execute() ? $conexion->insert_id : false;
}


function listarMensajes()
{
    global $conexion;
    $resultado = $conexion->query('SELECT * FROM mensajes ORDER BY fecha_envio DESC, id DESC');
    return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
}


function eliminarMensaje($id)
{
    global $conexion;
    $sql = $conexion->prepare('DELETE FROM mensajes WHERE id = ?');
    $sql->bind_param('i', $id);
    return $sql->execute();
}


function marcarMensajeLeido($id)
{
    global $conexion;
    $sql = $conexion->prepare('UPDATE mensajes SET leido = 1 WHERE id = ?');
    $sql->bind_param('i', $id);
    return $sql->execute();
}
