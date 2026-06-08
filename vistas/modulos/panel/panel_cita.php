<?php
if (session_status() == PHP_SESSION_NONE) session_start();
include "vistas/modulos/modales/citas/agregar_cita.php";
include "vistas/modulos/modales/citas/detalle_cita.php";
include "vistas/modulos/modales/citas/reprogramar_cita.php";
include "vistas/assets/eventos/citas.eventos.php";
?>

<div class="row mb-3">
  <div class="col-12">
    <button type="button" class="btn btn-primary" id="btnAbrirModalCita">
      <i class="fa fa-calendar-plus-o"></i> Agregar nueva cita
    </button>
  </div>
</div>

<!-- Estados de las citas -->
<div class="row mb-3">
  <div class="col-12">
    <span style="margin-right:15px;">
      <span class="calendario-estados pendiente"></span> Pendiente
    </span>
    <span style="margin-right:15px;">
      <span class="calendario-estados atendida"></span> Atendida
    </span>
  </div>
</div>

<!-- Calendario -->
<div class="box box-body">
  <div id="calendarioCitas"></div>
</div>