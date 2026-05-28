<?php
date_default_timezone_set('America/La_Paz');
$fecha = date("Y-m-d");
?>
<div class="row">
    <div class="col-8">
        <div class="card mb-3" style="width: 50rem; Height: 18rem;">
            <div class="row p-3">
                <div class="col-md-8">
                    <div class="card-body">
                        <div class="row p-3">
                            <h3>Bienvenido <?php echo $_SESSION["nombre"] . " " . $_SESSION["apellido"] ?></h3>
                            <p class="card-text pt-2">Que vamos a realizar hoy</p>
                        </div> 
                    </div>
                </div>
                <div class="col-md-4 p-1">
                    <img src="vistas/assets/img/img_dashboard1.png" class="img-fluid rounded-start" alt="">
                </div>
            </div>
        </div>
    </div>
    <div class="col-4">
        <div class="card mb-3" style="width: 24rem; Height: 18rem;">
            <div class="row p-4">
                <div class="col-12">
                    <div class="row p-3">
                        <h3>Programación de citas</h3>
                    </div>
                    <div class="row p-3">
                                
                    </div> 
                </div>
            </div>
        </div>
    </div>
</div>
