<div class="modal fade" id="modalReprogramarCita" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="pt-2"><i class="fa fa-calendar-o"></i> Reprogramar cita</h5>
      </div>
      <div class="modal-body">

        <!-- Info de la cita original -->
        <div style="border:1px solid #c3e6cb; border-radius:6px; padding:12px; margin-bottom:15px; background:#f0fff4;">
          <p style="color:#28a745; font-weight:600; margin-bottom:8px;">
            <i class="fa fa-info-circle"></i> Cita original
          </p>
          <div class="row">
            <div class="col-md-6">
              <p style="margin:3px 0;"><strong>Paciente:</strong> <span id="repPaciente"></span></p>
              <p style="margin:3px 0;"><strong>Fecha actual:</strong> <span id="repFechaActual"></span></p>
            </div>
            <div class="col-md-6">
              <p style="margin:3px 0;"><strong>Médico actual:</strong> <span id="repMedicoActual"></span></p>
              <p style="margin:3px 0;"><strong>Hora actual:</strong> <span id="repHoraActual"></span></p>
            </div>
          </div>
        </div>

        <input type="hidden" id="repIdCitaOriginal">

        <!-- Fecha y Tipo de cita -->
        <?php
          date_default_timezone_set('America/La_Paz');
          $hoyRep       = date('Y-m-d');
          $diaSemanaRep = date('N');
          if ($diaSemanaRep == 7) $hoyRep = date('Y-m-d', strtotime('+1 day'));
        ?>
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label>Nueva fecha: <small class="text-muted">No domingos</small></label>
              <input type="date" class="form-control" id="repFecha" min="<?= $hoyRep ?>" required>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label>Tipo de cita:</label>
              <select class="form-control" id="repIdTipoCita" required>
                <option value="">Seleccione un tipo</option>
                <?php
                  $tiposCitaRep = ControladorTipoCita::ctrMostrarTiposCita(null, null);
                  foreach ($tiposCitaRep as $tc):
                ?>
                  <option value="<?= $tc["id_tipo_cita"] ?>" data-tiempo="<?= $tc["tiempo"] ?>">
                    <?= htmlspecialchars($tc["nombre"]) ?> (<?= $tc["tiempo"] ?> min)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
        </div>

        <!-- Médico y Hora -->
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label>Médico: <small class="text-muted" id="repLabelMedico">Seleccione fecha y tipo primero</small></label>
              <select class="form-control" id="repIdMedico" disabled required>
                <option value="">Seleccione un médico</option>
              </select>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label>Nueva hora: <small class="text-muted">07:30 - 19:50 (Sáb hasta 13:30)</small></label>
              <input type="time" class="form-control" id="repHora"
                     min="07:30" max="19:50" step="600" required>
            </div>
          </div>
        </div>

      </div>
      <div class="modal-footer">
        <button type="button" data-dismiss="modal" class="btn btn-default">
          <i class="fa fa-times"></i> Cancelar
        </button>
        <button type="button" class="btn btn-warning" id="btnConfirmarReprogramar">
          <i class="fa fa-calendar-check-o"></i> Confirmar reprogramación
        </button>
      </div>
    </div>
  </div>
</div>