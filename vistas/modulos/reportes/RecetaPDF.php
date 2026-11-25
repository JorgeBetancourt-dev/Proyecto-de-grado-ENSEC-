<?php
require_once __DIR__ . "/../../../vendor/autoload.php";

class RecetaPDF extends TCPDF {

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

    private $tituloDocumento = "Receta\nMédica";
    private $fechaReceta = null;

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

        if ($this->fechaReceta) {
            $this->SetFont('helvetica', '', 9);
            $this->SetTextColorArray(self::GRIS_TEXTO);
            $html = '<b style="color:#163A56;">Fecha:</b> ' . $this->fecha($this->fechaReceta);
            $this->writeHTMLCell(85, 5, $xTitulo, $this->GetY() + 3, $html, 0, 1, false, true, 'R');
        }

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

        $this->SetY(-14.5);
        $this->SetFont('helvetica', '', 8);
        $this->SetTextColorArray(self::GRIS_PIE);
        $this->Cell(90, 5, 'Generado el ' . $this->ahora()->format('d/m/Y H:i'), 0, 0, 'L');
        $this->Cell(90, 5, 'Página ' . $this->getAliasNumPage() . ' / ' . $this->getAliasNbPages(), 0, 0, 'R');
    }

    // ── Utilidades de fecha ────────────────────────────────────────────────
    private function ahora() {
        return new DateTimeImmutable('now', new DateTimeZone(self::ZONA));
    }

    private function fecha($valor) {
        if (!$valor || substr($valor, 0, 10) === '0000-00-00') return 'No registrada';
        try {
            return (new DateTimeImmutable($valor, new DateTimeZone(self::ZONA)))->format('d/m/Y');
        } catch (Exception $e) { return 'No registrada'; }
    }

    private function textoEdad($nacimiento) {
        if (empty($nacimiento) || substr($nacimiento, 0, 10) === '0000-00-00') return 'No registrada';
        try {
            $n = new DateTimeImmutable($nacimiento, new DateTimeZone(self::ZONA));
            $hoy = $this->ahora();
            if ($n > $hoy) return 'No registrada';
            $anios = $n->diff($hoy)->y;
            return $anios . ($anios === 1 ? ' año' : ' años');
        } catch (Exception $e) { return 'No registrada'; }
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
            $this->Cell($p[3], self::ALTO_FILA, $valor === '' ? '-' : $valor, 1, $i === $ultimo ? 1 : 0, 'L', true, '', 1, false, 'T', 'M');
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
        $this->Ln(6);
    }

    // Línea de firma al final de la receta
    private function firma($nombreMedico) {
        $this->Ln(10);
        $this->asegurarEspacio(34);

        $ancho = 70;
        $x = self::MARGEN + self::ANCHO_UTIL - $ancho;
        $y = $this->GetY() + 18;

        $this->SetDrawColorArray(self::NAVY);
        $this->SetLineWidth(0.3);
        $this->Line($x, $y, $x + $ancho, $y);

        $this->SetXY($x, $y + 1.5);
        $this->SetFont('helvetica', 'B', 10);
        $this->SetTextColorArray(self::NAVY);
        $this->Cell($ancho, 5, $nombreMedico !== '' ? 'Dr(a). ' . $nombreMedico : 'Médico', 0, 1, 'C', false, '', 1);

        $this->SetX($x);
        $this->SetFont('helvetica', '', 9);
        $this->SetTextColorArray(self::GRIS_TEXTO);
        $this->Cell($ancho, 5, 'Firma y sello', 0, 1, 'C');
    }

    // ── Contenido completo de la receta ────────────────────────────────────
    // $datos: fecha_hora, paciente_nombre, paciente_apellidos, ci, fecha_nacimiento,
    //         medico_nombre, medico_apellido  (opcional: indicaciones)
    // $medicamentos: [['nombre'=>, 'dosis'=>, 'frecuencia'=>, 'duracion'=>], ...]
    public function generarReceta(array $datos, array $medicamentos) {
        $this->fechaReceta = $datos["fecha_hora"] ?? null;
        $this->SetCreator('Marie Stopes Bolivia - Sistema Clínico');
        $this->SetTitle('Receta Médica');
        $this->SetMargins(self::MARGEN, self::TOP_CONTENIDO, self::MARGEN);
        $this->SetAutoPageBreak(true, 20);
        $this->setCellPaddings(2, 1, 2, 1);
        $this->AddPage();

        $paciente = trim(($datos["paciente_nombre"] ?? '') . ' ' . ($datos["paciente_apellidos"] ?? ''));
        $medico   = trim(($datos["medico_nombre"] ?? '') . ' ' . ($datos["medico_apellido"] ?? ''));

        // ── Datos del paciente ─────────────────────────────────────────────
        $this->tituloSeccion('Datos del paciente');
        $this->filaDatos([['Paciente', $paciente, 28, 92], ['C.I.', $datos["ci"] ?? '', 24, 36]]);
        $this->filaDatos([['Edad', $this->textoEdad($datos["fecha_nacimiento"] ?? null), 28, 92], ['Fecha', $this->fecha($datos["fecha_hora"] ?? null), 24, 36]]);
        $this->filaDatos([['Médico', $medico !== '' ? 'Dr(a). ' . $medico : '', 28, 152]]);
        $this->Ln(6);

        // ── Medicamentos ───────────────────────────────────────────────────
        $this->tituloSeccion('Medicamentos recetados');

        $cols = [['N.º', 14], ['Medicamento', 66], ['Dosis', 34], ['Frecuencia', 36], ['Duración', 30]];
        $anchos = array_column($cols, 1);

        $this->encabezadoTabla($cols);
        if (empty($medicamentos)) {
            $this->filaMensaje('No se registraron medicamentos en esta receta.');
        } else {
            $n = 0;
            foreach ($medicamentos as $m) {
                $this->saltoSiHaceFalta($cols);
                $this->filaTabla($anchos, [
                    ++$n,
                    $m["nombre"] ?? '',
                    $m["dosis"] ?? '',
                    $m["frecuencia"] ?? '',
                    !empty($m["duracion"]) ? $m["duracion"] : '-'
                ]);
            }
        }
        $this->Ln(6);

        // ── Indicaciones (solo si vienen en $datos) ────────────────────────
        if (trim((string) ($datos["indicaciones"] ?? '')) !== '') {
            $this->bloqueTexto('Indicaciones', $datos["indicaciones"]);
        }

        $this->firma($medico);
    }
}