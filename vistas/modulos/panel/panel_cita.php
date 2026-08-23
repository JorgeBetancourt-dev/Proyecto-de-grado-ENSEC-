<?php
if (session_status() == PHP_SESSION_NONE) session_start();
include "vistas/modulos/modales/citas/agregar_cita.php";
include "vistas/modulos/modales/citas/detalle_cita.php";
include "vistas/modulos/modales/citas/reprogramar_cita.php";
include "vistas/assets/eventos/citas.eventos.php";
?>

<?php if (isset($_SESSION["cobro_finalizado"])): ?>
<script>
document.addEventListener("DOMContentLoaded", function () {
    Swal.fire({ icon: "success", title: "Cobro registrado correctamente", confirmButtonText: "Cerrar" });
});
</script>
<?php unset($_SESSION["cobro_finalizado"]); endif; ?>

<!-- Paso 1: selección de médico -->
<div class="row mb-3" id="panelMedicos">
  <div class="col-12">
    <h5><i class="fa fa-user-md"></i> Seleccione un médico</h5>
    <div class="row" id="listaMedicos">
      <!-- bloques de médico, generados por JS -->
    </div>
  </div>
</div>

<!-- Paso 2: calendario del médico elegido -->
<div class="row mb-3" id="panelCalendario" style="display:none;">
  <div class="col-12">
    <button type="button" class="btn btn-link pl-0" id="btnCambiarMedico">
      <i class="fa fa-arrow-left"></i> Cambiar médico
    </button>
    <span id="medicoSeleccionadoLabel" style="font-weight:600;"></span>
  </div>
</div>

<div class="row mb-3" id="panelEstados" style="display:none;">
  <div class="col-12">
    <span style="margin-right:15px;">
      <span class="calendario-estados pendiente"></span> Pendiente
    </span>
    <span style="margin-right:15px;">
      <span class="calendario-estados atendida"></span> Atendida
    </span>
    <small class="text-muted"><i class="fa fa-mouse-pointer"></i> Toca un horario libre en el calendario para registrar una cita</small>
  </div>
</div>

<!-- Calendario -->
<div class="box box-body" id="boxCalendario" style="display:none;">
  <div id="calendarioCitas"></div>
</div>