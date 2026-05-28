<?php
require_once "conexion.php";

class ModeloPacientes {
    static public function MdlCrearPacientes($tabla, $datos) {
            $pdo = Conexion::conectar();
            $stmt = $pdo->prepare("INSERT INTO $tabla (nombre, ci, fecha_nacimiento, grupo_sanguineo, telefono, direccion) 
                                   VALUES (:nombre, :ci, :fecha_nacimiento, :grupo_sanguineo, :telefono, :direccion)");

            $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
            $stmt->bindParam(":ci", $datos["ci"], PDO::PARAM_STR);
            $stmt->bindParam(":fecha_nacimiento", $datos["fecha_nacimiento"], PDO::PARAM_STR);
            $stmt->bindParam(":grupo_sanguineo", $datos["grupo_sanguineo"], PDO::PARAM_STR);
            $stmt->bindParam(":telefono", $datos["telefono"], PDO::PARAM_STR);
            $stmt->bindParam(":direccion", $datos["direccion"], PDO::PARAM_STR);

            if ($stmt->execute()) {
                return "ok";
            } else {
                return "error";
            }
    }
    static public function mdlMostrarPacientes($tabla, $item, $valor) {

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
    static public function mdlEditarPaciente($tabla, $datos) {
        $stmt = Conexion::conectar()->prepare(
            "UPDATE $tabla SET nombre = :nombre, ci = :ci, fecha_nacimiento = :fecha_nacimiento, 
            grupo_sanguineo = :grupo_sanguineo, telefono = :telefono, direccion = :direccion
            WHERE id_paciente = :id"
        );
        $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
        $stmt->bindParam(":ci", $datos["ci"], PDO::PARAM_STR);
        $stmt->bindParam(":fecha_nacimiento", $datos["fecha_nacimiento"], PDO::PARAM_STR);
        $stmt->bindParam(":grupo_sanguineo", $datos["grupo_sanguineo"], PDO::PARAM_STR);
        $stmt->bindParam(":telefono", $datos["telefono"], PDO::PARAM_STR);
        $stmt->bindParam(":direccion", $datos["direccion"], PDO::PARAM_STR);
        $stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);

        if ($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }
    }
    static public function mdlEliminarPacientes($tabla, $id) {
        $stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE id_paciente = :id");
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }
    }
}