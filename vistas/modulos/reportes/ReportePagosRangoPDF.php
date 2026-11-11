<?php
require_once __DIR__ . "/../../../vendor/autoload.php";

class ReportePagosRangoPDF extends TCPDF {

    private $fechaInicio;
    private $fechaFin;

    public function setRangoFechas($inicio, $fin) {
        $this->fechaInicio = $inicio;
        $this->fechaFin = $fin;
    }

    public function Header() {
        $this->SetFont('helvetica', 'B', 14);
        $this->Cell(0, 8, 'Marie Stopes Bolivia', 0, 1, 'C');
        $this->SetFont('helvetica', '', 11);
        $this->Cell(0, 6, 'Reporte de Pagos por Rango de Fechas', 0, 1, 'C');
        if ($this->fechaInicio && $this->fechaFin) {
            $this->SetFont('helvetica', '', 9);
            $this->Cell(0, 5, 'Del ' . date('d/m/Y', strtotime($this->fechaInicio)) . ' al ' . date('d/m/Y', strtotime($this->fechaFin)), 0, 1, 'C');
        }
        $this->Ln(2);
        $this->SetLineWidth(0.3);
        $this->Line(15, $this->GetY(), 195, $this->GetY());
        $this->Ln(4);
    }

    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 5, 'Generado el ' . date('d/m/Y H:i'), 0, 0, 'L');
        $this->Cell(0, 5, 'Página ' . $this->getAliasNumPage() . ' / ' . $this->getAliasNbPages(), 0, 0, 'R');
    }

    public function generarReportePagosRango($fechaInicio, $fechaFin, array $detalle, array $diarios, array $resumen) {
        $this->setRangoFechas($fechaInicio, $fechaFin);
        $this->SetCreator('Marie Stopes Bolivia - Sistema Clínico');
        $this->SetTitle('Reporte de Pagos ' . $fechaInicio . ' a ' . $fechaFin);
        $this->SetMargins(15, 30, 15);
        $this->SetAutoPageBreak(true, 20);
        $this->AddPage();

        // ── Tabla: totales por día ──────────────────────────────────────────
        $this->SetFont('helvetica', 'B', 11);
        $this->Cell(0, 7, 'Totales por día', 0, 1, 'L');
        $this->Ln(1);

        $this->SetFont('helvetica', 'B', 9);
        $this->SetFillColor(230, 230, 230);
        $this->Cell(60, 7, 'Fecha', 1, 0, 'C', true);
        $this->Cell(60, 7, 'Cantidad de pagos', 1, 0, 'C', true);
        $this->Cell(60, 7, 'Total (Bs.)', 1, 1, 'C', true);

        $this->SetFont('helvetica', '', 9);
        foreach ($diarios as $fila) {
            $this->Cell(60, 6, date('d/m/Y', strtotime($fila["dia"])), 1, 0, 'C');
            $this->Cell(60, 6, $fila["cantidad_pagos"], 1, 0, 'C');
            $this->Cell(60, 6, number_format($fila["total"], 2), 1, 1, 'R');
        }
        if (empty($diarios)) {
            $this->SetFont('helvetica', 'I', 9);
            $this->Cell(0, 8, 'No se registraron pagos en este rango.', 1, 1, 'C');
        }

        $this->Ln(6);

        // ── Tabla: detalle de cada pago ──────────────────────────────────────
        $this->SetFont('helvetica', 'B', 11);
        $this->Cell(0, 7, 'Detalle de pagos', 0, 1, 'L');
        $this->Ln(1);

        $this->SetFont('helvetica', 'B', 9);
        $this->SetFillColor(230, 230, 230);
        $anchoFecha = 30; $anchoMetodo = 30; $anchoCliente = 75; $anchoMonto = 25;

        $this->Cell($anchoFecha, 7, 'Fecha', 1, 0, 'C', true);
        $this->Cell($anchoMetodo, 7, 'Método', 1, 0, 'C', true);
        $this->Cell($anchoCliente, 7, 'Cliente', 1, 0, 'C', true);
        $this->Cell($anchoMonto, 7, 'Monto (Bs.)', 1, 1, 'C', true);

        $this->SetFont('helvetica', '', 9);
        $fill = false;
        $diaAnterior = null;

        foreach ($detalle as $fila) {
            $diaActual = date('Y-m-d', strtotime($fila["fecha"]));
            if ($diaActual !== $diaAnterior) {
                $fill = !$fill;
                $diaAnterior = $diaActual;
            }
            $this->SetFillColor($fill ? 245 : 255, $fill ? 245 : 255, $fill ? 245 : 255);

            $this->Cell($anchoFecha, 6, date('d/m/Y H:i', strtotime($fila["fecha"])), 1, 0, 'C', true);
            $this->Cell($anchoMetodo, 6, ucfirst($fila["metodo_pago"]), 1, 0, 'C', true);
            $this->Cell($anchoCliente, 6, $fila["cliente"], 1, 0, 'L', true);
            $this->Cell($anchoMonto, 6, number_format($fila["costo_total"], 2), 1, 1, 'R', true);
        }
        if (empty($detalle)) {
            $this->SetFont('helvetica', 'I', 9);
            $this->Cell(0, 8, 'No se registraron pagos en este rango.', 1, 1, 'C');
        }

        $this->Ln(6);

        // ── Tabla: resumen por método de pago ────────────────────────────────
        $this->SetFont('helvetica', 'B', 11);
        $this->Cell(0, 7, 'Resumen por método de pago', 0, 1, 'L');
        $this->Ln(1);

        $this->SetFont('helvetica', 'B', 9);
        $this->SetFillColor(230, 230, 230);
        $this->Cell(60, 7, 'Método de pago', 1, 0, 'C', true);
        $this->Cell(45, 7, 'Cantidad de pagos', 1, 0, 'C', true);
        $this->Cell(55, 7, 'Total (Bs.)', 1, 1, 'C', true);

        foreach ($resumen as $fila) {
            $esTotalGeneral = ($fila["metodo_pago"] === 'TOTAL GENERAL');
            if ($esTotalGeneral) {
                $this->SetFont('helvetica', 'B', 9);
                $this->SetFillColor(220, 220, 220);
            } else {
                $this->SetFont('helvetica', '', 9);
                $this->SetFillColor(255, 255, 255);
            }
            $this->Cell(60, 6, ucfirst($fila["metodo_pago"]), 1, 0, 'L', true);
            $this->Cell(45, 6, $fila["cantidad_pagos"], 1, 0, 'C', true);
            $this->Cell(55, 6, number_format($fila["total"], 2), 1, 1, 'R', true);
        }
    }
}