<div class="modal fade" id="modalEditarTipoServicio" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
    <form method="POST" class="pt-3">
      <div class="modal-header">
        <h5 class="pt-3">Editar servicio</h5>
      </div>
      <div class="modal-body">
        <input type="hidden" name="editarIdTipoServicio" id="editarIdTipoServicio">
        <div class="form-group">
          <div style="position:relative;">
            <span class="position-icon"><i class="ti-tag"></i></span>
            <input type="text" class="form-control input-lg" name="editarNombreTipoServicio" id="editarNombreTipoServicio" placeholder="Ingresar nombre" style="padding-left:35px;" required>
          </div>
        </div>
        <div class="form-group">
          <div style="position:relative;">
            <span class="position-icon"><i class="ti-money"></i></span>
            <input type="number" step="0.01" min="0" class="form-control input-lg" name="editarPrecioTipoServicio" id="editarPrecioTipoServicio" placeholder="Ingresar precio" style="padding-left:35px;" required>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" data-dismiss="modal" class="btn btn-default">Cerrar</button>
        <button type="submit" class="btn btn-success">Guardar cambios</button>
      </div>
    </form>
    </div>
  </div>
</div>