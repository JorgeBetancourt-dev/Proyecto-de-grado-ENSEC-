<?php
if (session_status() == PHP_SESSION_NONE) session_start();
include "vistas/modulos/modales/roles/agregar_rol.php";
include "vistas/modulos/modales/roles/editar_rol.php";
include "vistas/modulos/modales/permisos/agregar_permiso.php";
include "vistas/modulos/modales/permisos/editar_permiso.php";
include "vistas/assets/eventos/permisos.eventos.php";
include "vistas/assets/eventos/roles.eventos.php";

$todosPermisos = ControladorPermisos::ctrMostrarPermisos(null, null);
?>

<div class="row pt-3">
  <div class="col-12">
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalAgregarRol">
      Agregar nuevo rol
    </button>
    <button type="button" id="btnResetPermisos" class="btn btn-primary">
      Mostrar todos los permisos
    </button>
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalAgregarPermiso">
      Agregar nuevo permiso
    </button>
  </div>
</div>

<div class="row pt-3">

  <!-- Tabla de Roles -->
  <div class="col-6 pt-3">
    <div class="box-body">
      <div class="pb-2"><h3>Roles</h3></div>
      <table class="table table-bordered table-striped tablas" width="100%">
        <thead>
          <tr>
            <th>#</th>
            <th>Nombre</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php
            $roles = ControladorRoles::ctrMostrarRoles(null, null);
            foreach ($roles as $rol):
              $id  = $rol["id_rol"];
              $nom = htmlspecialchars($rol["nombre"]);
          ?>
          <tr>
            <td><?= $id ?></td>
            <td><?= $nom ?></td>
            <td>
              <button type="button" class="btn btn-primary btn-ver-permisos"
                      data-id="<?= $id ?>" data-nombre="<?= $nom ?>">
                Ver permisos
              </button>
              <button type="button" class="btn btn-warning btnEditarRol"
                data-id="<?= $id ?>" data-nombre="<?= $nom ?>">
                Editar
              </button>
              <button type="button" class="btn btn-danger btnEliminarRol"
                      data-id="<?= $id ?>">
                Eliminar
              </button>
              <form id="formEliminarRol-<?= $id ?>" method="POST" style="display:none;">
                <input type="hidden" name="eliminarRol" value="<?= $id ?>">
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Panel de Permisos -->
  <div class="col-6 pt-3">
    <div class="box-body">
      <div class="pb-3">
        <h3 style="display:inline;">Permisos</h3>
        <h3 style="display:inline; visibility:hidden;" id="tituloFiltro"> filtrados por </h3>
        <h3 style="display:inline; visibility:hidden; color:#6c63ff;" id="nombreRolFiltro"></h3>
      </div>
      <table class="table table-bordered table-striped tablas" width="100%">
        <thead>
          <tr>
            <th>#</th>
            <th>Módulo</th>
            <th>Nombre</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody id="tablaPermisosFiltrados">
          <?php foreach ($todosPermisos as $i => $p): ?>
          <tr>
            <td><?= $i + 1 ?></td>
            <td><?= htmlspecialchars($p["modulo"]) ?></td>
            <td><?= htmlspecialchars($p["nombre"]) ?></td>
            <td>
              <button type="button" class="btn btn-warning btnEditarPermiso"
                data-id="<?= $p['id_permiso'] ?>"
                data-modulo="<?= htmlspecialchars($p['modulo']) ?>"
                data-nombre="<?= htmlspecialchars($p['nombre']) ?>">
                Editar
              </button>
              <button type="button" class="btn btn-danger btnEliminarPermiso"
                data-id="<?= $p['id_permiso'] ?>">
                Eliminar
              </button>
              <form id="formEliminarPermiso-<?= $p['id_permiso'] ?>" method="POST" style="display:none;">
                <input type="hidden" name="eliminarPermiso" value="<?= $p['id_permiso'] ?>">
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
var todosLosPermisos = <?= json_encode(array_values($todosPermisos)) ?>;

function renderizarTodosPermisos() {
  var tbody = document.getElementById("tablaPermisosFiltrados");
  tbody.innerHTML = "";

  todosLosPermisos.forEach(function(p, i) {
    tbody.innerHTML +=
      '<tr>' +
        '<td>' + (i + 1) + '</td>' +
        '<td>' + p.modulo + '</td>' +
        '<td>' + p.nombre + '</td>' +
        '<td>' +
          '<button type="button" class="btn btn-warning btnEditarPermiso" ' +
            'data-id="' + p.id_permiso + '" ' +
            'data-modulo="' + p.modulo + '" ' +
            'data-nombre="' + p.nombre + '">Editar</button> ' +
          '<button type="button" class="btn btn-danger btnEliminarPermiso" ' +
            'data-id="' + p.id_permiso + '">Eliminar</button>' +
          '<form id="formEliminarPermiso-' + p.id_permiso + '" method="POST" style="display:none;">' +
            '<input type="hidden" name="eliminarPermiso" value="' + p.id_permiso + '">' +
          '</form>' +
        '</td>' +
      '</tr>';
  });

  document.getElementById("tituloFiltro").style.visibility    = "hidden";
  document.getElementById("nombreRolFiltro").style.visibility = "hidden";
}

document.addEventListener("DOMContentLoaded", function () {

  document.getElementById("btnResetPermisos").addEventListener("click", function() {
    renderizarTodosPermisos();
  });

  document.querySelectorAll(".btn-ver-permisos").forEach(function (btn) {
    btn.addEventListener("click", function () {
      var idRol     = this.dataset.id;
      var nombreRol = this.dataset.nombre;

      document.getElementById("tituloFiltro").style.visibility    = "visible";
      document.getElementById("nombreRolFiltro").style.visibility = "visible";
      document.getElementById("nombreRolFiltro").textContent = nombreRol;

      fetch("/Marie_stopes_pruebas/index.php?action=getPermisosPorRol&id_rol=" + idRol)
        .then(function(res) { return res.json(); })
        .then(function(permisos) {
          var tbody = document.getElementById("tablaPermisosFiltrados");
          tbody.innerHTML = "";

          if (permisos.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">Sin permisos asignados</td></tr>';
            return;
          }

          permisos.forEach(function(p, i) {
            tbody.innerHTML +=
              '<tr>' +
                '<td>' + (i + 1) + '</td>' +
                '<td>' + p.modulo + '</td>' +
                '<td>' + p.nombre + '</td>' +
                '<td>' +
                  '<button type="button" class="btn btn-warning btnEditarPermiso" ' +
                    'data-id="' + p.id_permiso + '" ' +
                    'data-modulo="' + p.modulo + '" ' +
                    'data-nombre="' + p.nombre + '">Editar</button> ' +
                  '<button type="button" class="btn btn-danger btnEliminarPermiso" ' +
                    'data-id="' + p.id_permiso + '">Eliminar</button>' +
                  '<form id="formEliminarPermiso-' + p.id_permiso + '" method="POST" style="display:none;">' +
                    '<input type="hidden" name="eliminarPermiso" value="' + p.id_permiso + '">' +
                  '</form>' +
                '</td>' +
              '</tr>';
          });
        });
    });
  });
});
</script>