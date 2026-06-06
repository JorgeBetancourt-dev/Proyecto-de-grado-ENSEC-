<?php if (isset($_SESSION["crear_horario"])): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        <?php if ($_SESSION["crear_horario"] == "ok"): ?>
        Swal.fire({ icon: "success", title: "¡Horario registrado correctamente!", confirmButtonText: "Cerrar" });
        <?php else: ?>
        Swal.fire({ icon: "error", title: "¡Error al registrar el horario!", confirmButtonText: "Cerrar" });
        <?php endif; ?>
    });
</script>
<?php unset($_SESSION["crear_horario"]); endif; ?>

<?php if (isset($_SESSION["editar_horario"])): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        <?php if ($_SESSION["editar_horario"] == "ok"): ?>
        Swal.fire({ icon: "success", title: "¡Horario editado correctamente!", confirmButtonText: "Cerrar" });
        <?php else: ?>
        Swal.fire({ icon: "error", title: "¡Error al editar el horario!", confirmButtonText: "Cerrar" });
        <?php endif; ?>
    });
</script>
<?php unset($_SESSION["editar_horario"]); endif; ?>

<?php if (isset($_SESSION["eliminar_horario"])): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        <?php if ($_SESSION["eliminar_horario"] == "ok"): ?>
        Swal.fire({ icon: "success", title: "¡Horario eliminado correctamente!", confirmButtonText: "Cerrar" });
        <?php else: ?>
        Swal.fire({ icon: "error", title: "¡Error al eliminar el horario!", confirmButtonText: "Cerrar" });
        <?php endif; ?>
    });
</script>
<?php unset($_SESSION["eliminar_horario"]); endif; ?>

<script>
document.addEventListener("click", function(e) {
    if (e.target.classList.contains("btnEditarHorario") || e.target.closest(".btnEditarHorario")) {
        var btn = e.target.closest(".btnEditarHorario") || e.target;
        document.getElementById("editarIdHorario").value      = btn.getAttribute("data-id");
        document.getElementById("editarNombreHorario").value  = btn.getAttribute("data-nombre");
        document.getElementById("editarHoraInicio").value     = btn.getAttribute("data-hora-inicio");
        document.getElementById("editarHoraFin").value        = btn.getAttribute("data-hora-fin");
        document.getElementById("editarHoraISabado").value    = btn.getAttribute("data-horaI-sabado");
        document.getElementById("editarHoraFSabado").value    = btn.getAttribute("data-horaF-sabado");
    }
});

document.addEventListener("click", function(e) {
    if (e.target.classList.contains("btnEliminarHorario") || e.target.closest(".btnEliminarHorario")) {
        var btn = e.target.closest(".btnEliminarHorario") || e.target;
        var id  = btn.getAttribute("data-id");
        Swal.fire({
            icon: "warning",
            title: "¿Estás seguro?",
            text: "Esta acción no se puede deshacer.",
            showCancelButton: true,
            confirmButtonText: "Sí, eliminar",
            cancelButtonText: "Cancelar"
        }).then(function(result) {
            if (result.isConfirmed) {
                document.getElementById("formEliminarHorario-" + id).submit();
            }
        });
    }
});
</script>