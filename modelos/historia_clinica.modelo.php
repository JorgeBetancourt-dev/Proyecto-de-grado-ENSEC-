<?php
require_once __DIR__ . "/conexion.php";

class ModeloHistoriaClinica {
    private static function filas($conn, $sql, $id_paciente) {
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(":id_paciente", $id_paciente, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    static public function mdlPuedeVerHistoria($id_usuario) {
        $stmt = Conexion::conectar()->prepare(
            "SELECT 1 FROM usuarios u
             INNER JOIN roles r ON r.id_rol = u.id_rol AND r.activo = 1
             INNER JOIN rol_permiso rp ON rp.id_rol = r.id_rol AND rp.activo = 1
             INNER JOIN permisos p ON p.id_permiso = rp.id_permiso AND p.activo = 1
             WHERE u.id_usuario = :id_usuario AND u.activo = 1 AND u.bloqueado = 0
               AND p.modulo = 'panel_paciente' LIMIT 1"
        );
        $stmt->bindValue(":id_usuario", $id_usuario, PDO::PARAM_INT);
        $stmt->execute();
        return (bool) $stmt->fetchColumn();
    }

    static public function mdlObtenerHistoria($id_paciente) {
        $conn = Conexion::conectar();
        $pacientes = self::filas($conn,
            "SELECT * FROM pacientes WHERE id_paciente = :id_paciente AND activo = 1", $id_paciente);
        if (!$pacientes) return null;

        // Consultas guardadas; el estado actual de la cita o del médico no borra su historia.
        $consultas = self::filas($conn,
            "SELECT co.id_consulta, co.motivo, co.observaciones, co.fecha_atencion,
                    ci.id_cita, u.nombre AS medico_nombre, u.apellido AS medico_apellido
             FROM consultas co
             INNER JOIN citas ci ON ci.id_cita = co.id_cita
             LEFT JOIN usuarios u ON u.id_usuario = ci.id_medico
             WHERE ci.id_paciente = :id_paciente AND co.activo = 1
             ORDER BY co.fecha_atencion ASC, co.id_consulta ASC", $id_paciente);
        $porConsulta = [];
        foreach ($consultas as $consulta) {
            $consulta['triaje'] = null;
            $consulta['examenes'] = [];
            $consulta['tratamientos'] = [];
            $porConsulta[$consulta['id_consulta']] = $consulta;
        }
        if (!$porConsulta) return ['paciente' => $pacientes[0], 'consultas' => []];

        // Consultas separadas para no multiplicar filas al combinar exámenes y medicamentos.
        $triajes = self::filas($conn,
            "SELECT t.* FROM triaje t
             INNER JOIN consultas co ON co.id_consulta = t.id_consulta AND co.activo = 1
             INNER JOIN citas ci ON ci.id_cita = co.id_cita
             WHERE ci.id_paciente = :id_paciente AND t.activo = 1", $id_paciente);
        foreach ($triajes as $triaje) $porConsulta[$triaje['id_consulta']]['triaje'] = $triaje;

        $examenes = self::filas($conn,
            "SELECT de.id_consulta, de.fecha_solicitud, e.nombre, e.tipo
             FROM detalle_examen de
             INNER JOIN consultas co ON co.id_consulta = de.id_consulta AND co.activo = 1
             INNER JOIN citas ci ON ci.id_cita = co.id_cita
             INNER JOIN examenes e ON e.id_examen = de.id_examen
             WHERE ci.id_paciente = :id_paciente AND de.activo = 1
             ORDER BY de.id_detalle_examen ASC", $id_paciente);
        foreach ($examenes as $examen) $porConsulta[$examen['id_consulta']]['examenes'][] = $examen;

        $tratamientos = self::filas($conn,
            "SELECT t.id_consulta, t.id_tratamiento, t.indicaciones FROM tratamientos t
             INNER JOIN consultas co ON co.id_consulta = t.id_consulta AND co.activo = 1
             INNER JOIN citas ci ON ci.id_cita = co.id_cita
             WHERE ci.id_paciente = :id_paciente AND t.activo = 1
             ORDER BY t.id_tratamiento ASC", $id_paciente);
        foreach ($tratamientos as $tratamiento) {
            $tratamiento['medicamentos'] = [];
            $porConsulta[$tratamiento['id_consulta']]['tratamientos'][$tratamiento['id_tratamiento']] = $tratamiento;
        }

        $medicamentos = self::filas($conn,
            "SELECT t.id_consulta, t.id_tratamiento, m.nombre, dt.dosis, dt.frecuencia, dt.duracion
             FROM detalle_tratamiento dt
             INNER JOIN tratamientos t ON t.id_tratamiento = dt.id_tratamiento AND t.activo = 1
             INNER JOIN consultas co ON co.id_consulta = t.id_consulta AND co.activo = 1
             INNER JOIN citas ci ON ci.id_cita = co.id_cita
             INNER JOIN medicamentos m ON m.id_medicamento = dt.id_medicamento
             WHERE ci.id_paciente = :id_paciente AND dt.activo = 1
             ORDER BY t.id_tratamiento ASC, dt.id_detalle_tratamiento ASC", $id_paciente);
        foreach ($medicamentos as $medicamento) {
            $porConsulta[$medicamento['id_consulta']]['tratamientos'][$medicamento['id_tratamiento']]['medicamentos'][] = $medicamento;
        }
        return ['paciente' => $pacientes[0], 'consultas' => array_values($porConsulta)];
    }
}
