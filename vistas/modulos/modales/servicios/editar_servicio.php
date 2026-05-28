<div class="modal fade" id="modalEditarServicio" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
    <form method="POST" class="pt-3">
      <div class="modal-header">       
        <h5 class="pt-3">Editar servicio</h5>
      </div>
      <div class="modal-body">
        <input type="hidden" name="editarIdServicio" id="editarIdServicio">
        <div class="form-group">              
          <div class="input-group">              
            <span class="input-group-addon"><i class="fa fa-user"></i></span>
            <input type="text" class="form-control input-lg" name="editarNombre" id="editarNombre" placeholder="Ingresar nombre" required>
          </div>
        </div>
        <div class="form-group">              
          <div class="input-group">              
            <span class="input-group-addon"><i class="fa fa-user"></i></span>
            <input type="text" class="form-control input-lg" name="editarPrecio" id="editarPrecio" placeholder="Ingresar precio" required>
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