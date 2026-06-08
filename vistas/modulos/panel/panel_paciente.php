<?php
if (session_status() == PHP_SESSION_NONE) session_start();
include "vistas/modulos/modales/pacientes/agregar_paciente.php";
include "vistas/modulos/modales/pacientes/editar_paciente.php";
include "vistas/assets/eventos/pacientes.eventos.php";
?>

<div>
  <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalAgregarPaciente">
    Agregar nuevo paciente
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
            <th>Apellidos</th>
            <th>C.I.</th>
            <th>Grupo <br> Sanguíneo</th>
            <th>Teléfono</th>
            <th>Fecha <br> Nacimiento</th>
            <th>Dirección</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php
            $pacientes = ControladorPacientes::ctrMostrarPacientes(null, null);
            foreach ($pacientes as $value):
              $id = $value["id_paciente"];
          ?>
          <tr>
            <td><?= $id ?></td>
            <td><?= htmlspecialchars($value["nombre"]) ?></td>
            <td><?= htmlspecialchars($value["apellidos"]) ?></td>
            <td><?= htmlspecialchars($value["ci"]) ?></td>
            <td><?= htmlspecialchars($value["grupo_sanguineo"]) ?></td>
            <td><?= htmlspecialchars($value["telefono"]) ?></td>
            <td><?= htmlspecialchars($value["fecha_nacimiento"]) ?></td>
            <td><?= wrapText(htmlspecialchars($value["direccion"])) ?></td>
            <td>
              <button class="btn btn-warning btnEditarPaciente"
                data-id="<?= $id ?>"
                data-nombre="<?= htmlspecialchars($value["nombre"]) ?>"
                data-apellidos="<?= htmlspecialchars($value["apellidos"]) ?>"
                data-ci="<?= htmlspecialchars($value["ci"]) ?>"
                data-grupo-sanguineo="<?= htmlspecialchars($value["grupo_sanguineo"]) ?>"
                data-telefono="<?= htmlspecialchars($value["telefono"]) ?>"
                data-fecha-nacimiento="<?= htmlspecialchars($value["fecha_nacimiento"]) ?>"
                data-direccion="<?= htmlspecialchars($value["direccion"]) ?>"
                data-toggle="modal"
                data-target="#modalEditarPaciente">
                Editar
              </button>
              <button class="btn btn-danger btnEliminarPaciente" data-id="<?= $id ?>">
                Eliminar
              </button>
              <form id="formEliminarPaciente-<?= $id ?>" method="POST" style="display:none;">
                <input type="hidden" name="eliminarPaciente" value="<?= $id ?>">
              </form>
            </td>
          </tr>
          <?php endforeach;
           
          function wrapText($text, $maxChars = 30) {
                      $words = explode(' ', $text);
                      $currentLine = '';
                      $result = '';

                      foreach ($words as $word) {
                          $test = $currentLine ? $currentLine . ' ' . $word : $word;

                          if (strlen($test) > $maxChars) {
                              $result .= $currentLine . '<br>';
                              $currentLine = $word;
                          } else {
                              $currentLine = $test;
                          }
                      }

                      if ($currentLine) $result .= $currentLine;
                      return $result;
                  }
                ?>    
          
        </tbody>
      </table>
    </div>
  </div>
</div>
