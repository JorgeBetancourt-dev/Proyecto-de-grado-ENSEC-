<div class="modal fade" id="modalEditarPermiso" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
    <form method="POST" class="pt-3">
      <div class="modal-header">
        <h5 class="pt-3">Editar permiso</h5>
      </div>
      <div class="modal-body">
        <input type="hidden" name="editarIdPermiso" id="editarIdPermiso">
        <div class="form-group">
          <div class="input-group">
            <span class="input-group-addon"><i class="fa fa-key"></i></span>
            <input type="text" class="form-control input-lg" name="editarModulo"
                   id="editarModulo" placeholder="Módulo (ej: crear_usuario)" required>
          </div>
        </div>
        <div class="form-group">
          <div class="input-group">
            <span class="input-group-addon"><i class="fa fa-tag"></i></span>
            <input type="text" class="form-control input-lg" name="editarNombrePermiso"
                   id="editarNombrePermiso" placeholder="Nombre (ej: usuario)" required>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" data-dismiss="modal" class="btn btn-primary">Cerrar</button>
        <button type="submit" class="btn btn-success">Guardar cambios</button>
      </div>
    </form>
    </div>
  </div>
</div>