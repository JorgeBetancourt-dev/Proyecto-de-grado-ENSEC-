<?php
class ControladorPacientes {
    static public function crtCrearPacientes() {
        if(isset($_POST["nuevoNombre"], $_POST["nuevoCI"], $_POST["nuevaFechaNacimiento"], 
                $_POST["nuevoGrupoSanguineo"], $_POST["nuevoTelefono"], $_POST["nuevaDireccion"])){
            if(preg_match('/^[a-zA-ZñÑáéíóúÁÉÍÓÚ\s]+$/', $_POST["nuevoNombre"]) &&
            preg_match('/^[a-zA-Z0-9]+$/', $_POST["nuevoCI"]) &&
            !empty($_POST["nuevaFechaNacimiento"]) &&   
            preg_match('/^[A-Z0-9+]+$/', $_POST["nuevoGrupoSanguineo"]) &&
            preg_match('/^[0-9]+$/', $_POST["nuevoTelefono"]) &&
            preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ\s#.,]+$/', $_POST["nuevaDireccion"])){

                $tabla = "pacientes";
                $datos = array(
                    "nombre" => $_POST["nuevoNombre"],
                    "ci" => $_POST["nuevoCI"],
                    "fecha_nacimiento" => $_POST["nuevaFechaNacimiento"],
                    "grupo_sanguineo" => $_POST["nuevoGrupoSanguineo"],
                    "telefono" => $_POST["nuevoTelefono"],
                    "direccion" => $_POST["nuevaDireccion"]
                );

                $respuesta = ModeloPacientes::MdlCrearPacientes($tabla, $datos);

                if (session_status() == PHP_SESSION_NONE) session_start();

                if($respuesta == "ok"){
                    $_SESSION["crear_paciente"] = "ok";
                } else {
                    $_SESSION["crear_paciente"] = "error";
                }
                header("Location: " . $_SERVER["HTTP_REFERER"]);
                exit;
            }
        } 
    }
    static public function ctrMostrarPacientes($item, $valor){
        $tabla = "pacientes";
        $respuesta = ModeloPacientes::mdlMostrarPacientes($tabla, $item, $valor);
        return $respuesta;
    }
    static public function ctrEditarPacientes() {
        if (isset($_POST["editarIdPaciente"])) {
            if (preg_match('/^[a-zA-ZñÑáéíóúÁÉÍÓÚ\s]+$/', $_POST["editarNombre"]) &&
                preg_match('/^[0-9]+\.?[0-9]*$/', $_POST["editarCI"])&&
                !empty($_POST["editarFechaNacimiento"])&&
                preg_match('/^[A-Z0-9+]+$/', $_POST["editarGrupoSanguineo"])&&
                preg_match('/^[0-9]+$/', $_POST["editarTelefono"])&&
                preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ\s#.,]+$/', $_POST["editarDireccion"])) {

                $tabla = "pacientes";
                $datos = array(
                    "id"     => $_POST["editarIdPaciente"],
                    "nombre" => $_POST["editarNombre"],
                    "ci" => $_POST["editarCI"],
                    "fecha_nacimiento" => $_POST["editarFechaNacimiento"],
                    "grupo_sanguineo" => $_POST["editarGrupoSanguineo"],
                    "telefono" => $_POST["editarTelefono"],
                    "direccion" => $_POST["editarDireccion"]
                );

                $respuesta = ModeloPacientes::mdlEditarPaciente($tabla, $datos);

                if (session_status() == PHP_SESSION_NONE) session_start();

                if ($respuesta == "ok") {
                    $_SESSION["editar_paciente"] = "ok";
                } else {
                    $_SESSION["editar_paciente"] = "error";
                }
                header("Location: " . $_SERVER["HTTP_REFERER"]);
                exit;
            }
        }
    }
    static public function ctrEliminarPacientes() {
        if (isset($_POST["eliminarPaciente"])) {
            $tabla = "pacientes";
            $id = $_POST["eliminarPaciente"];
            $respuesta = ModeloPacientes::mdlEliminarPacientes($tabla, $id);

            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }

            if ($respuesta == "ok") {
                $_SESSION["eliminar_paciente"] = "ok";
            } else {
                $_SESSION["eliminar_paciente"] = "error";
            }
            header("Location: " . $_SERVER["HTTP_REFERER"]);
            exit;
        }
    }
    
}