<?php
class ControladorConsultas {

    // ── Consulta / triaje (llamados directo desde las vistas, no AJAX) ────────

    static public function ctrIniciarAtencion($id_cita) {
        return ModeloConsultas::mdlObtenerOcrearConsultaPorCita($id_cita);
    }

    static public function ctrObtenerConsultaPorCita($id_cita) {
        return ModeloConsultas::mdlObtenerConsultaPorCita($id_cita);
    }

    static public function ctrObtenerTriaje($id_consulta) {
        return ModeloConsultas::mdlObtenerTriajePorConsulta($id_consulta);
    }

    static public function ctrMostrarCatalogoExamenes() {
        return ModeloConsultas::mdlMostrarCatalogoExamenes();
    }

    static public function ctrMostrarExamenesConsulta($id_consulta) {
        return ModeloConsultas::mdlMostrarExamenesConsulta($id_consulta);
    }

    static public function ctrMostrarCatalogoMedicamentos() {
        return ModeloConsultas::mdlMostrarCatalogoMedicamentos();
    }

    static public function ctrMostrarTratamientosConsulta($id_consulta) {
        return ModeloConsultas::mdlMostrarTratamientosConsulta($id_consulta);
    }

    static public function ctrMostrarMedicamentosTratamiento($id_tratamiento) {
        return ModeloConsultas::mdlMostrarMedicamentosTratamiento($id_tratamiento);
    }
    static public function ctrMostrarMedicamentosConsulta($id_consulta) {
        return ModeloConsultas::mdlMostrarMedicamentosConsulta($id_consulta);
    }

    static public function ctrMostrarCatalogoExamenesConsulta() {
        return ModeloConsultas::mdlMostrarCatalogoExamenesConsulta();
    }

    // ── Guardar triaje (POST) ───────────────────────────────────────────────

    static public function ctrGuardarTriaje() {
        if (isset($_POST["triajeIdConsulta"])) {
            if (session_status() == PHP_SESSION_NONE) session_start();

            $datos = array(
                "id_consulta"             => (int) $_POST["triajeIdConsulta"],
                "tension_arterial"        => trim($_POST["triajeTensionArterial"]) !== "" ? trim($_POST["triajeTensionArterial"]) : null,
                "frecuencia_cardiaca"     => $_POST["triajeFrecuenciaCardiaca"]     !== "" ? (int) $_POST["triajeFrecuenciaCardiaca"]     : null,
                "frecuencia_respiratoria" => $_POST["triajeFrecuenciaRespiratoria"] !== "" ? (int) $_POST["triajeFrecuenciaRespiratoria"] : null,
                "temperatura"             => $_POST["triajeTemperatura"]           !== "" ? $_POST["triajeTemperatura"]                   : null,
                "saturacion"              => $_POST["triajeSaturacion"]            !== "" ? $_POST["triajeSaturacion"]                    : null,
                "peso"                    => $_POST["triajePeso"]                  !== "" ? $_POST["triajePeso"]                           : null,
            );

            ModeloConsultas::mdlGuardarTriaje($datos);
            header("Location: index.php?ruta=panel_consulta&id_consulta=" . $datos["id_consulta"]);
            exit;
        }
    }

    // ── Finalizar consulta (POST): guarda motivo/observaciones y marca la cita atendida ──

    static public function ctrGuardarConsulta() {
        if (isset($_POST["guardarConsultaIdConsulta"])) {
            if (session_status() == PHP_SESSION_NONE) session_start();

            $id_consulta = (int) $_POST["guardarConsultaIdConsulta"];
            $id_cita     = (int) $_POST["guardarConsultaIdCita"];
            $motivo      = trim($_POST["guardarConsultaMotivo"] ?? "");
            $observaciones = trim($_POST["guardarConsultaObservaciones"] ?? "");

            if ($motivo === "") {
                $_SESSION["accion_consulta"] = "consulta_error";
                header("Location: index.php?ruta=panel_consulta&id_consulta=" . $id_consulta);
                exit;
            }

            $r1 = ModeloConsultas::mdlActualizarConsulta($id_consulta, $motivo, $observaciones);
            $r2 = ControladorCitas::ctrMarcarAtendida($id_cita);

            if ($r1 == "ok" && $r2 == "ok") {
                $_SESSION["atencion_finalizada"] = "ok";
                header("Location: index.php?ruta=panel_atencion");
            } else {
                $_SESSION["accion_consulta"] = "consulta_error";
                header("Location: index.php?ruta=panel_consulta&id_consulta=" . $id_consulta);
            }
            exit;
        }
    }

    // ── Exámenes (POST) ─────────────────────────────────────────────────────

    static public function ctrAgregarExamen() {
        if (isset($_POST["agregarExamenIdConsulta"])) {
            if (session_status() == PHP_SESSION_NONE) session_start();
            $id_consulta = (int) $_POST["agregarExamenIdConsulta"];
            $id_examen   = (int) $_POST["agregarExamenIdExamen"];

            $respuesta = ModeloConsultas::mdlAgregarExamen($id_consulta, $id_examen);
            $_SESSION["accion_consulta"] = ($respuesta == "ok") ? "examen_agregado" : "consulta_error";
            header("Location: index.php?ruta=panel_consulta&id_consulta=" . $id_consulta);
            exit;
        }
    }

    static public function ctrQuitarExamen() {
        if (isset($_POST["quitarExamenIdDetalle"])) {
            if (session_status() == PHP_SESSION_NONE) session_start();
            $id_consulta = (int) $_POST["quitarExamenIdConsulta"];

            $respuesta = ModeloConsultas::mdlQuitarExamen((int) $_POST["quitarExamenIdDetalle"]);
            $_SESSION["accion_consulta"] = ($respuesta == "ok") ? "examen_quitado" : "consulta_error";
            header("Location: index.php?ruta=panel_consulta&id_consulta=" . $id_consulta);
            exit;
        }
    }

    // ── Tratamientos (POST) ─────────────────────────────────────────────────

    static public function ctrAgregarTratamiento() {
        if (isset($_POST["agregarTratamientoIdConsulta"])) {
            if (session_status() == PHP_SESSION_NONE) session_start();
            $id_consulta = (int) $_POST["agregarTratamientoIdConsulta"];

            $datos = array(
                "id_consulta"  => $id_consulta,
                "descripcion"  => trim($_POST["agregarTratamientoDescripcion"] ?? ""),
                "duracion"     => trim($_POST["agregarTratamientoDuracion"] ?? "") ?: null,
                "indicaciones" => trim($_POST["agregarTratamientoIndicaciones"] ?? "") ?: null,
            );

            if ($datos["descripcion"] === "") {
                $_SESSION["accion_consulta"] = "consulta_error";
                header("Location: index.php?ruta=panel_consulta&id_consulta=" . $id_consulta);
                exit;
            }

            $respuesta = ModeloConsultas::mdlAgregarTratamiento($datos);
            $_SESSION["accion_consulta"] = ($respuesta == "ok") ? "tratamiento_agregado" : "consulta_error";
            header("Location: index.php?ruta=panel_consulta&id_consulta=" . $id_consulta);
            exit;
        }
    }

    static public function ctrQuitarTratamiento() {
        if (isset($_POST["quitarTratamientoId"])) {
            if (session_status() == PHP_SESSION_NONE) session_start();
            $id_consulta = (int) $_POST["quitarTratamientoIdConsulta"];

            $respuesta = ModeloConsultas::mdlQuitarTratamiento((int) $_POST["quitarTratamientoId"]);
            $_SESSION["accion_consulta"] = ($respuesta == "ok") ? "tratamiento_quitado" : "consulta_error";
            header("Location: index.php?ruta=panel_consulta&id_consulta=" . $id_consulta);
            exit;
        }
    }

    static public function ctrAgregarMedicamentoTratamiento() {
        if (isset($_POST["agregarMedicamentoIdConsulta"], $_POST["agregarMedicamentoIdMedicamento"])) {
            if (session_status() == PHP_SESSION_NONE) session_start();
            $id_consulta = (int) $_POST["agregarMedicamentoIdConsulta"];

            $tratamiento = ModeloConsultas::mdlObtenerOcrearTratamiento($id_consulta);
            if (!$tratamiento) {
                $_SESSION["accion_consulta"] = "consulta_error";
                header("Location: index.php?ruta=panel_consulta&id_consulta=" . $id_consulta);
                exit;
            }

            $datos = array(
                "id_tratamiento" => (int) $tratamiento["id_tratamiento"],
                "id_medicamento" => (int) $_POST["agregarMedicamentoIdMedicamento"],
                "dosis"          => trim($_POST["agregarMedicamentoDosis"] ?? ""),
                "frecuencia"     => trim($_POST["agregarMedicamentoFrecuencia"] ?? ""),
                "duracion"       => trim($_POST["agregarMedicamentoDuracion"] ?? ""),
            );

            $respuesta = ModeloConsultas::mdlAgregarMedicamentoTratamiento($datos);
            $_SESSION["accion_consulta"] = ($respuesta == "ok") ? "medicamento_agregado" : "consulta_error";
            header("Location: index.php?ruta=panel_consulta&id_consulta=" . $id_consulta);
            exit;
        }
    }

    static public function ctrQuitarMedicamentoTratamiento() {
        if (isset($_POST["quitarMedicamentoIdDetalle"])) {
            if (session_status() == PHP_SESSION_NONE) session_start();
            $id_consulta = (int) $_POST["quitarMedicamentoIdConsulta"];

            $respuesta = ModeloConsultas::mdlQuitarMedicamentoTratamiento((int) $_POST["quitarMedicamentoIdDetalle"]);
            $_SESSION["accion_consulta"] = ($respuesta == "ok") ? "medicamento_quitado" : "consulta_error";
            header("Location: index.php?ruta=panel_consulta&id_consulta=" . $id_consulta);
            exit;
        }
    }
        // ── Navegación desde el dashboard: cita -> consulta (crea si no existe) ──

        static public function ctrIrAConsulta() {
        if (isset($_GET["irAConsulta"])) {
            $id_cita = (int) $_GET["irAConsulta"];
            if ($id_cita <= 0) { header("Location: index.php?ruta=inicio"); exit; }
            header("Location: index.php?ruta=panel_consulta&id_cita=" . $id_cita);
            exit;
        }
    }
        // ── Carrito de consulta en sesión (nada se escribe en BD hasta finalizar) ──

    private static function ctrConsultaCarritoInicializar($id_cita) {
        if (session_status() == PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION["carrito_consulta"]) || ($_SESSION["carrito_consulta"]["id_cita"] ?? null) != $id_cita) {
            $_SESSION["carrito_consulta"] = [
                "id_cita"       => $id_cita,
                "motivo"        => "",
                "observaciones" => "",
                "triaje" => [
                    "tension_arterial" => "", "frecuencia_cardiaca" => "",
                    "frecuencia_respiratoria" => "", "temperatura" => "",
                    "saturacion" => "", "peso" => "",
                ],
                "examenes" => [],      // [{id_linea, id_examen, nombre, tipo}]
                "medicamentos" => [],  // [{id_linea, id_medicamento, nombre, dosis, frecuencia, duracion}]
                "next_linea_id" => 1,
            ];
        }
        return $_SESSION["carrito_consulta"];
    }

    private static function ctrConsultaCarritoResponder() {
        echo json_encode(["status" => "ok", "carrito" => $_SESSION["carrito_consulta"]]);
        exit;
    }

    // Leer/inicializar el carrito para el render inicial de panel_consulta.php (no AJAX)
    static public function ctrConsultaCarritoLeer($id_cita) {
        return self::ctrConsultaCarritoInicializar($id_cita);
    }

    // AJAX: obtener el carrito actual
    static public function ctrConsultaCarritoObtener() {
        if (isset($_GET["consultaCarritoIdCita"])) {
            self::ctrConsultaCarritoInicializar((int) $_GET["consultaCarritoIdCita"]);
            self::ctrConsultaCarritoResponder();
        }
    }

    // AJAX: guardar motivo/observaciones/triaje (un solo endpoint, se llama con debounce desde el JS)
    static public function ctrConsultaCarritoActualizarCampos() {
        if (isset($_POST["consultaCarritoIdCita"], $_POST["consultaCampo"])) {
            self::ctrConsultaCarritoInicializar((int) $_POST["consultaCarritoIdCita"]);

            $campo = $_POST["consultaCampo"];
            $valor = trim($_POST["consultaValor"] ?? "");

            if (in_array($campo, ["motivo", "observaciones"])) {
                $_SESSION["carrito_consulta"][$campo] = $valor;
            } elseif (isset($_SESSION["carrito_consulta"]["triaje"][$campo])) {
                $_SESSION["carrito_consulta"]["triaje"][$campo] = $valor;
            }

            self::ctrConsultaCarritoResponder();
        }
    }

    // AJAX: agregar exámenes en lote (desde el modal de Laboratorios)
    static public function ctrConsultaCarritoAgregarExamenes() {
        if (isset($_POST["consultaCarritoIdCita"], $_POST["examenes"])) {
            self::ctrConsultaCarritoInicializar((int) $_POST["consultaCarritoIdCita"]);

            $examenes = json_decode($_POST["examenes"], true) ?: [];
            $existentesIds = array_column($_SESSION["carrito_consulta"]["examenes"], "id_examen");

            foreach ($examenes as $ex) {
                if (in_array((int) $ex["id_examen"], $existentesIds)) continue; // evita duplicados
                $id = $_SESSION["carrito_consulta"]["next_linea_id"]++;
                $_SESSION["carrito_consulta"]["examenes"][] = [
                    "id_linea"  => $id,
                    "id_examen" => (int) $ex["id_examen"],
                    "nombre"    => trim($ex["nombre"] ?? ""),
                    "tipo"      => trim($ex["tipo"] ?? ""),
                ];
            }
            self::ctrConsultaCarritoResponder();
        }
    }

    // AJAX: quitar un examen del carrito
    static public function ctrConsultaCarritoQuitarExamen() {
        if (isset($_POST["consultaCarritoIdCita"], $_POST["quitarExamenIdLinea"])) {
            self::ctrConsultaCarritoInicializar((int) $_POST["consultaCarritoIdCita"]);

            $idLinea = (int) $_POST["quitarExamenIdLinea"];
            $_SESSION["carrito_consulta"]["examenes"] = array_values(array_filter(
                $_SESSION["carrito_consulta"]["examenes"],
                function ($e) use ($idLinea) { return $e["id_linea"] !== $idLinea; }
            ));
            self::ctrConsultaCarritoResponder();
        }
    }

    // AJAX: agregar medicamentos en lote (desde el modal de Medicamentos — Bloque 4)
    static public function ctrConsultaCarritoAgregarMedicamentos() {
        if (isset($_POST["consultaCarritoIdCita"], $_POST["medicamentos"])) {
            self::ctrConsultaCarritoInicializar((int) $_POST["consultaCarritoIdCita"]);

            $medicamentos = json_decode($_POST["medicamentos"], true) ?: [];
            foreach ($medicamentos as $med) {
                $id = $_SESSION["carrito_consulta"]["next_linea_id"]++;
                $_SESSION["carrito_consulta"]["medicamentos"][] = [
                    "id_linea"       => $id,
                    "id_medicamento" => (int) $med["id_medicamento"],
                    "nombre"         => trim($med["nombre"] ?? ""),
                    "dosis"          => trim($med["dosis"] ?? ""),
                    "frecuencia"     => trim($med["frecuencia"] ?? ""),
                    "duracion"       => trim($med["duracion"] ?? ""),
                ];
            }
            self::ctrConsultaCarritoResponder();
        }
    }

    // AJAX: quitar un medicamento del carrito
    static public function ctrConsultaCarritoQuitarMedicamento() {
        if (isset($_POST["consultaCarritoIdCita"], $_POST["quitarMedicamentoIdLinea"])) {
            self::ctrConsultaCarritoInicializar((int) $_POST["consultaCarritoIdCita"]);

            $idLinea = (int) $_POST["quitarMedicamentoIdLinea"];
            $_SESSION["carrito_consulta"]["medicamentos"] = array_values(array_filter(
                $_SESSION["carrito_consulta"]["medicamentos"],
                function ($m) use ($idLinea) { return $m["id_linea"] !== $idLinea; }
            ));
            self::ctrConsultaCarritoResponder();
        }
    }
        // AJAX: catálogo de exámenes filtrado por categoría/búsqueda (sin precio)
    static public function ctrBuscarExamenesConsulta() {
        if (isset($_GET["buscarExamenesConsulta"])) {
            $termino = trim($_GET["buscarExamenesConsulta"]);
            $tipo    = trim($_GET["tipoExamen"] ?? "");
            $catalogo = ModeloConsultas::mdlMostrarCatalogoExamenesConsulta();

            $filtrado = array_values(array_filter($catalogo, function ($ex) use ($termino, $tipo) {
                $matchTermino = $termino === "" || stripos($ex["nombre"], $termino) !== false;
                $matchTipo    = $tipo === "" || $tipo === "todos" || $ex["tipo"] === $tipo;
                return $matchTermino && $matchTipo;
            }));

            echo json_encode($filtrado);
            exit;
        }
    }
        // AJAX: catálogo de medicamentos filtrado por búsqueda (sin precio)
    static public function ctrBuscarMedicamentosConsulta() {
        if (isset($_GET["buscarMedicamentosConsulta"])) {
            $termino = trim($_GET["buscarMedicamentosConsulta"]);
            $catalogo = ModeloConsultas::mdlMostrarCatalogoMedicamentos();

            $filtrado = array_values(array_filter($catalogo, function ($m) use ($termino) {
                return $termino === "" || stripos($m["nombre"], $termino) !== false;
            }));

            echo json_encode($filtrado);
            exit;
        }
    }
        // ── Confirmar la consulta completa (AJAX, escritura atómica) ────────────

    static public function ctrConsultaFinalizar() {
        if (isset($_POST["consultaFinalizarIdCita"])) {
            if (session_status() == PHP_SESSION_NONE) session_start();

            $id_cita = (int) $_POST["consultaFinalizarIdCita"];
            $carrito = $_SESSION["carrito_consulta"] ?? null;

            if (!$carrito || ($carrito["id_cita"] ?? null) != $id_cita) {
                echo json_encode(["status" => "error", "mensaje" => "No hay una consulta en curso para esta cita"]);
                exit;
            }

            if (trim($carrito["motivo"]) === "") {
                echo json_encode(["status" => "error", "mensaje" => "El motivo de consulta es obligatorio"]);
                exit;
            }

            $id_consulta = ModeloConsultas::mdlConfirmarCarrito($carrito);

            if (!$id_consulta) {
                echo json_encode(["status" => "error", "mensaje" => "No se pudo registrar la consulta, intenta de nuevo"]);
                exit;
            }

            unset($_SESSION["carrito_consulta"]);
            echo json_encode(["status" => "ok", "id_consulta" => $id_consulta]);
            exit;
        }
    }
}