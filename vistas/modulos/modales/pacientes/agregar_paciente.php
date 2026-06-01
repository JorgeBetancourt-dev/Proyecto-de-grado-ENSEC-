<div class="modal fade" id="modalAgregarPaciente" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
    <form method="POST" class="pt-3">
      <div class="modal-header">
        <h5 class="pt-3">Registrar paciente</h5>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <div style="position:relative;">
            <span class="position-icon"><i class="ti-user"></i></span>
            <input type="text" class="form-control input-lg" name="nuevoNombre" placeholder="Ingresar nombre" style="padding-left:35px;" required>
          </div>
        </div>
        <div class="form-group">
          <div style="position:relative;">
            <span class="position-icon"><i class="ti-user"></i></span>
            <input type="text" class="form-control input-lg" name="nuevoApellidos" placeholder="Ingresar apellidos" style="padding-left:35px;" required>
          </div>
        </div>
        <div class="form-group">
          <div style="position:relative;">
            <span class="position-icon"><i class="ti-id-badge"></i></span>
            <input type="text" class="form-control input-lg" name="nuevoCI" placeholder="Ingresar C.I." style="padding-left:35px;" required>
          </div>
        </div>
        <div class="form-group">
          <div style="position:relative;">
            <span class="position-icon"><i class="ti-heart"></i></span>
            <input type="text" class="form-control input-lg" name="nuevoGrupoSanguineo" placeholder="Grupo sanguíneo (ej: A+)" style="padding-left:35px;" required>
          </div>
        </div>
        <div class="form-group">
          <div style="position:relative;">
            <span class="position-icon"><i class="ti-mobile"></i></span>
            <input type="text" class="form-control input-lg" name="nuevoTelefono" placeholder="Ingresar teléfono" style="padding-left:35px;" required>
          </div>
        </div>
        <div class="form-group">
          <div style="position:relative;">
            <span class="position-icon"><i class="ti-calendar"></i></span>
            <input type="date" class="form-control input-lg" name="nuevaFechaNacimiento" min="1930-01-01" max="<?= date('Y-m-d') ?>" style="padding-left:35px;" required>
          </div>
        </div>
        <div class="form-group">
          <div style="position:relative;">
            <span class="position-icon"><i class="ti-location-pin"></i></span>
            <input type="text" class="form-control input-lg" name="nuevaDireccion" placeholder="Ingresar dirección" style="padding-left:35px;" required>
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