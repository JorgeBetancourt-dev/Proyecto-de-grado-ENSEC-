<div class="modal fade" id="modalAgregarPaciente" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
    <form method="POST" class="pt-3">
      <div class="modal-header">
        <h5 class="pt-3">Registrar paciente</h5>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <div class="input-group">
            <span class="input-group-addon"><i class="fa fa-user"></i></span>
            <input type="text" class="form-control input-lg" name="nuevoNombre"
                   placeholder="Ingresar nombre" required>
          </div>
        </div>
        <div class="form-group">
          <div class="input-group">
            <span class="input-group-addon"><i class="fa fa-user"></i></span>
            <input type="text" class="form-control input-lg" name="nuevoApellidos"
                   placeholder="Ingresar apellidos" required>
          </div>
        </div>
        <div class="form-group">
          <div class="input-group">
            <span class="input-group-addon"><i class="fa fa-id-card"></i></span>
            <input type="text" class="form-control input-lg" name="nuevoCI"
                   placeholder="Ingresar C.I." required>
          </div>
        </div>
        <div class="form-group">
          <div class="input-group">
            <span class="input-group-addon"><i class="fa fa-tint"></i></span>
            <input type="text" class="form-control input-lg" name="nuevoGrupoSanguineo"
                   placeholder="Grupo sanguíneo (ej: A+)" required>
          </div>
        </div>
        <div class="form-group">
          <div class="input-group">
            <span class="input-group-addon"><i class="fa fa-phone"></i></span>
            <input type="text" class="form-control input-lg" name="nuevoTelefono"
                   placeholder="Ingresar teléfono" required>
          </div>
        </div>
        <div class="form-group">
          <div class="input-group">
            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
            <input type="date" class="form-control input-lg" name="nuevaFechaNacimiento" required>
          </div>
        </div>
        <div class="form-group">
          <div class="input-group">
            <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
            <input type="text" class="form-control input-lg" name="nuevaDireccion"
                   placeholder="Ingresar dirección" required>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" data-dismiss="modal" class="btn btn-default">Cerrar</button>
        <button type="submit" class="btn btn-primary">Registrar paciente</button>
      </div>
    </form>
    </div>
  </div>
</div>