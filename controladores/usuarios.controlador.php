<?php
class ControladorUsuarios {

    public function crtIngresoUsuario() {
        if (isset($_POST["ingUsuario"])) {
            if (preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ]+$/', $_POST["ingUsuario"]) &&
                strlen($_POST["ingPassword"]) >= 1) {

                $tabla = "usuarios";
                $item  = "usuario";
                $valor = $_POST["ingUsuario"];

                $respuesta = ModeloUsuarios::mdlMostrarUsuarios($tabla, $item, $valor);

                if ($respuesta &&
                    $respuesta["usuario"] == $_POST["ingUsuario"] &&
                    password_verify($_POST["ingPassword"], $respuesta["contraseña"])) {

                    if (session_status() == PHP_SESSION_NONE) session_start();

                    $_SESSION["iniciarSesion"] = "ok";
                    $_SESSION["IdUsuario"]     = $respuesta["id_usuario"];
                    $_SESSION["nombre"]        = $respuesta["nombre"];
                    $_SESSION["apellido"]      = $respuesta["apellido"];
                    $_SESSION["usuario"]       = $respuesta["usuario"];
                    $_SESSION["id_rol"]        = $respuesta["id_rol"];
                    $_SESSION["nombre_rol"]    = $respuesta["nombre_rol"];

                    if (password_needs_rehash($respuesta["contraseña"], PASSWORD_BCRYPT, ['cost' => 12])) {
                        $nuevoHash = password_hash($_POST["ingPassword"], PASSWORD_BCRYPT, ['cost' => 12]);
                        ModeloUsuarios::mdlActualizarHash($tabla, $respuesta["id_usuario"], $nuevoHash);
                    }

                    echo '<script>window.location = "inicio";</script>';

                } else {
                    echo '<br><div class="alert alert-danger">Error al ingresar, vuelve a intentarlo</div>';
                }
            }
        }
    }

    static public function ctrMostrarUsuarios($item, $valor) {
        $tabla = "usuarios";
        $respuesta = ModeloUsuarios::mdlMostrarUsuarios($tabla, $item, $valor);
        return $respuesta;
    }

    static public function crtCrearUsuarios() {
        if (isset($_POST["nuevoNombre"], $_POST["nuevoApellido"], $_POST["nuevoUsuario"],
                  $_POST["nuevoPassword"], $_POST["nuevoIdRol"], $_POST["nuevoIdHorario"])) {
            if (preg_match('/^[a-zA-ZñÑáéíóúÁÉÍÓÚ\s]+$/', $_POST["nuevoNombre"])     &&
                preg_match('/^[a-zA-ZñÑáéíóúÁÉÍÓÚ\s]+$/', $_POST["nuevoApellido"])  &&
                preg_match('/^[a-zA-Z0-9]+$/', $_POST["nuevoUsuario"])               &&
                preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $_POST["nuevoPassword"]) &&
                is_numeric($_POST["nuevoIdRol"])                                      &&
                is_numeric($_POST["nuevoIdHorario"])) {

                $tabla        = "usuarios";
                $passwordHash = password_hash($_POST["nuevoPassword"], PASSWORD_BCRYPT, ['cost' => 12]);

                $datos = array(
                    "nombre"     => $_POST["nuevoNombre"],
                    "apellido"   => $_POST["nuevoApellido"],
                    "usuario"    => $_POST["nuevoUsuario"],
                    "contraseña" => $passwordHash,
                    "id_rol"     => (int) $_POST["nuevoIdRol"],
                    "id_horario" => (int) $_POST["nuevoIdHorario"]
                );

                $respuesta = ModeloUsuarios::mdlCrearUsuarios($tabla, $datos);

                if (session_status() == PHP_SESSION_NONE) session_start();
                $_SESSION["crear_usuario"] = ($respuesta == "ok") ? "ok" : "error";

                header("Location: " . $_SERVER["HTTP_REFERER"]);
                exit;
            } else {
                if (session_status() == PHP_SESSION_NONE) session_start();
                $_SESSION["crear_usuario"] = "error";
                header("Location: " . $_SERVER["HTTP_REFERER"]);
                exit;
            }
        }
    }

    static public function ctrEditarUsuarios() {
        if (isset($_POST["editarIdUsuario"], $_POST["editarNombre"], $_POST["editarApellido"],
                  $_POST["editarUsuario"], $_POST["editarIdRol"], $_POST["editarIdHorario"])) {
            if (preg_match('/^[a-zA-ZñÑáéíóúÁÉÍÓÚ\s]+$/', $_POST["editarNombre"])    &&
                preg_match('/^[a-zA-ZñÑáéíóúÁÉÍÓÚ\s]+$/', $_POST["editarApellido"]) &&
                preg_match('/^[a-zA-Z0-9]+$/', $_POST["editarUsuario"])              &&
                is_numeric($_POST["editarIdRol"])                                     &&
                is_numeric($_POST["editarIdHorario"])) {

                $tabla = "usuarios";
                $datos = array(
                    "id"         => $_POST["editarIdUsuario"],
                    "nombre"     => $_POST["editarNombre"],
                    "apellido"   => $_POST["editarApellido"],
                    "usuario"    => $_POST["editarUsuario"],
                    "id_rol"     => (int) $_POST["editarIdRol"],
                    "id_horario" => (int) $_POST["editarIdHorario"]
                );

                $respuesta = ModeloUsuarios::mdlEditarUsuario($tabla, $datos);

                if (session_status() == PHP_SESSION_NONE) session_start();
                $_SESSION["editar_usuario"] = ($respuesta == "ok") ? "ok" : "error";

                header("Location: " . $_SERVER["HTTP_REFERER"]);
                exit;
            }
        }
    }

    static public function ctrEliminarUsuarios() {
        if (isset($_POST["eliminarUsuario"])) {
            $tabla     = "usuarios";
            $id        = $_POST["eliminarUsuario"];
            $respuesta = ModeloUsuarios::mdlEliminarUsuarios($tabla, $id);

            if (session_status() == PHP_SESSION_NONE) session_start();
            $_SESSION["eliminar_usuario"] = ($respuesta == "ok") ? "ok" : "error";

            header("Location: " . $_SERVER["HTTP_REFERER"]);
            exit;
        }
    }

    public function crtCambiarContraseñaUsuario() {
        if (isset($_POST["act_password"], $_POST["new_password1"], $_POST["new_password2"])) {
            if (strlen($_POST["act_password"]) >= 1 &&
                strlen($_POST["new_password1"]) >= 8 &&
                strlen($_POST["new_password2"]) >= 8) {

                $tabla = "usuarios";
                $datos = array(
                    "act"    => $_POST["act_password"],
                    "new_p1" => $_POST["new_password1"],
                    "new_p2" => $_POST["new_password2"]
                );

                $respuesta = ModeloUsuarios::mdlCambiarContraseñaUsuario($tabla, $datos);

                if (session_status() == PHP_SESSION_NONE) session_start();
                $_SESSION["cambiar_password"] = $respuesta;
            }
        }
    }
}
?>