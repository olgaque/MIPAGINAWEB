<?php

require_once __DIR__ . '/../modelo/auth.php';
require_once __DIR__ . '/../modelo/biblioteca.php';
require_once __DIR__ . '/../modelo/validacion_archivos.php';

$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'guardar':
        
        requerirRol('administrador');
        $tamanoMaximo = 15 * 1024 * 1024; 

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['archivo'])) {
            $titulo = trim($_POST['titulo'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $categoria = trim($_POST['categoria'] ?? '');
            if (!in_array($categoria, CATEGORIAS_BIBLIOTECA_PERMITIDAS, true)) {
                $categoria = 'Material digital';
            }
            $archivo = $_FILES['archivo'];

            if (
                $titulo !== ''
                && $archivo['error'] === UPLOAD_ERR_OK
                && $archivo['size'] > 0
                && $archivo['size'] <= $tamanoMaximo
            ) {
                $tipoMime = validarArchivoSubido($archivo['name'], $archivo['tmp_name']);
                if ($tipoMime !== null) {
                    $contenido = file_get_contents($archivo['tmp_name']);
                    guardarArchivoBiblioteca(
                        $titulo,
                        $descripcion,
                        $categoria,
                        $archivo['name'],
                        $tipoMime,
                        $archivo['size'],
                        $contenido
                    );
                }
            }
        }
        header('Location: ../vista/dashboard.php#biblioteca');
        exit;

    case 'eliminar':
        
        requerirRol('administrador');
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar'])) {
            eliminarArchivoBiblioteca((int) $_POST['eliminar']);
        }
        header('Location: ../vista/dashboard.php#biblioteca');
        exit;

    case 'descargar':
        
        $id = (int) ($_GET['id'] ?? 0);
        $archivo = $id > 0 ? obtenerArchivoBiblioteca($id) : null;

        if (!$archivo) {
            http_response_code(404);
            echo 'Archivo no encontrado.';
            exit;
        }

        $disposicion = isset($_GET['ver']) ? 'inline' : 'attachment';

        header('Content-Type: ' . $archivo['tipo_mime']);
        header('Content-Disposition: ' . $disposicion . '; filename="' . basename($archivo['nombre_archivo']) . '"');
        header('Content-Length: ' . strlen($archivo['contenido']));
        echo $archivo['contenido'];
        exit;

    case 'listar':
        
        header('Content-Type: application/json; charset=utf-8');
        $archivos = listarArchivosBiblioteca();

        $datos = array_map(function ($a) {
            return [
                'id'          => (int) $a['id'],
                'titulo'      => $a['titulo'],
                'descripcion' => $a['descripcion'],
                'categoria'   => $a['categoria'],
                'tamano'      => formatearTamano($a['tamano_bytes']),
                'ver'         => 'controlador/biblioteca.php?action=descargar&id=' . $a['id'] . '&ver=1',
                'descarga'    => 'controlador/biblioteca.php?action=descargar&id=' . $a['id'],
            ];
        }, $archivos);

        echo json_encode($datos);
        exit;

    default:
        http_response_code(400);
        echo 'Acción no válida o no proporcionada.';
        exit;
}
