<?php
require_once "conexion.php";

class ModeloRoles {

    static public function mdlMostrarPermisosPorRol($id_rol) {
        $stmt = Conexion::conectar()->prepare(
            "SELECT p.id_permiso, p.nombre, p.modulo, rp.activo
             FROM rol_permiso rp
             INNER JOIN permisos p ON p.id_permiso = rp.id_permiso
             WHERE rp.id_rol = :id_rol
             AND p.activo = 1"
        );
        $stmt->bindParam(":id_rol", $id_rol, PDO::PARAM_INT);
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        $stmt = null;
        return $resultado;
    }

    static public function mdlMostrarRoles($tabla, $item, $valor) {
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

    static public function mdlCrearRol($datos, $permisos) {
        $pdo = Conexion::conectar();

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare(
                "INSERT INTO roles (nombre, activo) VALUES (:nombre, 1)"
            );
            $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
            $stmt->execute();
            $id_rol = $pdo->lastInsertId();

            $stmtRp = $pdo->prepare(
                "INSERT INTO rol_permiso (id_rol, id_permiso, activo) 
                 VALUES (:id_rol, :id_permiso, 1)"
            );
            foreach ($permisos as $id_permiso) {
                $stmtRp->bindParam(":id_rol",     $id_rol,     PDO::PARAM_INT);
                $stmtRp->bindParam(":id_permiso", $id_permiso, PDO::PARAM_INT);
                $stmtRp->execute();
            }

            $pdo->commit();
            return "ok";

        } catch (Exception $e) {
            $pdo->rollBack();
            return "error";
        }
    }

    static public function mdlEditarRol($datos, $permisos) {
        $pdo = Conexion::conectar();

        try {
            $pdo->beginTransaction();

            // Actualizar nombre del rol
            $stmt = $pdo->prepare(
                "UPDATE roles SET nombre = :nombre WHERE id_rol = :id"
            );
            $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
            $stmt->bindParam(":id",     $datos["id"],     PDO::PARAM_INT);
            $stmt->execute();

            // Desactivar todos los permisos actuales del rol
            $stmtDes = $pdo->prepare(
                "UPDATE rol_permiso SET activo = 0 WHERE id_rol = :id_rol"
            );
            $stmtDes->bindParam(":id_rol", $datos["id"], PDO::PARAM_INT);
            $stmtDes->execute();

            // Reactivar o insertar los permisos seleccionados
            $stmtCheck = $pdo->prepare(
                "SELECT id_permiso FROM rol_permiso 
                 WHERE id_rol = :id_rol AND id_permiso = :id_permiso"
            );
            $stmtAct = $pdo->prepare(
                "UPDATE rol_permiso SET activo = 1 
                 WHERE id_rol = :id_rol AND id_permiso = :id_permiso"
            );
            $stmtIns = $pdo->prepare(
                "INSERT INTO rol_permiso (id_rol, id_permiso, activo) 
                 VALUES (:id_rol, :id_permiso, 1)"
            );

            foreach ($permisos as $id_permiso) {
                $stmtCheck->bindParam(":id_rol",     $datos["id"], PDO::PARAM_INT);
                $stmtCheck->bindParam(":id_permiso", $id_permiso,  PDO::PARAM_INT);
                $stmtCheck->execute();

                if ($stmtCheck->fetch()) {
                    $stmtAct->bindParam(":id_rol",     $datos["id"], PDO::PARAM_INT);
                    $stmtAct->bindParam(":id_permiso", $id_permiso,  PDO::PARAM_INT);
                    $stmtAct->execute();
                } else {
                    $stmtIns->bindParam(":id_rol",     $datos["id"], PDO::PARAM_INT);
                    $stmtIns->bindParam(":id_permiso", $id_permiso,  PDO::PARAM_INT);
                    $stmtIns->execute();
                }
            }

            $pdo->commit();
            return "ok";

        } catch (Exception $e) {
            $pdo->rollBack();
            return "error";
        }
    }

    static public function mdlEliminarRol($id) {
        $stmt = Conexion::conectar()->prepare(
            "UPDATE roles SET activo = 0 WHERE id_rol = :id"
        );
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        return $stmt->execute() ? "ok" : "error";
    }
}
?>