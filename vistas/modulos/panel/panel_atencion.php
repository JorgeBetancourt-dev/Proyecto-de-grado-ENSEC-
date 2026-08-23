<?php
if (session_status() == PHP_SESSION_NONE) session_start();
?>

<?php if (isset($_SESSION["guardar_triaje"]) || isset($_SESSION["atencion_finalizada"])): ?>
<script>
document.addEventListener("DOMContentLoaded", function () {
    <?php if (isset($_SESSION["guardar_triaje"])): ?>
    Swal.fire({
        icon: "<?= $_SESSION['guardar_triaje'] == 'ok' ? 'success' : 'error' ?>",
        title: "<?= $_SESSION['guardar_triaje'] == 'ok' ? 'Triaje guardado correctamente' : 'Error al guardar el triaje' ?>",
        confirmButtonText: "Cerrar"
    });
    <?php endif; ?>
    <?php if (isset($_SESSION["atencion_finalizada"])): ?>
    Swal.fire({ icon: "success", title: "Consulta finalizada correctamente", confirmButtonText: "Cerrar" });
    <?php endif; ?>
});
</script>
<?php unset($_SESSION["guardar_triaje"]); unset($_SESSION["atencion_finalizada"]); endif; ?>

<h4 class="mb-3"><i class="fa fa-stethoscope"></i> Mis citas de hoy</h4>

<?php $citas = ControladorCitas::ctrMostrarCitasPendientesHoy((int) $_SESSION["IdUsuario"]); ?>

<?php if (empty($citas)): ?>
    <p class="text-muted">No tienes citas pendientes para hoy.</p>
<?php else: ?>
<div class="box box-body">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Hora</th>
                    <th>Paciente</th>
                    <th>Carnet</th>
                    <th>Tipo de cita</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($citas as $c): ?>
                <tr>
                    <td><?= substr($c["fecha_hora"], 11, 5) ?></td>
                    <td><?= htmlspecialchars($c["nombre"] . " " . $c["apellidos"]) ?></td>
                    <td><?= htmlspecialchars($c["ci"]) ?></td>
                    <td><?= htmlspecialchars($c["tipo_cita"] ?? "Sin especificar") ?></td>
                    <td class="text-right">
                        <a href="index.php?ruta=panel_triaje&id_cita=<?= (int) $c["id_cita"] ?>" class="btn btn-primary btn-sm">
                            <i class="fa fa-play"></i> Atender
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>