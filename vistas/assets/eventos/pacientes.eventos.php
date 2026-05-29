<?php if (isset($_SESSION["crear_paciente"])): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        <?php if ($_SESSION["crear_paciente"] == "ok"): ?>
        Swal.fire({ icon: "success", title: "¡Paciente guardado correctamente!", confirmButtonText: "Cerrar" });
        <?php else: ?>
        Swal.fire({ icon: "error", title: "¡Error al guardar el paciente! Verifica los datos.", confirmButtonText: "Cerrar" });
        <?php endif; ?>
    });
</script>
<?php unset($_SESSION["crear_paciente"]); endif; ?>

<?php if (isset($_SESSION["editar_paciente"])): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        <?php if ($_SESSION["editar_paciente"] == "ok"): ?>
        Swal.fire({ icon: "success", title: "¡Paciente editado correctamente!", confirmButtonText: "Cerrar" });
        <?php else: ?>
        Swal.fire({ icon: "error", title: "¡Error al editar el paciente!", confirmButtonText: "Cerrar" });
        <?php endif; ?>
    });
</script>
<?php unset($_SESSION["editar_paciente"]); endif; ?>

<?php if (isset($_SESSION["eliminar_paciente"])): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        <?php if ($_SESSION["eliminar_paciente"] == "ok"): ?>
        Swal.fire({ icon: "success", title: "¡Paciente eliminado correctamente!", confirmButtonText: "Cerrar" });
        <?php else: ?>
        Swal.fire({ icon: "error", title: "¡Error al eliminar el paciente!", confirmButtonText: "Cerrar" });
        <?php endif; ?>
    });
</script>
<?php unset($_SESSION["eliminar_paciente"]); endif; ?>

<script>
document.addEventListener("click", function(e) {
    if (e.target.classList.contains("btnEditarPaciente")) {
        let id              = e.target.getAttribute("data-id");
        let nombre          = e.target.getAttribute("data-nombre");
        let apellidos       = e.target.getAttribute("data-apellidos");
        let ci              = e.target.getAttribute("data-ci");
        let grupoSanguineo  = e.target.getAttribute("data-grupo-sanguineo");
        let telefono        = e.target.getAttribute("data-telefono");
        let fechaNacimiento = e.target.getAttribute("data-fecha-nacimiento");
        let direccion       = e.target.getAttribute("data-direccion");

        document.getElementById("editarIdPaciente").value       = id;
        document.getElementById("editarNombre").value           = nombre;
        document.getElementById("editarApellidos").value        = apellidos;
        document.getElementById("editarCI").value               = ci;
        document.getElementById("editarGrupoSanguineo").value   = grupoSanguineo;
        document.getElementById("editarTelefono").value         = telefono;
        document.getElementById("editarFechaNacimiento").value  = fechaNacimiento;
        document.getElementById("editarDireccion").value        = direccion;
    }
});

document.addEventListener("click", function(e) {
    if (e.target.classList.contains("btnEliminarPaciente")) {
        let id = e.target.getAttribute("data-id");
        Swal.fire({
            icon: "warning",
            title: "¿Estás seguro?",
            text: "Esta acción desactivará al paciente.",
            showCancelButton: true,
            confirmButtonText: "Sí, eliminar",
            cancelButtonText: "Cancelar"
        }).then(function(result) {
            if (result.isConfirmed) {
                document.getElementById("formEliminarPaciente-" + id).submit();
            }
        });
    }
});
</script>