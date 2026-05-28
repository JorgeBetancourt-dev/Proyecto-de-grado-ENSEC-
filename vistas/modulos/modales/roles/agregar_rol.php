<div class="modal fade" id="modalAgregarRol" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
    <form method="POST" class="pt-3">
      <div class="modal-header">
        <h5 class="pt-3">Registrar rol</h5>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <div class="input-group">
            <span class="input-group-addon"><i class="fa fa-shield"></i></span>
            <input type="text" class="form-control input-lg" name="nuevoNombreRol" 
                   placeholder="Nombre del rol" required>
          </div>
        </div>
        <div class="form-group pt-2">
          <label><strong>Asignar permisos:</strong></label>
          <?php 
            $todosPermisos = ControladorPermisos::ctrMostrarPermisos(null, null);
            foreach ($todosPermisos as $permiso): 
          ?>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" 
                   name="permisosRol[]" 
                   value="<?= $permiso['id_permiso'] ?>" 
                   id="permiso_<?= $permiso['id_permiso'] ?>">
            <label class="form-check-label" for="permiso_<?= $permiso['id_permiso'] ?>">
              <?= htmlspecialchars($permiso['modulo']) ?> 
              <small class="text-muted">(<?= htmlspecialchars($permiso['nombre']) ?>)</small>
            </label>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" data-dismiss="modal" class="btn btn-primary">Cerrar</button>
        <button type="submit" class="btn btn-primary">Registrar rol</button>
      </div>
    </form>
    </div>
  </div>
</div>