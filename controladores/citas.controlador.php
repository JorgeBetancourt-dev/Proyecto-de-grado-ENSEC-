<?php
class ControladorCitas {

    // Llamado directo desde panel_atencion.php (no AJAX)
    static public function ctrMostrarCitasPendientesHoy($id_medico) {
        return ModeloCitas::mdlMostrarCitasPendientesHoy($id_medico);
    }

    // Médicos activos para el panel de selección (AJAX GET)
    static public function ctrMostrarMedicosActivos() {
        if (isset($_GET["action"]) && $_GET["action"] == "getMedicosActivos") {
            $medicos = ModeloCitas::mdlMostrarMedicosActivos();
            echo json_encode($medicos);
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

    // Obtener médicos con horario activo en una fecha (AJAX GET)
    static public function ctrMostrarMedicos() {
        if (isset($_GET["action"]) && $_GET["action"] == "getMedicos") {
            $fecha = $_GET["fecha"] ?? "";
            if (empty($fecha)) { echo json_encode([]); exit; }
            $medicos = ModeloCitas::mdlMostrarMedicosPorHorario($fecha);
            echo json_encode($medicos);
            exit;
        }
    }

    // Eventos del calendario para un médico (AJAX GET) — antes vivía suelto en index.php
    static public function ctrMostrarCitasCalendario() {
        if (isset($_GET["action"]) && $_GET["action"] == "getCitas") {
            if (session_status() == PHP_SESSION_NONE) session_start();
            if (!isset($_SESSION["IdUsuario"])) {
                echo json_encode(["error" => "No autorizado"]); exit;
            }
            header("Content-Type: application/json");

            $id_medico = $_GET["id_medico"] ?? null;
            $citas     = ModeloCitas::mdlMostrarCitasCalendario($id_medico);
            $colores   = ["pendiente" => "#3788d8", "atendida" => "#28a745", "cancelada" => "#dc3545"];
            $eventos   = [];
            foreach ($citas as $c) {
                $color = $colores[$c["estado"]] ?? "#3788d8";
                $eventos[] = [
                    "id"              => $c["id_cita"],
                    "title"           => $c["pac_nombre"] . " " . $c["pac_apellidos"],
                    "start"           => str_replace(" ", "T", $c["fecha_hora"]),
                    "backgroundColor" => $color,
                    "borderColor"     => $color,
                    "extendedProps"   => [
                        "estado"        => $c["estado"],
                        "pac_ci"        => $c["pac_ci"],
                        "pac_telefono"  => $c["pac_telefono"],
                        "medico"        => $c["medico"] ?? "Sin asignar",
                        "recepcionista" => $c["recepcionista_registra"],
                        "tipo_cita"     => $c["tipo_cita"] ?? "Sin especificar",
                        "pagado"        => (bool) $c["pagado"]
                    ]
                ];
            }
            echo json_encode($eventos);
            exit;
        }
    }

    // Verifica que $hora (HH:MM) esté dentro del horario real del médico para $fecha.
    // Centraliza lo que antes estaba hardcodeado y duplicado en crtCrearCita y ctrReprogramarCita.
    private static function ctrHoraDentroDeHorarioMedico($id_medico, $fecha, $hora) {
        $horario = ModeloCitas::mdlObtenerHorarioMedico($id_medico);
        if (!$horario) return false;

        $esSabado = (date('N', strtotime($fecha)) == 6);
        $inicio   = $esSabado ? $horario["horaI_sabado"] : $horario["hora_inicio"];
        $fin      = $esSabado ? $horario["horaF_sabado"] : $horario["hora_fin"];

        if (empty($inicio) || empty($fin)) return false; // el médico no atiende ese día

        return ($hora >= substr($inicio, 0, 5) && $hora <= substr($fin, 0, 5));
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

            // Validar hora contra el horario real del médico elegido
            if (!self::ctrHoraDentroDeHorarioMedico($id_medico, $fecha, $hora)) {
                echo json_encode(["error" => "Hora fuera del horario de atención de ese médico"]); exit;
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
            date_default_timezone_set('America/La_Paz');

            $id_paciente  = $_POST["nuevaCitaIdPaciente"];
            $fecha        = $_POST["nuevaCitaFecha"];
            $hora         = $_POST["nuevaCitaHora"];
            $id_medico    = !empty($_POST["nuevaCitaIdMedico"])    ? $_POST["nuevaCitaIdMedico"]    : null;
            $id_tipo_cita = !empty($_POST["nuevaCitaIdTipoCita"])  ? $_POST["nuevaCitaIdTipoCita"]  : null;

            if (!is_numeric($id_paciente) || empty($fecha) || empty($hora) || $id_tipo_cita === null || $id_medico === null) {
                $_SESSION["crear_cita"] = "error";
                header("Location: " . $_SERVER["HTTP_REFERER"]); exit;
            }

            if (date('N', strtotime($fecha)) == 7) {
                $_SESSION["crear_cita"] = "domingo";
                header("Location: " . $_SERVER["HTTP_REFERER"]); exit;
            }

            if (!self::ctrHoraDentroDeHorarioMedico($id_medico, $fecha, $hora)) {
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

    // Llamado directo desde ControladorConsultas al finalizar una consulta
    static public function ctrMarcarAtendida($id_cita) {
        return ModeloCitas::mdlMarcarAtendida($id_cita);
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