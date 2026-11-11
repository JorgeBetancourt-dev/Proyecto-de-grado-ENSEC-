<!-- Modal: Laboratorios -->
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