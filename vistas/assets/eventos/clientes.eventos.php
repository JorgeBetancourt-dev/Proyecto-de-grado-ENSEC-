<?php if (isset($_SESSION["crear_cliente"])): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        <?php if ($_SESSION["crear_cliente"] == "ok"): ?>
        Swal.fire({ icon: "success", title: "¡Cliente guardado correctamente!", confirmButtonText: "Cerrar" });
        <?php else: ?>
        Swal.fire({ icon: "error", title: "¡Error al guardar! Verifica los datos.", confirmButtonText: "Cerrar" });
        <?php endif; ?>
    });
</script>
<?php unset($_SESSION["crear_cliente"]); endif; ?>

<?php if (isset($_SESSION["editar_cliente"])): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        <?php if ($_SESSION["editar_cliente"] == "ok"): ?>
        Swal.fire({ icon: "success", title: "¡Cliente editado correctamente!", confirmButtonText: "Cerrar" });
        <?php else: ?>
        Swal.fire({ icon: "error", title: "¡Error al editar!", confirmButtonText: "Cerrar" });
        <?php endif; ?>
    });
</script>
<?php unset($_SESSION["editar_cliente"]); endif; ?>

<?php if (isset($_SESSION["eliminar_cliente"])): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        <?php if ($_SESSION["eliminar_cliente"] == "ok"): ?>
        Swal.fire({ icon: "success", title: "¡Cliente eliminado correctamente!", confirmButtonText: "Cerrar" });
        <?php else: ?>
        Swal.fire({ icon: "error", title: "¡Error al eliminar!", confirmButtonText: "Cerrar" });
        <?php endif; ?>
    });
</script>
<?php unset($_SESSION["eliminar_cliente"]); endif; ?>

<script>
document.addEventListener("click", function(e) {
    if (e.target.classList.contains("btnEditarCliente")) {
        let id        = e.target.getAttribute("data-id");
        let nombre    = e.target.getAttribute("data-nombre");
        let apellidos = e.target.getAttribute("data-apellidos");
        let nit       = e.target.getAttribute("data-nit");

        document.getElementById("editarIdCliente").value       = id;
        document.getElementById("editarNombreCliente").value   = nombre;
        document.getElementById("editarApellidosCliente").value = apellidos;
        document.getElementById("editarNitCliente").value      = nit;
    }
});

document.addEventListener("click", function(e) {
    if (e.target.classList.contains("btnEliminarCliente")) {
        let id = e.target.getAttribute("data-id");
        Swal.fire({
            icon: "warning",
            title: "¿Estás seguro?",
            text: "Esta acción desactivará el cliente.",
            showCancelButton: true,
            confirmButtonText: "Sí, eliminar",
            cancelButtonText: "Cancelar"
        }).then(function(result) {
            if (result.isConfirmed) {
                document.getElementById("formEliminarCliente-" + id).submit();
            }
        });
    }
});
</script>