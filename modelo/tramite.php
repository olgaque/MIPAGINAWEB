<?php

require_once __DIR__ . '/conexion.php';

const TIPOS_TRAMITE_PERMITIDOS = [
    'Constancia de matrícula',
    'Constancia de notas',
    'Certificado de estudios',
    'Duplicado de carné',
    'Traslado interno',
    'Retiro / reserva de matrícula',
    'Solicitud de práctica pre-profesional',
    'Convalidación de cursos',
    'Otro',
];

const AREAS_DESTINO_PERMITIDAS = [
    'Por asignar',
    'Secretaría académica',
    'Coordinación académica',
    'Bienestar estudiantil',
    'Tesorería',
    'Dirección general',
];

const ESTADOS_TRAMITE_PERMITIDOS = [
    'pendiente' => 'Pendiente',
    'proceso'   => 'En proceso',
    'atendido'  => 'Atendido',
    'rechazado' => 'Rechazado',
];


function guardarTramite($solicitante, $tipoTramite, $areaDestino, $detalle)
{
    global $conexion;
    $sql = $conexion->prepare('INSERT INTO tramites (solicitante, tipo_tramite, area_destino, detalle) VALUES (?, ?, ?, ?)');
    $sql->bind_param('ssss', $solicitante, $tipoTramite, $areaDestino, $detalle);
    return $sql->execute() ? $conexion->insert_id : false;
}


function guardarTramitePublico($idUsuario, $solicitante, $correo, $telefono, $tipoTramite, $detalle, $documentoNombre, $documentoMime, $documentoTamano, $documentoContenido)
{
    global $conexion;
    $sql = $conexion->prepare(
        "INSERT INTO tramites (id_usuario, solicitante, correo, telefono, tipo_tramite, detalle, area_destino, origen, documento_nombre, documento_mime, documento_tamano, documento_contenido)
         VALUES (?, ?, ?, ?, ?, ?, 'Por asignar', 'publico', ?, ?, ?, ?)"
    );
    $sql->bind_param('isssssssis', $idUsuario, $solicitante, $correo, $telefono, $tipoTramite, $detalle, $documentoNombre, $documentoMime, $documentoTamano, $documentoContenido);
    return $sql->execute() ? $conexion->insert_id : false;
}


function listarTramites()
{
    global $conexion;
    $resultado = $conexion->query(
        'SELECT id, solicitante, correo, telefono, tipo_tramite, detalle, area_destino, origen, estado,
                documento_nombre, documento_mime, documento_tamano, fecha_registro
         FROM tramites ORDER BY fecha_registro DESC, id DESC'
    );
    return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
}


function listarTramitesPorUsuario($idUsuario)
{
    global $conexion;
    $sql = $conexion->prepare(
        'SELECT id, tipo_tramite, detalle, area_destino, estado, documento_nombre, fecha_registro
         FROM tramites WHERE id_usuario = ? ORDER BY fecha_registro DESC, id DESC'
    );
    $sql->bind_param('i', $idUsuario);
    $sql->execute();
    $resultado = $sql->get_result();
    return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
}


function obtenerDocumentoTramite($id)
{
    global $conexion;
    $sql = $conexion->prepare('SELECT id_usuario, documento_nombre, documento_mime, documento_contenido FROM tramites WHERE id = ? AND documento_contenido IS NOT NULL');
    $sql->bind_param('i', $id);
    $sql->execute();
    $resultado = $sql->get_result();
    return $resultado ? $resultado->fetch_assoc() : null;
}


function actualizarEstadoTramite($id, $estado)
{
    global $conexion;
    $sql = $conexion->prepare('UPDATE tramites SET estado = ? WHERE id = ?');
    $sql->bind_param('si', $estado, $id);
    return $sql->execute();
}


function actualizarAreaTramite($id, $area)
{
    global $conexion;
    $sql = $conexion->prepare('UPDATE tramites SET area_destino = ? WHERE id = ?');
    $sql->bind_param('si', $area, $id);
    return $sql->execute();
}


function obtenerTramitePorId($id)
{
    global $conexion;
    $sql = $conexion->prepare(
        'SELECT id, solicitante, correo, telefono, tipo_tramite, detalle, area_destino, origen, estado, documento_nombre, fecha_registro
         FROM tramites WHERE id = ?'
    );
    $sql->bind_param('i', $id);
    $sql->execute();
    $resultado = $sql->get_result();
    return $resultado ? $resultado->fetch_assoc() : null;
}


function eliminarTramite($id)
{
    global $conexion;
    $sql = $conexion->prepare('DELETE FROM tramites WHERE id = ?');
    $sql->bind_param('i', $id);
    return $sql->execute();
}

