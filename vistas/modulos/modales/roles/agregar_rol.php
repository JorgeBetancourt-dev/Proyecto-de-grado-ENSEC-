<div class="modal fade" id="modalAgregarRol" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
    <form method="POST" class="pt-3">
      <div class="modal-header">
        <h5 class="pt-3">Registrar rol</h5>
      </div>
      <div class="modal-body">
       <div class="form-group">
          <div style="position:relative;">
            <span class="position-icon"><i class="ti-user"></i></span>
            <input type="text" class="form-control input-lg" name="nuevoNombreRol" placeholder="Nombre del rol" style="padding-left:35px;" required>
          </div>
        </div>
        <div class="form-group pt-2">
          <label><strong>Asignar permisos:</strong></label>
          <?php
            $todosPermisos = ControladorPermisos::ctrMostrarPermisos(null, null);
            $permisosAgrupados = [];
            foreach ($todosPermisos as $permiso) {
                $permisosAgrupados[$permiso["nombre"]][] = $permiso;
            }
            foreach ($permisosAgrupados as $grupo => $permisos):
              $grupoId = "grupo_agregar_" . $grupo;
          ?>
          <div class="pt-2">
            <div class="d-flex align-items-center justify-content-between">
              <label class="text-uppercase font-weight-bold mb-0" style="color:#6c63ff; font-size:0.85rem;">
                <i class="fa fa-folder-open"></i> <?= htmlspecialchars($grupo) ?>
              </label>
              <div class="d-flex align-items-center">
                <small class="mr-1 text-muted">Seleccionar todos</small>
                <input type="checkbox" class="checkbox-grupo-agregar"
                       data-grupo="<?= $grupoId ?>">
              </div>
            </div>
            <div class="pl-3" id="<?= $grupoId ?>">
              <?php foreach ($permisos as $permiso): ?>
              <div class="form-check">
                <input class="form-check-input checkbox-hijo-agregar" type="checkbox"
                       name="permisosRol[]"
                       value="<?= $permiso['id_permiso'] ?>"
                       id="permiso_<?= $permiso['id_permiso'] ?>">
                <label class="form-check-label" for="permiso_<?= $permiso['id_permiso'] ?>">
                  <?= htmlspecialchars($permiso['modulo']) ?>
                </label>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" data-dismiss="modal" class="btn btn-default">Cerrar</button>
        <button type="submit" class="btn btn-primary">Registrar rol</button>
      </div>
    </form>
    </div>
  </div>
</div>

<script>
document.addEventListener("change", function(e) {
    if (e.target.classList.contains("checkbox-grupo-agregar")) {
        var grupoId = e.target.getAttribute("data-grupo");
        var hijos   = document.querySelectorAll("#" + grupoId + " .checkbox-hijo-agregar");
        hijos.forEach(function(hijo) {
            hijo.checked = e.target.checked;
        });
    }
});
</script>