<?php
if (session_status() == PHP_SESSION_NONE) session_start();
include "vistas/modulos/modales/medicamentos/agregar_medicamento.php";
include "vistas/modulos/modales/medicamentos/editar_medicamento.php";
include "vistas/assets/eventos/medicamentos.eventos.php";
?>

<div>
  <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalAgregarMedicamento">
    Agregar nuevo medicamento
  </button>
</div>

<div class="row">
  <div class="col-10">
    <div class="box-body pt-3">
      <table class="table table-bordered table-striped tablas" width="100%">
        <thead>
          <tr>
            <th>#</th>
            <th>Nombre</th>
            <th>Precio</th>
            <th>Descripción</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php
            $medicamentos = ControladorMedicamentos::ctrMostrarMedicamentos(null, null);
            foreach ($medicamentos as $value):
              $id = $value["id_medicamento"];
          ?>
          <tr>
            <td><?= $id ?></td>
            <td><?= htmlspecialchars($value["nombre"]) ?></td>
            <td>$<?= number_format($value["precio"], 2) ?></td>
            <td><?= htmlspecialchars($value["descripcion"] ?? "") ?></td>
            <td>
              <button class="btn btn-warning btnEditarMedicamento"
                data-id="<?= $id ?>"
                data-nombre="<?= htmlspecialchars($value["nombre"]) ?>"
                data-precio="<?= $value["precio"] ?>"
                data-descripcion="<?= htmlspecialchars($value["descripcion"] ?? "") ?>"
                data-toggle="modal"
                data-target="#modalEditarMedicamento">
                Editar
              </button>
              <button class="btn btn-danger btnEliminarMedicamento" data-id="<?= $id ?>">
                Eliminar
              </button>
              <form id="formEliminarMedicamento-<?= $id ?>" method="POST" style="display:none;">
                <input type="hidden" name="eliminarMedicamento" value="<?= $id ?>">
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>