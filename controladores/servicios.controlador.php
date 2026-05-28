<?php
class ControladorServicios {
    static public function crtCrearServicios() {
        if(isset($_POST["nuevoNombre"], $_POST["nuevoPrecio"])){
             if(preg_match('/^[a-zA-ZñÑáéíóúÁÉÍÓÚ\s]+$/', $_POST["nuevoNombre"]) &&
                preg_match('/^[0-9]+\.?[0-9]*$/', $_POST["nuevoPrecio"])
               ){
                    $tabla = "tipo_servicio";
                    $datos = array(
                        "nombre" => $_POST["nuevoNombre"],
                        "precio" => $_POST["nuevoPrecio"]
                    );

                    $respuesta = ModeloServicios::MdlCrearServicios($tabla, $datos);

                    if (session_status() == PHP_SESSION_NONE) session_start();

                    if($respuesta == "ok"){
                        $_SESSION["crear_servicio"] = "ok";
                    } else {
                        $_SESSION["crear_servicio"] = "error";
                    }
                    header("Location: " . $_SERVER["HTTP_REFERER"]);
                    exit;
             }
        } 
    }
    static public function ctrMostrarServicios($item, $valor){
        $tabla = "tipo_servicio";
        $respuesta = ModeloServicios::mdlMostrarServicios($tabla, $item, $valor);
        return $respuesta;
    }
    static public function ctrEditarServicio() {
        if (isset($_POST["editarIdServicio"])) {
            if (preg_match('/^[a-zA-ZñÑáéíóúÁÉÍÓÚ\s]+$/', $_POST["editarNombre"]) &&
                preg_match('/^[0-9]+\.?[0-9]*$/', $_POST["editarPrecio"])) {

                $tabla = "tipo_servicio";
                $datos = array(
                    "id"     => $_POST["editarIdServicio"],
                    "nombre" => $_POST["editarNombre"],
                    "precio" => $_POST["editarPrecio"]
                );

                $respuesta = ModeloServicios::mdlEditarServicio($tabla, $datos);

                if (session_status() == PHP_SESSION_NONE) session_start();

                if ($respuesta == "ok") {
                    $_SESSION["editar_servicio"] = "ok";
                } else {
                    $_SESSION["editar_servicio"] = "error";
                }
                header("Location: " . $_SERVER["HTTP_REFERER"]);
                exit;
            }
        }
    }
    static public function ctrEliminarServicio() {
        if (isset($_POST["eliminarServicio"])) {
            $tabla = "tipo_servicio";
            $id = $_POST["eliminarServicio"];
            $respuesta = ModeloServicios::mdlEliminarServicio($tabla, $id);

            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }

            if ($respuesta == "ok") {
                $_SESSION["eliminar_servicio"] = "ok";
            } else {
                $_SESSION["eliminar_servicio"] = "error";
            }
            header("Location: " . $_SERVER["HTTP_REFERER"]);
            exit;
        }
    }
    
    }