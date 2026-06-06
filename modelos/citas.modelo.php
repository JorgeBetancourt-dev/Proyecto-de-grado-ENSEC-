<?php
require_once "conexion.php";

class ModeloCitas {

    // Buscar paciente por carnet de identidad
    static public function mdlBuscarPacientePorCarnet($carnet) {
        $stmt = Conexion::conectar()->prepare(
            "SELECT id_paciente, nombre, apellidos, ci, grupo_sanguineo, telefono, fecha_nacimiento, direccion
             FROM pacientes 
             WHERE ci = :carnet AND activo = 1"
        );
        $stmt->bindParam(":carnet", $carnet, PDO::PARAM_STR);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        $stmt = null;
        return $resultado;
    }

    // Registrar nuevo paciente desde el formulario inline
    static public function mdlCrearPacienteDesideCita($datos) {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare(
            "INSERT INTO pacientes (nombre, apellidos, ci, grupo_sanguineo, telefono, fecha_nacimiento, direccion, activo)
             VALUES (:nombre, :apellidos, :ci, :grupo_sanguineo, :telefono, :fecha_nacimiento, :direccion, 1)"
        );
        $stmt->bindParam(":nombre",           $datos["nombre"],           PDO::PARAM_STR);
        $stmt->bindParam(":apellidos",        $datos["apellidos"],        PDO::PARAM_STR);
        $stmt->bindParam(":ci",               $datos["ci"],               PDO::PARAM_STR);
        $stmt->bindParam(":grupo_sanguineo",  $datos["grupo_sanguineo"],  PDO::PARAM_STR);
        $stmt->bindParam(":telefono",         $datos["telefono"],         PDO::PARAM_STR);
        $stmt->bindParam(":fecha_nacimiento", $datos["fecha_nacimiento"], PDO::PARAM_STR);
        $stmt->bindParam(":direccion",        $datos["direccion"],        PDO::PARAM_STR);

        if ($stmt->execute()) {
            return $conn->lastInsertId();
        }
        return null;
    }

    // Obtener todas las citas activas para FullCalendar (AJAX GET)
    static public function mdlMostrarCitasCalendario() {
        $stmt = Conexion::conectar()->prepare(
            "SELECT c.id_cita, c.fecha, c.hora, c.estado,
                    p.nombre AS pac_nombre, p.apellidos AS pac_apellidos, p.ci AS pac_ci,
                    p.telefono AS pac_telefono,
                    CONCAT(m.nombre, ' ', m.apellido) AS medico,
                    CONCAT(r.nombre, ' ', r.apellido) AS recepcionista_registra
             FROM citas c
             INNER JOIN pacientes p ON c.id_paciente = p.id_paciente
             LEFT  JOIN usuarios m  ON c.id_medico = m.id_usuario
             LEFT  JOIN usuarios r  ON c.id_recepcionista_registra = r.id_usuario
             WHERE c.activo = 1
             ORDER BY c.fecha ASC, c.hora ASC"
        );
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        $stmt = null;
        return $resultado;
    }
    static public function mdlMostrarMedicos() {
        $stmt = Conexion::conectar()->prepare(
            "SELECT u.id_usuario, u.nombre, u.apellido 
             FROM usuarios u
             INNER JOIN roles r ON u.id_rol = r.id_rol
             WHERE r.nombre = 'medico' AND u.activo = 1
             ORDER BY u.nombre ASC"
        );
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        $stmt = null;
        return $resultado;
    }

    // Registrar nueva cita (paso 1)
    static public function mdlCrearCita($datos) {
        $stmt = Conexion::conectar()->prepare(
            "INSERT INTO citas 
                (id_paciente, id_recepcionista_registra, id_recepcionista_asigna, id_medico, fecha, hora, estado, activo) 
             VALUES 
                (:id_paciente, :id_recepcionista_registra, :id_recepcionista_asigna, :id_medico, :fecha, :hora, 'pendiente', 1)"
        );
        $stmt->bindParam(":id_paciente",                $datos["id_paciente"],               PDO::PARAM_INT);
        $stmt->bindParam(":id_recepcionista_registra",  $datos["id_recepcionista_registra"],  PDO::PARAM_INT);
        $stmt->bindValue(":id_recepcionista_asigna",    $datos["id_recepcionista_asigna"],    $datos["id_recepcionista_asigna"] === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(":id_medico",                  $datos["id_medico"],                  $datos["id_medico"]               === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindParam(":fecha",                      $datos["fecha"],                      PDO::PARAM_STR);
        $stmt->bindParam(":hora",                       $datos["hora"],                       PDO::PARAM_STR);
        return $stmt->execute() ? "ok" : "error";
    }
}
?>