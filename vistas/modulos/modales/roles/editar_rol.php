<div class="modal fade" id="modalEditarRol" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
    <form method="POST" class="pt-3">
      <div class="modal-header">
        <h5 class="pt-3">Editar rol</h5>
      </div>
      <div class="modal-body">
        <input type="hidden" name="editarIdRol" id="editarIdRol">
        <div class="form-group">
          <div class="input-group">
            <span class="input-group-addon"><i class="fa fa-shield"></i></span>
            <input type="text" class="form-control input-lg" name="editarNombreRol"
                   id="editarNombreRol" placeholder="Nombre del rol" required>
          </div>
        </div>
        <div class="form-group pt-2">
          <label><strong>Permisos asignados:</strong></label>
          <div id="checkboxesEditarRol">
            <?php
              $todosPermisos = ControladorPermisos::ctrMostrarPermisos(null, null);
              foreach ($todosPermisos as $permiso):
            ?>
            <div class="form-check">
              <input class="form-check-input checkbox-editar-permiso" type="checkbox"
                     name="permisosEditarRol[]"
                     value="<?= $permiso['id_permiso'] ?>"
                     id="editarPermiso_<?= $permiso['id_permiso'] ?>">
              <label class="form-check-label" for="editarPermiso_<?= $permiso['id_permiso'] ?>">
                <?= htmlspecialchars($permiso['modulo']) ?>
                <small class="text-muted">(<?= htmlspecialchars($permiso['nombre']) ?>)</small>
              </label>
            </div>
            <?php endforeach; ?>
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