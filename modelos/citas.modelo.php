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
                    CONCAT(r.nombre, ' ', r.apellido) AS recepcionista_registra,
                    tc.nombre AS tipo_cita
             FROM citas c
             INNER JOIN pacientes p ON c.id_paciente = p.id_paciente
             LEFT  JOIN usuarios m    ON c.id_medico = m.id_usuario
             LEFT  JOIN usuarios r    ON c.id_recepcionista_registra = r.id_usuario
             LEFT  JOIN tipo_cita tc  ON c.id_tipo_cita = tc.id_tipo_cita
             WHERE c.activo = 1
             ORDER BY c.fecha ASC, c.hora ASC"
        );
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        $stmt = null;
        return $resultado;
    }
    // Obtener médicos disponibles según fecha y tipo de cita
    static public function mdlMostrarMedicosPorHorario($fecha, $id_tipo_cita) {
        // Determinar si es sábado (6) o lun-vie
        $diaSemana = date('N', strtotime($fecha)); // 1=lun ... 6=sab
        $esSabado  = ($diaSemana == 6);

        // Columnas de horario según día
        $colInicio = $esSabado ? "h.horaI_sabado" : "h.hora_inicio";
        $colFin    = $esSabado ? "h.horaF_sabado" : "h.hora_fin";

        $stmt = Conexion::conectar()->prepare(
            "SELECT u.id_usuario, u.nombre, u.apellido,
                    $colInicio AS turno_inicio,
                    $colFin    AS turno_fin
             FROM usuarios u
             INNER JOIN roles    r ON r.id_rol     = u.id_rol
             INNER JOIN horarios h ON h.id_horario = u.id_horario
             WHERE r.nombre = 'medico'
               AND u.activo = 1
               AND h.activo = 1
             ORDER BY u.nombre ASC"
        );
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        $stmt = null;
        return $resultado;
    }

    // Obtener horas ocupadas de un médico en una fecha, con duración de cada cita
    static public function mdlHorasOcupadasMedico($id_medico, $fecha) {
        $stmt = Conexion::conectar()->prepare(
            "SELECT c.hora, tc.tiempo
             FROM citas c
             INNER JOIN tipo_cita tc ON tc.id_tipo_cita = c.id_tipo_cita
             WHERE c.id_medico = :id_medico
               AND c.fecha     = :fecha
               AND c.activo    = 1
               AND c.estado   != 'cancelada'
             ORDER BY c.hora ASC"
        );
        $stmt->bindParam(":id_medico", $id_medico, PDO::PARAM_INT);
        $stmt->bindParam(":fecha",     $fecha,      PDO::PARAM_STR);
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
                (id_paciente, id_recepcionista_registra, id_recepcionista_asigna, id_medico, id_tipo_cita, fecha, hora, estado, activo) 
             VALUES 
                (:id_paciente, :id_recepcionista_registra, :id_recepcionista_asigna, :id_medico, :id_tipo_cita, :fecha, :hora, 'pendiente', 1)"
        );
        $stmt->bindParam(":id_paciente",                $datos["id_paciente"],               PDO::PARAM_INT);
        $stmt->bindParam(":id_recepcionista_registra",  $datos["id_recepcionista_registra"],  PDO::PARAM_INT);
        $stmt->bindValue(":id_recepcionista_asigna",    $datos["id_recepcionista_asigna"],    $datos["id_recepcionista_asigna"] === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(":id_medico",                  $datos["id_medico"],                  $datos["id_medico"]               === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(":id_tipo_cita",               $datos["id_tipo_cita"],               $datos["id_tipo_cita"]            === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindParam(":fecha",                      $datos["fecha"],                      PDO::PARAM_STR);
        $stmt->bindParam(":hora",                       $datos["hora"],                       PDO::PARAM_STR);
        return $stmt->execute() ? "ok" : "error";
    }
}
?>