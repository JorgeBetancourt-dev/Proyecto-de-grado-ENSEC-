<?php
if (session_status() == PHP_SESSION_NONE) session_start();

$id_usuario = (int) $_SESSION["IdUsuario"];

$id_cita_get = $_GET["id_cita"] ?? null;
$tieneCita = false;
$cita = null;
$id_cita = 0;

$catalogoServicios    = ControladorPagos::ctrMostrarCatalogoServicios();
$catalogoExamenes     = ControladorPagos::ctrMostrarCatalogoExamenes();
$catalogoMedicamentos = ControladorPagos::ctrMostrarCatalogoMedicamentos();

if ($id_cita_get !== null) {
    // ── Flujo CON cita: sin cambios respecto a la versión anterior ─────────
    if (!is_numeric($id_cita_get)) { echo '<p class="text-danger">Cita no válida.</p>'; return; }
    $id_cita = (int) $id_cita_get;

    $cita = ModeloCitas::mdlObtenerCita($id_cita);
    if (!$cita) { echo '<p class="text-danger">Cita no encontrada.</p>'; return; }
    $tieneCita = true;

    $pago = ControladorPagos::ctrIniciarCobro((int) $cita["id_paciente"], $id_cita, $id_usuario);
    if (!$pago) { echo '<p class="text-danger">No se pudo iniciar el cobro.</p>'; return; }

    $lineas = ControladorPagos::ctrMostrarLineasPago((int) $pago["id_servicio_prestado"]);

    $examenesSugeridos = [];
    $medicamentosSugeridos = [];
    $consulta = ControladorConsultas::ctrObtenerConsultaPorCita($id_cita);
    if ($consulta) {
        $examenesSugeridos = ControladorPagos::ctrExamenesPendientesCobro((int) $consulta["id_consulta"], (int) $pago["id_servicio_prestado"]);
        $medicamentosSugeridos = ControladorPagos::ctrMedicamentosPendientesCobro((int) $consulta["id_consulta"], (int) $pago["id_servicio_prestado"]);
    }
} else {
    // ── Flujo SIN cita: todo vive en el carrito de sesión ──────────────────
    $carrito = ControladorPagos::ctrCarritoLeer();
    $totalCarrito = 0;
    foreach ($carrito["lineas"] as $l) {
        $totalCarrito += $l["precio"] * $l["cantidad"];
    }
}

$mensajes = [
    "pago_error"      => ["error",   "No se pudo completar la acción, revisa los datos"],
    "linea_agregada"  => ["success", "Ítem agregado al cobro"],
    "linea_quitada"   => ["success", "Ítem quitado del cobro"],
];
?>

<?php if ($tieneCita && isset($_SESSION["accion_pago"]) && isset($mensajes[$_SESSION["accion_pago"]])):
    [$icon, $title] = $mensajes[$_SESSION["accion_pago"]];
?>
<script>
document.addEventListener("DOMContentLoaded", function () {
    Swal.fire({ icon: "<?= $icon ?>", title: "<?= $title ?>", confirmButtonText: "Cerrar" });
});
</script>
<?php endif; unset($_SESSION["accion_pago"]); ?>

<style>
.panel-pago-header{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;margin-bottom:16px;}
.panel-pago-header h4{margin:0;}
.panel-pago-fecha{background:#fff;border:1px solid #e3e6f0;border-radius:6px;padding:6px 14px;font-size:0.9em;color:#555;}
.card-box{background:#fff;border:1px solid #e3e6f0;border-radius:8px;padding:18px;margin-bottom:18px;}
.detalle-cuenta{position:sticky;top:15px;}
.concepto-tabs{display:flex;border-bottom:1px solid #e3e6f0;margin-bottom:14px;}
.concepto-tab{padding:8px 16px;cursor:pointer;color:#777;border-bottom:2px solid transparent;font-weight:500;}
.concepto-tab.activo{color:#4e5cf0;border-bottom-color:#4e5cf0;}
.concepto-panel{display:none;}
.concepto-panel.activo{display:block;}
.concepto-grid{display:grid;grid-template-columns:repeat(auto-fill, minmax(220px, 1fr));gap:12px;}
.concepto-card{border:1px solid #e3e6f0;border-radius:8px;padding:12px;display:flex;flex-direction:column;justify-content:space-between;}
.concepto-card .nombre{font-weight:600;}
.concepto-card .precio{color:#4e5cf0;font-weight:600;margin-bottom:4px;}
.concepto-card .descripcion{font-size:0.85em;color:#888;margin-bottom:8px;}
.linea-cuenta{display:flex;justify-content:space-between;align-items:flex-start;padding:8px 0;border-bottom:1px solid #f0f0f0;}
.linea-cuenta .nombre{font-weight:500;}
.linea-cuenta .meta{font-size:0.85em;color:#888;}
</style>

<div id="cobro-wrapper">

<div class="panel-pago-header">
    <h4><i class="fa fa-dollar"></i> <?= $tieneCita ? "Cobro de la cita" : "Registro de Pagos y Facturación" ?></h4>
    <span class="panel-pago-fecha"><i class="fa fa-calendar"></i> <?= date("d/m/Y") ?></span>
</div>

<div style="display:flex;gap:20px;align-items:flex-start;flex-wrap:wrap;">
    <!-- ── Columna izquierda ─────────────────────────────────────────────── -->
    <div style="flex:1 1 600px;min-width:0;">

        <?php if ($tieneCita): ?>
        <div class="card-box">
            <h5><i class="fa fa-user"></i> Paciente de la cita</h5>
            <p style="margin:3px 0;"><strong>Paciente:</strong> <?= htmlspecialchars($cita["pac_nombre"] . " " . $cita["pac_apellidos"]) ?></p>
            <p style="margin:3px 0;"><strong>Carnet:</strong> <?= htmlspecialchars($cita["pac_ci"]) ?></p>
            <p style="margin:3px 0;"><strong>Fecha de la cita:</strong> <?= htmlspecialchars($cita["fecha"]) ?> <?= htmlspecialchars($cita["hora"]) ?></p>
        </div>
        <?php else: ?>

        <!-- Datos del Cliente (Facturación) — carrito en sesión -->
        <div class="card-box">
            <h5><i class="fa fa-file-text"></i> Datos del Cliente (Facturación)</h5>

            <div id="cliente-asignado" style="<?= $carrito["id_cliente"] ? "" : "display:none;" ?>">
                <div class="alert alert-success mb-2">
                    <i class="fa fa-check-circle"></i> <strong>Cliente encontrado</strong>
                    <div class="row mt-2">
                        <div class="col-6"><strong>Nombre:</strong> <span id="cliente-nombre-actual"><?= htmlspecialchars($carrito["cliente_nombre"] ?? "") ?></span></div>
                        <div class="col-6"><strong>NIT:</strong> <span id="cliente-nit-actual"><?= htmlspecialchars($carrito["cliente_nit"] ?? "") ?></span></div>
                    </div>
                </div>
                <button type="button" class="btn btn-link btn-sm p-0" onclick="mostrarBuscadorCliente()">Cambiar cliente</button>
            </div>

            <div id="cliente-buscador" style="<?= $carrito["id_cliente"] ? "display:none;" : "" ?>">
                <div class="input-group mb-2">
                    <input type="text" class="form-control" id="input-buscar-cliente" placeholder="Ingresar NIT">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-primary" onclick="buscarCliente()"><i class="fa fa-search"></i> Buscar</button>
                    </div>
                </div>
                <div id="resultado-cliente"></div>
            </div>
        </div>

        <!-- Datos del Paciente (Atención) — carrito en sesión -->
        <div class="card-box">
            <h5><i class="fa fa-user"></i> Datos del Paciente (Atención)</h5>

            <div id="paciente-asignado" style="<?= $carrito["id_paciente"] ? "" : "display:none;" ?>">
                <div class="alert alert-success mb-2">
                    <i class="fa fa-check-circle"></i> <strong>Paciente encontrado</strong>
                    <div class="row mt-2">
                        <div class="col-6"><strong>Nombre:</strong> <span id="paciente-nombre-actual"><?= htmlspecialchars($carrito["paciente_nombre"] ?? "") ?></span></div>
                        <div class="col-6"><strong>Carnet:</strong> <span id="paciente-ci-actual"><?= htmlspecialchars($carrito["paciente_ci"] ?? "") ?></span></div>
                    </div>
                </div>
                <button type="button" class="btn btn-link btn-sm p-0" onclick="mostrarBuscadorPaciente()">Cambiar paciente</button>
            </div>

            <div id="paciente-buscador" style="<?= $carrito["id_paciente"] ? "display:none;" : "" ?>">
                <div class="input-group mb-2">
                    <input type="text" class="form-control" id="input-buscar-paciente" placeholder="Ingresar C.I.">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-primary" onclick="buscarPaciente()"><i class="fa fa-search"></i> Buscar</button>
                    </div>
                </div>
                <div id="resultado-paciente"></div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($tieneCita && (!empty($examenesSugeridos) || !empty($medicamentosSugeridos))): ?>
        <div class="card-box" style="background:#fffbe6;">
            <h5><i class="fa fa-lightbulb-o"></i> Pendientes de la consulta</h5>
            <p class="text-muted">Estos ítems fueron solicitados por el médico en la consulta de esta cita y todavía no se cobraron.</p>
            <?php foreach ($examenesSugeridos as $ex): ?>
            <form method="POST" class="row align-items-center mb-2">
                <input type="hidden" name="lineaIdServicioPrestado" value="<?= (int) $pago["id_servicio_prestado"] ?>">
                <input type="hidden" name="lineaIdCita" value="<?= $id_cita ?>">
                <input type="hidden" name="lineaTipo" value="examen">
                <input type="hidden" name="lineaIdReferencia" value="<?= (int) $ex["id_examen"] ?>">
                <input type="hidden" name="lineaCantidad" value="1">
                <div class="col-md-7"><i class="fa fa-flask"></i> <?= htmlspecialchars($ex["nombre"]) ?> (<?= ucfirst($ex["tipo"]) ?>)</div>
                <div class="col-md-3"><input type="number" step="0.01" class="form-control form-control-sm" name="lineaPrecio" value="<?= $ex["costo"] ?>" required></div>
                <div class="col-md-2"><button type="submit" class="btn btn-sm btn-warning btn-block"><i class="fa fa-plus"></i> Cobrar</button></div>
            </form>
            <?php endforeach; ?>
            <?php foreach ($medicamentosSugeridos as $med): ?>
            <form method="POST" class="row align-items-center mb-2">
                <input type="hidden" name="lineaIdServicioPrestado" value="<?= (int) $pago["id_servicio_prestado"] ?>">
                <input type="hidden" name="lineaIdCita" value="<?= $id_cita ?>">
                <input type="hidden" name="lineaTipo" value="medicamento">
                <input type="hidden" name="lineaIdReferencia" value="<?= (int) $med["id_medicamento"] ?>">
                <div class="col-md-5"><i class="fa fa-pills"></i> <?= htmlspecialchars($med["nombre"]) ?></div>
                <div class="col-md-2"><input type="number" min="1" class="form-control form-control-sm" name="lineaCantidad" value="1" required></div>
                <div class="col-md-3"><input type="number" step="0.01" class="form-control form-control-sm" name="lineaPrecio" value="<?= $med["precio"] ?>" required></div>
                <div class="col-md-2"><button type="submit" class="btn btn-sm btn-warning btn-block"><i class="fa fa-plus"></i> Cobrar</button></div>
            </form>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Agregar Conceptos -->
        <div class="card-box">
            <h5><i class="fa fa-shopping-cart"></i> Agregar Conceptos</h5>

            <div class="concepto-tabs">
                <div class="concepto-tab activo" data-tab="servicios" onclick="cambiarTabConcepto('servicios')">Servicios</div>
                <div class="concepto-tab" data-tab="laboratorios" onclick="cambiarTabConcepto('laboratorios')">Laboratorios</div>
                <div class="concepto-tab" data-tab="medicamentos" onclick="cambiarTabConcepto('medicamentos')">Medicamentos</div>
            </div>

            <!-- Servicios -->
            <div class="concepto-panel activo" id="panel-servicios">
                <div class="concepto-grid">
                    <?php foreach ($catalogoServicios as $s): ?>
                    <?php if ($tieneCita): ?>
                    <form method="POST" class="concepto-card">
                        <input type="hidden" name="lineaIdServicioPrestado" value="<?= (int) $pago["id_servicio_prestado"] ?>">
                        <input type="hidden" name="lineaIdCita" value="<?= $id_cita ?>">
                        <input type="hidden" name="lineaTipo" value="servicio">
                        <input type="hidden" name="lineaIdReferencia" value="<?= $s["id_servicio"] ?>">
                        <input type="hidden" name="lineaPrecio" value="<?= $s["precio"] ?>">
                        <div><div class="nombre"><?= htmlspecialchars($s["nombre"]) ?></div><div class="precio">Bs. <?= number_format($s["precio"], 2) ?></div></div>
                        <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Añadir</button>
                    </form>
                    <?php else: ?>
                    <div class="concepto-card">
                        <div><div class="nombre"><?= htmlspecialchars($s["nombre"]) ?></div><div class="precio">Bs. <?= number_format($s["precio"], 2) ?></div></div>
                        <button type="button" class="btn btn-primary btn-sm" onclick="agregarLineaCarrito('servicio', <?= $s["id_servicio"] ?>, '<?= htmlspecialchars($s["nombre"], ENT_QUOTES) ?>', <?= $s["precio"] ?>, 1)"><i class="fa fa-plus"></i> Añadir</button>
                    </div>
                    <?php endif; ?>
                    <?php endforeach; ?>
                    <?php if (empty($catalogoServicios)): ?><p class="text-muted">No hay servicios en el catálogo.</p><?php endif; ?>
                </div>
            </div>

            <!-- Laboratorios (exámenes) -->
            <div class="concepto-panel" id="panel-laboratorios">
                <div class="concepto-grid">
                    <?php foreach ($catalogoExamenes as $ex): ?>
                    <?php if ($tieneCita): ?>
                    <form method="POST" class="concepto-card">
                        <input type="hidden" name="lineaIdServicioPrestado" value="<?= (int) $pago["id_servicio_prestado"] ?>">
                        <input type="hidden" name="lineaIdCita" value="<?= $id_cita ?>">
                        <input type="hidden" name="lineaTipo" value="examen">
                        <input type="hidden" name="lineaIdReferencia" value="<?= $ex["id_examen"] ?>">
                        <input type="hidden" name="lineaPrecio" value="<?= $ex["precio"] ?>">
                        <input type="hidden" name="lineaCantidad" value="1">
                        <div><div class="nombre"><?= htmlspecialchars($ex["nombre"]) ?></div><div class="descripcion"><?= ucfirst($ex["tipo"]) ?></div><div class="precio">Bs. <?= number_format($ex["precio"], 2) ?></div></div>
                        <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Añadir</button>
                    </form>
                    <?php else: ?>
                    <div class="concepto-card">
                        <div><div class="nombre"><?= htmlspecialchars($ex["nombre"]) ?></div><div class="descripcion"><?= ucfirst($ex["tipo"]) ?></div><div class="precio">Bs. <?= number_format($ex["precio"], 2) ?></div></div>
                        <button type="button" class="btn btn-primary btn-sm" onclick="agregarLineaCarrito('examen', <?= $ex["id_examen"] ?>, '<?= htmlspecialchars($ex["nombre"], ENT_QUOTES) ?>', <?= $ex["precio"] ?>, 1)"><i class="fa fa-plus"></i> Añadir</button>
                    </div>
                    <?php endif; ?>
                    <?php endforeach; ?>
                    <?php if (empty($catalogoExamenes)): ?><p class="text-muted">No hay exámenes en el catálogo.</p><?php endif; ?>
                </div>
            </div>

            <!-- Medicamentos -->
            <div class="concepto-panel" id="panel-medicamentos">
                <div class="concepto-grid">
                    <?php foreach ($catalogoMedicamentos as $med): ?>
                    <?php if ($tieneCita): ?>
                    <form method="POST" class="concepto-card">
                        <input type="hidden" name="lineaIdServicioPrestado" value="<?= (int) $pago["id_servicio_prestado"] ?>">
                        <input type="hidden" name="lineaIdCita" value="<?= $id_cita ?>">
                        <input type="hidden" name="lineaTipo" value="medicamento">
                        <input type="hidden" name="lineaIdReferencia" value="<?= $med["id_medicamento"] ?>">
                        <input type="hidden" name="lineaPrecio" value="<?= $med["precio"] ?>">
                        <div class="nombre mb-2"><?= htmlspecialchars($med["nombre"]) ?></div>
                        <div class="precio"><?= number_format($med["precio"], 2) ?></div>
                        <input type="number" min="1" class="form-control form-control-sm mb-2" name="lineaCantidad" value="1" required>
                        <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Añadir</button>
                    </form>
                    <?php else: ?>
                    <div class="concepto-card">
                        <div class="nombre mb-1"><?= htmlspecialchars($med["nombre"]) ?></div>
                        <div class="precio mb-2">Bs. <?= number_format($med["precio"], 2) ?></div>
                        <label style="font-size:0.8em;color:#888;">Cantidad</label>
                        <input type="number" min="1" class="form-control form-control-sm mb-2" id="cant-med-<?= $med["id_medicamento"] ?>" value="1">
                        <button type="button" class="btn btn-primary btn-sm" onclick="agregarMedicamentoCarrito(<?= $med["id_medicamento"] ?>, '<?= htmlspecialchars($med["nombre"], ENT_QUOTES) ?>', <?= $med["precio"] ?>)"><i class="fa fa-plus"></i> Añadir</button>
                    </div>
                    <?php endif; ?>
                    <?php endforeach; ?>
                    <?php if (empty($catalogoMedicamentos)): ?><p class="text-muted">No hay medicamentos en el catálogo.</p><?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Columna derecha: Detalle de Cuenta ──────────────────────────────── -->
    <div style="flex:0 0 340px;max-width:100%;">
        <div class="card-box detalle-cuenta">
            <h5><i class="fa fa-file-text-o"></i> Detalle de Cuenta</h5>

            <?php if ($tieneCita): ?>
                <div id="lineas-cuenta">
                <?php if (empty($lineas)): ?>
                    <p class="text-muted">Todavía no hay ítems en este cobro.</p>
                <?php else: foreach ($lineas as $l):
                    $nombre = $l["nombre_servicio"] ?? $l["nombre_examen"] ?? $l["nombre_medicamento"] ?? "—";
                ?>
                <div class="linea-cuenta">
                    <div><div class="nombre"><?= htmlspecialchars($nombre) ?></div><div class="meta">Cant. <?= (int) $l["cantidad"] ?> × Bs. <?= number_format($l["precio_final"], 2) ?></div></div>
                    <div class="text-right">
                        <div class="nombre">Bs. <?= number_format($l["subtotal"], 2) ?></div>
                        <form method="POST" class="d-inline" onsubmit="return confirm('¿Quitar este ítem del cobro?');">
                            <input type="hidden" name="quitarLineaId" value="<?= (int) $l["id_detalle_servicio"] ?>">
                            <input type="hidden" name="quitarLineaIdServicioPrestado" value="<?= (int) $pago["id_servicio_prestado"] ?>">
                            <input type="hidden" name="quitarLineaIdCita" value="<?= $id_cita ?>">
                            <button type="submit" class="btn btn-link text-danger p-0"><i class="fa fa-trash"></i></button>
                        </form>
                    </div>
                </div>
                <?php endforeach; endif; ?>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3 mb-3">
                    <strong>Total a Pagar</strong>
                    <strong style="font-size:1.3em;color:#28a745;">Bs. <?= number_format($pago["costo_total"], 2) ?></strong>
                </div>

                <form method="POST">
                    <input type="hidden" name="finalizarPagoIdServicioPrestado" value="<?= (int) $pago["id_servicio_prestado"] ?>">
                    <input type="hidden" name="finalizarPagoIdCita" value="<?= $id_cita ?>">
                    <div class="form-group">
                        <label>Método de pago</label>
                        <select class="form-control" name="finalizarPagoMetodo" required>
                            <option value="efectivo" <?= $pago["metodo_pago"] === "efectivo" ? "selected" : "" ?>>Efectivo</option>
                            <option value="tarjeta"  <?= $pago["metodo_pago"] === "tarjeta"  ? "selected" : "" ?>>Tarjeta</option>
                            <option value="qr"       <?= $pago["metodo_pago"] === "qr"       ? "selected" : "" ?>>QR</option>
                            <option value="transferencia" <?= $pago["metodo_pago"] === "transferencia" ? "selected" : "" ?>>Transferencia</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Cliente para factura (opcional)</label>
                        <input type="text" class="form-control" name="finalizarPagoClienteNombre" placeholder="Nombre/Razón social">
                    </div>
                    <div class="form-group">
                        <label>NIT (opcional)</label>
                        <input type="text" class="form-control" name="finalizarPagoClienteNit" placeholder="NIT">
                    </div>
                    <button type="submit" class="btn btn-success btn-block" <?= $pago["costo_total"] <= 0 ? "disabled" : "" ?>><i class="fa fa-check"></i> Realizar Pago</button>
                    <a href="index.php?ruta=panel_cita" class="btn btn-default btn-block mt-2"><i class="fa fa-times"></i> Cancelar</a>
                </form>

            <?php else: ?>
                <!-- Sin cita: se llena/actualiza por JS desde el carrito -->
                <div id="lineas-cuenta"></div>
                <div class="d-flex justify-content-between align-items-center mt-3 mb-3">
                    <strong>Total a Pagar</strong>
                    <strong style="font-size:1.3em;color:#28a745;" id="total-pagar">Bs. 0.00</strong>
                </div>

                <div class="form-group">
                    <label>Método de pago</label>
                    <select class="form-control" id="metodo-pago">
                        <option value="efectivo">Efectivo</option>
                        <option value="tarjeta">Tarjeta</option>
                        <option value="qr">QR</option>
                        <option value="transferencia">Transferencia</option>
                    </select>
                </div>
                <p class="text-muted" id="texto-cliente-factura"><small></small></p>

                <button type="button" class="btn btn-success btn-block" id="btn-realizar-pago" disabled onclick="finalizarPagoCarrito()"><i class="fa fa-check"></i> Realizar Pago</button>
                <a href="#" class="btn btn-default btn-block mt-2" onclick="cancelarCarrito(); return false;"><i class="fa fa-times"></i> Cancelar</a>
                <p class="text-muted text-center mt-2" id="texto-sin-items"><small>Agrega al menos un ítem para poder confirmar el cobro.</small></p>
            <?php endif; ?>
        </div>
    </div>
</div>

</div><!-- /#cobro-wrapper -->

<script>
function cambiarTabConcepto(tab) {
    document.querySelectorAll(".concepto-tab").forEach(function (el) { el.classList.toggle("activo", el.dataset.tab === tab); });
    document.querySelectorAll(".concepto-panel").forEach(function (el) { el.classList.remove("activo"); });
    document.getElementById("panel-" + tab).classList.add("activo");
}
</script>

<?php if (!$tieneCita): ?>
<script>
// ── Render del carrito (Detalle de Cuenta) ──────────────────────────────

function renderCarrito(carrito, total) {
    var cont = document.getElementById("lineas-cuenta");
    if (!carrito.lineas.length) {
        cont.innerHTML = '<p class="text-muted">Todavía no hay ítems en este cobro.</p>';
    } else {
        var html = "";
        carrito.lineas.forEach(function (l) {
            var subtotal = (l.precio * l.cantidad).toFixed(2);
            html += '<div class="linea-cuenta">' +
                '<div><div class="nombre">' + l.nombre + '</div><div class="meta">Cant. ' + l.cantidad + ' × Bs. ' + parseFloat(l.precio).toFixed(2) + '</div></div>' +
                '<div class="text-right"><div class="nombre">Bs. ' + subtotal + '</div>' +
                '<button type="button" class="btn btn-link text-danger p-0" onclick="quitarLineaCarrito(' + l.id_linea + ')"><i class="fa fa-trash"></i></button></div>' +
                '</div>';
        });
        cont.innerHTML = html;
    }
    document.getElementById("total-pagar").textContent = "Bs. " + parseFloat(total).toFixed(2);
    document.getElementById("btn-realizar-pago").disabled = total <= 0;
    document.getElementById("texto-sin-items").style.display = total > 0 ? "none" : "block";

    var textoCliente = document.getElementById("texto-cliente-factura");
    textoCliente.innerHTML = carrito.id_cliente
        ? "<small>La factura se emitirá a: " + carrito.cliente_nombre + "</small>"
        : "<small>Sin cliente seleccionado — la factura quedará sin datos de facturación.</small>";
}

function agregarLineaCarrito(tipo, idReferencia, nombre, precio, cantidad) {
    var body = new URLSearchParams();
    body.append("carritoLineaTipo", tipo);
    body.append("carritoLineaIdReferencia", idReferencia);
    body.append("carritoLineaNombre", nombre);
    body.append("carritoLineaPrecio", precio);
    body.append("carritoLineaCantidad", cantidad);

    fetch("index.php", { method: "POST", body: body })
        .then(function (r) { return r.json(); })
        .then(function (data) { renderCarrito(data.carrito, data.total); });
}

function agregarMedicamentoCarrito(idMedicamento, nombre, precio) {
    var cantidad = parseInt(document.getElementById("cant-med-" + idMedicamento).value) || 1;
    agregarLineaCarrito("medicamento", idMedicamento, nombre, precio, cantidad);
}

function quitarLineaCarrito(idLinea) {
    var body = new URLSearchParams();
    body.append("carritoQuitarLineaId", idLinea);

    fetch("index.php", { method: "POST", body: body })
        .then(function (r) { return r.json(); })
        .then(function (data) { renderCarrito(data.carrito, data.total); });
}

function finalizarPagoCarrito() {
    var metodo = document.getElementById("metodo-pago").value;
    var body = new URLSearchParams();
    body.append("carritoFinalizarMetodo", metodo);

    fetch("index.php", { method: "POST", body: body })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.status === "ok") {
                Swal.fire({ icon: "success", title: "Pago registrado", confirmButtonText: "Cerrar" })
                    .then(function () { window.location = "index.php?ruta=panel_pago"; });
            } else {
                Swal.fire({ icon: "error", title: data.mensaje || "No se pudo registrar el cobro" });
            }
        });
}

function cancelarCarrito() {
    var body = new URLSearchParams();
    body.append("carritoCancelar", "1");
    fetch("index.php", { method: "POST", body: body })
        .then(function () { window.location = "index.php?ruta=panel_pago"; });
}

// ── Paciente ──────────────────────────────────────────────────────────────

function mostrarBuscadorPaciente() {
    document.getElementById("paciente-asignado").style.display = "none";
    document.getElementById("paciente-buscador").style.display = "block";
    document.getElementById("resultado-paciente").innerHTML = "";
}

function buscarPaciente() {
    var ci = document.getElementById("input-buscar-paciente").value.trim();
    if (!ci) return;
    fetch("index.php?buscarPacientePorCI=" + encodeURIComponent(ci))
        .then(function (r) { return r.json(); })
        .then(function (data) {
            var cont = document.getElementById("resultado-paciente");
            if (data.encontrado) {
                var p = data.paciente;
                cont.innerHTML =
                    '<div class="alert alert-success mb-2"><i class="fa fa-check-circle"></i> <strong>Paciente encontrado</strong>' +
                    '<div class="row mt-2"><div class="col-6"><strong>Nombre:</strong> ' + p.nombre + ' ' + p.apellidos + '</div>' +
                    '<div class="col-6"><strong>Carnet:</strong> ' + p.ci + '</div></div></div>' +
                    '<button type="button" class="btn btn-success btn-sm" onclick="seleccionarPaciente(' + p.id_paciente + ', \'' + (p.nombre + " " + p.apellidos).replace(/'/g, "\\'") + '\', \'' + p.ci + '\')">' +
                    '<i class="fa fa-check"></i> Usar este paciente</button>';
            } else {
                cont.innerHTML =
                    '<div class="card-box" style="background:#eaf4ff;"><h6><i class="fa fa-user-plus"></i> Registrar nuevo paciente</h6>' +
                    '<div class="row">' +
                    '<div class="col-md-6 mb-2"><input type="text" class="form-control" id="rp-nombre" placeholder="Ingresar nombre"></div>' +
                    '<div class="col-md-6 mb-2"><input type="text" class="form-control" id="rp-apellidos" placeholder="Ingresar apellidos"></div>' +
                    '<div class="col-md-6 mb-2"><input type="text" class="form-control" id="rp-ci" value="' + ci + '" placeholder="Ingresar C.I."></div>' +
                    '<div class="col-md-6 mb-2"><input type="text" class="form-control" id="rp-grupo" placeholder="Grupo sanguíneo (ej: A+)"></div>' +
                    '<div class="col-md-6 mb-2"><input type="text" class="form-control" id="rp-telefono" placeholder="Ingresar teléfono"></div>' +
                    '<div class="col-md-6 mb-2"><input type="date" class="form-control" id="rp-fecha"></div>' +
                    '<div class="col-md-12 mb-2"><input type="text" class="form-control" id="rp-direccion" placeholder="Ingresar dirección"></div>' +
                    '</div><div class="text-right"><button type="button" class="btn btn-success btn-sm" onclick="registrarPacienteRapido()"><i class="fa fa-save"></i> Guardar paciente</button></div></div>';
            }
        });
}

function seleccionarPaciente(idPaciente, nombreCompleto, ci) {
    var body = new URLSearchParams();
    body.append("carritoAsignarPacienteId", idPaciente);
    body.append("carritoAsignarPacienteNombre", nombreCompleto);
    body.append("carritoAsignarPacienteCI", ci);

    fetch("index.php", { method: "POST", body: body })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            aplicarPacienteAsignado(nombreCompleto, ci);
            renderCarrito(data.carrito, data.total);
        });
}

function registrarPacienteRapido() {
    var nombre = document.getElementById("rp-nombre").value.trim();
    var ci     = document.getElementById("rp-ci").value.trim();
    if (!nombre || !ci) { Swal.fire({ icon: "warning", title: "Nombre y carnet son obligatorios" }); return; }

    var body = new URLSearchParams();
    body.append("carritoNuevoPacienteNombre", nombre);
    body.append("carritoNuevoPacienteApellidos", document.getElementById("rp-apellidos").value.trim());
    body.append("carritoNuevoPacienteCI", ci);
    body.append("carritoNuevoPacienteGrupoSanguineo", document.getElementById("rp-grupo").value.trim());
    body.append("carritoNuevoPacienteTelefono", document.getElementById("rp-telefono").value.trim());
    body.append("carritoNuevoPacienteFechaNacimiento", document.getElementById("rp-fecha").value);
    body.append("carritoNuevoPacienteDireccion", document.getElementById("rp-direccion").value.trim());

    fetch("index.php", { method: "POST", body: body })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.status === "ok" || data.carrito) {
                aplicarPacienteAsignado(data.carrito.paciente_nombre, data.carrito.paciente_ci);
                renderCarrito(data.carrito, data.total);
            } else {
                Swal.fire({ icon: "error", title: data.mensaje || "No se pudo registrar el paciente" });
            }
        });
}

function aplicarPacienteAsignado(nombreCompleto, ci) {
    document.getElementById("paciente-nombre-actual").textContent = nombreCompleto;
    document.getElementById("paciente-ci-actual").textContent = ci;
    document.getElementById("paciente-asignado").style.display = "block";
    document.getElementById("paciente-buscador").style.display = "none";
    document.getElementById("resultado-paciente").innerHTML = "";
}

// ── Cliente ───────────────────────────────────────────────────────────────

function mostrarBuscadorCliente() {
    document.getElementById("cliente-asignado").style.display = "none";
    document.getElementById("cliente-buscador").style.display = "block";
    document.getElementById("resultado-cliente").innerHTML = "";
}

function buscarCliente() {
    var nit = document.getElementById("input-buscar-cliente").value.trim();
    if (!nit) return;
    fetch("index.php?buscarClientePorNit=" + encodeURIComponent(nit))
        .then(function (r) { return r.json(); })
        .then(function (data) {
            var cont = document.getElementById("resultado-cliente");
            if (data.encontrado) {
                var c = data.cliente;
                cont.innerHTML =
                    '<div class="alert alert-success mb-2"><i class="fa fa-check-circle"></i> <strong>Cliente encontrado</strong>' +
                    '<div class="row mt-2"><div class="col-6"><strong>Nombre:</strong> ' + c.nombre + '</div>' +
                    '<div class="col-6"><strong>NIT:</strong> ' + c.nit + '</div></div></div>' +
                    '<button type="button" class="btn btn-success btn-sm" onclick="seleccionarCliente(' + c.id_cliente + ', \'' + c.nombre.replace(/'/g, "\\'") + '\', \'' + c.nit + '\')">' +
                    '<i class="fa fa-check"></i> Usar este cliente</button>';
            } else {
                cont.innerHTML =
                    '<div class="card-box" style="background:#eaf4ff;"><h6><i class="fa fa-user-plus"></i> Registrar nuevo cliente</h6>' +
                    '<div class="row">' +
                    '<div class="col-md-6 mb-2"><input type="text" class="form-control" id="rc-nombre" placeholder="Ingresar nombre / razón social"></div>' +
                    '<div class="col-md-6 mb-2"><input type="text" class="form-control" id="rc-nit" value="' + nit + '" placeholder="Ingresar NIT"></div>' +
                    '</div><div class="text-right"><button type="button" class="btn btn-success btn-sm" onclick="registrarClienteRapido()"><i class="fa fa-save"></i> Guardar cliente</button></div></div>';
            }
        });
}

function seleccionarCliente(idCliente, nombre, nit) {
    var body = new URLSearchParams();
    body.append("carritoAsignarClienteId", idCliente);
    body.append("carritoAsignarClienteNombre", nombre);
    body.append("carritoAsignarClienteNit", nit);

    fetch("index.php", { method: "POST", body: body })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            aplicarClienteAsignado(nombre, nit);
            renderCarrito(data.carrito, data.total);
        });
}

function registrarClienteRapido() {
    var nombre = document.getElementById("rc-nombre").value.trim();
    var nit    = document.getElementById("rc-nit").value.trim();
    if (!nombre || !nit) { Swal.fire({ icon: "warning", title: "Nombre y NIT son obligatorios" }); return; }

    var body = new URLSearchParams();
    body.append("carritoNuevoClienteNombre", nombre);
    body.append("carritoNuevoClienteNit", nit);

    fetch("index.php", { method: "POST", body: body })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.status === "ok" || data.carrito) {
                aplicarClienteAsignado(data.carrito.cliente_nombre, data.carrito.cliente_nit);
                renderCarrito(data.carrito, data.total);
            } else {
                Swal.fire({ icon: "error", title: data.mensaje || "No se pudo registrar el cliente" });
            }
        });
}

function aplicarClienteAsignado(nombre, nit) {
    document.getElementById("cliente-nombre-actual").textContent = nombre;
    document.getElementById("cliente-nit-actual").textContent = nit;
    document.getElementById("cliente-asignado").style.display = "block";
    document.getElementById("cliente-buscador").style.display = "none";
    document.getElementById("resultado-cliente").innerHTML = "";
}

// Render inicial desde el carrito ya en sesión (por si hubo un refresh)
renderCarrito(<?= json_encode($carrito) ?>, <?= json_encode($totalCarrito) ?>);
</script>
<?php endif; ?>