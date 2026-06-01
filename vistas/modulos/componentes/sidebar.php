<?php
$id_rol = $_SESSION["id_rol"] ?? null;

$permisosRol = $id_rol
    ? ControladorRoles::ctrMostrarPermisosPorRol($id_rol)
    : [];

$nombresUnicos = array_unique(
    array_map(
        fn($p) => $p["nombre"],
        array_filter($permisosRol, fn($p) => $p["activo"] == 1)
    )
);
?>

<nav class="sidebar sidebar-offcanvas" id="sidebar">
  <ul class="nav">

    <li class="nav-item">
      <a class="nav-link" href="/Marie_stopes_pruebas/inicio">
        <i class="fa fa-home menu-icon"></i>
        <span class="menu-title">Inicio</span>
      </a>
    </li>

    <?php foreach ($nombresUnicos as $nombre): ?>
      <?php

        switch ($nombre) {
            case "usuario":
                $icono    = "fa fa-users";
                $etiqueta = "Usuarios";
                $ruta     = "panel_usuario";
                break;
            case "paciente":
                $icono    = "fa fa-user-plus";
                $etiqueta = "Pacientes";
                $ruta     = "panel_paciente";
                break;
            case "tipo_servicio":
                $icono    = "fa fa-list-alt";
                $etiqueta = "Servicios";
                $ruta     = "panel_tipo_servicio";
                break;
            case "examen":
                $icono    = "fa fa-flask";
                $etiqueta = "Exámenes";
                $ruta     = "panel_examen";
                break;
            case "medicamento":
                $icono    = "fa fa-medkit";
                $etiqueta = "Medicamentos";
                $ruta     = "panel_medicamento";
                break;
            case "cliente":
                $icono    = "fa fa-building";
                $etiqueta = "Clientes";
                $ruta     = "panel_cliente";
                break;
        }
      ?>
      <li class="nav-item">
        <a class="nav-link" href="/Marie_stopes_pruebas/<?= $ruta ?>">
          <i class="<?= $icono ?> menu-icon"></i>
          <span class="menu-title"><?= $etiqueta ?></span>
        </a>
      </li>
      
    <?php endforeach; ?>

  </ul>
</nav>