<?php
require_once "conexion.php";

class ModeloPacientes {

    static public function mdlMostrarPacientes($tabla, $item, $valor) {
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

    static public function mdlCrearPaciente($tabla, $datos) {
        $stmt = Conexion::conectar()->prepare(
            "INSERT INTO $tabla (nombre, apellidos, ci, grupo_sanguineo, telefono, fecha_nacimiento, direccion, activo)
             VALUES (:nombre, :apellidos, :ci, :grupo_sanguineo, :telefono, :fecha_nacimiento, :direccion, 1)"
        );
        $stmt->bindParam(":nombre",           $datos["nombre"],           PDO::PARAM_STR);
        $stmt->bindParam(":apellidos",        $datos["apellidos"],        PDO::PARAM_STR);
        $stmt->bindParam(":ci",               $datos["ci"],               PDO::PARAM_STR);
        $stmt->bindParam(":grupo_sanguineo",  $datos["grupo_sanguineo"],  PDO::PARAM_STR);
        $stmt->bindParam(":telefono",         $datos["telefono"],         PDO::PARAM_STR);
        $stmt->bindParam(":fecha_nacimiento", $datos["fecha_nacimiento"], PDO::PARAM_STR);
        $stmt->bindParam(":direccion",        $datos["direccion"],        PDO::PARAM_STR);
        return $stmt->execute() ? "ok" : "error";
    }

    static public function mdlEditarPaciente($tabla, $datos) {
        $stmt = Conexion::conectar()->prepare(
            "UPDATE $tabla SET nombre = :nombre, apellidos = :apellidos, ci = :ci,
             grupo_sanguineo = :grupo_sanguineo, telefono = :telefono,
             fecha_nacimiento = :fecha_nacimiento, direccion = :direccion
             WHERE id_paciente = :id"
        );
        $stmt->bindParam(":nombre",           $datos["nombre"],           PDO::PARAM_STR);
        $stmt->bindParam(":apellidos",        $datos["apellidos"],        PDO::PARAM_STR);
        $stmt->bindParam(":ci",               $datos["ci"],               PDO::PARAM_STR);
        $stmt->bindParam(":grupo_sanguineo",  $datos["grupo_sanguineo"],  PDO::PARAM_STR);
        $stmt->bindParam(":telefono",         $datos["telefono"],         PDO::PARAM_STR);
        $stmt->bindParam(":fecha_nacimiento", $datos["fecha_nacimiento"], PDO::PARAM_STR);
        $stmt->bindParam(":direccion",        $datos["direccion"],        PDO::PARAM_STR);
        $stmt->bindParam(":id",               $datos["id"],               PDO::PARAM_INT);
        return $stmt->execute() ? "ok" : "error";
    }

    static public function mdlEliminarPaciente($tabla, $id) {
        $stmt = Conexion::conectar()->prepare(
            "UPDATE $tabla SET activo = 0 WHERE id_paciente = :id"
        );
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        return $stmt->execute() ? "ok" : "error";
    }
}
?>