<?php
require_once __DIR__ . "/ReporteBasePDF.php";

class ReporteCitasPDF extends ReporteBasePDF {

    // Orden en que aparecen los estados en el resumen
    const ESTADOS = ['Pendiente', 'Vencida', 'Atendida', 'Reprogramada', 'Cancelada'];

    // La BD guarda: pendiente | atendida | cancelada | reprogramada.
    // "Vencida" = cita pendiente cuya fecha/hora ya pasó.
    private function estadoMostrado($cita) {
        $estado = strtolower($cita["estado"]);
        if ($estado === 'pendiente' && strtotime($cita["fecha_hora"]) < time()) {
            return 'Vencida';
        }
        return ucfirst($estado);
    }

    /**
     * $modoMedico    false → reporte general (columna Médico en la tabla)
     *                true  → reporte por médico (Médico en el encabezado; si son
     *                        todos, las citas se agrupan por médico)
     * $nombreMedico  null = todos los médicos
     */
    public function generarReporteCitas($titulo, $fechaInicio, $fechaFin, $modoMedico, $nombreMedico, array $citas) {
        $info = [[['Fecha inicio', $this->fmtFecha($fechaInicio)], ['Fecha final', $this->fmtFecha($fechaFin)]]];
        if ($modoMedico) {
            $info[] = $this->infoMedico($nombreMedico);
        }
        $this->iniciarReporte($titulo, $info, $titulo . ' - ' . $fechaInicio . ' a ' . $fechaFin);

        // ── Resumen por estado (solo estados con citas) ────────────────────
        $conteo = array_fill_keys(self::ESTADOS, 0);
        foreach ($citas as $c) {
            $e = $this->estadoMostrado($c);
            $conteo[$e] = isset($conteo[$e]) ? $conteo[$e] + 1 : 1;
        }

        $this->tituloSeccion('Resumen por estado');
        $filas = [];
        foreach ($conteo as $estado => $n) {
            if ($n > 0) $filas[] = [$estado, $n];
        }
        $colsResumen = [['Estado', 90], ['Cantidad de citas', 90]];
        if (empty($filas)) {
            $this->encabezadoTabla($colsResumen);
            $this->filaMensaje('No se registraron citas en este rango de fechas.');
        } else {
            $filas[] = ['negrita' => true, 'celdas' => ['Total de citas', count($citas)]];
            $this->tabla($colsResumen, $filas);
        }
        $this->Ln(6);

        // ── Detalle de citas ───────────────────────────────────────────────
        $this->tituloSeccion('Detalle de citas');

        // Con médico en el encabezado o agrupado, la columna Médico sobra
        $conColumnaMedico = !$modoMedico;
        $cols = $conColumnaMedico
            ? [['Fecha/Hora', 42], ['Paciente', 35], ['C.I.', 22], ['Tipo', 20], ['Médico', 37], ['Estado', 24]]
            : [['Fecha/Hora', 42], ['Paciente', 50], ['C.I.', 26], ['Tipo', 36], ['Estado', 26]];

        if (empty($citas)) {
            $this->encabezadoTabla($cols);
            $this->filaMensaje('No se registraron citas en este rango de fechas.');
            return;
        }

        $fila = function ($c) use ($conColumnaMedico) {
            $celdas = [$this->fmtFechaHora($c["fecha_hora"]), $c["paciente"], $c["ci"], $c["tipo_cita"]];
            if ($conColumnaMedico) $celdas[] = $c["medico"];
            $celdas[] = $this->estadoMostrado($c);
            return $celdas;
        };

        $filas = [];
        if ($modoMedico && !$nombreMedico) {
            foreach ($this->agruparPorMedico($citas) as $g) {
                $n = count($g['filas']);
                $filas[] = ['banda' => $g['nombre'] . '  (' . $n . ($n === 1 ? ' cita)' : ' citas)')];
                foreach ($g['filas'] as $c) $filas[] = $fila($c);
            }
        } else {
            foreach ($citas as $c) $filas[] = $fila($c);
        }
        $this->tabla($cols, $filas, 9);
    }
}