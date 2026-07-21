<?php

require_once __DIR__ . '/../modelo/auth.php';
require_once __DIR__ . '/../modelo/tramite.php';
require_once __DIR__ . '/../modelo/validacion_archivos.php';

$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'guardar':
        
        requerirRol('administrador');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $solicitante = trim($_POST['solicitante'] ?? '');
            $tipoTramite = trim($_POST['tipo_tramite'] ?? '');
            $areaDestino = trim($_POST['area_destino'] ?? '');
            $detalle = trim($_POST['detalle'] ?? '');

            if (
                $solicitante !== ''
                && in_array($tipoTramite, TIPOS_TRAMITE_PERMITIDOS, true)
                && in_array($areaDestino, AREAS_DESTINO_PERMITIDAS, true)
            ) {
                guardarTramite($solicitante, $tipoTramite, $areaDestino, $detalle);
            }
        }
        header('Location: ../vista/dashboard.php#mesa-partes');
        exit;

    case 'guardar_publico':
        
        header('Content-Type: application/json; charset=utf-8');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            exit;
        }

        $idUsuario = null;
        $solicitante = '';
        $correo = '';
        $telefono = null;

        if (estaAutenticado() && rolActual() === 'cliente') {
            $usuario = obtenerUsuarioPorId(idUsuarioActual());
            if ($usuario) {
                $idUsuario = $usuario['id'];
                $solicitante = $usuario['nombre'];
                $correo = $usuario['correo'];
                $telefono = $usuario['telefono'];
            }
        }

        if ($idUsuario === null) {
            $solicitante = trim($_POST['solicitante'] ?? '');
            $correo = trim($_POST['correo'] ?? '');
            $telefono = trim($_POST['telefono'] ?? '');
            
            if (empty($solicitante) || empty($correo)) {
                http_response_code(400);
                echo json_encode(['error' => 'Debes completar tu nombre completo y correo electrónico para enviar el trámite.']);
                exit;
            }
            
            if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                echo json_encode(['error' => 'El formato del correo electrónico no es válido.']);
                exit;
            }
            
            if (empty($telefono)) {
                $telefono = null;
            }
        }

        $tipoTramite = trim($_POST['tipo_tramite'] ?? '');
        $detalle     = trim($_POST['detalle'] ?? '');

        if (!in_array($tipoTramite, TIPOS_TRAMITE_PERMITIDOS, true)) {
            http_response_code(400);
            echo json_encode(['error' => 'El tipo de trámite seleccionado no es válido.']);
            exit;
        }

        $documentoNombre = null;
        $documentoMime = null;
        $documentoTamano = null;
        $documentoContenido = null;
        $tamanoMaximo = 15 * 1024 * 1024; 

        if (isset($_FILES['documento']) && $_FILES['documento']['error'] !== UPLOAD_ERR_NO_FILE) {
            $documento = $_FILES['documento'];

            if ($documento['error'] !== UPLOAD_ERR_OK || $documento['size'] <= 0 || $documento['size'] > $tamanoMaximo) {
                http_response_code(400);
                echo json_encode(['error' => 'El documento no es válido o supera los 15 MB permitidos.']);
                exit;
            }

            $documentoMime = validarArchivoSubido($documento['name'], $documento['tmp_name']);
            if ($documentoMime === null) {
                http_response_code(400);
                echo json_encode(['error' => 'El tipo de documento no está permitido.']);
                exit;
            }

            $documentoNombre = $documento['name'];
            $documentoTamano = $documento['size'];
            $documentoContenido = file_get_contents($documento['tmp_name']);
        }

        $id = guardarTramitePublico(
            $idUsuario,
            $solicitante,
            $correo,
            $telefono,
            $tipoTramite,
            $detalle,
            $documentoNombre,
            $documentoMime,
            $documentoTamano,
            $documentoContenido
        );

        if ($id !== false) {
            $codigo = 'MP-' . date('Y') . '-' . str_pad($id, 4, '0', STR_PAD_LEFT);
            http_response_code(201);
            echo json_encode(['ok' => true, 'id' => $id, 'codigo' => $codigo]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'No se pudo registrar el trámite.']);
        }
        exit;

    case 'consultar':
        
        header('Content-Type: application/json; charset=utf-8');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            exit;
        }

        $codigo = trim($_POST['codigo'] ?? '');
        $correo = trim($_POST['correo'] ?? '');

        if (empty($codigo) || empty($correo)) {
            http_response_code(400);
            echo json_encode(['error' => 'Debes ingresar el código del trámite y el correo electrónico.']);
            exit;
        }

        if (!preg_match('/^MP-\d{4}-(\d+)$/i', $codigo, $matches)) {
            http_response_code(400);
            echo json_encode(['error' => 'El formato del código de trámite no es válido. Debe ser similar a MP-2026-0001.']);
            exit;
        }

        $idTramite = (int) $matches[1];
        $tramite = obtenerTramitePorId($idTramite);

        if (!$tramite) {
            http_response_code(404);
            echo json_encode(['error' => 'No se encontró ningún trámite con el código proporcionado.']);
            exit;
        }

        if (strcasecmp($tramite['correo'], $correo) !== 0) {
            http_response_code(403);
            echo json_encode(['error' => 'El correo electrónico no coincide con el registrado para este trámite.']);
            exit;
        }

        $estadoLegible = ESTADOS_TRAMITE_PERMITIDOS[$tramite['estado']] ?? $tramite['estado'];
        $fechaFormateada = date('d/m/Y H:i', strtotime($tramite['fecha_registro']));

        echo json_encode([
            'ok' => true,
            'tramite' => [
                'codigo' => $codigo,
                'solicitante' => $tramite['solicitante'],
                'tipo_tramite' => $tramite['tipo_tramite'],
                'detalle' => $tramite['detalle'],
                'area_destino' => $tramite['area_destino'],
                'estado' => $tramite['estado'],
                'estado_legible' => $estadoLegible,
                'fecha' => $fechaFormateada,
                'documento' => $tramite['documento_nombre']
            ]
        ]);
        exit;

    case 'actualizar_area':
        
        requerirRol('administrador');
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['area_destino'])) {
            $area = $_POST['area_destino'];
            if (in_array($area, AREAS_DESTINO_PERMITIDAS, true)) {
                actualizarAreaTramite((int) $_POST['id'], $area);
            }
        }
        header('Location: ../vista/dashboard.php#mesa-partes');
        exit;

    case 'actualizar_estado':
        
        requerirRol('administrador');
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['estado'])) {
            $estado = $_POST['estado'];
            if (array_key_exists($estado, ESTADOS_TRAMITE_PERMITIDOS)) {
                actualizarEstadoTramite((int) $_POST['id'], $estado);
            }
        }
        header('Location: ../vista/dashboard.php#mesa-partes');
        exit;

    case 'eliminar':
        
        requerirRol('administrador');
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar'])) {
            eliminarTramite((int) $_POST['eliminar']);
        }
        header('Location: ../vista/dashboard.php#mesa-partes');
        exit;

    case 'descargar_documento':
        
        if (!estaAutenticado()) {
            header('Location: ../index.html');
            exit;
        }

        $id = (int) ($_GET['id'] ?? 0);
        $documento = $id > 0 ? obtenerDocumentoTramite($id) : null;

        if (!$documento) {
            http_response_code(404);
            echo 'Documento no encontrado.';
            exit;
        }

        $esDueno = rolActual() === 'cliente' && (int) $documento['id_usuario'] === idUsuarioActual();
        if (rolActual() !== 'administrador' && !$esDueno) {
            http_response_code(403);
            echo 'No tienes permiso para ver este documento.';
            exit;
        }

        $disposicion = isset($_GET['ver']) ? 'inline' : 'attachment';

        header('Content-Type: ' . $documento['documento_mime']);
        header('Content-Disposition: ' . $disposicion . '; filename="' . basename($documento['documento_nombre']) . '"');
        header('Content-Length: ' . strlen($documento['documento_contenido']));
        echo $documento['documento_contenido'];
        exit;

    default:
        http_response_code(400);
        echo 'Acción no válida o no proporcionada.';
        exit;
}
