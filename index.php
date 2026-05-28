<?php
require_once "controladores/App/App.controlador.php";
require_once "controladores/usuarios.controlador.php";
require_once "modelos/usuarios.modelo.php";
require_once "controladores/pacientes.controlador.php";
require_once "modelos/pacientes.modelo.php";
require_once "controladores/servicios.controlador.php";
require_once "modelos/servicios.modelo.php";
require_once "controladores/permisos.controlador.php"; 
require_once "modelos/permisos.modelo.php"; 
require_once "controladores/roles.controlador.php";
require_once "modelos/roles.modelo.php";

ControladorServicios::ctrEliminarServicio();
ControladorServicios::ctrEditarServicio();
ControladorServicios::crtCrearServicios();

ControladorPacientes::ctrEliminarPacientes();
ControladorPacientes::ctrEditarPacientes();
ControladorPacientes::crtCrearPacientes();

ControladorUsuarios::ctrEliminarUsuarios();
ControladorUsuarios::ctrEditarUsuarios();
ControladorUsuarios::crtCrearUsuarios();

ControladorRoles::crtCrearRol();
ControladorRoles::ctrEditarRol();
ControladorRoles::ctrEliminarRol();

ControladorPermisos::crtCrearPermiso();
ControladorPermisos::ctrEditarPermiso();
ControladorPermisos::ctrEliminarPermiso();

$app = new ControladorApp();
$app->crtApp();
?>