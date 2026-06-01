<div class="modal fade" id="modalEditarUsuario" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form method="POST" class="pt-3">
        <div class="modal-header">
          <h5 class="pt-3">Editar usuario</h5>
        </div>
        <div class="modal-body">
          <input type="hidden" name="editarIdUsuario" id="editarIdUsuario">
          <div class="form-group">
            <div style="position:relative;">
              <span class="position-icon"><i class="ti-user"></i></span>
              <input type="text" class="form-control input-lg" name="editarNombre" id="editarNombre" placeholder="Ingresar nombre" style="padding-left:35px;" required>
            </div>
          </div>
          <div class="form-group">
            <div style="position:relative;">
              <span class="position-icon"><i class="ti-user"></i></span>
              <input type="text" class="form-control input-lg" name="editarApellido" id="editarApellido" placeholder="Ingresar apellido" style="padding-left:35px;" required>
            </div>
          </div>
          <div class="form-group">
            <div style="position:relative;">
              <span class="position-icon"><i class="ti-id-badge"></i></span>
              <input type="text" class="form-control input-lg" name="editarUsuario" id="editarUsuario" placeholder="Ingresar usuario" style="padding-left:35px;" required>
            </div>
          </div>
          <div class="form-group">
            <select class="form-control input-lg" name="editarIdRol" id="editarIdRol" required>
              <option value="">Selecciona un rol</option>
              <?php
                $roles = ControladorRoles::ctrMostrarRoles(null, null);
                foreach ($roles as $rol):
              ?>
                <option value="<?= $rol["id_rol"] ?>"><?= htmlspecialchars($rol["nombre"]) ?></option>
              <?php endforeach; ?>
            </select>
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

