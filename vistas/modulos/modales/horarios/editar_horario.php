<div class="modal fade" id="modalEditarHorario" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form method="POST" class="pt-3">
        <div class="modal-header">
          <h5 class="pt-3"><i class="fa fa-pencil"></i> Editar horario</h5>
        </div>
        <div class="modal-body">

          <input type="hidden" name="editarIdHorario" id="editarIdHorario">

          <div class="form-group">
            <div style="position:relative;">
              <span class="position-icon"><i class="fa fa-tag"></i></span>
              <input type="text" class="form-control input-lg" name="editarNombreHorario"
                     id="editarNombreHorario" placeholder="Nombre del horario"
                     style="padding-left:35px;" required>
            </div>
          </div>

          <hr>
          <p class="text-muted" style="margin-bottom:10px;"><strong>Lunes a Viernes</strong></p>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Hora inicio</label>
                <input type="time" class="form-control" name="editarHoraInicio" id="editarHoraInicio" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Hora fin</label>
                <input type="time" class="form-control" name="editarHoraFin" id="editarHoraFin" required>
              </div>
            </div>
          </div>

          <hr>
          <p class="text-muted" style="margin-bottom:10px;"><strong>Sábados</strong></p>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Hora inicio</label>
                <input type="time" class="form-control" name="editarHoraISabado" id="editarHoraISabado" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Hora fin</label>
                <input type="time" class="form-control" name="editarHoraFSabado" id="editarHoraFSabado" required>
              </div>
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