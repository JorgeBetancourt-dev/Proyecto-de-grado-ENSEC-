<?php
require_once "conexion.php";

class ModeloConsultas {

    // ── Consulta ──────────────────────────────────────────────────────────────

    // Busca la consulta ligada a una cita; si no existe, la crea (motivo vacío, se completa después)
    static public function mdlObtenerOcrearConsultaPorCita($id_cita) {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare("SELECT * FROM consultas WHERE id_cita = :id_cita AND activo = 1");
        $stmt->bindParam(":id_cita", $id_cita, PDO::PARAM_INT);
        $stmt->execute();
        $consulta = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        if ($consulta) {
            return $consulta;
        }

        $stmt = $conn->prepare(
            "INSERT INTO consultas (id_cita, motivo, fecha_atencion, activo)
             VALUES (:id_cita, '', NOW(), 1)"
        );
        $stmt->bindParam(":id_cita", $id_cita, PDO::PARAM_INT);
        if (!$stmt->execute()) {
            return null;
        }
        $idConsulta = $conn->lastInsertId();

        $stmt = $conn->prepare("SELECT * FROM consultas WHERE id_consulta = :id");
        $stmt->bindParam(":id", $idConsulta, PDO::PARAM_INT);
        $stmt->execute();
        $consulta = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $consulta;
    }

    static public function mdlObtenerConsultaPorCita($id_cita) {
        $stmt = Conexion::conectar()->prepare("SELECT * FROM consultas WHERE id_cita = :id_cita AND activo = 1");
        $stmt->bindParam(":id_cita", $id_cita, PDO::PARAM_INT);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $resultado;
    }

    static public function mdlObtenerConsulta($id_consulta) {
        $stmt = Conexion::conectar()->prepare("SELECT * FROM consultas WHERE id_consulta = :id AND activo = 1");
        $stmt->bindParam(":id", $id_consulta, PDO::PARAM_INT);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $resultado;
    }

    // Guarda motivo/observaciones (se usa al finalizar)
    static public function mdlActualizarConsulta($id_consulta, $motivo, $observaciones) {
        $stmt = Conexion::conectar()->prepare(
            "UPDATE consultas SET motivo = :motivo, observaciones = :observaciones WHERE id_consulta = :id"
        );
        $stmt->bindParam(":motivo",        $motivo,        PDO::PARAM_STR);
        $stmt->bindParam(":observaciones", $observaciones, PDO::PARAM_STR);
        $stmt->bindParam(":id",            $id_consulta,   PDO::PARAM_INT);
        return $stmt->execute() ? "ok" : "error";
    }

    // ── Triaje ────────────────────────────────────────────────────────────────

    static public function mdlObtenerTriajePorConsulta($id_consulta) {
        $stmt = Conexion::conectar()->prepare("SELECT * FROM triaje WHERE id_consulta = :id AND activo = 1");
        $stmt->bindParam(":id", $id_consulta, PDO::PARAM_INT);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $resultado;
    }

    // Crea el triaje de una consulta, o lo actualiza si el médico vuelve a entrar a la misma cita
    static public function mdlGuardarTriaje($datos) {
        $existente = self::mdlObtenerTriajePorConsulta($datos["id_consulta"]);
        $conn = Conexion::conectar();

        if ($existente) {
            $stmt = $conn->prepare(
                "UPDATE triaje SET tension_arterial = :tension_arterial, frecuencia_cardiaca = :frecuencia_cardiaca,
                     frecuencia_respiratoria = :frecuencia_respiratoria, temperatura = :temperatura,
                     saturacion = :saturacion, peso = :peso, fecha = NOW()
                 WHERE id_triaje = :id_triaje"
            );
            $stmt->bindParam(":id_triaje", $existente["id_triaje"], PDO::PARAM_INT);
        } else {
            $stmt = $conn->prepare(
                "INSERT INTO triaje (id_consulta, tension_arterial, frecuencia_cardiaca, frecuencia_respiratoria,
                     temperatura, saturacion, peso, fecha, activo)
                 VALUES (:id_consulta, :tension_arterial, :frecuencia_cardiaca, :frecuencia_respiratoria,
                     :temperatura, :saturacion, :peso, NOW(), 1)"
            );
            $stmt->bindParam(":id_consulta", $datos["id_consulta"], PDO::PARAM_INT);
        }

        $stmt->bindParam(":tension_arterial",        $datos["tension_arterial"],        PDO::PARAM_STR);
        $stmt->bindParam(":frecuencia_cardiaca",     $datos["frecuencia_cardiaca"],     PDO::PARAM_INT);
        $stmt->bindParam(":frecuencia_respiratoria", $datos["frecuencia_respiratoria"], PDO::PARAM_INT);
        $stmt->bindParam(":temperatura",             $datos["temperatura"],             PDO::PARAM_STR);
        $stmt->bindParam(":saturacion",              $datos["saturacion"],              PDO::PARAM_STR);
        $stmt->bindParam(":peso",                    $datos["peso"],                    PDO::PARAM_STR);

        return $stmt->execute() ? "ok" : "error";
    }

    // ── Exámenes solicitados en la consulta ──────────────────────────────────

    static public function mdlMostrarCatalogoExamenes() {
        $stmt = Conexion::conectar()->prepare("SELECT id_examen, nombre, tipo, costo FROM examenes WHERE activo = 1 ORDER BY nombre ASC");
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $resultado;
    }
        static public function mdlMostrarCatalogoExamenesConsulta() {
        $stmt = Conexion::conectar()->prepare("SELECT id_examen, nombre, tipo FROM examenes WHERE activo = 1 ORDER BY nombre ASC");
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $resultado;
    }

    static public function mdlMostrarExamenesConsulta($id_consulta) {
        $stmt = Conexion::conectar()->prepare(
            "SELECT de.id_detalle_examen, de.estado, de.fecha_solicitud, e.nombre, e.tipo, e.costo
             FROM detalle_examen de
             INNER JOIN examenes e ON e.id_examen = de.id_examen
             WHERE de.id_consulta = :id AND de.activo = 1
             ORDER BY de.id_detalle_examen ASC"
        );
        $stmt->bindParam(":id", $id_consulta, PDO::PARAM_INT);
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $resultado;
    }

    static public function mdlAgregarExamen($id_consulta, $id_examen) {
        $stmt = Conexion::conectar()->prepare(
            "INSERT INTO detalle_examen (id_consulta, id_examen, fecha_solicitud, estado, activo)
             VALUES (:id_consulta, :id_examen, CURDATE(), 'solicitado', 1)"
        );
        $stmt->bindParam(":id_consulta", $id_consulta, PDO::PARAM_INT);
        $stmt->bindParam(":id_examen",   $id_examen,   PDO::PARAM_INT);
        return $stmt->execute() ? "ok" : "error";
    }

    static public function mdlQuitarExamen($id_detalle_examen) {
        $stmt = Conexion::conectar()->prepare("UPDATE detalle_examen SET activo = 0 WHERE id_detalle_examen = :id");
        $stmt->bindParam(":id", $id_detalle_examen, PDO::PARAM_INT);
        return $stmt->execute() ? "ok" : "error";
    }

    // ── Tratamientos y sus medicamentos ──────────────────────────────────────

    static public function mdlMostrarCatalogoMedicamentos() {
        $stmt = Conexion::conectar()->prepare("SELECT id_medicamento, nombre FROM medicamentos WHERE activo = 1 ORDER BY nombre ASC");
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $resultado;
    }

    static public function mdlMostrarTratamientosConsulta($id_consulta) {
        $stmt = Conexion::conectar()->prepare(
            "SELECT * FROM tratamientos WHERE id_consulta = :id AND activo = 1 ORDER BY id_tratamiento ASC"
        );
        $stmt->bindParam(":id", $id_consulta, PDO::PARAM_INT);
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $resultado;
    }

    static public function mdlAgregarTratamiento($datos) {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare(
            "INSERT INTO tratamientos (id_consulta, descripcion, duracion, indicaciones, activo)
             VALUES (:id_consulta, :descripcion, :duracion, :indicaciones, 1)"
        );
        $stmt->bindParam(":id_consulta",  $datos["id_consulta"],  PDO::PARAM_INT);
        $stmt->bindParam(":descripcion",  $datos["descripcion"],  PDO::PARAM_STR);
        $stmt->bindParam(":duracion",     $datos["duracion"],     PDO::PARAM_STR);
        $stmt->bindParam(":indicaciones", $datos["indicaciones"], PDO::PARAM_STR);
        return $stmt->execute() ? "ok" : "error";
    }

    static public function mdlQuitarTratamiento($id_tratamiento) {
        $stmt = Conexion::conectar()->prepare("UPDATE tratamientos SET activo = 0 WHERE id_tratamiento = :id");
        $stmt->bindParam(":id", $id_tratamiento, PDO::PARAM_INT);
        return $stmt->execute() ? "ok" : "error";
    }

    static public function mdlMostrarMedicamentosConsulta($id_consulta) {
        $stmt = Conexion::conectar()->prepare(
            "SELECT dt.id_detalle_tratamiento, dt.dosis, dt.frecuencia, dt.duracion, m.nombre
             FROM detalle_tratamiento dt
             INNER JOIN tratamientos t   ON t.id_tratamiento = dt.id_tratamiento
             INNER JOIN medicamentos m   ON m.id_medicamento = dt.id_medicamento
             WHERE t.id_consulta = :id_consulta AND t.activo = 1 AND dt.activo = 1
             ORDER BY dt.id_detalle_tratamiento ASC"
        );
        $stmt->bindParam(":id_consulta", $id_consulta, PDO::PARAM_INT);
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $resultado;
    }

    static public function mdlAgregarMedicamentoTratamiento($datos) {
        $stmt = Conexion::conectar()->prepare(
            "INSERT INTO detalle_tratamiento (id_tratamiento, id_medicamento, dosis, frecuencia, duracion, activo)
             VALUES (:id_tratamiento, :id_medicamento, :dosis, :frecuencia, :duracion, 1)"
        );
        $stmt->bindParam(":id_tratamiento", $datos["id_tratamiento"], PDO::PARAM_INT);
        $stmt->bindParam(":id_medicamento", $datos["id_medicamento"], PDO::PARAM_INT);
        $stmt->bindParam(":dosis",          $datos["dosis"],          PDO::PARAM_STR);
        $stmt->bindParam(":frecuencia",     $datos["frecuencia"],     PDO::PARAM_STR);
        $stmt->bindValue(":duracion",       $datos["duracion"] ?: null, $datos["duracion"] ? PDO::PARAM_STR : PDO::PARAM_NULL);
        return $stmt->execute() ? "ok" : "error";
    }

    static public function mdlQuitarMedicamentoTratamiento($id_detalle_tratamiento) {
        $stmt = Conexion::conectar()->prepare("UPDATE detalle_tratamiento SET activo = 0 WHERE id_detalle_tratamiento = :id");
        $stmt->bindParam(":id", $id_detalle_tratamiento, PDO::PARAM_INT);
        return $stmt->execute() ? "ok" : "error";
    }
        static public function mdlObtenerTratamientoPorConsulta($id_consulta) {
        $stmt = Conexion::conectar()->prepare(
            "SELECT * FROM tratamientos WHERE id_consulta = :id AND activo = 1 LIMIT 1"
        );
        $stmt->bindParam(":id", $id_consulta, PDO::PARAM_INT);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $resultado;
    }

    // Cada consulta tiene un único tratamiento implícito (sin descripción propia),
    // que actúa solo como contenedor de los medicamentos recetados.
    static public function mdlObtenerOcrearTratamiento($id_consulta) {
        $existente = self::mdlObtenerTratamientoPorConsulta($id_consulta);
        if ($existente) {
            return $existente;
        }

        $conn = Conexion::conectar();
        $stmt = $conn->prepare(
            "INSERT INTO tratamientos (id_consulta, descripcion, duracion, indicaciones, activo)
             VALUES (:id_consulta, '', NULL, NULL, 1)"
        );
        $stmt->bindParam(":id_consulta", $id_consulta, PDO::PARAM_INT);
        if (!$stmt->execute()) {
            return null;
        }
        $id = $conn->lastInsertId();

        $stmt = $conn->prepare("SELECT * FROM tratamientos WHERE id_tratamiento = :id");
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $resultado;
    }
        // Graba la consulta completa de una sola vez a partir del carrito en sesión
    static public function mdlConfirmarCarrito($carrito) {
        $conn = Conexion::conectar();

        try {
            $conn->beginTransaction();

            // 1. Crear la consulta
            $stmt = $conn->prepare(
                "INSERT INTO consultas (id_cita, motivo, observaciones, fecha_atencion, activo)
                 VALUES (:id_cita, :motivo, :observaciones, NOW(), 1)"
            );
            $stmt->bindParam(":id_cita", $carrito["id_cita"], PDO::PARAM_INT);
            $stmt->bindParam(":motivo", $carrito["motivo"], PDO::PARAM_STR);
            $stmt->bindParam(":observaciones", $carrito["observaciones"], PDO::PARAM_STR);
            if (!$stmt->execute()) {
                throw new Exception("No se pudo crear la consulta");
            }
            $id_consulta = (int) $conn->lastInsertId();

            // 2. Triaje (solo si al menos un campo fue llenado)
            $t = $carrito["triaje"];
            $tieneTriaje = array_filter($t, function ($v) { return trim((string) $v) !== ""; });
            if (!empty($tieneTriaje)) {
                $stmt = $conn->prepare(
                    "INSERT INTO triaje (id_consulta, tension_arterial, frecuencia_cardiaca, frecuencia_respiratoria,
                         temperatura, saturacion, peso, fecha, activo)
                     VALUES (:id_consulta, :tension_arterial, :frecuencia_cardiaca, :frecuencia_respiratoria,
                         :temperatura, :saturacion, :peso, NOW(), 1)"
                );
                $stmt->bindParam(":id_consulta", $id_consulta, PDO::PARAM_INT);
                $stmt->bindValue(":tension_arterial", $t["tension_arterial"] ?: null, $t["tension_arterial"] ? PDO::PARAM_STR : PDO::PARAM_NULL);
                $stmt->bindValue(":frecuencia_cardiaca", $t["frecuencia_cardiaca"] !== "" ? (int) $t["frecuencia_cardiaca"] : null, $t["frecuencia_cardiaca"] !== "" ? PDO::PARAM_INT : PDO::PARAM_NULL);
                $stmt->bindValue(":frecuencia_respiratoria", $t["frecuencia_respiratoria"] !== "" ? (int) $t["frecuencia_respiratoria"] : null, $t["frecuencia_respiratoria"] !== "" ? PDO::PARAM_INT : PDO::PARAM_NULL);
                $stmt->bindValue(":temperatura", $t["temperatura"] ?: null, $t["temperatura"] ? PDO::PARAM_STR : PDO::PARAM_NULL);
                $stmt->bindValue(":saturacion", $t["saturacion"] ?: null, $t["saturacion"] ? PDO::PARAM_STR : PDO::PARAM_NULL);
                $stmt->bindValue(":peso", $t["peso"] ?: null, $t["peso"] ? PDO::PARAM_STR : PDO::PARAM_NULL);
                if (!$stmt->execute()) {
                    throw new Exception("No se pudo guardar el triaje");
                }
            }

            // 3. Exámenes solicitados
            if (!empty($carrito["examenes"])) {
                $stmtEx = $conn->prepare(
                    "INSERT INTO detalle_examen (id_consulta, id_examen, fecha_solicitud, estado, activo)
                     VALUES (:id_consulta, :id_examen, CURDATE(), 'solicitado', 1)"
                );
                foreach ($carrito["examenes"] as $ex) {
                    $stmtEx->bindParam(":id_consulta", $id_consulta, PDO::PARAM_INT);
                    $stmtEx->bindValue(":id_examen", $ex["id_examen"], PDO::PARAM_INT);
                    if (!$stmtEx->execute()) {
                        throw new Exception("No se pudo guardar un examen solicitado");
                    }
                }
            }

            // 4. Tratamiento implícito + medicamentos
            if (!empty($carrito["medicamentos"])) {
                $stmt = $conn->prepare(
                    "INSERT INTO tratamientos (id_consulta, descripcion, duracion, indicaciones, activo)
                     VALUES (:id_consulta, '', NULL, NULL, 1)"
                );
                $stmt->bindParam(":id_consulta", $id_consulta, PDO::PARAM_INT);
                if (!$stmt->execute()) {
                    throw new Exception("No se pudo crear el tratamiento");
                }
                $id_tratamiento = (int) $conn->lastInsertId();

                $stmtMed = $conn->prepare(
                    "INSERT INTO detalle_tratamiento (id_tratamiento, id_medicamento, dosis, frecuencia, duracion, activo)
                     VALUES (:id_tratamiento, :id_medicamento, :dosis, :frecuencia, :duracion, 1)"
                );
                foreach ($carrito["medicamentos"] as $med) {
                    $stmtMed->bindParam(":id_tratamiento", $id_tratamiento, PDO::PARAM_INT);
                    $stmtMed->bindValue(":id_medicamento", $med["id_medicamento"], PDO::PARAM_INT);
                    $stmtMed->bindValue(":dosis", $med["dosis"], PDO::PARAM_STR);
                    $stmtMed->bindValue(":frecuencia", $med["frecuencia"], PDO::PARAM_STR);
                    $stmtMed->bindValue(":duracion", $med["duracion"] ?: null, $med["duracion"] ? PDO::PARAM_STR : PDO::PARAM_NULL);
                    if (!$stmtMed->execute()) {
                        throw new Exception("No se pudo guardar un medicamento del tratamiento");
                    }
                }
            }

            // 5. Marcar la cita como atendida
            $stmt = $conn->prepare("UPDATE citas SET estado = 'atendida' WHERE id_cita = :id_cita AND activo = 1");
            $stmt->bindParam(":id_cita", $carrito["id_cita"], PDO::PARAM_INT);
            if (!$stmt->execute()) {
                throw new Exception("No se pudo marcar la cita como atendida");
            }

            $conn->commit();
            return $id_consulta;

        } catch (Exception $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            return null;
        }
    }
    
}