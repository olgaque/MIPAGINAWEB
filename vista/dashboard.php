<?php


require_once __DIR__ . '/../modelo/auth.php';
requerirRol('administrador');

require_once __DIR__ . '/../modelo/mensaje.php';
require_once __DIR__ . '/../modelo/tramite.php';
require_once __DIR__ . '/../modelo/biblioteca.php';
require_once __DIR__ . '/../modelo/usuario.php';

$mensajes = listarMensajes();
$tramites = listarTramites();
$archivos = listarArchivosBiblioteca();
$usuarios = listarUsuarios();
$totalAdministradores = contarUsuariosPorRol('administrador');
$totalClientes = contarUsuariosPorRol('cliente');

$pendientes = count(array_filter($tramites, fn($t) => $t['estado'] === 'pendiente'));
$enProceso  = count(array_filter($tramites, fn($t) => $t['estado'] === 'proceso'));
$atendidos  = count(array_filter($tramites, fn($t) => $t['estado'] === 'atendido'));
$rechazados = count(array_filter($tramites, fn($t) => $t['estado'] === 'rechazado'));

$mensajesNoLeidos = count(array_filter($mensajes, fn($m) => (int) $m['leido'] === 0));

function iconoArchivo($nombreArchivo)
{
    $iconos = [
        'pdf' => '📕',
        'doc' => '📄', 'docx' => '📄',
        'xls' => '📊', 'xlsx' => '📊',
        'ppt' => '📈', 'pptx' => '📈',
        'zip' => '🗜️', 'rar' => '🗜️',
        'jpg' => '🖼️', 'jpeg' => '🖼️', 'png' => '🖼️', 'gif' => '🖼️',
        'mp4' => '🎬', 'avi' => '🎬', 'mov' => '🎬',
        'mp3' => '🎵', 'wav' => '🎵',
    ];
    $ext = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));
    return $iconos[$ext] ?? '📁';
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | ASPTI</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --color-brand: #b30000;
            --color-brand-deep: #7a0000;
            --color-ink: #231e1c;
            --color-body: #4a4340;
            --color-bg: #f7f5f3;
            --color-surface: #ffffff;
            --color-border: #e9e2dd;
            --radius-sm: 8px;
            --radius-md: 16px;
            --shadow-sm: 0 2px 10px rgba(70, 15, 10, .08);
            --sidebar-w: 260px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--color-bg);
            color: var(--color-body);
            min-height: 100vh;
        }

        .layout {
            display: flex;
            min-height: 100vh;
        }

        /* ---------- Sidebar ---------- */
        .sidebar {
            width: var(--sidebar-w);
            flex-shrink: 0;
            background: var(--color-brand);
            color: #fff;
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 20;
            transition: transform .25s var(--ease, ease);
        }

        .sidebar-marca {
            padding: 24px 22px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            font-size: 1.15rem;
            border-bottom: 1px solid rgba(255, 255, 255, .15);
        }

        .sidebar-marca span.punto { color: #ffd7d2; }

        .sidebar-nav {
            list-style: none;
            padding: 18px 12px;
            display: grid;
            align-content: start;
            gap: 4px;
            flex: 1;
        }

        .sidebar-nav button {
            width: 100%;
            display: flex;
            align-items: center;
            gap: 12px;
            font-family: inherit;
            font-size: .95rem;
            color: rgba(255, 255, 255, .85);
            background: none;
            border: none;
            border-radius: var(--radius-sm);
            padding: 12px 14px;
            text-align: left;
            cursor: pointer;
        }

        .sidebar-nav button .icono { font-size: 1.1rem; }

        .sidebar-nav button:hover {
            background: rgba(255, 255, 255, .1);
            color: #fff;
        }

        .sidebar-nav button.activo {
            background: #fff;
            color: var(--color-brand-deep);
            font-weight: 600;
        }

        .sidebar-pie {
            padding: 18px 22px 22px;
            border-top: 1px solid rgba(255, 255, 255, .15);
            display: grid;
            gap: 8px;
        }

        .sidebar-pie a {
            color: #fff;
            text-decoration: none;
            font-size: .85rem;
            opacity: .85;
        }

        .sidebar-pie a:hover { opacity: 1; text-decoration: underline; }

        /* ---------- Contenido ---------- */
        .contenido {
            margin-left: var(--sidebar-w);
            flex: 1;
            min-width: 0;
        }

        .top-bar {
            background: var(--color-surface);
            border-bottom: 1px solid var(--color-border);
            padding: 20px 28px;
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .btn-menu {
            display: none;
            background: none;
            border: 1px solid var(--color-border);
            border-radius: var(--radius-sm);
            font-size: 1.1rem;
            padding: 6px 10px;
            cursor: pointer;
        }

        .top-bar h1 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--color-ink);
        }

        main { padding: 28px 28px 60px; }

        .seccion { display: none; }
        .seccion.activa { display: block; }

        .resumen {
            font-size: .95rem;
            margin-bottom: 20px;
        }

        .resumen strong { color: var(--color-brand); font-size: 1.1rem; }

        .panel {
            background: var(--color-surface);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
            padding: 22px;
            margin-bottom: 18px;
        }

        .panel h2 {
            font-size: 1.05rem;
            color: var(--color-brand-deep);
            margin-bottom: 10px;
        }

        .panel p {
            font-size: .95rem;
            line-height: 1.6;
            color: var(--color-body);
        }

        .panel ul {
            list-style: none;
            display: grid;
            gap: 8px;
            margin-top: 10px;
        }

        .panel li {
            background: var(--color-bg);
            border-radius: 999px;
            padding: 8px 12px;
            font-size: .9rem;
            color: var(--color-ink);
        }

        .mensaje {
            background: var(--color-surface);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
            padding: 20px 22px;
            margin-bottom: 16px;
        }

        .mensaje.no-leido {
            border-left: 3px solid var(--color-brand);
        }

        .punto-no-leido {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--color-brand);
            margin-right: 6px;
        }

        .mensaje-cabecera {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 10px;
        }

        .mensaje-cabecera h2 {
            font-size: 1.05rem;
            font-weight: 600;
            color: var(--color-ink);
        }

        .mensaje-cabecera a {
            color: var(--color-brand);
            text-decoration: none;
            font-size: .9rem;
        }

        .mensaje-cabecera a:hover { text-decoration: underline; }

        .fecha { font-size: .8rem; color: #948a85; white-space: nowrap; }

        .mensaje p { font-size: .95rem; line-height: 1.6; }

        .mensaje-acciones {
            margin-top: 14px;
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }

        .mensaje button {
            font-family: inherit;
            font-size: .8rem;
            color: var(--color-brand);
            background: none;
            border: 1px solid var(--color-border);
            border-radius: var(--radius-sm);
            padding: 6px 14px;
            cursor: pointer;
        }

        .mensaje button:hover {
            background: var(--color-brand);
            border-color: var(--color-brand);
            color: #fff;
        }

        .vacio {
            background: var(--color-surface);
            border: 1px dashed var(--color-border);
            border-radius: var(--radius-md);
            padding: 60px 20px;
            text-align: center;
            font-size: .95rem;
        }

        /* ---------- Mesa de partes ---------- */
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 14px;
            margin-bottom: 18px;
        }

        .stat {
            background: var(--color-surface);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
            padding: 16px 18px;
        }

        .stat span.numero {
            display: block;
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--color-brand);
        }

        .stat span.etiqueta {
            font-size: .85rem;
            color: var(--color-body);
        }

        .panel-cabecera {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 14px;
        }

        .tabla-envoltura {
            overflow-x: auto;
        }

        table.tabla-tramites {
            width: 100%;
            border-collapse: collapse;
            font-size: .9rem;
        }

        .tabla-tramites th {
            text-align: left;
            font-size: .78rem;
            text-transform: uppercase;
            letter-spacing: .03em;
            color: #948a85;
            padding: 0 10px 10px;
            border-bottom: 1px solid var(--color-border);
        }

        .tabla-tramites td {
            padding: 12px 10px;
            border-bottom: 1px solid var(--color-border);
            color: var(--color-ink);
            vertical-align: middle;
        }

        .tabla-tramites tr:last-child td { border-bottom: none; }

        .codigo-tramite {
            font-family: monospace;
            font-size: .85rem;
            color: var(--color-body);
        }

        .badge-estado {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 999px;
            font-size: .78rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .badge-estado.pendiente { background: #fdecea; color: #b30000; }
        .badge-estado.proceso   { background: #fff4de; color: #8a5a00; }
        .badge-estado.atendido  { background: #e6f4ea; color: #1e7a34; }
        .badge-estado.rechazado { background: #ececec; color: #5a5350; }

        .badge-origen {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 999px;
            font-size: .78rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .badge-origen.publico { background: #e5f0ff; color: #1a4fa0; }
        .badge-origen.interno { background: var(--color-bg); color: var(--color-body); }

        .badge-rol {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 999px;
            font-size: .78rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .badge-rol.administrador { background: #fdecea; color: #b30000; }
        .badge-rol.cliente { background: #e5f0ff; color: #1a4fa0; }

        .contacto-tramite { color: #948a85; font-size: .8rem; }

        .acciones-tramite {
            display: flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
        }

        .tipos-tramite {
            list-style: none;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
            margin-top: 12px;
        }

        .tipos-tramite li {
            background: var(--color-bg);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-sm);
            padding: 10px 14px;
            font-size: .88rem;
            color: var(--color-ink);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* ---------- Formularios (nuevo trámite / subir archivo) ---------- */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 14px;
            align-items: end;
            margin-top: 6px;
        }

        .form-grid label {
            display: grid;
            gap: 6px;
            font-size: .85rem;
            color: var(--color-body);
        }

        .form-grid .campo-ancho {
            grid-column: 1 / -1;
        }

        .form-grid input,
        .form-grid select {
            font-family: inherit;
            font-size: .9rem;
            color: var(--color-ink);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-sm);
            padding: 10px 12px;
            background: var(--color-bg);
        }

        .btn-primario {
            font-family: inherit;
            font-size: .9rem;
            font-weight: 600;
            color: #fff;
            background: var(--color-brand);
            border: none;
            border-radius: var(--radius-sm);
            padding: 11px 18px;
            cursor: pointer;
            height: fit-content;
        }

        .btn-primario:hover { background: var(--color-brand-deep); }

        .btn-eliminar {
            font-family: inherit;
            font-size: .8rem;
            color: var(--color-brand);
            background: none;
            border: 1px solid var(--color-border);
            border-radius: var(--radius-sm);
            padding: 6px 14px;
            cursor: pointer;
            white-space: nowrap;
        }

        .btn-eliminar:hover {
            background: var(--color-brand);
            border-color: var(--color-brand);
            color: #fff;
        }

        .form-estado { display: inline-block; }

        .form-estado select {
            font-family: inherit;
            font-size: .85rem;
            cursor: pointer;
        }

        .form-estado select.badge-estado {
            border: none;
        }

        .form-estado select:not(.badge-estado) {
            border: 1px solid var(--color-border);
            border-radius: var(--radius-sm);
            padding: 6px 10px;
            background: var(--color-bg);
            color: var(--color-ink);
        }

        /* ---------- Biblioteca ---------- */
        .lista-archivos {
            list-style: none;
            display: grid;
            gap: 12px;
            margin-top: 10px;
        }

        .archivo {
            display: flex;
            align-items: center;
            gap: 14px;
            background: var(--color-bg);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-sm);
            padding: 14px 16px;
        }

        .archivo-icono { font-size: 1.6rem; }

        .archivo-info { flex: 1; min-width: 0; }

        .archivo-info strong { color: var(--color-ink); font-size: .95rem; }

        .archivo-info p {
            font-size: .85rem;
            color: var(--color-body);
            margin-top: 2px;
        }

        .archivo-meta {
            display: block;
            font-size: .78rem;
            color: #948a85;
            margin-top: 4px;
        }

        .archivo-acciones {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }

        .btn-descargar {
            font-size: .8rem;
            font-weight: 600;
            color: #fff;
            background: var(--color-brand);
            text-decoration: none;
            border-radius: var(--radius-sm);
            padding: 7px 14px;
        }

        .btn-descargar:hover { background: var(--color-brand-deep); }

        .btn-ver {
            font-size: .8rem;
            font-weight: 600;
            color: var(--color-brand);
            background: none;
            border: 1px solid var(--color-border);
            text-decoration: none;
            border-radius: var(--radius-sm);
            padding: 7px 14px;
        }

        .btn-ver:hover {
            background: var(--color-brand);
            border-color: var(--color-brand);
            color: #fff;
        }

        .overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(35, 30, 28, .4);
            z-index: 15;
        }

        @media (max-width: 900px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.abierta { transform: translateX(0); }
            .contenido { margin-left: 0; }
            .btn-menu { display: inline-flex; }
            .overlay.visible { display: block; }
        }
    </style>
</head>

<body>
    <div class="layout">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-marca">🎓 APSTI<span class="punto">.</span></div>
            <ul class="sidebar-nav">
                <li><button type="button" class="activo" data-seccion="mesa-partes">
                    <span class="icono">🗂️</span> Mesa de partes
                </button></li>
                <li><button type="button" data-seccion="biblioteca">
                    <span class="icono">📚</span> Biblioteca
                </button></li>
                <li><button type="button" data-seccion="mensajes">
                    <span class="icono">💬</span> Mensajes
                    <?php if ($mensajesNoLeidos > 0): ?>
                        <span style="margin-left:auto; background:#fff; color:var(--color-brand-deep); border-radius:999px; padding:1px 8px; font-size:.75rem; font-weight:600;"><?php echo $mensajesNoLeidos; ?></span>
                    <?php endif; ?>
                </button></li>
                <li><button type="button" data-seccion="usuarios">
                    <span class="icono">👥</span> Usuarios
                </button></li>
            </ul>
            <div class="sidebar-pie">
                <a href="../index.html">← Volver al sitio</a>
                <a href="../controlador/auth.php?action=logout">Cerrar sesión</a>
            </div>
        </aside>

        <div class="overlay" id="overlay"></div>

        <div class="contenido">
            <div class="top-bar">
                <button type="button" class="btn-menu" id="btnMenu" aria-label="Abrir menú">☰</button>
                <h1 id="tituloSeccion">Mesa de partes</h1>
            </div>

            <main>

                <section class="seccion activa" id="mesa-partes">

                    <div class="stats">
                        <div class="stat">
                            <span class="numero"><?php echo count($tramites); ?></span>
                            <span class="etiqueta">Trámites registrados</span>
                        </div>
                        <div class="stat">
                            <span class="numero"><?php echo $pendientes; ?></span>
                            <span class="etiqueta">Pendientes de revisión</span>
                        </div>
                        <div class="stat">
                            <span class="numero"><?php echo $enProceso; ?></span>
                            <span class="etiqueta">En proceso</span>
                        </div>
                        <div class="stat">
                            <span class="numero"><?php echo $atendidos; ?></span>
                            <span class="etiqueta">Atendidos</span>
                        </div>
                        <div class="stat">
                            <span class="numero"><?php echo $rechazados; ?></span>
                            <span class="etiqueta">Rechazados</span>
                        </div>
                    </div>

                    <article class="panel">
                        <h2>➕ Registrar nuevo trámite</h2>
                        <form method="post" action="../controlador/tramite.php?action=guardar" class="form-grid">
                            <label>
                                Solicitante
                                <input type="text" name="solicitante" placeholder="Nombre completo" required>
                            </label>
                            <label>
                                Tipo de trámite
                                <select name="tipo_tramite" required>
                                    <option value="">Selecciona un tipo</option>
                                    <?php foreach (TIPOS_TRAMITE_PERMITIDOS as $tipo): ?>
                                        <option><?php echo htmlspecialchars($tipo); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                            <label>
                                Área destino
                                <select name="area_destino" required>
                                    <option value="">Selecciona un área</option>
                                    <?php foreach (AREAS_DESTINO_PERMITIDAS as $area): ?>
                                        <?php if ($area === 'Por asignar') continue; ?>
                                        <option><?php echo htmlspecialchars($area); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                            <label class="campo-ancho">
                                Detalle (obligatorio si el tipo es "Otro")
                                <input type="text" name="detalle" placeholder="Detalle adicional (opcional)">
                            </label>
                            <button type="submit" class="btn-primario">Registrar trámite</button>
                        </form>
                    </article>

                    <article class="panel">
                        <div class="panel-cabecera">
                            <h2>🗂️ Trámites recibidos</h2>
                        </div>

                        <?php if (count($tramites) === 0): ?>
                            <div class="vacio">Aún no hay trámites registrados.</div>
                        <?php else: ?>
                            <div class="tabla-envoltura">
                                <table class="tabla-tramites">
                                    <thead>
                                        <tr>
                                            <th>Código</th>
                                            <th>Solicitante</th>
                                            <th>Origen</th>
                                            <th>Tipo de trámite</th>
                                            <th>Área destino</th>
                                            <th>Fecha</th>
                                            <th>Estado</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($tramites as $t): ?>
                                            <tr>
                                                <td class="codigo-tramite">MP-<?php echo date('Y', strtotime($t['fecha_registro'])); ?>-<?php echo str_pad($t['id'], 4, '0', STR_PAD_LEFT); ?></td>
                                                <td>
                                                    <?php echo htmlspecialchars($t['solicitante']); ?>
                                                    <?php if (!empty($t['correo']) || !empty($t['telefono'])): ?>
                                                        <br><small class="contacto-tramite"><?php echo htmlspecialchars(trim(($t['correo'] ?? '') . (!empty($t['telefono']) ? ' · ' . $t['telefono'] : ''))); ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td><span class="badge-origen <?php echo $t['origen']; ?>"><?php echo $t['origen'] === 'publico' ? 'Público' : 'Interno'; ?></span></td>
                                                <td>
                                                    <?php echo htmlspecialchars($t['tipo_tramite']); ?>
                                                    <?php if (!empty($t['detalle'])): ?>
                                                        <br><small class="contacto-tramite"><?php echo htmlspecialchars($t['detalle']); ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <form method="post" action="../controlador/tramite.php?action=actualizar_area" class="form-estado">
                                                        <input type="hidden" name="id" value="<?php echo $t['id']; ?>">
                                                        <select name="area_destino" onchange="this.form.submit()">
                                                            <?php foreach (AREAS_DESTINO_PERMITIDAS as $area): ?>
                                                                <option value="<?php echo htmlspecialchars($area); ?>" <?php echo $area === $t['area_destino'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($area); ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </form>
                                                </td>
                                                <td><?php echo date('d/m/Y', strtotime($t['fecha_registro'])); ?></td>
                                                <td>
                                                    <form method="post" action="../controlador/tramite.php?action=actualizar_estado" class="form-estado">
                                                        <input type="hidden" name="id" value="<?php echo $t['id']; ?>">
                                                        <select name="estado" class="badge-estado <?php echo $t['estado']; ?>" onchange="this.form.submit()">
                                                            <?php foreach (ESTADOS_TRAMITE_PERMITIDOS as $valor => $etiqueta): ?>
                                                                <option value="<?php echo $valor; ?>" <?php echo $valor === $t['estado'] ? 'selected' : ''; ?>><?php echo $etiqueta; ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </form>
                                                </td>
                                                <td class="acciones-tramite">
                                                    <?php if (!empty($t['documento_nombre'])): ?>
                                                        <a href="../controlador/tramite.php?action=descargar_documento&id=<?php echo $t['id']; ?>&ver=1" target="_blank" rel="noopener" class="btn-ver">📎 Ver doc.</a>
                                                    <?php endif; ?>
                                                    <form method="post" action="../controlador/tramite.php?action=eliminar" onsubmit="return confirm('¿Eliminar este trámite?')">
                                                        <button type="submit" name="eliminar" value="<?php echo $t['id']; ?>" class="btn-eliminar">Eliminar</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </article>

                    <article class="panel">
                        <h2>📄 Tipos de trámite que se atienden</h2>
                        <p>Documentos y solicitudes que los estudiantes y docentes pueden gestionar por mesa de partes.</p>
                        <ul class="tipos-tramite">
                            <li>📑 Constancia de matrícula</li>
                            <li>📑 Constancia de notas</li>
                            <li>🎓 Certificado de estudios</li>
                            <li>🪪 Duplicado de carné</li>
                            <li>🔁 Traslado interno</li>
                            <li>📤 Retiro / reserva de matrícula</li>
                            <li>🧑‍🏫 Solicitud de práctica pre-profesional</li>
                            <li>📚 Convalidación de cursos</li>
                        </ul>
                    </article>
                </section>

                <section class="seccion" id="biblioteca">

                    <div class="stats">
                        <div class="stat">
                            <span class="numero"><?php echo count($archivos); ?></span>
                            <span class="etiqueta">Archivos en la biblioteca</span>
                        </div>
                    </div>

                    <article class="panel">
                        <h2>⬆️ Subir material</h2>
                        <p>El archivo se guarda directamente en la base de datos (máximo 15 MB).</p>
                        <form method="post" action="../controlador/biblioteca.php?action=guardar" enctype="multipart/form-data" class="form-grid">
                            <label>
                                Título
                                <input type="text" name="titulo" placeholder="Nombre del recurso" required>
                            </label>
                            <label>
                                Categoría
                                <select name="categoria">
                                    <option>Guía de estudio</option>
                                    <option>Material digital</option>
                                    <option>Recurso recomendado</option>
                                </select>
                            </label>
                            <label class="campo-ancho">
                                Descripción
                                <input type="text" name="descripcion" placeholder="Breve descripción (opcional)">
                            </label>
                            <label class="campo-ancho">
                                Archivo
                                <input type="file" name="archivo" required>
                            </label>
                            <button type="submit" class="btn-primario">Subir archivo</button>
                        </form>
                    </article>

                    <article class="panel">
                        <h2>📚 Material disponible</h2>

                        <?php if (count($archivos) === 0): ?>
                            <div class="vacio">Aún no se ha subido ningún archivo a la biblioteca.</div>
                        <?php else: ?>
                            <ul class="lista-archivos">
                                <?php foreach ($archivos as $a): ?>
                                    <li class="archivo">
                                        <span class="archivo-icono"><?php echo iconoArchivo($a['nombre_archivo']); ?></span>
                                        <div class="archivo-info">
                                            <strong><?php echo htmlspecialchars($a['titulo']); ?></strong>
                                            <?php if (!empty($a['descripcion'])): ?>
                                                <p><?php echo htmlspecialchars($a['descripcion']); ?></p>
                                            <?php endif; ?>
                                            <span class="archivo-meta">
                                                <?php echo htmlspecialchars($a['categoria']); ?> ·
                                                <?php echo formatearTamano($a['tamano_bytes']); ?> ·
                                                <?php echo date('d/m/Y', strtotime($a['fecha_subida'])); ?>
                                            </span>
                                        </div>
                                        <div class="archivo-acciones">
                                            <a href="../controlador/biblioteca.php?action=descargar&id=<?php echo $a['id']; ?>&ver=1" target="_blank" rel="noopener" class="btn-ver">Ver</a>
                                            <a href="../controlador/biblioteca.php?action=descargar&id=<?php echo $a['id']; ?>" class="btn-descargar">Descargar</a>
                                            <form method="post" action="../controlador/biblioteca.php?action=eliminar" onsubmit="return confirm('¿Eliminar este archivo?')">
                                                <button type="submit" name="eliminar" value="<?php echo $a['id']; ?>" class="btn-eliminar">Eliminar</button>
                                            </form>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </article>
                </section>

                <section class="seccion" id="mensajes">
                    <p class="resumen">
                        Tienes <strong><?php echo count($mensajes); ?></strong>
                        mensaje<?php echo count($mensajes) === 1 ? '' : 's'; ?> de contacto
                        (<strong><?php echo $mensajesNoLeidos; ?></strong> sin leer).
                    </p>

                    <?php if (count($mensajes) === 0): ?>
                        <div class="vacio">Aún no has recibido mensajes. Cuando alguien use el formulario de contacto, aparecerá aquí.</div>
                    <?php endif; ?>

                    <?php foreach ($mensajes as $fila): ?>
                        <article class="mensaje <?php echo (int) $fila['leido'] === 0 ? 'no-leido' : ''; ?>">
                            <div class="mensaje-cabecera">
                                <h2>
                                    <?php if ((int) $fila['leido'] === 0): ?>
                                        <span class="punto-no-leido" title="No leído"></span>
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars($fila['nombre']); ?>
                                </h2>
                                <a href="mailto:<?php echo htmlspecialchars($fila['correo']); ?>">
                                    <?php echo htmlspecialchars($fila['correo']); ?>
                                </a>
                                <span class="fecha"><?php echo date('d/m/Y H:i', strtotime($fila['fecha_envio'])); ?></span>
                            </div>
                            <p><?php echo nl2br(htmlspecialchars($fila['mensaje'])); ?></p>
                            <div class="mensaje-acciones">
                                <?php if ((int) $fila['leido'] === 0): ?>
                                    <form method="post" action="../controlador/mensaje.php?action=marcar_leido">
                                        <input type="hidden" name="id" value="<?php echo $fila['id']; ?>">
                                        <button type="submit" class="btn-ver">Marcar como leído</button>
                                    </form>
                                <?php endif; ?>
                                <form method="post" action="../controlador/mensaje.php?action=eliminar" onsubmit="return confirm('¿Eliminar este mensaje?')">
                                    <button type="submit" name="eliminar" value="<?php echo $fila['id']; ?>">Eliminar</button>
                                </form>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </section>

                <section class="seccion" id="usuarios">

                    <div class="stats">
                        <div class="stat">
                            <span class="numero"><?php echo count($usuarios); ?></span>
                            <span class="etiqueta">Cuentas registradas</span>
                        </div>
                        <div class="stat">
                            <span class="numero"><?php echo $totalAdministradores; ?></span>
                            <span class="etiqueta">Administradores</span>
                        </div>
                        <div class="stat">
                            <span class="numero"><?php echo $totalClientes; ?></span>
                            <span class="etiqueta">Clientes</span>
                        </div>
                    </div>

                    <article class="panel">
                        <h2>➕ Crear nueva cuenta</h2>
                        <form method="post" action="../controlador/usuario.php?action=crear" class="form-grid">
                            <label>
                                Nombre completo
                                <input type="text" name="nombre" placeholder="Nombre y apellidos" required>
                            </label>
                            <label>
                                Correo electrónico
                                <input type="email" name="correo" placeholder="correo@ejemplo.com" required>
                            </label>
                            <label>
                                Teléfono
                                <input type="text" name="telefono" placeholder="Opcional">
                            </label>
                            <label>
                                Contraseña
                                <input type="password" name="clave" placeholder="Mínimo 6 caracteres" minlength="6" required>
                            </label>
                            <label>
                                Rol
                                <select name="rol" required>
                                    <option value="cliente">Cliente</option>
                                    <option value="administrador">Administrador</option>
                                </select>
                            </label>
                            <button type="submit" class="btn-primario">Crear cuenta</button>
                        </form>
                    </article>

                    <article class="panel">
                        <div class="panel-cabecera">
                            <h2>👥 Cuentas registradas</h2>
                        </div>

                        <?php if (count($usuarios) === 0): ?>
                            <div class="vacio">Aún no hay usuarios registrados.</div>
                        <?php else: ?>
                            <div class="tabla-envoltura">
                                <table class="tabla-tramites">
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Correo</th>
                                            <th>Teléfono</th>
                                            <th>Rol</th>
                                            <th>Registrado</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($usuarios as $u): ?>
                                            <?php $esUsuarioActual = (int) $u['id'] === idUsuarioActual(); ?>
                                            <tr>
                                                <td>
                                                    <?php echo htmlspecialchars($u['nombre']); ?>
                                                    <?php if ($esUsuarioActual): ?>
                                                        <br><small class="contacto-tramite">(Tú)</small>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($u['correo']); ?></td>
                                                <td><?php echo htmlspecialchars($u['telefono'] ?? ''); ?></td>
                                                <td>
                                                    <?php if ($esUsuarioActual): ?>
                                                        <span class="badge-rol <?php echo $u['rol']; ?>"><?php echo $u['rol'] === 'administrador' ? 'Administrador' : 'Cliente'; ?></span>
                                                    <?php else: ?>
                                                        <form method="post" action="../controlador/usuario.php?action=actualizar_rol" class="form-estado">
                                                            <input type="hidden" name="id" value="<?php echo $u['id']; ?>">
                                                            <select name="rol" class="badge-rol <?php echo $u['rol']; ?>" onchange="this.form.submit()">
                                                                <option value="cliente" <?php echo $u['rol'] === 'cliente' ? 'selected' : ''; ?>>Cliente</option>
                                                                <option value="administrador" <?php echo $u['rol'] === 'administrador' ? 'selected' : ''; ?>>Administrador</option>
                                                            </select>
                                                        </form>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo date('d/m/Y', strtotime($u['fecha_registro'])); ?></td>
                                                <td class="acciones-tramite">
                                                    <?php if (!$esUsuarioActual): ?>
                                                        <form method="post" action="../controlador/usuario.php?action=eliminar" onsubmit="return confirm('¿Eliminar esta cuenta?')">
                                                            <button type="submit" name="eliminar" value="<?php echo $u['id']; ?>" class="btn-eliminar">Eliminar</button>
                                                        </form>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </article>
                </section>

            </main>
        </div>
    </div>

    <script>
        const botones = document.querySelectorAll('.sidebar-nav button');
        const secciones = document.querySelectorAll('.seccion');
        const titulo = document.getElementById('tituloSeccion');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        const btnMenu = document.getElementById('btnMenu');

        botones.forEach((boton) => {
            boton.addEventListener('click', () => {
                const destino = boton.dataset.seccion;

                botones.forEach((b) => b.classList.remove('activo'));
                boton.classList.add('activo');

                secciones.forEach((s) => s.classList.toggle('activa', s.id === destino));
                titulo.textContent = boton.textContent.trim().replace(/\s*\d+$/, '');

                sidebar.classList.remove('abierta');
                overlay.classList.remove('visible');
            });
        });

        btnMenu.addEventListener('click', () => {
            sidebar.classList.add('abierta');
            overlay.classList.add('visible');
        });

        overlay.addEventListener('click', () => {
            sidebar.classList.remove('abierta');
            overlay.classList.remove('visible');
        });

        const destinoInicial = window.location.hash.replace('#', '');
        if (destinoInicial) {
            const botonInicial = document.querySelector('.sidebar-nav button[data-seccion="' + destinoInicial + '"]');
            if (botonInicial) botonInicial.click();
        }
    </script>
</body>

</html>
