<?php
date_default_timezone_set('America/La_Paz');
$fecha = date("Y-m-d");

$esMedico = isset($_SESSION["nombre_rol"]) && strtolower($_SESSION["nombre_rol"]) === "medico";
$citasHoy = $esMedico ? ControladorCitas::ctrMostrarCitasPendientesHoy((int) $_SESSION["IdUsuario"]) : [];
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

<?php if ($esMedico): ?>
<div class="row mt-1">
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-body">
                <h4 class="mb-3"><i class="fa fa-stethoscope"></i> Citas de hoy</h4>

                <?php if (empty($citasHoy)): ?>
                    <p class="text-muted">No tienes citas pendientes para hoy.</p>
                <?php else: ?>
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Hora</th>
                            <th>Paciente</th>
                            <th>Carnet</th>
                            <th>Tipo de cita</th>
                            <th>Pago</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($citasHoy as $c): ?>
                        <tr>
                            <td><?= date("H:i", strtotime($c["fecha_hora"])) ?></td>
                            <td><?= htmlspecialchars($c["nombre"] . " " . $c["apellidos"]) ?></td>
                            <td><?= htmlspecialchars($c["ci"]) ?></td>
                            <td><?= htmlspecialchars($c["tipo_cita"] ?? "Sin especificar") ?></td>
                            <td>
                                <?php if ($c["pagado"]): ?>
                                    <span class="badge badge-success">Pagado</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">Pendiente</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-right">
                                <a href="index.php?ruta=inicio&irAConsulta=<?= (int) $c["id_cita"] ?>" class="btn btn-sm btn-primary">
                                    <i class="fa fa-play"></i> Atender
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>