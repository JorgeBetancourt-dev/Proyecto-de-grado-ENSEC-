<?php if (isset($_SESSION["crear_rol"])): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        <?php if ($_SESSION["crear_rol"] == "ok"): ?>
        Swal.fire({ icon: "success", title: "¡Rol creado correctamente!", confirmButtonText: "Cerrar" });
        <?php else: ?>
        Swal.fire({ icon: "error", title: "¡Error al crear el rol!", confirmButtonText: "Cerrar" });
        <?php endif; ?>
    });
</script>
<?php unset($_SESSION["crear_rol"]); endif; ?>

<?php if (isset($_SESSION["editar_rol"])): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        <?php if ($_SESSION["editar_rol"] == "ok"): ?>
        Swal.fire({ icon: "success", title: "¡Rol editado correctamente!", confirmButtonText: "Cerrar" });
        <?php else: ?>
        Swal.fire({ icon: "error", title: "¡Error al editar el rol!", confirmButtonText: "Cerrar" });
        <?php endif; ?>
    });
</script>
<?php unset($_SESSION["editar_rol"]); endif; ?>

<?php if (isset($_SESSION["eliminar_rol"])): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        <?php if ($_SESSION["eliminar_rol"] == "ok"): ?>
        Swal.fire({ icon: "success", title: "¡Rol eliminado correctamente!", confirmButtonText: "Cerrar" });
        <?php else: ?>
        Swal.fire({ icon: "error", title: "¡Error al eliminar el rol!", confirmButtonText: "Cerrar" });
        <?php endif; ?>
    });
</script>
<?php unset($_SESSION["eliminar_rol"]); endif; ?>

<script>
document.addEventListener("click", function(e) {
    if (e.target.classList.contains("btnEditarRol")) {
        let id     = e.target.getAttribute("data-id");
        let nombre = e.target.getAttribute("data-nombre");

        document.getElementById("editarIdRol").value     = id;
        document.getElementById("editarNombreRol").value = nombre;

        document.querySelectorAll(".checkbox-editar-permiso").forEach(function(cb) {
            cb.checked = false;
        });

        fetch("/Marie_stopes_pruebas/index.php?action=getPermisosPorRol&id_rol=" + id)
            .then(function(res) { return res.json(); })
            .then(function(permisos) {
                permisos.forEach(function(p) {
                    var cb = document.getElementById("editarPermiso_" + p.id_permiso);
                    if (cb) cb.checked = true;
                });
                $("#modalEditarRol").modal("show");
            });
    }
});

document.addEventListener("click", function(e) {
    if (e.target.classList.contains("btnEliminarRol")) {
        let id = e.target.getAttribute("data-id");
        Swal.fire({
            icon: "warning",
            title: "¿Estás seguro?",
            text: "Esta acción desactivará el rol.",
            showCancelButton: true,
            confirmButtonText: "Sí, eliminar",
            cancelButtonText: "Cancelar"
        }).then(function(result) {
            if (result.isConfirmed) {
                document.getElementById("formEliminarRol-" + id).submit();
            }
        });
    }
});
</script>