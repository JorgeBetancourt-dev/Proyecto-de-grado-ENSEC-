<?php if (isset($_SESSION["crear_usuario"])): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        <?php if ($_SESSION["crear_usuario"] == "ok"): ?>
        Swal.fire({ icon: "success", title: "¡El usuario ha sido guardado correctamente!", confirmButtonText: "Cerrar" });
        <?php else: ?>
        Swal.fire({ icon: "error", title: "¡Error al guardar el usuario! Verifica los datos.", confirmButtonText: "Cerrar" });
        <?php endif; ?>
    });
</script>
<?php unset($_SESSION["crear_usuario"]); endif; ?>

<?php if (isset($_SESSION["Validar_contraseña"])): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        <?php if ($_SESSION["Validar_contraseña"] == "error"): ?>
        Swal.fire({ icon: "error", title: "¡Error al guardar el usuario! La contraseña tiene que tener al menos 8 caracteres, incluyendo una letra mayúscula, una letra minúscula y un número.", confirmButtonText: "Cerrar" });
        <?php endif; ?>
    });
</script>
<?php unset($_SESSION["Validar_contraseña"]); endif; ?>

<?php if (isset($_SESSION["eliminar_usuario"])): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        <?php if ($_SESSION["eliminar_usuario"] == "ok"): ?>
        Swal.fire({ icon: "success", title: "¡Usuario eliminado correctamente!", confirmButtonText: "Cerrar" });
        <?php else: ?>
        Swal.fire({ icon: "error", title: "¡Error al eliminar!", confirmButtonText: "Cerrar" });
        <?php endif; ?>
    });
</script>
<?php unset($_SESSION["eliminar_usuario"]); endif; ?>

<?php if (isset($_SESSION["editar_usuario"])): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        <?php if ($_SESSION["editar_usuario"] == "ok"): ?>
        Swal.fire({ icon: "success", title: "¡Usuario editado correctamente!", confirmButtonText: "Cerrar" });
        <?php else: ?>
        Swal.fire({ icon: "error", title: "¡Error al editar!", confirmButtonText: "Cerrar" });
        <?php endif; ?>
    });
</script>
<?php unset($_SESSION["editar_usuario"]); endif; ?>

<script>
document.addEventListener("click", function(e) {
    if (e.target.classList.contains("btnEditarUsuario")) {
        let id         = e.target.getAttribute("data-id");
        let nombre     = e.target.getAttribute("data-nombre");
        let apellido   = e.target.getAttribute("data-apellido");
        let usuario    = e.target.getAttribute("data-usuario");
        let idRol      = e.target.getAttribute("data-id-rol");
        let idHorario  = e.target.getAttribute("data-id-horario");

        document.getElementById("editarIdUsuario").value  = id;
        document.getElementById("editarNombre").value     = nombre;
        document.getElementById("editarApellido").value   = apellido;
        document.getElementById("editarUsuario").value    = usuario;
        document.getElementById("editarIdRol").value      = idRol;
        document.getElementById("editarIdHorario").value  = idHorario;
    }
});

document.addEventListener("click", function(e) {
    if (e.target.classList.contains("btnEliminarUsuario")) {
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