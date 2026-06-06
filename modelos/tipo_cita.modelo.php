<?php
require_once "conexion.php";

class ModeloTipoCita {

    static public function mdlMostrarTiposCita($item, $valor) {
        $base = "SELECT * FROM tipo_cita WHERE activo = 1";

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

    static public function mdlCrearTipoCita($datos) {
        $stmt = Conexion::conectar()->prepare(
            "INSERT INTO tipo_cita (nombre, tiempo, activo)
             VALUES (:nombre, :tiempo, 1)"
        );
        $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
        $stmt->bindParam(":tiempo", $datos["tiempo"], PDO::PARAM_INT);
        return $stmt->execute() ? "ok" : "error";
    }

    static public function mdlEditarTipoCita($datos) {
        $stmt = Conexion::conectar()->prepare(
            "UPDATE tipo_cita SET
                nombre = :nombre,
                tiempo = :tiempo
             WHERE id_tipo_cita = :id"
        );
        $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
        $stmt->bindParam(":tiempo", $datos["tiempo"], PDO::PARAM_INT);
        $stmt->bindParam(":id",     $datos["id"],     PDO::PARAM_INT);
        return $stmt->execute() ? "ok" : "error";
    }

    static public function mdlEliminarTipoCita($id) {
        $stmt = Conexion::conectar()->prepare(
            "UPDATE tipo_cita SET activo = 0 WHERE id_tipo_cita = :id"
        );
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        return $stmt->execute() ? "ok" : "error";
    }
}
?>