<?php
if (session_status() == PHP_SESSION_NONE) session_start();
include "vistas/modulos/modales/horarios/agregar_horario.php";
include "vistas/modulos/modales/horarios/editar_horario.php";
include "vistas/assets/eventos/horarios.eventos.php";
?>

<div class="mb-3">
  <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalAgregarHorario">
    <i class="fa fa-plus"></i> Agregar nuevo horario
  </button>
</div>

<div class="row">
  <div class="col-12">
    <div class="box-body pt-3">
      <table class="table table-bordered table-striped tablas" width="100%">
        <thead>
          <tr>
            <th>#</th>
            <th>Nombre</th>
            <th>Lun - Vie inicio</th>
            <th>Lun - Vie fin</th>
            <th>Sáb inicio</th>
            <th>Sáb fin</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php
            $horarios = ControladorHorarios::ctrMostrarHorarios(null, null);
            foreach ($horarios as $h):
              $id = $h["id_horario"];
          ?>
          <tr>
            <td><?= $id ?></td>
            <td><?= htmlspecialchars($h["nombre"]) ?></td>
            <td><?= date("h:i A", strtotime($h["hora_inicio"])) ?></td>
            <td><?= date("h:i A", strtotime($h["hora_fin"])) ?></td>
            <td><?= date("h:i A", strtotime($h["horaI_sabado"])) ?></td>
            <td><?= date("h:i A", strtotime($h["horaF_sabado"])) ?></td>
            <td>
              <button class="btn btn-warning btnEditarHorario"
                data-id="<?= $id ?>"
                data-nombre="<?= htmlspecialchars($h["nombre"]) ?>"
                data-hora-inicio="<?= $h["hora_inicio"] ?>"
                data-hora-fin="<?= $h["hora_fin"] ?>"
                data-horaI-sabado="<?= $h["horaI_sabado"] ?>"
                data-horaF-sabado="<?= $h["horaF_sabado"] ?>"
                
                data-toggle="modal"
                data-target="#modalEditarHorario">
                <i class="fa fa-pencil"></i> Editar
              </button>
              <button class="btn btn-danger btnEliminarHorario" data-id="<?= $id ?>">
                <i class="fa fa-trash"></i> Eliminar
              </button>
              <form id="formEliminarHorario-<?= $id ?>" method="POST" style="display:none;">
                <input type="hidden" name="eliminarHorario" value="<?= $id ?>">
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>