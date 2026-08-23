<?php
require_once "conexion.php";

class ModeloClientes {

    static public function mdlMostrarClientes($tabla, $item, $valor) {
        $base = "SELECT * FROM $tabla WHERE activo = 1";

        if ($item != null) {
            $stmt = Conexion::conectar()->prepare("$base AND $item = :val");
            $stmt->bindParam(":val", $valor, PDO::PARAM_STR);
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $stmt = Conexion::conectar()->prepare($base);
            $stmt->execute();
            $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $stmt->closeCursor();
        $stmt = null;
        return $resultado;
    }

    static public function mdlCrearCliente($tabla, $datos) {
        $stmt = Conexion::conectar()->prepare(
            "INSERT INTO $tabla (nombre, apellidos, nit, activo) 
             VALUES (:nombre, :apellidos, :nit, 1)"
        );
        $stmt->bindParam(":nombre",    $datos["nombre"],    PDO::PARAM_STR);
        $stmt->bindParam(":apellidos", $datos["apellidos"], PDO::PARAM_STR);
        $stmt->bindParam(":nit",       $datos["nit"],       PDO::PARAM_STR);
        return $stmt->execute() ? "ok" : "error";
    }

    static public function mdlEditarCliente($tabla, $datos) {
        $stmt = Conexion::conectar()->prepare(
            "UPDATE $tabla SET nombre = :nombre, apellidos = :apellidos, nit = :nit 
            WHERE id_cliente = :id"
        );
        $stmt->bindParam(":nombre",    $datos["nombre"],    PDO::PARAM_STR);
        $stmt->bindParam(":apellidos", $datos["apellidos"], PDO::PARAM_STR);
        $stmt->bindParam(":nit",       $datos["nit"],       PDO::PARAM_STR);
        $stmt->bindParam(":id",        $datos["id"],        PDO::PARAM_INT);
        return $stmt->execute() ? "ok" : "error";
    }

    static public function mdlEliminarCliente($tabla, $id) {
        $stmt = Conexion::conectar()->prepare(
            "UPDATE $tabla SET activo = 0 WHERE id_cliente = :id"
        );
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        return $stmt->execute() ? "ok" : "error";
    }

    // ── Buscador del panel de pagos (coincidencia exacta por NIT) ───────────

    static public function mdlBuscarClientePorNit($nit) {
        $stmt = Conexion::conectar()->prepare(
            "SELECT id_cliente, nombre, nit FROM clientes WHERE nit = :nit AND activo = 1 LIMIT 1"
        );
        $stmt->bindParam(":nit", $nit, PDO::PARAM_STR);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $resultado;
    }

    static public function mdlCrearClienteRapido($nombre, $nit) {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare("INSERT INTO clientes (nombre, nit, activo) VALUES (:nombre, :nit, 1)");
        $stmt->bindParam(":nombre", $nombre, PDO::PARAM_STR);
        $stmt->bindValue(":nit", $nit ?: null, $nit ? PDO::PARAM_STR : PDO::PARAM_NULL);
        if (!$stmt->execute()) {
            return null;
        }
        return (int) $conn->lastInsertId();
    }
}
?>