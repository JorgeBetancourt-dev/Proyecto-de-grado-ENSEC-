<?php
require_once __DIR__ . "/ReporteBasePDF.php";

class ReporteConsultasExamenesPDF extends ReporteBasePDF {

    private static $TIPOS = ['todos' => 'Todos', 'laboratorio' => 'Laboratorio', 'ecografia' => 'Ecografía'];

    // $filas = una fila por examen solicitado. $tipoExamen: todos | laboratorio | ecografia
    public function generarReporteExamenes($fechaInicio, $fechaFin, $nombreMedico, $tipoExamen, array $filas) {
        $this->iniciarReporte(
            'Reporte de Consultas con Exámenes Solicitados',
            [
                [['Fecha inicio', $this->fmtFecha($fechaInicio)], ['Fecha final', $this->fmtFecha($fechaFin)]],
                $this->infoMedico($nombreMedico),
                [['Tipo de examen', $this->nombreTipo($tipoExamen)]]
            ],
            'Reporte de Consultas con Exámenes - ' . $fechaInicio . ' a ' . $fechaFin
        );

        $grupos = $this->agruparPorMedico($filas);

        // ── Resumen por médico ─────────────────────────────────────────────
        $this->tituloSeccion('Resumen por médico');
        $colsResumen = [['Médico', 80], ['Consultas con examen', 50], ['Exámenes solicitados', 50]];
        if (empty($grupos)) {
            $this->encabezadoTabla($colsResumen);
            $this->filaMensaje('No se registraron exámenes solicitados en este rango de fechas.');
        } else {
            $resumen = [];
            $totalConsultas = 0;
            foreach ($grupos as $g) {
                $consultas = count(array_unique(array_column($g['filas'], 'id_consulta')));
                $totalConsultas += $consultas;
                $resumen[] = [$g['nombre'], $consultas, count($g['filas'])];
            }
            $resumen[] = ['negrita' => true, 'celdas' => ['Total', $totalConsultas, count($filas)]];
            $this->tabla($colsResumen, $resumen);
        }
        $this->Ln(6);

        // ── Detalle de exámenes ────────────────────────────────────────────
        $this->tituloSeccion('Detalle de exámenes solicitados');
        $cols = [['Fecha', 24], ['Paciente', 40], ['C.I.', 20], ['Examen', 44], ['Tipo', 26], ['Solicitado', 26]];

        if (empty($filas)) {
            $this->encabezadoTabla($cols);
            $this->filaMensaje('No se registraron exámenes solicitados en este rango de fechas.');
            return;
        }

        $detalle = [];
        foreach ($grupos as $g) {
            if (!$nombreMedico) {
                $n = count($g['filas']);
                $detalle[] = ['banda' => $g['nombre'] . '  (' . $n . ($n === 1 ? ' examen)' : ' exámenes)')];
            }
            foreach ($g['filas'] as $f) {
                $detalle[] = [
                    $this->fmtFecha($f["fecha_atencion"]),
                    $f["paciente"],
                    $f["ci"],
                    $f["examen"],
                    $this->nombreTipo($f["tipo"]),
                    $this->fmtFecha($f["fecha_solicitud"])
                ];
            }
        }
        $this->tabla($cols, $detalle, 9);
    }

    private function nombreTipo($tipo) {
        $t = strtolower($tipo);
        return isset(self::$TIPOS[$t]) ? self::$TIPOS[$t] : ucfirst($t);
    }
}