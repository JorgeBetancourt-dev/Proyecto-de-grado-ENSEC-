<?php
if (session_status() == PHP_SESSION_NONE) session_start();
include "vistas/modulos/modales/tipo_cita/agregar_tipo_cita.php";
include "vistas/modulos/modales/tipo_cita/editar_tipo_cita.php";
include "vistas/assets/eventos/tipo_cita.eventos.php";
?>

<div class="mb-3">
  <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalAgregarTipoCita">
    <i class="fa fa-plus"></i> Agregar tipo de cita
  </button>
</div>

<div class="row">
  <div class="col-6">
    <div class="box-body pt-3">
      <table class="table table-bordered table-striped tablas" width="100%">
        <thead>
          <tr>
            <th>#</th>
            <th>Nombre</th>
            <th>Duración</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php
            $tipos = ControladorTipoCita::ctrMostrarTiposCita(null, null);
            foreach ($tipos as $t):
              $id = $t["id_tipo_cita"];
          ?>
          <tr>
            <td><?= $id ?></td>
            <td><?= htmlspecialchars($t["nombre"]) ?></td>
            <td><?= $t["tiempo"] ?> min</td>
            <td>
              <button class="btn btn-warning btnEditarTipoCita"
                data-id="<?= $id ?>"
                data-nombre="<?= htmlspecialchars($t["nombre"]) ?>"
                data-tiempo="<?= $t["tiempo"] ?>"
                data-toggle="modal"
                data-target="#modalEditarTipoCita">
                <i class="fa fa-pencil"></i> Editar
              </button>
              <button class="btn btn-danger btnEliminarTipoCita" data-id="<?= $id ?>">
                <i class="fa fa-trash"></i> Eliminar
              </button>
              <form id="formEliminarTipoCita-<?= $id ?>" method="POST" style="display:none;">
                <input type="hidden" name="eliminarTipoCita" value="<?= $id ?>">
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>