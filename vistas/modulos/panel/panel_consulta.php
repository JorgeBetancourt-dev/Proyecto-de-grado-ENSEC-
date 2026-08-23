<?php
if (session_status() == PHP_SESSION_NONE) session_start();

$id_consulta = $_GET["id_consulta"] ?? null;
if (!is_numeric($id_consulta)) { echo '<p class="text-danger">Consulta no válida.</p>'; return; }

$consulta = ModeloConsultas::mdlObtenerConsulta((int) $id_consulta);
if (!$consulta) { echo '<p class="text-danger">Consulta no encontrada.</p>'; return; }

$cita   = ModeloCitas::mdlObtenerCita((int) $consulta["id_cita"]);
$triaje = ControladorConsultas::ctrObtenerTriaje((int) $id_consulta);

$examenesConsulta   = ControladorConsultas::ctrMostrarExamenesConsulta((int) $id_consulta);
$catalogoExamenes   = ControladorConsultas::ctrMostrarCatalogoExamenes();

$tratamientosConsulta = ControladorConsultas::ctrMostrarTratamientosConsulta((int) $id_consulta);
$catalogoMedicamentos = ControladorConsultas::ctrMostrarCatalogoMedicamentos();

$mensajes = [
    "consulta_error"       => ["error",   "Revisa los datos, algo no se pudo guardar"],
    "examen_agregado"      => ["success", "Examen agregado"],
    "examen_quitado"       => ["success", "Examen quitado"],
    "tratamiento_agregado" => ["success", "Tratamiento agregado"],
    "tratamiento_quitado"  => ["success", "Tratamiento quitado"],
    "medicamento_agregado" => ["success", "Medicamento agregado"],
    "medicamento_quitado"  => ["success", "Medicamento quitado"],
];
?>

<?php if (isset($_SESSION["accion_consulta"]) && isset($mensajes[$_SESSION["accion_consulta"]])):
    [$icon, $title] = $mensajes[$_SESSION["accion_consulta"]];
?>
<script>
document.addEventListener("DOMContentLoaded", function () {
    Swal.fire({ icon: "<?= $icon ?>", title: "<?= $title ?>", confirmButtonText: "Cerrar" });
});
</script>
<?php endif; unset($_SESSION["accion_consulta"]); ?>

<h4 class="mb-3"><i class="fa fa-notes-medical"></i> Consulta</h4>

<div class="box box-body mb-3">
    <div class="row">
        <div class="col-md-6">
            <p style="margin:3px 0;"><strong>Paciente:</strong> <?= htmlspecialchars($cita["pac_nombre"] . " " . $cita["pac_apellidos"]) ?></p>
            <p style="margin:3px 0;"><strong>Carnet:</strong> <?= htmlspecialchars($cita["pac_ci"]) ?></p>
        </div>
        <div class="col-md-6">
            <?php if ($triaje): ?>
            <p style="margin:3px 0;"><strong>Triaje:</strong>
                T/A <?= htmlspecialchars($triaje["tension_arterial"] ?? "-") ?> ·
                FC <?= htmlspecialchars($triaje["frecuencia_cardiaca"] ?? "-") ?> ·
                FR <?= htmlspecialchars($triaje["frecuencia_respiratoria"] ?? "-") ?> ·
                Temp <?= htmlspecialchars($triaje["temperatura"] ?? "-") ?>°C ·
                Sat <?= htmlspecialchars($triaje["saturacion"] ?? "-") ?>% ·
                Peso <?= htmlspecialchars($triaje["peso"] ?? "-") ?>kg
            </p>
            <?php else: ?>
            <p class="text-muted">Sin triaje registrado.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Motivo / observaciones + finalizar -->
<form method="POST" class="box box-body mb-3">
    <input type="hidden" name="guardarConsultaIdConsulta" value="<?= (int) $id_consulta ?>">
    <input type="hidden" name="guardarConsultaIdCita" value="<?= (int) $cita["id_cita"] ?>">
    <div class="form-group">
        <label>Motivo de consulta <span class="text-danger">*</span></label>
        <textarea class="form-control" name="guardarConsultaMotivo" rows="2" required><?= htmlspecialchars($consulta["motivo"]) ?></textarea>
    </div>
    <div class="form-group">
        <label>Observaciones</label>
        <textarea class="form-control" name="guardarConsultaObservaciones" rows="3"><?= htmlspecialchars($consulta["observaciones"] ?? "") ?></textarea>
    </div>
    <div class="text-right">
        <button type="submit" class="btn btn-success" onclick="return confirm('Esto marca la cita como atendida. ¿Confirmas que terminaste la consulta?');">
            <i class="fa fa-check"></i> Finalizar consulta
        </button>
    </div>
</form>

<!-- Exámenes -->
<div class="box box-body mb-3">
    <h5><i class="fa fa-flask"></i> Exámenes solicitados</h5>

    <?php if (empty($examenesConsulta)): ?>
        <p class="text-muted">Todavía no se solicitó ningún examen.</p>
    <?php else: ?>
    <table class="table table-sm">
        <thead><tr><th>Examen</th><th>Tipo</th><th>Costo</th><th>Estado</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($examenesConsulta as $ex): ?>
            <tr>
                <td><?= htmlspecialchars($ex["nombre"]) ?></td>
                <td><?= htmlspecialchars(ucfirst($ex["tipo"])) ?></td>
                <td>Bs. <?= number_format($ex["costo"], 2) ?></td>
                <td><span class="badge badge-secondary"><?= htmlspecialchars(ucfirst($ex["estado"])) ?></span></td>
                <td class="text-right">
                    <form method="POST" class="d-inline" onsubmit="return confirm('¿Quitar este examen?');">
                        <input type="hidden" name="quitarExamenIdDetalle" value="<?= (int) $ex["id_detalle_examen"] ?>">
                        <input type="hidden" name="quitarExamenIdConsulta" value="<?= (int) $id_consulta ?>">
                        <button type="submit" class="btn btn-link text-danger p-0"><i class="fa fa-trash"></i></button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <form method="POST" class="row align-items-end">
        <input type="hidden" name="agregarExamenIdConsulta" value="<?= (int) $id_consulta ?>">
        <div class="col-md-8">
            <label>Agregar examen</label>
            <select class="form-control" name="agregarExamenIdExamen" required>
                <option value="">Seleccione un examen</option>
                <?php foreach ($catalogoExamenes as $ex): ?>
                    <option value="<?= $ex["id_examen"] ?>">
                        <?= htmlspecialchars($ex["nombre"]) ?> (<?= ucfirst($ex["tipo"]) ?> — Bs. <?= number_format($ex["costo"], 2) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-plus"></i> Agregar</button>
        </div>
    </form>
</div>

<!-- Tratamientos -->
<div class="box box-body mb-3">
    <h5><i class="fa fa-pills"></i> Tratamientos</h5>

    <?php if (empty($tratamientosConsulta)): ?>
        <p class="text-muted">Todavía no se registró ningún tratamiento.</p>
    <?php else: foreach ($tratamientosConsulta as $tr):
        $medicamentosTr = ControladorConsultas::ctrMostrarMedicamentosTratamiento((int) $tr["id_tratamiento"]);
    ?>
    <div class="border rounded p-3 mb-3">
        <div class="d-flex justify-content-between">
            <div>
                <p style="margin:3px 0;"><strong>Descripción:</strong> <?= nl2br(htmlspecialchars($tr["descripcion"])) ?></p>
                <?php if (!empty($tr["duracion"])): ?><p style="margin:3px 0;"><strong>Duración:</strong> <?= htmlspecialchars($tr["duracion"]) ?></p><?php endif; ?>
                <?php if (!empty($tr["indicaciones"])): ?><p style="margin:3px 0;"><strong>Indicaciones:</strong> <?= nl2br(htmlspecialchars($tr["indicaciones"])) ?></p><?php endif; ?>
            </div>
            <form method="POST" onsubmit="return confirm('¿Quitar este tratamiento completo?');">
                <input type="hidden" name="quitarTratamientoId" value="<?= (int) $tr["id_tratamiento"] ?>">
                <input type="hidden" name="quitarTratamientoIdConsulta" value="<?= (int) $id_consulta ?>">
                <button type="submit" class="btn btn-link text-danger p-0"><i class="fa fa-trash"></i></button>
            </form>
        </div>

        <?php if (!empty($medicamentosTr)): ?>
        <table class="table table-sm mt-2">
            <thead><tr><th>Medicamento</th><th>Dosis</th><th>Frecuencia</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($medicamentosTr as $m): ?>
                <tr>
                    <td><?= htmlspecialchars($m["nombre"]) ?></td>
                    <td><?= htmlspecialchars($m["dosis"]) ?></td>
                    <td><?= htmlspecialchars($m["frecuencia"]) ?></td>
                    <td class="text-right">
                        <form method="POST" class="d-inline" onsubmit="return confirm('¿Quitar este medicamento?');">
                            <input type="hidden" name="quitarMedicamentoIdDetalle" value="<?= (int) $m["id_detalle_tratamiento"] ?>">
                            <input type="hidden" name="quitarMedicamentoIdConsulta" value="<?= (int) $id_consulta ?>">
                            <button type="submit" class="btn btn-link text-danger p-0"><i class="fa fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>

        <form method="POST" class="row align-items-end mt-2">
            <input type="hidden" name="agregarMedicamentoIdTratamiento" value="<?= (int) $tr["id_tratamiento"] ?>">
            <input type="hidden" name="agregarMedicamentoIdConsulta" value="<?= (int) $id_consulta ?>">
            <div class="col-md-4">
                <select class="form-control" name="agregarMedicamentoIdMedicamento" required>
                    <option value="">Medicamento</option>
                    <?php foreach ($catalogoMedicamentos as $med): ?>
                        <option value="<?= $med["id_medicamento"] ?>"><?= htmlspecialchars($med["nombre"]) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <input type="text" class="form-control" name="agregarMedicamentoDosis" placeholder="Dosis (ej: 500mg)" required>
            </div>
            <div class="col-md-3">
                <input type="text" class="form-control" name="agregarMedicamentoFrecuencia" placeholder="Frecuencia (ej: c/8h)" required>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary btn-block"><i class="fa fa-plus"></i></button>
            </div>
        </form>
    </div>
    <?php endforeach; endif; ?>

    <form method="POST" class="row align-items-end">
        <input type="hidden" name="agregarTratamientoIdConsulta" value="<?= (int) $id_consulta ?>">
        <div class="col-md-5">
            <input type="text" class="form-control" name="agregarTratamientoDescripcion" placeholder="Descripción del tratamiento" required>
        </div>
        <div class="col-md-3">
            <input type="text" class="form-control" name="agregarTratamientoDuracion" placeholder="Duración (ej: 7 días)">
        </div>
        <div class="col-md-3">
            <input type="text" class="form-control" name="agregarTratamientoIndicaciones" placeholder="Indicaciones">
        </div>
        <div class="col-md-1">
            <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-plus"></i></button>
        </div>
    </form>
</div>