<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Prototipo</title>
  
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-12 d-flex align-items-center justify-content-center" style="height: 100vh;">
                <div class="card mb-3" style="width: 80%; Height: 60%;">
                    <div class="row p-3 box">
                      <!--Imagen doctor-->
                      <div class="col-md-6 d-flex align-items-end justify-content-start">
                        <img src="vistas/assets/img/doctor_generico.png" alt="Doctor" class="doctor-img">
                      </div>
                      <div class="col-md-6 boxForm">
                          <!--Imagen Logo-->
                          <div class="row">
                            <div class="col-12 d-flex align-items-end justify-content-end">
                            <img src="vistas/assets/img/Logo_marieStopes.jpg" alt="logo" class="logo-img">
                            </div>
                          </div>
                          <!--Formulario-->
                          <div class="row">
                            <div class="col-12">
                              <form method="POST" class="pt-3">
                                  <div class="form-group">
                                    <label class="textcolor">Usuario</label>
                                    <input type="text" class="form-control form-control-lg"  placeholder="Usuario" name="ingUsuario" required>
                                  </div>
                                  <div class="form-group">
                                    <label class="textcolor">Contraseña</label>
                                    <input type="password" class="form-control form-control-lg"  placeholder="Contraseña" name="ingPassword" required>
                                  </div>
                                  <div class="mt-3">
                                    <button type="submit" class="btn btn-block btncolor btn-lg font-weight-medium auth-form-btn">Ingresar</button>
                                  <?php 
                                      $login= new ControladorUsuarios();
                                      $login->crtIngresoUsuario();
                                  ?>
                              </form>
                            </div>
                          </div>
                        
                      </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
