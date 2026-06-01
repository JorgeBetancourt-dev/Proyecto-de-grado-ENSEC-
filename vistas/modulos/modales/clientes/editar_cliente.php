<div class="modal fade" id="modalEditarCliente" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
    <form method="POST" class="pt-3">
      <div class="modal-header">
        <h5 class="pt-3">Editar cliente</h5>
      </div>
      <div class="modal-body">
        <input type="hidden" name="editarIdCliente" id="editarIdCliente">
        <div class="form-group">
          <div style="position:relative;">
            <span class="position-icon"><i class="ti-user"></i></span>
            <input type="text" class="form-control input-lg" name="editarNombreCliente" id="editarNombreCliente" placeholder="Ingresar nombre" style="padding-left:35px;" required>
          </div>
        </div>
        <div class="form-group">
          <div style="position:relative;">
            <span class="position-icon"><i class="ti-user"></i></span>
            <input type="text" class="form-control input-lg" name="editarApellidosCliente" id="editarApellidosCliente" placeholder="Ingresar apellidos" style="padding-left:35px;" required>
          </div>
        </div>
        <div class="form-group">
          <div style="position:relative;">
            <span class="position-icon"><i class="ti-id-badge"></i></span>
            <input type="text" class="form-control input-lg" name="editarNitCliente" id="editarNitCliente" placeholder="Ingresar NIT (opcional)" style="padding-left:35px;">
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