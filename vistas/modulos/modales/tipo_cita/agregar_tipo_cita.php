<div class="modal fade" id="modalAgregarTipoCita" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form method="POST" class="pt-3">
        <div class="modal-header">
          <h5 class="pt-3"><i class="fa fa-tag"></i> Registrar tipo de cita</h5>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <div style="position:relative;">
              <span class="position-icon"><i class="fa fa-tag"></i></span>
              <input type="text" class="form-control input-lg" name="nuevoNombreTipoCita"
                     placeholder="Nombre del tipo de cita" style="padding-left:35px;" required>
            </div>
          </div>
          <div class="form-group">
            <label>Duración (minutos)</label>
            <div style="position:relative;">
              <span class="position-icon"><i class="fa fa-clock-o"></i></span>
              <input type="number" class="form-control input-lg" name="nuevoTiempoCita"
                     placeholder="Ej: 40" min="1" style="padding-left:35px;" required>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" data-dismiss="modal" class="btn btn-default">Cerrar</button>
          <button type="submit" class="btn btn-primary">
            <i class="fa fa-save"></i> Registrar
          </button>
        </div>
      </form>
    </div>
  </div>
</div>