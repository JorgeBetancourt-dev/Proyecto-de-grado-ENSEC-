<?php
if (session_status() == PHP_SESSION_NONE) session_start();
include "vistas/modulos/modales/citas/agregar_cita.php";
include "vistas/modulos/modales/citas/detalle_cita.php";
include "vistas/assets/eventos/citas.eventos.php";
?>

<!-- CSS de FullCalendar -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css">

<style>
  /* Neutralizar colores del tema sobre FullCalendar */
  #calendarioCitas { background: #fff; }
  #calendarioCitas .fc-scrollgrid,
  #calendarioCitas .fc-scrollgrid td,
  #calendarioCitas .fc-scrollgrid th { border-color: #dee2e6; }
  #calendarioCitas .fc-col-header-cell { background: #f8f9fa; color: #333; }
  #calendarioCitas .fc-timegrid-slot   { background: #fff; }
  #calendarioCitas .fc-day-today       { background: #fff8e1 !important; }
  #calendarioCitas .fc-timegrid-now-indicator-line { border-color: #dc3545; }
  #calendarioCitas .fc-toolbar-title   { color: #333; }
  #calendarioCitas .fc-button          { background-color: #6c757d; border-color: #6c757d; color: #fff; }
  #calendarioCitas .fc-button:hover    { background-color: #5a6268; border-color: #545b62; }
  #calendarioCitas .fc-button-active,
  #calendarioCitas .fc-button-primary:not(:disabled).fc-button-active { background-color: #343a40; border-color: #343a40; }
</style>

<div class="row mb-3">
  <div class="col-12">
    <button type="button" class="btn btn-primary" id="btnAbrirModalCita">
      <i class="fa fa-calendar-plus-o"></i> Agregar nueva cita
    </button>
  </div>
</div>

<!-- Leyenda de colores -->
<div class="row mb-3">
  <div class="col-12">
    <span style="margin-right:15px;">
      <span style="display:inline-block; width:14px; height:14px; background:#3788d8; border-radius:3px; margin-right:4px;"></span> Pendiente
    </span>
    <span style="margin-right:15px;">
      <span style="display:inline-block; width:14px; height:14px; background:#28a745; border-radius:3px; margin-right:4px;"></span> Atendida
    </span>
    <span>
      <span style="display:inline-block; width:14px; height:14px; background:#dc3545; border-radius:3px; margin-right:4px;"></span> Cancelada
    </span>
  </div>
</div>

<!-- Calendario -->
<div class="box box-body">
  <div id="calendarioCitas"></div>
</div>

<!-- JS de FullCalendar -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/locales/es.global.min.js"></script>