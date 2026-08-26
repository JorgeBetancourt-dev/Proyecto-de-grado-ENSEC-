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

<script>
var labSeleccion = {}; // { id_examen: {id_examen, nombre, tipo} } — estado local, no se manda hasta confirmar
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
    if (labSeleccion[idExamen]) {
        delete labSeleccion[idExamen];
    } else {
        labSeleccion[idExamen] = { id_examen: idExamen, nombre: nombre, tipo: tipo };
    }
    renderLabSeleccionados();
    buscarLaboratorios();
}

function renderLabSeleccionados() {
    var lista = Object.values(labSeleccion);
    document.getElementById("labContadorSeleccionados").textContent = lista.length;
    var cont = document.getElementById("labSeleccionados");
    if (!lista.length) { cont.innerHTML = '<p class="text-muted">Ningún laboratorio seleccionado.</p>'; return; }
    var html = "";
    lista.forEach(function (ex) {
        html += '<div class="d-flex justify-content-between align-items-center border rounded p-2 mb-2">' +
            '<div><strong>' + ex.nombre + '</strong><br><small class="text-muted">' + ex.tipo + '</small></div>' +
            '<button type="button" class="btn btn-link text-danger p-0" onclick="toggleLaboratorio(' + ex.id_examen + ', \'\', \'\')"><i class="fa fa-trash"></i></button></div>';
    });
    cont.innerHTML = html;
}

function confirmarSeleccionLaboratorios() {
    var lista = Object.values(labSeleccion);
    if (!lista.length) { $("#modalLaboratorios").modal("hide"); return; }

    var body = new URLSearchParams();
    body.append("consultaCarritoIdCita", idCitaConsulta); // variable global definida en panel_consulta.php
    body.append("examenes", JSON.stringify(lista));

    fetch("index.php", { method: "POST", body: body })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            renderCarritoConsulta(data.carrito); // función del Bloque 5, repinta el panel principal
            $("#modalLaboratorios").modal("hide");
        });
}
</script>