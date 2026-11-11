<?php
require_once __DIR__ . "/ReporteBasePDF.php";

class ReporteConsultasExamenesPDF extends ReporteBasePDF {

    private $etiquetasTipo = [
        'todos'       => 'Todos',
        'laboratorio' => 'Laboratorio',
        'ecografia'   => 'Ecografía'
    ];

    // $filas = una fila por examen solicitado (varias filas por consulta)
    // $nombreMedico = null → todos los médicos (se agrupa por médico)
    public function generarReporteExamenes($fechaInicio, $fechaFin, $nombreMedico, $tipoExamen, array $filas) {
        $lineas = ['Del ' . date('d/m/Y', strtotime($fechaInicio)) . ' al ' . date('d/m/Y', strtotime($fechaFin))];
        $lineas[] = ($nombreMedico !== null) ? 'Médico: ' . $nombreMedico : 'Todos los médicos';
        $lineas[] = 'Tipo de examen: ' . ($this->etiquetasTipo[$tipoExamen] ?? 'Todos');

        $this->iniciar(
            'Reporte de Consultas con Solicitudes de Exámenes',
            $lineas,
            'Reporte de Consultas con Exámenes ' . $fechaInicio . ' a ' . $fechaFin
        );

        $agrupar = ($nombreMedico === null);

        $this->encabezadoTabla([
            ['Fecha', 26], ['Paciente', 54], ['Examen', 60], ['Tipo', 20], ['Solicitado', 20]
        ]);

        if (empty($filas)) {
            $this->mensajeVacio('No se encontraron consultas con exámenes solicitados en este rango.');
            return;
        }

        // Conteos por médico y por examen
        $consultasPorMedico = [];
        $examenesPorMedico  = [];
        $nombres            = [];
        $porExamen          = [];
        foreach ($filas as $f) {
            $m = $f['id_medico'];
            $consultasPorMedico[$m][$f['id_consulta']] = true;
            $examenesPorMedico[$m] = ($examenesPorMedico[$m] ?? 0) + 1;
            $nombres[$m] = $f['medico'];
            $porExamen[$f['examen']] = ($porExamen[$f['examen']] ?? 0) + 1;
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
                    . $examenesPorMedico[$medicoActual] . ' examen(es)'
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
                $f['examen'],
                $this->etiquetasTipo[$f['tipo']] ?? ucfirst($f['tipo']),
                date('d/m/Y', strtotime($f['fecha_solicitud']))
            ], ['C', 'L', 'L', 'C', 'C'], $fill);
        }

        ksort($porExamen);
        $resumen = [];
        foreach ($porExamen as $nombre => $cantidad) {
            $resumen[] = [$nombre, $cantidad];
        }
        $this->tablaResumen(
            'Resumen por examen solicitado',
            [['Examen', 130], ['Cantidad solicitada', 50]],
            $resumen,
            'TOTAL GENERAL'
        );
    }
}