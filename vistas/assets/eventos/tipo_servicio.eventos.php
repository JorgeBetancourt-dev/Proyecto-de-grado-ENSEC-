<?php if (isset($_SESSION["crear_tipo_servicio"])): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        <?php if ($_SESSION["crear_tipo_servicio"] == "ok"): ?>
        Swal.fire({ icon: "success", title: "¡Tipo de servicio guardado correctamente!", confirmButtonText: "Cerrar" });
        <?php else: ?>
        Swal.fire({ icon: "error", title: "¡Error al guardar! Verifica los datos.", confirmButtonText: "Cerrar" });
        <?php endif; ?>
    });
</script>
<?php unset($_SESSION["crear_tipo_servicio"]); endif; ?>

<?php if (isset($_SESSION["editar_tipo_servicio"])): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        <?php if ($_SESSION["editar_tipo_servicio"] == "ok"): ?>
        Swal.fire({ icon: "success", title: "¡Tipo de servicio editado correctamente!", confirmButtonText: "Cerrar" });
        <?php else: ?>
        Swal.fire({ icon: "error", title: "¡Error al editar!", confirmButtonText: "Cerrar" });
        <?php endif; ?>
    });
</script>
<?php unset($_SESSION["editar_tipo_servicio"]); endif; ?>

<?php if (isset($_SESSION["eliminar_tipo_servicio"])): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        <?php if ($_SESSION["eliminar_tipo_servicio"] == "ok"): ?>
        Swal.fire({ icon: "success", title: "¡Tipo de servicio eliminado correctamente!", confirmButtonText: "Cerrar" });
        <?php else: ?>
        Swal.fire({ icon: "error", title: "¡Error al eliminar!", confirmButtonText: "Cerrar" });
        <?php endif; ?>
    });
</script>
<?php unset($_SESSION["eliminar_tipo_servicio"]); endif; ?>

<script>
document.addEventListener("click", function(e) {
    if (e.target.classList.contains("btnEditarTipoServicio")) {
        let id     = e.target.getAttribute("data-id");
        let nombre = e.target.getAttribute("data-nombre");
        let precio = e.target.getAttribute("data-precio");

        document.getElementById("editarIdTipoServicio").value      = id;
        document.getElementById("editarNombreTipoServicio").value  = nombre;
        document.getElementById("editarPrecioTipoServicio").value  = precio;
    }
});

document.addEventListener("click", function(e) {
    if (e.target.classList.contains("btnEliminarTipoServicio")) {
        let id = e.target.getAttribute("data-id");
        Swal.fire({
            icon: "warning",
            title: "¿Estás seguro?",
            text: "Esta acción desactivará el tipo de servicio.",
            showCancelButton: true,
            confirmButtonText: "Sí, eliminar",
            cancelButtonText: "Cancelar"
        }).then(function(result) {
            if (result.isConfirmed) {
                document.getElementById("formEliminarTipoServicio-" + id).submit();
            }
        });
    }
});
</script>