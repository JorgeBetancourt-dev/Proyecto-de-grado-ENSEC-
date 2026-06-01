<div class="modal fade" id="modalEditarPaciente" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
    <form method="POST" class="pt-3">
      <div class="modal-header">
        <h5 class="pt-3">Editar paciente</h5>
      </div>
      <div class="modal-body">
        <input type="hidden" name="editarIdPaciente" id="editarIdPaciente">
        <div class="form-group">
          <div style="position:relative;">
            <span class="position-icon"><i class="ti-user"></i></span>
            <input type="text" class="form-control input-lg" name="editarNombre" id="editarNombre" placeholder="Ingresar nombre" style="padding-left:35px;" required>
          </div>
        </div>
        <div class="form-group">
          <div style="position:relative;">
            <span class="position-icon"><i class="ti-user"></i></span>
            <input type="text" class="form-control input-lg" name="editarApellidos" id="editarApellidos" placeholder="Ingresar apellidos" style="padding-left:35px;" required>
          </div>
        </div>
        <div class="form-group">
          <div style="position:relative;">
            <span class="position-icon"><i class="ti-id-badge"></i></span>
            <input type="text" class="form-control input-lg" name="editarCI" id="editarCI" placeholder="Ingresar C.I." style="padding-left:35px;" required>
          </div>
        </div>
        <div class="form-group">
          <div style="position:relative;">
            <span class="position-icon"><i class="ti-heart"></i></span>
            <input type="text" class="form-control input-lg" name="editarGrupoSanguineo" id="editarGrupoSanguineo" placeholder="Grupo sanguíneo (ej: A+)" style="padding-left:35px;" required>
          </div>
        </div>
        <div class="form-group">
          <div style="position:relative;">
            <span class="position-icon"><i class="ti-mobile"></i></span>
            <input type="text" class="form-control input-lg" name="editarTelefono" id="editarTelefono" placeholder="Ingresar teléfono" style="padding-left:35px;" required>
          </div>
        </div>
        <div class="form-group">
          <div style="position:relative;">
            <span class="position-icon"><i class="ti-calendar"></i></span>
            <input type="date" class="form-control input-lg" name="editarFechaNacimiento" id="editarFechaNacimiento" min="1930-01-01" max="<?= date('Y-m-d') ?>" style="padding-left:35px;" required>
          </div>
        </div>
        <div class="form-group">
          <div style="position:relative;">
            <span class="position-icon"><i class="ti-location-pin"></i></span>
            <input type="text" class="form-control input-lg" name="editarDireccion" id="editarDireccion" placeholder="Ingresar dirección" style="padding-left:35px;" required>
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