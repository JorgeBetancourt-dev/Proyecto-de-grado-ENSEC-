<?php
if (session_status() == PHP_SESSION_NONE) session_start();

$id_cita_get = $_GET["id_cita"] ?? null;
if (!is_numeric($id_cita_get)) { echo '<p class="text-danger">Cita no válida.</p>'; return; }
$id_cita = (int) $id_cita_get;

$cita = ModeloCitas::mdlObtenerCita($id_cita);
if (!$cita) { echo '<p class="text-danger">Cita no encontrada.</p>'; return; }

if (!ModeloCitas::mdlCitaTienePago($id_cita)) {
    echo '<div class="alert alert-warning"><i class="fa fa-lock"></i> Esta cita todavía no fue pagada. Debes registrar el pago antes de iniciar la consulta.</div>';
    echo '<a href="index.php?ruta=inicio" class="btn btn-default"><i class="fa fa-arrow-left"></i> Volver al inicio</a>';
    return;
}

$paciente = ModeloPacientes::mdlMostrarPacientes("pacientes", "id_paciente", (int) $cita["id_paciente"]);
$edad = "-";
if (!empty($paciente["fecha_nacimiento"])) {
    $edad = date_diff(date_create($paciente["fecha_nacimiento"]), date_create("today"))->y;
}

$carrito = ControladorConsultas::ctrConsultaCarritoLeer($id_cita);

$catalogoMedicamentos = ControladorConsultas::ctrMostrarCatalogoMedicamentos();

?>
<div id="consulta-wrapper" data-id-cita="<?= $id_cita ?>">

    <!-- Encabezado paciente -->
    <div class="consulta-header-card">
        <div class="d-flex align-items-center">
            <div class="consulta-avatar"><?= strtoupper(substr($paciente["nombre"], 0, 1) . substr($paciente["apellidos"], 0, 1)) ?></div>
            <div>
                <h5 class="mb-0"><?= htmlspecialchars($paciente["nombre"] . " " . $paciente["apellidos"]) ?></h5>
                <small class="text-muted"><?= $edad ?> años · Carnet <?= htmlspecialchars($paciente["ci"]) ?></small>
            </div>
        </div>
    </div>

    <div style="display:flex;gap:20px;align-items:flex-start;flex-wrap:wrap;">
        <!-- Columna izquierda -->
        <div style="flex:1 1 600px;min-width:0;">

            <!-- 1. Triaje -->
            <div class="card-box">
                <h5>Triaje & Signos Vitales</h5>
                <div class="triaje-grid mt-2">
                    <div>
                        <label><i class="fa fa-heart text-danger"></i> Presión Arterial</label>
                        <input type="text" class="form-control" id="triaje-tension_arterial" placeholder="120/80">
                    </div>
                    <div>
                        <label><i class="fa fa-heartbeat"></i> Frec. Cardiaca (bpm)</label>
                        <input type="text" class="form-control" id="triaje-frecuencia_cardiaca" placeholder="72">
                    </div>
                    <div>
                        <label><i class="fa fa-lungs"></i> Frec. Respiratoria</label>
                        <input type="text" class="form-control" id="triaje-frecuencia_respiratoria" placeholder="18">
                    </div>
                    <div>
                        <label><i class="fa fa-thermometer-half"></i> Temperatura (°C)</label>
                        <input type="text" class="form-control" id="triaje-temperatura" placeholder="36.8">
                    </div>
                    <div>
                        <label><i class="fa fa-tint"></i> Saturación (%)</label>
                        <input type="text" class="form-control" id="triaje-saturacion" placeholder="98">
                    </div>
                    <div>
                        <label><i class="fa fa-balance-scale"></i> Peso (kg)</label>
                        <input type="text" class="form-control" id="triaje-peso" placeholder="64.5">
                    </div>
                </div>
            </div>

            <!-- 2. Motivo + Observaciones -->
            <div class="card-box">
                <h5> Observaciones Médicas</h5>
                <div class="form-group mt-2">
                    <label>Motivo de consulta <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="campo-motivo" placeholder="Motivo por el cual acude el paciente">
                </div>
                <div class="form-group">
                    <label>Diagnostico</label>
                    <textarea class="form-control" id="campo-observaciones" rows="5" placeholder="Ingrese observaciones clínicas, síntomas y hallazgos aquí..."></textarea>
                </div>
            </div>
        </div>

        <!-- Columna derecha -->
        <div style="flex:0 0 320px;max-width:100%;">
            <div class="card-box">

                <h6 class="mt-3">Laboratorios <span class="badge badge-secondary" id="contador-examenes">0</span></h6>
                <div id="lista-examenes" class="mb-2"></div>
                <button type="button" class="btn btn-primary btn-block mb-3" onclick="abrirModalLaboratorios()">
                    <i class="fa fa-plus-circle"></i> Agregar Laboratorio
                </button>
            </div>
            <div class="card-box">
                <h6>Recetas <span class="badge badge-secondary" id="contador-medicamentos">0</span></h6>
                <div id="lista-medicamentos" class="mb-2"></div>
                <button type="button" class="btn btn-primary btn-block" onclick="abrirModalMedicamentos()">
                    <i class="fa fa-plus-circle"></i> Agregar receta
                </button>
            </div>
            <div class="card-box">
                <button type="button" class="btn btn-success btn-block" onclick="finalizarConsulta()">
                    <i class="fa fa-check-double"></i> Finalizar Consulta
                </button>
            </div>
        </div>
    </div>

</div><!-- /#consulta-wrapper -->

<?php
include "vistas/modulos/modales/consultas/agregar_laboratorio.php";
include "vistas/modulos/modales/consultas/agregar_receta.php";
include "vistas/assets/eventos/consultas.eventos.php"; // el <script> va AL FINAL
?>

