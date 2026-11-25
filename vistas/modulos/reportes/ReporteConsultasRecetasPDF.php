<?php
require_once __DIR__ . "/ReporteBasePDF.php";

class ReporteConsultasRecetasPDF extends ReporteBasePDF {

    // $filas = una fila por medicamento recetado. $nombreMedico = null → todos
    public function generarReporteRecetas($fechaInicio, $fechaFin, $nombreMedico, array $filas) {
        $this->iniciarReporte(
            'Reporte de Consultas con Recetas por Médico',
            [
                [['Fecha inicio', $this->fmtFecha($fechaInicio)], ['Fecha final', $this->fmtFecha($fechaFin)]],
                $this->infoMedico($nombreMedico)
            ],
            'Reporte de Consultas con Recetas - ' . $fechaInicio . ' a ' . $fechaFin
        );

        $grupos = $this->agruparPorMedico($filas);

        // ── Resumen por médico ─────────────────────────────────────────────
        $this->tituloSeccion('Resumen por médico');
        $colsResumen = [['Médico', 80], ['Consultas con receta', 50], ['Medicamentos', 50]];
        if (empty($grupos)) {
            $this->encabezadoTabla($colsResumen);
            $this->filaMensaje('No se registraron consultas con receta en este rango de fechas.');
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

        // ── Detalle de recetas ─────────────────────────────────────────────
        $this->tituloSeccion('Detalle de recetas');
        $cols = [['Fecha', 22], ['Paciente', 34], ['C.I.', 20], ['Medicamento', 40], ['Dosis', 20], ['Frecuencia', 24], ['Duración', 20]];

        if (empty($filas)) {
            $this->encabezadoTabla($cols);
            $this->filaMensaje('No se registraron recetas en este rango de fechas.');
            return;
        }

        $detalle = [];
        foreach ($grupos as $g) {
            if (!$nombreMedico) {
                $n = count(array_unique(array_column($g['filas'], 'id_consulta')));
                $detalle[] = ['banda' => $g['nombre'] . '  (' . $n . ($n === 1 ? ' consulta)' : ' consultas)')];
            }
            foreach ($g['filas'] as $f) {
                $detalle[] = [
                    $this->fmtFecha($f["fecha_atencion"]),
                    $f["paciente"],
                    $f["ci"],
                    $f["medicamento"],
                    $f["dosis"],
                    $f["frecuencia"],
                    $f["duracion"] !== null && $f["duracion"] !== '' ? $f["duracion"] : '-'
                ];
            }
        }
        $this->tabla($cols, $detalle, 8.5);
    }
}