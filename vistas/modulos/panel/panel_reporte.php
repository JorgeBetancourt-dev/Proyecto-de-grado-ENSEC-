<?php
if (session_status() == PHP_SESSION_NONE) session_start();

// Médicos activos para el filtro de los modales (id_usuario, nombre, apellido)
$medicos = ControladorReportes::ctrMedicosActivos();

/*
 * Catálogo de reportes de Consultas y Citas.
 * id     → sufijo de ids HTML (modalRep{id}, inicio_{id}, fin_{id}, med_{id}, exa_{id})
 * flag   → parámetro GET que index.php recibirá para generar el PDF
 * medico → si el modal muestra el filtro de médico
 * examen → si el modal muestra el filtro de tipo de examen
 */
$reportesConsultas = [
    [
        'id' => 'ConsultasPorMedico',
        'titulo' => 'Consultas por médico',
        'desc' => 'Listado de las consultas realizadas en el rango de fechas. Si eliges "Todos los médicos", el reporte se agrupa por médico.',
        'flag' => 'generarReporteConsultasPorMedico',
        'medico' => true, 'examen' => false,
    ],
    [
        'id' => 'ConsultasConRecetas',
        'titulo' => 'Consultas por médico con recetas',
        'desc' => 'Consultas del rango de fechas que tienen un tratamiento indicado, con los medicamentos recetados.',
        'flag' => 'generarReporteConsultasConRecetas',
        'medico' => true, 'examen' => false,
    ],
    [
        'id' => 'ConsultasConExamenes',
        'titulo' => 'Consultas por médico con solicitudes de exámenes',
        'desc' => 'Consultas del rango de fechas en las que el médico solicitó exámenes, con el detalle de lo solicitado.',
        'flag' => 'generarReporteConsultasConExamenes',
        'medico' => true, 'examen' => true,
    ],
];

$reportesCitas = [
    [
        'id' => 'CitasEntreFechas',
        'titulo' => 'Citas entre fechas',
        'desc' => 'Todas las citas agendadas en el rango de fechas, sin importar el médico ni el estado.',
        'flag' => 'generarReporteCitasEntreFechas',
        'medico' => false, 'examen' => false,
    ],
];

// Configuración que lee el JS para armar la URL de cada reporte
$configJs = [];
foreach (array_merge($reportesConsultas, $reportesCitas) as $r) {
    $configJs[$r['id']] = ['flag' => $r['flag'], 'medico' => $r['medico'], 'examen' => $r['examen']];
}
?>

<style>
.reportes-header h4{margin-bottom:20px;}
.reporte-card{background:#fff;border:1px solid #e3e6f0;border-radius:10px;padding:20px;height:100%;}
.reporte-card-header{display:flex;align-items:center;margin-bottom:14px;}
.reporte-icono{width:38px;height:38px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:1.1em;margin-right:12px;}
.reporte-icono.consultas{background:#e6ecff;color:#4e5cf0;}
.reporte-icono.citas{background:#e6f4ff;color:#1e90cf;}
.reporte-icono.pagos{background:#e3f9ee;color:#1fa86a;}
.reporte-card-header h5{margin:0;font-weight:600;}
.reporte-subrayado{height:2px;width:100%;margin-bottom:16px;}
.reporte-subrayado.consultas{background:#4e5cf0;}
.reporte-subrayado.citas{background:#1e90cf;}
.reporte-subrayado.pagos{background:#1fa86a;}
.reporte-item{display:flex;align-items:flex-start;padding:10px 8px;border-radius:6px;cursor:pointer;transition:background .15s;}
.reporte-item:hover{background:#f5f6fa;}
.reporte-item i{margin-right:10px;margin-top:3px;color:#888;}
.reporte-item span{color:#333;line-height:1.3;}
.reporte-item span strong{color:#4e5cf0;font-weight:600;}
</style>

<div class="reportes-header">
    <h4><i class="fa fa-bar-chart"></i> Panel de Reportes</h4>
</div>

<div class="row">
    <!-- ── Consultas ────────────────────────────────────────────────────── -->
    <div class="col-md-4 mb-3">
        <div class="reporte-card">
            <div class="reporte-card-header">
                <div class="reporte-icono consultas"><i class="fa fa-stethoscope"></i></div>
                <h5>Consultas</h5>
            </div>
            <div class="reporte-subrayado consultas"></div>

            <?php foreach ($reportesConsultas as $r): ?>
            <div class="reporte-item" data-toggle="modal" data-target="#modalRep<?= $r['id'] ?>">
                <i class="fa fa-file-pdf-o"></i>
                <span><?= htmlspecialchars($r['titulo']) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ── Citas ────────────────────────────────────────────────────────── -->
    <div class="col-md-4 mb-3">
        <div class="reporte-card">
            <div class="reporte-card-header">
                <div class="reporte-icono citas"><i class="fa fa-calendar"></i></div>
                <h5>Citas</h5>
            </div>
            <div class="reporte-subrayado citas"></div>

            <?php foreach ($reportesCitas as $r): ?>
            <div class="reporte-item" data-toggle="modal" data-target="#modalRep<?= $r['id'] ?>">
                <i class="fa fa-file-pdf-o"></i>
                <span><?= htmlspecialchars($r['titulo']) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ── Pagos ────────────────────────────────────────────────────────── -->
    <div class="col-md-4 mb-3">
        <div class="reporte-card">
            <div class="reporte-card-header">
                <div class="reporte-icono pagos"><i class="fa fa-money"></i></div>
                <h5>Pagos</h5>
            </div>
            <div class="reporte-subrayado pagos"></div>

            <div class="reporte-item" data-toggle="modal" data-target="#modalReportePagosDia">
                <i class="fa fa-file-text-o"></i>
                <span><strong>Generar Reporte</strong> de pagos del día</span>
            </div>
            <div class="reporte-item" data-toggle="modal" data-target="#modalReportePagosRango">
                <i class="fa fa-calendar"></i>
                <span><strong>Generar Reporte</strong> de pagos entre fechas</span>
            </div>
            <div class="reporte-item" data-toggle="modal" data-target="#modalReportePagosPorMetodo">
                <i class="fa fa-filter"></i>
                <span><strong>Generar Reporte</strong> de pagos por fecha y método</span>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════════════
     MODALES: Consultas y Citas
     ══════════════════════════════════════════════════════════════════════ -->
<?php foreach (array_merge($reportesConsultas, $reportesCitas) as $r): ?>
<div class="modal fade" id="modalRep<?= $r['id'] ?>" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="mb-0"><i class="fa fa-file-pdf-o"></i> <?= htmlspecialchars($r['titulo']) ?></h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <p class="text-muted"><?= htmlspecialchars($r['desc']) ?></p>
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label>Desde</label>
              <input type="date" class="form-control" id="inicio_<?= $r['id'] ?>">
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label>Hasta</label>
              <input type="date" class="form-control" id="fin_<?= $r['id'] ?>" value="<?= date('Y-m-d') ?>">
            </div>
          </div>
        </div>
        <?php if ($r['medico']): ?>
        <div class="form-group">
          <label>Médico</label>
          <select class="form-control" id="med_<?= $r['id'] ?>">
            <option value="0">Todos los médicos</option>
            <?php foreach ($medicos as $m): ?>
            <option value="<?= (int)$m['id_usuario'] ?>"><?= htmlspecialchars($m['nombre'] . ' ' . $m['apellido']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <?php endif; ?>
        <?php if ($r['examen']): ?>
        <div class="form-group">
          <label>Tipo de examen</label>
          <select class="form-control" id="exa_<?= $r['id'] ?>">
            <option value="todos">Todos</option>
            <option value="laboratorio">Laboratorio</option>
            <option value="ecografia">Ecografía</option>
          </select>
        </div>
        <?php endif; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" onclick="generarReporteFiltrado('<?= $r['id'] ?>')">
          <i class="fa fa-file-pdf-o"></i> Generar Reporte
        </button>
      </div>
    </div>
  </div>
</div>
<?php endforeach; ?>

<!-- ══════════════════════════════════════════════════════════════════════
     MODALES: Pagos
     ══════════════════════════════════════════════════════════════════════ -->

<!-- Modal: Reporte de Pagos por Fecha + Método -->
<div class="modal fade" id="modalReportePagosPorMetodo" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="mb-0"><i class="fa fa-filter"></i> Reporte de Pagos por Método</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <p class="text-muted">Filtra los pagos por un rango de fechas y un método de pago específico.</p>
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label>Desde</label>
              <input type="date" class="form-control" id="repMetodoFechaInicio" max="<?= date('Y-m-d') ?>">
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label>Hasta</label>
              <input type="date" class="form-control" id="repMetodoFechaFin" max="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>">
            </div>
          </div>
        </div>
        <div class="form-group">
          <label>Método de pago</label>
          <select class="form-control" id="repMetodoTipo">
            <option value="efectivo">Efectivo</option>
            <option value="tarjeta">Tarjeta</option>
            <option value="qr">QR</option>
            <option value="efectivo y tarjeta">Efectivo y Tarjeta</option>
            <option value="efectivo y qr">Efectivo y QR</option>
            <option value="tarjeta y qr">Tarjeta y QR</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" onclick="generarReportePagosPorMetodo()">
          <i class="fa fa-file-pdf-o"></i> Generar Reporte
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Reporte de Pagos entre Fechas -->
<div class="modal fade" id="modalReportePagosRango" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="mb-0"><i class="fa fa-money"></i> Reporte de Pagos entre Fechas</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <p class="text-muted">Genera el detalle y los totales de los pagos registrados en el rango de fechas seleccionado.</p>
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label>Desde</label>
              <input type="date" class="form-control" id="repPagosRangoInicio" max="<?= date('Y-m-d') ?>">
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label>Hasta</label>
              <input type="date" class="form-control" id="repPagosRangoFin" max="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>">
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" onclick="generarReportePagosRango()">
          <i class="fa fa-file-pdf-o"></i> Generar Reporte
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Reporte de Pagos del Día -->
<div class="modal fade" id="modalReportePagosDia" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="mb-0"><i class="fa fa-money"></i> Reporte de Pagos del Día</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <p class="text-muted">Genera el balance de caja del día seleccionado: detalle de cada pago y totales por método de pago.</p>
        <div class="form-group">
          <label>Fecha</label>
          <input type="date" class="form-control" id="repPagosFecha" value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" onclick="generarReportePagosDia()">
          <i class="fa fa-file-pdf-o"></i> Generar Reporte
        </button>
      </div>
    </div>
  </div>
</div>

<script>
// ── Consultas y Citas ───────────────────────────────────────────────────────
var REPORTES_CONFIG = <?= json_encode($configJs) ?>;

function generarReporteFiltrado(id) {
    var cfg    = REPORTES_CONFIG[id];
    var inicio = document.getElementById("inicio_" + id).value;
    var fin    = document.getElementById("fin_" + id).value;

    if (!inicio || !fin) {
        Swal.fire({ icon: "warning", title: "Selecciona ambas fechas" });
        return;
    }
    if (inicio > fin) {
        Swal.fire({ icon: "warning", title: "La fecha 'Desde' no puede ser posterior a 'Hasta'" });
        return;
    }

    var url = "index.php?" + cfg.flag + "=1&fecha_inicio=" + inicio + "&fecha_fin=" + fin;
    if (cfg.medico) url += "&id_medico=" + document.getElementById("med_" + id).value;
    if (cfg.examen) url += "&tipo_examen=" + document.getElementById("exa_" + id).value;

    window.open(url, "_blank");
}

// ── Pagos ───────────────────────────────────────────────────────────────────
function generarReportePagosDia() {
    var fecha = document.getElementById("repPagosFecha").value;
    if (!fecha) {
        Swal.fire({ icon: "warning", title: "Selecciona una fecha" });
        return;
    }
    window.open("index.php?generarReportePagosDia=1&fecha=" + fecha, "_blank");
}

function generarReportePagosRango() {
    var inicio = document.getElementById("repPagosRangoInicio").value;
    var fin    = document.getElementById("repPagosRangoFin").value;

    if (!inicio || !fin) {
        Swal.fire({ icon: "warning", title: "Selecciona ambas fechas" });
        return;
    }
    if (inicio > fin) {
        Swal.fire({ icon: "warning", title: "La fecha 'Desde' no puede ser posterior a 'Hasta'" });
        return;
    }
    window.open("index.php?generarReportePagosRango=1&fecha_inicio=" + inicio + "&fecha_fin=" + fin, "_blank");
}

function generarReportePagosPorMetodo() {
    var inicio = document.getElementById("repMetodoFechaInicio").value;
    var fin    = document.getElementById("repMetodoFechaFin").value;
    var metodo = document.getElementById("repMetodoTipo").value;

    if (!inicio || !fin) {
        Swal.fire({ icon: "warning", title: "Selecciona ambas fechas" });
        return;
    }
    if (inicio > fin) {
        Swal.fire({ icon: "warning", title: "La fecha 'Desde' no puede ser posterior a 'Hasta'" });
        return;
    }
    window.open("index.php?generarReportePagosPorMetodo=1&fecha_inicio=" + inicio + "&fecha_fin=" + fin + "&metodo_pago=" + metodo, "_blank");
}
</script>