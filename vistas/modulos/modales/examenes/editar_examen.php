<div class="modal fade" id="modalEditarExamen" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
    <form method="POST" class="pt-3">
      <div class="modal-header">
        <h5 class="pt-3">Editar examen</h5>
      </div>
      <div class="modal-body">
        <input type="hidden" name="editarIdExamen" id="editarIdExamen">
        <div class="form-group">
              <div style="position:relative;">
                <span class="position-icon"><i class="fa fa-flask"></i></span>
            <input type="text" class="form-control input-lg" name="editarNombreExamen" id="editarNombreExamen" placeholder="Ingresar nombre del examen" style="padding-left:35px;" required>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" data-dismiss="modal" class="btn btn-default">Cerrar</button>
        <button type="submit" class="btn btn-success">Guardar cambios</button>
      </div>
    </form>
    </div>
  </div>
</div>