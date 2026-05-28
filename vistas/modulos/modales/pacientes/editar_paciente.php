<div class="modal fade" id="modalEditarPaciente" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
    <form method="POST" class="pt-3">
      <div class="modal-header">       
        <h5 class="pt-3">Editar paciente</h5>
      </div>
      <div class="modal-body">
        <input type="hidden" name="editarIdPaciente" id="editarIdPaciente">
        <div class="form-group">              
          <div class="input-group">              
            <span class="input-group-addon"><i class="fa fa-user"></i></span>
            <input type="text" class="form-control input-lg" name="editarNombre" id="editarNombre" placeholder="Ingresar nombre" required>
          </div>
        </div>
        <div class="form-group">              
          <div class="input-group">            
            <span class="input-group-addon"><i class="fa fa-id-card"></i></span>
            <input type="text" class="form-control input-lg" name="editarCI" id="editarCI" placeholder="Ingresar C.I." required>
          </div>          
        </div>
        <div class="form-group">              
          <div class="input-group">            
            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
            <input type="date" class="form-control input-lg" name="editarFechaNacimiento" id="editarFechaNacimiento" required>
          </div>
        </div>
        <div class="form-group">              
          <div class="input-group">            
            <span class="input-group-addon"><i class="fa fa-tint"></i></span>
            <input type="text" class="form-control input-lg" name="editarGrupoSanguineo" id="editarGrupoSanguineo" placeholder="Ingresar grupo sanguineo" required>
          </div>
        </div>
        <div class="form-group">              
          <div class="input-group">            
            <span class="input-group-addon"><i class="fa fa-phone"></i></span>
            <input type="text" class="form-control input-lg" name="editarTelefono" id="editarTelefono" placeholder="Ingresar telefono" required>
          </div>    
        </div>  
        <div class="form-group">              
          <div class="input-group">            
            <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
            <input type="text" class="form-control input-lg" name="editarDireccion" id="editarDireccion" placeholder="Ingresar dirección" required>
          </div>
        </div>  
      </div>
      <div class="modal-footer">
          <button type="button" data-dismiss="modal" class="btn btn-primary">Cerrar</button>
          <button type="submit" class="btn btn-success">Guardar cambios</button>
      </div>
    </form>
    </div>
  </div>
</div>