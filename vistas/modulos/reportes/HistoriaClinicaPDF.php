<?php
require_once __DIR__ . '/../../../vendor/autoload.php';

class HistoriaClinicaPDF extends TCPDF {

    // ── Paleta (idéntica a ReportePagosPDF) ────────────────────────────────
    const AZUL       = [0, 159, 227];
    const AZUL_CLARO = [214, 230, 245];
    const NAVY       = [22, 58, 86];
    const GRIS_TEXTO = [60, 60, 60];
    const GRIS_LINEA = [200, 200, 200];
    const GRIS_PIE   = [110, 110, 110];

    // ── Medidas (mm) ───────────────────────────────────────────────────────
    const MARGEN      = 15;
    const ANCHO_UTIL  = 180;
    const ALTO_HEADER = 9.5;   // fila de títulos de la tabla
    const ALTO_FILA   = 9;     // filas de datos
    const TOP_CONTENIDO = 62;  // el contenido empieza debajo de la línea azul del encabezado

    // ── Logo (el PNG trae margen transparente, por eso Y queda en 0) ───────
    const LOGO_X = 10;
    const LOGO_Y = 0;
    const LOGO_W = 70;

    const ZONA = 'America/La_Paz';

    private $tituloDocumento = "Historia\nClínica";
    private $numeroHistoria = 1;
    private $idConsulta = null;
    private $paginaInicial = 1;
    private $nombrePaciente = '';
    private $generado = null;

    // ── Encabezado de cada página (mismo diseño que los reportes) ──────────
    public function Header() {
        $this->setCellPaddings(0, 0, 0, 0);

        $logo = __DIR__ . '/../../assets/img/Logo.png';
        if (is_file($logo)) $this->Image($logo, self::LOGO_X, self::LOGO_Y, self::LOGO_W, 0, 'PNG');

        $xTitulo = self::MARGEN + self::ANCHO_UTIL - 85;
        $this->SetTextColorArray(self::AZUL);
        $this->SetFont('helvetica', 'B', 18);
        $this->SetXY($xTitulo, 21.5);
        $this->MultiCell(85, 9, $this->tituloDocumento, 0, 'R', false, 1);

        // Línea 1: N.º de historia (+ ID de consulta). Línea 2: paciente
        $etq = function ($t) { return '<b style="color:#163A56;">' . $t . '</b>'; };
        $linea1 = $etq('Historia N.º:') . ' ' . (int) $this->numeroHistoria;
        if ($this->idConsulta !== null) {
            $linea1 .= ' | ' . $etq('Consulta ID:') . ' ' . htmlspecialchars((string) $this->idConsulta, ENT_QUOTES, 'UTF-8');
        }
        if ($this->getPage() > $this->paginaInicial) $linea1 .= ' <i>(cont.)</i>';
        $html = $linea1;
        if ($this->nombrePaciente !== '') {
            $html .= '<br>' . $etq('Paciente:') . ' ' . htmlspecialchars($this->nombrePaciente, ENT_QUOTES, 'UTF-8');
        }

        $this->SetFont('helvetica', '', 9);
        $this->SetTextColorArray(self::GRIS_TEXTO);
        $this->writeHTMLCell(85, 5, $xTitulo, $this->GetY() + 3, $html, 0, 1, false, true, 'R');

        $this->SetDrawColorArray(self::AZUL);
        $this->SetLineWidth(0.7);
        $this->Line(self::MARGEN, 56, self::MARGEN + self::ANCHO_UTIL, 56);
        $this->SetTextColor(0, 0, 0);
    }

    // ── Pie de página (mismo diseño que los reportes) ──────────────────────
    public function Footer() {
        $this->setCellPaddings(0, 0, 0, 0);

        $this->SetY(-16);
        $this->SetDrawColorArray(self::GRIS_LINEA);
        $this->SetLineWidth(0.2);
        $this->Line(self::MARGEN, $this->GetY(), self::MARGEN + self::ANCHO_UTIL, $this->GetY());

        $momento = $this->generado ?: new DateTimeImmutable('now', new DateTimeZone(self::ZONA));
        $this->SetY(-14.5);
        $this->SetFont('helvetica', '', 8);
        $this->SetTextColorArray(self::GRIS_PIE);
        $this->Cell(110, 5, 'Generado el ' . $momento->format('d/m/Y H:i') . ' | Documento confidencial', 0, 0, 'L');
        $this->Cell(70, 5, 'Página ' . $this->getAliasNumPage() . ' / ' . $this->getAliasNbPages(), 0, 0, 'R');
    }

    // ── Utilidades de formato ──────────────────────────────────────────────
    private function fecha($valor, $conHora = false) {
        if (!$valor || substr($valor, 0, 10) === '0000-00-00') return 'No registrada';
        try {
            return (new DateTimeImmutable($valor, new DateTimeZone(self::ZONA)))->format($conHora ? 'd/m/Y H:i' : 'd/m/Y');
        } catch (Exception $e) { return 'No registrada'; }
    }

    // 37.00 → "37"   |   37.50 → "37.5"   |   "120/80" → "120/80"
    private function signo($valor, $unidad = '') {
        $v = trim((string) ($valor ?? ''));
        if ($v === '') return '-';
        if (is_numeric($v)) $v = rtrim(rtrim(number_format((float) $v, 2, '.', ''), '0'), '.');
        return $unidad === '' ? $v : $v . ' ' . $unidad;
    }

    private function tipoExamen($tipo) {
        $tipos = ['laboratorio' => 'Laboratorio', 'ecografia' => 'Ecografía'];
        $clave = strtolower(trim((string) $tipo));
        return isset($tipos[$clave]) ? $tipos[$clave] : ucfirst($clave);
    }

    // ── Helpers de diseño ──────────────────────────────────────────────────
    private function asegurarEspacio($alto) {
        if ($this->GetY() + $alto > $this->getPageHeight() - $this->getBreakMargin()) $this->AddPage();
    }

    private function tituloSeccion($texto, $espacioMinimo = 40) {
        $this->asegurarEspacio($espacioMinimo);
        $this->SetFont('helvetica', 'B', 11);
        $this->SetTextColorArray(self::NAVY);
        $this->Cell(0, 7, $texto, 0, 1, 'L');
        $this->Ln(2);
    }

    // $cols = [['Título', ancho], ...]
    private function encabezadoTabla(array $cols, $tam = 10) {
        $this->SetFont('helvetica', 'B', $tam);
        $this->SetFillColorArray(self::AZUL);
        $this->SetTextColor(255, 255, 255);
        $this->SetDrawColor(255, 255, 255);
        $this->SetLineWidth(0.3);

        $ultimo = count($cols) - 1;
        foreach ($cols as $i => $col) {
            $this->Cell($col[1], self::ALTO_HEADER, $col[0], 'LR', $i === $ultimo ? 1 : 0, 'C', true, '', 1, false, 'T', 'M');
        }
    }

    private function filaTabla(array $anchos, array $celdas, $negrita = false) {
        $this->SetFont('helvetica', $negrita ? 'B' : '', 10);
        $this->SetFillColorArray(self::AZUL_CLARO);
        $this->SetTextColorArray(self::GRIS_TEXTO);
        $this->SetDrawColorArray(self::AZUL);
        $this->SetLineWidth(0.35);

        $ultimo = count($celdas) - 1;
        foreach ($celdas as $i => $texto) {
            $this->Cell($anchos[$i], self::ALTO_FILA, (string) $texto, 1, $i === $ultimo ? 1 : 0, 'C', true, '', 1, false, 'T', 'M');
        }
    }

    // Si la fila no cabe en la página, salta y repite el encabezado de la tabla
    private function saltoSiHaceFalta(array $cols, $tam = 10) {
        if ($this->GetY() + self::ALTO_FILA > $this->getPageHeight() - $this->getBreakMargin()) {
            $this->AddPage();
            $this->encabezadoTabla($cols, $tam);
        }
    }

    private function filaMensaje($texto) {
        $this->asegurarEspacio(self::ALTO_FILA);
        $this->SetFont('helvetica', 'I', 10);
        $this->SetFillColorArray(self::AZUL_CLARO);
        $this->SetTextColorArray(self::GRIS_TEXTO);
        $this->SetDrawColorArray(self::AZUL);
        $this->SetLineWidth(0.35);
        $this->Cell(self::ANCHO_UTIL, self::ALTO_FILA, $texto, 1, 1, 'C', true, '', 0, false, 'T', 'M');
    }

    // Fila de datos tipo ficha: etiqueta (azul) + valor (azul claro)
    // $pares = [[etiqueta, valor, anchoEtiqueta, anchoValor], ...]
    private function filaDatos(array $pares) {
        $this->asegurarEspacio(self::ALTO_FILA);
        $ultimo = count($pares) - 1;
        foreach ($pares as $i => $p) {
            $this->SetFont('helvetica', 'B', 9);
            $this->SetFillColorArray(self::AZUL);
            $this->SetTextColor(255, 255, 255);
            $this->SetDrawColor(255, 255, 255);
            $this->SetLineWidth(0.3);
            $this->Cell($p[2], self::ALTO_FILA, $p[0], 'B', 0, 'L', true, '', 1, false, 'T', 'M');

            $valor = trim((string) $p[1]);
            $this->SetFont('helvetica', '', 10);
            $this->SetFillColorArray(self::AZUL_CLARO);
            $this->SetTextColorArray(self::GRIS_TEXTO);
            $this->SetDrawColorArray(self::AZUL);
            $this->SetLineWidth(0.35);
            $this->Cell($p[3], self::ALTO_FILA, $valor === '' ? 'No registrado' : $valor, 1, $i === $ultimo ? 1 : 0, 'L', true, '', 1, false, 'T', 'M');
        }
    }

    // Bloque de texto largo: barra azul con el título + cuadro azul claro que crece según el contenido
    private function bloqueTexto($titulo, $texto, $vacio = 'No registrado') {
        $this->asegurarEspacio(self::ALTO_HEADER + 20);
        $this->encabezadoTabla([[$titulo, self::ANCHO_UTIL]]);

        $texto = trim((string) $texto);
        $this->SetFont('helvetica', $texto === '' ? 'I' : '', 10);
        $this->SetFillColorArray(self::AZUL_CLARO);
        $this->SetTextColorArray(self::GRIS_TEXTO);
        $this->SetDrawColorArray(self::AZUL);
        $this->SetLineWidth(0.35);

        $this->setCellPaddings(3, 2, 3, 2);
        $this->MultiCell(self::ANCHO_UTIL, self::ALTO_FILA, $texto === '' ? $vacio : $texto, 1, 'L', true, 1);
        $this->setCellPaddings(2, 1, 2, 1);
        $this->Ln(4);
    }

    // Cada historia (datos del paciente / cada consulta) empieza en página nueva
    private function nuevaHistoria($numero, $idConsulta = null) {
        $this->numeroHistoria = $numero;
        $this->idConsulta = $idConsulta;
        $this->paginaInicial = $this->getNumPages() + 1;
        $this->AddPage();
    }

    // ── Contenido completo de la historia clínica ──────────────────────────
    public function generarHistoria(array $historia, DateTimeImmutable $generado) {
        $this->generado = $generado->setTimezone(new DateTimeZone(self::ZONA));
        $paciente = $historia['paciente'];
        $this->nombrePaciente = trim(($paciente['nombre'] ?? '') . ' ' . ($paciente['apellidos'] ?? ''));

        $this->SetCreator('Marie Stopes Bolivia - Sistema Clínico');
        $this->SetTitle('Historia clínica' . ($this->nombrePaciente !== '' ? ' - ' . $this->nombrePaciente : ''));
        $this->SetMargins(self::MARGEN, self::TOP_CONTENIDO, self::MARGEN);
        $this->SetAutoPageBreak(true, 20);
        $this->setCellPaddings(2, 1, 2, 1);

        // ── Historia N.º 1: datos del paciente ─────────────────────────────
        $this->nuevaHistoria(1);
        $this->tituloSeccion('Datos del paciente');

        $edad = 'No registrada';
        if (!empty($paciente['fecha_nacimiento']) && $paciente['fecha_nacimiento'] !== '0000-00-00') {
            try {
                $nacimiento = new DateTimeImmutable($paciente['fecha_nacimiento'], new DateTimeZone(self::ZONA));
                if ($nacimiento <= $this->generado) {
                    $anios = $nacimiento->diff($this->generado)->y;
                    $edad = $anios . ($anios === 1 ? ' año' : ' años');
                }
            } catch (Exception $e) { /* Fecha inválida: se queda sin edad. */ }
        }

        $this->filaDatos([['Paciente', $this->nombrePaciente, 40, 140]]);
        $this->filaDatos([['ID del paciente', $paciente['id_paciente'] ?? '', 40, 50], ['Carnet de identidad', $paciente['ci'] ?? '', 40, 50]]);
        $this->filaDatos([['Fecha de nacimiento', $this->fecha($paciente['fecha_nacimiento'] ?? null), 40, 50], ['Edad', $edad, 40, 50]]);
        $this->filaDatos([['Grupo sanguíneo', $paciente['grupo_sanguineo'] ?? '', 40, 50], ['Teléfono', $paciente['telefono'] ?? '', 40, 50]]);
        $this->filaDatos([['Dirección', $paciente['direccion'] ?? '', 40, 140]]);
        $this->filaDatos([['Generado el', $this->generado->format('d/m/Y H:i:s') . ' (hora de Bolivia)', 40, 140]]);
        $this->Ln(6);

        if (empty($historia['consultas'])) {
            $this->tituloSeccion('Consultas', 28);
            $this->filaMensaje('Este paciente no tiene consultas registradas.');
            return;
        }

        // ── Una historia por consulta ──────────────────────────────────────
        foreach ($historia['consultas'] as $indice => $consulta) {
            $this->nuevaHistoria($indice + 2, $consulta['id_consulta']);

            // Datos de la consulta
            $this->tituloSeccion('Datos de la consulta');
            $this->filaDatos([
                ['Fecha y hora', $this->fecha($consulta['fecha_atencion'] ?? null, true), 40, 50],
                ['Consulta ID', $consulta['id_consulta'], 40, 50]
            ]);
            $medico = trim(($consulta['medico_nombre'] ?? '') . ' ' . ($consulta['medico_apellido'] ?? ''));
            $this->filaDatos([['Médico', $medico, 40, 140]]);
            $this->Ln(6);

            // Triaje y signos vitales
            $t = $consulta['triaje'] ?? null;
            if ($t) {
                $this->tituloSeccion('Triaje y signos vitales');
                $colsTriaje = [['Presión arterial', 36], ['Frec. cardiaca', 28], ['Frec. respiratoria', 34],
                               ['Temperatura', 26], ['Saturación', 26], ['Peso', 30]];
                $this->encabezadoTabla($colsTriaje, 9);
                $this->filaTabla(array_column($colsTriaje, 1), [
                    $this->signo($t['tension_arterial'] ?? null, 'mmHg'),
                    $this->signo($t['frecuencia_cardiaca'] ?? null, 'bpm'),
                    $this->signo($t['frecuencia_respiratoria'] ?? null, 'rpm'),
                    $this->signo($t['temperatura'] ?? null, '°C'),
                    $this->signo($t['saturacion'] ?? null, '%'),
                    $this->signo($t['peso'] ?? null, 'kg')
                ]);
                $this->Ln(6);
            }

            // Observaciones médicas
            $this->tituloSeccion('Observaciones médicas');
            $this->bloqueTexto('Motivo de consulta', $consulta['motivo'] ?? '');
            $this->bloqueTexto('Diagnóstico', $consulta['observaciones'] ?? '');
            $this->Ln(2);

            // Exámenes solicitados
            if (!empty($consulta['examenes'])) {
                $this->tituloSeccion('Exámenes solicitados');
                $colsExamen = [['Examen', 90], ['Tipo', 40], ['Fecha de solicitud', 50]];
                $anchosExamen = array_column($colsExamen, 1);
                $this->encabezadoTabla($colsExamen);
                foreach ($consulta['examenes'] as $examen) {
                    $this->saltoSiHaceFalta($colsExamen);
                    $this->filaTabla($anchosExamen, [
                        $examen['nombre'] ?? '',
                        $this->tipoExamen($examen['tipo'] ?? ''),
                        $this->fecha($examen['fecha_solicitud'] ?? null)
                    ]);
                }
                $this->Ln(6);
            }

            // Tratamiento / Receta
            $tratamientos = array_filter($consulta['tratamientos'] ?? [], function ($tr) {
                return trim((string) ($tr['indicaciones'] ?? '')) !== '' || !empty($tr['medicamentos']);
            });
            if ($tratamientos) {
                $this->tituloSeccion('Tratamiento / Receta');
                $medicamentos = [];
                foreach ($tratamientos as $tratamiento) {
                    if (trim((string) ($tratamiento['indicaciones'] ?? '')) !== '') {
                        $this->bloqueTexto('Indicaciones', $tratamiento['indicaciones']);
                    }
                    foreach (($tratamiento['medicamentos'] ?? []) as $m) $medicamentos[] = $m;
                }

                if ($medicamentos) {
                    $colsMed = [['N.º', 14], ['Medicamento', 66], ['Dosis', 34], ['Frecuencia', 36], ['Duración', 30]];
                    $anchosMed = array_column($colsMed, 1);
                    $this->encabezadoTabla($colsMed);
                    $n = 0;
                    foreach ($medicamentos as $m) {
                        $this->saltoSiHaceFalta($colsMed);
                        $this->filaTabla($anchosMed, [
                            ++$n,
                            $m['nombre'] ?? '',
                            $m['dosis'] ?? '',
                            $m['frecuencia'] ?? '',
                            !empty($m['duracion']) ? $m['duracion'] : '-'
                        ]);
                    }
                }
            }
        }
    }
}