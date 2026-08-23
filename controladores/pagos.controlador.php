<?php
class ControladorPagos {

    // ── Llamados directo desde panel_pago.php (no AJAX) ────────────────────

    static public function ctrIniciarCobro($id_paciente, $id_cita, $id_usuario) {
        return ModeloPagos::mdlObtenerOcrearPago($id_paciente, $id_cita, $id_usuario);
    }

    static public function ctrMostrarLineasPago($id_servicio_prestado) {
        return ModeloPagos::mdlMostrarLineasPago($id_servicio_prestado);
    }

    static public function ctrMostrarCatalogoServicios() {
        return ModeloPagos::mdlMostrarCatalogoServicios();
    }

    static public function ctrMostrarCatalogoExamenes() {
        return ModeloPagos::mdlMostrarCatalogoExamenes();
    }

    static public function ctrMostrarCatalogoMedicamentos() {
        return ModeloPagos::mdlMostrarCatalogoMedicamentos();
    }

    static public function ctrExamenesPendientesCobro($id_consulta, $id_servicio_prestado) {
        return ModeloPagos::mdlExamenesPendientesCobro($id_consulta, $id_servicio_prestado);
    }

    static public function ctrMedicamentosPendientesCobro($id_consulta, $id_servicio_prestado) {
        return ModeloPagos::mdlMedicamentosPendientesCobro($id_consulta, $id_servicio_prestado);
    }
        // Lectura directa del carrito para el render inicial de panel_pago.php (no AJAX)
    static public function ctrCarritoLeer() {
        return self::ctrCarritoObtenerOInicializar();
    }

    // Redirección común: si hay cita, vuelve al panel de la cita; si no, vuelve al cobro suelto
    static private function ctrRedireccionCobro($id_cita, $id_servicio_prestado) {
        if ($id_cita > 0) {
            header("Location: index.php?ruta=panel_servicio_prestado&id_cita=" . $id_cita);
        } else {
            header("Location: index.php?ruta=panel_pago&id_servicio_prestado=" . $id_servicio_prestado);
        }
        exit;
    }

    // ── Agregar línea (POST) ────────────────────────────────────────────────

       static public function ctrAgregarLinea() {
        if (isset($_POST["lineaIdServicioPrestado"])) {
            if (session_status() == PHP_SESSION_NONE) session_start();

            $tipo = $_POST["lineaTipo"] ?? ""; // 'servicio' | 'examen' | 'medicamento'
            $id_sp   = (int) $_POST["lineaIdServicioPrestado"];
            $id_cita = (int) ($_POST["lineaIdCita"] ?? 0);

            $datos = array(
                "id_servicio_prestado" => $id_sp,
                "id_servicio"          => $tipo === "servicio"    ? (int) $_POST["lineaIdReferencia"] : null,
                "id_examen"            => $tipo === "examen"      ? (int) $_POST["lineaIdReferencia"] : null,
                "id_medicamento"       => $tipo === "medicamento" ? (int) $_POST["lineaIdReferencia"] : null,
                "precio_final"         => $_POST["lineaPrecio"]   ?? "0",
                "cantidad"             => !empty($_POST["lineaCantidad"]) ? (int) $_POST["lineaCantidad"] : 1,
            );

            $respuesta = ModeloPagos::mdlAgregarLinea($datos);
            $_SESSION["accion_pago"] = ($respuesta == "ok") ? "linea_agregada" : "pago_error";
            self::ctrRedireccionCobro($id_cita, $id_sp);
        }
    }

    static public function ctrQuitarLinea() {
        if (isset($_POST["quitarLineaId"])) {
            if (session_status() == PHP_SESSION_NONE) session_start();

            $id_sp   = (int) $_POST["quitarLineaIdServicioPrestado"];
            $id_cita = (int) ($_POST["quitarLineaIdCita"] ?? 0);

            $respuesta = ModeloPagos::mdlQuitarLinea((int) $_POST["quitarLineaId"], $id_sp);
            $_SESSION["accion_pago"] = ($respuesta == "ok") ? "linea_quitada" : "pago_error";
            self::ctrRedireccionCobro($id_cita, $id_sp);
        }
    }

    // ── Asignar/cambiar paciente o cliente de un cobro (AJAX, flujo sin cita) ──

    static public function ctrAsignarPaciente() {
        if (isset($_POST["asignarPacienteIdServicioPrestado"], $_POST["asignarPacienteIdPaciente"])) {
            if (session_status() == PHP_SESSION_NONE) session_start();

            $id_sp       = (int) $_POST["asignarPacienteIdServicioPrestado"];
            $id_paciente = (int) $_POST["asignarPacienteIdPaciente"];

            $respuesta = ModeloPagos::mdlAsignarPaciente($id_sp, $id_paciente);
            echo json_encode(["status" => $respuesta]);
            exit;
        }
    }

    static public function ctrAsignarCliente() {
        if (isset($_POST["asignarClienteIdServicioPrestado"], $_POST["asignarClienteIdCliente"])) {
            if (session_status() == PHP_SESSION_NONE) session_start();

            $id_sp      = (int) $_POST["asignarClienteIdServicioPrestado"];
            $id_cliente = (int) $_POST["asignarClienteIdCliente"];

            $respuesta = ModeloPagos::mdlAsignarCliente($id_sp, $id_cliente);
            echo json_encode(["status" => $respuesta]);
            exit;
        }
    }

    // ── Registro rápido de paciente/cliente + asignación (AJAX, flujo sin cita) ──

    static public function ctrRegistrarPacienteRapido() {
        if (isset($_POST["nuevoPacienteRapidoIdServicioPrestado"], $_POST["nuevoPacienteRapidoNombre"], $_POST["nuevoPacienteRapidoCI"])) {
            if (session_status() == PHP_SESSION_NONE) session_start();

            $id_sp  = (int) $_POST["nuevoPacienteRapidoIdServicioPrestado"];
            $nombre = trim($_POST["nuevoPacienteRapidoNombre"]);
            $ci     = trim($_POST["nuevoPacienteRapidoCI"]);

            if ($nombre === "" || $ci === "") {
                echo json_encode(["status" => "error", "mensaje" => "Nombre y carnet son obligatorios"]);
                exit;
            }

            $datos = [
                "nombre"           => $nombre,
                "apellidos"        => trim($_POST["nuevoPacienteRapidoApellidos"] ?? ""),
                "ci"               => $ci,
                "grupo_sanguineo"  => trim($_POST["nuevoPacienteRapidoGrupoSanguineo"] ?? ""),
                "telefono"         => trim($_POST["nuevoPacienteRapidoTelefono"] ?? ""),
                "fecha_nacimiento" => trim($_POST["nuevoPacienteRapidoFechaNacimiento"] ?? ""),
                "direccion"        => trim($_POST["nuevoPacienteRapidoDireccion"] ?? ""),
            ];

            $id_paciente = ModeloPacientes::mdlCrearPacienteRapido($datos);
            if (!$id_paciente) {
                echo json_encode(["status" => "error", "mensaje" => "No se pudo registrar el paciente (verifica que el carnet no esté repetido)"]);
                exit;
            }

            ModeloPagos::mdlAsignarPaciente($id_sp, $id_paciente);
            echo json_encode(["status" => "ok", "id_paciente" => $id_paciente, "nombre" => $nombre, "ci" => $ci]);
            exit;
        }
    }

    static public function ctrRegistrarClienteRapido() {
        if (isset($_POST["nuevoClienteRapidoIdServicioPrestado"], $_POST["nuevoClienteRapidoNombre"], $_POST["nuevoClienteRapidoNit"])) {
            if (session_status() == PHP_SESSION_NONE) session_start();

            $id_sp  = (int) $_POST["nuevoClienteRapidoIdServicioPrestado"];
            $nombre = trim($_POST["nuevoClienteRapidoNombre"]);
            $nit    = trim($_POST["nuevoClienteRapidoNit"]);

            if ($nombre === "" || $nit === "") {
                echo json_encode(["status" => "error", "mensaje" => "Nombre y NIT son obligatorios"]);
                exit;
            }

            $id_cliente = ModeloClientes::mdlCrearClienteRapido($nombre, $nit);
            if (!$id_cliente) {
                echo json_encode(["status" => "error", "mensaje" => "No se pudo registrar el cliente"]);
                exit;
            }

            ModeloPagos::mdlAsignarCliente($id_sp, $id_cliente);
            echo json_encode(["status" => "ok", "id_cliente" => $id_cliente, "nombre" => $nombre, "nit" => $nit]);
            exit;
        }
    }

    static public function ctrFinalizarPago() {
        if (isset($_POST["finalizarPagoIdServicioPrestado"])) {
            if (session_status() == PHP_SESSION_NONE) session_start();

            $id_sp   = (int) $_POST["finalizarPagoIdServicioPrestado"];
            $id_cita = (int) ($_POST["finalizarPagoIdCita"] ?? 0);
            $metodo  = trim($_POST["finalizarPagoMetodo"] ?? "");
            $nombreCliente = trim($_POST["finalizarPagoClienteNombre"] ?? "");
            $nitCliente    = trim($_POST["finalizarPagoClienteNit"] ?? "");

            $pago = ModeloPagos::mdlObtenerPago($id_sp);

            if ($metodo === "" || !$pago || $pago["costo_total"] <= 0) {
                $_SESSION["accion_pago"] = "pago_error";
                self::ctrRedireccionCobro($id_cita, $id_sp);
            }

            // Por defecto se conserva el cliente ya asignado (flujo sin cita, vía buscador).
            // Solo se crea/busca uno nuevo si se escribió un nombre a mano (flujo con cita).
            $id_cliente = $pago["id_cliente"];
            if ($nombreCliente !== "") {
                $id_cliente = ModeloPagos::mdlBuscarOcrearCliente($nombreCliente, $nitCliente);
            }

            $respuesta = ModeloPagos::mdlFinalizarPago($id_sp, $metodo, $id_cliente);

            if ($respuesta == "ok") {
                $_SESSION["cobro_finalizado"] = "ok";
                if ($id_cita > 0) {
                    header("Location: index.php?ruta=panel_cita");
                } else {
                    header("Location: index.php?ruta=panel_pago");
                }
                exit;
            } else {
                $_SESSION["accion_pago"] = "pago_error";
                self::ctrRedireccionCobro($id_cita, $id_sp);
            }
        }
    }
        // ── Carrito en sesión (nada se escribe en BD hasta confirmar el pago) ──

    private static function ctrCarritoObtenerOInicializar() {
        if (session_status() == PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION["carrito_pago"])) {
            $_SESSION["carrito_pago"] = [
                "id_paciente" => null, "paciente_nombre" => null, "paciente_ci" => null,
                "id_cliente" => null, "cliente_nombre" => null, "cliente_nit" => null,
                "lineas" => [],
                "next_linea_id" => 1,
            ];
        }
        return $_SESSION["carrito_pago"];
    }

    private static function ctrCarritoTotal($carrito) {
        $total = 0;
        foreach ($carrito["lineas"] as $l) {
            $total += $l["precio"] * $l["cantidad"];
        }
        return $total;
    }

    private static function ctrCarritoResponder() {
        $carrito = self::ctrCarritoObtenerOInicializar();
        echo json_encode([
            "status"  => "ok",
            "carrito" => $carrito,
            "total"   => self::ctrCarritoTotal($carrito),
        ]);
        exit;
    }

    // Devuelve el estado actual del carrito (para hidratar el JS si hace falta)
    static public function ctrCarritoObtener() {
        if (isset($_GET["carritoObtener"])) {
            self::ctrCarritoResponder();
        }
    }

    // ── Paciente ──────────────────────────────────────────────────────────

    static public function ctrCarritoAsignarPaciente() {
        if (isset($_POST["carritoAsignarPacienteId"])) {
            self::ctrCarritoObtenerOInicializar();
            $_SESSION["carrito_pago"]["id_paciente"]     = (int) $_POST["carritoAsignarPacienteId"];
            $_SESSION["carrito_pago"]["paciente_nombre"] = trim($_POST["carritoAsignarPacienteNombre"] ?? "");
            $_SESSION["carrito_pago"]["paciente_ci"]     = trim($_POST["carritoAsignarPacienteCI"] ?? "");
            self::ctrCarritoResponder();
        }
    }

    static public function ctrCarritoRegistrarPacienteRapido() {
        if (isset($_POST["carritoNuevoPacienteNombre"], $_POST["carritoNuevoPacienteCI"])) {
            self::ctrCarritoObtenerOInicializar();

            $nombre = trim($_POST["carritoNuevoPacienteNombre"]);
            $ci     = trim($_POST["carritoNuevoPacienteCI"]);

            if ($nombre === "" || $ci === "") {
                echo json_encode(["status" => "error", "mensaje" => "Nombre y carnet son obligatorios"]);
                exit;
            }

            $datos = [
                "nombre"           => $nombre,
                "apellidos"        => trim($_POST["carritoNuevoPacienteApellidos"] ?? ""),
                "ci"               => $ci,
                "grupo_sanguineo"  => trim($_POST["carritoNuevoPacienteGrupoSanguineo"] ?? ""),
                "telefono"         => trim($_POST["carritoNuevoPacienteTelefono"] ?? ""),
                "fecha_nacimiento" => trim($_POST["carritoNuevoPacienteFechaNacimiento"] ?? ""),
                "direccion"        => trim($_POST["carritoNuevoPacienteDireccion"] ?? ""),
            ];

            $id_paciente = ModeloPacientes::mdlCrearPacienteRapido($datos);
            if (!$id_paciente) {
                echo json_encode(["status" => "error", "mensaje" => "No se pudo registrar el paciente (verifica que el carnet no esté repetido)"]);
                exit;
            }

            $_SESSION["carrito_pago"]["id_paciente"]     = $id_paciente;
            $_SESSION["carrito_pago"]["paciente_nombre"] = trim($nombre . " " . $datos["apellidos"]);
            $_SESSION["carrito_pago"]["paciente_ci"]     = $ci;
            self::ctrCarritoResponder();
        }
    }

    // ── Cliente ───────────────────────────────────────────────────────────

    static public function ctrCarritoAsignarCliente() {
        if (isset($_POST["carritoAsignarClienteId"])) {
            self::ctrCarritoObtenerOInicializar();
            $_SESSION["carrito_pago"]["id_cliente"]     = (int) $_POST["carritoAsignarClienteId"];
            $_SESSION["carrito_pago"]["cliente_nombre"] = trim($_POST["carritoAsignarClienteNombre"] ?? "");
            $_SESSION["carrito_pago"]["cliente_nit"]    = trim($_POST["carritoAsignarClienteNit"] ?? "");
            self::ctrCarritoResponder();
        }
    }

    static public function ctrCarritoRegistrarClienteRapido() {
        if (isset($_POST["carritoNuevoClienteNombre"], $_POST["carritoNuevoClienteNit"])) {
            self::ctrCarritoObtenerOInicializar();

            $nombre = trim($_POST["carritoNuevoClienteNombre"]);
            $nit    = trim($_POST["carritoNuevoClienteNit"]);

            if ($nombre === "" || $nit === "") {
                echo json_encode(["status" => "error", "mensaje" => "Nombre y NIT son obligatorios"]);
                exit;
            }

            $id_cliente = ModeloClientes::mdlCrearClienteRapido($nombre, $nit);
            if (!$id_cliente) {
                echo json_encode(["status" => "error", "mensaje" => "No se pudo registrar el cliente"]);
                exit;
            }

            $_SESSION["carrito_pago"]["id_cliente"]     = $id_cliente;
            $_SESSION["carrito_pago"]["cliente_nombre"] = $nombre;
            $_SESSION["carrito_pago"]["cliente_nit"]    = $nit;
            self::ctrCarritoResponder();
        }
    }

    // ── Líneas del carrito ───────────────────────────────────────────────

    static public function ctrCarritoAgregarLinea() {
        if (isset($_POST["carritoLineaTipo"], $_POST["carritoLineaIdReferencia"])) {
            self::ctrCarritoObtenerOInicializar();

            $id = $_SESSION["carrito_pago"]["next_linea_id"]++;
            $_SESSION["carrito_pago"]["lineas"][] = [
                "id_linea"      => $id,
                "tipo"          => $_POST["carritoLineaTipo"], // 'servicio' | 'examen' | 'medicamento'
                "id_referencia" => (int) $_POST["carritoLineaIdReferencia"],
                "nombre"        => trim($_POST["carritoLineaNombre"] ?? ""),
                "precio"        => (float) ($_POST["carritoLineaPrecio"] ?? 0),
                "cantidad"      => !empty($_POST["carritoLineaCantidad"]) ? (int) $_POST["carritoLineaCantidad"] : 1,
            ];
            self::ctrCarritoResponder();
        }
    }

    static public function ctrCarritoQuitarLinea() {
        if (isset($_POST["carritoQuitarLineaId"])) {
            self::ctrCarritoObtenerOInicializar();

            $idLinea = (int) $_POST["carritoQuitarLineaId"];
            $_SESSION["carrito_pago"]["lineas"] = array_values(array_filter(
                $_SESSION["carrito_pago"]["lineas"],
                function ($l) use ($idLinea) { return $l["id_linea"] !== $idLinea; }
            ));
            self::ctrCarritoResponder();
        }
    }

    // Vacía el carrito (botón Cancelar)
    static public function ctrCarritoCancelar() {
        if (isset($_POST["carritoCancelar"])) {
            if (session_status() == PHP_SESSION_NONE) session_start();
            unset($_SESSION["carrito_pago"]);
            echo json_encode(["status" => "ok"]);
            exit;
        }
    }
        // ── Confirmar el carrito completo (AJAX, escritura atómica) ────────────

    static public function ctrCarritoFinalizarPago() {
        if (isset($_POST["carritoFinalizarMetodo"])) {
            if (session_status() == PHP_SESSION_NONE) session_start();

            $carrito = $_SESSION["carrito_pago"] ?? null;
            $metodo  = trim($_POST["carritoFinalizarMetodo"]);
            $id_cita = !empty($_POST["carritoFinalizarIdCita"]) ? (int) $_POST["carritoFinalizarIdCita"] : null;
            $id_usuario = (int) $_SESSION["IdUsuario"];

            if (!$carrito || empty($carrito["lineas"]) || $metodo === "") {
                echo json_encode(["status" => "error", "mensaje" => "Agrega al menos un ítem y selecciona el método de pago"]);
                exit;
            }

            $id_servicio_prestado = ModeloPagos::mdlConfirmarCarrito($carrito, $id_cita, $id_usuario, $metodo);

            if (!$id_servicio_prestado) {
                echo json_encode(["status" => "error", "mensaje" => "No se pudo registrar el cobro, intenta de nuevo"]);
                exit;
            }

            unset($_SESSION["carrito_pago"]);
            echo json_encode(["status" => "ok", "id_servicio_prestado" => $id_servicio_prestado]);
            exit;
        }
    }
}