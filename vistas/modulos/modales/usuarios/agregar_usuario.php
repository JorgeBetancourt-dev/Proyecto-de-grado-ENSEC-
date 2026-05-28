<div class="modal fade" id="modalAgregarUsuario" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form method="POST" class="pt-3">
        <div class="modal-header">
          <h5 class="pt-3">Registrar usuario</h5>
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
              <input type="text" class="form-control input-lg" name="nuevoApellido" placeholder="Ingresar apellido" required>
            </div>
          </div>

          <div class="form-group">
            <div class="input-group">
              <span class="input-group-addon"><i class="fa fa-key"></i></span>
              <input type="text" class="form-control input-lg" name="nuevoUsuario" placeholder="Ingresar usuario" required>
            </div>
          </div>

          <div class="form-group">
            <div class="input-group">
              <span class="input-group-addon"><i class="fa fa-lock"></i></span>
              <input type="password" class="form-control input-lg" name="nuevoPassword" placeholder="Ingresar contraseña" required>
            </div>
          </div>

          <!-- Selector de rol dinámico desde la BD -->
          <div class="form-group">
            <div class="input-group">
              <select class="form-control input-lg" name="nuevoIdRol" required>
                <option value="">Selecciona un rol</option>
                <?php
                  $roles = ControladorRoles::ctrMostrarRoles(null, null);
                  foreach ($roles as $rol):
                ?>
                  <option value="<?= $rol["id_rol"] ?>">
                    <?= htmlspecialchars($rol["nombre"]) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" data-dismiss="modal" class="btn btn-default">Cerrar</button>
          <button type="submit" class="btn btn-primary">Registrar nuevo usuario</button>
        </div>
      </form>
    </div>
  </div>
</div>