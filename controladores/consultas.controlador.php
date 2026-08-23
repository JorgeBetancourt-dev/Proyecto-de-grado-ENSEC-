<?php
class ControladorConsultas {

    // ── Consulta / triaje (llamados directo desde las vistas, no AJAX) ────────

    static public function ctrIniciarAtencion($id_cita) {
        return ModeloConsultas::mdlObtenerOcrearConsultaPorCita($id_cita);
    }

    static public function ctrObtenerConsultaPorCita($id_cita) {
        return ModeloConsultas::mdlObtenerConsultaPorCita($id_cita);
    }

    static public function ctrObtenerTriaje($id_consulta) {
        return ModeloConsultas::mdlObtenerTriajePorConsulta($id_consulta);
    }

    static public function ctrMostrarCatalogoExamenes() {
        return ModeloConsultas::mdlMostrarCatalogoExamenes();
    }

    static public function ctrMostrarExamenesConsulta($id_consulta) {
        return ModeloConsultas::mdlMostrarExamenesConsulta($id_consulta);
    }

    static public function ctrMostrarCatalogoMedicamentos() {
        return ModeloConsultas::mdlMostrarCatalogoMedicamentos();
    }

    static public function ctrMostrarTratamientosConsulta($id_consulta) {
        return ModeloConsultas::mdlMostrarTratamientosConsulta($id_consulta);
    }

    static public function ctrMostrarMedicamentosTratamiento($id_tratamiento) {
        return ModeloConsultas::mdlMostrarMedicamentosTratamiento($id_tratamiento);
    }

    // ── Guardar triaje (POST) ───────────────────────────────────────────────

    static public function ctrGuardarTriaje() {
        if (isset($_POST["triajeIdConsulta"])) {
            if (session_status() == PHP_SESSION_NONE) session_start();

            $datos = array(
                "id_consulta"             => (int) $_POST["triajeIdConsulta"],
                "tension_arterial"        => trim($_POST["triajeTensionArterial"]) !== "" ? trim($_POST["triajeTensionArterial"]) : null,
                "frecuencia_cardiaca"     => $_POST["triajeFrecuenciaCardiaca"]     !== "" ? (int) $_POST["triajeFrecuenciaCardiaca"]     : null,
                "frecuencia_respiratoria" => $_POST["triajeFrecuenciaRespiratoria"] !== "" ? (int) $_POST["triajeFrecuenciaRespiratoria"] : null,
                "temperatura"             => $_POST["triajeTemperatura"]           !== "" ? $_POST["triajeTemperatura"]                   : null,
                "saturacion"              => $_POST["triajeSaturacion"]            !== "" ? $_POST["triajeSaturacion"]                    : null,
                "peso"                    => $_POST["triajePeso"]                  !== "" ? $_POST["triajePeso"]                           : null,
            );

            ModeloConsultas::mdlGuardarTriaje($datos);
            header("Location: index.php?ruta=panel_consulta&id_consulta=" . $datos["id_consulta"]);
            exit;
        }
    }

    // ── Finalizar consulta (POST): guarda motivo/observaciones y marca la cita atendida ──

    static public function ctrGuardarConsulta() {
        if (isset($_POST["guardarConsultaIdConsulta"])) {
            if (session_status() == PHP_SESSION_NONE) session_start();

            $id_consulta = (int) $_POST["guardarConsultaIdConsulta"];
            $id_cita     = (int) $_POST["guardarConsultaIdCita"];
            $motivo      = trim($_POST["guardarConsultaMotivo"] ?? "");
            $observaciones = trim($_POST["guardarConsultaObservaciones"] ?? "");

            if ($motivo === "") {
                $_SESSION["accion_consulta"] = "consulta_error";
                header("Location: index.php?ruta=panel_consulta&id_consulta=" . $id_consulta);
                exit;
            }

            $r1 = ModeloConsultas::mdlActualizarConsulta($id_consulta, $motivo, $observaciones);
            $r2 = ControladorCitas::ctrMarcarAtendida($id_cita);

            if ($r1 == "ok" && $r2 == "ok") {
                $_SESSION["atencion_finalizada"] = "ok";
                header("Location: index.php?ruta=panel_atencion");
            } else {
                $_SESSION["accion_consulta"] = "consulta_error";
                header("Location: index.php?ruta=panel_consulta&id_consulta=" . $id_consulta);
            }
            exit;
        }
    }

    // ── Exámenes (POST) ─────────────────────────────────────────────────────

    static public function ctrAgregarExamen() {
        if (isset($_POST["agregarExamenIdConsulta"])) {
            if (session_status() == PHP_SESSION_NONE) session_start();
            $id_consulta = (int) $_POST["agregarExamenIdConsulta"];
            $id_examen   = (int) $_POST["agregarExamenIdExamen"];

            $respuesta = ModeloConsultas::mdlAgregarExamen($id_consulta, $id_examen);
            $_SESSION["accion_consulta"] = ($respuesta == "ok") ? "examen_agregado" : "consulta_error";
            header("Location: index.php?ruta=panel_consulta&id_consulta=" . $id_consulta);
            exit;
        }
    }

    static public function ctrQuitarExamen() {
        if (isset($_POST["quitarExamenIdDetalle"])) {
            if (session_status() == PHP_SESSION_NONE) session_start();
            $id_consulta = (int) $_POST["quitarExamenIdConsulta"];

            $respuesta = ModeloConsultas::mdlQuitarExamen((int) $_POST["quitarExamenIdDetalle"]);
            $_SESSION["accion_consulta"] = ($respuesta == "ok") ? "examen_quitado" : "consulta_error";
            header("Location: index.php?ruta=panel_consulta&id_consulta=" . $id_consulta);
            exit;
        }
    }

    // ── Tratamientos (POST) ─────────────────────────────────────────────────

    static public function ctrAgregarTratamiento() {
        if (isset($_POST["agregarTratamientoIdConsulta"])) {
            if (session_status() == PHP_SESSION_NONE) session_start();
            $id_consulta = (int) $_POST["agregarTratamientoIdConsulta"];

            $datos = array(
                "id_consulta"  => $id_consulta,
                "descripcion"  => trim($_POST["agregarTratamientoDescripcion"] ?? ""),
                "duracion"     => trim($_POST["agregarTratamientoDuracion"] ?? "") ?: null,
                "indicaciones" => trim($_POST["agregarTratamientoIndicaciones"] ?? "") ?: null,
            );

            if ($datos["descripcion"] === "") {
                $_SESSION["accion_consulta"] = "consulta_error";
                header("Location: index.php?ruta=panel_consulta&id_consulta=" . $id_consulta);
                exit;
            }

            $respuesta = ModeloConsultas::mdlAgregarTratamiento($datos);
            $_SESSION["accion_consulta"] = ($respuesta == "ok") ? "tratamiento_agregado" : "consulta_error";
            header("Location: index.php?ruta=panel_consulta&id_consulta=" . $id_consulta);
            exit;
        }
    }

    static public function ctrQuitarTratamiento() {
        if (isset($_POST["quitarTratamientoId"])) {
            if (session_status() == PHP_SESSION_NONE) session_start();
            $id_consulta = (int) $_POST["quitarTratamientoIdConsulta"];

            $respuesta = ModeloConsultas::mdlQuitarTratamiento((int) $_POST["quitarTratamientoId"]);
            $_SESSION["accion_consulta"] = ($respuesta == "ok") ? "tratamiento_quitado" : "consulta_error";
            header("Location: index.php?ruta=panel_consulta&id_consulta=" . $id_consulta);
            exit;
        }
    }

    static public function ctrAgregarMedicamentoTratamiento() {
        if (isset($_POST["agregarMedicamentoIdTratamiento"])) {
            if (session_status() == PHP_SESSION_NONE) session_start();
            $id_consulta = (int) $_POST["agregarMedicamentoIdConsulta"];

            $datos = array(
                "id_tratamiento" => (int) $_POST["agregarMedicamentoIdTratamiento"],
                "id_medicamento" => (int) $_POST["agregarMedicamentoIdMedicamento"],
                "dosis"          => trim($_POST["agregarMedicamentoDosis"] ?? ""),
                "frecuencia"     => trim($_POST["agregarMedicamentoFrecuencia"] ?? ""),
            );

            $respuesta = ModeloConsultas::mdlAgregarMedicamentoTratamiento($datos);
            $_SESSION["accion_consulta"] = ($respuesta == "ok") ? "medicamento_agregado" : "consulta_error";
            header("Location: index.php?ruta=panel_consulta&id_consulta=" . $id_consulta);
            exit;
        }
    }

    static public function ctrQuitarMedicamentoTratamiento() {
        if (isset($_POST["quitarMedicamentoIdDetalle"])) {
            if (session_status() == PHP_SESSION_NONE) session_start();
            $id_consulta = (int) $_POST["quitarMedicamentoIdConsulta"];

            $respuesta = ModeloConsultas::mdlQuitarMedicamentoTratamiento((int) $_POST["quitarMedicamentoIdDetalle"]);
            $_SESSION["accion_consulta"] = ($respuesta == "ok") ? "medicamento_quitado" : "consulta_error";
            header("Location: index.php?ruta=panel_consulta&id_consulta=" . $id_consulta);
            exit;
        }
    }
}