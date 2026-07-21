<?php


require_once __DIR__ . '/conexion.php';

const CATEGORIAS_BIBLIOTECA_PERMITIDAS = ['Guía de estudio', 'Material digital', 'Recurso recomendado'];


function guardarArchivoBiblioteca($titulo, $descripcion, $categoria, $nombreArchivo, $tipoMime, $tamanoBytes, $contenido)
{
    global $conexion;
    $sql = $conexion->prepare('INSERT INTO biblioteca (titulo, descripcion, categoria, nombre_archivo, tipo_mime, tamano_bytes, contenido) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $sql->bind_param('sssssis', $titulo, $descripcion, $categoria, $nombreArchivo, $tipoMime, $tamanoBytes, $contenido);
    return $sql->execute() ? $conexion->insert_id : false;
}


function listarArchivosBiblioteca()
{
    global $conexion;
    $resultado = $conexion->query(
        'SELECT id, titulo, descripcion, categoria, nombre_archivo, tipo_mime, tamano_bytes, fecha_subida
         FROM biblioteca ORDER BY fecha_subida DESC, id DESC'
    );
    return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
}


function obtenerArchivoBiblioteca($id)
{
    global $conexion;
    $sql = $conexion->prepare('SELECT nombre_archivo, tipo_mime, contenido FROM biblioteca WHERE id = ?');
    $sql->bind_param('i', $id);
    $sql->execute();
    $resultado = $sql->get_result();
    return $resultado ? $resultado->fetch_assoc() : null;
}


function eliminarArchivoBiblioteca($id)
{
    global $conexion;
    $sql = $conexion->prepare('DELETE FROM biblioteca WHERE id = ?');
    $sql->bind_param('i', $id);
    return $sql->execute();
}


function formatearTamano($bytes)
{
    if ($bytes >= 1048576) {
        return round($bytes / 1048576, 1) . ' MB';
    }
    if ($bytes >= 1024) {
        return round($bytes / 1024, 1) . ' KB';
    }
    return $bytes . ' B';
}
