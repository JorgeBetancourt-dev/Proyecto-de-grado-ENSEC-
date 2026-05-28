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
                <div class="input-group">              
                    <span class="input-group-addon"><i class="fa fa-user"></i></span>
                    <input type="password" class="form-control input-lg" name="act_password" placeholder="Contraseña actual" required>
                </div>
            </div>
            <h5>Ingrese su nueva contraseña</h5>
            <div class="form-group">              
                <div class="input-group">              
                    <span class="input-group-addon"><i class="fa fa-lock"></i></span>
                    <input type="password" class="form-control input-lg" name="new_password1" placeholder="Nueva contraseña (mín. 8 caracteres)" required>
                </div>
            </div>
            <div class="form-group">              
                <div class="input-group">              
                    <span class="input-group-addon"><i class="fa fa-lock"></i></span>
                    <input type="password" class="form-control input-lg" name="new_password2" placeholder="Repetir nueva contraseña" required>
                </div>
            </div>
            <div class="pt-2">
                <button type="submit" class="btn btn-primary">Cambiar contraseña</button>
            </div>

            <?php 
                //$cambiarC = new ControladorUsuarios();
                // $cambiarC->crtCambiarContraseñaUsuario();
            ?>
        </form>
    </div>
</div>

<!-- ✅ Notificaciones SweetAlert2 consistentes con el resto del sistema -->
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