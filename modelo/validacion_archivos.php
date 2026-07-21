<?php





const EXTENSIONES_ARCHIVO_PERMITIDAS = [
    'pdf'  => 'application/pdf',
    'doc'  => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'xls'  => 'application/vnd.ms-excel',
    'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'ppt'  => 'application/vnd.ms-powerpoint',
    'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png'  => 'image/png',
    'gif'  => 'image/gif',
    'txt'  => 'text/plain',
    'zip'  => 'application/zip',
    'rar'  => 'application/x-rar-compressed',
];

const MIME_REALES_BLOQUEADOS = [
    'text/html',
    'image/svg+xml',
    'application/x-httpd-php',
    'text/x-php',
    'application/javascript',
    'text/javascript',
];



function validarArchivoSubido($nombreOriginal, $rutaTemporal)
{
    $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
    if (!isset(EXTENSIONES_ARCHIVO_PERMITIDAS[$extension])) {
        return null;
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeReal = finfo_file($finfo, $rutaTemporal);
    finfo_close($finfo);

    if (in_array($mimeReal, MIME_REALES_BLOQUEADOS, true)) {
        return null;
    }

    return EXTENSIONES_ARCHIVO_PERMITIDAS[$extension];
}
