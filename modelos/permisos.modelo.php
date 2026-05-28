<?php
require_once "conexion.php";

class ModeloPermisos {

    static public function mdlMostrarPermisos($tabla, $item, $valor) {
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

    static public function mdlCrearPermiso($tabla, $datos) {
        $stmt = Conexion::conectar()->prepare(
            "INSERT INTO $tabla (modulo, nombre, activo) 
             VALUES (:modulo, :nombre, 1)"
        );
        $stmt->bindParam(":modulo", $datos["modulo"], PDO::PARAM_STR);
        $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
        return $stmt->execute() ? "ok" : "error";
    }

    static public function mdlEditarPermiso($tabla, $datos) {
        $stmt = Conexion::conectar()->prepare(
            "UPDATE $tabla SET modulo = :modulo, nombre = :nombre 
             WHERE id_permiso = :id"
        );
        $stmt->bindParam(":modulo", $datos["modulo"], PDO::PARAM_STR);
        $stmt->bindParam(":nombre", $datos["nombre"],  PDO::PARAM_STR);
        $stmt->bindParam(":id",     $datos["id"],      PDO::PARAM_INT);
        return $stmt->execute() ? "ok" : "error";
    }

    static public function mdlEliminarPermiso($tabla, $id) {
        $stmt = Conexion::conectar()->prepare(
            "UPDATE $tabla SET activo = 0 WHERE id_permiso = :id"
        );
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        return $stmt->execute() ? "ok" : "error";
    }
}
?>