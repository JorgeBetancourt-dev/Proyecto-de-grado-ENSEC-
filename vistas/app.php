<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Prototipo</title>
  <?php include "vistas/referencias/referencias.php"; ?>
</head>
<body>
    <?php 
    if (isset($_SESSION['iniciarSesion']) && $_SESSION['iniciarSesion'] == "ok") {
    ?>
    <div class="container-scroller">
        <?php include "modulos/componentes/navbar.php"; ?>
        <div class="container-fluid page-body-wrapper">
            <?php include "modulos/componentes/sidebar.php"; ?>
            <div class="main-panel">
              <div class="content-wrapper">
                <?php
                $rutasBase = ["inicio", "password", "salir"];

                $mapaRutas = [
                    "panel_usuario"  => "panel_usuario.php",
                    "panel_acceso"   => "panel_acceso.php",
                    "panel_paciente" => "panel_paciente.php",
                    "panel_tipo_servicio" => "panel_tipo_servicio.php",
                ];

                if (isset($_GET["ruta"])) {
                    $rutaSolicitada = $_GET["ruta"];
                    $id_rol = $_SESSION["id_rol"] ?? null;

                    $permisosRol = $id_rol
                        ? ControladorRoles::ctrMostrarPermisosPorRol($id_rol)
                        : [];

                    $rutasPermitidas = array_map(
                        fn($p) => strtolower($p["modulo"]),
                        array_filter($permisosRol, fn($p) => $p["activo"] == 1)
                    );

                    $rutasPermitidas = array_merge($rutasBase, $rutasPermitidas);

                    

                    if (in_array($rutaSolicitada, $rutasPermitidas)) {
                        $archivo = $mapaRutas[$rutaSolicitada] ?? $rutaSolicitada . ".php";
                        include "modulos/panel/" . $archivo;
                    } else {
                        include "modulos/acceso_usuario/404.php";
                    }
                } else {
                    include "modulos/panel/inicio.php";
                }
                ?>
              </div>
            </div>
        </div>
    </div>
    <?php 
    } else {
        include "modulos/acceso_usuario/login.php";
    }
    ?>
</body>
</html>