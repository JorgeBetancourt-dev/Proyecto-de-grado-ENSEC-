<div class="modal fade" id="modalAgregarHorario" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form method="POST" class="pt-3">
        <div class="modal-header">
          <h5 class="pt-3"><i class="fa fa-clock-o"></i> Registrar horario</h5>
        </div>
        <div class="modal-body">

          <div class="form-group">
            <div style="position:relative;">
              <span class="position-icon"><i class="fa fa-tag"></i></span>
              <input type="text" class="form-control input-lg" name="nuevoNombreHorario"
                     placeholder="Nombre del horario" style="padding-left:35px;" required>
            </div>
          </div>

          <hr>
          <p class="text-muted" style="margin-bottom:10px;"><strong>Lunes a Viernes</strong></p>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Hora inicio</label>
                <input type="time" class="form-control" name="nuevoHoraInicio" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Hora fin</label>
                <input type="time" class="form-control" name="nuevoHoraFin" required>
              </div>
            </div>
          </div>

          <hr>
          <p class="text-muted" style="margin-bottom:10px;"><strong>Sábados</strong></p>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Hora inicio</label>
                <input type="time" class="form-control" name="nuevoHoraISabado" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Hora fin</label>
                <input type="time" class="form-control" name="nuevoHoraFSabado" required>
              </div>
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