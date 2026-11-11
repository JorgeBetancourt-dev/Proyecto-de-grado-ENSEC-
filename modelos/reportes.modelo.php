<?php
require_once "conexion.php";

class ModeloReportes {

    // ── Reporte de pagos del día (balance de caja) ──────────────────────────

    // Detalle de cada pago del día, agrupado luego por método en el PDF
    static public function mdlDetallePagosPorFecha($fecha) {
        $stmt = Conexion::conectar()->prepare(
            "SELECT
                sp.id_servicio_prestado,
                sp.fecha,
                sp.metodo_pago,
                sp.costo_total,
                COALESCE(
                    CONCAT(p.nombre, ' ', p.apellidos),
                    CONCAT(c.nombre, ' ', c.apellidos),
                    'Sin registrar'
                ) AS cliente
             FROM servicio_prestado sp
             LEFT JOIN pacientes p ON p.id_paciente = sp.id_paciente
             LEFT JOIN clientes  c ON c.id_cliente  = sp.id_cliente
             WHERE DATE(sp.fecha) = :fecha
               AND sp.activo = 1
             ORDER BY sp.metodo_pago, sp.fecha"
        );
        $stmt->bindParam(":fecha", $fecha, PDO::PARAM_STR);
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $resultado;
    }

    // Subtotal por método de pago + total general del día (WITH ROLLUP)
    static public function mdlResumenPagosPorFecha($fecha) {
        $stmt = Conexion::conectar()->prepare(
            "SELECT
                COALESCE(metodo_pago, 'TOTAL GENERAL') AS metodo_pago,
                COUNT(*) AS cantidad_pagos,
                SUM(costo_total) AS total
             FROM servicio_prestado
             WHERE DATE(fecha) = :fecha
               AND activo = 1
             GROUP BY metodo_pago WITH ROLLUP"
        );
        $stmt->bindParam(":fecha", $fecha, PDO::PARAM_STR);
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $resultado;
    }


        // ── Reporte de pagos entre fechas ────────────────────────────────────────

    static public function mdlDetallePagosPorRango($fechaInicio, $fechaFin) {
        $stmt = Conexion::conectar()->prepare(
            "SELECT
                sp.id_servicio_prestado,
                sp.fecha,
                sp.metodo_pago,
                sp.costo_total,
                COALESCE(
                    CONCAT(p.nombre, ' ', p.apellidos),
                    CONCAT(c.nombre, ' ', c.apellidos),
                    'Sin registrar'
                ) AS cliente
             FROM servicio_prestado sp
             LEFT JOIN pacientes p ON p.id_paciente = sp.id_paciente
             LEFT JOIN clientes  c ON c.id_cliente  = sp.id_cliente
             WHERE DATE(sp.fecha) BETWEEN :fecha_inicio AND :fecha_fin
               AND sp.activo = 1
             ORDER BY sp.fecha, sp.metodo_pago"
        );
        $stmt->bindParam(":fecha_inicio", $fechaInicio, PDO::PARAM_STR);
        $stmt->bindParam(":fecha_fin",    $fechaFin,    PDO::PARAM_STR);
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $resultado;
    }

    static public function mdlResumenPagosPorRango($fechaInicio, $fechaFin) {
        $stmt = Conexion::conectar()->prepare(
            "SELECT
                COALESCE(metodo_pago, 'TOTAL GENERAL') AS metodo_pago,
                COUNT(*) AS cantidad_pagos,
                SUM(costo_total) AS total
             FROM servicio_prestado
             WHERE DATE(fecha) BETWEEN :fecha_inicio AND :fecha_fin
               AND activo = 1
             GROUP BY metodo_pago WITH ROLLUP"
        );
        $stmt->bindParam(":fecha_inicio", $fechaInicio, PDO::PARAM_STR);
        $stmt->bindParam(":fecha_fin",    $fechaFin,    PDO::PARAM_STR);
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $resultado;
    }

    // Total por día dentro del rango (útil para ver la tendencia día a día)
    static public function mdlTotalesDiariosPorRango($fechaInicio, $fechaFin) {
        $stmt = Conexion::conectar()->prepare(
            "SELECT DATE(fecha) AS dia, COUNT(*) AS cantidad_pagos, SUM(costo_total) AS total
             FROM servicio_prestado
             WHERE DATE(fecha) BETWEEN :fecha_inicio AND :fecha_fin
               AND activo = 1
             GROUP BY DATE(fecha)
             ORDER BY DATE(fecha)"
        );
        $stmt->bindParam(":fecha_inicio", $fechaInicio, PDO::PARAM_STR);
        $stmt->bindParam(":fecha_fin",    $fechaFin,    PDO::PARAM_STR);
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $resultado;
    }

        // ── Reporte de pagos por fecha + método específico ────────────────────────

    static public function mdlDetallePagosPorRangoYMetodo($fechaInicio, $fechaFin, $metodoPago) {
        $stmt = Conexion::conectar()->prepare(
            "SELECT
                sp.id_servicio_prestado,
                sp.fecha,
                sp.metodo_pago,
                sp.costo_total,
                COALESCE(
                    CONCAT(p.nombre, ' ', p.apellidos),
                    CONCAT(c.nombre, ' ', c.apellidos),
                    'Sin registrar'
                ) AS cliente
             FROM servicio_prestado sp
             LEFT JOIN pacientes p ON p.id_paciente = sp.id_paciente
             LEFT JOIN clientes  c ON c.id_cliente  = sp.id_cliente
             WHERE DATE(sp.fecha) BETWEEN :fecha_inicio AND :fecha_fin
               AND sp.metodo_pago = :metodo_pago
               AND sp.activo = 1
             ORDER BY sp.fecha"
        );
        $stmt->bindParam(":fecha_inicio", $fechaInicio, PDO::PARAM_STR);
        $stmt->bindParam(":fecha_fin",    $fechaFin,    PDO::PARAM_STR);
        $stmt->bindParam(":metodo_pago",  $metodoPago,  PDO::PARAM_STR);
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $resultado;
    }

    static public function mdlTotalesDiariosPorRangoYMetodo($fechaInicio, $fechaFin, $metodoPago) {
        $stmt = Conexion::conectar()->prepare(
            "SELECT DATE(fecha) AS dia, COUNT(*) AS cantidad_pagos, SUM(costo_total) AS total
             FROM servicio_prestado
             WHERE DATE(fecha) BETWEEN :fecha_inicio AND :fecha_fin
               AND metodo_pago = :metodo_pago
               AND activo = 1
             GROUP BY DATE(fecha)
             ORDER BY DATE(fecha)"
        );
        $stmt->bindParam(":fecha_inicio", $fechaInicio, PDO::PARAM_STR);
        $stmt->bindParam(":fecha_fin",    $fechaFin,    PDO::PARAM_STR);
        $stmt->bindParam(":metodo_pago",  $metodoPago,  PDO::PARAM_STR);
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $resultado;
    }

    // Total general (una sola fila) para el método y rango elegidos
    static public function mdlTotalGeneralPorRangoYMetodo($fechaInicio, $fechaFin, $metodoPago) {
        $stmt = Conexion::conectar()->prepare(
            "SELECT COUNT(*) AS cantidad_pagos, COALESCE(SUM(costo_total), 0) AS total
             FROM servicio_prestado
             WHERE DATE(fecha) BETWEEN :fecha_inicio AND :fecha_fin
               AND metodo_pago = :metodo_pago
               AND activo = 1"
        );
        $stmt->bindParam(":fecha_inicio", $fechaInicio, PDO::PARAM_STR);
        $stmt->bindParam(":fecha_fin",    $fechaFin,    PDO::PARAM_STR);
        $stmt->bindParam(":metodo_pago",  $metodoPago,  PDO::PARAM_STR);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $resultado;
    }

    // ══════════════════════════════════════════════════════════════════════
    //  Filtros compartidos: médicos
    // ══════════════════════════════════════════════════════════════════════

    // Médicos activos para el select de los modales
    static public function mdlMedicosActivos() {
        $stmt = Conexion::conectar()->prepare(
            "SELECT u.id_usuario, u.nombre, u.apellido
             FROM usuarios u
             INNER JOIN roles r ON r.id_rol = u.id_rol
             WHERE r.nombre IN ('Medico', 'Médico')
               AND u.activo = 1
             ORDER BY u.nombre, u.apellido"
        );
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $resultado;
    }

    // Nombre completo de un médico (false si no existe)
    static public function mdlNombreMedico($idMedico) {
        $stmt = Conexion::conectar()->prepare(
            "SELECT CONCAT(u.nombre, ' ', u.apellido) AS nombre
             FROM usuarios u
             INNER JOIN roles r ON r.id_rol = u.id_rol
             WHERE u.id_usuario = :id_medico
               AND r.nombre IN ('Medico', 'Médico')"
        );
        $stmt->bindParam(":id_medico", $idMedico, PDO::PARAM_INT);
        $stmt->execute();
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $fila ? $fila["nombre"] : false;
    }

    // ══════════════════════════════════════════════════════════════════════
    //  Reportes de consultas
    //  $idMedico = 0 → todos los médicos
    // ══════════════════════════════════════════════════════════════════════

    // Consultas por médico: una fila por consulta
    static public function mdlConsultasReporte($fechaInicio, $fechaFin, $idMedico) {
        $sql = "SELECT
                    co.id_consulta,
                    co.fecha_atencion,
                    co.motivo,
                    p.ci,
                    COALESCE(NULLIF(TRIM(CONCAT_WS(' ', p.nombre, p.apellidos)), ''), 'Sin registrar') AS paciente,
                    c.id_medico,
                    CONCAT(m.nombre, ' ', m.apellido) AS medico
                FROM consultas co
                INNER JOIN citas     c ON c.id_cita     = co.id_cita
                INNER JOIN pacientes p ON p.id_paciente = c.id_paciente
                INNER JOIN usuarios  m ON m.id_usuario  = c.id_medico
                WHERE co.activo = 1
                  AND DATE(co.fecha_atencion) BETWEEN :fecha_inicio AND :fecha_fin";
        if ($idMedico > 0) {
            $sql .= " AND c.id_medico = :id_medico";
        }
        $sql .= " ORDER BY m.nombre, m.apellido, c.id_medico, co.fecha_atencion";

        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->bindParam(":fecha_inicio", $fechaInicio, PDO::PARAM_STR);
        $stmt->bindParam(":fecha_fin",    $fechaFin,    PDO::PARAM_STR);
        if ($idMedico > 0) {
            $stmt->bindParam(":id_medico", $idMedico, PDO::PARAM_INT);
        }
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $resultado;
    }

    // Consultas con receta: una fila por medicamento recetado
    static public function mdlConsultasRecetasReporte($fechaInicio, $fechaFin, $idMedico) {
        $sql = "SELECT
                    co.id_consulta,
                    co.fecha_atencion,
                    p.ci,
                    COALESCE(NULLIF(TRIM(CONCAT_WS(' ', p.nombre, p.apellidos)), ''), 'Sin registrar') AS paciente,
                    c.id_medico,
                    CONCAT(m.nombre, ' ', m.apellido) AS medico,
                    med.nombre AS medicamento,
                    dt.dosis,
                    dt.frecuencia,
                    dt.duracion
                FROM consultas co
                INNER JOIN citas               c   ON c.id_cita          = co.id_cita
                INNER JOIN pacientes           p   ON p.id_paciente      = c.id_paciente
                INNER JOIN usuarios            m   ON m.id_usuario       = c.id_medico
                INNER JOIN tratamientos        t   ON t.id_consulta      = co.id_consulta AND t.activo  = 1
                INNER JOIN detalle_tratamiento dt  ON dt.id_tratamiento  = t.id_tratamiento AND dt.activo = 1
                INNER JOIN medicamentos        med ON med.id_medicamento = dt.id_medicamento
                WHERE co.activo = 1
                  AND DATE(co.fecha_atencion) BETWEEN :fecha_inicio AND :fecha_fin";
        if ($idMedico > 0) {
            $sql .= " AND c.id_medico = :id_medico";
        }
        $sql .= " ORDER BY m.nombre, m.apellido, c.id_medico, co.fecha_atencion, co.id_consulta, med.nombre";

        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->bindParam(":fecha_inicio", $fechaInicio, PDO::PARAM_STR);
        $stmt->bindParam(":fecha_fin",    $fechaFin,    PDO::PARAM_STR);
        if ($idMedico > 0) {
            $stmt->bindParam(":id_medico", $idMedico, PDO::PARAM_INT);
        }
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $resultado;
    }

    // Consultas con exámenes solicitados: una fila por examen
    // $tipoExamen: 'todos' | 'laboratorio' | 'ecografia'
    static public function mdlConsultasExamenesReporte($fechaInicio, $fechaFin, $idMedico, $tipoExamen) {
        $sql = "SELECT
                    co.id_consulta,
                    co.fecha_atencion,
                    p.ci,
                    COALESCE(NULLIF(TRIM(CONCAT_WS(' ', p.nombre, p.apellidos)), ''), 'Sin registrar') AS paciente,
                    c.id_medico,
                    CONCAT(m.nombre, ' ', m.apellido) AS medico,
                    e.nombre AS examen,
                    e.tipo,
                    de.fecha_solicitud
                FROM consultas co
                INNER JOIN citas           c  ON c.id_cita     = co.id_cita
                INNER JOIN pacientes       p  ON p.id_paciente = c.id_paciente
                INNER JOIN usuarios        m  ON m.id_usuario  = c.id_medico
                INNER JOIN detalle_examen  de ON de.id_consulta = co.id_consulta AND de.activo = 1
                INNER JOIN examenes        e  ON e.id_examen    = de.id_examen
                WHERE co.activo = 1
                  AND DATE(co.fecha_atencion) BETWEEN :fecha_inicio AND :fecha_fin";
        if ($idMedico > 0) {
            $sql .= " AND c.id_medico = :id_medico";
        }
        if ($tipoExamen !== 'todos') {
            $sql .= " AND e.tipo = :tipo_examen";
        }
        $sql .= " ORDER BY m.nombre, m.apellido, c.id_medico, co.fecha_atencion, co.id_consulta, e.nombre";

        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->bindParam(":fecha_inicio", $fechaInicio, PDO::PARAM_STR);
        $stmt->bindParam(":fecha_fin",    $fechaFin,    PDO::PARAM_STR);
        if ($idMedico > 0) {
            $stmt->bindParam(":id_medico", $idMedico, PDO::PARAM_INT);
        }
        if ($tipoExamen !== 'todos') {
            $stmt->bindParam(":tipo_examen", $tipoExamen, PDO::PARAM_STR);
        }
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $resultado;
    }

    // ══════════════════════════════════════════════════════════════════════
    //  Reportes de citas
    //  $idMedico = 0 → todos los médicos | $estado = null → cualquier estado
    //  $agruparPorMedico → ordena por médico (para el reporte agrupado)
    // ══════════════════════════════════════════════════════════════════════

    static public function mdlCitasReporte($fechaInicio, $fechaFin, $idMedico, $estado, $agruparPorMedico) {
        // Las canceladas se incluyen aunque la cancelación haya dejado activo = 0
        $sql = "SELECT
                    c.id_cita,
                    c.fecha_hora,
                    c.estado,
                    p.ci,
                    COALESCE(NULLIF(TRIM(CONCAT_WS(' ', p.nombre, p.apellidos)), ''), 'Sin registrar') AS paciente,
                    tc.nombre AS tipo_cita,
                    c.id_medico,
                    CONCAT(m.nombre, ' ', m.apellido) AS medico
                FROM citas c
                INNER JOIN pacientes p  ON p.id_paciente   = c.id_paciente
                INNER JOIN tipo_cita tc ON tc.id_tipo_cita = c.id_tipo_cita
                INNER JOIN usuarios  m  ON m.id_usuario    = c.id_medico
                WHERE (c.activo = 1 OR c.estado = 'cancelada')
                  AND DATE(c.fecha_hora) BETWEEN :fecha_inicio AND :fecha_fin";
        if ($idMedico > 0) {
            $sql .= " AND c.id_medico = :id_medico";
        }
        if ($estado !== null) {
            $sql .= " AND c.estado = :estado";
        }
        $sql .= $agruparPorMedico
            ? " ORDER BY m.nombre, m.apellido, c.id_medico, c.fecha_hora"
            : " ORDER BY c.fecha_hora";

        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->bindParam(":fecha_inicio", $fechaInicio, PDO::PARAM_STR);
        $stmt->bindParam(":fecha_fin",    $fechaFin,    PDO::PARAM_STR);
        if ($idMedico > 0) {
            $stmt->bindParam(":id_medico", $idMedico, PDO::PARAM_INT);
        }
        if ($estado !== null) {
            $stmt->bindParam(":estado", $estado, PDO::PARAM_STR);
        }
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $resultado;
    }
}