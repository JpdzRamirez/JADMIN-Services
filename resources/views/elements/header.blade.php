<header id="topnav"><meta http-equiv="Content-Type" content="text/html; charset=gb18030">
	<div class="topbar-main">
		<div class="container-fluid">
			<div class="logo d-flex flex-row align-items-center" style="gap:1em">
				<img style="width: 3em" src="{{asset('img/crm.png')}}" alt="CRM-JADMIN" >
				<a href="{{ route('home') }}" class="logo">
					CRM JADMIN
				</a>
			</div>
			<div class="menu-extras topbar-custom">
				<ul class="list-inline float-right mb-0">
						<li class="list-inline-item dropdown notification-list">
								<a class="nav-link dropdown-toggle arrow-none waves-effect nav-user" data-toggle="dropdown"
									href="#" role="button" aria-haspopup="false" aria-expanded="false" id="linksms">
									<img src="/img/sms.png" alt="mensaje" id="imgsms">
									<span class="ml-1"></i>Mensajes</span>
								</a>
								<div class="dropdown-menu dropdown-menu-right" id="menusms">

									<hr>
									<a class="dropdown-item" href="{{ route('mensajes.listar') }}">
										Todos los mensajes
									</a>
									@if (Auth::user()->roles_id == 1)
										<hr style="margin-top: 0">
										<a class="dropdown-item" href="{{ route('mensajes.programados') }}">
											Mensajes Programados
										</a>
									@endif									
								</div>
					</li>
					<li class="list-inline-item dropdown notification-list">
								<a class="nav-link dropdown-toggle arrow-none waves-effect nav-user" data-toggle="dropdown"
									href="#" role="button" aria-haspopup="false" aria-expanded="false" id="linkalerta">
									<img src="/img/bell.png" alt="notificacion" id="imgalerta">
									<span class="ml-1"></i>Alertas</span>
								</a>
								<div class="dropdown-menu dropdown-menu-right" id="menualerta">
									<hr>
									<a class="dropdown-item" href="{{ route('alertas.listar') }}">
										Todas las alertas
									</a>
								</div>
					</li>
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
							@if ($usuario->roles_id == 1)
								<a class="dropdown-item" href="{{ route('users.listar') }}">
									<i class="fa fa-users" aria-hidden="true"></i> Usuarios
								</a>						
							@endif
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
				<ul class="navigation-menu d-flex flex-row">

					@if ($usuario->roles_id == 1 || $usuario->modulos[0]->pivot->ver == 1)
					<li class="has-submenu">
						<a class="nav-item" href="#">
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
							<li>
								<a href="{{ route('servicios.devoluciones') }}">Devoluciones</a>
							</li>
						</ul>
					</li>
					@endif	

					@if ($usuario->roles_id == 1 || $usuario->modulos[0]->pivot->editar == 1)
						<li class="has-submenu">
							<a class="nav-item" href="{{ route('servicios.nuevo') }}"><i class="fa fa-plus-square" aria-hidden="true"></i> Nuevo servicio</a>
						</li>
					@endif

					@if ($usuario->roles_id == 1 || $usuario->modulos[1]->pivot->ver == 1)
					<li class="has-submenu">
						<a class="nav-item" href="{{ route('afiliados.listar') }}"><i class="fa fa-handshake-o" aria-hidden="true"></i> Afiliados</a>
					</li>
					@endif

					@if ($usuario->roles_id == 1 || $usuario->modulos[2]->pivot->ver == 1)
					<li class="has-submenu">
						<a class="nav-item" href="#">
							<i class="fa fa-taxi" aria-hidden="true"></i> Vehiculos
							<i class="mdi mdi-chevron-down mdi-drop"></i>
						</a>
						<ul class="submenu">
							<li>
								<a href="{{ route('vehiculos.ubicar') }}">Conectados</a>
							</li>
							<li>
								<a href="{{ route('vehiculos.listar') }}">Todos</a>
							</li>
						</ul>
					</li>
					@endif

					@if ($usuario->roles_id == 1 || $usuario->modulos[3]->pivot->ver == 1)
					<li class="has-submenu">
						<a class="nav-item" href="{{ route('flotas.listar') }}"><i class="fas fa-sitemap"></i> Flotas</a>
					</li>
					@endif

					@if ($usuario->roles_id == 1 || $usuario->modulos[4]->pivot->ver == 1)
					<li class="has-submenu">
						<a  class="nav-item" href="#">
							<i class="fa fa-building-o" aria-hidden="true"></i> Empresas
							<i class="mdi mdi-chevron-down mdi-drop"></i>
						</a>
						<ul class="submenu">
							<li>
								<a href="{{ route('empresas.listar') }}">Agencias</a>
							</li>
							<li>
								<a href="{{ route('terceros.listar') }}">Terceros</a>
							</li>
						</ul>
					</li>
					@endif

					@if ($usuario->roles_id == 1 || $usuario->modulos[5]->pivot->ver == 1)
					<li class="has-submenu">
						<a class="nav-item" href="#">
							<i class="fa fa-ticket" aria-hidden="true"></i> Valeras
							<i class="mdi mdi-chevron-down mdi-drop"></i>
						</a>
						<ul class="submenu">
							<li>
								<a href="{{ route('valeras.listar') }}">Electrónicas</a>
							</li>
							<li>
								<a href="{{ route('valerasfisicas.listar') }}">Físicas</a>
							</li>
						</ul>
						
					</li>
					@endif
					
					@if ($usuario->roles_id == 1 || $usuario->modulos[6]->pivot->ver == 1 || $usuario->modulos[7]->pivot->ver == 1)
					<li class="has-submenu">
						<a class="nav-item" href="#">
							<i class="fa fa-money" aria-hidden="true"></i> Cuentas
							<i class="mdi mdi-chevron-down mdi-drop"></i>
						</a>
						<ul class="submenu">
							@if ($usuario->roles_id == 1 || $usuario->modulos[6]->pivot->ver == 1)
								<li>
									<a href="{{ route('cuentasc.listar') }}">Afiliados</a>
								</li>
							@endif
							@if ($usuario->roles_id == 1 || $usuario->modulos[7]->pivot->ver == 1)
								<li>
									<a href="{{ route('sucursales.listar') }}">Sucursales</a>
								</li>
							@endif
							@if ($usuario->roles_id == 1 || $usuario->modulos[6]->pivot->ver == 1)
								<li>
									<a href="{{ route('cuentasc.movimientos') }}">Movimientos</a>
								</li>
							@endif
						</ul>
					</li>
					@endif

					@if ($usuario->roles_id == 1 || $usuario->modulos[8]->pivot->ver == 1)
						<li class="has-submenu">
							<a class="nav-item" href="{{ route('carteras.listar') }}"><i class="fa fa-suitcase" aria-hidden="true"></i> Cartera</a>
						</li>
					@endif

					@if ($usuario->roles_id == 1 || $usuario->modulos[9]->pivot->ver == 1)
						<li class="has-submenu">
							<a class="nav-item" >
							<i class="fa fa-users" aria-hidden="true"></i> Pasajeros
							<i class="mdi mdi-chevron-down mdi-drop"></i>
							</a>
							<ul class="submenu">
								<li>
									<a href="/pasajeros/CRM/listar">CMB</a>
								</li>
								<li>
									<a href="/pasajeros/avianca">LATAM Airlines</a>
								</li>
							</ul>
						</li>
					@endif

					@if ($usuario->roles_id == 1 || $usuario->modulos[10]->pivot->ver == 1)
					<li class="has-submenu">
						<a class="nav-item" href="#">
							<i class="fa fa-book" aria-hidden="true"></i> Acuerdos
							<i class="mdi mdi-chevron-down mdi-drop"></i>
						</a>
						<ul class="submenu">
							<li>
								<a href="/acuerdos/listar">Acuerdos de pago</a>
							</li>
							<li>
								<a href="/acuerdos/registrar_pago">Registrar pagos</a>
							</li>
						</ul>
						
					</li>
					@endif

					@if ($usuario->roles_id == 1)
						<li class="has-submenu">
							<a class="nav-item" href="/vehiculos/ubicar/avianca"><i class="fa fa-plane" aria-hidden="true"></i> Vehiculos Aeropuerto</a>
						</li>
					@endif
					
				</ul>
			</div>
		</div>
	</div>
</header>