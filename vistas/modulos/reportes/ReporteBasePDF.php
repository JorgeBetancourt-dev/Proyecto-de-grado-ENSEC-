<?php
require_once __DIR__ . "/../../../vendor/autoload.php";

/**
 * Base común de los reportes PDF (mismo diseño que ReportePagosPDF).
 * Encabezado con logo + título + líneas de información, pie de página y
 * helpers para dibujar tablas con filas de alto variable.
 */
class ReporteBasePDF extends TCPDF {

    // ── Paleta ─────────────────────────────────────────────────────────────
    const AZUL       = [0, 159, 227];
    const AZUL_CLARO = [214, 230, 245];
    const NAVY       = [22, 58, 86];
    const GRIS_TEXTO = [60, 60, 60];
    const GRIS_LINEA = [200, 200, 200];
    const GRIS_PIE   = [110, 110, 110];

    // ── Medidas (mm) ───────────────────────────────────────────────────────
    const MARGEN       = 15;
    const ANCHO_UTIL   = 180;
    const ALTO_HEADER  = 9.5;
    const ALTO_FILA    = 9;
    const ANCHO_TITULO = 85;
    const ANCHO_INFO   = 120;

    // ── Logo ───────────────────────────────────────────────────────────────
    const LOGO_X = 10;
    const LOGO_Y = 0;
    const LOGO_W = 70;

    protected $tituloReporte = '';
    protected $infoReporte   = [];   // [ [ ['Etiqueta','Valor'], ... ], ... ]  (una entrada = una línea)
    protected $yTitulo       = 21.5;
    protected $yLineaHeader  = 56;

    // ══════════════════════════════════════════════════════════════════════
    //  Encabezado y pie
    // ══════════════════════════════════════════════════════════════════════

    /**
     * Prepara el documento: calcula la altura del encabezado según las líneas
     * del título y de la información, fija márgenes y agrega la primera página.
     * $info = [ [['Fecha inicio','15/11/2026'],['Fecha final','16/11/2026']], [['Método de pago','Efectivo']] ]
     */
    protected function iniciarReporte($titulo, array $info, $tituloPdf) {
        $this->tituloReporte = $titulo;
        $this->infoReporte   = $info;

        $this->SetFont('helvetica', 'B', 18);
        $lineasTitulo = max(1, $this->getNumLines($titulo, self::ANCHO_TITULO));
        $alto = $lineasTitulo * 9 + (count($info) ? 3 + count($info) * 5.5 : 0);

        $this->yTitulo      = max(8, 34.5 - $alto / 2);
        $this->yLineaHeader = $this->yTitulo + $alto + 8.5;

        $this->SetCreator('Marie Stopes Bolivia - Sistema Clínico');
        $this->SetTitle($tituloPdf);
        $this->SetMargins(self::MARGEN, $this->yLineaHeader + 6, self::MARGEN);
        $this->SetAutoPageBreak(true, 20);
        $this->AddPage();
    }

    public function Header() {
        // Logo (centrado verticalmente si el encabezado crece)
        $logo = __DIR__ . '/../../assets/img/Logo.png';
        if (is_file($logo)) {
            $this->Image($logo, self::LOGO_X, self::LOGO_Y + max(0, ($this->yLineaHeader - 56) / 2), self::LOGO_W, 0, 'PNG');
        }

        // Título a la derecha
        $xTitulo = self::MARGEN + self::ANCHO_UTIL - self::ANCHO_TITULO;
        $this->SetTextColorArray(self::AZUL);
        $this->SetFont('helvetica', 'B', 18);
        $this->SetXY($xTitulo, $this->yTitulo);
        $this->MultiCell(self::ANCHO_TITULO, 9, $this->tituloReporte, 0, 'R', false, 1);

        // Líneas de información (etiqueta en negrita azul oscuro, valor normal)
        $this->SetFont('helvetica', '', 9);
        $this->SetTextColorArray(self::GRIS_TEXTO);
        $xInfo = self::MARGEN + self::ANCHO_UTIL - self::ANCHO_INFO;
        $y = $this->GetY() + 3;
        foreach ($this->infoReporte as $linea) {
            $partes = [];
            foreach ($linea as $par) {
                $partes[] = '<b style="color:#163A56;">' . htmlspecialchars($par[0]) . ':</b> ' . htmlspecialchars($par[1]);
            }
            $this->writeHTMLCell(self::ANCHO_INFO, 5, $xInfo, $y, implode(' ', $partes), 0, 1, false, true, 'R');
            $y += 5.5;
        }

        // Línea azul separadora
        $this->SetDrawColorArray(self::AZUL);
        $this->SetLineWidth(0.7);
        $this->Line(self::MARGEN, $this->yLineaHeader, self::MARGEN + self::ANCHO_UTIL, $this->yLineaHeader);
        $this->SetTextColor(0, 0, 0);
    }

    public function Footer() {
        $this->SetY(-16);
        $this->SetDrawColorArray(self::GRIS_LINEA);
        $this->SetLineWidth(0.2);
        $this->Line(self::MARGEN, $this->GetY(), self::MARGEN + self::ANCHO_UTIL, $this->GetY());

        $this->SetY(-14.5);
        $this->SetFont('helvetica', '', 8);
        $this->SetTextColorArray(self::GRIS_PIE);
        $this->Cell(90, 5, 'Generado el ' . date('d/m/Y H:i'), 0, 0, 'L');
        $this->Cell(90, 5, 'Página ' . $this->getAliasNumPage() . ' / ' . $this->getAliasNbPages(), 0, 0, 'R');
    }

    // ══════════════════════════════════════════════════════════════════════
    //  Helpers de diseño
    // ══════════════════════════════════════════════════════════════════════

    // Salta de página si no quedan $mm milímetros disponibles
    protected function asegurarEspacio($mm) {
        if ($this->GetY() + $mm > $this->getPageHeight() - $this->getBreakMargin()) {
            $this->AddPage();
        }
    }

    protected function tituloSeccion($texto) {
        $this->asegurarEspacio(7 + 2 + self::ALTO_HEADER + self::ALTO_FILA);
        $this->SetFont('helvetica', 'B', 11);
        $this->SetTextColorArray(self::NAVY);
        $this->Cell(0, 7, $texto, 0, 1, 'L');
        $this->Ln(2);
    }

    // $cols = [['Título', ancho], ...]
    protected function encabezadoTabla(array $cols) {
        $this->SetFont('helvetica', 'B', 10);
        $this->SetFillColorArray(self::AZUL);
        $this->SetTextColor(255, 255, 255);
        $this->SetDrawColor(255, 255, 255);
        $this->SetLineWidth(0.3);

        $ultimo = count($cols) - 1;
        foreach ($cols as $i => $col) {
            $this->Cell($col[1], self::ALTO_HEADER, $col[0], 'LR', $i === $ultimo ? 1 : 0, 'C', true, '', 0, false, 'T', 'M');
        }
    }

    /**
     * Dibuja una tabla completa: encabezado + filas. Si una fila no cabe, salta
     * de página y repite el encabezado. Las celdas largas se parten en varias líneas.
     *
     * $filas admite:
     *   ['celda1', 'celda2', ...]            fila normal
     *   ['negrita' => true, 'celdas' => [..]] fila en negrita (totales)
     *   ['banda' => 'Texto']                  fila de grupo a todo el ancho (p. ej. un médico)
     */
    protected function tabla(array $cols, array $filas, $tamFuente = 10) {
        $anchos = array_column($cols, 1);
        $this->encabezadoTabla($cols);

        foreach ($filas as $fila) {
            if (isset($fila['banda'])) {
                // La banda nunca queda sola al final de una página
                $this->saltoSiHaceFalta($cols, self::ALTO_FILA * 2);
                $this->banda($fila['banda']);
                continue;
            }

            $celdas  = isset($fila['celdas']) ? $fila['celdas'] : $fila;
            $negrita = !empty($fila['negrita']);

            $this->SetFont('helvetica', $negrita ? 'B' : '', $tamFuente);
            $alto = $this->altoFila($anchos, $celdas);
            $this->saltoSiHaceFalta($cols, $alto);
            $this->dibujarFila($anchos, $celdas, $alto, $negrita, $tamFuente);
        }
    }

    // Fila de una sola celda a todo el ancho (mensaje de "sin datos")
    protected function filaMensaje($texto) {
        $this->SetFont('helvetica', 'I', 10);
        $this->SetFillColorArray(self::AZUL_CLARO);
        $this->SetTextColorArray(self::GRIS_TEXTO);
        $this->SetDrawColorArray(self::AZUL);
        $this->SetLineWidth(0.35);
        $this->Cell(self::ANCHO_UTIL, self::ALTO_FILA, $texto, 1, 1, 'C', true, '', 0, false, 'T', 'M');
    }

    // Banda de grupo (azul oscuro, texto blanco)
    private function banda($texto) {
        $this->SetFont('helvetica', 'B', 10);
        $this->SetFillColorArray(self::NAVY);
        $this->SetTextColor(255, 255, 255);
        $this->SetDrawColorArray(self::NAVY);
        $this->SetLineWidth(0.35);
        $this->Cell(self::ANCHO_UTIL, 8, $texto, 1, 1, 'L', true, '', 0, false, 'T', 'M');
    }

    // Alto de la fila = el de la celda con más líneas (mínimo ALTO_FILA)
    private function altoFila(array $anchos, array $celdas) {
        $maxLineas = 1;
        foreach ($celdas as $i => $texto) {
            $maxLineas = max($maxLineas, $this->getNumLines((string)$texto, $anchos[$i]));
        }
        $alto = $maxLineas * $this->getFontSize() * $this->getCellHeightRatio() + 3.4;
        return max(self::ALTO_FILA, $alto);
    }

    private function dibujarFila(array $anchos, array $celdas, $alto, $negrita, $tamFuente) {
        $this->SetFont('helvetica', $negrita ? 'B' : '', $tamFuente);
        $this->SetFillColorArray(self::AZUL_CLARO);
        $this->SetTextColorArray(self::GRIS_TEXTO);
        $this->SetDrawColorArray(self::AZUL);
        $this->SetLineWidth(0.35);

        $x = self::MARGEN;
        $y = $this->GetY();
        foreach ($celdas as $i => $texto) {
            $this->SetXY($x, $y);
            $this->MultiCell($anchos[$i], $alto, (string)$texto, 1, 'C', true, 0, '', '', true, 0, false, true, $alto, 'M');
            $x += $anchos[$i];
        }
        $this->SetXY(self::MARGEN, $y + $alto);
    }

    // Si la fila no cabe en la página, salta y repite el encabezado de la tabla
    private function saltoSiHaceFalta(array $cols, $alto) {
        if ($this->GetY() + $alto > $this->getPageHeight() - $this->getBreakMargin()) {
            $this->AddPage();
            $this->encabezadoTabla($cols);
        }
    }

    // ══════════════════════════════════════════════════════════════════════
    //  Formatos
    // ══════════════════════════════════════════════════════════════════════

    // 1200 → "1200"   |   1200.5 → "1200.50"
    protected function formatoMonto($valor) {
        $valor = (float)$valor;
        return floor($valor) == $valor
            ? number_format($valor, 0, '', '')
            : number_format($valor, 2, '.', '');
    }

    // "efectivo y qr" → "Efectivo y QR"
    protected function nombreMetodo($metodo) {
        return preg_replace('/\bqr\b/i', 'QR', ucfirst(strtolower($metodo)));
    }

    protected function fmtFecha($fecha) {
        return date('d/m/Y', strtotime($fecha));
    }

    protected function fmtFechaHora($fecha) {
        return date('d/m/Y H:i', strtotime($fecha));
    }

    // Agrupa filas (ya ordenadas por médico) → [id_medico => ['nombre' => .., 'filas' => [..]]]
    protected function agruparPorMedico(array $filas) {
        $grupos = [];
        foreach ($filas as $f) {
            $id = $f['id_medico'];
            if (!isset($grupos[$id])) {
                $grupos[$id] = ['nombre' => $f['medico'], 'filas' => []];
            }
            $grupos[$id]['filas'][] = $f;
        }
        return $grupos;
    }

    // Línea de información "Médico: ..." (todos los médicos si no se eligió uno)
    protected function infoMedico($nombreMedico) {
        return [['Médico', $nombreMedico ? $nombreMedico : 'Todos los médicos']];
    }
}