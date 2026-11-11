<?php
require_once __DIR__ . '/../../../vendor/autoload.php';

class HistoriaClinicaPDF extends TCPDF {
    private $numeroHistoria = 1;
    private $idConsulta = null;
    private $paginaInicial = 1;

    public function Header() {
        $this->SetTextColor(53, 65, 86);
        $this->SetFont('dejavusans', 'B', 13);
        $this->Cell(0, 7, 'Marie Stopes Bolivia', 0, 1, 'L');
        $this->SetFont('dejavusans', '', 10);
        $titulo = 'Historia clínica N.º ' . $this->numeroHistoria;
        $titulo .= $this->idConsulta === null ? ' | Datos del paciente' : ' | Consulta ID ' . $this->idConsulta;
        $this->Cell(0, 6, $titulo, 0, 1, 'L');
        if ($this->getPage() > $this->paginaInicial) {
            $this->SetFont('dejavusans', 'I', 9);
            $this->Cell(0, 5, 'Continuación', 0, 1, 'L');
        }
        $this->SetDrawColor(192, 205, 214);
        $this->Line(16, 32, 194, 32);
    }

    public function Footer() {
        $this->SetY(-14);
        $this->SetFont('dejavusans', '', 8);
        $this->SetTextColor(100, 110, 120);
        $this->Cell(100, 5, 'Historia clínica | Documento confidencial', 0, 0, 'L');
        $this->Cell(0, 5, 'Página ' . $this->getAliasNumPage() . ' / ' . $this->getAliasNbPages(), 0, 0, 'R');
    }

    private function espacio($alto = 20) {
        if ($this->GetY() + $alto > $this->getPageHeight() - 20) $this->AddPage();
    }

    private function tituloSeccion($titulo) {
        $this->espacio();
        $this->SetFont('dejavusans', 'B', 11);
        $this->SetTextColor(34, 91, 105);
        $this->SetFillColor(237, 245, 247);
        $this->MultiCell(0, 8, $titulo, 0, 'L', true, 1);
        $this->Ln(3);
    }

    private function campo($etiqueta, $valor, $vacio = 'No registrado') {
        $this->espacio(17);
        $this->SetTextColor(55, 65, 75);
        $this->SetFont('dejavusans', 'B', 9);
        $this->MultiCell(0, 5, $etiqueta, 0, 'L', false, 1);
        $this->SetFont('dejavusans', '', 10);
        $texto = trim((string) ($valor ?? ''));
        // MultiCell permite continuar textos largos sin reducir la tipografía.
        $this->MultiCell(0, 6, $texto === '' ? $vacio : $texto, 0, 'L', false, 1);
        $this->Ln(3);
    }

    private function fecha($valor, $conHora = false) {
        if (!$valor || substr($valor, 0, 10) === '0000-00-00') return 'No registrada';
        try {
            return (new DateTimeImmutable($valor, new DateTimeZone('America/La_Paz')))->format($conHora ? 'd/m/Y H:i' : 'd/m/Y');
        } catch (Exception $e) { return 'No registrada'; }
    }

    private function nuevaHistoria($numero, $idConsulta = null) {
        $this->numeroHistoria = $numero;
        $this->idConsulta = $idConsulta;
        $this->paginaInicial = $this->getNumPages() + 1;
        $this->AddPage();
    }

    public function generarHistoria(array $historia, DateTimeImmutable $generado) {
        $this->SetCreator('Marie Stopes Bolivia - Sistema Clínico');
        $this->SetTitle('Historia clínica');
        $this->SetMargins(16, 38, 16);
        $this->SetHeaderMargin(10);
        $this->SetAutoPageBreak(true, 20);
        $this->nuevaHistoria(1);
        $paciente = $historia['paciente'];
        $this->tituloSeccion('Datos del paciente');
        $this->campo('Generado el', $generado->setTimezone(new DateTimeZone('America/La_Paz'))->format('d/m/Y H:i:s') . ' (hora de Bolivia)');
        $this->campo('Paciente', trim(($paciente['nombre'] ?? '') . ' ' . ($paciente['apellidos'] ?? '')));
        $this->campo('ID del paciente / Carnet de identidad', $paciente['id_paciente'] . ' / ' . $paciente['ci']);
        $edad = '';
        if (!empty($paciente['fecha_nacimiento']) && $paciente['fecha_nacimiento'] !== '0000-00-00') {
            try {
                $nacimiento = new DateTimeImmutable($paciente['fecha_nacimiento'], new DateTimeZone('America/La_Paz'));
                if ($nacimiento <= $generado) $edad = ' (' . $nacimiento->diff($generado)->y . ' años al generar el documento)';
            } catch (Exception $e) { /* Fecha ausente o inválida: se mantiene sin edad. */ }
        }
        $this->campo('Fecha de nacimiento', $this->fecha($paciente['fecha_nacimiento'] ?? null) . $edad);
        $this->campo('Grupo sanguíneo', $paciente['grupo_sanguineo'] ?? null);
        $this->campo('Teléfono', $paciente['telefono'] ?? null);
        $this->campo('Dirección', $paciente['direccion'] ?? null);
        if (!$historia['consultas']) $this->campo('Consultas', 'Este paciente no tiene consultas registradas.');

        foreach ($historia['consultas'] as $indice => $consulta) {
            $this->nuevaHistoria($indice + 2, $consulta['id_consulta']);
            $this->campo('Fecha y hora de atención', $this->fecha($consulta['fecha_atencion'], true));
            $this->campo('Médico', trim(($consulta['medico_nombre'] ?? '') . ' ' . ($consulta['medico_apellido'] ?? '')));
            if ($consulta['triaje']) {
                $this->tituloSeccion('Triaje y signos vitales');
                $t = $consulta['triaje'];
                $signos = ['tension_arterial' => 'Presión arterial', 'frecuencia_cardiaca' => 'Frecuencia cardiaca (bpm)',
                    'frecuencia_respiratoria' => 'Frecuencia respiratoria (rpm)', 'temperatura' => 'Temperatura (°C)',
                    'saturacion' => 'Saturación (%)', 'peso' => 'Peso (kg)'];
                // Resumen compacto para dejar espacio al contenido clínico y la receta.
                $lineas = [];
                foreach ($signos as $clave => $etiqueta) {
                    $valor = $t[$clave] ?? null;
                    $lineas[] = $etiqueta . ': ' . ($valor === null || $valor === '' ? 'No registrado' : $valor);
                }
                $this->SetFont('dejavusans', '', 10);
                $this->SetTextColor(55, 65, 75);
                $this->MultiCell(0, 6, implode("\n", $lineas), 0, 'L', false, 1);
                $this->Ln(4);
            }
            $this->tituloSeccion('Observaciones médicas');
            $this->campo('Motivo de consulta', $consulta['motivo'], '');
            $this->campo('Diagnóstico', $consulta['observaciones'], '');
            if ($consulta['examenes']) {
                $this->tituloSeccion('Exámenes solicitados');
                foreach ($consulta['examenes'] as $examen) {
                    $this->campo($examen['nombre'], ucfirst($examen['tipo']) . ' | Solicitado el ' . $this->fecha($examen['fecha_solicitud']));
                }
            }
            $tratamientos = array_filter($consulta['tratamientos'], function ($t) {
                return trim((string) ($t['indicaciones'] ?? '')) !== '' || !empty($t['medicamentos']);
            });
            if ($tratamientos) {
                $this->tituloSeccion('Tratamiento / Receta');
                $numeroMedicamento = 0;
                foreach ($tratamientos as $tratamiento) {
                    if (trim((string) ($tratamiento['indicaciones'] ?? '')) !== '') $this->campo('Indicaciones', $tratamiento['indicaciones']);
                    foreach ($tratamiento['medicamentos'] as $medicamento) {
                        $this->espacio(22);
                        $this->campo('Medicamento ' . ++$numeroMedicamento, $medicamento['nombre']);
                        $this->campo('Dosis', $medicamento['dosis']);
                        $this->campo('Frecuencia', $medicamento['frecuencia']);
                        $this->campo('Duración', $medicamento['duracion']);
                    }
                }
            }
        }
    }
}
