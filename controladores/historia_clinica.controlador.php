<?php
class ControladorHistoriaClinica {
    static public function ctrGenerarPDF() {
        if (!isset($_GET['generarHistoriaClinica'])) return;
        if (session_status() === PHP_SESSION_NONE) session_start();
        header('Cache-Control: private, no-store, max-age=0');
        if (($_SESSION['iniciarSesion'] ?? '') !== 'ok' || empty($_SESSION['IdUsuario'])) {
            http_response_code(401);
            exit('Debes iniciar sesión para consultar la historia clínica.');
        }
        $id = filter_var($_GET['generarHistoriaClinica'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($id === false) {
            http_response_code(400);
            exit('Paciente no válido.');
        }
        try {
            if (!ModeloHistoriaClinica::mdlPuedeVerHistoria((int) $_SESSION['IdUsuario'])) {
                http_response_code(403);
                exit('No tienes permiso para consultar la historia clínica.');
            }
            $historia = ModeloHistoriaClinica::mdlObtenerHistoria($id);
            if (!$historia) {
                http_response_code(404);
                exit('Paciente no encontrado.');
            }
            session_write_close();
            require_once __DIR__ . '/../vistas/modulos/reportes/HistoriaClinicaPDF.php';
            $generado = new DateTimeImmutable('now', new DateTimeZone('America/La_Paz'));
            $pdf = new HistoriaClinicaPDF();
            $pdf->generarHistoria($historia, $generado);
            // Se entrega al navegador; no se escribe en la BD ni se deja una copia pública.
            $pdf->Output('historia_clinica_' . $id . '.pdf', 'I');
            exit;
        } catch (Throwable $e) {
            error_log('Error al generar historia clinica; codigo=' . $e->getCode());
            http_response_code(500);
            exit('No se pudo generar la historia clínica. Intenta nuevamente.');
        }
    }
}
