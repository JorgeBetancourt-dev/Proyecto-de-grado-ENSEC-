<?php
class ControladorHorarios {

    static public function ctrMostrarHorarios($item, $valor) {
        $tabla     = "horarios";
        $respuesta = ModeloHorarios::mdlMostrarHorarios($item, $valor);
        return $respuesta;
    }

    static public function crtCrearHorario() {
        if (isset($_POST["nuevoNombreHorario"])) {
            if (session_status() == PHP_SESSION_NONE) session_start();

            $datos = array(
                "nombre"       => trim($_POST["nuevoNombreHorario"]),
                "hora_inicio"  => $_POST["nuevoHoraInicio"],
                "hora_fin"     => $_POST["nuevoHoraFin"],
                "horaI_sabado" => $_POST["nuevoHoraISabado"],
                "horaF_sabado" => $_POST["nuevoHoraFSabado"]
            );

            // Validar que ningún campo esté vacío
            foreach ($datos as $val) {
                if ($val === "") {
                    $_SESSION["crear_horario"] = "error";
                    header("Location: " . $_SERVER["HTTP_REFERER"]);
                    exit;
                }
            }

            $respuesta = ModeloHorarios::mdlCrearHorario($datos);

            $_SESSION["crear_horario"] = ($respuesta == "ok") ? "ok" : "error";
            header("Location: " . $_SERVER["HTTP_REFERER"]);
            exit;
        }
    }

    static public function ctrEditarHorario() {
        if (isset($_POST["editarIdHorario"])) {
            if (session_status() == PHP_SESSION_NONE) session_start();

            if (!is_numeric($_POST["editarIdHorario"])) {
                $_SESSION["editar_horario"] = "error";
                header("Location: " . $_SERVER["HTTP_REFERER"]);
                exit;
            }

            $datos = array(
                "id"           => $_POST["editarIdHorario"],
                "nombre"       => trim($_POST["editarNombreHorario"]),
                "hora_inicio"  => $_POST["editarHoraInicio"],
                "hora_fin"     => $_POST["editarHoraFin"],
                "horaI_sabado" => $_POST["editarHoraISabado"],
                "horaF_sabado" => $_POST["editarHoraFSabado"]
            );

            foreach ($datos as $key => $val) {
                if ($key !== "id" && $val === "") {
                    $_SESSION["editar_horario"] = "error";
                    header("Location: " . $_SERVER["HTTP_REFERER"]);
                    exit;
                }
            }

            $respuesta = ModeloHorarios::mdlEditarHorario($datos);

            $_SESSION["editar_horario"] = ($respuesta == "ok") ? "ok" : "error";
            header("Location: " . $_SERVER["HTTP_REFERER"]);
            exit;
        }
    }

    static public function ctrEliminarHorario() {
        if (isset($_POST["eliminarHorario"])) {
            if (session_status() == PHP_SESSION_NONE) session_start();

            if (!is_numeric($_POST["eliminarHorario"])) {
                $_SESSION["eliminar_horario"] = "error";
                header("Location: " . $_SERVER["HTTP_REFERER"]);
                exit;
            }

            $id        = $_POST["eliminarHorario"];
            $respuesta = ModeloHorarios::mdlEliminarHorario($id);

            $_SESSION["eliminar_horario"] = ($respuesta == "ok") ? "ok" : "error";
            header("Location: " . $_SERVER["HTTP_REFERER"]);
            exit;
        }
    }
}
?>