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
                                    <a href="#">
                                        <i class="fa fa-tasks" aria-hidden="true"></i> Servicios
                                        <i class="mdi mdi-chevron-down mdi-drop"></i>
                                    </a>
                                    <ul class="submenu">
                                        <li>
                                            <a href="{{ route('servicios.encurso') }}">En curso</a>
                                        </li>
                                        <li>
                                            <a href="{{ route('servicios.finalizados') }}">Finalizados</a>
                                        </li>
                                    </ul>
                            </li>

                            <li class="has-submenu">
                                @if (Auth::user()->roles_id == 4)
                                    <a href="{{ route('empresas.listar') }}"><i class="fa fa-building-o" aria-hidden="true"></i> Empresa</a>
                                @else
                                    <a href="{{ route('empresas.listar') }}"><i class="fa fa-building-o" aria-hidden="true"></i> Agencias</a>
                                @endif
                            </li>

                            <li class="has-submenu">
                                <a href="{{ route('valeras.listar') }}"><i class="fa fa-ticket" aria-hidden="true"></i> Valeras</a>
                            </li>

                            @if (Auth::user()->id == 125)
                                <li class="has-submenu">
                                    <a href="/vehiculos/ubicar/avianca"><i class="fa fa-plane" aria-hidden="true"></i> Vehiculos AV</a>
                                </li>
                            @endif
                            @if (Auth::user()->roles_id == 5 && Auth::user()->idtercero == 784)
                                <li class="has-submenu">
                                    <a >
                                    <i class="fa fa-users" aria-hidden="true"></i> Pasajeros
                                    <i class="mdi mdi-chevron-down mdi-drop"></i>
                                    </a>
                                    <ul class="submenu" style="left:0;">
                                        <li>
                                            <a href="{{ url('pasajeros/CRM/listar') }}">PetroSantander</a>
                                        </li>
                                    </ul>
                                </li>
                            @endif
                    </ul>
                </div>
            </div>
        </div>
    </header>