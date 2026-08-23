<?php
if (session_status() == PHP_SESSION_NONE) session_start();
include "vistas/modulos/modales/examenes/agregar_examen.php";
include "vistas/modulos/modales/examenes/editar_examen.php";
include "vistas/assets/eventos/examenes.eventos.php";
?>

<div>
  <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalAgregarExamen">
    Agregar nuevo examen
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
            $examenes = ControladorExamenes::ctrMostrarExamenes(null, null);
            foreach ($examenes as $value):
              $id = $value["id_examen"];
          ?>
          <tr>
            <td><?= $id ?></td>
            <td><?= htmlspecialchars($value["nombre"]) ?></td>
            <td>$<?= number_format($value["precio"], 2) ?></td>
            <td>
              <button class="btn btn-warning btnEditarExamen"
                data-id="<?= $id ?>"
                data-nombre="<?= htmlspecialchars($value["nombre"]) ?>"
                data-precio="<?= $value["precio"] ?>"
                data-toggle="modal"
                data-target="#modalEditarExamen">
                Editar
              </button>
              <button class="btn btn-danger btnEliminarExamen" data-id="<?= $id ?>">
                Eliminar
              </button>
              <form id="formEliminarExamen-<?= $id ?>" method="POST" style="display:none;">
                <input type="hidden" name="eliminarExamen" value="<?= $id ?>">
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>