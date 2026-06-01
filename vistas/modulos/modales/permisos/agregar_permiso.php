<div class="modal fade" id="modalAgregarPermiso" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
    <form method="POST" class="pt-3">
      <div class="modal-header">
        <h5 class="pt-3">Registrar permiso</h5>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <div style="position:relative;">
            <span class="position-icon"><i class="ti-key"></i></span>
            <input type="text" class="form-control input-lg" name="nuevoModulo" placeholder="Módulo (ej: crear_usuario)" style="padding-left:35px;" required>
          </div>
        </div>
        <div class="form-group">
          <div style="position:relative;">
            <span class="position-icon"><i class="ti-tag"></i></span>
            <input type="text" class="form-control input-lg" name="nuevoNombrePermiso" placeholder="Nombre (ej: usuario)" style="padding-left:35px;" required>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" data-dismiss="modal" class="btn btn-primary">Cerrar</button>
        <button type="submit" class="btn btn-primary">Registrar permiso</button>
      </div>
    </form>
    </div>
  </div>
</div>