<?php
require_once __DIR__ . "/ReporteBasePDF.php";

/**
 * Sirve para los 5 reportes de citas: entre fechas, por médico, atendidas,
 * canceladas y pendientes. Lo único que cambia entre ellos es el título y las
 * filas que le pasa el controlador.
 */
class ReporteCitasPDF extends ReporteBasePDF {

    private $ordenEstados = ['Pendiente', 'Vencida', 'Atendida', 'Cancelada', 'Reprogramada'];

    // Una cita pendiente cuya fecha ya pasó se muestra como "Vencida"
    private function etiquetaEstado($estado, $fechaHora) {
        if ($estado === 'pendiente' && strtotime($fechaHora) < time()) {
            return 'Vencida';
        }
        return ucfirst($estado);
    }

    // $modoMedico    → true en los reportes "por médico"; false en "entre fechas"
    // $nombreMedico  → null con "todos los médicos" (se agrupa por médico)
    public function generarReporteCitas($titulo, $fechaInicio, $fechaFin, $modoMedico, $nombreMedico, array $citas) {
        $lineas = ['Del ' . date('d/m/Y', strtotime($fechaInicio)) . ' al ' . date('d/m/Y', strtotime($fechaFin))];
        if ($modoMedico) {
            $lineas[] = ($nombreMedico !== null) ? 'Médico: ' . $nombreMedico : 'Todos los médicos';
        }

        $this->iniciar($titulo, $lineas, $titulo . ' ' . $fechaInicio . ' a ' . $fechaFin);

        $agrupar   = ($modoMedico && $nombreMedico === null);
        $conMedico = !$modoMedico; // en "entre fechas" el médico es una columna más

        if ($conMedico) {
            $this->encabezadoTabla([
                ['Fecha/Hora', 28], ['Paciente', 48], ['CI', 22],
                ['Tipo', 24], ['Médico', 34], ['Estado', 24]
            ]);
            $alineaciones = ['C', 'L', 'C', 'L', 'L', 'C'];
        } else {
            $this->encabezadoTabla([
                ['Fecha/Hora', 30], ['Paciente', 62], ['CI', 26],
                ['Tipo', 32], ['Estado', 30]
            ]);
            $alineaciones = ['C', 'L', 'C', 'L', 'C'];
        }

        if (empty($citas)) {
            $this->mensajeVacio('No se encontraron citas en este rango.');
            return;
        }

        // Conteos para los títulos de grupo y los resúmenes
        $porMedico = [];
        $nombres   = [];
        $porEstado = [];
        foreach ($citas as $c) {
            $m = $c['id_medico'];
            $porMedico[$m] = ($porMedico[$m] ?? 0) + 1;
            $nombres[$m]   = $c['medico'];
            $etiqueta = $this->etiquetaEstado($c['estado'], $c['fecha_hora']);
            $porEstado[$etiqueta] = ($porEstado[$etiqueta] ?? 0) + 1;
        }

        $medicoActual = null;
        $fill = false;
        foreach ($citas as $c) {
            if ($agrupar && $c['id_medico'] != $medicoActual) {
                $medicoActual = $c['id_medico'];
                $this->filaGrupo('Médico: ' . $c['medico'] . ' - ' . $porMedico[$medicoActual] . ' cita(s)');
                $fill = false;
            }
            $fill = !$fill;

            $valores = [
                date('d/m/Y H:i', strtotime($c['fecha_hora'])),
                $c['paciente'],
                $c['ci'],
                $c['tipo_cita']
            ];
            if ($conMedico) {
                $valores[] = $c['medico'];
            }
            $valores[] = $this->etiquetaEstado($c['estado'], $c['fecha_hora']);

            $this->filaTabla($valores, $alineaciones, $fill);
        }

        // Resumen por estado, en un orden fijo y solo con los estados presentes
        $resumenEstado = [];
        foreach ($this->ordenEstados as $estado) {
            if (!empty($porEstado[$estado])) {
                $resumenEstado[] = [$estado, $porEstado[$estado]];
            }
        }
        $this->tablaResumen('Resumen por estado', [['Estado', 130], ['Cantidad', 50]], $resumenEstado, 'TOTAL GENERAL');

        if ($agrupar) {
            $resumenMedico = [];
            foreach ($porMedico as $m => $n) {
                $resumenMedico[] = [$nombres[$m], $n];
            }
            $this->tablaResumen('Resumen por médico', [['Médico', 130], ['Citas', 50]], $resumenMedico, 'TOTAL GENERAL');
        }

        if (!empty($porEstado['Vencida'])) {
            $this->Ln(3);
            $this->SetFont('helvetica', 'I', 8);
            $this->Cell(0, 5, 'Vencida: cita pendiente cuya fecha y hora ya pasaron sin haber sido atendida.', 0, 1, 'L');
        }
    }
}