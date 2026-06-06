<?php
require_once "controladores/App/App.controlador.php";
//Usuarios
require_once "controladores/usuarios.controlador.php";
require_once "modelos/usuarios.modelo.php";
//Permisos
require_once "controladores/permisos.controlador.php"; 
require_once "modelos/permisos.modelo.php"; 
//Roles
require_once "controladores/roles.controlador.php";
require_once "modelos/roles.modelo.php";
//Pacientes
require_once "controladores/pacientes.controlador.php";
require_once "modelos/pacientes.modelo.php";
//Tipo de Servicio
require_once "controladores/tipo_servicio.controlador.php";
require_once "modelos/tipo_servicio.modelo.php";
//Examenes
require_once "controladores/examenes.controlador.php";
require_once "modelos/examenes.modelo.php";
//Medicamentos
require_once "controladores/medicamentos.controlador.php";
require_once "modelos/medicamentos.modelo.php";
//clientes
require_once "controladores/clientes.controlador.php";
require_once "modelos/clientes.modelo.php";
// Citas
require_once "controladores/citas.controlador.php";
require_once "modelos/citas.modelo.php";

ControladorUsuarios::ctrEliminarUsuarios();
ControladorUsuarios::ctrEditarUsuarios();
ControladorUsuarios::crtCrearUsuarios();

ControladorRoles::crtCrearRol();
ControladorRoles::ctrEditarRol();
ControladorRoles::ctrEliminarRol();

ControladorPermisos::crtCrearPermiso();
ControladorPermisos::ctrEditarPermiso();
ControladorPermisos::ctrEliminarPermiso();

ControladorPacientes::crtCrearPacientes();
ControladorPacientes::ctrEditarPacientes();
ControladorPacientes::ctrEliminarPacientes();

ControladorTipoServicio::crtCrearTipoServicio();
ControladorTipoServicio::ctrEditarTipoServicio();
ControladorTipoServicio::ctrEliminarTipoServicio();

ControladorExamenes::crtCrearExamen();
ControladorExamenes::ctrEditarExamen();
ControladorExamenes::ctrEliminarExamen();

ControladorMedicamentos::crtCrearMedicamento();
ControladorMedicamentos::ctrEditarMedicamento();
ControladorMedicamentos::ctrEliminarMedicamento();

ControladorClientes::crtCrearCliente();
ControladorClientes::ctrEditarCliente();
ControladorClientes::ctrEliminarCliente();

ControladorCitas::ctrCrearPacienteDesdeCita();         // AJAX POST: registrar paciente inline
ControladorCitas::ctrBuscarPacientePorCarnet();        // AJAX: buscar paciente por carnet
ControladorCitas::ctrMostrarMedicos();                 // AJAX: obtener lista de médicos
ControladorCitas::crtCrearCita();                      // POST: registrar nueva cita
 
// AJAX para FullCalendar — va junto al bloque de getPermisosPorRol
if (isset($_GET["action"]) && $_GET["action"] == "getCitas") {
    session_start();
    if (!isset($_SESSION["IdUsuario"])) {
        echo json_encode(["error" => "No autorizado"]);
        exit;
    }
    header("Content-Type: application/json");
    $citas   = ModeloCitas::mdlMostrarCitasCalendario();
    $eventos = [];
    foreach ($citas as $c) {
        $colores = ["pendiente" => "#3788d8", "atendida" => "#28a745", "cancelada" => "#dc3545"];
        $color   = $colores[$c["estado"]] ?? "#3788d8";
        $eventos[] = [
            "id"              => $c["id_cita"],
            "title"           => $c["pac_nombre"] . " " . $c["pac_apellidos"],
            "start"           => $c["fecha"] . "T" . $c["hora"],
            "backgroundColor" => $color,
            "borderColor"     => $color,
            "extendedProps"   => [
                "estado"                 => $c["estado"],
                "pac_ci"                 => $c["pac_ci"],
                "pac_telefono"           => $c["pac_telefono"],
                "medico"                 => $c["medico"] ?? "Sin asignar",
                "recepcionista_registra" => $c["recepcionista_registra"]
            ]
        ];
    }
    echo json_encode($eventos);
    exit;
}   

if (isset($_GET["action"]) && $_GET["action"] == "getPermisosPorRol") {
    $permisos = ControladorRoles::ctrMostrarPermisosPorRol((int)$_GET["id_rol"]);
    echo json_encode($permisos);
    exit;
}

$app = new ControladorApp();
$app->crtApp();
?>