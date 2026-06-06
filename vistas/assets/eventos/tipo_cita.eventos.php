<?php if (isset($_SESSION["crear_tipo_cita"])): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        <?php if ($_SESSION["crear_tipo_cita"] == "ok"): ?>
        Swal.fire({ icon: "success", title: "¡Tipo de cita registrado correctamente!", confirmButtonText: "Cerrar" });
        <?php else: ?>
        Swal.fire({ icon: "error", title: "¡Error al registrar el tipo de cita!", confirmButtonText: "Cerrar" });
        <?php endif; ?>
    });
</script>
<?php unset($_SESSION["crear_tipo_cita"]); endif; ?>

<?php if (isset($_SESSION["editar_tipo_cita"])): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        <?php if ($_SESSION["editar_tipo_cita"] == "ok"): ?>
        Swal.fire({ icon: "success", title: "¡Tipo de cita editado correctamente!", confirmButtonText: "Cerrar" });
        <?php else: ?>
        Swal.fire({ icon: "error", title: "¡Error al editar el tipo de cita!", confirmButtonText: "Cerrar" });
        <?php endif; ?>
    });
</script>
<?php unset($_SESSION["editar_tipo_cita"]); endif; ?>

<?php if (isset($_SESSION["eliminar_tipo_cita"])): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        <?php if ($_SESSION["eliminar_tipo_cita"] == "ok"): ?>
        Swal.fire({ icon: "success", title: "¡Tipo de cita eliminado correctamente!", confirmButtonText: "Cerrar" });
        <?php else: ?>
        Swal.fire({ icon: "error", title: "¡Error al eliminar el tipo de cita!", confirmButtonText: "Cerrar" });
        <?php endif; ?>
    });
</script>
<?php unset($_SESSION["eliminar_tipo_cita"]); endif; ?>

<script>
document.addEventListener("click", function(e) {
    if (e.target.classList.contains("btnEditarTipoCita") || e.target.closest(".btnEditarTipoCita")) {
        var btn = e.target.closest(".btnEditarTipoCita") || e.target;
        document.getElementById("editarIdTipoCita").value      = btn.getAttribute("data-id");
        document.getElementById("editarNombreTipoCita").value  = btn.getAttribute("data-nombre");
        document.getElementById("editarTiempoCita").value      = btn.getAttribute("data-tiempo");
    }
});

document.addEventListener("click", function(e) {
    if (e.target.classList.contains("btnEliminarTipoCita") || e.target.closest(".btnEliminarTipoCita")) {
        var btn = e.target.closest(".btnEliminarTipoCita") || e.target;
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
                document.getElementById("formEliminarTipoCita-" + id).submit();
            }
        });
    }
});
</script>