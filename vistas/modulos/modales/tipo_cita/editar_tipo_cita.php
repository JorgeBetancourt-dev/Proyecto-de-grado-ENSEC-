<div class="modal fade" id="modalEditarTipoCita" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form method="POST" class="pt-3">
        <div class="modal-header">
          <h5 class="pt-3"><i class="fa fa-pencil"></i> Editar tipo de cita</h5>
        </div>
        <div class="modal-body">
          <input type="hidden" name="editarIdTipoCita" id="editarIdTipoCita">
          <div class="form-group">
            <div style="position:relative;">
              <span class="position-icon"><i class="fa fa-tag"></i></span>
              <input type="text" class="form-control input-lg" name="editarNombreTipoCita"
                     id="editarNombreTipoCita" placeholder="Nombre del tipo de cita"
                     style="padding-left:35px;" required>
            </div>
          </div>
          <div class="form-group">
            <label>Duración (minutos)</label>
            <div style="position:relative;">
              <span class="position-icon"><i class="fa fa-clock-o"></i></span>
              <input type="number" class="form-control input-lg" name="editarTiempoCita"
                     id="editarTiempoCita" placeholder="Ej: 40" min="1"
                     style="padding-left:35px;" required>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" data-dismiss="modal" class="btn btn-default">Cerrar</button>
          <button type="submit" class="btn btn-warning">
            <i class="fa fa-save"></i> Guardar cambios
          </button>
        </div>
      </form>
    </div>
  </div>
</div>