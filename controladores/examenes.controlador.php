<?php
class ControladorExamenes {

    static public function ctrMostrarExamenes($item, $valor) {
        $tabla = "examenes";
        $respuesta = ModeloExamenes::mdlMostrarExamenes($tabla, $item, $valor);
        return $respuesta;
    }

    static public function crtCrearExamen() {
        if (isset($_POST["nuevoNombreExamen"], $_POST["nuevoTipoExamen"], $_POST["nuevoPrecioExamen"])) {
            $tiposValidos = array("laboratorio", "ecografia");

            if (preg_match('/^[a-zA-ZñÑáéíóúÁÉÍÓÚ\s]+$/', $_POST["nuevoNombreExamen"]) &&
                in_array($_POST["nuevoTipoExamen"], $tiposValidos) &&
                is_numeric($_POST["nuevoPrecioExamen"]) &&
                $_POST["nuevoPrecioExamen"] >= 0) {

                $tabla = "examenes";
                $datos = array(
                    "nombre" => trim($_POST["nuevoNombreExamen"]),
                    "tipo"   => $_POST["nuevoTipoExamen"],
                    "precio" => $_POST["nuevoPrecioExamen"]
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
        if (isset($_POST["editarIdExamen"], $_POST["editarNombreExamen"], $_POST["editarTipoExamen"], $_POST["editarPrecioExamen"])) {
            $tiposValidos = array("laboratorio", "ecografia");

            if (preg_match('/^[a-zA-ZñÑáéíóúÁÉÍÓÚ\s]+$/', $_POST["editarNombreExamen"]) &&
                in_array($_POST["editarTipoExamen"], $tiposValidos) &&
                is_numeric($_POST["editarIdExamen"]) &&
                is_numeric($_POST["editarPrecioExamen"]) &&
                $_POST["editarPrecioExamen"] >= 0) {

                $tabla = "examenes";
                $datos = array(
                    "id"     => $_POST["editarIdExamen"],
                    "nombre" => trim($_POST["editarNombreExamen"]),
                    "tipo"   => $_POST["editarTipoExamen"],
                    "precio" => $_POST["editarPrecioExamen"]
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