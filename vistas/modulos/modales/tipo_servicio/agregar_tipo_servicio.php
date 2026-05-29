<div class="modal fade" id="modalAgregarTipoServicio" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
    <form method="POST" class="pt-3">
      <div class="modal-header">
        <h5 class="pt-3">Registrar tipo de servicio</h5>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <div class="input-group">
            <span class="input-group-addon"><i class="fa fa-tag"></i></span>
            <input type="text" class="form-control input-lg" name="nuevoNombreTipoServicio"
                   placeholder="Ingresar nombre" required>
          </div>
        </div>
        <div class="form-group">
          <div class="input-group">
            <span class="input-group-addon"><i class="fa fa-usd"></i></span>
            <input type="number" step="0.01" min="0" class="form-control input-lg"
                   name="nuevoPrecioTipoServicio" placeholder="Ingresar precio" required>
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