<?php
class ControladorMedicamentos {

    static public function ctrMostrarMedicamentos($item, $valor) {
        $tabla = "medicamentos";
        $respuesta = ModeloMedicamentos::mdlMostrarMedicamentos($tabla, $item, $valor);
        return $respuesta;
    }

    static public function crtCrearMedicamento() {
        if (isset($_POST["nuevoNombreMedicamento"], $_POST["nuevoPrecioMedicamento"])) {
            if (preg_match('/^[a-zA-ZñÑáéíóúÁÉÍÓÚ\s]+$/', $_POST["nuevoNombreMedicamento"]) &&
                is_numeric($_POST["nuevoPrecioMedicamento"]) &&
                $_POST["nuevoPrecioMedicamento"] >= 0) {

                $tabla = "medicamentos";
                $datos = array(
                    "nombre"      => trim($_POST["nuevoNombreMedicamento"]),
                    "descripcion" => trim($_POST["nuevoDescripcionMedicamento"] ?? ""),
                    "precio"      => $_POST["nuevoPrecioMedicamento"]
                );

                $respuesta = ModeloMedicamentos::mdlCrearMedicamento($tabla, $datos);

                if (session_status() == PHP_SESSION_NONE) session_start();
                $_SESSION["crear_medicamento"] = ($respuesta == "ok") ? "ok" : "error";

                header("Location: " . $_SERVER["HTTP_REFERER"]);
                exit;
            }
        }
    }

    static public function ctrEditarMedicamento() {
        if (isset($_POST["editarIdMedicamento"], $_POST["editarNombreMedicamento"], $_POST["editarPrecioMedicamento"])) {
            if (preg_match('/^[a-zA-ZñÑáéíóúÁÉÍÓÚ\s]+$/', $_POST["editarNombreMedicamento"]) &&
                is_numeric($_POST["editarIdMedicamento"]) &&
                is_numeric($_POST["editarPrecioMedicamento"]) &&
                $_POST["editarPrecioMedicamento"] >= 0) {

                $tabla = "medicamentos";
                $datos = array(
                    "id"          => $_POST["editarIdMedicamento"],
                    "nombre"      => trim($_POST["editarNombreMedicamento"]),
                    "descripcion" => trim($_POST["editarDescripcionMedicamento"] ?? ""),
                    "precio"      => $_POST["editarPrecioMedicamento"]
                );

                $respuesta = ModeloMedicamentos::mdlEditarMedicamento($tabla, $datos);

                if (session_status() == PHP_SESSION_NONE) session_start();
                $_SESSION["editar_medicamento"] = ($respuesta == "ok") ? "ok" : "error";

                header("Location: " . $_SERVER["HTTP_REFERER"]);
                exit;
            }
        }
    }

    static public function ctrEliminarMedicamento() {
        if (isset($_POST["eliminarMedicamento"])) {
            if (is_numeric($_POST["eliminarMedicamento"])) {
                $tabla     = "medicamentos";
                $id        = $_POST["eliminarMedicamento"];
                $respuesta = ModeloMedicamentos::mdlEliminarMedicamento($tabla, $id);

                if (session_status() == PHP_SESSION_NONE) session_start();
                $_SESSION["eliminar_medicamento"] = ($respuesta == "ok") ? "ok" : "error";

                header("Location: " . $_SERVER["HTTP_REFERER"]);
                exit;
            }
        }
    }
}
?>