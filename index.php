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

$app = new ControladorApp();
$app->crtApp();
?>