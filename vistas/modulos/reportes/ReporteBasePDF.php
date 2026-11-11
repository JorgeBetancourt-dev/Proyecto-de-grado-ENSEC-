<?php
require_once __DIR__ . "/../../../vendor/autoload.php";

/**
 * Clase base de los reportes de Consultas y Citas.
 * Concentra el encabezado, el pie de página y las utilidades de tabla
 * (encabezado, filas, filas de grupo, salto de página y resumen con total).
 */
class ReporteBasePDF extends TCPDF {

    protected $tituloReporte    = '';
    protected $lineasSubtitulo  = [];
    protected $columnasActuales = [];

    // Encabezado de cada página
    public function Header() {
        $this->SetFont('helvetica', 'B', 14);
        $this->Cell(0, 8, 'Marie Stopes Bolivia', 0, 1, 'C');
        $this->SetFont('helvetica', '', 11);
        $this->Cell(0, 6, $this->tituloReporte, 0, 1, 'C');
        $this->SetFont('helvetica', '', 9);
        foreach ($this->lineasSubtitulo as $linea) {
            $this->Cell(0, 5, $linea, 0, 1, 'C');
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

    // Configura el documento y abre la primera página.
    // El margen superior crece con la cantidad de líneas de subtítulo del encabezado.
    protected function iniciar($tituloReporte, array $lineasSubtitulo, $tituloDocumento) {
        $this->tituloReporte   = $tituloReporte;
        $this->lineasSubtitulo = $lineasSubtitulo;
        $this->SetCreator('Marie Stopes Bolivia - Sistema Clínico');
        $this->SetTitle($tituloDocumento);
        $this->SetMargins(15, 25 + 5 * max(1, count($lineasSubtitulo)), 15);
        $this->SetAutoPageBreak(true, 20);
        $this->AddPage();
    }

    // Recorta el texto para que quepa en la celda (usa la fuente actual)
    protected function ajustar($texto, $ancho) {
        $texto = trim(preg_replace('/\s+/', ' ', (string)$texto));
        if ($this->GetStringWidth($texto) <= $ancho - 2) {
            return $texto;
        }
        while ($texto !== '' && $this->GetStringWidth($texto . '...') > $ancho - 2) {
            $texto = mb_substr($texto, 0, -1);
        }
        return $texto . '...';
    }

    // Dibuja el encabezado de una tabla. $columnas = [[etiqueta, ancho], ...]
    protected function encabezadoTabla(array $columnas) {
        $this->columnasActuales = $columnas;
        $this->SetFont('helvetica', 'B', 9);
        $this->SetFillColor(230, 230, 230);
        $ultima = count($columnas) - 1;
        foreach ($columnas as $i => $col) {
            $this->Cell($col[1], 7, $col[0], 1, $i === $ultima ? 1 : 0, 'C', true);
        }
        $this->SetFont('helvetica', '', 9);
    }

    // Si no cabe una fila más, abre otra página y repite el encabezado de la tabla
    protected function asegurarEspacio($alto) {
        if ($this->GetY() + $alto > $this->getPageHeight() - $this->getBreakMargin()) {
            $this->AddPage();
            if (!empty($this->columnasActuales)) {
                $this->encabezadoTabla($this->columnasActuales);
            }
        }
    }

    // Fila de datos con las columnas de la tabla actual
    protected function filaTabla(array $valores, array $alineaciones, $fill = false, $negrita = false) {
        $this->asegurarEspacio(6);
        $this->SetFont('helvetica', $negrita ? 'B' : '', 9);
        $gris = $fill ? 245 : 255;
        $this->SetFillColor($gris, $gris, $gris);
        $ultima = count($this->columnasActuales) - 1;
        foreach ($this->columnasActuales as $i => $col) {
            $this->Cell(
                $col[1], 6,
                $this->ajustar($valores[$i] ?? '', $col[1]),
                1, $i === $ultima ? 1 : 0,
                $alineaciones[$i] ?? 'L', true
            );
        }
    }

    // Fila que ocupa todo el ancho de la tabla (separador de grupo, ej. por médico)
    protected function filaGrupo($texto) {
        $this->asegurarEspacio(14);
        $ancho = array_sum(array_column($this->columnasActuales, 1));
        $this->SetFont('helvetica', 'B', 9);
        $this->SetFillColor(210, 220, 240);
        $this->Cell($ancho, 7, $this->ajustar($texto, $ancho), 1, 1, 'L', true);
    }

    protected function mensajeVacio($texto) {
        $this->SetFont('helvetica', 'I', 9);
        $this->Cell(0, 8, $texto, 1, 1, 'C');
    }

    // Tabla resumen con fila de total. $filas = [[etiqueta, n1, n2, ...], ...]
    // La fila final suma las columnas numéricas (desde la segunda en adelante).
    protected function tablaResumen($titulo, array $columnas, array $filas, $etiquetaTotal) {
        $this->columnasActuales = [];
        $this->Ln(6);
        $this->asegurarEspacio(16 + 6 * (count($filas) + 1));

        $this->SetFont('helvetica', 'B', 11);
        $this->Cell(0, 7, $titulo, 0, 1, 'L');
        $this->Ln(1);
        $this->encabezadoTabla($columnas);

        $n = count($columnas);
        $totales = array_fill(0, $n, 0);
        $alineaciones = array_fill(0, $n, 'C');
        $alineaciones[0] = 'L';

        foreach ($filas as $fila) {
            $this->filaTabla($fila, $alineaciones);
            for ($j = 1; $j < $n; $j++) {
                $totales[$j] += $fila[$j];
            }
        }
        $totales[0] = $etiquetaTotal;
        $this->filaTabla($totales, $alineaciones, true, true);
    }
}