<?php
class ControladorCitas {

    // Obtener citas para FullCalendar (AJAX GET)
    static public function ctrMostrarCitasCalendario() {
        if (isset($_GET["action"]) && $_GET["action"] == "getCitas") {
            $citas    = ModeloCitas::mdlMostrarCitasCalendario();
            $eventos  = [];

            foreach ($citas as $c) {
                // Color según estado
                $colores = [
                    "pendiente" => "#3788d8",
                    "atendida"  => "#28a745",
                    "cancelada" => "#dc3545"
                ];
                $color = $colores[$c["estado"]] ?? "#3788d8";

                $eventos[] = [
                    "id"                    => $c["id_cita"],
                    "title"                 => $c["pac_nombre"] . " " . $c["pac_apellidos"],
                    "start"                 => $c["fecha"] . "T" . $c["hora"],
                    "backgroundColor"       => $color,
                    "borderColor"           => $color,
                    // Datos extra para el modal de detalle
                    "extendedProps" => [
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
    }
    static public function ctrCrearPacienteDesdeCita() {
        if (isset($_POST["action"]) && $_POST["action"] == "registrarPacienteCita") {
            $datos = array(
                "nombre"           => trim($_POST["nombre"]),
                "apellidos"        => trim($_POST["apellidos"]),
                "ci"               => trim($_POST["ci"]),
                "grupo_sanguineo"  => trim($_POST["grupo_sanguineo"]),
                "telefono"         => trim($_POST["telefono"]),
                "fecha_nacimiento" => $_POST["fecha_nacimiento"],
                "direccion"        => trim($_POST["direccion"])
            );

            // Validar que ningún campo esté vacío
            foreach ($datos as $val) {
                if ($val === "") {
                    echo json_encode(["error" => "Todos los campos son obligatorios"]);
                    exit;
                }
            }

            $id = ModeloCitas::mdlCrearPacienteDesideCita($datos);

            if ($id) {
                echo json_encode([
                    "ok"              => true,
                    "id_paciente"     => $id,
                    "nombre"          => $datos["nombre"],
                    "apellidos"       => $datos["apellidos"],
                    "ci"              => $datos["ci"],
                    "grupo_sanguineo" => $datos["grupo_sanguineo"],
                    "telefono"        => $datos["telefono"],
                    "fecha_nacimiento"=> $datos["fecha_nacimiento"],
                    "direccion"       => $datos["direccion"]
                ]);
            } else {
                echo json_encode(["error" => "No se pudo registrar el paciente"]);
            }
            exit;
        }
    }

    // Buscar paciente por carnet (llamado vía AJAX)
    static public function ctrBuscarPacientePorCarnet() {
        if (isset($_GET["action"]) && $_GET["action"] == "buscarPacienteCarnet") {
            $carnet    = trim($_GET["carnet"]);
            $resultado = ModeloCitas::mdlBuscarPacientePorCarnet($carnet);
            echo json_encode($resultado ?: ["error" => "Paciente no encontrado"]);
            exit;
        }
    }

    // Obtener médicos filtrados por horario según fecha (AJAX)
    static public function ctrMostrarMedicos() {
        if (isset($_GET["action"]) && $_GET["action"] == "getMedicos") {
            $fecha        = $_GET["fecha"]        ?? "";
            $id_tipo_cita = $_GET["id_tipo_cita"] ?? "";

            if (empty($fecha) || empty($id_tipo_cita)) {
                echo json_encode([]);
                exit;
            }

            $medicos = ModeloCitas::mdlMostrarMedicosPorHorario($fecha, $id_tipo_cita);
            echo json_encode($medicos);
            exit;
        }
    }

    // Obtener horas ocupadas de un médico en una fecha (AJAX)
    static public function ctrHorasOcupadas() {
        if (isset($_GET["action"]) && $_GET["action"] == "getHorasOcupadas") {
            $id_medico = $_GET["id_medico"] ?? "";
            $fecha     = $_GET["fecha"]     ?? "";

            if (empty($id_medico) || empty($fecha)) {
                echo json_encode([]);
                exit;
            }

            $ocupadas = ModeloCitas::mdlHorasOcupadasMedico($id_medico, $fecha);
            echo json_encode($ocupadas);
            exit;
        }
    }

    // Registrar nueva cita
    static public function crtCrearCita() {
        if (isset($_POST["nuevaCitaIdPaciente"])) {
            if (session_status() == PHP_SESSION_NONE) session_start();

            $id_paciente = $_POST["nuevaCitaIdPaciente"];
            $fecha       = $_POST["nuevaCitaFecha"];
            $hora        = $_POST["nuevaCitaHora"];
            $id_medico    = !empty($_POST["nuevaCitaIdMedico"])    ? $_POST["nuevaCitaIdMedico"]    : null;
            $id_tipo_cita = !empty($_POST["nuevaCitaIdTipoCita"]) ? $_POST["nuevaCitaIdTipoCita"] : null;

            // Validaciones básicas
            if (!is_numeric($id_paciente) || empty($fecha) || empty($hora) || $id_tipo_cita === null) {
                $_SESSION["crear_cita"] = "error";
                header("Location: " . $_SERVER["HTTP_REFERER"]);
                exit;
            }

            // Validar que no sea domingo
            $diaSemana = date('N', strtotime($fecha)); // 7 = domingo
            if ($diaSemana == 7) {
                $_SESSION["crear_cita"] = "domingo";
                header("Location: " . $_SERVER["HTTP_REFERER"]);
                exit;
            }

            // Validar que la hora no esté entre 01:00 y 07:29
            if ($hora >= "01:00" && $hora < "07:30") {
                $_SESSION["crear_cita"] = "hora_no_permitida";
                header("Location: " . $_SERVER["HTTP_REFERER"]);
                exit;
            }

            // Validar hora máxima según día
            $esSabado = ($diaSemana == 6);
            $horaMax  = $esSabado ? "13:30" : "19:50";
            if ($hora > $horaMax) {
                $_SESSION["crear_cita"] = "hora_no_permitida";
                header("Location: " . $_SERVER["HTTP_REFERER"]);
                exit;
            }

            // Validar que la fecha y hora no sean anteriores a la actual
            $fechaHoraCita   = new DateTime($fecha . ' ' . $hora);
            $fechaHoraActual = new DateTime();
            if ($fechaHoraCita <= $fechaHoraActual) {
                $_SESSION["crear_cita"] = "fecha_pasada";
                header("Location: " . $_SERVER["HTTP_REFERER"]);
                exit;
            }

            $datos = array(
                "id_paciente"               => (int) $id_paciente,
                "id_recepcionista_registra" => (int) $_SESSION["IdUsuario"],
                "id_recepcionista_asigna"   => $id_medico !== null ? (int) $_SESSION["IdUsuario"] : null,
                "id_medico"                 => $id_medico !== null ? (int) $id_medico : null,
                "id_tipo_cita"              => $id_tipo_cita !== null ? (int) $id_tipo_cita : null,
                "fecha"                     => $fecha,
                "hora"                      => $hora
            );

            $respuesta = ModeloCitas::mdlCrearCita($datos);

            $_SESSION["crear_cita"] = ($respuesta == "ok") ? "ok" : "error";

            header("Location: " . $_SERVER["HTTP_REFERER"]);
            exit;
        }
    }
}
?>