<?php if (isset($_SESSION["crear_permiso"])): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        <?php if ($_SESSION["crear_permiso"] == "ok"): ?>
        Swal.fire({ icon: "success", title: "¡Permiso guardado correctamente!", confirmButtonText: "Cerrar" });
        <?php else: ?>
        Swal.fire({ icon: "error", title: "¡Error al guardar el permiso! Verifica los datos.", confirmButtonText: "Cerrar" });
        <?php endif; ?>
    });
</script>
<?php unset($_SESSION["crear_permiso"]); endif; ?>

<?php if (isset($_SESSION["editar_permiso"])): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        <?php if ($_SESSION["editar_permiso"] == "ok"): ?>
        Swal.fire({ icon: "success", title: "¡Permiso editado correctamente!", confirmButtonText: "Cerrar" });
        <?php else: ?>
        Swal.fire({ icon: "error", title: "¡Error al editar el permiso!", confirmButtonText: "Cerrar" });
        <?php endif; ?>
    });
</script>
<?php unset($_SESSION["editar_permiso"]); endif; ?>

<?php if (isset($_SESSION["eliminar_permiso"])): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        <?php if ($_SESSION["eliminar_permiso"] == "ok"): ?>
        Swal.fire({ icon: "success", title: "¡Permiso eliminado correctamente!", confirmButtonText: "Cerrar" });
        <?php else: ?>
        Swal.fire({ icon: "error", title: "¡Error al eliminar el permiso!", confirmButtonText: "Cerrar" });
        <?php endif; ?>
    });
</script>
<?php unset($_SESSION["eliminar_permiso"]); endif; ?>

<script>
document.addEventListener("click", function(e) {
    if (e.target.classList.contains("btnEditarPermiso")) {
        let id     = e.target.getAttribute("data-id");
        let modulo = e.target.getAttribute("data-modulo");
        let nombre = e.target.getAttribute("data-nombre");

        document.getElementById("editarIdPermiso").value      = id;
        document.getElementById("editarModulo").value         = modulo;
        document.getElementById("editarNombrePermiso").value  = nombre;

        var modal = new bootstrap.Modal(document.getElementById("modalEditarPermiso"));
        modal.show();
    }
});

document.addEventListener("click", function(e) {
    if (e.target.classList.contains("btnEliminarPermiso")) {
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
                document.getElementById("formEliminarPermiso-" + id).submit();
            }
        });
    }
});
</script>