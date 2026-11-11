<?php
require_once __DIR__ . "/../../../vendor/autoload.php";


class ReportePagosPDF extends TCPDF {

    private $fechaReporte;

    public function setFechaReporte($fecha) {
        $this->fechaReporte = $fecha;
    }

    // Encabezado de cada página
    public function Header() {
        $this->SetFont('helvetica', 'B', 14);
        $this->Cell(0, 8, 'Marie Stopes Bolivia', 0, 1, 'C');
        $this->SetFont('helvetica', '', 11);
        $this->Cell(0, 6, 'Reporte de Pagos del Día', 0, 1, 'C');
        if ($this->fechaReporte) {
            $this->SetFont('helvetica', '', 9);
            $this->Cell(0, 5, 'Fecha: ' . date('d/m/Y', strtotime($this->fechaReporte)), 0, 1, 'C');
        }
        $this->Ln(2);
        $this->SetLineWidth(0.3);
        $this->Line(15, $this->GetY(), 195, $this->GetY());
        $this->Ln(4);
    }

    // Pie de página
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 5, 'Generado el ' . date('d/m/Y H:i'), 0, 0, 'L');
        $this->Cell(0, 5, 'Página ' . $this->getAliasNumPage() . ' / ' . $this->getAliasNbPages(), 0, 0, 'R');
    }

    // Arma el contenido completo del reporte (detalle + resumen)
    public function generarReportePagos($fecha, array $detalle, array $resumen) {
        $this->setFechaReporte($fecha);
        $this->SetCreator('Marie Stopes Bolivia - Sistema Clínico');
        $this->SetTitle('Reporte de Pagos - ' . $fecha);
        $this->SetMargins(15, 30, 15);
        $this->SetAutoPageBreak(true, 20);
        $this->AddPage();

        // ── Tabla de detalle ────────────────────────────────────────────────
        $this->SetFont('helvetica', 'B', 11);
        $this->Cell(0, 7, 'Detalle de pagos', 0, 1, 'L');
        $this->Ln(1);

        $this->SetFont('helvetica', 'B', 9);
        $this->SetFillColor(230, 230, 230);
        $anchoHora = 30; $anchoMetodo = 30; $anchoCliente = 75; $anchoMonto = 25;

        $this->Cell($anchoHora, 7, 'Fecha/Hora', 1, 0, 'C', true);
        $this->Cell($anchoMetodo, 7, 'Método', 1, 0, 'C', true);
        $this->Cell($anchoCliente, 7, 'Cliente', 1, 0, 'C', true);
        $this->Cell($anchoMonto, 7, 'Monto (Bs.)', 1, 1, 'C', true);

        $this->SetFont('helvetica', '', 9);
        $metodoAnterior = null;
        $fill = false;

        foreach ($detalle as $fila) {
            // Alterna color de fondo cada vez que cambia el método (agrupación visual)
            if ($fila["metodo_pago"] !== $metodoAnterior) {
                $fill = !$fill;
                $metodoAnterior = $fila["metodo_pago"];
            }
            $this->SetFillColor($fill ? 245 : 255, $fill ? 245 : 255, $fill ? 245 : 255);

            $this->Cell($anchoHora, 6, date('H:i:s', strtotime($fila["fecha"])), 1, 0, 'C', true);
            $this->Cell($anchoMetodo, 6, ucfirst($fila["metodo_pago"]), 1, 0, 'C', true);
            $this->Cell($anchoCliente, 6, $fila["cliente"], 1, 0, 'L', true);
            $this->Cell($anchoMonto, 6, number_format($fila["costo_total"], 2), 1, 1, 'R', true);
        }

        if (empty($detalle)) {
            $this->SetFont('helvetica', 'I', 9);
            $this->Cell(0, 8, 'No se registraron pagos en esta fecha.', 1, 1, 'C');
        }

        $this->Ln(6);

        // ── Tabla de resumen ─────────────────────────────────────────────────
        $this->SetFont('helvetica', 'B', 11);
        $this->Cell(0, 7, 'Resumen por método de pago', 0, 1, 'L');
        $this->Ln(1);

        $this->SetFont('helvetica', 'B', 9);
        $this->SetFillColor(230, 230, 230);
        $anchoMetodoR = 60; $anchoCantidad = 45; $anchoTotal = 55;

        $this->Cell($anchoMetodoR, 7, 'Método de pago', 1, 0, 'C', true);
        $this->Cell($anchoCantidad, 7, 'Cantidad de pagos', 1, 0, 'C', true);
        $this->Cell($anchoTotal, 7, 'Total (Bs.)', 1, 1, 'C', true);

        $this->SetFont('helvetica', '', 9);
        foreach ($resumen as $fila) {
            $esTotalGeneral = ($fila["metodo_pago"] === 'TOTAL GENERAL');
            if ($esTotalGeneral) {
                $this->SetFont('helvetica', 'B', 9);
                $this->SetFillColor(220, 220, 220);
            } else {
                $this->SetFont('helvetica', '', 9);
                $this->SetFillColor(255, 255, 255);
            }
            $this->Cell($anchoMetodoR, 6, ucfirst($fila["metodo_pago"]), 1, 0, 'L', true);
            $this->Cell($anchoCantidad, 6, $fila["cantidad_pagos"], 1, 0, 'C', true);
            $this->Cell($anchoTotal, 6, number_format($fila["total"], 2), 1, 1, 'R', true);
        }
    }
}