<nav class="navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
      <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-center">
        <a class="navbar-brand brand-logo mr-5" href="inicio"><img src="vistas/assets/img/LogoLargo_marieStopes.jpg" alt="logo largo"></a>
        <a class="navbar-brand brand-logo-mini" href="inicio"><img src="vistas/assets/img/LogoMini_marieStopes.jpg" alt="logo mini" ></a>
      </div>
      <div class="navbar-menu-wrapper d-flex align-items-center justify-content-end">
        <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
          <span class="icon-menu"></span>
        </button>
        
        <ul class="navbar-nav navbar-nav-right">
        <!--Notificaciones-->
          <li class="nav-item nav-profile dropdown">
            <a class="nav-link dropdown-toggle" data-toggle="dropdown" >
              <i class="icon-bell mx-0" ></i>
            </a>
            <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="profileDropdown">
              <a class="dropdown-item">
                Notificacion 1
              </a>
              <a class="dropdown-item">
                Notificacion 2
              </a>
            </div>
          </li>
        <!--Opciones-->
          <li class="nav-item nav-profile dropdown">
            <a class="nav-link dropdown-toggle" data-toggle="dropdown" >
              <span class="hidden-xs"><?php echo $_SESSION["nombre"] . " " . $_SESSION["apellido"] ?></span>
            </a>
            <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="profileDropdown">
              <a class="dropdown-item" href="password">
                Cambiar contraseña
              </a>
              <a class="dropdown-item" href="salir">
                <i class="ti-power-off text-primary" ></i>
                Cerrar sesion
              </a>
            </div>
          </li>
        </ul>
        
      </div>
    </nav>