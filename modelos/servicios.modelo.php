<?php
require_once "conexion.php";

class ModeloServicios {
    static public function MdlCrearServicios($tabla, $datos) {
            $pdo = Conexion::conectar();
            $stmt = $pdo->prepare("INSERT INTO $tabla (nombre, precio) 
                                   VALUES (:nombre, :precio)");

            $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
            $stmt->bindParam(":precio", $datos["precio"], PDO::PARAM_STR);

            if ($stmt->execute()) {
                return "ok";
            } else {
                return "error";
            }
    }
    static public function mdlMostrarServicios($tabla, $item, $valor) {

        if ($item != null) {

            $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item");
            $stmt->bindParam(":".$item, $valor, PDO::PARAM_STR);
            $stmt->execute();

            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        } else {

            $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla");
            $stmt->execute();

            $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $stmt->closeCursor();
        $stmt = null;

        return $resultado;
    }
    static public function mdlEditarServicio($tabla, $datos) {
        $stmt = Conexion::conectar()->prepare(
            "UPDATE $tabla SET nombre = :nombre, precio = :precio 
            WHERE id_tipo_servicio = :id"
        );
        $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
        $stmt->bindParam(":precio", $datos["precio"], PDO::PARAM_STR);
        $stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);

        if ($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }
    }
    static public function mdlEliminarServicio($tabla, $id) {
        $stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE id_tipo_servicio = :id");
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }
    }  
}