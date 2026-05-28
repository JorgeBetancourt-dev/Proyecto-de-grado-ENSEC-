<div class="modal fade" id="modalAgregarServicio" tabindex="-1" role="dialog" >
  <div class="modal-dialog" role="document">
    <div class="modal-content">
    <form method="POST" class="pt-3">
      <div class="modal-header">       
              <h5 class="pt-3">Registrar servicio</h5>
      </div>
      <div class="modal-body">
        <div class="form-group">              
          <div class="input-group">              
            <span class="input-group-addon"><i class="fa fa-user"></i></span>
            <input type="text" class="form-control input-lg" name="nuevoNombre" placeholder="Ingresar nombre" required>
          </div>
        </div>
        <div class="form-group">              
          <div class="input-group">              
            <span class="input-group-addon"><i class="fa fa-user"></i></span>
            <input type="text" class="form-control input-lg" name="nuevoPrecio" placeholder="Ingresar precio" required>
          </div>
        </div>         
      </div>
      <div class="modal-footer">
          <button type="button" data-dismiss="modal" class="btn btn-primary">Cerrar</button>
          <button type="submit" class="btn btn-primary">Registrar nuevo servicio</button>
      </div>
    </form>
    </div>
  </div>
</div>