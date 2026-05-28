<?php
class ControladorPermisos {

    static public function ctrMostrarPermisos($item, $valor) {
        $tabla = "permisos";
        $respuesta = ModeloPermisos::mdlMostrarPermisos($tabla, $item, $valor);
        return $respuesta;
    }

    static public function crtCrearPermiso() {
        if (isset($_POST["nuevoModulo"], $_POST["nuevoNombrePermiso"])) {
            if (preg_match('/^[a-zA-Z0-9_ñÑáéíóúÁÉÍÓÚ\s]+$/', $_POST["nuevoModulo"]) &&
                preg_match('/^[a-zA-ZñÑáéíóúÁÉÍÓÚ\s]+$/', $_POST["nuevoNombrePermiso"])) {

                $tabla = "permisos";
                $datos = array(
                    "modulo" => $_POST["nuevoModulo"],
                    "nombre" => strtolower(trim($_POST["nuevoNombrePermiso"]))
                );

                $respuesta = ModeloPermisos::mdlCrearPermiso($tabla, $datos);

                if (session_status() == PHP_SESSION_NONE) session_start();
                $_SESSION["crear_permiso"] = ($respuesta == "ok") ? "ok" : "error";

                header("Location: " . $_SERVER["HTTP_REFERER"]);
                exit;
            }
        }
    }

    static public function ctrEditarPermiso() {
        if (isset($_POST["editarIdPermiso"], $_POST["editarModulo"], $_POST["editarNombrePermiso"])) {
            if (preg_match('/^[a-zA-Z0-9_ñÑáéíóúÁÉÍÓÚ\s]+$/', $_POST["editarModulo"]) &&
                preg_match('/^[a-zA-ZñÑáéíóúÁÉÍÓÚ\s]+$/', $_POST["editarNombrePermiso"]) &&
                is_numeric($_POST["editarIdPermiso"])) {

                $tabla = "permisos";
                $datos = array(
                    "id"     => $_POST["editarIdPermiso"],
                    "modulo" => $_POST["editarModulo"],
                    "nombre" => strtolower(trim($_POST["editarNombrePermiso"]))
                );

                $respuesta = ModeloPermisos::mdlEditarPermiso($tabla, $datos);

                if (session_status() == PHP_SESSION_NONE) session_start();
                $_SESSION["editar_permiso"] = ($respuesta == "ok") ? "ok" : "error";

                header("Location: " . $_SERVER["HTTP_REFERER"]);
                exit;
            }
        }
    }

    static public function ctrEliminarPermiso() {
        if (isset($_POST["eliminarPermiso"])) {
            if (is_numeric($_POST["eliminarPermiso"])) {
                $tabla     = "permisos";
                $id        = $_POST["eliminarPermiso"];
                $respuesta = ModeloPermisos::mdlEliminarPermiso($tabla, $id);

                if (session_status() == PHP_SESSION_NONE) session_start();
                $_SESSION["eliminar_permiso"] = ($respuesta == "ok") ? "ok" : "error";

                header("Location: " . $_SERVER["HTTP_REFERER"]);
                exit;
            }
        }
    }
}
?>