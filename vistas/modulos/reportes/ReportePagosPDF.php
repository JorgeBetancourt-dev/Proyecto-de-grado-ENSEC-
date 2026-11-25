<?php
require_once __DIR__ . "/../../../vendor/autoload.php";


class ReportePagosPDF extends TCPDF {

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

    // ── Logo (el PNG trae margen transparente, por eso Y queda en 0) ───────
    const LOGO_X = 10;
    const LOGO_Y = 0;
    const LOGO_W = 70;

    // Métodos de pago que SIEMPRE aparecen en el resumen (en este orden)
    const METODOS = ['Efectivo', 'Tarjeta', 'QR', 'Efectivo y tarjeta', 'Efectivo y QR', 'Tarjeta y QR'];

    private $fechaReporte;
    private $tituloReporte = "Reporte de\nPagos del Día";

    public function setFechaReporte($fecha) {
        $this->fechaReporte = $fecha;
    }

    public function setTituloReporte($titulo) {
        $this->tituloReporte = $titulo;
    }

    // ── Encabezado de cada página ──────────────────────────────────────────
    public function Header() {
        // Logo a la izquierda
        $logo = __DIR__ . '/../../assets/img/Logo.png';
        if (is_file($logo)) $this->Image($logo, self::LOGO_X, self::LOGO_Y, self::LOGO_W, 0, 'PNG');

        // Título a la derecha
        $xTitulo = self::MARGEN + self::ANCHO_UTIL - 85;
        $this->SetTextColorArray(self::AZUL);
        $this->SetFont('helvetica', 'B', 18);
        $this->SetXY($xTitulo, 21.5);
        $this->MultiCell(85, 9, $this->tituloReporte, 0, 'R', false, 1);

        // Fecha (etiqueta en negrita azul oscuro, valor normal)
        if ($this->fechaReporte) {
            $this->SetFont('helvetica', '', 9);
            $this->SetTextColorArray(self::GRIS_TEXTO);
            $html = '<b style="color:#163A56;">Fecha:</b> ' . date('d/m/Y', strtotime($this->fechaReporte));
            $this->writeHTMLCell(85, 5, $xTitulo, $this->GetY() + 3, $html, 0, 1, false, true, 'R');
        }

        // Línea azul separadora
        $this->SetDrawColorArray(self::AZUL);
        $this->SetLineWidth(0.7);
        $this->Line(self::MARGEN, 56, self::MARGEN + self::ANCHO_UTIL, 56);
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

    // Clave tolerante: "Efectivo y QR", "efectivo_qr" y "qr + efectivo" dan la misma clave
    private function claveMetodo($metodo) {
        $m = preg_replace('/[^a-záéíóúñ]+/u', ' ', strtolower(trim($metodo)));
        $tokens = array_filter(explode(' ', $m), function ($t) {
            return $t !== '' && $t !== 'y' && $t !== 'e';
        });
        sort($tokens);
        return implode('|', $tokens);
    }

    // Devuelve el resumen con los 6 métodos siempre presentes (en 0 si no hubo pagos)
    private function resumenCompleto(array $resumen) {
        $porClave = [];
        $total = null;
        foreach ($resumen as $fila) {
            if ($fila["metodo_pago"] === 'TOTAL GENERAL') { $total = $fila; continue; }
            $porClave[$this->claveMetodo($fila["metodo_pago"])] = $fila;
        }

        $resultado = [];
        foreach (self::METODOS as $nombre) {
            $clave = $this->claveMetodo($nombre);
            $fila = isset($porClave[$clave]) ? $porClave[$clave] : null;
            unset($porClave[$clave]);
            $resultado[] = [
                "metodo_pago"    => $nombre,
                "cantidad_pagos" => $fila ? $fila["cantidad_pagos"] : 0,
                "total"          => $fila ? $fila["total"] : 0
            ];
        }
        foreach ($porClave as $fila) $resultado[] = $fila;   // métodos no previstos
        if ($total !== null) $resultado[] = $total;
        return $resultado;
    }

    // ── Contenido completo del reporte (resumen + detalle) ─────────────────
    public function generarReportePagos($fecha, array $detalle, array $resumen) {
        $this->setFechaReporte($fecha);
        $this->SetCreator('Marie Stopes Bolivia - Sistema Clínico');
        $this->SetTitle('Reporte de Pagos - ' . $fecha);
        $this->SetMargins(self::MARGEN, 62, self::MARGEN);
        $this->SetAutoPageBreak(true, 20);
        $this->AddPage();

        // ── Resumen por método de pago ─────────────────────────────────────
        $this->tituloSeccion('Resumen por método de pago');

        $colsResumen = [['Método de pago', 78], ['Cantidad de pagos', 51], ['Total (Bs.)', 51]];
        $anchosResumen = array_column($colsResumen, 1);

        $this->encabezadoTabla($colsResumen);
        foreach ($this->resumenCompleto($resumen) as $fila) {
            $this->saltoSiHaceFalta($colsResumen);
            $esTotal = ($fila["metodo_pago"] === 'TOTAL GENERAL');
            $this->filaTabla($anchosResumen, [
                $esTotal ? 'TOTAL GENERAL' : $this->nombreMetodo($fila["metodo_pago"]),
                $fila["cantidad_pagos"],
                $this->formatoMonto($fila["total"])
            ], $esTotal);
        }

        $this->Ln(6);

        // ── Detalle de pagos ───────────────────────────────────────────────
        $this->tituloSeccion('Detalle de pagos');

        $colsDetalle = [['Hora', 27], ['Método', 62], ['Cliente', 55], ['Monto (Bs.)', 36]];
        $anchosDetalle = array_column($colsDetalle, 1);

        $this->encabezadoTabla($colsDetalle);

        if (empty($detalle)) {
            $this->SetFont('helvetica', 'I', 10);
            $this->SetFillColorArray(self::AZUL_CLARO);
            $this->SetTextColorArray(self::GRIS_TEXTO);
            $this->SetDrawColorArray(self::AZUL);
            $this->SetLineWidth(0.35);
            $this->Cell(self::ANCHO_UTIL, self::ALTO_FILA, 'No se registraron pagos en esta fecha.', 1, 1, 'C', true, '', 0, false, 'T', 'M');
            return;
        }

        foreach ($detalle as $fila) {
            $this->saltoSiHaceFalta($colsDetalle);
            $this->filaTabla($anchosDetalle, [
                date('H:i', strtotime($fila["fecha"])),
                $this->nombreMetodo($fila["metodo_pago"]),
                $fila["cliente"],
                $this->formatoMonto($fila["costo_total"])
            ]);
        }
    }
}