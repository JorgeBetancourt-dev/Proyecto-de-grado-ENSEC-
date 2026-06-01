<div class="modal fade" id="modalAgregarMedicamento" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
    <form method="POST" class="pt-3">
      <div class="modal-header">
        <h5 class="pt-3">Registrar medicamento</h5>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <div style="position:relative;">
            <span class="position-icon"><i class="ti-heart"></i></span>
            <input type="text" class="form-control input-lg" name="nuevoNombreMedicamento" placeholder="Ingresar nombre" style="padding-left:35px;" required>
          </div>
        </div>
        <div class="form-group">
          <textarea class="form-control" name="nuevoDescripcionMedicamento" rows="3" placeholder="Ingresar descripción (opcional)"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" data-dismiss="modal" class="btn btn-default">Cerrar</button>
        <button type="submit" class="btn btn-primary">Registrar</button>
      </div>
    </form>
    </div>
  </div>
</div>