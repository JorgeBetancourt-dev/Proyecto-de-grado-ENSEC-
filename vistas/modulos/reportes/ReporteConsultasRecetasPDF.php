<?php
require_once __DIR__ . "/ReporteBasePDF.php";

class ReporteConsultasRecetasPDF extends ReporteBasePDF {

    // $filas = una fila por medicamento recetado (varias filas por consulta)
    // $nombreMedico = null → todos los médicos (se agrupa por médico)
    public function generarReporteRecetas($fechaInicio, $fechaFin, $nombreMedico, array $filas) {
        $lineas = ['Del ' . date('d/m/Y', strtotime($fechaInicio)) . ' al ' . date('d/m/Y', strtotime($fechaFin))];
        $lineas[] = ($nombreMedico !== null) ? 'Médico: ' . $nombreMedico : 'Todos los médicos';

        $this->iniciar(
            'Reporte de Consultas con Recetas',
            $lineas,
            'Reporte de Consultas con Recetas ' . $fechaInicio . ' a ' . $fechaFin
        );

        $agrupar = ($nombreMedico === null);

        $this->encabezadoTabla([
            ['Fecha', 26], ['Paciente', 44], ['Medicamento', 40],
            ['Dosis', 22], ['Frecuencia', 24], ['Duración', 24]
        ]);

        if (empty($filas)) {
            $this->mensajeVacio('No se encontraron consultas con recetas en este rango.');
            return;
        }

        // Conteos por médico: consultas distintas y medicamentos recetados
        $consultasPorMedico    = [];
        $medicamentosPorMedico = [];
        $nombres               = [];
        foreach ($filas as $f) {
            $m = $f['id_medico'];
            $consultasPorMedico[$m][$f['id_consulta']] = true;
            $medicamentosPorMedico[$m] = ($medicamentosPorMedico[$m] ?? 0) + 1;
            $nombres[$m] = $f['medico'];
        }

        $medicoActual   = null;
        $consultaActual = null;
        $fill = false;

        foreach ($filas as $f) {
            if ($agrupar && $f['id_medico'] != $medicoActual) {
                $medicoActual   = $f['id_medico'];
                $consultaActual = null;
                $this->filaGrupo(
                    'Médico: ' . $f['medico'] . ' - ' . count($consultasPorMedico[$medicoActual]) . ' consulta(s), '
                    . $medicamentosPorMedico[$medicoActual] . ' medicamento(s)'
                );
            }

            // La fecha y el paciente solo se muestran en la primera fila de cada consulta
            $esNueva = ($f['id_consulta'] != $consultaActual);
            if ($esNueva) {
                $fill = !$fill;
                $consultaActual = $f['id_consulta'];
            }

            $this->filaTabla([
                $esNueva ? date('d/m/Y', strtotime($f['fecha_atencion'])) : '',
                $esNueva ? $f['paciente'] : '',
                $f['medicamento'],
                $f['dosis'],
                $f['frecuencia'],
                $f['duracion']
            ], ['C', 'L', 'L', 'C', 'C', 'C'], $fill);
        }

        $resumen = [];
        foreach ($consultasPorMedico as $m => $consultas) {
            $resumen[] = [$nombres[$m], count($consultas), $medicamentosPorMedico[$m]];
        }
        $this->tablaResumen(
            'Resumen por médico',
            [['Médico', 90], ['Consultas con receta', 50], ['Medicamentos', 40]],
            $resumen,
            'TOTAL GENERAL'
        );
    }
}