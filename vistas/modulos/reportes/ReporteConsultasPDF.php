<?php
require_once __DIR__ . "/ReporteBasePDF.php";

class ReporteConsultasPDF extends ReporteBasePDF {

    // $nombreMedico = null  → todos los médicos (se agrupa por médico)
    public function generarReporteConsultas($fechaInicio, $fechaFin, $nombreMedico, array $consultas) {
        $lineas = ['Del ' . date('d/m/Y', strtotime($fechaInicio)) . ' al ' . date('d/m/Y', strtotime($fechaFin))];
        $lineas[] = ($nombreMedico !== null) ? 'Médico: ' . $nombreMedico : 'Todos los médicos';

        $this->iniciar(
            'Reporte de Consultas por Médico',
            $lineas,
            'Reporte de Consultas ' . $fechaInicio . ' a ' . $fechaFin
        );

        $agrupar = ($nombreMedico === null);

        $this->encabezadoTabla([
            ['Fecha atención', 30], ['Paciente', 55], ['CI', 25], ['Motivo', 70]
        ]);

        if (empty($consultas)) {
            $this->mensajeVacio('No se registraron consultas en este rango.');
            return;
        }

        // Cantidad de consultas por médico (para el título de cada grupo y el resumen)
        $conteo  = [];
        $nombres = [];
        foreach ($consultas as $f) {
            $id = $f['id_medico'];
            $conteo[$id]  = ($conteo[$id] ?? 0) + 1;
            $nombres[$id] = $f['medico'];
        }

        $medicoActual = null;
        $fill = false;
        foreach ($consultas as $f) {
            if ($agrupar && $f['id_medico'] != $medicoActual) {
                $medicoActual = $f['id_medico'];
                $this->filaGrupo('Médico: ' . $f['medico'] . ' - ' . $conteo[$medicoActual] . ' consulta(s)');
                $fill = false;
            }
            $fill = !$fill;
            $this->filaTabla([
                date('d/m/Y H:i', strtotime($f['fecha_atencion'])),
                $f['paciente'],
                $f['ci'],
                $f['motivo']
            ], ['C', 'L', 'C', 'L'], $fill);
        }

        $filas = [];
        foreach ($conteo as $id => $n) {
            $filas[] = [$nombres[$id], $n];
        }
        $this->tablaResumen('Resumen por médico', [['Médico', 130], ['Consultas', 50]], $filas, 'TOTAL GENERAL');
    }
}