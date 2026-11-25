<?php
require_once __DIR__ . "/../../../vendor/autoload.php";

/**
 * Comprobante de pago. Ocupa UNA MITAD de una hoja A4 en vertical: la columna
 * izquierda (105 mm de ancho por toda la altura). Se corta por la línea
 * punteada central. Si los ítems no caben en el alto, continúa en otra hoja.
 */
class FacturaPagoPDF extends TCPDF {

    // ── Paleta (la misma de los reportes) ──────────────────────────────────
    const AZUL       = [0, 159, 227];
    const AZUL_CLARO = [214, 230, 245];
    const NAVY       = [22, 58, 86];
    const GRIS_TEXTO = [60, 60, 60];
    const GRIS_LINEA = [200, 200, 200];
    const GRIS_PIE   = [110, 110, 110];

    // ── Medidas (mm) ───────────────────────────────────────────────────────
    const ANCHO_MITAD = 105;                       // la mitad del ancho de un A4
    const COLUMNA     = 'izquierda';               // 'izquierda' | 'derecha'
    const MARGEN      = 7.5;                       // margen dentro de la mitad
    const ANCHO_UTIL  = 90;                        // ANCHO_MITAD - 2 * MARGEN
    const Y_LINEA     = 31;                        // línea azul bajo el encabezado
    const Y_LIMITE    = 268;                       // el contenido no pasa de aquí
    const Y_PIE       = 276;
    const ALTO_HEADER = 8;
    const ALTO_FILA   = 7;
    const LINEA_CORTE = true;                      // línea punteada en el centro de la hoja

    // ── Logo (el PNG trae margen blanco: se compensa para alinear al margen) ─
    const LOGO_W = 40;
    const LOGO_X = 3.1;    // relativo a la mitad
    const LOGO_Y = -2.7;

    private $numeroFactura;
    private $fechaComprobante;

    public function setNumeroFactura($num) {
        $this->numeroFactura = $num;
    }

    // Desplazamiento horizontal de la mitad elegida
    private function x0() {
        return self::COLUMNA === 'derecha' ? self::ANCHO_MITAD : 0;
    }

    // ── Encabezado ─────────────────────────────────────────────────────────
    public function Header() {
        $x0 = $this->x0();

        $logo = __DIR__ . '/../../assets/img/Logo.png';
        if (is_file($logo)) $this->Image($logo, $x0 + self::LOGO_X, self::LOGO_Y, self::LOGO_W, 0, 'PNG');

        $ancho = 58;
        $x = $x0 + self::MARGEN + self::ANCHO_UTIL - $ancho;

        $this->SetTextColorArray(self::AZUL);
        $this->SetFont('helvetica', 'B', 14);
        $this->SetXY($x, 9);
        $this->Cell($ancho, 8, 'Comprobante de pago', 0, 1, 'R');

        $this->SetFont('helvetica', '', 8.5);
        $this->SetTextColorArray(self::GRIS_TEXTO);
        if ($this->fechaComprobante) {
            $html = '<b style="color:#163A56;">Fecha:</b> ' . date('d/m/Y', strtotime($this->fechaComprobante));
            $this->writeHTMLCell($ancho, 5, $x, 18, $html, 0, 1, false, true, 'R');
        }
        if ($this->numeroFactura) {
            $this->SetXY($x, 23);
            $this->Cell($ancho, 5, 'N° ' . str_pad($this->numeroFactura, 8, '0', STR_PAD_LEFT), 0, 1, 'R');
        }

        $this->SetDrawColorArray(self::AZUL);
        $this->SetLineWidth(0.6);
        $this->Line($x0 + self::MARGEN, self::Y_LINEA, $x0 + self::MARGEN + self::ANCHO_UTIL, self::Y_LINEA);
        $this->SetTextColor(0, 0, 0);
    }

    // ── Pie ────────────────────────────────────────────────────────────────
    public function Footer() {
        $x0 = $this->x0();
        $xm = $x0 + self::MARGEN;

        $this->SetDrawColorArray(self::GRIS_LINEA);
        $this->SetLineWidth(0.2);
        $this->Line($xm, self::Y_PIE, $xm + self::ANCHO_UTIL, self::Y_PIE);

        $this->SetFont('helvetica', '', 7);
        $this->SetTextColorArray(self::GRIS_PIE);
        $this->SetXY($xm, self::Y_PIE + 1.5);
        $this->MultiCell(self::ANCHO_UTIL, 3.5, 'Documento generado por el sistema - no válido como factura fiscal', 0, 'L', false, 1);
        if ($this->getNumPages() > 1) {
            $this->SetX($xm);
            $this->Cell(self::ANCHO_UTIL, 3.5, 'Página ' . $this->getAliasNumPage() . ' / ' . $this->getAliasNbPages(), 0, 0, 'R');
        }

        // Línea de corte vertical en el centro de la hoja
        if (self::LINEA_CORTE) {
            $this->SetLineStyle(['width' => 0.2, 'color' => self::GRIS_LINEA, 'dash' => '2,2']);
            $this->Line(self::ANCHO_MITAD, 0, self::ANCHO_MITAD, $this->getPageHeight());
            $this->SetLineStyle(['dash' => 0]);
        }
    }

    // ── Helpers ────────────────────────────────────────────────────────────
    // 100 → "100"   |   100.5 → "100.50"
    private function formatoMonto($valor) {
        $valor = (float)$valor;
        return floor($valor) == $valor
            ? number_format($valor, 0, '', '')
            : number_format($valor, 2, '.', '');
    }

    // Etiqueta en negrita azul oscuro + valor normal (el texto largo baja de línea)
    private function filaDato($etiqueta, $valor, $sep = ':') {
        $this->SetFont('helvetica', '', 9);
        $this->SetTextColorArray(self::GRIS_TEXTO);
        $html = '<b style="color:#163A56;">' . htmlspecialchars($etiqueta) . $sep . '</b> ' . htmlspecialchars($valor);
        $this->writeHTMLCell(self::ANCHO_UTIL, 0, $this->x0() + self::MARGEN, $this->GetY(), $html, 0, 1, false, true, 'L');
        $this->Ln(1);
    }

    private function encabezadoTabla(array $cols) {
        $this->SetFont('helvetica', 'B', 9);
        $this->SetFillColorArray(self::AZUL);
        $this->SetTextColor(255, 255, 255);
        $this->SetDrawColor(255, 255, 255);
        $this->SetLineWidth(0.3);
        $this->SetX($this->x0() + self::MARGEN);
        $ultimo = count($cols) - 1;
        foreach ($cols as $i => $c) {
            $this->Cell($c[1], self::ALTO_HEADER, $c[0], 'LR', $i === $ultimo ? 1 : 0, 'C', true, '', 0, false, 'T', 'M');
        }
    }

    // Fila con alto variable: el concepto largo se parte en varias líneas
    private function fila(array $anchos, array $celdas) {
        $this->SetFont('helvetica', '', 9);
        $maxLineas = 1;
        foreach ($celdas as $i => $txt) {
            $maxLineas = max($maxLineas, $this->getNumLines((string)$txt, $anchos[$i]));
        }
        $alto = max(self::ALTO_FILA, $maxLineas * $this->getFontSize() * $this->getCellHeightRatio() + 3);

        if ($this->GetY() + $alto > self::Y_LIMITE) {
            $this->AddPage();
            $this->encabezadoTabla([['Concepto', $anchos[0]], ['Cantidad', $anchos[1]], ['Subtotal (Bs.)', $anchos[2]]]);
            $this->SetFont('helvetica', '', 9);
        }

        $this->SetFillColorArray(self::AZUL_CLARO);
        $this->SetTextColorArray(self::GRIS_TEXTO);
        $this->SetDrawColorArray(self::AZUL);
        $this->SetLineWidth(0.35);

        $x = $this->x0() + self::MARGEN;
        $y = $this->GetY();
        foreach ($celdas as $i => $txt) {
            $this->SetXY($x, $y);
            $this->MultiCell($anchos[$i], $alto, (string)$txt, 1, 'C', true, 0, '', '', true, 0, false, true, $alto, 'M');
            $x += $anchos[$i];
        }
        $this->SetXY($this->x0() + self::MARGEN, $y + $alto);
    }

    // ── Contenido ──────────────────────────────────────────────────────────
    public function generarFactura(array $datos, array $lineas) {
        $this->setNumeroFactura($datos["id_servicio_prestado"]);
        $this->fechaComprobante = $datos["fecha"];
        $this->SetCreator('Marie Stopes Bolivia - Sistema Clínico');
        $this->SetTitle('Comprobante de Pago N° ' . $datos["id_servicio_prestado"]);

        $x0 = $this->x0();
        // Márgenes: el contenido queda dentro de la mitad elegida
        $this->SetMargins($x0 + self::MARGEN, self::Y_LINEA + 5, $this->getPageWidth() - ($x0 + self::MARGEN + self::ANCHO_UTIL));
        $this->SetAutoPageBreak(false);
        $this->AddPage();

        // ── Paciente / Cliente / Método ─────────────────────────────────────
        $nombrePaciente = trim(($datos["paciente_nombre"] ?? "") . " " . ($datos["paciente_apellidos"] ?? ""));
        if ($nombrePaciente !== "") {
            $this->filaDato('Paciente', $nombrePaciente);
            if (!empty($datos["paciente_ci"])) $this->filaDato('C.I.', (string)$datos["paciente_ci"], '');
        }
        if (!empty($datos["cliente_nombre"])) {
            $this->filaDato('Cliente', $datos["cliente_nombre"]);
            if (!empty($datos["cliente_nit"])) $this->filaDato('C.I.', (string)$datos["cliente_nit"], '');
        }
        $this->filaDato('Método de pago', $datos["metodo_pago"]);
        $this->Ln(2);

        // ── Tabla de ítems ──────────────────────────────────────────────────
        $cols   = [['Concepto', 44], ['Cantidad', 20], ['Subtotal (Bs.)', 26]];
        $anchos = array_column($cols, 1);

        $this->encabezadoTabla($cols);
        foreach ($lineas as $l) {
            $nombre = $l["nombre_servicio"] ?? $l["nombre_examen"] ?? "—";
            $this->fila($anchos, [$nombre, $l["cantidad"], $this->formatoMonto($l["subtotal"])]);
        }

        // ── Total ───────────────────────────────────────────────────────────
        $this->Ln(4);
        if ($this->GetY() + 10 > self::Y_LIMITE) {
            $this->AddPage();
        }

        $this->SetFont('helvetica', 'B', 11);
        $this->SetFillColorArray(self::AZUL);
        $this->SetTextColor(255, 255, 255);
        $this->SetDrawColorArray(self::AZUL);
        $this->SetLineWidth(0.35);
        $this->SetX($x0 + self::MARGEN);
        $this->Cell(30, 10, 'Total', 1, 0, 'C', true, '', 0, false, 'T', 'M');

        $this->SetFillColorArray(self::AZUL_CLARO);
        $this->SetTextColorArray(self::NAVY);
        $this->Cell(60, 10, $this->formatoMonto($datos["costo_total"]) . ' Bs.', 1, 1, 'C', true, '', 0, false, 'T', 'M');
    }
}