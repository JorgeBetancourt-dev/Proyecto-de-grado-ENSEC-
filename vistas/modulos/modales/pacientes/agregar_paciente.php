<div class="modal fade" id="modalAgregarPaciente" tabindex="-1" role="dialog" >
  <div class="modal-dialog" role="document">
    <div class="modal-content">
    <form method="POST" class="pt-3">
      <div class="modal-header">       
        <h5 class="pt-3">Registrar  paciente</h5>
              
      </div>
      <div class="modal-body">
        <div class="form-group">              
          <div class="input-group">              
            <span class="input-group-addon"><i class="fa fa-user"></i></span>
            <input type="text" class="form-control input-lg" name="nuevoNombre" placeholder="Ingresar nombre" required>
          </div>
        </div>
        <div class="form-group">              
          <div class="input-group">            
            <span class="input-group-addon"><i class="fa fa-id-card"></i></span>
            <input type="text" class="form-control input-lg" name="nuevoCI" placeholder="Ingresar C.I." id="nuevoCI" required>
          </div>          
        </div>
        <div class="form-group">              
          <div class="input-group">            
            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
            <input type="date" class="form-control input-lg" name="nuevaFechaNacimiento" id="nuevaFechaNacimiento" required>
          </div>
        </div>
        <div class="form-group">              
          <div class="input-group">            
            <span class="input-group-addon"><i class="fa fa-tint"></i></span>
            <input type="text" class="form-control input-lg" name="nuevoGrupoSanguineo" placeholder="Ingresar grupo sanguineo" id="nuevoGrupoSanguineo" required>
          </div>
        </div>
        <div class="form-group">              
          <div class="input-group">            
            <span class="input-group-addon"><i class="fa fa-phone"></i></span>
            <input type="text" class="form-control input-lg" name="nuevoTelefono" placeholder="Ingresar telefono" id="nuevoTelefono" required>
          </div>    
        </div>  
        <div class="form-group">              
          <div class="input-group">            
            <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
            <input type="text" class="form-control input-lg" name="nuevaDireccion" placeholder="Ingresar dirección" id="nuevaDireccion" required>
          </div>
        </div>  
             
      </div>
      <div class="modal-footer">
          <button type="button" data-dismiss="modal" class="btn btn-primary">Cerrar</button>
          <button type="submit" class="btn btn-primary">Registrar nuevo paciente</button>
      </div>
    </form>
    </div>
  </div>
</div>