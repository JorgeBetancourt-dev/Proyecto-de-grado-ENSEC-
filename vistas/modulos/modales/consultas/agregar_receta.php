<!-- Modal: Recetas -->

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
        <button type="button" class="btn btn-outline-primary" onclick="generarRecetaPDF()">
            <i class="fa fa-file-pdf"></i> Generar Receta
        </button>
        <button type="button" class="btn btn-primary" onclick="confirmarSeleccionMedicamentos()">Confirmar Selección</button>
      </div>
    </div>
  </div>
</div>