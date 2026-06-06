<?php
require_once "conexion.php";

class ModeloUsuarios {

    static public function mdlMostrarUsuarios($tabla, $item, $valor) {
        $base = "SELECT u.*, r.nombre AS nombre_rol, h.nombre AS nombre_horario
                 FROM $tabla u
                 LEFT JOIN roles r    ON r.id_rol     = u.id_rol
                 LEFT JOIN horarios h ON h.id_horario = u.id_horario
                 WHERE u.activo = 1";

        if ($item != null) {
            $stmt = Conexion::conectar()->prepare("$base AND u.$item = :val");
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

    static public function mdlActualizarHash($tabla, $idUsuario, $nuevoHash) {
        $stmt = Conexion::conectar()->prepare(
            "UPDATE $tabla SET contraseña = :contrasena WHERE id_usuario = :id"
        );
        $stmt->bindParam(":contrasena", $nuevoHash, PDO::PARAM_STR);
        $stmt->bindParam(":id", $idUsuario, PDO::PARAM_INT);
        $stmt->execute();
    }

    static public function mdlCrearUsuarios($tabla, $datos) {
        $stmt = Conexion::conectar()->prepare(
            "INSERT INTO $tabla (nombre, apellido, usuario, contraseña, id_rol, id_horario, activo)
             VALUES (:nombre, :apellido, :usuario, :contrasena, :id_rol, :id_horario, 1)"
        );
        $stmt->bindParam(":nombre",     $datos["nombre"],     PDO::PARAM_STR);
        $stmt->bindParam(":apellido",   $datos["apellido"],   PDO::PARAM_STR);
        $stmt->bindParam(":usuario",    $datos["usuario"],    PDO::PARAM_STR);
        $stmt->bindParam(":contrasena", $datos["contraseña"], PDO::PARAM_STR);
        $stmt->bindParam(":id_rol",     $datos["id_rol"],     PDO::PARAM_INT);
        $stmt->bindParam(":id_horario", $datos["id_horario"], PDO::PARAM_INT);
        return $stmt->execute() ? "ok" : "error";
    }

    static public function mdlEditarUsuario($tabla, $datos) {
        $stmt = Conexion::conectar()->prepare(
            "UPDATE $tabla SET
                nombre     = :nombre,
                apellido   = :apellido,
                usuario    = :usuario,
                id_rol     = :id_rol,
                id_horario = :id_horario
             WHERE id_usuario = :id"
        );
        $stmt->bindParam(":nombre",     $datos["nombre"],     PDO::PARAM_STR);
        $stmt->bindParam(":apellido",   $datos["apellido"],   PDO::PARAM_STR);
        $stmt->bindParam(":usuario",    $datos["usuario"],    PDO::PARAM_STR);
        $stmt->bindParam(":id_rol",     $datos["id_rol"],     PDO::PARAM_INT);
        $stmt->bindParam(":id_horario", $datos["id_horario"], PDO::PARAM_INT);
        $stmt->bindParam(":id",         $datos["id"],         PDO::PARAM_INT);
        return $stmt->execute() ? "ok" : "error";
    }

    static public function mdlEliminarUsuarios($tabla, $id) {
        $stmt = Conexion::conectar()->prepare(
            "UPDATE $tabla SET activo = 0 WHERE id_usuario = :id"
        );
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        return $stmt->execute() ? "ok" : "error";
    }

    static public function mdlCambiarContraseñaUsuario($tabla, $datos) {
        if ($datos["new_p1"] != $datos["new_p2"]) return "no_coinciden";

        $idUsuario = $_SESSION["IdUsuario"];
        $pdo = Conexion::conectar();

        $stmt = $pdo->prepare("SELECT contraseña FROM $tabla WHERE id_usuario = :id");
        $stmt->bindParam(":id", $idUsuario, PDO::PARAM_INT);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$resultado || !password_verify($datos["act"], $resultado["contraseña"])) {
            return "password_incorrecta";
        }

        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $datos["new_p1"])) {
            return "password_debil";
        }

        $nuevoHash = password_hash($datos["new_p1"], PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $pdo->prepare("UPDATE $tabla SET contraseña = :contrasena WHERE id_usuario = :id");
        $stmt->bindParam(":contrasena", $nuevoHash, PDO::PARAM_STR);
        $stmt->bindParam(":id", $idUsuario, PDO::PARAM_INT);

        return $stmt->execute() ? "ok" : "error";
    }
}
?>