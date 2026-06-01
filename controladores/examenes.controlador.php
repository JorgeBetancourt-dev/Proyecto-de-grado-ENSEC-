<?php
class ControladorExamenes {

    static public function ctrMostrarExamenes($item, $valor) {
        $tabla = "examenes";
        $respuesta = ModeloExamenes::mdlMostrarExamenes($tabla, $item, $valor);
        return $respuesta;
    }

    static public function crtCrearExamen() {
        if (isset($_POST["nuevoNombreExamen"])) {
            if (preg_match('/^[a-zA-ZñÑáéíóúÁÉÍÓÚ\s]+$/', $_POST["nuevoNombreExamen"])) {

                $tabla = "examenes";
                $datos = array(
                    "nombre" => trim($_POST["nuevoNombreExamen"])
                );

                $respuesta = ModeloExamenes::mdlCrearExamen($tabla, $datos);

                if (session_status() == PHP_SESSION_NONE) session_start();
                $_SESSION["crear_examen"] = ($respuesta == "ok") ? "ok" : "error";

                header("Location: " . $_SERVER["HTTP_REFERER"]);
                exit;
            }
        }
    }

    static public function ctrEditarExamen() {
        if (isset($_POST["editarIdExamen"], $_POST["editarNombreExamen"])) {
            if (preg_match('/^[a-zA-ZñÑáéíóúÁÉÍÓÚ\s]+$/', $_POST["editarNombreExamen"]) &&
                is_numeric($_POST["editarIdExamen"])) {

                $tabla = "examenes";
                $datos = array(
                    "id"     => $_POST["editarIdExamen"],
                    "nombre" => trim($_POST["editarNombreExamen"])
                );

                $respuesta = ModeloExamenes::mdlEditarExamen($tabla, $datos);

                if (session_status() == PHP_SESSION_NONE) session_start();
                $_SESSION["editar_examen"] = ($respuesta == "ok") ? "ok" : "error";

                header("Location: " . $_SERVER["HTTP_REFERER"]);
                exit;
            }
        }
    }

    static public function ctrEliminarExamen() {
        if (isset($_POST["eliminarExamen"])) {
            if (is_numeric($_POST["eliminarExamen"])) {
                $tabla     = "examenes";
                $id        = $_POST["eliminarExamen"];
                $respuesta = ModeloExamenes::mdlEliminarExamen($tabla, $id);

                if (session_status() == PHP_SESSION_NONE) session_start();
                $_SESSION["eliminar_examen"] = ($respuesta == "ok") ? "ok" : "error";

                header("Location: " . $_SERVER["HTTP_REFERER"]);
                exit;
            }
        }
    }
}
?>