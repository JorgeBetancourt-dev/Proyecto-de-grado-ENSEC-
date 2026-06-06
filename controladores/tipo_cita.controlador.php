<?php
class ControladorTipoCita {

    static public function ctrMostrarTiposCita($item, $valor) {
        $respuesta = ModeloTipoCita::mdlMostrarTiposCita($item, $valor);
        return $respuesta;
    }

    static public function crtCrearTipoCita() {
        if (isset($_POST["nuevoNombreTipoCita"])) {
            if (session_status() == PHP_SESSION_NONE) session_start();

            $nombre = trim($_POST["nuevoNombreTipoCita"]);
            $tiempo = $_POST["nuevoTiempoCita"];

            if (empty($nombre) || !is_numeric($tiempo) || (int)$tiempo <= 0) {
                $_SESSION["crear_tipo_cita"] = "error";
                header("Location: " . $_SERVER["HTTP_REFERER"]);
                exit;
            }

            $datos = array(
                "nombre" => $nombre,
                "tiempo" => (int) $tiempo
            );

            $respuesta = ModeloTipoCita::mdlCrearTipoCita($datos);

            $_SESSION["crear_tipo_cita"] = ($respuesta == "ok") ? "ok" : "error";
            header("Location: " . $_SERVER["HTTP_REFERER"]);
            exit;
        }
    }

    static public function ctrEditarTipoCita() {
        if (isset($_POST["editarIdTipoCita"])) {
            if (session_status() == PHP_SESSION_NONE) session_start();

            $id     = $_POST["editarIdTipoCita"];
            $nombre = trim($_POST["editarNombreTipoCita"]);
            $tiempo = $_POST["editarTiempoCita"];

            if (!is_numeric($id) || empty($nombre) || !is_numeric($tiempo) || (int)$tiempo <= 0) {
                $_SESSION["editar_tipo_cita"] = "error";
                header("Location: " . $_SERVER["HTTP_REFERER"]);
                exit;
            }

            $datos = array(
                "id"     => (int) $id,
                "nombre" => $nombre,
                "tiempo" => (int) $tiempo
            );

            $respuesta = ModeloTipoCita::mdlEditarTipoCita($datos);

            $_SESSION["editar_tipo_cita"] = ($respuesta == "ok") ? "ok" : "error";
            header("Location: " . $_SERVER["HTTP_REFERER"]);
            exit;
        }
    }

    static public function ctrEliminarTipoCita() {
        if (isset($_POST["eliminarTipoCita"])) {
            if (session_status() == PHP_SESSION_NONE) session_start();

            if (!is_numeric($_POST["eliminarTipoCita"])) {
                $_SESSION["eliminar_tipo_cita"] = "error";
                header("Location: " . $_SERVER["HTTP_REFERER"]);
                exit;
            }

            $id        = $_POST["eliminarTipoCita"];
            $respuesta = ModeloTipoCita::mdlEliminarTipoCita($id);

            $_SESSION["eliminar_tipo_cita"] = ($respuesta == "ok") ? "ok" : "error";
            header("Location: " . $_SERVER["HTTP_REFERER"]);
            exit;
        }
    }
}
?>