<?php if (isset($_SESSION["crear_cita"])): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        <?php if ($_SESSION["crear_cita"] == "ok"): ?>
        Swal.fire({ icon: "success", title: "¡Cita registrada correctamente!", confirmButtonText: "Cerrar" })
            .then(function () { if (window.refrescarCalendario) window.refrescarCalendario(); });
        <?php elseif ($_SESSION["crear_cita"] == "domingo"): ?>
        Swal.fire({ icon: "warning", title: "Día no disponible", text: "No se pueden registrar citas los domingos.", confirmButtonText: "Cerrar" });
        <?php elseif ($_SESSION["crear_cita"] == "hora_no_permitida"): ?>
        Swal.fire({ icon: "warning", title: "Hora no permitida", text: "El horario de atención es de 07:30 a 19:50 (Lun-Vie) y de 07:30 a 13:30 los sábados.", confirmButtonText: "Cerrar" });
        <?php elseif ($_SESSION["crear_cita"] == "fecha_pasada"): ?>
        Swal.fire({ icon: "warning", title: "Fecha u hora no válida", text: "No se pueden registrar citas en una fecha u hora anterior a la actual.", confirmButtonText: "Cerrar" });
        <?php else: ?>
        Swal.fire({ icon: "error", title: "¡Error al registrar la cita!", text: "Verifica los datos e intenta nuevamente.", confirmButtonText: "Cerrar" });
        <?php endif; ?>
    });
</script>
<?php unset($_SESSION["crear_cita"]); endif; ?>

<script>
document.addEventListener("DOMContentLoaded", function () {

    // Controlar la fecha: bloquear domingos y ajustar min/max de hora
    document.getElementById("nuevaCitaFecha").addEventListener("change", function () {
        var fechaSeleccionada = new Date(this.value + "T00:00:00");
        var diaSemana         = fechaSeleccionada.getDay(); // 0=domingo, 6=sábado

        // Si eligió domingo, limpiar y avisar
        if (diaSemana === 0) {
            this.value = "";
            document.getElementById("nuevaCitaHora").value = "";
            Swal.fire({
                icon: "warning",
                title: "Día no disponible",
                text: "No se pueden programar citas los domingos.",
                confirmButtonText: "Cerrar"
            });
            return;
        }

        // Cargar médicos disponibles para esa fecha y tipo de cita
        intentarCargarMedicos();

        var horaInput  = document.getElementById("nuevaCitaHora");
        var maxHora    = diaSemana === 6 ? "13:30" : "19:50"; // sábado o lun-vie
        horaInput.max  = maxHora;

        // Si ya tenía una hora fuera del nuevo max, limpiarla
        if (horaInput.value && horaInput.value > maxHora) {
            horaInput.value = "";
        }

        var hoy    = new Date();
        var hoyStr = hoy.getFullYear() + "-" +
                     String(hoy.getMonth() + 1).padStart(2, "0") + "-" +
                     String(hoy.getDate()).padStart(2, "0");
        var hh     = String(hoy.getHours()).padStart(2, "0");
        var mm     = String(hoy.getMinutes()).padStart(2, "0");
        var horaAhora = hh + ":" + mm;

        if (this.value === hoyStr) {
            // Hoy: mínimo es el mayor entre 07:30 y hora actual
            var minHora   = horaAhora >= "07:30" ? horaAhora : "07:30";
            horaInput.min = minHora;
            if (horaInput.value && horaInput.value < minHora) {
                horaInput.value = "";
            }
        } else {
            horaInput.min = "07:30";
            if (horaInput.value && horaInput.value < "07:30") {
                horaInput.value = "";
            }
        }
    });

    // ── FullCalendar ──────────────────────────────────────────────────────────
    var calendarEl = document.getElementById("calendarioCitas");
    var calendar   = new FullCalendar.Calendar(calendarEl, {
        locale:          "es",
        initialView:     "timeGridWeek",
        headerToolbar: {
            left:   "prev,next today",
            center: "title",
            right:  "dayGridMonth,timeGridWeek,timeGridDay"
        },
        buttonText: {
            today:  "Hoy",
            month:  "Mes",
            week:   "Semana",
            day:    "Día"
        },
        height:       "auto",
        nowIndicator: true,
        events: function (info, successCallback, failureCallback) {
            fetch("/Marie_stopes_pruebas/index.php?action=getCitas")
                .then(function (res) { return res.json(); })
                .then(function (data) { successCallback(data); })
                .catch(function () { failureCallback(); });
        },
        eventClick: function (info) {
            var e  = info.event;
            var ep = e.extendedProps;

            // Poblar modal de detalle
            var start = e.start;
            var fecha = start.getFullYear() + "-" +
                        String(start.getMonth() + 1).padStart(2, "0") + "-" +
                        String(start.getDate()).padStart(2, "0");
            var hora  = String(start.getHours()).padStart(2, "0") + ":" +
                        String(start.getMinutes()).padStart(2, "0");

            document.getElementById("detPaciente").textContent      = e.title;
            document.getElementById("detCI").textContent            = ep.pac_ci;
            document.getElementById("detTelefono").textContent      = ep.pac_telefono;
            document.getElementById("detFecha").textContent         = formatearFecha(fecha);
            document.getElementById("detHora").textContent          = hora;
            document.getElementById("detMedico").textContent        = ep.medico;
            document.getElementById("detTipoCita").textContent      = ep.tipo_cita;
            document.getElementById("detRecepcionista").textContent = ep.recepcionista_registra;

            // Badge de estado
            var badge  = document.getElementById("detEstadoBadge");
            var colores = { pendiente: "#3788d8", atendida: "#28a745", cancelada: "#dc3545" };
            badge.textContent             = ep.estado.charAt(0).toUpperCase() + ep.estado.slice(1);
            badge.style.backgroundColor   = colores[ep.estado] || "#3788d8";
            badge.style.color             = "#fff";

            $("#modalDetalleCita").modal("show");
        }
    });
    calendar.render();
    // ─────────────────────────────────────────────────────────────────────────

    // Refrescar calendario al registrar una cita exitosamente
    // (se llama desde el evento de crear_cita ok)
    window.refrescarCalendario = function () {
        calendar.refetchEvents();
    };

    // ── Variables de estado ───────────────────────────────────────────────────
    var tiempoCitaSeleccionado = 0; // minutos del tipo de cita elegido

    // Intentar cargar médicos si ya hay fecha y tipo seleccionados
    function intentarCargarMedicos() {
        var fecha        = document.getElementById("nuevaCitaFecha").value;
        var selectTipo   = document.getElementById("nuevaCitaIdTipoCita");
        var id_tipo_cita = selectTipo.value;
        var selectMedico = document.getElementById("nuevaCitaIdMedico");

        if (!fecha || !id_tipo_cita) {
            selectMedico.innerHTML = '<option value="">Seleccione un médico</option>';
            selectMedico.disabled  = true;
            document.getElementById("labelMedicoInfo").textContent = "Seleccione tipo y fecha primero";
            document.getElementById("nuevaCitaHora").value = "";
            return;
        }

        // Guardar tiempo del tipo seleccionado
        var optionSeleccionada = selectTipo.options[selectTipo.selectedIndex];
        tiempoCitaSeleccionado = parseInt(optionSeleccionada.getAttribute("data-tiempo")) || 0;

        fetch("/Marie_stopes_pruebas/index.php?action=getMedicos&fecha=" + encodeURIComponent(fecha) + "&id_tipo_cita=" + encodeURIComponent(id_tipo_cita))
            .then(function(res) { return res.json(); })
            .then(function(medicos) {
                selectMedico.innerHTML = '<option value="">Seleccione un médico (Opcional)</option>';
                if (medicos.length === 0) {
                    document.getElementById("labelMedicoInfo").textContent = "Sin médicos disponibles ese día";
                    selectMedico.disabled = true;
                } else {
                    medicos.forEach(function(m) {
                        var opt = document.createElement("option");
                        opt.value = m.id_usuario;
                        opt.textContent = m.nombre + " " + m.apellido +
                                          " (" + m.turno_inicio.slice(0,5) + " - " + m.turno_fin.slice(0,5) + ")";
                        opt.setAttribute("data-turno-inicio", m.turno_inicio.slice(0,5));
                        opt.setAttribute("data-turno-fin",    m.turno_fin.slice(0,5));
                        selectMedico.appendChild(opt);
                    });
                    selectMedico.disabled = false;
                    document.getElementById("labelMedicoInfo").textContent = "Opcional";
                }
                // Limpiar hora al cambiar médicos disponibles
                document.getElementById("nuevaCitaHora").value = "";
            });
    }

    // Al cambiar tipo de cita
    document.getElementById("nuevaCitaIdTipoCita").addEventListener("change", function() {
        intentarCargarMedicos();
    });

    // Al cambiar médico → actualizar restricciones de hora según turno y ocupación
    document.getElementById("nuevaCitaIdMedico").addEventListener("change", function() {
        actualizarRestriccionesHora();
    });

    function actualizarRestriccionesHora() {
        var fecha      = document.getElementById("nuevaCitaFecha").value;
        var idMedico   = document.getElementById("nuevaCitaIdMedico").value;
        var horaInput  = document.getElementById("nuevaCitaHora");
        horaInput.value = "";

        if (!idMedico || !fecha) return;

        // Obtener turno del médico seleccionado
        var selectMedico = document.getElementById("nuevaCitaIdMedico");
        var optMedico    = selectMedico.options[selectMedico.selectedIndex];
        var turnoInicio  = optMedico.getAttribute("data-turno-inicio");
        var turnoFin     = optMedico.getAttribute("data-turno-fin");

        // Ajustar min/max según turno del médico
        horaInput.min = turnoInicio;
        horaInput.max = turnoFin;

        // Obtener horas ocupadas y mostrarlas como advertencia
        fetch("/Marie_stopes_pruebas/index.php?action=getHorasOcupadas&id_medico=" + encodeURIComponent(idMedico) + "&fecha=" + encodeURIComponent(fecha))
            .then(function(res) { return res.json(); })
            .then(function(ocupadas) {
                // Guardar en variable global para validar al elegir hora
                window.horasOcupadasMedico = ocupadas;

                if (ocupadas.length > 0) {
                    var lista = ocupadas.map(function(o) {
                        var fin = sumarMinutos(o.hora.slice(0,5), o.tiempo);
                        return o.hora.slice(0,5) + " - " + fin;
                    }).join(", ");
                    document.getElementById("labelMedicoInfo").textContent = "Bloqueado: " + lista;
                } else {
                    document.getElementById("labelMedicoInfo").textContent = "Sin citas ese día";
                }
            });
    }

    // Validar hora elegida contra citas ocupadas
    document.getElementById("nuevaCitaHora").addEventListener("change", function() {
        var horaElegida  = this.value;
        var duracion     = tiempoCitaSeleccionado;
        var ocupadas     = window.horasOcupadasMedico || [];

        if (!horaElegida || !duracion) return;

        var inicioNueva = horaAMinutos(horaElegida);
        var finNueva    = inicioNueva + duracion;

        var conflicto = ocupadas.some(function(o) {
            var inicioOcup = horaAMinutos(o.hora.slice(0,5));
            var finOcup    = inicioOcup + parseInt(o.tiempo);
            // Hay conflicto si los rangos se solapan
            return inicioNueva < finOcup && finNueva > inicioOcup;
        });

        if (conflicto) {
            Swal.fire({
                icon: "warning",
                title: "Hora no disponible",
                text: "El médico ya tiene una cita en ese horario. Por favor elige otra hora.",
                confirmButtonText: "Cerrar"
            });
            this.value = "";
        }
    });

    // Helpers
    function horaAMinutos(hora) {
        var partes = hora.split(":");
        return parseInt(partes[0]) * 60 + parseInt(partes[1]);
    }

    function sumarMinutos(hora, minutos) {
        var total = horaAMinutos(hora) + parseInt(minutos);
        var h = Math.floor(total / 60) % 24;
        var m = total % 60;
        return String(h).padStart(2,"0") + ":" + String(m).padStart(2,"0");
    }

    // Cargar médicos al abrir el modal
    document.getElementById("btnAbrirModalCita").addEventListener("click", function () {
        limpiarModal();
        $("#modalAgregarCita").modal("show");
    });

    // Buscar paciente por carnet
    document.getElementById("btnBuscarPaciente").addEventListener("click", function () {
        buscarPaciente();
    });

    // También buscar al presionar Enter en el campo carnet
    document.getElementById("buscarCarnet").addEventListener("keypress", function (e) {
        if (e.key === "Enter") {
            e.preventDefault();
            buscarPaciente();
        }
    });

    // Botón Nuevo Paciente: muestra el formulario inline
    document.getElementById("btnNuevoPaciente").addEventListener("click", function () {
        document.getElementById("infoPaciente").style.display            = "none";
        document.getElementById("errorPaciente").style.display           = "none";
        document.getElementById("formNuevoPacienteInline").style.display = "block";
        document.getElementById("btnGuardarCita").disabled               = true;
    });

    // Cancelar registro inline
    document.getElementById("btnCancelarNuevoPaciente").addEventListener("click", function () {
        limpiarFormularioNuevoPaciente();
        document.getElementById("formNuevoPacienteInline").style.display = "none";
    });

    // Guardar nuevo paciente vía AJAX
    document.getElementById("btnRegistrarNuevoPaciente").addEventListener("click", function () {
        var btn = this;
        var datos = {
            action:           "registrarPacienteCita",
            nombre:           document.getElementById("npNombre").value.trim(),
            apellidos:        document.getElementById("npApellidos").value.trim(),
            ci:               document.getElementById("npCI").value.trim(),
            grupo_sanguineo:  document.getElementById("npGrupoSanguineo").value.trim(),
            telefono:         document.getElementById("npTelefono").value.trim(),
            fecha_nacimiento: document.getElementById("npFechaNacimiento").value,
            direccion:        document.getElementById("npDireccion").value.trim()
        };

        // Validar campos vacíos
        for (var key in datos) {
            if (key !== "action" && datos[key] === "") {
                Swal.fire({ icon: "warning", title: "Todos los campos son obligatorios", confirmButtonText: "Cerrar" });
                return;
            }
        }

        // Deshabilitar botón para evitar doble envío
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Guardando...';

        fetch("index.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: new URLSearchParams(datos)
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-save"></i> Guardar paciente';

            if (data.error) {
                Swal.fire({ icon: "error", title: data.error, confirmButtonText: "Cerrar" });
            } else {
                // Ocultar formulario inline y mostrar tarjeta del paciente recién creado
                document.getElementById("formNuevoPacienteInline").style.display = "none";
                limpiarFormularioNuevoPaciente();

                document.getElementById("infoPaciente").style.display    = "block";
                document.getElementById("pacNombre").textContent         = data.nombre + " " + data.apellidos;
                document.getElementById("pacCI").textContent             = data.ci;
                document.getElementById("pacFechaNac").textContent       = formatearFecha(data.fecha_nacimiento);
                document.getElementById("pacTelefono").textContent       = data.telefono;
                document.getElementById("pacDireccion").textContent      = data.direccion;
                document.getElementById("pacGrupoSanguineo").textContent = data.grupo_sanguineo;
                document.getElementById("nuevaCitaIdPaciente").value     = data.id_paciente;
                document.getElementById("btnGuardarCita").disabled       = false;

                Swal.fire({ icon: "success", title: "Paciente registrado correctamente", confirmButtonText: "Cerrar" });
            }
        })
        .catch(function () {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-save"></i> Guardar paciente';
            Swal.fire({ icon: "error", title: "Error al registrar el paciente", confirmButtonText: "Cerrar" });
        });
    });

    function buscarPaciente() {
        var carnet = document.getElementById("buscarCarnet").value.trim();
        if (carnet === "") {
            Swal.fire({ icon: "warning", title: "Ingresa el número de carnet", confirmButtonText: "Cerrar" });
            return;
        }

        fetch("index.php?action=buscarPacienteCarnet&carnet=" + encodeURIComponent(carnet))
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.error) {
                    document.getElementById("infoPaciente").style.display  = "none";
                    document.getElementById("errorPaciente").style.display = "block";
                    document.getElementById("nuevaCitaIdPaciente").value   = "";
                    document.getElementById("btnGuardarCita").disabled     = true;
                } else {
                    document.getElementById("errorPaciente").style.display    = "none";
                    document.getElementById("infoPaciente").style.display     = "block";
                    document.getElementById("pacNombre").textContent          = data.nombre + " " + data.apellidos;
                    document.getElementById("pacCI").textContent              = data.ci;
                    document.getElementById("pacFechaNac").textContent        = formatearFecha(data.fecha_nacimiento);
                    document.getElementById("pacTelefono").textContent        = data.telefono;
                    document.getElementById("pacDireccion").textContent       = data.direccion;
                    document.getElementById("pacGrupoSanguineo").textContent  = data.grupo_sanguineo;
                    document.getElementById("nuevaCitaIdPaciente").value      = data.id_paciente;
                    document.getElementById("btnGuardarCita").disabled        = false;
                }
            })
            .catch(function () {
                Swal.fire({ icon: "error", title: "Error al buscar el paciente", confirmButtonText: "Cerrar" });
            });
    }

    // Convierte "1990-04-15" a "15/04/1990"
    function formatearFecha(fecha) {
        if (!fecha) return "";
        var partes = fecha.split("-");
        return partes[2] + "/" + partes[1] + "/" + partes[0];
    }

    function limpiarFormularioNuevoPaciente() {
        document.getElementById("npNombre").value          = "";
        document.getElementById("npApellidos").value       = "";
        document.getElementById("npCI").value              = "";
        document.getElementById("npGrupoSanguineo").value  = "";
        document.getElementById("npTelefono").value        = "";
        document.getElementById("npFechaNacimiento").value = "";
        document.getElementById("npDireccion").value       = "";
    }

    function limpiarModal() {
        document.getElementById("buscarCarnet").value             = "";
        document.getElementById("nuevaCitaIdPaciente").value      = "";
        document.getElementById("nuevaCitaFecha").value           = "";
        document.getElementById("nuevaCitaHora").value            = "";
        document.getElementById("nuevaCitaIdTipoCita").value      = "";
        document.getElementById("nuevaCitaIdMedico").innerHTML    = '<option value="">Seleccione un médico</option>';
        document.getElementById("nuevaCitaIdMedico").disabled     = true;
        document.getElementById("labelMedicoInfo").textContent    = "Seleccione tipo y fecha primero";
        document.getElementById("pacNombre").textContent          = "";
        document.getElementById("pacCI").textContent              = "";
        document.getElementById("pacFechaNac").textContent        = "";
        document.getElementById("pacTelefono").textContent        = "";
        document.getElementById("pacDireccion").textContent       = "";
        document.getElementById("pacGrupoSanguineo").textContent  = "";
        document.getElementById("infoPaciente").style.display            = "none";
        document.getElementById("errorPaciente").style.display           = "none";
        document.getElementById("formNuevoPacienteInline").style.display = "none";
        document.getElementById("btnGuardarCita").disabled               = true;
        limpiarFormularioNuevoPaciente();
        window.horasOcupadasMedico = [];
        tiempoCitaSeleccionado     = 0;
    }

});
</script>