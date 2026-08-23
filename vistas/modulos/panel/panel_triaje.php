<?php
if (session_status() == PHP_SESSION_NONE) session_start();

$id_cita = $_GET["id_cita"] ?? null;

if (!is_numeric($id_cita)) {
    echo '<p class="text-danger">Cita no válida.</p>';
    return;
}

$cita = ModeloCitas::mdlObtenerCita((int) $id_cita);

if (!$cita || $cita["estado"] !== "pendiente") {
    echo '<p class="text-danger">Esta cita no está disponible para atención.</p>';
    return;
}

$consulta = ControladorConsultas::ctrIniciarAtencion((int) $id_cita);
$triaje   = ControladorConsultas::ctrObtenerTriaje($consulta["id_consulta"]);
?>

<h4 class="mb-3"><i class="fa fa-heartbeat"></i> Triaje</h4>

<div class="box box-body mb-3">
    <p style="margin:3px 0;"><strong>Paciente:</strong> <?= htmlspecialchars($cita["pac_nombre"] . " " . $cita["pac_apellidos"]) ?></p>
    <p style="margin:3px 0;"><strong>Carnet:</strong> <?= htmlspecialchars($cita["pac_ci"]) ?></p>
    <p style="margin:3px 0;"><strong>Hora de la cita:</strong> <?= substr($cita["fecha_hora"], 11, 5) ?></p>
</div>

<form method="POST" id="formTriaje">
    <input type="hidden" name="triajeIdConsulta" value="<?= (int) $consulta["id_consulta"] ?>">

    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label>Tensión arterial</label>
                <input type="text" class="form-control" name="triajeTensionArterial" placeholder="ej: 120/80"
                       value="<?= htmlspecialchars($triaje["tension_arterial"] ?? "") ?>">
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>Frecuencia cardiaca (lpm)</label>
                <input type="number" class="form-control" name="triajeFrecuenciaCardiaca"
                       value="<?= htmlspecialchars($triaje["frecuencia_cardiaca"] ?? "") ?>">
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>Frecuencia respiratoria (rpm)</label>
                <input type="number" class="form-control" name="triajeFrecuenciaRespiratoria"
                       value="<?= htmlspecialchars($triaje["frecuencia_respiratoria"] ?? "") ?>">
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label>Temperatura (°C)</label>
                <input type="number" step="0.1" class="form-control" name="triajeTemperatura"
                       value="<?= htmlspecialchars($triaje["temperatura"] ?? "") ?>">
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>Saturación (%)</label>
                <input type="number" step="0.1" class="form-control" name="triajeSaturacion"
                       value="<?= htmlspecialchars($triaje["saturacion"] ?? "") ?>">
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>Peso (kg)</label>
                <input type="number" step="0.1" class="form-control" name="triajePeso"
                       value="<?= htmlspecialchars($triaje["peso"] ?? "") ?>">
            </div>
        </div>
    </div>

    <div class="text-right">
        <a href="index.php?ruta=panel_atencion" class="btn btn-default"><i class="fa fa-times"></i> Cancelar</a>
        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Guardar triaje y continuar</button>
    </div>
</form>