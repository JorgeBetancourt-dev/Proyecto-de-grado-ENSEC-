<?php if (isset($_SESSION["crear_cita"])): ?>
<script>
document.addEventListener("DOMContentLoaded", function() {
    var msgs = {
        ok:              { icon: "success", title: "¡Cita registrada correctamente!" },
        domingo:         { icon: "warning", title: "Día no disponible",       text: "No se pueden registrar citas los domingos." },
        hora_no_permitida: { icon: "warning", title: "Hora no permitida",     text: "El horario de atención es de 07:30 a 19:50 (Lun-Vie) y de 07:30 a 13:30 los sábados." },
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

    function validarConflictoHora(horaVal, tiempo, ocupadas, inputEl) {
        if (!horaVal || !tiempo) return;
        var ini = horaAMinutos(horaVal), fin = ini + tiempo;
        var conflicto = ocupadas.some(function(o) {
            var oi = horaAMinutos(o.hora.slice(0,5)), of2 = oi + +o.tiempo;
            return ini < of2 && fin > oi;
        });
        if (conflicto) { swal({ icon:"warning", title:"Hora no disponible", text:"El médico ya tiene una cita en ese horario." }); inputEl.value = ""; }
    }

    function cargarHorasOcupadas(idMedico, fecha, labelId, storageKey, excluirCita) {
        var url = "/Marie_stopes_pruebas/index.php?action=getHorasOcupadas&id_medico=" + encodeURIComponent(idMedico) + "&fecha=" + encodeURIComponent(fecha);
        if (excluirCita) url += "&excluir_cita=" + encodeURIComponent(excluirCita);
        fetchJson(url).then(function(ocupadas) {
            window[storageKey] = ocupadas;
            txt(labelId, ocupadas.length
                ? "Bloqueado: " + ocupadas.map(function(o) { return o.hora.slice(0,5)+" - "+sumarMinutos(o.hora.slice(0,5),o.tiempo); }).join(", ")
                : "Sin citas ese día");
        });
    }

    // ── Variables de estado ───────────────────────────────────────────────────
    var tiempoCita = 0, repTiempo = 0, repIdCita = null;

    // ── FullCalendar ──────────────────────────────────────────────────────────
    var calendar = new FullCalendar.Calendar(id("calendarioCitas"), {
        locale: "es", initialView: "timeGridWeek",slotMinTime: "07:00:00",
        slotMaxTime: "21:00:00",hiddenDays: [0], height: "auto", nowIndicator: true,
        headerToolbar: { left:"prev,next today", center:"title", right:"dayGridMonth,timeGridWeek,timeGridDay" },
        buttonText: { today:"Hoy", month:"Mes", week:"Semana", day:"Día" },
        events: function(info, ok, fail) {
            fetchJson("/Marie_stopes_pruebas/index.php?action=getCitas").then(ok).catch(fail);
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

            // Botones según estado
            var visible = ep.estado === "pendiente" ? "inline-block" : "none";
            id("btnAbrirReprogramar").style.display = visible;
            id("btnCancelarCita").style.display     = visible;

            $("#modalDetalleCita").modal("show");
        }
    });
    calendar.render();
    window.refrescarCalendario = function() { calendar.refetchEvents(); };

    // ── Detalle: Reprogramar ──────────────────────────────────────────────────
    id("btnAbrirReprogramar").addEventListener("click", function() {
        $("#modalDetalleCita").modal("hide");
        setTimeout(function() { limpiarModalReprogramar(); $("#modalReprogramarCita").modal("show"); }, 400);
    });

    // ── Detalle: Cancelar cita ────────────────────────────────────────────────
    id("btnCancelarCita").addEventListener("click", function() {
        swal({
            icon:"warning", title:"¿Cancelar esta cita?",
            html:"Se cancelará la cita de <strong>" + txt("detPaciente") + id("detPaciente").textContent + "</strong>.<br>Esta acción no se puede deshacer.",
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

    // ── Reprogramar: eventos ──────────────────────────────────────────────────
    id("repFecha").addEventListener("change", function() {
        var d = new Date(this.value + "T00:00:00");
        if (d.getDay() === 0) { this.value = ""; swal({ icon:"warning", title:"Día no disponible", text:"No se pueden programar citas los domingos." }); return; }
        var horaInput = id("repHora"), maxHora = d.getDay() === 6 ? "13:30" : "19:50";
        horaInput.max = maxHora;
        if (horaInput.value > maxHora) horaInput.value = "";
        cargarMedicos("rep");
    });
    id("repIdTipoCita").addEventListener("change", function() { cargarMedicos("rep"); });
    id("repIdMedico").addEventListener("change", function() { actualizarHora("rep"); });
    id("repHora").addEventListener("change", function() {
        validarConflictoHora(this.value, repTiempo, window.repHorasOcupadas || [], this);
    });

    // ── Agregar cita: eventos ─────────────────────────────────────────────────
    id("btnAbrirModalCita").addEventListener("click", function() { limpiarModal(); $("#modalAgregarCita").modal("show"); });
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
    id("nuevaCitaIdTipoCita").addEventListener("change", function() { cargarMedicos("nueva"); });
    id("nuevaCitaIdMedico").addEventListener("change",   function() { actualizarHora("nueva"); });
    id("nuevaCitaFecha").addEventListener("change", function() {
        var d = new Date(this.value + "T00:00:00"), dia = d.getDay();
        if (dia === 0) { this.value = ""; val("nuevaCitaHora",""); swal({ icon:"warning", title:"Día no disponible", text:"No se pueden programar citas los domingos." }); return; }
        var hi = id("nuevaCitaHora"), max = dia === 6 ? "13:30" : "19:50";
        hi.max = max; if (hi.value > max) hi.value = "";
        var hoy = new Date(), hoyStr = hoy.getFullYear()+"-"+String(hoy.getMonth()+1).padStart(2,"0")+"-"+String(hoy.getDate()).padStart(2,"0");
        var ahora = String(hoy.getHours()).padStart(2,"0")+":"+String(hoy.getMinutes()).padStart(2,"0");
        hi.min = (this.value === hoyStr) ? (ahora >= "07:30" ? ahora : "07:30") : "07:30";
        if (hi.value < hi.min) hi.value = "";
        cargarMedicos("nueva");
    });
    id("nuevaCitaHora").addEventListener("change", function() {
        validarConflictoHora(this.value, tiempoCita, window.horasOcupadasMedico || [], this);
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
    function cargarMedicos(pref) {
        var isRep    = pref === "rep";
        var fecha    = val(isRep ? "repFecha"        : "nuevaCitaFecha");
        var selTipo  = id(isRep ? "repIdTipoCita"    : "nuevaCitaIdTipoCita");
        var selMed   = id(isRep ? "repIdMedico"      : "nuevaCitaIdMedico");
        var labelId  = isRep ? "repLabelMedico"      : "labelMedicoInfo";
        var horaId   = isRep ? "repHora"             : "nuevaCitaHora";

        if (!fecha || !selTipo.value) {
            selMed.innerHTML = '<option value="">Seleccione un médico</option>'; selMed.disabled = true;
            txt(labelId, "Seleccione " + (isRep ? "fecha y tipo" : "tipo y fecha") + " primero");
            val(horaId, ""); return;
        }
        if (isRep) repTiempo = parseInt(selTipo.options[selTipo.selectedIndex].getAttribute("data-tiempo")) || 0;
        else       tiempoCita = parseInt(selTipo.options[selTipo.selectedIndex].getAttribute("data-tiempo")) || 0;

        fetchJson("/Marie_stopes_pruebas/index.php?action=getMedicos&fecha="+encodeURIComponent(fecha)+"&id_tipo_cita="+encodeURIComponent(selTipo.value))
            .then(function(medicos) { construirOpcionesMedicos(selMed, medicos, labelId); val(horaId, ""); });
    }

    function actualizarHora(pref) {
        var isRep   = pref === "rep";
        var fecha   = val(isRep ? "repFecha"    : "nuevaCitaFecha");
        var selMed  = id(isRep ? "repIdMedico"  : "nuevaCitaIdMedico");
        var hiId    = isRep ? "repHora"         : "nuevaCitaHora";
        var labelId = isRep ? "repLabelMedico"  : "labelMedicoInfo";
        var storeKey= isRep ? "repHorasOcupadas": "horasOcupadasMedico";
        val(hiId, ""); if (!selMed.value || !fecha) return;
        var opt = selMed.options[selMed.selectedIndex];
        id(hiId).min = opt.getAttribute("data-turno-inicio");
        id(hiId).max = opt.getAttribute("data-turno-fin");
        cargarHorasOcupadas(selMed.value, fecha, labelId, storeKey, isRep ? repIdCita : null);
    }

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
        ["buscarCarnet","nuevaCitaIdPaciente","nuevaCitaFecha","nuevaCitaHora","nuevaCitaIdTipoCita"]
            .forEach(function(s) { val(s,""); });
        id("nuevaCitaIdMedico").innerHTML = '<option value="">Seleccione un médico</option>'; id("nuevaCitaIdMedico").disabled = true;
        txt("labelMedicoInfo","Seleccione tipo y fecha primero");
        ["pacNombre","pacCI","pacFechaNac","pacTelefono","pacDireccion","pacGrupoSanguineo"].forEach(function(s) { txt(s,""); });
        id("infoPaciente").style.display = id("errorPaciente").style.display = id("formNuevoPacienteInline").style.display = "none";
        id("btnGuardarCita").disabled = true;
        limpiarFormPaciente(); window.horasOcupadasMedico = []; tiempoCita = 0;
    }

});
</script>