<div class="modal fade" id="modalAgregarCita" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <form method="POST" id="formAgregarCita" class="pt-3">

        <div class="modal-header">
          <h5 class="pt-3"><i class="fa fa-calendar"></i> Registro de citas</h5>
        </div>

        <div class="modal-body">

          <!-- Médico / Fecha / Hora: fijados al tocar el calendario -->
          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label>Médico:</label>
                <select class="form-control" id="nuevaCitaIdMedicoDisplay" disabled>
                  <option value="">—</option>
                </select>
                <input type="hidden" name="nuevaCitaIdMedico" id="nuevaCitaIdMedico">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label>Fecha:</label>
                <input type="date" class="form-control" id="nuevaCitaFechaDisplay" disabled>
                <input type="hidden" name="nuevaCitaFecha" id="nuevaCitaFecha">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label>Hora:</label>
                <input type="time" class="form-control" id="nuevaCitaHoraDisplay" disabled>
                <input type="hidden" name="nuevaCitaHora" id="nuevaCitaHora">
              </div>
            </div>
          </div>

          <!-- Buscar paciente por carnet -->
          <div class="form-group">
            <label>Carnet de identidad:</label>
            <div class="input-group">
              <input type="text" id="buscarCarnet" class="form-control" placeholder="Ingrese el número de carnet">
              <span class="input-group-btn">
                <button type="button" class="btn btn-primary" id="btnBuscarPaciente">
                  <i class="fa fa-search"></i> Buscar
                </button>
              </span>
              <span class="input-group-btn" style="padding-left:5px;">
                <button type="button" class="btn btn-success" id="btnNuevoPaciente">
                  <i class="fa fa-user-plus"></i> Nuevo Paciente
                </button>
              </span>
            </div>
          </div>

          <!-- Tarjeta: paciente encontrado -->
          <div id="infoPaciente" style="display:none; border:1px solid #c3e6cb; border-radius:6px; padding:15px; margin-bottom:15px; background:#f0fff4;">
            <p style="color:#28a745; font-weight:600; margin-bottom:12px;">
              <i class="fa fa-check-circle"></i> Paciente encontrado
            </p>
            <div class="row">
              <div class="col-md-1 text-center" style="padding-top:5px;">
                <i class="fa fa-user-circle-o" style="font-size:40px; color:#aaa;"></i>
              </div>
              <div class="col-md-11">
                <div class="row">
                  <div class="col-md-6">
                    <p style="margin:3px 0;"><strong>Nombre:</strong> <span id="pacNombre"></span></p>
                    <p style="margin:3px 0;"><strong>Carnet:</strong> <span id="pacCI"></span></p>
                    <p style="margin:3px 0;"><strong>Fecha de Nacimiento:</strong> <span id="pacFechaNac"></span></p>
                  </div>
                  <div class="col-md-6">
                    <p style="margin:3px 0;"><strong>Teléfono:</strong> <span id="pacTelefono"></span></p>
                    <p style="margin:3px 0;"><strong>Dirección:</strong> <span id="pacDireccion"></span></p>
                    <p style="margin:3px 0;"><strong>Grupo Sanguíneo:</strong> <span id="pacGrupoSanguineo"></span></p>
                  </div>
                </div>
              </div>
            </div>
            <input type="hidden" name="nuevaCitaIdPaciente" id="nuevaCitaIdPaciente">
          </div>

          <!-- Alerta: paciente no encontrado -->
          <div id="errorPaciente" class="alert alert-danger" style="display:none;">
            <i class="fa fa-times-circle"></i> Paciente no encontrado. Verifique el número de carnet o registre uno nuevo.
          </div>

          <!-- Formulario inline: registrar nuevo paciente -->
          <div id="formNuevoPacienteInline" style="display:none; border:1px solid #bee5eb; border-radius:6px; padding:15px; margin-bottom:15px; background:#f0f9ff;">
            <p style="color:#17a2b8; font-weight:600; margin-bottom:12px;">
              <i class="fa fa-user-plus"></i> Registrar nuevo paciente
            </p>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <div style="position:relative;">
                    <span class="position-icon"><i class="ti-user"></i></span>
                    <input type="text" class="form-control" id="npNombre" placeholder="Ingresar nombre" style="padding-left:35px;">
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <div style="position:relative;">
                    <span class="position-icon"><i class="ti-user"></i></span>
                    <input type="text" class="form-control" id="npApellidos" placeholder="Ingresar apellidos" style="padding-left:35px;">
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <div style="position:relative;">
                    <span class="position-icon"><i class="ti-id-badge"></i></span>
                    <input type="text" class="form-control" id="npCI" placeholder="Ingresar C.I." style="padding-left:35px;">
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <div style="position:relative;">
                    <span class="position-icon"><i class="ti-heart"></i></span>
                    <input type="text" class="form-control" id="npGrupoSanguineo" placeholder="Grupo sanguíneo (ej: A+)" style="padding-left:35px;">
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <div style="position:relative;">
                    <span class="position-icon"><i class="ti-mobile"></i></span>
                    <input type="text" class="form-control" id="npTelefono" placeholder="Ingresar teléfono" style="padding-left:35px;">
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <div style="position:relative;">
                    <span class="position-icon"><i class="ti-calendar"></i></span>
                    <input type="date" class="form-control" id="npFechaNacimiento" min="1930-01-01" max="<?= date('Y-m-d') ?>" style="padding-left:35px;">
                  </div>
                </div>
              </div>
            </div>
            <div class="form-group">
              <div style="position:relative;">
                <span class="position-icon"><i class="ti-location-pin"></i></span>
                <input type="text" class="form-control" id="npDireccion" placeholder="Ingresar dirección" style="padding-left:35px;">
              </div>
            </div>
            <div class="text-right">
              <button type="button" class="btn btn-default" id="btnCancelarNuevoPaciente">
                <i class="fa fa-times"></i> Cancelar
              </button>
              <button type="button" class="btn btn-success" id="btnRegistrarNuevoPaciente">
                <i class="fa fa-save"></i> Guardar paciente
              </button>
            </div>
          </div>

          <!-- Tipo de cita: se deshabilitan las opciones que no caben en el horario elegido -->
          <div class="form-group">
            <label>Tipo de cita:</label>
            <select class="form-control" name="nuevaCitaIdTipoCita" id="nuevaCitaIdTipoCita" required>
              <option value="">Seleccione un tipo</option>
              <?php
                $tiposCita = ControladorTipoCita::ctrMostrarTiposCita(null, null);
                foreach ($tiposCita as $tc):
              ?>
                <option value="<?= $tc["id_tipo_cita"] ?>" data-tiempo="<?= $tc["tiempo"] ?>">
                  <?= htmlspecialchars($tc["nombre"]) ?> (<?= $tc["tiempo"] ?> min)
                </option>
              <?php endforeach; ?>
            </select>
            <small class="text-muted">Los tipos que no caben en el horario elegido aparecen deshabilitados.</small>
          </div>

        </div>

        <div class="modal-footer">
          <button type="button" data-dismiss="modal" class="btn btn-default">
            <i class="fa fa-times"></i> Cancelar
          </button>
          <button type="submit" class="btn btn-primary" id="btnGuardarCita" disabled>
            <i class="fa fa-save"></i> Guardar Cita
          </button>
        </div>

      </form>
    </div>
  </div>
</div>