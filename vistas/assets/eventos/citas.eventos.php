<?php if (isset($_SESSION["crear_cita"])): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        <?php if ($_SESSION["crear_cita"] == "ok"): ?>
        Swal.fire({ icon: "success", title: "¡Cita registrada correctamente!", confirmButtonText: "Cerrar" })
            .then(function () { if (window.refrescarCalendario) window.refrescarCalendario(); });
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

    // Controlar el min de la hora según la fecha seleccionada
    document.getElementById("nuevaCitaFecha").addEventListener("change", function () {
        var hoy       = new Date();
        var horaInput = document.getElementById("nuevaCitaHora");
        var hh        = String(hoy.getHours()).padStart(2, "0");
        var mm        = String(hoy.getMinutes()).padStart(2, "0");
        var horaMin   = hh + ":" + mm;

        // Construir "hoy" en formato YYYY-MM-DD usando hora local (no UTC)
        var hoyStr = hoy.getFullYear() + "-" +
                     String(hoy.getMonth() + 1).padStart(2, "0") + "-" +
                     String(hoy.getDate()).padStart(2, "0");

        if (this.value === hoyStr) {
            horaInput.min = horaMin;
            if (horaInput.value && horaInput.value <= horaMin) {
                horaInput.value = "";
            }
        } else {
            horaInput.min = "";
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

    // Cargar médicos al abrir el modal
    document.getElementById("btnAbrirModalCita").addEventListener("click", function () {
        cargarMedicos();
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

    function cargarMedicos() {
        fetch("index.php?action=getMedicos")
            .then(function (res) { return res.json(); })
            .then(function (medicos) {
                var select = document.getElementById("nuevaCitaIdMedico");
                select.innerHTML = '<option value="">Seleccione un médico</option>';
                medicos.forEach(function (m) {
                    var opt = document.createElement("option");
                    opt.value       = m.id_usuario;
                    opt.textContent = m.nombre + " " + m.apellido;
                    select.appendChild(opt);
                });
            });
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
        document.getElementById("nuevaCitaIdMedico").value        = "";
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
    }

});
</script>