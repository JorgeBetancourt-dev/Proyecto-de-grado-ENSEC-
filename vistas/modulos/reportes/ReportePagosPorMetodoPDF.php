<?php
require_once __DIR__ . "/../../../vendor/autoload.php";

class ReportePagosPorMetodoPDF extends TCPDF {

    // ── Paleta (ajústala aquí si cambian los colores institucionales) ──────
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
    const TITULO_W    = 95;    // ancho del bloque de título (derecha del encabezado)

    // ── Logo (el PNG trae margen transparente, por eso Y queda en 0) ───────
    const LOGO_X = 10;
    const LOGO_Y = 0;
    const LOGO_W = 70;

    private $fechaInicio;
    private $fechaFin;
    private $metodoPago;
    private $tituloReporte = "Reporte de Pagos entre\nfechas y método de pago\nseleccionado";

    public function setDatosReporte($inicio, $fin, $metodo) {
        $this->fechaInicio = $inicio;
        $this->fechaFin = $fin;
        $this->metodoPago = $metodo;
    }

    public function setTituloReporte($titulo) {
        $this->tituloReporte = $titulo;
    }

    // Líneas de filtros que se muestran bajo el título (HTML simple)
    private function lineasFiltros() {
        $etq = 'style="color:#163A56;"';
        $lineas = [];
        if ($this->fechaInicio && $this->fechaFin) {
            $lineas[] = "<b $etq>Fecha inicio:</b> " . date('d/m/Y', strtotime($this->fechaInicio))
                      . " <b $etq>Fecha final:</b> " . date('d/m/Y', strtotime($this->fechaFin));
        }
        if ($this->metodoPago) {
            $lineas[] = "<b $etq>Método de pago:</b> " . $this->nombreMetodo($this->metodoPago);
        }
        return $lineas;
    }

    // Calcula posiciones del encabezado según cuántas líneas tiene el título y los filtros
    // Devuelve [yTitulo, yFiltros, yLineaSeparadora]
    private function medidasEncabezado() {
        $lineasTitulo = substr_count($this->tituloReporte, "\n") + 1;
        $yTitulo  = ($lineasTitulo > 2) ? 15 : 21.5;
        $yFiltros = $yTitulo + $lineasTitulo * 9 + 3;
        $yFin     = $yFiltros + count($this->lineasFiltros()) * 5;
        return [$yTitulo, $yFiltros, max(56, $yFin + 3)];
    }

    // ── Encabezado de cada página ──────────────────────────────────────────
    public function Header() {
        list($yTitulo, $yFiltros, $yLinea) = $this->medidasEncabezado();

        // Logo a la izquierda
        $logo = __DIR__ . '/../../assets/img/Logo.png';
        if (is_file($logo)) $this->Image($logo, self::LOGO_X, self::LOGO_Y, self::LOGO_W, 0, 'PNG');

        // Título a la derecha
        $xTitulo = self::MARGEN + self::ANCHO_UTIL - self::TITULO_W;
        $this->SetTextColorArray(self::AZUL);
        $this->SetFont('helvetica', 'B', 18);
        $this->SetXY($xTitulo, $yTitulo);
        $this->MultiCell(self::TITULO_W, 9, $this->tituloReporte, 0, 'R', false, 1);

        // Filtros (etiqueta en negrita azul oscuro, valor normal)
        $this->SetFont('helvetica', '', 9);
        $this->SetTextColorArray(self::GRIS_TEXTO);
        foreach ($this->lineasFiltros() as $i => $html) {
            $this->writeHTMLCell(self::TITULO_W, 5, $xTitulo, $yFiltros + $i * 5, $html, 0, 1, false, true, 'R');
        }

        // Línea azul separadora
        $this->SetDrawColorArray(self::AZUL);
        $this->SetLineWidth(0.7);
        $this->Line(self::MARGEN, $yLinea, self::MARGEN + self::ANCHO_UTIL, $yLinea);
        $this->SetTextColor(0, 0, 0);
    }

    // ── Pie de página ──────────────────────────────────────────────────────
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

    // ── Helpers de diseño ──────────────────────────────────────────────────
    private function tituloSeccion($texto) {
        $this->SetFont('helvetica', 'B', 11);
        $this->SetTextColorArray(self::NAVY);
        $this->Cell(0, 7, $texto, 0, 1, 'L');
        $this->Ln(2);
    }

    // $cols = [['Título', ancho], ...]
    private function encabezadoTabla(array $cols) {
        $this->SetFont('helvetica', 'B', 10);
        $this->SetFillColorArray(self::AZUL);
        $this->SetTextColor(255, 255, 255);
        $this->SetDrawColor(255, 255, 255);   // separadores blancos entre columnas
        $this->SetLineWidth(0.3);

        $ultimo = count($cols) - 1;
        foreach ($cols as $i => $col) {
            $this->Cell($col[1], self::ALTO_HEADER, $col[0], 'LR', $i === $ultimo ? 1 : 0, 'C', true, '', 0, false, 'T', 'M');
        }
    }

    // $anchos = [27, 62, ...]  $celdas = ['13:00', 'Efectivo', ...]
    private function filaTabla(array $anchos, array $celdas, $negrita = false) {
        $this->SetFont('helvetica', $negrita ? 'B' : '', 10);
        $this->SetFillColorArray(self::AZUL_CLARO);
        $this->SetTextColorArray(self::GRIS_TEXTO);
        $this->SetDrawColorArray(self::AZUL);
        $this->SetLineWidth(0.35);

        $ultimo = count($celdas) - 1;
        foreach ($celdas as $i => $texto) {
            // stretch=1: si el texto no cabe, se comprime en lugar de salirse de la celda
            $this->Cell($anchos[$i], self::ALTO_FILA, $texto, 1, $i === $ultimo ? 1 : 0, 'C', true, '', 1, false, 'T', 'M');
        }
    }

    // Si la fila no cabe en la página, salta y repite el encabezado de la tabla
    private function saltoSiHaceFalta(array $cols) {
        if ($this->GetY() + self::ALTO_FILA > $this->getPageHeight() - $this->getBreakMargin()) {
            $this->AddPage();
            $this->encabezadoTabla($cols);
        }
    }

    // 1200 → "1200"   |   1200.5 → "1200.50"
    private function formatoMonto($valor) {
        $valor = (float)$valor;
        return floor($valor) == $valor
            ? number_format($valor, 0, '', '')
            : number_format($valor, 2, '.', '');
    }

    // "efectivo y qr" → "Efectivo y QR"
    private function nombreMetodo($metodo) {
        return preg_replace('/\bqr\b/i', 'QR', ucfirst(strtolower($metodo)));
    }

    // ── Contenido completo del reporte (resumen + detalle) ─────────────────
    // $diarios se conserva en la firma por compatibilidad con el controlador,
    // pero el nuevo diseño ya no muestra la tabla "Totales por día".
    public function generarReportePagosPorMetodo($fechaInicio, $fechaFin, $metodoPago, array $detalle, array $diarios, array $totalGeneral) {
        $this->setDatosReporte($fechaInicio, $fechaFin, $metodoPago);
        $this->SetCreator('Marie Stopes Bolivia - Sistema Clínico');
        $this->SetTitle('Reporte de Pagos ' . $metodoPago . ' ' . $fechaInicio . ' a ' . $fechaFin);

        list(, , $yLinea) = $this->medidasEncabezado();
        $this->SetMargins(self::MARGEN, $yLinea + 6, self::MARGEN);
        $this->SetAutoPageBreak(true, 20);
        $this->AddPage();

        // ── Resumen del método seleccionado ────────────────────────────────
        $this->tituloSeccion('Resumen por método de pago');

        $colsResumen = [['Método de pago', 78], ['Cantidad de pagos', 51], ['Total (Bs.)', 51]];
        $anchosResumen = array_column($colsResumen, 1);

        $this->encabezadoTabla($colsResumen);
        $this->filaTabla($anchosResumen, [
            $this->nombreMetodo($metodoPago),
            isset($totalGeneral["cantidad_pagos"]) ? (int)$totalGeneral["cantidad_pagos"] : 0,
            $this->formatoMonto(isset($totalGeneral["total"]) ? $totalGeneral["total"] : 0)
        ]);

        $this->Ln(6);

        // ── Detalle de pagos ───────────────────────────────────────────────
        $this->tituloSeccion('Detalle de pagos');

        $colsDetalle = [['Fecha/Hora', 42], ['Método', 47], ['Cliente', 55], ['Monto (Bs.)', 36]];
        $anchosDetalle = array_column($colsDetalle, 1);

        $this->encabezadoTabla($colsDetalle);

        if (empty($detalle)) {
            $this->SetFont('helvetica', 'I', 10);
            $this->SetFillColorArray(self::AZUL_CLARO);
            $this->SetTextColorArray(self::GRIS_TEXTO);
            $this->SetDrawColorArray(self::AZUL);
            $this->SetLineWidth(0.35);
            $this->Cell(self::ANCHO_UTIL, self::ALTO_FILA, 'No se registraron pagos con este método en el rango indicado.', 1, 1, 'C', true, '', 0, false, 'T', 'M');
            return;
        }

        foreach ($detalle as $fila) {
            $this->saltoSiHaceFalta($colsDetalle);
            // Si la consulta no trae metodo_pago por fila, se usa el método seleccionado
            $metodoFila = isset($fila["metodo_pago"]) ? $fila["metodo_pago"] : $metodoPago;
            $this->filaTabla($anchosDetalle, [
                date('d/m/Y H:i', strtotime($fila["fecha"])),
                $this->nombreMetodo($metodoFila),
                $fila["cliente"],
                $this->formatoMonto($fila["costo_total"])
            ]);
        }
    }
}