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

    // Citas pendientes de hoy para un médico (dashboard de atención), con bandera de pago
    static public function mdlMostrarCitasPendientesHoy($id_medico) {
        $stmt = Conexion::conectar()->prepare(
            "SELECT c.id_cita, c.fecha_hora,
                    p.nombre, p.apellidos, p.ci,
                    tc.nombre AS tipo_cita,
                    EXISTS(SELECT 1 FROM servicio_prestado sp WHERE sp.id_cita = c.id_cita AND sp.activo = 1) AS pagado
             FROM citas c
             INNER JOIN pacientes p ON c.id_paciente = p.id_paciente
             LEFT  JOIN tipo_cita tc ON c.id_tipo_cita = tc.id_tipo_cita
             WHERE c.id_medico = :id_medico
               AND c.estado = 'pendiente'
               AND c.activo = 1
               AND DATE(c.fecha_hora) = CURDATE()
             ORDER BY c.fecha_hora ASC"
        );
        $stmt->bindParam(":id_medico", $id_medico, PDO::PARAM_INT);
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        $stmt = null;
        return $resultado;
    }

    // Verifica si una cita ya tiene un pago registrado con al menos un ítem cobrado
    static public function mdlCitaTienePago($id_cita) {
        $stmt = Conexion::conectar()->prepare(
            "SELECT 1 FROM servicio_prestado WHERE id_cita = :id_cita AND activo = 1 AND costo_total > 0 LIMIT 1"
        );
        $stmt->bindParam(":id_cita", $id_cita, PDO::PARAM_INT);
        $stmt->execute();
        $existe = (bool) $stmt->fetchColumn();
        $stmt->closeCursor();
        return $existe;
    }

    // Médicos activos para el panel de selección, incluyendo su horario real
    // (se usa para restringir el FullCalendar a las horas que ese médico atiende)
    static public function mdlMostrarMedicosActivos() {
        $stmt = Conexion::conectar()->prepare(
            "SELECT u.id_usuario, u.nombre, u.apellido,
                    h.hora_inicio, h.hora_fin, h.horaI_sabado, h.horaF_sabado
             FROM usuarios u
             INNER JOIN roles r ON r.id_rol = u.id_rol
             LEFT  JOIN horarios h ON h.id_horario = u.id_horario AND h.activo = 1
             WHERE r.nombre = 'medico'
               AND u.activo = 1
             ORDER BY u.nombre ASC"
        );
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        $stmt = null;
        return $resultado;
    }

    // Horario real de un médico específico (para validar en el backend
    // que la cita se está creando/reprogramando dentro de su horario)
    static public function mdlObtenerHorarioMedico($id_medico) {
        $stmt = Conexion::conectar()->prepare(
            "SELECT h.hora_inicio, h.hora_fin, h.horaI_sabado, h.horaF_sabado
             FROM usuarios u
             INNER JOIN horarios h ON h.id_horario = u.id_horario
             WHERE u.id_usuario = :id_medico AND u.activo = 1 AND h.activo = 1"
        );
        $stmt->bindParam(":id_medico", $id_medico, PDO::PARAM_INT);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        $stmt = null;
        return $resultado;
    }

    // Citas activas para FullCalendar, opcionalmente filtradas por médico
    static public function mdlMostrarCitasCalendario($id_medico = null) {
        $sql = "SELECT c.id_cita, c.fecha_hora, c.estado,
                       p.nombre AS pac_nombre, p.apellidos AS pac_apellidos, p.ci AS pac_ci,
                       p.telefono AS pac_telefono,
                       CONCAT(m.nombre, ' ', m.apellido) AS medico,
                       CONCAT(r.nombre, ' ', r.apellido) AS recepcionista_registra,
                       tc.nombre AS tipo_cita,
                       EXISTS(SELECT 1 FROM servicio_prestado sp WHERE sp.id_cita = c.id_cita AND sp.activo = 1 AND sp.costo_total > 0) AS pagado
                FROM citas c
                INNER JOIN pacientes p ON c.id_paciente = p.id_paciente
                LEFT  JOIN usuarios m    ON c.id_medico = m.id_usuario
                LEFT  JOIN usuarios r    ON c.id_recepcionista = r.id_usuario
                LEFT  JOIN tipo_cita tc  ON c.id_tipo_cita = tc.id_tipo_cita
                WHERE c.activo = 1 AND c.estado != 'reprogramada'";
        if ($id_medico !== null && is_numeric($id_medico)) {
            $sql .= " AND c.id_medico = " . (int)$id_medico;
        }
        $sql .= " ORDER BY c.fecha_hora ASC";

        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        $stmt = null;
        return $resultado;
    }

    // Obtener médicos con horario activo en esa fecha (usado por Reprogramar)
    // Nota: antes recibía $id_tipo_cita pero nunca se usaba en la consulta — se quitó.
    static public function mdlMostrarMedicosPorHorario($fecha) {
        $diaSemana = date('N', strtotime($fecha)); // 1=lun ... 6=sab
        $esSabado  = ($diaSemana == 6);

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

    // Horas ocupadas de un médico en una fecha, con duración de cada cita
    static public function mdlHorasOcupadasMedico($id_medico, $fecha, $excluir_cita = null) {
        $sql = "SELECT TIME(c.fecha_hora) AS hora, tc.tiempo
                FROM citas c
                INNER JOIN tipo_cita tc ON tc.id_tipo_cita = c.id_tipo_cita
                WHERE c.id_medico = :id_medico
                  AND DATE(c.fecha_hora) = :fecha
                  AND c.activo = 1
                  AND c.estado NOT IN ('cancelada', 'reprogramada')";
        if ($excluir_cita !== null && is_numeric($excluir_cita)) {
            $sql .= " AND c.id_cita != " . (int)$excluir_cita;
        }
        $sql .= " ORDER BY c.fecha_hora ASC";

        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->bindParam(":id_medico", $id_medico, PDO::PARAM_INT);
        $stmt->bindParam(":fecha",     $fecha,      PDO::PARAM_STR);
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        $stmt = null;
        return $resultado;
    }

    // Obtener una cita por id (para poblar el modal de reprogramar)
    static public function mdlObtenerCita($id_cita) {
        $stmt = Conexion::conectar()->prepare(
            "SELECT c.*, TIME(c.fecha_hora) AS hora, DATE(c.fecha_hora) AS fecha,
                    p.nombre AS pac_nombre, p.apellidos AS pac_apellidos, p.ci AS pac_ci,
                    tc.tiempo AS tipo_tiempo
             FROM citas c
             INNER JOIN pacientes p  ON c.id_paciente  = p.id_paciente
             INNER JOIN tipo_cita tc ON c.id_tipo_cita = tc.id_tipo_cita
             WHERE c.id_cita = :id_cita AND c.activo = 1"
        );
        $stmt->bindParam(":id_cita", $id_cita, PDO::PARAM_INT);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        $stmt = null;
        return $resultado;
    }

    // Marcar cita original como reprogramada (estado, ya no un flag aparte)
    static public function mdlMarcarReprogramada($id_cita) {
        $stmt = Conexion::conectar()->prepare(
            "UPDATE citas SET estado = 'reprogramada' WHERE id_cita = :id_cita"
        );
        $stmt->bindParam(":id_cita", $id_cita, PDO::PARAM_INT);
        return $stmt->execute() ? "ok" : "error";
    }

    // Crear nueva cita como reprogramación
    static public function mdlReprogramarCita($datos) {
        $fecha_hora = $datos["fecha"] . " " . $datos["hora"] . ":00";
        $conn = Conexion::conectar();
        $stmt = $conn->prepare(
            "INSERT INTO citas
                (id_paciente, id_recepcionista, id_medico, id_tipo_cita, fecha_hora, estado, id_cita_original, activo)
             VALUES
                (:id_paciente, :id_recepcionista, :id_medico, :id_tipo_cita, :fecha_hora, 'pendiente', :id_cita_original, 1)"
        );
        $stmt->bindParam(":id_paciente",      $datos["id_paciente"],      PDO::PARAM_INT);
        $stmt->bindParam(":id_recepcionista", $datos["id_recepcionista"], PDO::PARAM_INT);
        $stmt->bindParam(":id_medico",        $datos["id_medico"],        PDO::PARAM_INT);
        $stmt->bindParam(":id_tipo_cita",     $datos["id_tipo_cita"],     PDO::PARAM_INT);
        $stmt->bindParam(":fecha_hora",       $fecha_hora,                PDO::PARAM_STR);
        $stmt->bindParam(":id_cita_original", $datos["id_cita_original"], PDO::PARAM_INT);
        return $stmt->execute() ? "ok" : "error";
    }

    // Registrar nueva cita
    static public function mdlCrearCita($datos) {
        $fecha_hora = $datos["fecha"] . " " . $datos["hora"] . ":00";
        $stmt = Conexion::conectar()->prepare(
            "INSERT INTO citas
                (id_paciente, id_recepcionista, id_medico, id_tipo_cita, fecha_hora, estado, activo)
             VALUES
                (:id_paciente, :id_recepcionista, :id_medico, :id_tipo_cita, :fecha_hora, 'pendiente', 1)"
        );
        $stmt->bindParam(":id_paciente",      $datos["id_paciente"],      PDO::PARAM_INT);
        $stmt->bindParam(":id_recepcionista", $datos["id_recepcionista"], PDO::PARAM_INT);
        $stmt->bindParam(":id_medico",        $datos["id_medico"],        PDO::PARAM_INT);
        $stmt->bindParam(":id_tipo_cita",     $datos["id_tipo_cita"],     PDO::PARAM_INT);
        $stmt->bindParam(":fecha_hora",       $fecha_hora,                PDO::PARAM_STR);
        return $stmt->execute() ? "ok" : "error";
    }

    // Marcar cita como atendida (al finalizar la consulta)
    static public function mdlMarcarAtendida($id_cita) {
        $stmt = Conexion::conectar()->prepare(
            "UPDATE citas SET estado = 'atendida' WHERE id_cita = :id_cita AND activo = 1"
        );
        $stmt->bindParam(":id_cita", $id_cita, PDO::PARAM_INT);
        return $stmt->execute() ? "ok" : "error";
    }

    // Cancelar cita (borrado lógico: activo = 0, estado = 'cancelada')
    static public function mdlCancelarCita($id_cita) {
        $stmt = Conexion::conectar()->prepare(
            "UPDATE citas 
             SET activo = 0, estado = 'cancelada' 
             WHERE id_cita = :id_cita AND activo = 1 AND estado = 'pendiente'"
        );
        $stmt->bindParam(":id_cita", $id_cita, PDO::PARAM_INT);
        $stmt->execute();
        $afectadas = $stmt->rowCount();
        $stmt->closeCursor();
        $stmt = null;
        return $afectadas > 0 ? "ok" : "error";
    }
}