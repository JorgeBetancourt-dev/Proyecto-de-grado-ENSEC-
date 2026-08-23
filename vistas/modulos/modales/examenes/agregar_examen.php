<div class="modal fade" id="modalAgregarExamen" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
    <form method="POST" class="pt-3">
      <div class="modal-header">
        <h5 class="pt-3">Registrar examen</h5>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <div style="position:relative;">
            <span class="position-icon"><i class="fa fa-flask"></i></span>
            <input type="text" class="form-control input-lg" name="nuevoNombreExamen" placeholder="Ingresar nombre del examen" style="padding-left:35px;" required>
          </div>
        </div>
        <div class="form-group">
          <div style="position:relative;">
            <span class="position-icon"><i class="fa fa-dollar-sign"></i></span>
            <input type="number" step="0.01" min="0" class="form-control input-lg" name="nuevoPrecioExamen" placeholder="Ingresar precio" style="padding-left:35px;" required>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" data-dismiss="modal" class="btn btn-default">Cerrar</button>
        <button type="submit" class="btn btn-primary">Registrar</button>
      </div>
    </form>
    </div>
  </div>
</div>