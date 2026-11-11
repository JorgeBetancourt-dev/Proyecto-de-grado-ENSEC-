<?php if (isset($_SESSION["crear_medicamento"])): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        <?php if ($_SESSION["crear_medicamento"] == "ok"): ?>
        Swal.fire({ icon: "success", title: "¡Medicamento guardado correctamente!", confirmButtonText: "Cerrar" });
        <?php else: ?>
        Swal.fire({ icon: "error", title: "¡Error al guardar! Verifica los datos.", confirmButtonText: "Cerrar" });
        <?php endif; ?>
    });
</script>
<?php unset($_SESSION["crear_medicamento"]); endif; ?>

<?php if (isset($_SESSION["editar_medicamento"])): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        <?php if ($_SESSION["editar_medicamento"] == "ok"): ?>
        Swal.fire({ icon: "success", title: "¡Medicamento editado correctamente!", confirmButtonText: "Cerrar" });
        <?php else: ?>
        Swal.fire({ icon: "error", title: "¡Error al editar!", confirmButtonText: "Cerrar" });
        <?php endif; ?>
    });
</script>
<?php unset($_SESSION["editar_medicamento"]); endif; ?>

<?php if (isset($_SESSION["eliminar_medicamento"])): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        <?php if ($_SESSION["eliminar_medicamento"] == "ok"): ?>
        Swal.fire({ icon: "success", title: "¡Medicamento eliminado correctamente!", confirmButtonText: "Cerrar" });
        <?php else: ?>
        Swal.fire({ icon: "error", title: "¡Error al eliminar!", confirmButtonText: "Cerrar" });
        <?php endif; ?>
    });
</script>
<?php unset($_SESSION["eliminar_medicamento"]); endif; ?>

<script>
document.addEventListener("click", function(e) {
    if (e.target.classList.contains("btnEditarMedicamento")) {
        let id          = e.target.getAttribute("data-id");
        let nombre      = e.target.getAttribute("data-nombre");
        let descripcion = e.target.getAttribute("data-descripcion");

        document.getElementById("editarIdMedicamento").value          = id;
        document.getElementById("editarNombreMedicamento").value      = nombre;
        document.getElementById("editarDescripcionMedicamento").value = descripcion;
    }
});

document.addEventListener("click", function(e) {
    if (e.target.classList.contains("btnEliminarMedicamento")) {
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
                document.getElementById("formEliminarMedicamento-" + id).submit();
            }
        });
    }
});
</script>