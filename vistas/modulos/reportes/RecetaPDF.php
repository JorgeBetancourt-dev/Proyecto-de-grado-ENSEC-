<?php
require_once __DIR__ . "/../../../vendor/autoload.php";

class RecetaPDF extends TCPDF {

    public function Header() {
        $this->SetFont('helvetica', 'B', 14);
        $this->Cell(0, 8, 'Marie Stopes Bolivia', 0, 1, 'C');
        $this->SetFont('helvetica', '', 11);
        $this->Cell(0, 6, 'Receta Médica', 0, 1, 'C');
        $this->Ln(2);
        $this->SetLineWidth(0.3);
        $this->Line(15, $this->GetY(), 195, $this->GetY());
        $this->Ln(4);
    }

    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 5, 'Documento generado por el sistema', 0, 0, 'L');
        $this->Cell(0, 5, 'Página ' . $this->getAliasNumPage() . ' / ' . $this->getAliasNbPages(), 0, 0, 'R');
    }

    public function generarReceta(array $datos, array $medicamentos) {
        $this->SetCreator('Marie Stopes Bolivia - Sistema Clínico');
        $this->SetTitle('Receta Médica');
        $this->SetMargins(15, 30, 15);
        $this->SetAutoPageBreak(true, 20);
        $this->AddPage();

        $edad = "-";
        if (!empty($datos["fecha_nacimiento"])) {
            $edad = date_diff(date_create($datos["fecha_nacimiento"]), date_create("today"))->y;
        }

        $this->SetFont('helvetica', 'B', 10);
        $this->Cell(0, 6, 'Fecha: ' . date('d/m/Y', strtotime($datos["fecha_hora"])), 0, 1, 'L');
        $this->Cell(0, 6, 'Paciente: ' . trim($datos["paciente_nombre"] . " " . $datos["paciente_apellidos"]) . ' (CI: ' . $datos["ci"] . ')', 0, 1, 'L');
        $this->Cell(0, 6, 'Edad: ' . $edad . ' años', 0, 1, 'L');
        $this->Cell(0, 6, 'Médico: Dr(a). ' . trim($datos["medico_nombre"] . " " . $datos["medico_apellido"]), 0, 1, 'L');
        $this->Ln(4);

        $this->SetFont('helvetica', 'B', 9);
        $this->SetFillColor(230, 230, 230);
        $anchoMed = 65; $anchoDosis = 35; $anchoFrec = 45; $anchoDur = 35;

        $this->Cell($anchoMed, 7, 'Medicamento', 1, 0, 'C', true);
        $this->Cell($anchoDosis, 7, 'Dosis', 1, 0, 'C', true);
        $this->Cell($anchoFrec, 7, 'Frecuencia', 1, 0, 'C', true);
        $this->Cell($anchoDur, 7, 'Duración', 1, 1, 'C', true);

        $this->SetFont('helvetica', '', 9);
        foreach ($medicamentos as $m) {
            $this->Cell($anchoMed, 6, $m["nombre"], 1, 0, 'L');
            $this->Cell($anchoDosis, 6, $m["dosis"], 1, 0, 'C');
            $this->Cell($anchoFrec, 6, $m["frecuencia"], 1, 0, 'C');
            $this->Cell($anchoDur, 6, $m["duracion"] ?: '-', 1, 1, 'C');
        }
    }
}