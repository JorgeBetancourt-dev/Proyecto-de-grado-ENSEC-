<?php 
session_destroy();
echo'<script> 
        window.location="login";
    </script>';
?>


<?php
/*
Codigo de Claude que sirve para cerrar la sesión de forma segura,
 eliminando todas las variables de sesión y la cookie de sesión del navegador. 
 Luego redirige al usuario a la página de inicio de sesión.
session_start();
$_SESSION = array();

// Borra la cookie de sesión del navegador
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();
echo '<script>window.location = "login";</script>';
*/
?>