<?php
require_once "conexion.php";

class ModeloPagos {

    // ── Borrador de cobro ────────────────────────────────────────────────────

    // Busca el cobro abierto de una cita (solo aplica si la cita existe)
    static public function mdlObtenerPagoPorCita($id_cita) {
        $stmt = Conexion::conectar()->prepare(
            "SELECT * FROM servicio_prestado WHERE id_cita = :id_cita AND activo = 1"
        );
        $stmt->bindParam(":id_cita", $id_cita, PDO::PARAM_INT);
        $stmt->execute();
        $pago = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $pago;
    }

    // Crea un borrador de cobro (costo_total = 0). id_paciente e id_cita son opcionales.
    static public function mdlCrearPago($id_paciente, $id_cita, $id_usuario) {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare(
            "INSERT INTO servicio_prestado (id_paciente, id_cliente, id_cita, id_usuario, metodo_pago, costo_total, fecha, activo)
             VALUES (:id_paciente, NULL, :id_cita, :id_usuario, 'efectivo', 0, NOW(), 1)"
        );
        $stmt->bindValue(":id_paciente", $id_paciente, $id_paciente === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(":id_cita",     $id_cita,     $id_cita     === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindParam(":id_usuario",  $id_usuario,  PDO::PARAM_INT);
        if (!$stmt->execute()) {
            return null;
        }
        return self::mdlObtenerPago((int) $conn->lastInsertId());
    }

    // Punto de entrada único: reutiliza el borrador si ya existe (cuando hay cita), o crea uno nuevo
    static public function mdlObtenerOcrearPago($id_paciente, $id_cita, $id_usuario) {
        if ($id_cita !== null) {
            $pago = self::mdlObtenerPagoPorCita($id_cita);
            if ($pago) {
                return $pago;
            }
        }
        return self::mdlCrearPago($id_paciente, $id_cita, $id_usuario);
    }

    static public function mdlObtenerPago($id_servicio_prestado) {
        $stmt = Conexion::conectar()->prepare("SELECT * FROM servicio_prestado WHERE id_servicio_prestado = :id AND activo = 1");
        $stmt->bindParam(":id", $id_servicio_prestado, PDO::PARAM_INT);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $resultado;
    }

    // Recalcula costo_total desde la suma de las líneas activas
    static public function mdlRecalcularTotal($id_servicio_prestado) {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare(
            "UPDATE servicio_prestado
             SET costo_total = (SELECT COALESCE(SUM(subtotal), 0) FROM detalle_servicio WHERE id_servicio_prestado = :id1 AND activo = 1)
             WHERE id_servicio_prestado = :id2"
        );
        $stmt->bindParam(":id1", $id_servicio_prestado, PDO::PARAM_INT);
        $stmt->bindParam(":id2", $id_servicio_prestado, PDO::PARAM_INT);
        return $stmt->execute() ? "ok" : "error";
    }

    // Finaliza el cobro: fija el método de pago y, si corresponde, el cliente de la factura
    static public function mdlFinalizarPago($id_servicio_prestado, $metodo_pago, $id_cliente) {
        $stmt = Conexion::conectar()->prepare(
            "UPDATE servicio_prestado SET metodo_pago = :metodo_pago, id_cliente = :id_cliente WHERE id_servicio_prestado = :id"
        );
        $stmt->bindParam(":metodo_pago", $metodo_pago, PDO::PARAM_STR);
        $stmt->bindValue(":id_cliente",  $id_cliente,  $id_cliente === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindParam(":id",          $id_servicio_prestado, PDO::PARAM_INT);
        return $stmt->execute() ? "ok" : "error";
    }

    // Asigna o cambia el paciente de un cobro ya creado (flujo sin cita)
    static public function mdlAsignarPaciente($id_servicio_prestado, $id_paciente) {
        $stmt = Conexion::conectar()->prepare(
            "UPDATE servicio_prestado SET id_paciente = :id_paciente WHERE id_servicio_prestado = :id"
        );
        $stmt->bindValue(":id_paciente", $id_paciente, $id_paciente === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindParam(":id", $id_servicio_prestado, PDO::PARAM_INT);
        return $stmt->execute() ? "ok" : "error";
    }

    // Asigna o cambia el cliente de un cobro ya creado (flujo sin cita)
    static public function mdlAsignarCliente($id_servicio_prestado, $id_cliente) {
        $stmt = Conexion::conectar()->prepare(
            "UPDATE servicio_prestado SET id_cliente = :id_cliente WHERE id_servicio_prestado = :id"
        );
        $stmt->bindValue(":id_cliente", $id_cliente, $id_cliente === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindParam(":id", $id_servicio_prestado, PDO::PARAM_INT);
        return $stmt->execute() ? "ok" : "error";
    }

    // Cliente opcional para la factura (búsqueda simple por NIT, o se crea nuevo) — usado al finalizar el cobro
    static public function mdlBuscarOcrearCliente($nombre, $nit) {
        $conn = Conexion::conectar();
        if (!empty($nit)) {
            $stmt = $conn->prepare("SELECT id_cliente FROM clientes WHERE nit = :nit AND activo = 1 LIMIT 1");
            $stmt->bindParam(":nit", $nit, PDO::PARAM_STR);
            $stmt->execute();
            $existente = $stmt->fetchColumn();
            $stmt->closeCursor();
            if ($existente) {
                return (int) $existente;
            }
        }
        $stmt = $conn->prepare("INSERT INTO clientes (nombre, nit, activo) VALUES (:nombre, :nit, 1)");
        $stmt->bindParam(":nombre", $nombre, PDO::PARAM_STR);
        $stmt->bindValue(":nit", $nit ?: null, $nit ? PDO::PARAM_STR : PDO::PARAM_NULL);
        if (!$stmt->execute()) {
            return null;
        }
        return (int) $conn->lastInsertId();
    }

    // ── Líneas del cobro (detalle_servicio) ──────────────────────────────────

    static public function mdlMostrarLineasPago($id_servicio_prestado) {
        $stmt = Conexion::conectar()->prepare(
            "SELECT ds.id_detalle_servicio, ds.precio_final, ds.cantidad, ds.subtotal,
                    s.nombre AS nombre_servicio, e.nombre AS nombre_examen, m.nombre AS nombre_medicamento
             FROM detalle_servicio ds
             LEFT JOIN servicios s     ON s.id_servicio      = ds.id_servicio
             LEFT JOIN examenes e      ON e.id_examen        = ds.id_examen
             LEFT JOIN medicamentos m  ON m.id_medicamento   = ds.id_medicamento
             WHERE ds.id_servicio_prestado = :id AND ds.activo = 1
             ORDER BY ds.id_detalle_servicio ASC"
        );
        $stmt->bindParam(":id", $id_servicio_prestado, PDO::PARAM_INT);
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $resultado;
    }

    static public function mdlAgregarLinea($datos) {
        $stmt = Conexion::conectar()->prepare(
            "INSERT INTO detalle_servicio (id_servicio_prestado, id_servicio, id_examen, id_medicamento, precio_final, cantidad, activo)
             VALUES (:id_servicio_prestado, :id_servicio, :id_examen, :id_medicamento, :precio_final, :cantidad, 1)"
        );
        $stmt->bindParam(":id_servicio_prestado", $datos["id_servicio_prestado"], PDO::PARAM_INT);
        $stmt->bindValue(":id_servicio",     $datos["id_servicio"],     $datos["id_servicio"]     === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(":id_examen",       $datos["id_examen"],       $datos["id_examen"]        === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(":id_medicamento",  $datos["id_medicamento"],  $datos["id_medicamento"]   === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindParam(":precio_final", $datos["precio_final"], PDO::PARAM_STR);
        $stmt->bindParam(":cantidad",     $datos["cantidad"],     PDO::PARAM_INT);
        if (!$stmt->execute()) {
            return "error";
        }
        return self::mdlRecalcularTotal($datos["id_servicio_prestado"]);
    }

    static public function mdlQuitarLinea($id_detalle_servicio, $id_servicio_prestado) {
        $stmt = Conexion::conectar()->prepare("UPDATE detalle_servicio SET activo = 0 WHERE id_detalle_servicio = :id");
        $stmt->bindParam(":id", $id_detalle_servicio, PDO::PARAM_INT);
        if (!$stmt->execute()) {
            return "error";
        }
        return self::mdlRecalcularTotal($id_servicio_prestado);
    }

    // ── Catálogos ─────────────────────────────────────────────────────────────

    static public function mdlMostrarCatalogoServicios() {
        $stmt = Conexion::conectar()->prepare("SELECT id_servicio, nombre, precio FROM servicios WHERE activo = 1 ORDER BY nombre ASC");
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $resultado;
    }

    static public function mdlMostrarCatalogoExamenes() {
        $stmt = Conexion::conectar()->prepare("SELECT id_examen, nombre, tipo, precio FROM examenes WHERE activo = 1 ORDER BY nombre ASC");
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $resultado;
    }

    static public function mdlMostrarCatalogoMedicamentos() {
        $stmt = Conexion::conectar()->prepare("SELECT id_medicamento, nombre, descripcion, precio FROM medicamentos WHERE activo = 1 ORDER BY nombre ASC");
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $resultado;
    }

    // ── Sugeridos desde la consulta (exámenes/medicamentos aún no cobrados en este pago) ──

    static public function mdlExamenesPendientesCobro($id_consulta, $id_servicio_prestado) {
        $stmt = Conexion::conectar()->prepare(
            "SELECT DISTINCT e.id_examen, e.nombre, e.tipo, e.precio
             FROM detalle_examen de
             INNER JOIN examenes e ON e.id_examen = de.id_examen
             WHERE de.id_consulta = :id_consulta AND de.activo = 1
               AND e.id_examen NOT IN (
                   SELECT id_examen FROM detalle_servicio
                   WHERE id_servicio_prestado = :id_sp AND id_examen IS NOT NULL AND activo = 1
               )"
        );
        $stmt->bindParam(":id_consulta", $id_consulta, PDO::PARAM_INT);
        $stmt->bindParam(":id_sp", $id_servicio_prestado, PDO::PARAM_INT);
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $resultado;
    }

    static public function mdlMedicamentosPendientesCobro($id_consulta, $id_servicio_prestado) {
        $stmt = Conexion::conectar()->prepare(
            "SELECT DISTINCT m.id_medicamento, m.nombre, m.precio
             FROM detalle_tratamiento dt
             INNER JOIN tratamientos t   ON t.id_tratamiento = dt.id_tratamiento
             INNER JOIN medicamentos m   ON m.id_medicamento = dt.id_medicamento
             WHERE t.id_consulta = :id_consulta AND t.activo = 1 AND dt.activo = 1
               AND m.id_medicamento NOT IN (
                   SELECT id_medicamento FROM detalle_servicio
                   WHERE id_servicio_prestado = :id_sp AND id_medicamento IS NOT NULL AND activo = 1
               )"
        );
        $stmt->bindParam(":id_consulta", $id_consulta, PDO::PARAM_INT);
        $stmt->bindParam(":id_sp", $id_servicio_prestado, PDO::PARAM_INT);
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $resultado;
    }
        // Graba el cobro completo de una sola vez a partir del carrito en sesión
        // Graba el cobro completo de una sola vez a partir del carrito en sesión
    static public function mdlConfirmarCarrito($carrito, $id_cita, $id_usuario, $metodo_pago) {
        $conn = Conexion::conectar();

        try {
            $conn->beginTransaction();

            // 1. Crear el servicio_prestado
            $stmt = $conn->prepare(
                "INSERT INTO servicio_prestado (id_paciente, id_cliente, id_cita, id_usuario, metodo_pago, costo_total, fecha, activo)
                 VALUES (:id_paciente, :id_cliente, :id_cita, :id_usuario, :metodo_pago, :costo_total, NOW(), 1)"
            );
            $costo_total = 0;
            foreach ($carrito["lineas"] as $l) {
                $costo_total += $l["precio"] * $l["cantidad"];
            }

            $stmt->bindValue(":id_paciente", $carrito["id_paciente"], $carrito["id_paciente"] === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(":id_cliente",  $carrito["id_cliente"],  $carrito["id_cliente"]  === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(":id_cita",     $id_cita,                $id_cita                === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindParam(":id_usuario",  $id_usuario,  PDO::PARAM_INT);
            $stmt->bindParam(":metodo_pago", $metodo_pago, PDO::PARAM_STR);
            $stmt->bindParam(":costo_total", $costo_total, PDO::PARAM_STR);

            if (!$stmt->execute()) {
                throw new Exception("No se pudo crear el servicio_prestado");
            }

            $id_servicio_prestado = (int) $conn->lastInsertId();

            // 2. Insertar cada línea del carrito en detalle_servicio
            $stmtLinea = $conn->prepare(
                "INSERT INTO detalle_servicio (id_servicio_prestado, id_servicio, id_examen, id_medicamento, precio_final, cantidad, activo)
                 VALUES (:id_servicio_prestado, :id_servicio, :id_examen, :id_medicamento, :precio_final, :cantidad, 1)"
            );

            foreach ($carrito["lineas"] as $l) {
                $id_servicio    = $l["tipo"] === "servicio"    ? $l["id_referencia"] : null;
                $id_examen      = $l["tipo"] === "examen"      ? $l["id_referencia"] : null;
                $id_medicamento = $l["tipo"] === "medicamento" ? $l["id_referencia"] : null;

                $stmtLinea->bindParam(":id_servicio_prestado", $id_servicio_prestado, PDO::PARAM_INT);
                $stmtLinea->bindValue(":id_servicio",    $id_servicio,    $id_servicio    === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
                $stmtLinea->bindValue(":id_examen",      $id_examen,      $id_examen      === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
                $stmtLinea->bindValue(":id_medicamento", $id_medicamento, $id_medicamento === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
                $stmtLinea->bindValue(":precio_final", $l["precio"], PDO::PARAM_STR);
                $stmtLinea->bindValue(":cantidad",     $l["cantidad"], PDO::PARAM_INT);

                if (!$stmtLinea->execute()) {
                    throw new Exception("No se pudo insertar una línea del detalle");
                }
            }

            $conn->commit();
            return $id_servicio_prestado;

        } catch (Exception $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            return null;
        }
    }
}