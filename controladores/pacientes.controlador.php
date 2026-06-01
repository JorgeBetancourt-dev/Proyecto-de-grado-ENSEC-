<?php
class ControladorPacientes {

    static public function ctrMostrarPacientes($item, $valor) {
        $tabla = "pacientes";
        $respuesta = ModeloPacientes::mdlMostrarPacientes($tabla, $item, $valor);
        return $respuesta;
    }

static public function crtCrearPacientes() {
    if (isset($_POST["nuevoNombre"], $_POST["nuevoApellidos"], $_POST["nuevoCI"],
              $_POST["nuevoGrupoSanguineo"], $_POST["nuevoTelefono"],
              $_POST["nuevaFechaNacimiento"], $_POST["nuevaDireccion"])) {

            if (preg_match('/^[a-zA-ZñÑáéíóúÁÉÍÓÚ\s]+$/', $_POST["nuevoNombre"])       &&
                preg_match('/^[a-zA-ZñÑáéíóúÁÉÍÓÚ\s]+$/', $_POST["nuevoApellidos"])    &&
                preg_match('/^[a-zA-Z0-9]+$/', $_POST["nuevoCI"])                      &&
                preg_match('/^[A-Za-z0-9+\-]+$/', $_POST["nuevoGrupoSanguineo"])       &&
                preg_match('/^[0-9]+$/', $_POST["nuevoTelefono"])                      &&
                !empty( $_POST["nuevaFechaNacimiento"]) &&
                        $_POST["nuevaFechaNacimiento"] >= "1930-01-01" &&
                        $_POST["nuevaFechaNacimiento"] <= date("Y-m-d") &&
                preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ\s#.,ºª]+$/', $_POST["nuevaDireccion"])) {

                $tabla = "pacientes";
                $datos = array(
                    "nombre"           => $_POST["nuevoNombre"],
                    "apellidos"        => $_POST["nuevoApellidos"],
                    "ci"               => $_POST["nuevoCI"],
                    "grupo_sanguineo"  => $_POST["nuevoGrupoSanguineo"],
                    "telefono"         => $_POST["nuevoTelefono"],
                    "fecha_nacimiento" => $_POST["nuevaFechaNacimiento"],
                    "direccion"        => $_POST["nuevaDireccion"]
                );

                $respuesta = ModeloPacientes::mdlCrearPaciente($tabla, $datos);

                if (session_status() == PHP_SESSION_NONE) session_start();
                $_SESSION["crear_paciente"] = ($respuesta == "ok") ? "ok" : "error";

                header("Location: " . $_SERVER["HTTP_REFERER"]);
                exit;
            }
        }
    }

    static public function ctrEditarPacientes() {
        if (isset($_POST["editarIdPaciente"], $_POST["editarNombre"], $_POST["editarApellidos"],
                  $_POST["editarCI"], $_POST["editarGrupoSanguineo"], $_POST["editarTelefono"],
                  $_POST["editarFechaNacimiento"], $_POST["editarDireccion"])) {

            if (preg_match('/^[a-zA-ZñÑáéíóúÁÉÍÓÚ\s]+$/', $_POST["editarNombre"])     &&
                preg_match('/^[a-zA-ZñÑáéíóúÁÉÍÓÚ\s]+$/', $_POST["editarApellidos"]) &&
                preg_match('/^[a-zA-Z0-9]+$/', $_POST["editarCI"])                    &&
                preg_match('/^[A-Za-z0-9+\-]+$/', $_POST["editarGrupoSanguineo"]) &&
                preg_match('/^[0-9]+$/', $_POST["editarTelefono"])                    &&
                !empty( $_POST["editarFechaNacimiento"]) &&
                        $_POST["editarFechaNacimiento"] >= "1930-01-01" &&
                        $_POST["editarFechaNacimiento"] <= date("Y-m-d") &&
                preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ\s#.,ºª]+$/', $_POST["editarDireccion"])) {

                $tabla = "pacientes";
                $datos = array(
                    "id"               => $_POST["editarIdPaciente"],
                    "nombre"           => $_POST["editarNombre"],
                    "apellidos"        => $_POST["editarApellidos"],
                    "ci"               => $_POST["editarCI"],
                    "grupo_sanguineo"  => $_POST["editarGrupoSanguineo"],
                    "telefono"         => $_POST["editarTelefono"],
                    "fecha_nacimiento" => $_POST["editarFechaNacimiento"],
                    "direccion"        => $_POST["editarDireccion"]
                );

                $respuesta = ModeloPacientes::mdlEditarPaciente($tabla, $datos);

                if (session_status() == PHP_SESSION_NONE) session_start();
                $_SESSION["editar_paciente"] = ($respuesta == "ok") ? "ok" : "error";

                header("Location: " . $_SERVER["HTTP_REFERER"]);
                exit;
            }
        }
    }

    static public function ctrEliminarPacientes() {
        if (isset($_POST["eliminarPaciente"])) {
            if (is_numeric($_POST["eliminarPaciente"])) {
                $tabla     = "pacientes";
                $id        = $_POST["eliminarPaciente"];
                $respuesta = ModeloPacientes::mdlEliminarPaciente($tabla, $id);

                if (session_status() == PHP_SESSION_NONE) session_start();
                $_SESSION["eliminar_paciente"] = ($respuesta == "ok") ? "ok" : "error";

                header("Location: " . $_SERVER["HTTP_REFERER"]);
                exit;
            }
        }
    }
}
?>