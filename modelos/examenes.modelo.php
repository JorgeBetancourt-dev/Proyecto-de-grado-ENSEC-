<?php
require_once "conexion.php";

class ModeloExamenes {

    static public function mdlMostrarExamenes($tabla, $item, $valor) {
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

    static public function mdlCrearExamen($tabla, $datos) {
        $stmt = Conexion::conectar()->prepare(
            "INSERT INTO $tabla (nombre, precio, activo) VALUES (:nombre, :precio, 1)"
        );
        $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
        $stmt->bindParam(":precio", $datos["precio"], PDO::PARAM_STR);
        return $stmt->execute() ? "ok" : "error";
    }

    static public function mdlEditarExamen($tabla, $datos) {
        $stmt = Conexion::conectar()->prepare(
            "UPDATE $tabla SET nombre = :nombre, precio = :precio WHERE id_examen = :id"
        );
        $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
        $stmt->bindParam(":precio", $datos["precio"], PDO::PARAM_STR);
        $stmt->bindParam(":id",     $datos["id"],     PDO::PARAM_INT);
        return $stmt->execute() ? "ok" : "error";
    }

    static public function mdlEliminarExamen($tabla, $id) {
        $stmt = Conexion::conectar()->prepare(
            "UPDATE $tabla SET activo = 0 WHERE id_examen = :id"
        );
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        return $stmt->execute() ? "ok" : "error";
    }
}
?>