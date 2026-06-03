<?php if (isset($_SESSION["crear_examen"])): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        <?php if ($_SESSION["crear_examen"] == "ok"): ?>
        Swal.fire({ icon: "success", title: "¡Examen guardado correctamente!", confirmButtonText: "Cerrar" });
        <?php else: ?>
        Swal.fire({ icon: "error", title: "¡Error al guardar! Verifica los datos.", confirmButtonText: "Cerrar" });
        <?php endif; ?>
    });
</script>
<?php unset($_SESSION["crear_examen"]); endif; ?>

<?php if (isset($_SESSION["editar_examen"])): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        <?php if ($_SESSION["editar_examen"] == "ok"): ?>
        Swal.fire({ icon: "success", title: "¡Examen editado correctamente!", confirmButtonText: "Cerrar" });
        <?php else: ?>
        Swal.fire({ icon: "error", title: "¡Error al editar!", confirmButtonText: "Cerrar" });
        <?php endif; ?>
    });
</script>
<?php unset($_SESSION["editar_examen"]); endif; ?>

<?php if (isset($_SESSION["eliminar_examen"])): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        <?php if ($_SESSION["eliminar_examen"] == "ok"): ?>
        Swal.fire({ icon: "success", title: "¡Examen eliminado correctamente!", confirmButtonText: "Cerrar" });
        <?php else: ?>
        Swal.fire({ icon: "error", title: "¡Error al eliminar!", confirmButtonText: "Cerrar" });
        <?php endif; ?>
    });
</script>
<?php unset($_SESSION["eliminar_examen"]); endif; ?>

<script>
document.addEventListener("click", function(e) {
    if (e.target.classList.contains("btnEditarExamen")) {
        let id     = e.target.getAttribute("data-id");
        let nombre = e.target.getAttribute("data-nombre");

        document.getElementById("editarIdExamen").value     = id;
        document.getElementById("editarNombreExamen").value = nombre;
    }
});

document.addEventListener("click", function(e) {
    if (e.target.classList.contains("btnEliminarExamen")) {
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
                document.getElementById("formEliminarExamen-" + id).submit();
            }
        });
    }
});
</script>