<header id="topnav">
    <div class="topbar-main">
        <div class="container-fluid">
            <div class="logo">
                <a href="#" class="logo" style="letter-spacing: normal;font-size: 16px;font-weight: 600;">
                    {{Auth::user()->nombres}}
                </a>
            </div>
            <div class="menu-extras topbar-custom">
                <ul class="list-inline float-right mb-0">
                    
                    <li class="list-inline-item dropdown notification-list">
                        <a class="nav-link dropdown-toggle arrow-none waves-effect nav-user" data-toggle="dropdown"
                            href="#" role="button" aria-haspopup="false" aria-expanded="false">
                            <img src="/img/user.png" alt="user" class="rounded-circle">
                            <span class="ml-1">{{ Auth::user()->usuario }} <i class="mdi mdi-chevron-down"></i> </span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="{{ route('users.editcuenta', ['user' => Auth::id()]) }}">
                                <i class="fa fa-cog text-muted"></i> Ajustes
                            </a>
                            <a class="dropdown-item" href="{{ route('logout') }}">
                                <i class="dripicons-exit text-muted"></i> Cerrar sesión
                            </a>
                        </div>
                    </li>
                    <li class="menu-item list-inline-item">
                        <a class="navbar-toggle nav-link">
                            <div class="lines">
                                <span></span>
                                <span></span>
                                <span></span>
                            </div>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>
    <div class="navbar-custom">
        <div class="container-fluid">
            <div id="navigation">
                <ul class="navigation-menu">
                    <li class="has-submenu">
                        <a href="/sucursales/efectivo/nuevo"><i class="fa fa-money"></i> Pagar</a>
                    </li>
                    <li class="has-submenu">
                        <a href="/sucursales/efectivo/movimientos"><i class="fa fa-exchange" aria-hidden="true"></i> Pagos realizados</a>
                    </li>
                          
                </ul>
            </div>
        </div>
    </div>
</header>