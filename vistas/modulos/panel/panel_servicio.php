<?php
if (session_status() == PHP_SESSION_NONE) session_start();
include "vistas/modulos/modales/tipo_servicio/agregar_tipo_servicio.php";
include "vistas/modulos/modales/tipo_servicio/editar_tipo_servicio.php";
include "vistas/assets/eventos/tipo_servicio.eventos.php";
?>

<div>
  <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalAgregarTipoServicio">
    Agregar nuevo servicio
  </button>
</div>

<div class="row">
  <div class="col-8">
    <div class="box-body pt-3">
      <table class="table table-bordered table-striped tablas" width="100%">
        <thead>
          <tr>
            <th>#</th>
            <th>Nombre</th>
            <th>Precio</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php
            $tipos = ControladorTipoServicio::ctrMostrarTipoServicio(null, null);
            foreach ($tipos as $value):
              $id = $value["id_servicio"];
          ?>
          <tr>
            <td><?= $id ?></td>
            <td><?= htmlspecialchars($value["nombre"]) ?></td>
            <td><?= number_format($value["precio"], 2) ?></td>
            <td>
              <button class="btn btn-warning btnEditarTipoServicio"
                data-id="<?= $id ?>"
                data-nombre="<?= htmlspecialchars($value["nombre"]) ?>"
                data-precio="<?= $value["precio"] ?>"
                data-toggle="modal"
                data-target="#modalEditarTipoServicio">
                Editar
              </button>
              <button class="btn btn-danger btnEliminarTipoServicio" data-id="<?= $id ?>">
                Eliminar
              </button>
              <form id="formEliminarTipoServicio-<?= $id ?>" method="POST" style="display:none;">
                <input type="hidden" name="eliminarTipoServicio" value="<?= $id ?>">
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>