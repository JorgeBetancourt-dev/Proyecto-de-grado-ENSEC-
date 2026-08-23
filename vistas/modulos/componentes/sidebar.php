<?php
$id_rol = $_SESSION["id_rol"] ?? null;

$permisosRol = $id_rol
    ? ControladorRoles::ctrMostrarPermisosPorRol($id_rol)
    : [];

$permisosActivos = array_filter($permisosRol, fn($p) => $p["activo"] == 1);

$configMenu = [
    "usuario" => [
        "icono" => "fa fa-users",
        "items" => [
            "panel_usuario" => "Usuarios",
            "panel_acceso"  => "Accesos",
            "panel_horario" => "Horarios",
            "panel_tipo_cita" => "Tipos de cita",
        ],
    ],
    "cita" => [
        "icono" => "fa fa-calendar",
        "items" => [
            "panel_cita" => "Citas",
        ],
    ],
    "consulta" => [
        "icono" => "fa fa-stethoscope",
        "items" => [
            "panel_consulta" => "Consultas",
            "panel_atencion" => "Atención",
        ],
    ],
    "pago" => [
        "icono" => "fa fa-money",
        "items" => [
            "panel_pago" => "Pagos",
        ],
    ],
    "servicio_medico" => [
        "icono" => "fa fa-medkit",
        "items" => [
            "panel_servicio"    => "Servicios",
            "panel_examen"      => "Exámenes",
            "panel_medicamento" => "Medicamentos",
        ],
    ],
    "contacto" => [
        "icono" => "fa fa-address-book",
        "items" => [
            "panel_paciente" => "Pacientes",
            "panel_cliente"  => "Clientes",
        ],
    ],
];

$modulosActivos = array_column($permisosActivos, 'modulo');
?>

<nav class="sidebar sidebar-offcanvas" id="sidebar">
  <ul class="nav">

    <li class="nav-item">
      <a class="nav-link" href="/Marie_stopes_pruebas/inicio">
        <i class="fa fa-home menu-icon"></i>
        <span class="menu-title">Inicio</span>
      </a>
    </li>

    <?php foreach ($configMenu as $grupo => $datosGrupo): ?>
      <?php
        $itemsActivos = array_intersect_key(
            $datosGrupo["items"],
            array_flip($modulosActivos)
        );
        if (empty($itemsActivos)) continue;
      ?>

      <?php if (count($itemsActivos) === 1): ?>
        <?php
          $modulo   = array_key_first($itemsActivos);
          $etiqueta = $itemsActivos[$modulo];
        ?>
        <li class="nav-item">
          <a class="nav-link" href="/Marie_stopes_pruebas/<?= $modulo ?>">
            <i class="<?= $datosGrupo['icono'] ?> menu-icon"></i>
            <span class="menu-title"><?= $etiqueta ?></span>
          </a>
        </li>

      <?php else: ?>
        <li class="nav-item">
          <a class="nav-link" data-toggle="collapse" href="#submenu-<?= $grupo ?>"
             aria-expanded="false" aria-controls="submenu-<?= $grupo ?>">
            <i class="<?= $datosGrupo['icono'] ?> menu-icon"></i>
            <span class="menu-title"><?= ucfirst(str_replace('_', ' ', $grupo)) ?></span>
            <i class="menu-arrow"></i>
          </a>
          <div class="collapse" id="submenu-<?= $grupo ?>">
            <ul class="nav flex-column sub-menu">
              <?php foreach ($itemsActivos as $modulo => $etiqueta): ?>
                <li class="nav-item">
                  <a class="nav-link" href="/Marie_stopes_pruebas/<?= $modulo ?>">
                    <span class="menu-title"><?= $etiqueta ?></span>
                  </a>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
        </li>
      <?php endif; ?>

    <?php endforeach; ?>

  </ul>
</nav>