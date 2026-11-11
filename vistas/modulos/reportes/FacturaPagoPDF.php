<?php
require_once __DIR__ . "/../../../vendor/autoload.php";

class FacturaPagoPDF extends TCPDF {

    private $numeroFactura;

    public function setNumeroFactura($num) {
        $this->numeroFactura = $num;
    }

    public function Header() {
        $this->SetFont('helvetica', 'B', 14);
        $this->Cell(0, 8, 'Marie Stopes Bolivia', 0, 1, 'C');
        $this->SetFont('helvetica', '', 11);
        $this->Cell(0, 6, 'Comprobante de Pago', 0, 1, 'C');
        if ($this->numeroFactura) {
            $this->SetFont('helvetica', '', 9);
            $this->Cell(0, 5, 'N° ' . str_pad($this->numeroFactura, 6, '0', STR_PAD_LEFT), 0, 1, 'C');
        }
        $this->Ln(2);
        $this->SetLineWidth(0.3);
        $this->Line(15, $this->GetY(), 195, $this->GetY());
        $this->Ln(4);
    }

    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 5, 'Documento generado por el sistema - no válido como factura fiscal', 0, 0, 'L');
        $this->Cell(0, 5, 'Página ' . $this->getAliasNumPage() . ' / ' . $this->getAliasNbPages(), 0, 0, 'R');
    }

    public function generarFactura(array $datos, array $lineas) {
        $this->setNumeroFactura($datos["id_servicio_prestado"]);
        $this->SetCreator('Marie Stopes Bolivia - Sistema Clínico');
        $this->SetTitle('Comprobante de Pago N° ' . $datos["id_servicio_prestado"]);
        $this->SetMargins(15, 30, 15);
        $this->SetAutoPageBreak(true, 20);
        $this->AddPage();

        // ── Datos del cliente / paciente ─────────────────────────────────────
        $this->SetFont('helvetica', 'B', 10);
        $this->Cell(0, 6, 'Fecha: ' . date('d/m/Y H:i', strtotime($datos["fecha"])), 0, 1, 'L');

        $nombrePaciente = trim(($datos["paciente_nombre"] ?? "") . " " . ($datos["paciente_apellidos"] ?? ""));
        if ($nombrePaciente !== "") {
            $this->Cell(0, 6, 'Paciente: ' . $nombrePaciente . ($datos["paciente_ci"] ? ' (CI: ' . $datos["paciente_ci"] . ')' : ''), 0, 1, 'L');
        }
        if (!empty($datos["cliente_nombre"])) {
            $this->Cell(0, 6, 'Cliente: ' . $datos["cliente_nombre"] . ($datos["cliente_nit"] ? ' — CI: ' . $datos["cliente_nit"] : ''), 0, 1, 'L');
        }
        $this->Cell(0, 6, 'Método de pago: ' . $datos["metodo_pago"], 0, 1, 'L');
        $this->Ln(4);

        // ── Tabla de ítems ────────────────────────────────────────────────────
        $this->SetFont('helvetica', 'B', 9);
        $this->SetFillColor(230, 230, 230);
        $anchoConcepto = 90; $anchoCantidad = 25; $anchoPrecio = 30; $anchoSubtotal = 35;

        $this->Cell($anchoConcepto, 7, 'Concepto', 1, 0, 'C', true);
        $this->Cell($anchoCantidad, 7, 'Cant.', 1, 0, 'C', true);
        $this->Cell($anchoPrecio, 7, 'Precio', 1, 0, 'C', true);
        $this->Cell($anchoSubtotal, 7, 'Subtotal', 1, 1, 'C', true);

        $this->SetFont('helvetica', '', 9);
        foreach ($lineas as $l) {
            $nombre = $l["nombre_servicio"] ?? $l["nombre_examen"] ?? "—";
            $this->Cell($anchoConcepto, 6, $nombre, 1, 0, 'L');
            $this->Cell($anchoCantidad, 6, $l["cantidad"], 1, 0, 'C');
            $this->Cell($anchoPrecio, 6, number_format($l["precio_final"], 2), 1, 0, 'R');
            $this->Cell($anchoSubtotal, 6, number_format($l["subtotal"], 2), 1, 1, 'R');
        }

        $this->Ln(4);

        // ── Total ─────────────────────────────────────────────────────────────
        $this->SetFont('helvetica', 'B', 11);
        $this->SetFillColor(220, 220, 220);
        $this->Cell(145, 8, 'TOTAL', 1, 0, 'R', true);
        $this->Cell(35, 8, 'Bs. ' . number_format($datos["costo_total"], 2), 1, 1, 'R', true);
    }
}