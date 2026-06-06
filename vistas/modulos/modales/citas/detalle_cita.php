<div class="modal fade" id="modalDetalleCita" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header" id="modalDetalleCitaHeader">
        <h5 class="pt-2"><i class="fa fa-calendar-check-o"></i> Detalle de la cita</h5>
      </div>
      <div class="modal-body">

        <!-- Badge de estado -->
        <div class="text-center mb-3">
          <span id="detEstadoBadge" class="label" style="font-size:14px; padding:6px 16px; border-radius:20px;"></span>
        </div>

        <table class="table table-bordered" style="margin-bottom:0;">
          <tbody>
            <tr>
              <th style="width:40%;"><i class="fa fa-user"></i> Paciente</th>
              <td id="detPaciente"></td>
            </tr>
            <tr>
              <th><i class="ti-id-badge"></i> Carnet</th>
              <td id="detCI"></td>
            </tr>
            <tr>
              <th><i class="ti-mobile"></i> Teléfono</th>
              <td id="detTelefono"></td>
            </tr>
            <tr>
              <th><i class="fa fa-calendar"></i> Fecha</th>
              <td id="detFecha"></td>
            </tr>
            <tr>
              <th><i class="fa fa-clock-o"></i> Hora</th>
              <td id="detHora"></td>
            </tr>
            <tr>
              <th><i class="fa fa-tag"></i> Tipo de cita</th>
              <td id="detTipoCita"></td>
            </tr>
            <tr>
              <th><i class="fa fa-user-md"></i> Médico</th>
              <td id="detMedico"></td>
            </tr>
            <tr>
              <th><i class="fa fa-user-circle"></i> Registrado por</th>
              <td id="detRecepcionista"></td>
            </tr>
          </tbody>
        </table>

      </div>
      <div class="modal-footer">
        <button type="button" data-dismiss="modal" class="btn btn-default">
          <i class="fa fa-times"></i> Cerrar
        </button>
      </div>
    </div>
  </div>
</div>