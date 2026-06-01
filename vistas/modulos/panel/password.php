<div class="row">
    <div class="col-6">
        <h3>¿Seguro que quieres cambiar tu contraseña <?php echo $_SESSION["nombre"] . " " . $_SESSION["apellido"] ?>?</h3>
    </div>
</div>
<div class="row pt-5">
    <div class="col-6">
        <form method="POST">
            <h5>Ingrese su contraseña Actual</h5>
            <div class="form-group">
                <div style="position:relative;">
                    <input type="password" class="form-control input-lg" name="act_password"
                           id="act_password" placeholder="Contraseña actual" required
                           style="padding-right: 40px;">
                    <span class="btn-ojo" data-target="act_password"
                          style="position:absolute; right:10px; top:50%; transform:translateY(-50%); cursor:pointer;">
                        <i class="fa fa-eye-slash" id="ojo_act_password"></i>
                    </span>
                </div>
            </div>
            <h5>Ingrese su nueva contraseña</h5>
            <div class="form-group">
                <div style="position:relative;">
                    <input type="password" class="form-control input-lg" name="new_password1"
                           id="new_password1" placeholder="Nueva contraseña (mín. 8 caracteres, mayúscula, minúscula y número)" required
                           style="padding-right: 40px;">
                    <span class="btn-ojo" data-target="new_password1"
                          style="position:absolute; right:10px; top:50%; transform:translateY(-50%); cursor:pointer;">
                        <i class="fa fa-eye-slash" id="ojo_new_password1"></i>
                    </span>
                </div>
            </div>
            <div class="form-group">
                <div style="position:relative;">
                    <input type="password" class="form-control input-lg" name="new_password2"
                           id="new_password2" placeholder="Repetir nueva contraseña" required
                           style="padding-right: 40px;">
                    <span class="btn-ojo" data-target="new_password2"
                          style="position:absolute; right:10px; top:50%; transform:translateY(-50%); cursor:pointer;">
                        <i class="fa fa-eye-slash" id="ojo_new_password2"></i>
                    </span>
                </div>
            </div>
            <div class="pt-2">
                <button type="submit" class="btn btn-primary">Cambiar contraseña</button>
            </div>

            <?php
                $cambiarC = new ControladorUsuarios();
                $cambiarC->crtCambiarContraseñaUsuario();
            ?>
        </form>
    </div>
</div>

<?php if (isset($_SESSION["cambiar_password"])): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        <?php if ($_SESSION["cambiar_password"] == "ok"): ?>
        Swal.fire({
            icon: "success",
            title: "¡La contraseña ha sido cambiada correctamente!",
            confirmButtonText: "Cerrar"
        }).then(function(result) {
            if (result.isConfirmed) { window.location = "inicio"; }
        });
        <?php elseif ($_SESSION["cambiar_password"] == "no_coinciden"): ?>
        Swal.fire({
            icon: "error",
            title: "¡Las contraseñas nuevas no coinciden!",
            confirmButtonText: "Cerrar"
        });
        <?php elseif ($_SESSION["cambiar_password"] == "password_incorrecta"): ?>
        Swal.fire({
            icon: "error",
            title: "¡La contraseña actual es incorrecta!",
            confirmButtonText: "Cerrar"
        });
        <?php elseif ($_SESSION["cambiar_password"] == "password_debil"): ?>
        Swal.fire({
            icon: "error",
            title: "¡La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula y un número!",
            confirmButtonText: "Cerrar"
        });
        <?php else: ?>
        Swal.fire({
            icon: "error",
            title: "¡Error al cambiar la contraseña!",
            confirmButtonText: "Cerrar"
        });
        <?php endif; ?>
    });
</script>
<?php unset($_SESSION["cambiar_password"]); endif; ?>

<script>
document.querySelectorAll(".btn-ojo").forEach(function(btn) {
    var targetId = btn.getAttribute("data-target");
    var input    = document.getElementById(targetId);
    var icono    = document.getElementById("ojo_" + targetId);

    btn.addEventListener("mousedown", function() {
    input.type = "text";
    icono.classList.remove("fa-eye-slash");
    icono.classList.add("fa-eye");
    });

    btn.addEventListener("mouseup", function() {
        input.type = "password";
        icono.classList.remove("fa-eye");
        icono.classList.add("fa-eye-slash");
    });

    btn.addEventListener("mouseleave", function() {
        input.type = "password";
        icono.classList.remove("fa-eye");
        icono.classList.add("fa-eye-slash");
    });
});
</script>