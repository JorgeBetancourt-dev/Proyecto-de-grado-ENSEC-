<?php
require_once "conexion.php";

class ModeloHorarios {

    static public function mdlMostrarHorarios($item, $valor) {
        $base = "SELECT * FROM horarios WHERE activo = 1";

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

    static public function mdlCrearHorario($datos) {
        $stmt = Conexion::conectar()->prepare(
            "INSERT INTO horarios (nombre, hora_inicio, hora_fin, horaI_sabado, horaF_sabado, activo)
             VALUES (:nombre, :hora_inicio, :hora_fin, :horaI_sabado, :horaF_sabado, 1)"
        );
        $stmt->bindParam(":nombre",       $datos["nombre"],       PDO::PARAM_STR);
        $stmt->bindParam(":hora_inicio",  $datos["hora_inicio"],  PDO::PARAM_STR);
        $stmt->bindParam(":hora_fin",     $datos["hora_fin"],     PDO::PARAM_STR);
        $stmt->bindParam(":horaI_sabado", $datos["horaI_sabado"], PDO::PARAM_STR);
        $stmt->bindParam(":horaF_sabado", $datos["horaF_sabado"], PDO::PARAM_STR);
        return $stmt->execute() ? "ok" : "error";
    }

    static public function mdlEditarHorario($datos) {
        $stmt = Conexion::conectar()->prepare(
            "UPDATE horarios SET
                nombre       = :nombre,
                hora_inicio  = :hora_inicio,
                hora_fin     = :hora_fin,
                horaI_sabado = :horaI_sabado,
                horaF_sabado = :horaF_sabado
             WHERE id_horario = :id"
        );
        $stmt->bindParam(":nombre",       $datos["nombre"],       PDO::PARAM_STR);
        $stmt->bindParam(":hora_inicio",  $datos["hora_inicio"],  PDO::PARAM_STR);
        $stmt->bindParam(":hora_fin",     $datos["hora_fin"],     PDO::PARAM_STR);
        $stmt->bindParam(":horaI_sabado", $datos["horaI_sabado"], PDO::PARAM_STR);
        $stmt->bindParam(":horaF_sabado", $datos["horaF_sabado"], PDO::PARAM_STR);
        $stmt->bindParam(":id",           $datos["id"],           PDO::PARAM_INT);
        return $stmt->execute() ? "ok" : "error";
    }

    static public function mdlEliminarHorario($id) {
        $stmt = Conexion::conectar()->prepare(
            "UPDATE horarios SET activo = 0 WHERE id_horario = :id"
        );
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        return $stmt->execute() ? "ok" : "error";
    }
}
?>