<?php
class ControladorReportes {

    // ── Reporte de pagos del día ────────────────────────────────────────────

    static public function ctrDetallePagosPorFecha($fecha) {
        return ModeloReportes::mdlDetallePagosPorFecha($fecha);
    }

    static public function ctrResumenPagosPorFecha($fecha) {
        return ModeloReportes::mdlResumenPagosPorFecha($fecha);
    }

        // ── Reporte de pagos entre fechas ────────────────────────────────────────

    static public function ctrDetallePagosPorRango($fechaInicio, $fechaFin) {
        return ModeloReportes::mdlDetallePagosPorRango($fechaInicio, $fechaFin);
    }

    static public function ctrResumenPagosPorRango($fechaInicio, $fechaFin) {
        return ModeloReportes::mdlResumenPagosPorRango($fechaInicio, $fechaFin);
    }

    static public function ctrTotalesDiariosPorRango($fechaInicio, $fechaFin) {
        return ModeloReportes::mdlTotalesDiariosPorRango($fechaInicio, $fechaFin);
    }

        // ── Reporte de pagos por fecha + método específico ────────────────────────

    static public function ctrDetallePagosPorRangoYMetodo($fechaInicio, $fechaFin, $metodoPago) {
        return ModeloReportes::mdlDetallePagosPorRangoYMetodo($fechaInicio, $fechaFin, $metodoPago);
    }

    static public function ctrTotalesDiariosPorRangoYMetodo($fechaInicio, $fechaFin, $metodoPago) {
        return ModeloReportes::mdlTotalesDiariosPorRangoYMetodo($fechaInicio, $fechaFin, $metodoPago);
    }

    static public function ctrTotalGeneralPorRangoYMetodo($fechaInicio, $fechaFin, $metodoPago) {
        return ModeloReportes::mdlTotalGeneralPorRangoYMetodo($fechaInicio, $fechaFin, $metodoPago);
    }

    static public function ctrGenerarReportePagosPorMetodo() {
        if (isset($_GET["generarReportePagosPorMetodo"])) {
            $fechaInicio = $_GET["fecha_inicio"] ?? "";
            $fechaFin    = $_GET["fecha_fin"]    ?? "";
            $metodoPago  = $_GET["metodo_pago"]  ?? "";

            $metodosValidos = ["efectivo", "tarjeta", "qr", "transferencia"];

            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaInicio) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaFin)) {
                http_response_code(400);
                echo "Rango de fechas inválido.";
                exit;
            }
            if ($fechaInicio > $fechaFin) {
                http_response_code(400);
                echo "La fecha de inicio no puede ser posterior a la fecha de fin.";
                exit;
            }
            if (!in_array($metodoPago, $metodosValidos)) {
                http_response_code(400);
                echo "Método de pago inválido.";
                exit;
            }

            require_once __DIR__ . "/../vistas/modulos/reportes/ReportePagosPorMetodoPDF.php";

            $detalle = self::ctrDetallePagosPorRangoYMetodo($fechaInicio, $fechaFin, $metodoPago);
            $diarios = self::ctrTotalesDiariosPorRangoYMetodo($fechaInicio, $fechaFin, $metodoPago);
            $totalGeneral = self::ctrTotalGeneralPorRangoYMetodo($fechaInicio, $fechaFin, $metodoPago);

            $pdf = new ReportePagosPorMetodoPDF();
            $pdf->generarReportePagosPorMetodo($fechaInicio, $fechaFin, $metodoPago, $detalle, $diarios, $totalGeneral);

            $carpetaDestino = __DIR__ . "/../reportes_generados/pagos_por_metodo/";
            if (!is_dir($carpetaDestino)) {
                mkdir($carpetaDestino, 0755, true);
            }

            $nombreArchivo = "reporte_pagos_" . $metodoPago . "_" . $fechaInicio . "_a_" . $fechaFin . ".pdf";
            $rutaCompleta  = $carpetaDestino . $nombreArchivo;

            $pdf->Output($rutaCompleta, 'F');
            $pdf->Output($nombreArchivo, 'I');
            exit;
        }
    }

    static public function ctrGenerarReportePagosRango() {
        if (isset($_GET["generarReportePagosRango"])) {
            $fechaInicio = $_GET["fecha_inicio"] ?? "";
            $fechaFin    = $_GET["fecha_fin"]    ?? "";

            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaInicio) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaFin)) {
                http_response_code(400);
                echo "Rango de fechas inválido.";
                exit;
            }
            if ($fechaInicio > $fechaFin) {
                http_response_code(400);
                echo "La fecha de inicio no puede ser posterior a la fecha de fin.";
                exit;
            }

            require_once __DIR__ . "/../vistas/modulos/reportes/ReportePagosRangoPDF.php";

            $detalle = self::ctrDetallePagosPorRango($fechaInicio, $fechaFin);
            $resumen = self::ctrResumenPagosPorRango($fechaInicio, $fechaFin);
            $diarios = self::ctrTotalesDiariosPorRango($fechaInicio, $fechaFin);

            $pdf = new ReportePagosRangoPDF();
            $pdf->generarReportePagosRango($fechaInicio, $fechaFin, $detalle, $diarios, $resumen);

            $carpetaDestino = __DIR__ . "/../reportes_generados/pagos_rango/";
            if (!is_dir($carpetaDestino)) {
                mkdir($carpetaDestino, 0755, true);
            }

            $nombreArchivo = "reporte_pagos_" . $fechaInicio . "_a_" . $fechaFin . ".pdf";
            $rutaCompleta  = $carpetaDestino . $nombreArchivo;

            $pdf->Output($rutaCompleta, 'F');
            $pdf->Output($nombreArchivo, 'I');
            exit;
        }
    }

    // ── Generar y entregar el PDF del reporte de pagos del día ──────────────

    static public function ctrGenerarReportePagosDia() {
        if (isset($_GET["generarReportePagosDia"])) {
            $fecha = $_GET["fecha"] ?? date("Y-m-d");

            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
                http_response_code(400);
                echo "Fecha inválida.";
                exit;
            }

            require_once __DIR__ . "/../vistas/modulos/reportes/ReportePagosPDF.php";

            $detalle = self::ctrDetallePagosPorFecha($fecha);
            $resumen = self::ctrResumenPagosPorFecha($fecha);

            $pdf = new ReportePagosPDF();
            $pdf->generarReportePagos($fecha, $detalle, $resumen);

            // Carpeta de destino: reportes_generados/pagos_dia/
            $carpetaDestino = __DIR__ . "/../reportes_generados/pagos_dia/";
            if (!is_dir($carpetaDestino)) {
                mkdir($carpetaDestino, 0755, true);
            }

            $nombreArchivo = "reporte_pagos_" . $fecha . ".pdf";
            $rutaCompleta  = $carpetaDestino . $nombreArchivo;

            // Guarda una copia en disco (historial) y además la envía al navegador
            $pdf->Output($rutaCompleta, 'F');
            $pdf->Output($nombreArchivo, 'I');
            exit;
        }
    }

    // ══════════════════════════════════════════════════════════════════════
    //  Utilidades compartidas por los reportes de Consultas y Citas
    // ══════════════════════════════════════════════════════════════════════

    // Médicos activos para el select de los modales del panel
    static public function ctrMedicosActivos() {
        return ModeloReportes::mdlMedicosActivos();
    }

    // Valida el rango fecha_inicio / fecha_fin del GET. Devuelve [inicio, fin]
    static private function validarRango() {
        $fechaInicio = $_GET["fecha_inicio"] ?? "";
        $fechaFin    = $_GET["fecha_fin"]    ?? "";

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaInicio) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaFin)) {
            http_response_code(400);
            echo "Rango de fechas inválido.";
            exit;
        }
        if ($fechaInicio > $fechaFin) {
            http_response_code(400);
            echo "La fecha de inicio no puede ser posterior a la fecha de fin.";
            exit;
        }
        return [$fechaInicio, $fechaFin];
    }

    // Lee id_medico del GET. Devuelve [id, nombre]; id 0 y nombre null = todos los médicos
    static private function obtenerMedico() {
        $idMedico = isset($_GET["id_medico"]) ? (int)$_GET["id_medico"] : 0;
        if ($idMedico <= 0) {
            return [0, null];
        }

        $nombre = ModeloReportes::mdlNombreMedico($idMedico);
        if (!$nombre) {
            http_response_code(400);
            echo "Médico inválido.";
            exit;
        }
        return [$idMedico, $nombre];
    }

    // Etiqueta del médico para el nombre del archivo
    static private function etiquetaMedico($idMedico) {
        return $idMedico > 0 ? "medico" . $idMedico : "todos";
    }

    // Guarda una copia en reportes_generados/<subcarpeta>/ y envía el PDF al navegador
    static private function entregarPDF($pdf, $subcarpeta, $nombreArchivo) {
        $carpetaDestino = __DIR__ . "/../reportes_generados/" . $subcarpeta . "/";
        if (!is_dir($carpetaDestino)) {
            mkdir($carpetaDestino, 0755, true);
        }

        $pdf->Output($carpetaDestino . $nombreArchivo, 'F');
        $pdf->Output($nombreArchivo, 'I');
        exit;
    }

    // ══════════════════════════════════════════════════════════════════════
    //  Reportes de consultas
    // ══════════════════════════════════════════════════════════════════════

    static public function ctrConsultasReporte($fechaInicio, $fechaFin, $idMedico) {
        return ModeloReportes::mdlConsultasReporte($fechaInicio, $fechaFin, $idMedico);
    }

    static public function ctrConsultasRecetasReporte($fechaInicio, $fechaFin, $idMedico) {
        return ModeloReportes::mdlConsultasRecetasReporte($fechaInicio, $fechaFin, $idMedico);
    }

    static public function ctrConsultasExamenesReporte($fechaInicio, $fechaFin, $idMedico, $tipoExamen) {
        return ModeloReportes::mdlConsultasExamenesReporte($fechaInicio, $fechaFin, $idMedico, $tipoExamen);
    }

    // Consultas por médico
    static public function ctrGenerarReporteConsultasPorMedico() {
        if (isset($_GET["generarReporteConsultasPorMedico"])) {
            list($fechaInicio, $fechaFin) = self::validarRango();
            list($idMedico, $nombreMedico) = self::obtenerMedico();

            require_once __DIR__ . "/../vistas/modulos/reportes/ReporteConsultasPDF.php";

            $consultas = self::ctrConsultasReporte($fechaInicio, $fechaFin, $idMedico);

            $pdf = new ReporteConsultasPDF();
            $pdf->generarReporteConsultas($fechaInicio, $fechaFin, $nombreMedico, $consultas);

            self::entregarPDF(
                $pdf,
                "consultas_por_medico",
                "reporte_consultas_" . self::etiquetaMedico($idMedico) . "_" . $fechaInicio . "_a_" . $fechaFin . ".pdf"
            );
        }
    }

    // Consultas por médico con recetas
    static public function ctrGenerarReporteConsultasConRecetas() {
        if (isset($_GET["generarReporteConsultasConRecetas"])) {
            list($fechaInicio, $fechaFin) = self::validarRango();
            list($idMedico, $nombreMedico) = self::obtenerMedico();

            require_once __DIR__ . "/../vistas/modulos/reportes/ReporteConsultasRecetasPDF.php";

            $filas = self::ctrConsultasRecetasReporte($fechaInicio, $fechaFin, $idMedico);

            $pdf = new ReporteConsultasRecetasPDF();
            $pdf->generarReporteRecetas($fechaInicio, $fechaFin, $nombreMedico, $filas);

            self::entregarPDF(
                $pdf,
                "consultas_recetas",
                "reporte_consultas_recetas_" . self::etiquetaMedico($idMedico) . "_" . $fechaInicio . "_a_" . $fechaFin . ".pdf"
            );
        }
    }

    // Consultas por médico con solicitudes de exámenes
    static public function ctrGenerarReporteConsultasConExamenes() {
        if (isset($_GET["generarReporteConsultasConExamenes"])) {
            list($fechaInicio, $fechaFin) = self::validarRango();
            list($idMedico, $nombreMedico) = self::obtenerMedico();

            $tipoExamen = $_GET["tipo_examen"] ?? "todos";
            if (!in_array($tipoExamen, ["todos", "laboratorio", "ecografia"])) {
                http_response_code(400);
                echo "Tipo de examen inválido.";
                exit;
            }

            require_once __DIR__ . "/../vistas/modulos/reportes/ReporteConsultasExamenesPDF.php";

            $filas = self::ctrConsultasExamenesReporte($fechaInicio, $fechaFin, $idMedico, $tipoExamen);

            $pdf = new ReporteConsultasExamenesPDF();
            $pdf->generarReporteExamenes($fechaInicio, $fechaFin, $nombreMedico, $tipoExamen, $filas);

            self::entregarPDF(
                $pdf,
                "consultas_examenes",
                "reporte_consultas_examenes_" . self::etiquetaMedico($idMedico) . "_" . $tipoExamen . "_" . $fechaInicio . "_a_" . $fechaFin . ".pdf"
            );
        }
    }

    // ══════════════════════════════════════════════════════════════════════
    //  Reportes de citas
    // ══════════════════════════════════════════════════════════════════════

    static public function ctrCitasReporte($fechaInicio, $fechaFin, $idMedico, $estado, $agruparPorMedico) {
        return ModeloReportes::mdlCitasReporte($fechaInicio, $fechaFin, $idMedico, $estado, $agruparPorMedico);
    }

    // Lógica común de los 5 reportes de citas; cada uno solo cambia estos parámetros
    static private function generarPdfCitas($bandera, $titulo, $estado, $modoMedico, $carpeta, $prefijoArchivo) {
        if (!isset($_GET[$bandera])) {
            return;
        }

        list($fechaInicio, $fechaFin) = self::validarRango();

        $idMedico = 0;
        $nombreMedico = null;
        if ($modoMedico) {
            list($idMedico, $nombreMedico) = self::obtenerMedico();
        }

        require_once __DIR__ . "/../vistas/modulos/reportes/ReporteCitasPDF.php";

        $citas = self::ctrCitasReporte($fechaInicio, $fechaFin, $idMedico, $estado, $modoMedico && $idMedico === 0);

        $pdf = new ReporteCitasPDF();
        $pdf->generarReporteCitas($titulo, $fechaInicio, $fechaFin, $modoMedico, $nombreMedico, $citas);

        $etiquetaMedico = $modoMedico ? self::etiquetaMedico($idMedico) . "_" : "";
        self::entregarPDF($pdf, $carpeta, $prefijoArchivo . "_" . $etiquetaMedico . $fechaInicio . "_a_" . $fechaFin . ".pdf");
    }

    static public function ctrGenerarReporteCitasEntreFechas() {
        self::generarPdfCitas(
            "generarReporteCitasEntreFechas", "Reporte de Citas entre Fechas",
            null, false, "citas_entre_fechas", "reporte_citas"
        );
    }

    static public function ctrGenerarReporteCitasPorMedico() {
        self::generarPdfCitas(
            "generarReporteCitasPorMedico", "Reporte de Citas por Médico",
            null, true, "citas_por_medico", "reporte_citas"
        );
    }

    static public function ctrGenerarReporteCitasAtendidas() {
        self::generarPdfCitas(
            "generarReporteCitasAtendidas", "Reporte de Citas Atendidas por Médico",
            "atendida", true, "citas_atendidas", "reporte_citas_atendidas"
        );
    }

    static public function ctrGenerarReporteCitasCanceladas() {
        self::generarPdfCitas(
            "generarReporteCitasCanceladas", "Reporte de Citas Canceladas por Médico",
            "cancelada", true, "citas_canceladas", "reporte_citas_canceladas"
        );
    }

    static public function ctrGenerarReporteCitasPendientes() {
        self::generarPdfCitas(
            "generarReporteCitasPendientes", "Reporte de Citas Pendientes por Médico",
            "pendiente", true, "citas_pendientes", "reporte_citas_pendientes"
        );
    }
}