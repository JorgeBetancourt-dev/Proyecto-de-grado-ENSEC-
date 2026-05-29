<?php
class ControladorTipoServicio {

    static public function ctrMostrarTipoServicio($item, $valor) {
        $tabla = "tipo_servicio";
        $respuesta = ModeloTipoServicio::mdlMostrarTipoServicio($tabla, $item, $valor);
        return $respuesta;
    }

    static public function crtCrearTipoServicio() {
        if (isset($_POST["nuevoNombreTipoServicio"], $_POST["nuevoPrecioTipoServicio"])) {
            if (preg_match('/^[a-zA-ZñÑáéíóúÁÉÍÓÚ\s]+$/', $_POST["nuevoNombreTipoServicio"]) &&
                preg_match('/^\d+(\.\d{1,2})?$/', $_POST["nuevoPrecioTipoServicio"])) {

                $tabla = "tipo_servicio";
                $datos = array(
                    "nombre" => $_POST["nuevoNombreTipoServicio"],
                    "precio" => $_POST["nuevoPrecioTipoServicio"]
                );

                $respuesta = ModeloTipoServicio::mdlCrearTipoServicio($tabla, $datos);

                if (session_status() == PHP_SESSION_NONE) session_start();
                $_SESSION["crear_tipo_servicio"] = ($respuesta == "ok") ? "ok" : "error";

                header("Location: " . $_SERVER["HTTP_REFERER"]);
                exit;
            }
        }
    }

    static public function ctrEditarTipoServicio() {
        if (isset($_POST["editarIdTipoServicio"], $_POST["editarNombreTipoServicio"],
                  $_POST["editarPrecioTipoServicio"])) {
            if (preg_match('/^[a-zA-ZñÑáéíóúÁÉÍÓÚ\s]+$/', $_POST["editarNombreTipoServicio"]) &&
                preg_match('/^\d+(\.\d{1,2})?$/', $_POST["editarPrecioTipoServicio"])          &&
                is_numeric($_POST["editarIdTipoServicio"])) {

                $tabla = "tipo_servicio";
                $datos = array(
                    "id"     => $_POST["editarIdTipoServicio"],
                    "nombre" => $_POST["editarNombreTipoServicio"],
                    "precio" => $_POST["editarPrecioTipoServicio"]
                );

                $respuesta = ModeloTipoServicio::mdlEditarTipoServicio($tabla, $datos);

                if (session_status() == PHP_SESSION_NONE) session_start();
                $_SESSION["editar_tipo_servicio"] = ($respuesta == "ok") ? "ok" : "error";

                header("Location: " . $_SERVER["HTTP_REFERER"]);
                exit;
            }
        }
    }

    static public function ctrEliminarTipoServicio() {
        if (isset($_POST["eliminarTipoServicio"])) {
            if (is_numeric($_POST["eliminarTipoServicio"])) {
                $tabla     = "tipo_servicio";
                $id        = $_POST["eliminarTipoServicio"];
                $respuesta = ModeloTipoServicio::mdlEliminarTipoServicio($tabla, $id);

                if (session_status() == PHP_SESSION_NONE) session_start();
                $_SESSION["eliminar_tipo_servicio"] = ($respuesta == "ok") ? "ok" : "error";

                header("Location: " . $_SERVER["HTTP_REFERER"]);
                exit;
            }
        }
    }
}
?>