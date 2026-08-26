<?php
if (session_status() == PHP_SESSION_NONE) session_start();

$id_cita_get = $_GET["id_cita"] ?? null;
if (!is_numeric($id_cita_get)) { echo '<p class="text-danger">Cita no válida.</p>'; return; }
$id_cita = (int) $id_cita_get;

$cita = ModeloCitas::mdlObtenerCita($id_cita);
if (!$cita) { echo '<p class="text-danger">Cita no encontrada.</p>'; return; }

$paciente = ModeloPacientes::mdlMostrarPacientes("pacientes", "id_paciente", (int) $cita["id_paciente"]);
$edad = "-";
if (!empty($paciente["fecha_nacimiento"])) {
    $edad = date_diff(date_create($paciente["fecha_nacimiento"]), date_create("today"))->y;
}

$carrito = ControladorConsultas::ctrConsultaCarritoLeer($id_cita);

$catalogoMedicamentos = ControladorConsultas::ctrMostrarCatalogoMedicamentos();
?>

<style>
.consulta-header-card{background:#fff;border:1px solid #e3e6f0;border-radius:8px;padding:18px;margin-bottom:18px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;}
.consulta-avatar{width:48px;height:48px;border-radius:50%;background:#e3e6f0;display:flex;align-items:center;justify-content:center;font-weight:700;color:#4e5cf0;margin-right:14px;}
.card-box{background:#fff;border:1px solid #e3e6f0;border-radius:8px;padding:18px;margin-bottom:18px;}
.triaje-grid{display:grid;grid-template-columns:repeat(3, 1fr);gap:12px;}
.step-badge{width:26px;height:26px;border-radius:50%;background:#4e5cf0;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-weight:700;margin-right:8px;}
.linea-item{display:flex;justify-content:space-between;align-items:center;padding:6px 0;border-bottom:1px solid #f0f0f0;}
</style>

<div id="consulta-wrapper" data-id-cita="<?= $id_cita ?>">

<!-- Encabezado paciente -->
<div class="consulta-header-card">
    <div class="d-flex align-items-center">
        <div class="consulta-avatar"><?= strtoupper(substr($paciente["nombre"], 0, 1) . substr($paciente["apellidos"], 0, 1)) ?></div>
        <div>
            <h5 class="mb-0"><?= htmlspecialchars($paciente["nombre"] . " " . $paciente["apellidos"]) ?></h5>
            <small class="text-muted"><?= $edad ?> años · Carnet <?= htmlspecialchars($paciente["ci"]) ?></small>
        </div>
    </div>
    <span class="badge badge-success" style="font-size:0.9em;padding:6px 14px;">Checked In</span>
</div>

<div style="display:flex;gap:20px;align-items:flex-start;flex-wrap:wrap;">
    <!-- Columna izquierda -->
    <div style="flex:1 1 600px;min-width:0;">

        <!-- 1. Triaje -->
        <div class="card-box">
            <h5><span class="step-badge">1</span> Triaje & Signos Vitales</h5>
            <div class="triaje-grid mt-2">
                <div>
                    <label><i class="fa fa-heart text-danger"></i> Presión Arterial</label>
                    <input type="text" class="form-control" id="triaje-tension_arterial" placeholder="120/80">
                </div>
                <div>
                    <label><i class="fa fa-heartbeat"></i> Frec. Cardiaca (bpm)</label>
                    <input type="text" class="form-control" id="triaje-frecuencia_cardiaca" placeholder="72">
                </div>
                <div>
                    <label><i class="fa fa-lungs"></i> Frec. Respiratoria</label>
                    <input type="text" class="form-control" id="triaje-frecuencia_respiratoria" placeholder="18">
                </div>
                <div>
                    <label><i class="fa fa-thermometer-half"></i> Temperatura (°C)</label>
                    <input type="text" class="form-control" id="triaje-temperatura" placeholder="36.8">
                </div>
                <div>
                    <label><i class="fa fa-tint"></i> Saturación (%)</label>
                    <input type="text" class="form-control" id="triaje-saturacion" placeholder="98">
                </div>
                <div>
                    <label><i class="fa fa-balance-scale"></i> Peso (kg)</label>
                    <input type="text" class="form-control" id="triaje-peso" placeholder="64.5">
                </div>
            </div>
        </div>

        <!-- 2. Motivo + Observaciones -->
        <div class="card-box">
            <h5><span class="step-badge">2</span> Observaciones Médicas</h5>
            <div class="form-group mt-2">
                <label>Motivo de consulta <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="campo-motivo" placeholder="Motivo por el cual acude el paciente">
            </div>
            <div class="form-group">
                <label>Observaciones clínicas</label>
                <textarea class="form-control" id="campo-observaciones" rows="5" placeholder="Ingrese observaciones clínicas, síntomas y hallazgos aquí..."></textarea>
            </div>
        </div>
    </div>

    <!-- Columna derecha -->
    <div style="flex:0 0 320px;max-width:100%;">
        <div class="card-box">
            <h5><span class="step-badge">3</span> Solicitudes</h5>

            <h6 class="mt-3">Laboratorios <span class="badge badge-secondary" id="contador-examenes">0</span></h6>
            <div id="lista-examenes" class="mb-2"></div>
            <button type="button" class="btn btn-primary btn-block mb-3" onclick="abrirModalLaboratorios()">
                <i class="fa fa-plus-circle"></i> Agregar Laboratorio
            </button>

            <h6>Tratamientos <span class="badge badge-secondary" id="contador-medicamentos">0</span></h6>
            <div id="lista-medicamentos" class="mb-2"></div>
            <button type="button" class="btn btn-primary btn-block" onclick="abrirModalMedicamentos()">
                <i class="fa fa-plus-circle"></i> Agregar Tratamiento
            </button>
        </div>

        <div class="card-box">
            <button type="button" class="btn btn-success btn-block" onclick="finalizarConsulta()">
                <i class="fa fa-check-double"></i> Finalizar Consulta
            </button>
            <button type="button" class="btn btn-outline-primary btn-block mt-2" onclick="abrirVistaPrevia()">
                <i class="fa fa-eye"></i> Vista previa
            </button>
        </div>
    </div>
</div>

</div><!-- /#consulta-wrapper -->

<!-- Modal: Seleccionar Laboratorios -->
<div class="modal fade" id="modalLaboratorios" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h5 class="mb-0"><i class="fa fa-flask"></i> Seleccionar Laboratorios</h5>
          <small class="text-muted">Busque y agregue pruebas de laboratorio a la consulta actual.</small>
        </div>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-7">
            <input type="text" class="form-control mb-2" id="labBuscarTermino" placeholder="Buscar prueba de laboratorio...">
            <div class="mb-2">
              <button type="button" class="btn btn-sm btn-primary lab-tab-filtro" data-tipo="todos">Todos</button>
              <button type="button" class="btn btn-sm btn-outline-secondary lab-tab-filtro" data-tipo="laboratorio">Laboratorio</button>
              <button type="button" class="btn btn-sm btn-outline-secondary lab-tab-filtro" data-tipo="ecografia">Ecografía</button>
              <button type="button" class="btn btn-sm btn-outline-secondary lab-tab-filtro" data-tipo="serologia">Serología</button>
            </div>
            <div id="labResultados" style="max-height:350px;overflow-y:auto;"></div>
          </div>
          <div class="col-md-5">
            <h6>Laboratorios Seleccionados <span class="badge badge-primary" id="labContadorSeleccionados">0</span></h6>
            <div id="labSeleccionados"></div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" onclick="confirmarSeleccionLaboratorios()">Confirmar Selección <i class="fa fa-arrow-right"></i></button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Agregar Medicamentos (versión funcional simple — se pule visualmente en el Bloque 4) -->
<!-- Modal: Seleccionar Medicamentos -->
<div class="modal fade" id="modalMedicamentos" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="mb-0">Seleccionar Medicamentos</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-6">
            <input type="text" class="form-control mb-2" id="medBuscarTermino" placeholder="Buscar medicamento...">
            <div id="medResultados" style="max-height:400px;overflow-y:auto;"></div>
          </div>
          <div class="col-md-6">
            <h6>Detalle de Receta</h6>
            <div id="medDetalleReceta" style="max-height:400px;overflow-y:auto;"></div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" onclick="confirmarSeleccionMedicamentos()">Confirmar Selección</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Vista previa -->
<div class="modal fade" id="modalVistaPrevia" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h5 class="mb-0">Resumen de Consulta Médica</h5>
          <small class="text-muted"><?= htmlspecialchars($paciente["nombre"] . " " . $paciente["apellidos"]) ?> · <?= $edad ?> años</small>
        </div>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <h6><i class="fa fa-heartbeat"></i> Signos Vitales</h6>
        <div id="vpTriaje" class="triaje-grid mb-3"></div>

        <div class="row">
          <div class="col-md-7">
            <h6>Observaciones Clínicas</h6>
            <p id="vpObservaciones" class="text-muted"></p>
          </div>
          <div class="col-md-5">
            <h6>Laboratorios</h6>
            <div id="vpExamenes"></div>
            <h6 class="mt-3">Tratamientos</h6>
            <div id="vpMedicamentos"></div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-edit"></i> Editar Consulta</button>
        <button type="button" class="btn btn-success" onclick="finalizarConsulta()"><i class="fa fa-check"></i> Confirmar y Finalizar</button>
      </div>
    </div>
  </div>
</div>

<script>
var idCitaConsulta = document.getElementById("consulta-wrapper").dataset.idCita;
var carritoActual = null;
var timeoutGuardarCampo = null;

// ── Render principal ──────────────────────────────────────────────────────

function renderCarritoConsulta(carrito) {
    carritoActual = carrito;

    document.getElementById("campo-motivo").value = carrito.motivo || "";
    document.getElementById("campo-observaciones").value = carrito.observaciones || "";
    Object.keys(carrito.triaje).forEach(function (campo) {
        var input = document.getElementById("triaje-" + campo);
        if (input) input.value = carrito.triaje[campo] || "";
    });

    document.getElementById("contador-examenes").textContent = carrito.examenes.length;
    var contEx = document.getElementById("lista-examenes");
    contEx.innerHTML = carrito.examenes.length
        ? carrito.examenes.map(function (e) {
            return '<div class="linea-item"><span><i class="fa fa-flask text-muted"></i> ' + e.nombre + '</span>' +
                '<button type="button" class="btn btn-link text-danger p-0" onclick="quitarExamenCarrito(' + e.id_linea + ')"><i class="fa fa-times"></i></button></div>';
        }).join("")
        : '<p class="text-muted small mb-0">Ninguno agregado.</p>';

    document.getElementById("contador-medicamentos").textContent = carrito.medicamentos.length;
    var contMed = document.getElementById("lista-medicamentos");
    contMed.innerHTML = carrito.medicamentos.length
        ? carrito.medicamentos.map(function (m) {
            return '<div class="linea-item"><span><i class="fa fa-pills text-muted"></i> ' + m.nombre + '</span>' +
                '<button type="button" class="btn btn-link text-danger p-0" onclick="quitarMedicamentoCarrito(' + m.id_linea + ')"><i class="fa fa-times"></i></button></div>';
        }).join("")
        : '<p class="text-muted small mb-0">Ninguno agregado.</p>';
}

function cargarCarritoInicial() {
    fetch("index.php?consultaCarritoIdCita=" + idCitaConsulta)
        .then(function (r) { return r.json(); })
        .then(function (data) { renderCarritoConsulta(data.carrito); });
}

// ── Guardado con debounce de motivo/observaciones/triaje ────────────────

function guardarCampo(campo, valor) {
    clearTimeout(timeoutGuardarCampo);
    timeoutGuardarCampo = setTimeout(function () {
        var body = new URLSearchParams();
        body.append("consultaCarritoIdCita", idCitaConsulta);
        body.append("consultaCampo", campo);
        body.append("consultaValor", valor);
        fetch("index.php", { method: "POST", body: body })
            .then(function (r) { return r.json(); })
            .then(function (data) { carritoActual = data.carrito; });
    }, 500);
}

document.getElementById("campo-motivo").addEventListener("input", function () { guardarCampo("motivo", this.value); });
document.getElementById("campo-observaciones").addEventListener("input", function () { guardarCampo("observaciones", this.value); });
["tension_arterial","temperatura","peso","frecuencia_cardiaca","frecuencia_respiratoria","saturacion"].forEach(function (campo) {
    document.getElementById("triaje-" + campo).addEventListener("input", function () { guardarCampo(campo, this.value); });
});

// ── Laboratorios ─────────────────────────────────────────────────────────

var labSeleccion = {};
var labTipoActivo = "todos";

function abrirModalLaboratorios() {
    labSeleccion = {};
    renderLabSeleccionados();
    document.getElementById("labBuscarTermino").value = "";
    buscarLaboratorios();
    $("#modalLaboratorios").modal("show");
}

document.querySelectorAll(".lab-tab-filtro").forEach(function (btn) {
    btn.addEventListener("click", function () {
        document.querySelectorAll(".lab-tab-filtro").forEach(function (b) { b.classList.replace("btn-primary", "btn-outline-secondary"); });
        this.classList.replace("btn-outline-secondary", "btn-primary");
        labTipoActivo = this.dataset.tipo;
        buscarLaboratorios();
    });
});

document.getElementById("labBuscarTermino").addEventListener("input", function () { buscarLaboratorios(); });

function buscarLaboratorios() {
    var termino = document.getElementById("labBuscarTermino").value.trim();
    fetch("index.php?buscarExamenesConsulta=" + encodeURIComponent(termino) + "&tipoExamen=" + labTipoActivo)
        .then(function (r) { return r.json(); })
        .then(function (data) {
            var cont = document.getElementById("labResultados");
            if (!data.length) { cont.innerHTML = '<p class="text-muted">Sin resultados.</p>'; return; }
            var html = "";
            data.forEach(function (ex) {
                var yaSeleccionado = !!labSeleccion[ex.id_examen];
                html += '<div class="d-flex justify-content-between align-items-center border rounded p-2 mb-2">' +
                    '<div><strong>' + ex.nombre + '</strong><br><small class="text-muted">' + ex.tipo + '</small></div>' +
                    '<button type="button" class="btn btn-sm ' + (yaSeleccionado ? 'btn-success' : 'btn-outline-primary') + '" onclick="toggleLaboratorio(' + ex.id_examen + ', \'' + ex.nombre.replace(/'/g, "\\'") + '\', \'' + ex.tipo + '\')">' +
                    '<i class="fa fa-' + (yaSeleccionado ? 'check' : 'plus') + '"></i></button></div>';
            });
            cont.innerHTML = html;
        });
}

function toggleLaboratorio(idExamen, nombre, tipo) {
    if (labSeleccion[idExamen]) { delete labSeleccion[idExamen]; }
    else { labSeleccion[idExamen] = { id_examen: idExamen, nombre: nombre, tipo: tipo }; }
    renderLabSeleccionados();
    buscarLaboratorios();
}

function renderLabSeleccionados() {
    var lista = Object.values(labSeleccion);
    document.getElementById("labContadorSeleccionados").textContent = lista.length;
    var cont = document.getElementById("labSeleccionados");
    cont.innerHTML = lista.length ? lista.map(function (ex) {
        return '<div class="d-flex justify-content-between align-items-center border rounded p-2 mb-2">' +
            '<div><strong>' + ex.nombre + '</strong><br><small class="text-muted">' + ex.tipo + '</small></div>' +
            '<button type="button" class="btn btn-link text-danger p-0" onclick="toggleLaboratorio(' + ex.id_examen + ', \'\', \'\')"><i class="fa fa-trash"></i></button></div>';
    }).join("") : '<p class="text-muted">Ningún laboratorio seleccionado.</p>';
}

function confirmarSeleccionLaboratorios() {
    var lista = Object.values(labSeleccion);
    if (!lista.length) { $("#modalLaboratorios").modal("hide"); return; }

    var body = new URLSearchParams();
    body.append("consultaCarritoIdCita", idCitaConsulta);
    body.append("examenes", JSON.stringify(lista));

    fetch("index.php", { method: "POST", body: body })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            renderCarritoConsulta(data.carrito);
            $("#modalLaboratorios").modal("hide");
        });
}

function quitarExamenCarrito(idLinea) {
    var body = new URLSearchParams();
    body.append("consultaCarritoIdCita", idCitaConsulta);
    body.append("quitarExamenIdLinea", idLinea);
    fetch("index.php", { method: "POST", body: body })
        .then(function (r) { return r.json(); })
        .then(function (data) { renderCarritoConsulta(data.carrito); });
}

// ── Medicamentos (versión simple, se rediseña en Bloque 4) ──────────────

// ── Medicamentos ─────────────────────────────────────────────────────────

var medSeleccion = {}; // { id_medicamento: {id_medicamento, nombre, dosis, frecuencia, duracion} }

function abrirModalMedicamentos() {
    medSeleccion = {};
    renderMedDetalle();
    document.getElementById("medBuscarTermino").value = "";
    buscarMedicamentos();
    $("#modalMedicamentos").modal("show");
}

document.getElementById("medBuscarTermino").addEventListener("input", function () { buscarMedicamentos(); });

function buscarMedicamentos() {
    var termino = document.getElementById("medBuscarTermino").value.trim();
    fetch("index.php?buscarMedicamentosConsulta=" + encodeURIComponent(termino))
        .then(function (r) { return r.json(); })
        .then(function (data) {
            var cont = document.getElementById("medResultados");
            if (!data.length) { cont.innerHTML = '<p class="text-muted">Sin resultados.</p>'; return; }
            var html = "";
            data.forEach(function (m) {
                var yaSeleccionado = !!medSeleccion[m.id_medicamento];
                html += '<div class="d-flex justify-content-between align-items-center border rounded p-2 mb-2">' +
                    '<strong>' + m.nombre + '</strong>' +
                    '<button type="button" class="btn btn-sm ' + (yaSeleccionado ? 'btn-success' : 'btn-outline-primary') + '" onclick="toggleMedicamento(' + m.id_medicamento + ', \'' + m.nombre.replace(/'/g, "\\'") + '\')">' +
                    '<i class="fa fa-' + (yaSeleccionado ? 'check' : 'plus') + '"></i></button></div>';
            });
            cont.innerHTML = html;
        });
}

function toggleMedicamento(idMedicamento, nombre) {
    if (medSeleccion[idMedicamento]) {
        delete medSeleccion[idMedicamento];
    } else {
        medSeleccion[idMedicamento] = { id_medicamento: idMedicamento, nombre: nombre, dosis: "", frecuencia: "", duracion: "" };
    }
    renderMedDetalle();
    buscarMedicamentos();
}

function actualizarCampoMedicamento(idMedicamento, campo, valor) {
    if (medSeleccion[idMedicamento]) {
        medSeleccion[idMedicamento][campo] = valor;
    }
}

function renderMedDetalle() {
    var lista = Object.values(medSeleccion);
    var cont = document.getElementById("medDetalleReceta");
    if (!lista.length) { cont.innerHTML = '<p class="text-muted">Seleccione medicamentos de la lista para configurar la receta.</p>'; return; }

    var html = "";
    lista.forEach(function (m) {
        html += '<div class="border rounded p-2 mb-2">' +
            '<div class="d-flex justify-content-between align-items-center mb-2">' +
            '<strong>' + m.nombre + '</strong>' +
            '<button type="button" class="btn btn-link text-danger p-0" onclick="toggleMedicamento(' + m.id_medicamento + ', \'\')"><i class="fa fa-trash"></i></button></div>' +
            '<div class="row">' +
            '<div class="col-4"><label class="small mb-0">Dosis</label><input type="text" class="form-control form-control-sm" placeholder="500mg" value="' + m.dosis + '" oninput="actualizarCampoMedicamento(' + m.id_medicamento + ', \'dosis\', this.value)"></div>' +
            '<div class="col-4"><label class="small mb-0">Frecuencia</label><input type="text" class="form-control form-control-sm" placeholder="c/8h" value="' + m.frecuencia + '" oninput="actualizarCampoMedicamento(' + m.id_medicamento + ', \'frecuencia\', this.value)"></div>' +
            '<div class="col-4"><label class="small mb-0">Duración</label><input type="text" class="form-control form-control-sm" placeholder="7 días" value="' + m.duracion + '" oninput="actualizarCampoMedicamento(' + m.id_medicamento + ', \'duracion\', this.value)"></div>' +
            '</div></div>';
    });
    cont.innerHTML = html;
}

function confirmarSeleccionMedicamentos() {
    var lista = Object.values(medSeleccion);
    if (!lista.length) { $("#modalMedicamentos").modal("hide"); return; }

    var body = new URLSearchParams();
    body.append("consultaCarritoIdCita", idCitaConsulta);
    body.append("medicamentos", JSON.stringify(lista));

    fetch("index.php", { method: "POST", body: body })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            renderCarritoConsulta(data.carrito);
            $("#modalMedicamentos").modal("hide");
        });
}

// ── Vista previa ──────────────────────────────────────────────────────────

function abrirVistaPrevia() {
    var t = carritoActual.triaje;
    document.getElementById("vpTriaje").innerHTML =
        '<div><small class="text-muted">Presión Arterial</small><br><strong>' + (t.tension_arterial || "-") + '</strong></div>' +
        '<div><small class="text-muted">Temperatura</small><br><strong>' + (t.temperatura || "-") + ' °C</strong></div>' +
        '<div><small class="text-muted">Peso</small><br><strong>' + (t.peso || "-") + ' kg</strong></div>' +
        '<div><small class="text-muted">Frec. Cardiaca</small><br><strong>' + (t.frecuencia_cardiaca || "-") + ' bpm</strong></div>';

    document.getElementById("vpObservaciones").textContent = carritoActual.observaciones || "Sin observaciones registradas.";

    document.getElementById("vpExamenes").innerHTML = carritoActual.examenes.length
        ? carritoActual.examenes.map(function (e) { return '<div class="small"><i class="fa fa-flask"></i> ' + e.nombre + '</div>'; }).join("")
        : '<p class="text-muted small">Ninguno.</p>';

    document.getElementById("vpMedicamentos").innerHTML = carritoActual.medicamentos.length
        ? carritoActual.medicamentos.map(function (m) { return '<div class="small"><i class="fa fa-pills"></i> ' + m.nombre + ' — ' + m.dosis + ', ' + m.frecuencia + '</div>'; }).join("")
        : '<p class="text-muted small">Ninguno.</p>';

    $("#modalVistaPrevia").modal("show");
}

// ── Finalizar ─────────────

function finalizarConsulta() {
    if (!carritoActual.motivo) {
        Swal.fire({ icon: "warning", title: "El motivo de consulta es obligatorio" });
        return;
    }

    var body = new URLSearchParams();
    body.append("consultaFinalizarIdCita", idCitaConsulta);

    fetch("index.php", { method: "POST", body: body })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.status === "ok") {
                Swal.fire({ icon: "success", title: "Consulta finalizada correctamente", confirmButtonText: "Cerrar" })
                    .then(function () { window.location = "index.php?ruta=inicio"; });
            } else {
                Swal.fire({ icon: "error", title: data.mensaje || "No se pudo finalizar la consulta" });
            }
        });
}

cargarCarritoInicial();
</script>