<?php
class ControladorRoles {

    static public function ctrMostrarPermisosPorRol($id_rol) {
        $respuesta = ModeloRoles::mdlMostrarPermisosPorRol($id_rol);
        return $respuesta;
    }

    static public function ctrMostrarRoles($item, $valor) {
        $tabla = "roles";
        $respuesta = ModeloRoles::mdlMostrarRoles($tabla, $item, $valor);
        return $respuesta;
    }

    static public function crtCrearRol() {
        if (isset($_POST["nuevoNombreRol"])) {
            if (preg_match('/^[a-zA-ZñÑáéíóúÁÉÍÓÚ\s]+$/', $_POST["nuevoNombreRol"])) {

                $datos = array(
                    "nombre" => trim($_POST["nuevoNombreRol"])
                );

                $permisos = isset($_POST["permisosRol"])
                    ? array_filter($_POST["permisosRol"], 'is_numeric')
                    : [];

                $respuesta = ModeloRoles::mdlCrearRol($datos, $permisos);

                if (session_status() == PHP_SESSION_NONE) session_start();
                $_SESSION["crear_rol"] = ($respuesta == "ok") ? "ok" : "error";

                header("Location: " . $_SERVER["HTTP_REFERER"]);
                exit;
            }
        }
    }

    static public function ctrEditarRol() {
        if (isset($_POST["editarIdRol"], $_POST["editarNombreRol"])) {
            if (preg_match('/^[a-zA-ZñÑáéíóúÁÉÍÓÚ\s]+$/', $_POST["editarNombreRol"]) &&
                is_numeric($_POST["editarIdRol"])) {

                $datos = array(
                    "id"     => (int) $_POST["editarIdRol"],
                    "nombre" => trim($_POST["editarNombreRol"])
                );

                $permisos = isset($_POST["permisosEditarRol"])
                    ? array_filter($_POST["permisosEditarRol"], 'is_numeric')
                    : [];

                $respuesta = ModeloRoles::mdlEditarRol($datos, $permisos);

                if (session_status() == PHP_SESSION_NONE) session_start();
                $_SESSION["editar_rol"] = ($respuesta == "ok") ? "ok" : "error";

                header("Location: " . $_SERVER["HTTP_REFERER"]);
                exit;
            }
        }
    }

    static public function ctrEliminarRol() {
        if (isset($_POST["eliminarRol"])) {
            if (is_numeric($_POST["eliminarRol"])) {
                $id        = $_POST["eliminarRol"];
                $respuesta = ModeloRoles::mdlEliminarRol($id);

                if (session_status() == PHP_SESSION_NONE) session_start();
                $_SESSION["eliminar_rol"] = ($respuesta == "ok") ? "ok" : "error";

                header("Location: " . $_SERVER["HTTP_REFERER"]);
                exit;
            }
        }
    }
}
?>