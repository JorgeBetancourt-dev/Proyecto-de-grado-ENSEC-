<?php
if (session_status() == PHP_SESSION_NONE) session_start();
include "vistas/modulos/modales/usuarios/agregar_usuario.php";
include "vistas/modulos/modales/usuarios/editar_usuario.php";
include "vistas/assets/eventos/usuarios.eventos.php";
?>
<div>
  <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalAgregarUsuario">
    Agregar nuevo usuario
  </button>
  <a type="button" class="btn btn-primary" href="/Marie_stopes_pruebas/panel_acceso">Administrar Accesos</a>
</div>

<div class="row">
  <div class="col-12">
    <div class="box-body pt-3">
      <table class="table table-bordered table-striped tablas" width="100%">
        <thead>
          <tr>
            <th>#</th>
            <th>Nombre</th>
            <th>Apellido</th>
            <th>Usuario</th>
            <th>Rol</th>
            <th>Horario</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php
            $usuarios = ControladorUsuarios::ctrMostrarUsuarios(null, null);
            foreach ($usuarios as $value):
              $id = $value["id_usuario"];
          ?>
          <tr>
            <td><?= $id ?></td>
            <td><?= htmlspecialchars($value["nombre"]) ?></td>
            <td><?= htmlspecialchars($value["apellido"]) ?></td>
            <td><?= htmlspecialchars($value["usuario"]) ?></td>
            <td><?= htmlspecialchars($value["nombre_rol"] ?? "Sin rol") ?></td>
            <td><?= htmlspecialchars($value["nombre_horario"] ?? "Sin horario") ?></td>
            <td>
              <button class="btn btn-warning btnEditarUsuario"
                data-id="<?= $id ?>"
                data-nombre="<?= htmlspecialchars($value["nombre"]) ?>"
                data-apellido="<?= htmlspecialchars($value["apellido"]) ?>"
                data-usuario="<?= htmlspecialchars($value["usuario"]) ?>"
                data-id-rol="<?= $value["id_rol"] ?>"
                data-id-horario="<?= $value["id_horario"] ?>"
                data-toggle="modal"
                data-target="#modalEditarUsuario">
                Editar
              </button>
              <button class="btn btn-danger btnEliminarUsuario" data-id="<?= $id ?>">
                Eliminar
              </button>
              <form id="formEliminar-<?= $id ?>" method="POST" style="display:none;">
                <input type="hidden" name="eliminarUsuario" value="<?= $id ?>">
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>