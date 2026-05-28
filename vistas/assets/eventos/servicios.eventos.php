<?php if (isset($_SESSION["crear_servicio"])): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        <?php if ($_SESSION["crear_servicio"] == "ok"): ?>
        Swal.fire({
            icon: "success",
            title: "¡El servicio ha sido guardado correctamente!",
            confirmButtonText: "Cerrar"
        });
        <?php else: ?>
        Swal.fire({
            icon: "error",
            title: "¡Error al guardar el servicio! Verifica los datos.",
            confirmButtonText: "Cerrar"
        });
        <?php endif; ?>
    });
</script>
<?php unset($_SESSION["crear_servicio"]); endif; ?>
<?php if (isset($_SESSION["eliminar_servicio"])): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        <?php if ($_SESSION["eliminar_servicio"] == "ok"): ?>
        Swal.fire({ 
            icon: "success", 
            title: "¡Servicio eliminado correctamente!", 
            confirmButtonText: "Cerrar" });
        <?php else: ?>
        Swal.fire({ 
            icon: "error", 
            title: "¡Error al eliminar!", 
            confirmButtonText: "Cerrar" });
        <?php endif; ?>
    });
</script>
<?php unset($_SESSION["eliminar_servicio"]); endif; ?>
<?php if (isset($_SESSION["editar_servicio"])): ?>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        <?php if ($_SESSION["editar_servicio"] == "ok"): ?>
        Swal.fire({ 
            icon: "success", 
            title: "¡Servicio editado correctamente!", 
            confirmButtonText: "Cerrar" });
        <?php else: ?>
        Swal.fire({ 
            icon: "error", 
            title: "¡Error al editar!", 
            confirmButtonText: "Cerrar" });
        <?php endif; ?>
    });
</script>
<?php unset($_SESSION["editar_servicio"]); endif; ?>

<script>
// Cargar datos en el modal de editar
document.addEventListener("click", function(e) {
    if (e.target.classList.contains("btnEditarServicio")) {
        let id     = e.target.getAttribute("data-id");
        let nombre = e.target.getAttribute("data-nombre");
        let precio = e.target.getAttribute("data-precio");

        document.getElementById("editarIdServicio").value = id;
        document.getElementById("editarNombre").value     = nombre;
        document.getElementById("editarPrecio").value     = precio;
    }
});

// Confirmar eliminar
document.addEventListener("click", function(e) {
    if (e.target.classList.contains("btnEliminarServicio")) {
        let id = e.target.getAttribute("data-id");
        Swal.fire({
            icon: "warning",
            title: "¿Estás seguro?",
            text: "Esta acción no se puede deshacer.",
            showCancelButton: true,
            confirmButtonText: "Sí, eliminar",
            cancelButtonText: "Cancelar"
        }).then(function(result) {
            if (result.isConfirmed) {
                document.getElementById("formEliminar-" + id).submit();
            }
        });
    }
});
</script>