<?php if (isset($_SESSION["crear_cita"])): ?>
<script>
document.addEventListener("DOMContentLoaded", function() {
    var msgs = {
        ok:              { icon: "success", title: "¡Cita registrada correctamente!" },
        domingo:         { icon: "warning", title: "Día no disponible",       text: "No se pueden registrar citas los domingos." },
        hora_no_permitida: { icon: "warning", title: "Hora no permitida",     text: "Esa hora está fuera del horario de atención del médico seleccionado." },
        fecha_pasada:    { icon: "warning", title: "Fecha u hora no válida",  text: "No se pueden registrar citas en una fecha u hora anterior a la actual." }
    };
    var cfg = msgs["<?= $_SESSION['crear_cita'] ?>"] || { icon: "error", title: "¡Error al registrar la cita!", text: "Verifica los datos e intenta nuevamente." };
    cfg.confirmButtonText = "Cerrar";
    Swal.fire(cfg).then(function() { if (cfg.icon === "success" && window.refrescarCalendario) window.refrescarCalendario(); });
});
</script>
<?php unset($_SESSION["crear_cita"]); endif; ?>

<script>
document.addEventListener("DOMContentLoaded", function () {

    // ── Helpers ───────────────────────────────────────────────────────────────
    var id  = function(s) { return document.getElementById(s); };
    var txt = function(s, v) { id(s).textContent = v; };
    var val = function(s, v) { if (v !== undefined) id(s).value = v; return id(s).value; };

    function horaAMinutos(h) { var p = h.split(":"); return +p[0] * 60 + +p[1]; }
    function sumarMinutos(h, m) {
        var t = horaAMinutos(h) + +m;
        return String(Math.floor(t/60)%24).padStart(2,"0") + ":" + String(t%60).padStart(2,"0");
    }
    function formatearFecha(f) { if (!f) return ""; var p = f.split("-"); return p[2]+"/"+p[1]+"/"+p[0]; }
    function fetchJson(url) { return fetch(url).then(function(r) { return r.json(); }); }
    function postJson(url, data) {
        return fetch(url, { method:"POST", headers:{"Content-Type":"application/x-www-form-urlencoded"}, body: new URLSearchParams(data) })
               .then(function(r) { return r.json(); });
    }
    function swal(opts) { return Swal.fire(Object.assign({ confirmButtonText: "Cerrar" }, opts)); }

    function cargarHorasOcupadas(idMedico, fecha, labelId, storageKey, excluirCita) {
        var url = "/Marie_stopes_pruebas/index.php?action=getHorasOcupadas&id_medico=" + encodeURIComponent(idMedico) + "&fecha=" + encodeURIComponent(fecha);
        if (excluirCita) url += "&excluir_cita=" + encodeURIComponent(excluirCita);
        return fetchJson(url).then(function(ocupadas) {
            window[storageKey] = ocupadas;
            if (labelId) {
                txt(labelId, ocupadas.length
                    ? "Bloqueado: " + ocupadas.map(function(o) { return o.hora.slice(0,5)+" - "+sumarMinutos(o.hora.slice(0,5),o.tiempo); }).join(", ")
                    : "Sin citas ese día");
            }
            return ocupadas;
        });
    }

    // ── Variables de estado ───────────────────────────────────────────────────
    var repTiempo = 0, repIdCita = null;
    var medicoActualId = null, medicoActualNombre = "";
    // Horario real del médico seleccionado (HH:MM), vacío = no atiende ese día
    var medicoHorario = { inicio: "", fin: "", inicioSabado: "", finSabado: "" };

    // Rango de atención del médico actual para una fecha dada (lun-vie vs sábado)
    function rangoHorarioMedico(fecha) {
        var esSabado = new Date(fecha + "T00:00:00").getDay() === 6;
        return esSabado
            ? { inicio: medicoHorario.inicioSabado, fin: medicoHorario.finSabado }
            : { inicio: medicoHorario.inicio,        fin: medicoHorario.fin };
    }

    // businessHours de FullCalendar a partir del horario del médico actual
    function construirBusinessHours() {
        var bh = [];
        if (medicoHorario.inicio && medicoHorario.fin) {
            bh.push({ daysOfWeek: [1,2,3,4,5], startTime: medicoHorario.inicio, endTime: medicoHorario.fin });
        }
        if (medicoHorario.inicioSabado && medicoHorario.finSabado) {
            bh.push({ daysOfWeek: [6], startTime: medicoHorario.inicioSabado, endTime: medicoHorario.finSabado });
        }
        return bh;
    }

    // ── Panel de selección de médico ─────────────────────────────────────────
    function cargarPanelMedicos() {
        fetchJson("/Marie_stopes_pruebas/index.php?action=getMedicosActivos").then(function(medicos) {
            var cont = id("listaMedicos");
            cont.innerHTML = "";
            if (!medicos.length) {
                cont.innerHTML = '<div class="col-12"><p class="text-muted">No hay médicos activos registrados.</p></div>';
                return;
            }
            medicos.forEach(function(m) {
                var col = document.createElement("div");
                col.className = "col-md-3 col-sm-4 col-6 mb-3";
                col.innerHTML =
                    '<div class="card bloque-medico" data-id="' + m.id_usuario + '" ' +
                         'data-nombre="' + (m.nombre + " " + m.apellido).replace(/"/g,"") + '" ' +
                         'data-hora-inicio="'        + (m.hora_inicio  ? m.hora_inicio.slice(0,5)  : "") + '" ' +
                         'data-hora-fin="'           + (m.hora_fin     ? m.hora_fin.slice(0,5)     : "") + '" ' +
                         'data-hora-inicio-sabado="' + (m.horaI_sabado ? m.horaI_sabado.slice(0,5) : "") + '" ' +
                         'data-hora-fin-sabado="'    + (m.horaF_sabado ? m.horaF_sabado.slice(0,5) : "") + '" ' +
                         'style="cursor:pointer; text-align:center; padding:15px; border:1px solid #ddd; border-radius:8px;">' +
                        '<i class="fa fa-user-md" style="font-size:32px; color:#3788d8;"></i>' +
                        '<p style="margin:8px 0 0; font-weight:600;">' + m.nombre + ' ' + m.apellido + '</p>' +
                    '</div>';
                cont.appendChild(col);
            });
            cont.querySelectorAll(".bloque-medico").forEach(function(el) {
                el.addEventListener("click", function() {
                    medicoActualId     = this.getAttribute("data-id");
                    medicoActualNombre = this.getAttribute("data-nombre");
                    medicoHorario = {
                        inicio:       this.getAttribute("data-hora-inicio"),
                        fin:          this.getAttribute("data-hora-fin"),
                        inicioSabado: this.getAttribute("data-hora-inicio-sabado"),
                        finSabado:    this.getAttribute("data-hora-fin-sabado")
                    };
                    seleccionarMedico();
                });
            });
        });
    }

    function seleccionarMedico() {
        id("panelMedicos").style.display    = "none";
        id("panelCalendario").style.display = "block";
        id("panelEstados").style.display    = "block";
        id("boxCalendario").style.display   = "block";
        txt("medicoSeleccionadoLabel", medicoActualNombre);
        calendar.setOption("businessHours", construirBusinessHours());
        calendar.refetchEvents();
        setTimeout(function() { calendar.updateSize(); }, 50);
    }

    id("btnCambiarMedico").addEventListener("click", function() {
        medicoActualId = null; medicoActualNombre = "";
        id("panelMedicos").style.display    = "block";
        id("panelCalendario").style.display = "none";
        id("panelEstados").style.display    = "none";
        id("boxCalendario").style.display   = "none";
    });

    // ── FullCalendar ──────────────────────────────────────────────────────────
    var calendar = new FullCalendar.Calendar(id("calendarioCitas"), {
        locale: "es", initialView: "timeGridWeek", slotMinTime: "07:00:00",
        slotMaxTime: "21:00:00", hiddenDays: [0], height: "auto", nowIndicator: true,
        businessHours: [], // se llena con el horario real del médico al seleccionarlo
        headerToolbar: { left:"prev,next today", center:"title", right:"dayGridMonth,timeGridWeek,timeGridDay" },
        buttonText: { today:"Hoy", month:"Mes", week:"Semana", day:"Día" },
        events: function(info, ok, fail) {
            if (!medicoActualId) { ok([]); return; }
            fetchJson("/Marie_stopes_pruebas/index.php?action=getCitas&id_medico=" + encodeURIComponent(medicoActualId))
                .then(ok).catch(fail);
        },
        dateClick: function(info) {
            abrirFormularioDesdeCalendario(info.date);
        },
        eventClick: function(info) {
            var e = info.event, ep = e.extendedProps, s = e.start;
            repIdCita = e.id;
            var fecha = s.getFullYear()+"-"+String(s.getMonth()+1).padStart(2,"0")+"-"+String(s.getDate()).padStart(2,"0");
            var hora  = String(s.getHours()).padStart(2,"0")+":"+String(s.getMinutes()).padStart(2,"0");

            // Poblar modal detalle
            txt("detPaciente", e.title);  txt("detCI", ep.pac_ci);    txt("detTelefono", ep.pac_telefono);
            txt("detFecha", formatearFecha(fecha)); txt("detHora", hora);
            txt("detMedico", ep.medico);  txt("detTipoCita", ep.tipo_cita);
            val("detIdCita", e.id);

            // Poblar modal reprogramar
            val("repIdCitaOriginal", e.id); txt("repPaciente", e.title);
            txt("repFechaActual", formatearFecha(fecha)); txt("repHoraActual", hora); txt("repMedicoActual", ep.medico);

            // Badge de estado
            var badge = id("detEstadoBadge"), colores = { pendiente:"#3788d8", atendida:"#28a745", cancelada:"#dc3545" };
            badge.textContent = ep.estado.charAt(0).toUpperCase() + ep.estado.slice(1);
            badge.style.backgroundColor = colores[ep.estado] || "#3788d8";
            badge.style.color = "#fff";

            // Badge de pago
            var pagoBadge = id("detPagoBadge");
            pagoBadge.textContent = ep.pagado ? "Pagada" : "Pendiente de pago";
            pagoBadge.style.backgroundColor = ep.pagado ? "#28a745" : "#ffc107";
            pagoBadge.style.color = ep.pagado ? "#fff" : "#212529";

            // Botones según estado
            var visible = ep.estado === "pendiente" ? "inline-block" : "none";
            id("btnAbrirReprogramar").style.display = visible;
            id("btnCancelarCita").style.display     = visible;
            id("btnIrACobrar").style.display        = (ep.estado === "pendiente" && !ep.pagado) ? "inline-block" : "none";

            $("#modalDetalleCita").modal("show");
        }
    });
    calendar.render();
    window.refrescarCalendario = function() { if (medicoActualId) calendar.refetchEvents(); };

    cargarPanelMedicos();

    id("btnIrACobrar").addEventListener("click", function() {
        window.location.href = "index.php?ruta=panel_pago&id_cita=" + val("detIdCita");
    });

    // ── Clic en el calendario: abre el formulario con médico/fecha/hora fijos ─
    function abrirFormularioDesdeCalendario(d) {
        var fecha = d.getFullYear()+"-"+String(d.getMonth()+1).padStart(2,"0")+"-"+String(d.getDate()).padStart(2,"0");
        var hora  = String(d.getHours()).padStart(2,"0")+":"+String(d.getMinutes()).padStart(2,"0");
        var dia   = d.getDay();

        if (dia === 0) { swal({ icon:"warning", title:"Día no disponible", text:"No se pueden registrar citas los domingos." }); return; }

        var rango = rangoHorarioMedico(fecha);
        if (!rango.inicio || !rango.fin) {
            swal({ icon:"warning", title:"Médico no disponible", text: medicoActualNombre + " no atiende ese día." });
            return;
        }
        if (hora < rango.inicio || hora > rango.fin) {
            swal({ icon:"warning", title:"Hora fuera de horario", text: medicoActualNombre + " atiende ese día de " + rango.inicio + " a " + rango.fin + "." });
            return;
        }

        var ahora = new Date();
        var fechaHoy  = ahora.getFullYear()+"-"+String(ahora.getMonth()+1).padStart(2,"0")+"-"+String(ahora.getDate()).padStart(2,"0");
        var horaAhora = String(ahora.getHours()).padStart(2,"0")+":"+String(ahora.getMinutes()).padStart(2,"0");

        if (fecha < fechaHoy || (fecha === fechaHoy && hora <= horaAhora)) {
            swal({ icon:"warning", title:"Fecha u hora no válida", text:"No se pueden registrar citas en una fecha u hora pasada." });
            return;
        }

        limpiarModal();
        val("nuevaCitaIdMedico", medicoActualId);
        id("nuevaCitaIdMedicoDisplay").innerHTML = '<option value="' + medicoActualId + '">' + medicoActualNombre + '</option>';
        val("nuevaCitaFecha", fecha);
        val("nuevaCitaFechaDisplay", fecha);
        val("nuevaCitaHora", hora);
        val("nuevaCitaHoraDisplay", hora);

        cargarHorasOcupadas(medicoActualId, fecha, null, "horasOcupadasMedico", null).then(function(ocupadas) {
            deshabilitarTiposSegunHora(hora, ocupadas);
        });

        $("#modalAgregarCita").modal("show");
    }

    function deshabilitarTiposSegunHora(horaVal, ocupadas) {
        var ini = horaAMinutos(horaVal);
        var sel = id("nuevaCitaIdTipoCita");
        Array.prototype.forEach.call(sel.options, function(opt) {
            if (!opt.value) return;
            var tiempo = parseInt(opt.getAttribute("data-tiempo")) || 0;
            var fin = ini + tiempo;
            var conflicto = ocupadas.some(function(o) {
                var oi = horaAMinutos(o.hora.slice(0,5)), of2 = oi + +o.tiempo;
                return ini < of2 && fin > oi;
            });
            opt.disabled = conflicto;
            opt.title = conflicto ? "No cabe en este horario, se cruza con otra cita" : "";
        });
        val("nuevaCitaIdTipoCita", "");
    }

    // ── Detalle: Reprogramar ──────────────────────────────────────────────────
    id("btnAbrirReprogramar").addEventListener("click", function() {
        $("#modalDetalleCita").modal("hide");
        setTimeout(function() { limpiarModalReprogramar(); $("#modalReprogramarCita").modal("show"); }, 400);
    });

    // ── Detalle: Cancelar cita ────────────────────────────────────────────────
    id("btnCancelarCita").addEventListener("click", function() {
        swal({
            icon:"warning", title:"¿Cancelar esta cita?",
            html:"Se cancelará la cita de <strong>" + id("detPaciente").textContent + "</strong>.<br>Esta acción no se puede deshacer.",
            showCancelButton:true, confirmButtonColor:"#dc3545", cancelButtonColor:"#6c757d",
            confirmButtonText:'<i class="fa fa-ban"></i> Sí, cancelar', cancelButtonText:"No, volver"
        }).then(function(r) {
            if (!r.value) return;
            postJson("/Marie_stopes_pruebas/index.php", { action:"cancelarCita", id_cita: val("detIdCita") })
            .then(function(data) {
                if (data.ok) {
                    $("#modalDetalleCita").modal("hide");
                    swal({ icon:"success", title:"Cita cancelada", text:"La cita ha sido cancelada correctamente." })
                        .then(function() { if (window.refrescarCalendario) window.refrescarCalendario(); });
                } else {
                    swal({ icon:"error", title: data.error || "Error al cancelar la cita" });
                }
            }).catch(function() { swal({ icon:"error", title:"Error de conexión" }); });
        });
    });

    // ── Reprogramar: eventos (sigue con médico elegido en el formulario) ──────
    id("repFecha").addEventListener("change", function() {
        var d = new Date(this.value + "T00:00:00");
        if (d.getDay() === 0) { this.value = ""; swal({ icon:"warning", title:"Día no disponible", text:"No se pueden programar citas los domingos." }); return; }
        var horaInput = id("repHora"), maxHora = d.getDay() === 6 ? "13:30" : "19:50";
        horaInput.max = maxHora;
        if (horaInput.value > maxHora) horaInput.value = "";
        cargarMedicosReprogramar();
    });
    id("repIdTipoCita").addEventListener("change", function() { cargarMedicosReprogramar(); });
    id("repIdMedico").addEventListener("change", function() { actualizarHoraReprogramar(); });
    id("repHora").addEventListener("change", function() {
        validarConflictoHora(this.value, repTiempo, window.repHorasOcupadas || [], this);
    });

    function validarConflictoHora(horaVal, tiempo, ocupadas, inputEl) {
        if (!horaVal || !tiempo) return;
        var ini = horaAMinutos(horaVal), fin = ini + tiempo;
        var conflicto = ocupadas.some(function(o) {
            var oi = horaAMinutos(o.hora.slice(0,5)), of2 = oi + +o.tiempo;
            return ini < of2 && fin > oi;
        });
        if (conflicto) { swal({ icon:"warning", title:"Hora no disponible", text:"El médico ya tiene una cita en ese horario." }); inputEl.value = ""; }
    }

    function construirOpcionesMedicos(select, medicos, labelId) {
        select.innerHTML = '<option value="">Seleccione un médico</option>';
        if (!medicos.length) {
            txt(labelId, "Sin médicos disponibles ese día");
            select.disabled = true;
            return;
        }
        medicos.forEach(function(m) {
            var o = document.createElement("option");
            o.value = m.id_usuario;
            o.textContent = m.nombre + " " + m.apellido + " (" + m.turno_inicio.slice(0,5) + " - " + m.turno_fin.slice(0,5) + ")";
            o.setAttribute("data-turno-inicio", m.turno_inicio.slice(0,5));
            o.setAttribute("data-turno-fin",    m.turno_fin.slice(0,5));
            select.appendChild(o);
        });
        select.disabled = false;
        txt(labelId, "Disponible");
    }

    function cargarMedicosReprogramar() {
        var fecha   = val("repFecha");
        var selTipo = id("repIdTipoCita");
        var selMed  = id("repIdMedico");

        if (!fecha || !selTipo.value) {
            selMed.innerHTML = '<option value="">Seleccione un médico</option>'; selMed.disabled = true;
            txt("repLabelMedico", "Seleccione fecha y tipo primero");
            val("repHora", ""); return;
        }
        repTiempo = parseInt(selTipo.options[selTipo.selectedIndex].getAttribute("data-tiempo")) || 0;

        fetchJson("/Marie_stopes_pruebas/index.php?action=getMedicos&fecha="+encodeURIComponent(fecha))
            .then(function(medicos) { construirOpcionesMedicos(selMed, medicos, "repLabelMedico"); val("repHora", ""); });
    }

    function actualizarHoraReprogramar() {
        var fecha  = val("repFecha");
        var selMed = id("repIdMedico");
        val("repHora", ""); if (!selMed.value || !fecha) return;
        var opt = selMed.options[selMed.selectedIndex];
        id("repHora").min = opt.getAttribute("data-turno-inicio");
        id("repHora").max = opt.getAttribute("data-turno-fin");
        cargarHorasOcupadas(selMed.value, fecha, "repLabelMedico", "repHorasOcupadas", repIdCita);
    }

    // ── Agregar cita: eventos ─────────────────────────────────────────────────
    id("btnBuscarPaciente").addEventListener("click", buscarPaciente);
    id("buscarCarnet").addEventListener("keypress", function(e) { if (e.key === "Enter") { e.preventDefault(); buscarPaciente(); } });
    id("btnNuevoPaciente").addEventListener("click", function() {
        id("infoPaciente").style.display = id("errorPaciente").style.display = "none";
        id("formNuevoPacienteInline").style.display = "block";
        id("btnGuardarCita").disabled = true;
    });
    id("btnCancelarNuevoPaciente").addEventListener("click", function() {
        limpiarFormPaciente(); id("formNuevoPacienteInline").style.display = "none";
    });

    // ── Registrar nuevo paciente ──────────────────────────────────────────────
    id("btnRegistrarNuevoPaciente").addEventListener("click", function() {
        var btn = this;
        var campos = ["npNombre","npApellidos","npCI","npGrupoSanguineo","npTelefono","npFechaNacimiento","npDireccion"];
        for (var i = 0; i < campos.length; i++) {
            if (!id(campos[i]).value.trim()) { swal({ icon:"warning", title:"Todos los campos son obligatorios" }); return; }
        }
        btn.disabled = true; btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Guardando...';
        postJson("/Marie_stopes_pruebas/index.php", {
            action:"registrarPacienteCita",
            nombre: id("npNombre").value.trim(),           apellidos: id("npApellidos").value.trim(),
            ci: id("npCI").value.trim(),                   grupo_sanguineo: id("npGrupoSanguineo").value.trim(),
            telefono: id("npTelefono").value.trim(),       fecha_nacimiento: val("npFechaNacimiento"),
            direccion: id("npDireccion").value.trim()
        }).then(function(data) {
            btn.disabled = false; btn.innerHTML = '<i class="fa fa-save"></i> Guardar paciente';
            if (data.error) { swal({ icon:"error", title: data.error }); return; }
            id("formNuevoPacienteInline").style.display = "none"; limpiarFormPaciente();
            id("infoPaciente").style.display = "block";
            txt("pacNombre", data.nombre+" "+data.apellidos); txt("pacCI", data.ci);
            txt("pacFechaNac", formatearFecha(data.fecha_nacimiento)); txt("pacTelefono", data.telefono);
            txt("pacDireccion", data.direccion); txt("pacGrupoSanguineo", data.grupo_sanguineo);
            val("nuevaCitaIdPaciente", data.id_paciente); id("btnGuardarCita").disabled = false;
            swal({ icon:"success", title:"Paciente registrado correctamente" });
        }).catch(function() {
            btn.disabled = false; btn.innerHTML = '<i class="fa fa-save"></i> Guardar paciente';
            swal({ icon:"error", title:"Error al registrar el paciente" });
        });
    });

    // ── Confirmar reprogramación ──────────────────────────────────────────────
    id("btnConfirmarReprogramar").addEventListener("click", function() {
        var btn = this, fecha = val("repFecha"), hora = val("repHora"), idM = val("repIdMedico"), idT = val("repIdTipoCita");
        if (!fecha || !hora || !idM || !idT) { swal({ icon:"warning", title:"Completa todos los campos" }); return; }
        btn.disabled = true; btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Reprogramando...';
        postJson("/Marie_stopes_pruebas/index.php", {
            action:"reprogramarCita", id_cita_original: val("repIdCitaOriginal"),
            repFecha: fecha, repHora: hora, repIdMedico: idM, repIdTipoCita: idT
        }).then(function(data) {
            btn.disabled = false; btn.innerHTML = '<i class="fa fa-calendar-check-o"></i> Confirmar reprogramación';
            if (data.ok) {
                $("#modalReprogramarCita").modal("hide");
                swal({ icon:"success", title:"¡Cita reprogramada correctamente!" })
                    .then(function() { if (window.refrescarCalendario) window.refrescarCalendario(); });
            } else { swal({ icon:"error", title: data.error || "Error al reprogramar" }); }
        }).catch(function() {
            btn.disabled = false; btn.innerHTML = '<i class="fa fa-calendar-check-o"></i> Confirmar reprogramación';
            swal({ icon:"error", title:"Error de conexión" });
        });
    });

    // ── Funciones compartidas ─────────────────────────────────────────────────
    function buscarPaciente() {
        var carnet = id("buscarCarnet").value.trim();
        if (!carnet) { swal({ icon:"warning", title:"Ingresa el número de carnet" }); return; }
        fetchJson("/Marie_stopes_pruebas/index.php?action=buscarPacienteCarnet&carnet="+encodeURIComponent(carnet))
        .then(function(data) {
            if (data.error) {
                id("infoPaciente").style.display = "none"; id("errorPaciente").style.display = "block";
                val("nuevaCitaIdPaciente",""); id("btnGuardarCita").disabled = true;
            } else {
                id("errorPaciente").style.display = "none"; id("infoPaciente").style.display = "block";
                txt("pacNombre", data.nombre+" "+data.apellidos); txt("pacCI", data.ci);
                txt("pacFechaNac", formatearFecha(data.fecha_nacimiento)); txt("pacTelefono", data.telefono);
                txt("pacDireccion", data.direccion); txt("pacGrupoSanguineo", data.grupo_sanguineo);
                val("nuevaCitaIdPaciente", data.id_paciente); id("btnGuardarCita").disabled = false;
            }
        }).catch(function() { swal({ icon:"error", title:"Error al buscar el paciente" }); });
    }

    function limpiarFormPaciente() {
        ["npNombre","npApellidos","npCI","npGrupoSanguineo","npTelefono","npFechaNacimiento","npDireccion"]
            .forEach(function(s) { id(s).value = ""; });
    }

    function limpiarModalReprogramar() {
        val("repFecha",""); val("repHora",""); val("repIdTipoCita","");
        id("repIdMedico").innerHTML = '<option value="">Seleccione un médico</option>'; id("repIdMedico").disabled = true;
        txt("repLabelMedico","Seleccione fecha y tipo primero");
        window.repHorasOcupadas = []; repTiempo = 0;
    }

    function limpiarModal() {
        val("buscarCarnet",""); val("nuevaCitaIdPaciente","");
        val("nuevaCitaIdMedico",""); val("nuevaCitaFecha",""); val("nuevaCitaHora","");
        val("nuevaCitaFechaDisplay",""); val("nuevaCitaHoraDisplay","");
        id("nuevaCitaIdMedicoDisplay").innerHTML = '<option value="">—</option>';
        val("nuevaCitaIdTipoCita","");
        Array.prototype.forEach.call(id("nuevaCitaIdTipoCita").options, function(o) { o.disabled = false; o.title = ""; });
        ["pacNombre","pacCI","pacFechaNac","pacTelefono","pacDireccion","pacGrupoSanguineo"].forEach(function(s) { txt(s,""); });
        id("infoPaciente").style.display = id("errorPaciente").style.display = id("formNuevoPacienteInline").style.display = "none";
        id("btnGuardarCita").disabled = true;
        limpiarFormPaciente(); window.horasOcupadasMedico = [];
    }

});
</script>