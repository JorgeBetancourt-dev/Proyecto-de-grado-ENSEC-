<?php
class ControladorClientes {

    static public function ctrMostrarClientes($item, $valor) {
        $tabla = "clientes";
        $respuesta = ModeloClientes::mdlMostrarClientes($tabla, $item, $valor);
        return $respuesta;
    }

    static public function crtCrearCliente() {
        if (isset($_POST["nuevoNombreCliente"], $_POST["nuevoApellidosCliente"])) {
            if (preg_match('/^[a-zA-ZñÑáéíóúÁÉÍÓÚ\s]+$/', $_POST["nuevoNombreCliente"]) &&
                preg_match('/^[a-zA-ZñÑáéíóúÁÉÍÓÚ\s]+$/', $_POST["nuevoApellidosCliente"])) {

                $tabla = "clientes";
                $datos = array(
                    "nombre"    => trim($_POST["nuevoNombreCliente"]),
                    "apellidos" => trim($_POST["nuevoApellidosCliente"]),
                    "nit"       => trim($_POST["nuevoNitCliente"] ?? "")
                );

                $respuesta = ModeloClientes::mdlCrearCliente($tabla, $datos);

                if (session_status() == PHP_SESSION_NONE) session_start();
                $_SESSION["crear_cliente"] = ($respuesta == "ok") ? "ok" : "error";

                header("Location: " . $_SERVER["HTTP_REFERER"]);
                exit;
            }
        }
    }

    static public function ctrEditarCliente() {
        if (isset($_POST["editarIdCliente"], $_POST["editarNombreCliente"], $_POST["editarApellidosCliente"])) {
            if (preg_match('/^[a-zA-ZñÑáéíóúÁÉÍÓÚ\s]+$/', $_POST["editarNombreCliente"])    &&
                preg_match('/^[a-zA-ZñÑáéíóúÁÉÍÓÚ\s]+$/', $_POST["editarApellidosCliente"]) &&
                is_numeric($_POST["editarIdCliente"])) {

                $tabla = "clientes";
                $datos = array(
                    "id"        => $_POST["editarIdCliente"],
                    "nombre"    => trim($_POST["editarNombreCliente"]),
                    "apellidos" => trim($_POST["editarApellidosCliente"]),
                    "nit"       => trim($_POST["editarNitCliente"] ?? "")
                );

                $respuesta = ModeloClientes::mdlEditarCliente($tabla, $datos);

                if (session_status() == PHP_SESSION_NONE) session_start();
                $_SESSION["editar_cliente"] = ($respuesta == "ok") ? "ok" : "error";

                header("Location: " . $_SERVER["HTTP_REFERER"]);
                exit;
            }
        }
    }

    static public function ctrEliminarCliente() {
        if (isset($_POST["eliminarCliente"])) {
            if (is_numeric($_POST["eliminarCliente"])) {
                $tabla     = "clientes";
                $id        = $_POST["eliminarCliente"];
                $respuesta = ModeloClientes::mdlEliminarCliente($tabla, $id);

                if (session_status() == PHP_SESSION_NONE) session_start();
                $_SESSION["eliminar_cliente"] = ($respuesta == "ok") ? "ok" : "error";

                header("Location: " . $_SERVER["HTTP_REFERER"]);
                exit;
            }
        }
    }

    // ── Buscador del panel de pagos (coincidencia exacta por NIT) ───────────

    static public function ctrBuscarClientePorNit() {
        if (isset($_GET["buscarClientePorNit"])) {
            $nit = trim($_GET["buscarClientePorNit"]);
            if ($nit === "") {
                echo json_encode(["encontrado" => false]);
                exit;
            }
            $cliente = ModeloClientes::mdlBuscarClientePorNit($nit);
            echo json_encode(["encontrado" => (bool) $cliente, "cliente" => $cliente ?: null]);
            exit;
        }
    }
}
?>