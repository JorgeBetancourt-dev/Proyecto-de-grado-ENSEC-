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
// Horarios
require_once "controladores/horarios.controlador.php";
require_once "modelos/horarios.modelo.php";
// Tipo de cita
require_once "controladores/tipo_cita.controlador.php";
require_once "modelos/tipo_cita.modelo.php";
// Consultas
require_once "controladores/consultas.controlador.php";
require_once "modelos/consultas.modelo.php";
// Pagos
require_once "controladores/pagos.controlador.php";
require_once "modelos/pagos.modelo.php";

ControladorUsuarios::ctrEliminarUsuarios();
ControladorUsuarios::ctrEditarUsuarios();
ControladorUsuarios::crtCrearUsuarios();
ControladorUsuarios::ctrBloquearUsuario();

ControladorRoles::crtCrearRol();
ControladorRoles::ctrEditarRol();
ControladorRoles::ctrEliminarRol();

ControladorPermisos::crtCrearPermiso();
ControladorPermisos::ctrEditarPermiso();
ControladorPermisos::ctrEliminarPermiso();

ControladorPacientes::crtCrearPacientes();
ControladorPacientes::ctrEditarPacientes();
ControladorPacientes::ctrEliminarPacientes();
ControladorPacientes::ctrBuscarPacientePorCI();       // AJAX: buscar paciente por CI exacto (panel de pagos)

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
ControladorClientes::ctrBuscarClientePorNit();        // AJAX: buscar cliente por NIT exacto (panel de pagos)

ControladorCitas::ctrMostrarMedicosActivos();          // AJAX: médicos activos para el panel
ControladorCitas::ctrReprogramarCita();                // AJAX POST: reprogramar cita
ControladorCitas::ctrCrearPacienteDesdeCita();         // AJAX POST: registrar paciente inline
ControladorCitas::ctrBuscarPacientePorCarnet();        // AJAX: buscar paciente por carnet
ControladorCitas::ctrMostrarMedicos();                 // AJAX: médicos con horario activo en una fecha
ControladorCitas::ctrMostrarCitasCalendario();         // AJAX: eventos del calendario de un médico
ControladorCitas::ctrHorasOcupadas();                  // AJAX: horas ocupadas de un médico
ControladorCitas::ctrObtenerCita();                    // AJAX: datos de una cita
ControladorCitas::ctrCancelarCita();                   // AJAX POST: cancelar cita
ControladorCitas::crtCrearCita();                      // POST: registrar nueva cita

ControladorHorarios::crtCrearHorario();
ControladorHorarios::ctrEditarHorario();
ControladorHorarios::ctrEliminarHorario();

ControladorTipoCita::crtCrearTipoCita();
ControladorTipoCita::ctrEditarTipoCita();
ControladorTipoCita::ctrEliminarTipoCita();

ControladorConsultas::ctrIrAConsulta();                  // GET: cita -> obtiene/crea consulta y redirige
ControladorConsultas::ctrConsultaCarritoObtener();
ControladorConsultas::ctrConsultaCarritoActualizarCampos();
ControladorConsultas::ctrConsultaCarritoAgregarExamenes();
ControladorConsultas::ctrConsultaCarritoQuitarExamen();
ControladorConsultas::ctrConsultaCarritoAgregarMedicamentos();
ControladorConsultas::ctrConsultaCarritoQuitarMedicamento();
ControladorConsultas::ctrBuscarExamenesConsulta();
ControladorConsultas::ctrBuscarMedicamentosConsulta();

ControladorPagos::ctrAgregarLinea();                      // POST: agregar ítem al cobro
ControladorPagos::ctrQuitarLinea();                       // POST: quitar ítem del cobro
ControladorPagos::ctrAsignarPaciente();                   // AJAX POST: asignar paciente (flujo sin cita)
ControladorPagos::ctrAsignarCliente();                    // AJAX POST: asignar cliente (flujo sin cita)
ControladorPagos::ctrRegistrarPacienteRapido();           // AJAX POST: registro rápido de paciente + asignar
ControladorPagos::ctrRegistrarClienteRapido();            // AJAX POST: registro rápido de cliente + asignar
ControladorPagos::ctrFinalizarPago();                     // POST: confirmar cobro

ControladorPagos::ctrCarritoObtener();                  // AJAX: leer estado del carrito
ControladorPagos::ctrCarritoAsignarPaciente();           // AJAX POST: fijar paciente ya existente
ControladorPagos::ctrCarritoRegistrarPacienteRapido();   // AJAX POST: crear paciente + fijarlo
ControladorPagos::ctrCarritoAsignarCliente();            // AJAX POST: fijar cliente ya existente
ControladorPagos::ctrCarritoRegistrarClienteRapido();    // AJAX POST: crear cliente + fijarlo
ControladorPagos::ctrCarritoAgregarLinea();              // AJAX POST: agregar ítem al carrito
ControladorPagos::ctrCarritoQuitarLinea();                // AJAX POST: quitar ítem del carrito
ControladorPagos::ctrCarritoCancelar();                   // AJAX POST: vaciar carrito

ControladorPagos::ctrCarritoFinalizarPago();             // AJAX POST: confirmar y grabar el cobro completo

if (isset($_GET["action"]) && $_GET["action"] == "getPermisosPorRol") {
    $permisos = ControladorRoles::ctrMostrarPermisosPorRol((int)$_GET["id_rol"]);
    echo json_encode($permisos);
    exit;
}

$app = new ControladorApp();
$app->crtApp();
?>