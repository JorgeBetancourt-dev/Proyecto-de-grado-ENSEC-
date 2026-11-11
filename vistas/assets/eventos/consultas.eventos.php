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

function quitarMedicamentoCarrito(idLinea) {
    var body = new URLSearchParams();
    body.append("consultaCarritoIdCita", idCitaConsulta);
    body.append("quitarMedicamentoIdLinea", idLinea);
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

function generarRecetaPDF() {
    var lista = Object.values(medSeleccion);

    // Nada nuevo seleccionado en este modal: si ya hay medicamentos confirmados, solo abre el PDF
    if (!lista.length) {
        if (!carritoActual || !carritoActual.medicamentos.length) {
            Swal.fire({ icon: "warning", title: "Selecciona al menos un medicamento" });
            return;
        }
        window.open("index.php?generarRecetaConsulta=" + idCitaConsulta, "_blank");
        return;
    }

    var body = new URLSearchParams();
    body.append("consultaCarritoIdCita", idCitaConsulta);
    body.append("medicamentos", JSON.stringify(lista));

    fetch("index.php", { method: "POST", body: body })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            renderCarritoConsulta(data.carrito);
            medSeleccion = {};
            renderMedDetalle();
            window.open("index.php?generarRecetaConsulta=" + idCitaConsulta, "_blank");
        });
}


cargarCarritoInicial();
</script>