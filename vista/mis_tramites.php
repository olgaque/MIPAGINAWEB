<?php

require_once __DIR__ . '/../modelo/auth.php';
requerirRol('cliente');

require_once __DIR__ . '/../modelo/tramite.php';

$tramites = listarTramitesPorUsuario(idUsuarioActual());
$nombre = $_SESSION['usuario_nombre'];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis trámites | ASPTI</title>
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
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--color-bg);
            color: var(--color-body);
            min-height: 100vh;
        }

        header {
            background: var(--color-brand);
            color: #fff;
            padding: 22px 20px;
        }

        .contenedor { max-width: 900px; margin: 0 auto; }

        header .contenedor {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }

        header h1 { font-size: 1.3rem; font-weight: 600; }

        header .acciones a {
            color: #fff;
            text-decoration: none;
            font-size: .85rem;
            border: 1px solid rgba(255, 255, 255, .5);
            padding: 8px 16px;
            border-radius: 999px;
            margin-left: 8px;
        }

        header .acciones a:hover { background: rgba(255, 255, 255, .15); }

        main { padding: 28px 20px 60px; }

        .panel {
            background: var(--color-surface);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
            padding: 22px;
            margin-bottom: 20px;
        }

        .panel h2 {
            font-size: 1.05rem;
            color: var(--color-brand-deep);
            margin-bottom: 14px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 14px;
        }

        .form-grid label {
            display: grid;
            gap: 6px;
            font-size: .85rem;
            color: var(--color-body);
        }

        .form-grid .campo-ancho { grid-column: 1 / -1; }

        .form-grid input,
        .form-grid select,
        .form-grid textarea {
            font-family: inherit;
            font-size: .9rem;
            color: var(--color-ink);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-sm);
            padding: 10px 12px;
            background: var(--color-bg);
        }

        .form-grid textarea { min-height: 70px; resize: vertical; }

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
            grid-column: 1 / -1;
            justify-self: start;
        }

        .btn-primario:hover { background: var(--color-brand-deep); }

        #estado-tramite { font-size: .9rem; grid-column: 1 / -1; }

        .tabla-envoltura { overflow-x: auto; }

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
        }

        .tabla-tramites tr:last-child td { border-bottom: none; }

        .codigo-tramite { font-family: monospace; font-size: .85rem; color: var(--color-body); }

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

        .btn-ver {
            font-size: .8rem;
            font-weight: 600;
            color: var(--color-brand);
            background: none;
            border: 1px solid var(--color-border);
            text-decoration: none;
            border-radius: var(--radius-sm);
            padding: 6px 12px;
            white-space: nowrap;
        }

        .btn-ver:hover { background: var(--color-brand); border-color: var(--color-brand); color: #fff; }

        .vacio {
            background: var(--color-surface);
            border: 1px dashed var(--color-border);
            border-radius: var(--radius-md);
            padding: 40px 20px;
            text-align: center;
            font-size: .95rem;
        }
    </style>
</head>

<body>
    <header>
        <div class="contenedor">
            <h1>👤 Mis trámites — <?php echo htmlspecialchars($nombre); ?></h1>
            <div class="acciones">
                <a href="../index.html">← Volver al sitio</a>
                <a href="../controlador/auth.php?action=logout">Cerrar sesión</a>
            </div>
        </div>
    </header>

    <main class="contenedor">

        <article class="panel">
            <h2>➕ Registrar nuevo trámite</h2>
            <form id="form-tramite" enctype="multipart/form-data" class="form-grid">
                <label>
                    Tipo de trámite
                    <select name="tipo_tramite" id="tipoTramite" required>
                        <option value="">Selecciona un tipo</option>
                        <?php foreach (TIPOS_TRAMITE_PERMITIDOS as $tipo): ?>
                            <option><?php echo htmlspecialchars($tipo); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="campo-ancho">
                    Detalle (obligatorio si el tipo es "Otro")
                    <textarea name="detalle" id="detalleTramite" placeholder="Cuéntanos más sobre tu solicitud"></textarea>
                </label>
                <label class="campo-ancho">
                    Documento adjunto (opcional, máx. 15 MB)
                    <input type="file" name="documento">
                </label>
                <button type="submit" class="btn-primario">Enviar solicitud</button>
                <p id="estado-tramite" role="status"></p>
            </form>
        </article>

        <article class="panel">
            <h2>🗂️ Historial de mis trámites</h2>

            <?php if (count($tramites) === 0): ?>
                <div class="vacio">Aún no has registrado ningún trámite.</div>
            <?php else: ?>
                <div class="tabla-envoltura">
                    <table class="tabla-tramites">
                        <thead>
                            <tr>
                                <th>Código</th>
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
                                        <?php echo htmlspecialchars($t['tipo_tramite']); ?>
                                        <?php if (!empty($t['detalle'])): ?>
                                            <br><small style="color:#948a85;"><?php echo htmlspecialchars($t['detalle']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($t['area_destino']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($t['fecha_registro'])); ?></td>
                                    <td><span class="badge-estado <?php echo $t['estado']; ?>"><?php echo ESTADOS_TRAMITE_PERMITIDOS[$t['estado']]; ?></span></td>
                                    <td>
                                        <?php if (!empty($t['documento_nombre'])): ?>
                                            <a href="../controlador/tramite.php?action=descargar_documento&id=<?php echo $t['id']; ?>&ver=1" target="_blank" rel="noopener" class="btn-ver">📎 Ver doc.</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </article>

    </main>

    <script>
        const tipoTramiteSelect = document.getElementById('tipoTramite');
        const detalleTramite = document.getElementById('detalleTramite');
        tipoTramiteSelect.addEventListener('change', () => {
            detalleTramite.required = tipoTramiteSelect.value === 'Otro';
        });

        const formularioTramite = document.getElementById('form-tramite');
        const estadoTramite = document.getElementById('estado-tramite');

        formularioTramite.addEventListener('submit', async (evento) => {
            evento.preventDefault();
            estadoTramite.textContent = 'Enviando…';
            try {
                const respuesta = await fetch('../controlador/tramite.php?action=guardar_publico', {
                    method: 'POST',
                    body: new FormData(formularioTramite),
                });
                const cuerpo = await respuesta.json();
                if (respuesta.ok) {
                    estadoTramite.textContent = `Trámite registrado ✓ Tu código es ${cuerpo.codigo}.`;
                    setTimeout(() => window.location.reload(), 1200);
                } else {
                    estadoTramite.textContent = cuerpo.error ?? 'No se pudo registrar el trámite.';
                }
            } catch {
                estadoTramite.textContent = 'Error de conexión con el servidor.';
            }
        });
    </script>
</body>

</html>
