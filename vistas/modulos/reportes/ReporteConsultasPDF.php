<?php
require_once __DIR__ . "/ReporteBasePDF.php";

class ReporteConsultasPDF extends ReporteBasePDF {

    // $nombreMedico = null → todos los médicos (consultas agrupadas por médico)
    public function generarReporteConsultas($fechaInicio, $fechaFin, $nombreMedico, array $consultas) {
        $this->iniciarReporte(
            'Reporte de Consultas por Médico',
            [
                [['Fecha inicio', $this->fmtFecha($fechaInicio)], ['Fecha final', $this->fmtFecha($fechaFin)]],
                $this->infoMedico($nombreMedico)
            ],
            'Reporte de Consultas - ' . $fechaInicio . ' a ' . $fechaFin
        );

        $grupos = $this->agruparPorMedico($consultas);

        // ── Resumen por médico ─────────────────────────────────────────────
        $this->tituloSeccion('Resumen por médico');
        $colsResumen = [['Médico', 110], ['Cantidad de consultas', 70]];
        if (empty($grupos)) {
            $this->encabezadoTabla($colsResumen);
            $this->filaMensaje('No se registraron consultas en este rango de fechas.');
        } else {
            $filas = [];
            foreach ($grupos as $g) $filas[] = [$g['nombre'], count($g['filas'])];
            $filas[] = ['negrita' => true, 'celdas' => ['Total de consultas', count($consultas)]];
            $this->tabla($colsResumen, $filas);
        }
        $this->Ln(6);

        // ── Detalle de consultas ───────────────────────────────────────────
        $this->tituloSeccion('Detalle de consultas');
        $cols = [['Fecha/Hora', 36], ['Paciente', 46], ['C.I.', 24], ['Motivo', 74]];

        if (empty($consultas)) {
            $this->encabezadoTabla($cols);
            $this->filaMensaje('No se registraron consultas en este rango de fechas.');
            return;
        }

        $filas = [];
        foreach ($grupos as $g) {
            if (!$nombreMedico) {
                $n = count($g['filas']);
                $filas[] = ['banda' => $g['nombre'] . '  (' . $n . ($n === 1 ? ' consulta)' : ' consultas)')];
            }
            foreach ($g['filas'] as $c) {
                $filas[] = [$this->fmtFechaHora($c["fecha_atencion"]), $c["paciente"], $c["ci"], trim($c["motivo"])];
            }
        }
        $this->tabla($cols, $filas, 9);
    }
}