<?php
class ControladorCitas {

    // Obtener citas para FullCalendar (AJAX GET)
    static public function ctrMostrarCitasCalendario() {
        if (isset($_GET["action"]) && $_GET["action"] == "getCitas") {
            $citas   = ModeloCitas::mdlMostrarCitasCalendario();
            $eventos = [];
            foreach ($citas as $c) {
                $colores = [
                    "pendiente" => "#3788d8",
                    "atendida"  => "#28a745",
                    "cancelada" => "#dc3545"
                ];
                $color = $colores[$c["estado"]] ?? "#3788d8";
                $eventos[] = [
                    "id"              => $c["id_cita"],
                    "title"           => $c["pac_nombre"] . " " . $c["pac_apellidos"],
                    "start"           => $c["fecha"] . "T" . $c["hora"],
                    "backgroundColor" => $color,
                    "borderColor"     => $color,
                    "extendedProps"   => [
                        "estado"          => $c["estado"],
                        "pac_ci"          => $c["pac_ci"],
                        "pac_telefono"    => $c["pac_telefono"],
                        "medico"          => $c["medico"]          ?? "Sin asignar",
                        "tipo_cita"       => $c["tipo_cita"]       ?? "Sin especificar",
                        "recepcionista"   => $c["recepcionista_registra"],
                        "id_tipo_cita"    => $c["id_tipo_cita"],
                        "id_medico"       => $c["id_medico"]
                    ]
                ];
            }
            echo json_encode($eventos);
            exit;
        }
    }

    // Crear paciente desde inline (AJAX POST)
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
            foreach ($datos as $val) {
                if ($val === "") { echo json_encode(["error" => "Todos los campos son obligatorios"]); exit; }
            }
            $id = ModeloCitas::mdlCrearPacienteDesideCita($datos);
            if ($id) {
                echo json_encode(["ok" => true, "id_paciente" => $id,
                    "nombre" => $datos["nombre"], "apellidos" => $datos["apellidos"],
                    "ci" => $datos["ci"], "grupo_sanguineo" => $datos["grupo_sanguineo"],
                    "telefono" => $datos["telefono"], "fecha_nacimiento" => $datos["fecha_nacimiento"],
                    "direccion" => $datos["direccion"]]);
            } else {
                echo json_encode(["error" => "No se pudo registrar el paciente"]);
            }
            exit;
        }
    }

    // Buscar paciente por carnet (AJAX GET)
    static public function ctrBuscarPacientePorCarnet() {
        if (isset($_GET["action"]) && $_GET["action"] == "buscarPacienteCarnet") {
            $carnet    = trim($_GET["carnet"]);
            $resultado = ModeloCitas::mdlBuscarPacientePorCarnet($carnet);
            echo json_encode($resultado ?: ["error" => "Paciente no encontrado"]);
            exit;
        }
    }

    // Obtener médicos filtrados por horario (AJAX GET)
    static public function ctrMostrarMedicos() {
        if (isset($_GET["action"]) && $_GET["action"] == "getMedicos") {
            $fecha        = $_GET["fecha"]        ?? "";
            $id_tipo_cita = $_GET["id_tipo_cita"] ?? "";
            if (empty($fecha) || empty($id_tipo_cita)) { echo json_encode([]); exit; }
            $medicos = ModeloCitas::mdlMostrarMedicosPorHorario($fecha, $id_tipo_cita);
            echo json_encode($medicos);
            exit;
        }
    }

    // Obtener horas ocupadas de un médico (AJAX GET)
    static public function ctrHorasOcupadas() {
        if (isset($_GET["action"]) && $_GET["action"] == "getHorasOcupadas") {
            $id_medico    = $_GET["id_medico"]    ?? "";
            $fecha        = $_GET["fecha"]        ?? "";
            $excluir_cita = $_GET["excluir_cita"] ?? null; // Para excluir la cita que se está reprogramando
            if (empty($id_medico) || empty($fecha)) { echo json_encode([]); exit; }
            $ocupadas = ModeloCitas::mdlHorasOcupadasMedico($id_medico, $fecha, $excluir_cita);
            echo json_encode($ocupadas);
            exit;
        }
    }

    // Obtener datos de una cita (AJAX GET)
    static public function ctrObtenerCita() {
        if (isset($_GET["action"]) && $_GET["action"] == "getCita") {
            $id_cita = $_GET["id_cita"] ?? "";
            if (!is_numeric($id_cita)) { echo json_encode(["error" => "ID inválido"]); exit; }
            $cita = ModeloCitas::mdlObtenerCita((int)$id_cita);
            echo json_encode($cita ?: ["error" => "Cita no encontrada"]);
            exit;
        }
    }

    // Reprogramar cita (AJAX POST)
    static public function ctrReprogramarCita() {
        if (isset($_POST["action"]) && $_POST["action"] == "reprogramarCita") {
            if (session_status() == PHP_SESSION_NONE) session_start();
            header("Content-Type: application/json");
            date_default_timezone_set('America/La_Paz');

            $id_cita_original = $_POST["id_cita_original"] ?? null;
            $fecha            = $_POST["repFecha"]         ?? null;
            $hora             = $_POST["repHora"]          ?? null;
            $id_medico        = $_POST["repIdMedico"]      ?? null;
            $id_tipo_cita     = $_POST["repIdTipoCita"]    ?? null;

            if (!is_numeric($id_cita_original) || empty($fecha) || empty($hora) ||
                !is_numeric($id_medico) || !is_numeric($id_tipo_cita)) {
                echo json_encode(["error" => "Todos los campos son obligatorios"]); exit;
            }

            // Validar domingo
            if (date('N', strtotime($fecha)) == 7) {
                echo json_encode(["error" => "No se pueden programar citas los domingos"]); exit;
            }

            // Validar hora
            $esSabado = (date('N', strtotime($fecha)) == 6);
            $horaMax  = $esSabado ? "13:30" : "19:50";
            if ($hora < "07:30" || $hora > $horaMax) {
                echo json_encode(["error" => "Hora fuera del horario de atención"]); exit;
            }

            // Validar que no sea en el pasado
            $fechaHoraCita   = new DateTime($fecha . ' ' . $hora);
            $fechaHoraActual = new DateTime();
            if ($fechaHoraCita <= $fechaHoraActual) {
                echo json_encode(["error" => "No se pueden programar citas en una fecha u hora pasada"]); exit;
            }

            // Obtener datos de la cita original
            $citaOriginal = ModeloCitas::mdlObtenerCita((int)$id_cita_original);
            if (!$citaOriginal) {
                echo json_encode(["error" => "Cita original no encontrada"]); exit;
            }

            // Marcar cita original como reprogramada
            $marcar = ModeloCitas::mdlMarcarReprogramada((int)$id_cita_original);
            if ($marcar != "ok") {
                echo json_encode(["error" => "Error al marcar la cita original"]); exit;
            }

            // Crear nueva cita
            $datos = array(
                "id_paciente"      => (int) $citaOriginal["id_paciente"],
                "id_recepcionista" => (int) $_SESSION["IdUsuario"],
                "id_medico"        => (int) $id_medico,
                "id_tipo_cita"     => (int) $id_tipo_cita,
                "fecha"            => $fecha,
                "hora"             => $hora,
                "id_cita_original" => (int) $id_cita_original
            );

            $respuesta = ModeloCitas::mdlReprogramarCita($datos);
            echo json_encode($respuesta == "ok" ? ["ok" => true] : ["error" => "Error al crear la nueva cita"]);
            exit;
        }
    }

    // Registrar nueva cita (POST)
    static public function crtCrearCita() {
        if (isset($_POST["nuevaCitaIdPaciente"])) {
            if (session_status() == PHP_SESSION_NONE) session_start();

            $id_paciente  = $_POST["nuevaCitaIdPaciente"];
            $fecha        = $_POST["nuevaCitaFecha"];
            $hora         = $_POST["nuevaCitaHora"];
            $id_medico    = !empty($_POST["nuevaCitaIdMedico"])    ? $_POST["nuevaCitaIdMedico"]    : null;
            $id_tipo_cita = !empty($_POST["nuevaCitaIdTipoCita"])  ? $_POST["nuevaCitaIdTipoCita"]  : null;

            if (!is_numeric($id_paciente) || empty($fecha) || empty($hora) || $id_tipo_cita === null || $id_medico === null) {
                $_SESSION["crear_cita"] = "error";
                header("Location: " . $_SERVER["HTTP_REFERER"]); exit;
            }

            $diaSemana = date('N', strtotime($fecha));
            if ($diaSemana == 7) {
                $_SESSION["crear_cita"] = "domingo";
                header("Location: " . $_SERVER["HTTP_REFERER"]); exit;
            }

            if ($hora >= "01:00" && $hora < "07:30") {
                $_SESSION["crear_cita"] = "hora_no_permitida";
                header("Location: " . $_SERVER["HTTP_REFERER"]); exit;
            }

            $esSabado = ($diaSemana == 6);
            $horaMax  = $esSabado ? "13:30" : "19:50";
            if ($hora > $horaMax) {
                $_SESSION["crear_cita"] = "hora_no_permitida";
                header("Location: " . $_SERVER["HTTP_REFERER"]); exit;
            }

            $fechaHoraCita   = new DateTime($fecha . ' ' . $hora);
            $fechaHoraActual = new DateTime();
            if ($fechaHoraCita <= $fechaHoraActual) {
                $_SESSION["crear_cita"] = "fecha_pasada";
                header("Location: " . $_SERVER["HTTP_REFERER"]); exit;
            }

            $datos = array(
                "id_paciente"      => (int) $id_paciente,
                "id_recepcionista" => (int) $_SESSION["IdUsuario"],
                "id_medico"        => (int) $id_medico,
                "id_tipo_cita"     => (int) $id_tipo_cita,
                "fecha"            => $fecha,
                "hora"             => $hora
            );

            $respuesta = ModeloCitas::mdlCrearCita($datos);
            $_SESSION["crear_cita"] = ($respuesta == "ok") ? "ok" : "error";
            header("Location: " . $_SERVER["HTTP_REFERER"]);
            exit;
        }
    }
    // Cancelar cita (AJAX POST)
    static public function ctrCancelarCita() {
        if (isset($_POST["action"]) && $_POST["action"] == "cancelarCita") {
            header("Content-Type: application/json");
            $id_cita = $_POST["id_cita"] ?? null;
            if (!is_numeric($id_cita)) {
                echo json_encode(["error" => "ID de cita inválido"]); exit;
            }
            $respuesta = ModeloCitas::mdlCancelarCita((int)$id_cita);
            echo json_encode($respuesta === "ok"
                ? ["ok" => true]
                : ["error" => "No se pudo cancelar la cita. Es posible que ya haya sido cancelada o atendida."]);
            exit;
        }
    }
}
?>