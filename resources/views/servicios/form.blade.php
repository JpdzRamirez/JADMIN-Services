@extends('layouts.logeado')
@section('style')
    <link rel="stylesheet" href="https://openlayers.org/en/v6.9.0/css/ol.css" type="text/css">
@endsection
@section('sub_title', 'Nuevo servicio')

@section('sub_content')
	<div class="card">
		<div class="card-body" id="contservicio">
			<div class="text-right">
				<a href="/servicios/majorel" class="btn btn-sm btn-dark">Serv. Majorel - TransAmerica</a>
			</div>
			@if($errors->first('sql') != null)
				<div class="alert alert-danger" style="margin:5px 0">
					<h6>{{$errors->first('sql')}}</h6>
				</div>				
			@endif
			{{ Form::model($servicio, ['route' => $route, 'method' => $method, 'id' => 'formservicio'] ) }}
			{{ Form::hidden('id', null) }}
			<input type="hidden" name="latitud" id="latitud">
			<input type="hidden" name="longitud" id="longitud">
			<input type="hidden" value="" name="idvale" id="idvale">
			<input type="hidden" name="servicioid" id="servicioid">
			<input type="hidden" name="autorizacion" id="autorizacion">
			<input type="hidden" name="avianca" id="avianca" value="0">
				<h4>Datos del cliente</h4>
				<hr>
				<div class="form-group row">
					{{ Form::label('nombres', 'Nombre', ['class' => 'label-required col-md-2']) }}
					<div class="col-md-10">
						@if ($cliente != null)
							{{ Form::text('nombres', $cliente->nombres, ['required', 'class' => 'form-control', 'style' => 'width:60%']) }}
						@else
							{{ Form::text('nombres', null, ['required', 'class' => 'form-control', 'style' => 'width:60%']) }}
						@endif						
					</div>
				</div>
				<div class="form-group row">
					{{ Form::label('telefono', 'Telefono', ['class' => 'label-required col-md-2']) }}
					<div class="col-md-4">
						@if ($cliente != null)
							{{ Form::text('telefono', $cliente->telefono, ['required', 'class' => 'form-control']) }}
						@else
							{{ Form::text('telefono', null, ['required', 'class' => 'form-control']) }}
						@endif			
					</div>

					{{ Form::label('email', 'Correo electrónico', ['class' => 'label col-md-2']) }}
					<div class="col-md-4">
						@if ($cliente != null)
							{{ Form::text('email', $cliente->email, ['class' => 'form-control']) }}
						@else
							{{ Form::text('email', null, ['class' => 'form-control']) }}
						@endif					
					</div>
				</div>
				<h4>Datos del servicio</h4>
				<hr>
				<div class="form-group row">
					{{ Form::label('asignacion', 'Modo de asignacion', ['class' => 'label-required col-md-2']) }}
					<div class="col-md-2">
						<select name="asignacion" id="asignacion" class="form-control" >
							<option value="Normal" selected>Normal</option>
							<option value="Directo">Directo</option>
						</select>
					</div>
					{{ Form::label('hora', 'Hora del servicio', ['class' => 'label-required col-md-2', 'style' => 'text-align:center']) }}
					<div class="col-md-2">
						<select name="hora" id="hora" class="form-control">
							<option value="inmediato" selected>Inmediato</option>
							<option value="programado">Programado</option>
						</select>
					</div>
					{{ Form::label('pago', 'Método de pago', ['class' => 'label-required col-md-2', 'style' => 'text-align:center']) }}
					<div class="col-md-2">
						<select name="pago" id="pago" class="form-control" required >
							<option value="Efectivo">Efectivo</option>
							<option value="Vale electrónico">Vale electrónico</option>
							<option value="Vale físico">Vale físico</option>
						</select>
					</div>
				</div>
				<div id="divdirecto" style="display: none">					
					<div class="form-group row">
						{{ Form::label('placa', 'Placa', ['class' => 'label-required col-md-2']) }}
						<div class="col-md-10">
							<input type="text" minlength="7" maxlength="7" name="placa" id="placa" class="form-control" style="width: 60%" disabled required>
						</div>
					</div>
				</div>
				<div id="divprogramado" style="display: none">
					<div class="form-group row">
							{{ Form::label('horaprogramada', 'Fecha y hora', ['class' => 'label-required col-md-2']) }}
							<div class="col-md-10">
								@php
									date_default_timezone_set('America/Bogota');
								@endphp
								<input type="datetime-local" name="fechaprogramada" id="fechaprogramada" class="form-control" min="{{date('Y-m-d')}}T00:00:00" style="width:30%" disabled required>
							</div>
						</div>
				</div>
				<div id="divfisico" style="display: none">
					<div class="form-group row {{ $errors->has('empresafisico') ? 'form-error': '' }}">
						{{ Form::label('empresafisico', 'Agencia', ['class' => 'label-required col-md-2']) }}
						<div class="col-md-6">
							<select name="empresafisico" id="empresafisico" class="form-control" disabled>
								@foreach ($fisicas as $fisica)
									<option value="{{$fisica->NRO_IDENTIFICACION}}">{{$fisica->NOMBRE}}</option>
								@endforeach
							</select>
						</div>
					</div>
				</div>
				<div id="divesga" class="form-group row" style="display: none">
					{{ Form::label('subestacion', 'Subestación', ['class' => 'col-md-2']) }}
					<div class="col-md-10">
						<select name="subestacion" id="subestacion" class="form-control" style="width: 50%">
							<option value="CRA 19 # 24-56_Bucaramanga_por el lado de parqueadero">Electrificadora de Santander principal</option>
							<option value="Avenida Bellavista Sector A Bloque 12_Floridablanca_barrio de altos de bellavista, al frente de un botadero de basura">Subestación Bellavista de la ESSA </option>
							<option value="Calle 45A # 12 – 28_Floridablanca_barrio El Carmen, por la calle 45 entre carrera 12 y 13">Subestación las Villas ESSA</option>
							<option value="Subestación eléctrica Bucaramanga_Floridablanca_por detras de carabineros">Subestación Bucaramanga ESSA</option>
							<option value="Calle 24A #27–03_Girón_via zapatoca al lado del barrio del nuevo girón, por el lado del colegio de la casona infantil">Subestación los Cocos ESSA</option>
							<option value="Avenida Bucarica peatonal 20 – 40_Floridablanca_al lado de bomberos de floridablanca">Subestación Bucarica</option>
							<option value="Calle 64E # 1W – 40_Bucaramanga_Ciudad Bolívar">Subestacion Real de Minas</option>
							<option value="Subestación conucos_Bucaramanga_Conucos, sentido sur norte después del viaducto la flora">Subestación Conucos ESSA</option>
							<option value="Avenida los Caneyes # 17 – 02_Girón_al lado de la quebrada agua blanca, por la avenida los caneyes">Subestación Caneyes ESSA</option>
							<option value="Transversal el Bosque # 152b_Floridablanca_por detrás de ardila lulle al lado del parqueadero publico">Subestación el Bosque ESSA</option>
							<option value="essa sub el cero_Rionegro_km 15 via a la costa">Subestación el cero de Bocas ESSA</option>
							<option value="Bulevar Santander # 19 – 59_Bucaramanga_Barrio San Francisco">Subestación Norte ESSA</option>
							<option value="Cra. 10 #94_Bucaramanga_barrio Betania etapa 9, Carrera 10 manzana B60">Subestación Las Hamacas ESSA</option>
							<option value="Subestacion palos_Bucaramanga_frente al motel nuevo Egipto">Subestación Palos ESSA</option>
							<option value="Subestación eléctrica palenque ESSA_Girón_antes de llegar a la 45, al frente de los tanques de Terpel">Subestación El Palenque ESSA</option>
							<option value="Carrera 22 # 5 – 54_Bucaramanga_Barrio Comuneros">Subestación Principal ESSA</option>
							<option value="Calle 46 # 19 – 122_Bucaramanga_Barrio La Concordia, detrás de Homecenter">Subestación Sur ESSA</option>
							<option value="Carrera 17 # 9 – 17_Piedecuesta_subiendo por el restaurante los troncos">Subestación Cabecera del Llano ESSA</option>
							<option value="Subestacion Guatiguara, Piedecuesta_Piedecuesta_Más abajo de peses de Guatiguará, en una Y">Subestación Piedecuesta</option>
							<option value="Subestacion La Granja Piedecuesta_Piedecuesta_al lado del Sena al frente de ASOPENDER">Subestación La granja</option>
							<option value="Subestacion Essa Floridablanca_Floridablanca_dirigiendose para la Hormiga KM 3, vía Acapulco">Subestacion la Florida</option>
							<option value="San Pablo_Rionegro_KM 20 vía a la costa, frente a la Virgen">Subestación Rionegro</option>
						</select>
					</div>				
				</div>
					<div id="divvale" style="display: none">
						<div class="form-group row" id="divagevalera">
								{{ Form::label('empresa', 'Empresa', ['class' => 'label-required col-md-2']) }}
								<div class="col-md-4">
									<select name="empresa" id="empresa" class="form-control" required disabled>
										<option value="0">Seleccionar empresa</option>
										@if ($cliente != null)
											@foreach ($empresas as $empresa)
												@if ($cliente->tercero == $empresa->TERCERO)
													<option value="{{$empresa->TERCERO}}" selected>{{$empresa->RAZON_SOCIAL}}</option>
												@else
													<option value="{{$empresa->TERCERO}}">{{$empresa->RAZON_SOCIAL}}</option>
												@endif									
											@endforeach
										@else
											@foreach ($empresas as $empresa)
												<option value="{{$empresa->TERCERO}}">{{$empresa->RAZON_SOCIAL}}</option>
											@endforeach
										@endif		
									</select>
								</div>
								{{ Form::label('valera', 'Valera', ['class' => 'label-required col-md-1', 'style' => 'text-align:center']) }}
								<div class="col-md-5">
									<select name="valera" id="valera" class="form-control" required disabled>
										@foreach ($valeras as $valera)
											<option value="{{$valera->id}}">{{$valera->nombre}}</option>
										@endforeach
									</select>
								</div>
						</div>
						<div class="form-group row">
							{{ Form::label('codigo', 'Código del vale', ['class' => 'label-required col-md-2']) }}
							<div class="col-md-2">
								<input type="number" name="codigo" id="codigo" class="form-control" disabled required>
							</div>
						</div>						
						<div id="divasignado">							
							<div class="form-group row">
								{{ Form::label('cobro', 'Método de cobro', ['class' => 'label-required col-md-2']) }}
								<div class="col-md-4">
									<select name="cobro" id="cobro" class="form-control" disabled required>
										<option value="" disabled selected>Seleccionar cobro</option>
										<option value="Unidades">Unidades</option>
										<option value="Minutos">Minutos</option>
										<option value="Ruta">Ruta</option>
									</select>
								</div>
							</div>
							<div class="form-group row">
									{{ Form::label('beneficiariovale', 'Beneficiario del vale', ['class' => 'label col-md-2']) }}
									<div class="col-md-10">
										<input type="text" name="beneficiariovale" id="beneficiariovale" class="form-control" style="width: 50%" readonly>
									</div>
							</div>
							<div class="form-group row">
								{{ Form::label('centrocosto', 'Centro de Costo', ['class' => 'label col-md-2']) }}
								<div class="col-md-10">
									<input type="text" name="centrocosto" id="centrocosto" class="form-control" style="width: 50%" readonly>
								</div>
							</div>
							<div class="form-group row">
								{{ Form::label('referenciadovale', 'Actividad a realizar', ['class' => 'label col-md-2']) }}
								<div class="col-md-10">
									<input type="text" name="referenciadovale" id="referenciadovale" class="form-control" style="width: 50%" readonly>
								</div>
							</div>
							<div class="form-group row">
								{{ Form::label('destino', 'Dirección destino', ['class' => 'label col-md-2']) }}
								<div class="col-md-10">
									<input type="text" name="destino" id="destino" class="form-control" style="width: 50%" readonly>
								</div>
							</div>
						</div>
						<div id="divavianca" style="display: none">
							<div class="form-group row">
								{{ Form::label('fechavianca', 'Fecha programada', ['class' => 'col-md-2']) }}
								<div class="col-md-6 col-xs-10">
									<input type="datetime-local" name="fechavianca" id="fechavianca" class="form-control">
								</div>
							</div>
							<div id="divreportado">
								<div class="form-group row">
									{{ Form::label('fechareportado', 'Fecha de reportado', ['class' => 'col-md-2']) }}
									<div class="col-md-6 col-xs-10">
										<input type="datetime-local" name="fechareportado" id="fechareportado" class="form-control">
									</div>
								</div>
							</div>
							<div class="form-group row">
								{{ Form::label('tipovale', 'Tipo de vale', ['class' => 'label-required col-md-2']) }}
								<div class="col-md-6 col-xs-10">
									<select name="tipovale" id="tipovale" class="form-control">
										<option value="Tierra">Tierra</option>
										<option value="Tripulación">Tripulación</option>
										<option value="Equipaje">Equipaje</option>
										<option value="Viajero">Viajero</option>
									</select>
								</div>
							</div>
							<div class="form-group row">
								{{ Form::label('tiposer', 'Tipo de servicio', ['class' => 'label-required col-md-2']) }}
								<div class="col-md-6 col-xs-10">
									<select name="tiposer" id="tiposer" class="form-control">
										<option value="Recogida">Recogida</option>
										<option value="Reparto">Reparto</option>
									</select>
								</div>
							</div>
							<div class="form-group row">
								{{ Form::label('rutas', 'Rutas', ['class' => 'label-required col-md-2']) }}
								<div class="col-md-6 col-xs-10">
									<select name="rutas" id="rutas" class="form-control">
									</select>
								</div>
							</div>
							<div id="divusuariosav">
								<div class="form-group row">
									{{ Form::label('usuav1', 'Usuario 1', ['class' => 'label-required col-md-2']) }}
									<div class="col-md-5 col-xs-10">
										<input type="text" name="usuav1" id="usuav1" class="form-control usuav">
									</div>
								</div>
								<div class="form-group row">
									{{ Form::label('usuav2', 'Usuario 2', ['class' => 'col-md-2']) }}
									<div class="col-md-5 col-xs-10">
										<input type="text" name="usuav2" id="usuav2" class="form-control usuav">
									</div>
									<div class="col-md-3">
										<input type="datetime-local" name="horausuav2" min="{{date('Y-m-d')}}T00:00:00" id="horausuav2" class="form-control">
									</div>
								</div>
								<div class="form-group row">
									{{ Form::label('usuav3', 'Usuario 3', ['class' => 'col-md-2']) }}
									<div class="col-md-5 col-xs-10">
										<input type="text" name="usuav3" id="usuav3" class="form-control usuav">
									</div>
									<div class="col-md-3">
										<input type="datetime-local" name="horausuav3" min="{{date('Y-m-d')}}T00:00:00" id="horausuav3" class="form-control">
									</div>
								</div>
							</div>
							<div class="form-group row" id="divcentrocostoav" style="display: none">
								{{ Form::label('centrocostoav', 'Centro de Costo', ['class' => 'label-required col-md-2']) }}
								<div class="col-md-10">
									<input type="text" name="centrocostoav" id="centrocostoav" class="form-control" style="width: 50%">
								</div>
							</div>
							<div class="form-group row" id="divuelo" style="display: none">
								{{ Form::label('vuelo', 'Vuelo', ['class' => 'col-md-2']) }}
								<div class="col-md-10">
									<input type="text" name="vuelo" id="vuelo" class="form-control" style="width: 50%">
								</div>
							</div>
							<div class="form-group row" id="divoucher" style="display: none">
								{{ Form::label('voucher', 'Voucher', ['class' => 'col-md-2']) }}
								<div class="col-md-10">
									<input type="text" name="voucher" id="voucher" class="form-control" style="width: 50%">
								</div>
							</div>			
						</div>	

						<div id="divpetro" style="display: none">
							<div class="form-group row">
								{{ Form::label('rutaspetro', 'Rutas', ['class' => 'label-required col-md-2']) }}
								<div class="col-md-6 col-xs-10">
									<select name="rutaspetro" id="rutaspetro" class="form-control">
									</select>
								</div>
							</div>
							<div id="divusuariospetro">
								<div class="form-group row">
									{{ Form::label('usuariopetro1', 'Usuario 1', ['class' => 'label-required col-md-2']) }}
									<div class="col-md-6 col-xs-10 d-flex flex-row" style="gap:1em;">
										<div class="input-group mb-3" data-group-id="1">
												<input type="text" name="usuariopetro1" id="usuariopetro1"
												class="form-control usuariopetro">
												<input value="" data-toggle="tooltip" data-placement="top" 
												 title="Centro de Costo"
												 type="text" class="form-control centro-costo" 
												 placeholder="" aria-label="" 
												 aria-describedby="basic-addon2" readonly>
												<button type="button" 
												class="input-group-text btn btn-dark"
												data-toggle="modal"
												data-target="#modalCCosto"
												data-form-action=""
												data-modal-title="">Actualizar CC</button>
												<input type="hidden" name="idusuariopetro1">
										</div>
									</div>
								</div>
								<div class="form-group row">
									{{ Form::label('usuariopetro2', 'Usuario 2', ['class' => 'col-md-2']) }}
									<div class="col-md-6 col-xs-10 d-flex flex-row" style="gap:1em;">
										<div class="input-group mb-3" data-group-id="2">
												<input type="text" name="usuariopetro2" id="usuariopetro2"
												class="form-control usuariopetro">
												<input value="" data-toggle="tooltip" data-placement="top" 
												 title="Centro de Costo"
												 type="text" class="form-control centro-costo" 
												 placeholder="" aria-label="" 
												 aria-describedby="basic-addon2" readonly>
												<button type="button" 
												class="input-group-text btn btn-dark"
												data-toggle="modal"
												data-target="#modalCCosto" 
												data-form-action=""
												data-modal-title="Centro de Costo Pasajero:">Actualizar CC</button>
												<input type="hidden" name="idusuariopetro2">
										</div>
									</div>
								</div>
								<div class="form-group row">
									{{ Form::label('usuariopetro3', 'Usuario 3', ['class' => 'col-md-2']) }}
									<div class="col-md-6 col-xs-10 d-flex flex-row" style="gap:1em;">
										<div class="input-group mb-3" data-group-id="3">
												<input type="text" name="usuariopetro3" id="usuariopetro3"
												class="form-control usuariopetro">
												<input value="" data-toggle="tooltip" data-placement="top" 
												title="Centro de Costo"
												type="text" class="form-control centro-costo" 
												placeholder="" aria-label="" 
												aria-describedby="basic-addon2" readonly>
												<button type="button" 
												class="input-group-text btn btn-dark" 
												data-toggle="modal"
												data-target="#modalCCosto"
												data-form-action=""
												data-modal-title="Centro de Costo Pasajero:">Actualizar CC</button>
												<input type="hidden" name="idusuariopetro3">
										</div>
									</div>
								</div>
								<div class="form-group row">
									{{ Form::label('usuariopetro4', 'Usuario 4', ['class' => 'col-md-2']) }}
									<div class="col-md-6 col-xs-10 d-flex flex-row" style="gap:1em;">
										<div class="input-group mb-3" data-group-id="4">
												<input type="text" name="usuariopetro4" id="usuariopetro4"
												class="form-control usuariopetro">
												<input value="" data-toggle="tooltip" data-placement="top" 
												 title="Centro de Costo"
												 type="text" class="form-control centro-costo"
												 placeholder="" aria-label="" 
												 aria-describedby="basic-addon2" readonly>
												<button type="button"
												class="input-group-text btn btn-dark" 
												data-toggle="modal"
												data-target="#modalCCosto"
												data-form-action=""
												data-modal-title="Centro de Costo Pasajero:">Actualizar CC</button>
												<input type="hidden" name="idusuariopetro4">
										</div>
									</div>
								</div>
							</div>
						</div>

					</div>

					<div class="form-group row">
						{{ Form::label('usuarios', 'Usuarios del servicio', ['class' => 'label-required col-md-2']) }}
						<div class="col-md-5">
							<textarea name="usuarios" class="form-control" id="usuarios" cols="30" rows="3" required>
							</textarea> 
						</div>
						{{ Form::label('contacto', 'Teléfono contacto', ['class' => 'col-md-2']) }}
						<div class="col-md-3">
							<input type="number" step="1" class="form-control" name="contacto" id="contacto">
						</div>
					</div>

					<div class="form-group row mt-4 align-items-center">
						<div class="col-11">
							<input form="formbuscar" type="text" name="sitio" class="form-control" placeholder="Direccion Completa" id="sitio"> 
						</div>
						<div class="col-1">
							<button type="button" class="btn btn-sm btn-dark" onclick="buscarSitio();"><i class="fa fa-search"></i> Buscar</button>
						</div>
					</div>

					<div class="row mb-4">
						<div class="col-12">
							<div id="mapaOpen" style="height: 500px;width: 100%;">
							</div>
						</div>
					</div>

					<div class="form-group row {{ $errors->has('direccion') ? 'form-error': '' }}">
						{{ Form::label('direccion', 'Dirección de origen', ['class' => 'col-md-2 label-required']) }}
						<div class="col-md-5" style="display: inherit">
							@if ($cliente != null)
								@php
									$direcciones = explode(",", $cliente->direccion);						
								@endphp
								
								@if (count($direcciones) > 0)
									{{ Form::text('direccion', $direcciones[0], ['readonly', 'class' => 'form-control']) }}
								@else
									{{ Form::text('direccion', null, ['readonly', 'class' => 'form-control']) }}
								@endif
							@else
								{{ Form::text('direccion', null, ['readonly', 'class' => 'form-control']) }}
							@endif	
						</div>
					</div>

					<div class="form-group row {{ $errors->has('municipio') ? 'form-error': '' }}">
						{{ Form::label('municipio', 'Municipio de origen', ['class' => 'col-md-2 label-required']) }}
						<div class="col-md-10">
							@if ($cliente != null && count($direcciones) > 1)
								<input type="text" value="{{$direcciones[1]}}" name="municipio" id="municipio" class="form-control" style="width: 30%" readonly>
							@else
								<input type="text" name="municipio" id="municipio" class="form-control" style="width: 30%" readonly>
							@endif
						</div>
					</div>

					<div class="form-group row">
						{{ Form::label('flota', 'Flota', ['class' => 'col-md-2']) }}
						<div class="col-md-10">
							<select name="flota" id="flota" class="form-control" style="width: 30%">
								<option value="0" selected>Sin flota</option>
								@foreach ($flotas as $flota)
									<option value="{{$flota->id}}">{{$flota->descripcion}}</option>
								@endforeach
							</select>
						</div>
					</div>

					<div class="form-group row">
						{{ Form::label('complemento', 'Complemento', ['class' => 'col-md-2']) }}
						<div class="col-md-10">
							{{ Form::text('complemento', null, ['class' => 'form-control', 'style' => 'width:30%']) }}
						</div>
					</div>

					<div class="form-group row">
						{{ Form::label('contacto', 'Teléfono contacto', ['class' => 'col-md-2']) }}
						<div class="col-md-3">
							<input type="number" step="1" class="form-control" name="Otrocontacto" id="Otrocontacto">
						</div>
					</div>

					<div class="form-group row">
						{{ Form::label('observaciones', 'Observaciones', ['class' => 'col-md-2']) }}
						<div class="col-md-10">
							<textarea name="observaciones" id="observaciones" cols="30" rows="3" class="form-control" style="width: 30%"></textarea>
						</div>
					</div>

					@if ($cliente != null)
						<div class="text-left">
							<input type="checkbox" name="dataup" style="transform: scale(1.5)"> <b> Actualizar datos</b>
						</div>
					@endif

					<div class="form-group text-center">				
						{!! Form::button('Enviar', ['type' => 'submit', 'id' => 'btnsu bmit', 'class' => 'btn btn-lg m-t-20 btn-dark']) !!}
					</div>
			{{ Form::close() }}
		</div>
	</div>
	<div id="popup" class="ol-popup">
		<a href="#" id="popup-closer" class="ol-popup-closer"></a>
		<div id="popup-content"></div>
	</div>
@endsection

@section('modal')
<!-- Modalserv -->
<div id="Modalserv" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="Modal" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" style="min-width: 50%">
        <div class="modal-content">
            <div class="modal-header">
                <img src="/img/carga.gif" id="imgbuscar" height="60 px" class="img-responsive" alt="Buscando">
                <h4 class="modal-title" id="titlemodal">Buscando vehículos cercanos</h4>
                <button type="button" class="close" data-dismiss="modal" disabled></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <label for="textid" class="col-md-4">ID del servicio</label>
                    <div class="col-md-8">
                        <p id="txtid"></p>
                    </div>
                </div>
                <div class="row">
                    <label for="txtestado" class="col-md-4">Estado</label>
                    <div class="col-md-8">
                        <p id="txtestado">Pendiente</p>
                    </div>
                </div>
                <div class="row">
                    <label for="txtnombrec" class="col-md-4">Conductor</label>
                    <div class="col-md-8">
                        <p id="txtnombrec"></p>
                    </div>
                </div>
                <div class="row">
                    <label for="placa" class="col-md-4">Placa</label>
                    <div class="col-md-8">
                        <p id="placac"></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <a id="linkend" class="btn btn-success disabled" aria-disabled="true">Terminar</a>
                <button id="btnvolver" type="button" onclick="resetbuttons();" class="btn btn-default" disabled>Volver</button>
            </div>
        </div>
    </div>
</div>

<!-- modalCCosto -->
<div id="modalCCosto" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalCCostoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="min-width: 50%">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modalCCostoTitulo"></h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="modalCCostoCambiar" action="">
                @csrf
                <div class="modal-body d-flex flex-column" style="gap:1em;">
                    <div class="row">
                        <div class="col-md-4">
                            <label for="sub_cuenta" class="label-required">Sub-Cuenta</label>
                        </div>
                        <div class="col-md-8">
                            <input name="sub_cuenta" id="sub_cuenta" type="text" value="" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <label for="affe" class="label-required">Affe</label>
                        </div>
                        <div class="col-md-8">
                            <input name="affe" id="affe" type="text" value="" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-dark ccosto-submit">Guardar</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="https://openlayers.org/en/v6.9.0/build/ol.js" type="text/javascript"></script>
<script src="https://maps.googleapis.com/maps/api/js?key=apikey&callback=initMap" defer></script>
	<script>
		var container = document.getElementById('popup');
		var content = document.getElementById('popup-content');
		var closer = document.getElementById('popup-closer');
		var vectorSource, vectorLayer;
		var map;
		let idValera = 0;
	
		const overlay = new ol.Overlay({
			element: container,
			autoPan: true,
			autoPanAnimation: {
				duration: 250,
			}
		});

		var marcador = new ol.style.Icon({
			size: [28,40],
			src: '/img/marker.png'
        });

		closer.onclick = function () {
			overlay.setPosition(undefined);
			closer.blur();
			return false;
		};

		map = new ol.Map({ 
			layers: [ 
				new ol.layer.Tile({ 
					source: new ol.source.OSM() 
				})], 
			target: 'mapaOpen', 
			view: new ol.View({ 
				center: ol.proj.fromLonLat([-73.1156152, 7.1155167]),
				zoom: 13
			}) 
		});

		map.on("click", function (evt) {
			marker.getGeometry().setCoordinates(evt.coordinate);
			let lonlat = ol.proj.transform(evt.coordinate, 'EPSG:3857', 'EPSG:4326');
			let latLng = {lat:lonlat[1], lng:lonlat[0]};
			geocoder.geocode({ 'location': latLng }, function(results, status) {
				if (status === 'OK') {
				if (results[0]) {
					var direccion = results[0].formatted_address;
					var municipio = obtenerMunicipio(results[0]);
					
					// Lista de municipios permitidos
					var municipiosPermitidos = ['Piedecuesta', 'Bucaramanga', 'Floridablanca', 'Giron', 'Girón', 'Lebrija', 'Rionegro', 'Ruitoque' ];

					// Verificar si el municipio está en la lista de municipios permitidos
					if (municipiosPermitidos.includes(municipio)) {
					$("#latitud").val(latLng.lat);
					$("#longitud").val(latLng.lng);
					$("#direccion").val(direccion);
					$("#sitio").val(direccion);
					$("#municipio").val(municipio);
					} else {
					Swal.fire({
						icon: 'warning',
						title: 'Municipio no permitido',
						text: 'La ubicación seleccionada no pertenece a un municipio permitido'
					});
					}
				} else {
					Swal.fire({
					icon: 'error',
					title: 'Error',
					text: 'No se encontraron resultados'
					});
				}
				} else {
				Swal.fire({
					icon: 'error',
					title: 'Error',
					text: 'Geocoder falló: ' + status
				});
				}
			});
		});

		var marker, geocoder, lugar, sincronizador, time;
		lugar = new Array();
		var buscar = false; 

		function initMap() { 
			geocoder = new google.maps.Geocoder();
		}		
		
		$("#sitio").keypress(function (e) { 
			if (e.keyCode === 13) {   
				buscarSitio();
			}
		});

		function fundireccion(lnglat) {
			$("#direccion").val(lugar[0]);
			$("#municipio").val(lugar[1]);
			$("#latitud").val(lnglat[1]);
			$("#longitud").val(lnglat[0]);	
		}

		function obtenerMunicipio(result) {
		for (var i = 0; i < result.address_components.length; i++) {
			var component = result.address_components[i];
			if (component.types.includes('locality') || component.types.includes('administrative_area_level_2')) {
			return component.long_name;
			}
		}
		return '';
		}

		
		$("#asignacion").change(function () {
			var asignacion = $(this).val();
			if(asignacion == "Directo"){
				$("#divdirecto").css("display", "block");
				$("#divdirecto :input").attr('disabled', false);

				/*$("#hora").val("inmediato");
				$("#hora").trigger("change");
				$("#hora").attr("disabled", true);*/
	
			}else{		
				$("#divdirecto").css("display", "none");
				$("#divdirecto :input").attr('disabled', true);	
				//$("#hora").attr("disabled", false);	
			}
		});

		$("#hora").change(function () {
			var hora = $(this).val();
			if(hora == "programado"){
				$("#divprogramado").css("display", "block");
				$("#divprogramado :input").attr('disabled', false);

				/*$("#asignacion").val("Normal");
				$("#asignacion").trigger("change");
				$("#asignacion").attr("disabled", true);*/
			}else{
				$("#divprogramado").css("display", "none");
				$("#divprogramado :input").attr('disabled', true);
				//$("#asignacion").attr("disabled", false);	
			}
		});

		$("#pago").change(function () {
			var pago = $(this).val();
			if(pago == "Vale electrónico"){
				$("#divfisico").css("display", "none");
				$("#divfisico :input").attr('disabled', true);
				$("#divvale").css("display", "block");
				$("#divvale :input").attr('disabled', false);
				var empresa = $("#empresa").val();

				if(empresa != 0){
					$.ajax({
					type: "GET",
					dataType: "json",
					data: {"empresa": empresa},
					url: "/servicios/getcliente",
					})
					.done(function (data, textStatus, jqXHR) {
						$("#nombres").val(data.RAZON_SOCIAL + " " + $("#valera option:selected").text());
						$("#telefono").val(data.TELEFONO);
						$("#email").val(data.EMAIL);
					})
					.fail(function (jqXHR, textStatus, errorThrown) {
						Swal.fire({
							icon: 'error',
							title: 'Error',
							text: 'No se pudo recuperar la información de la base de datos'
						});
					});	
				}
			}else if(pago == "Vale físico"){
				$("#divvale").css("display", "none");
				$("#divvale :input").attr('disabled', true);
				$("#divfisico").css("display", "block");
				$("#divfisico :input").attr('disabled', false);
				$("#idvale").val("");

				if(idValera != 0){
					$("#empresafisico").val(idValera);
				}
				var agencia = $("#empresafisico").val();

				$.ajax({
				type: "GET",
				dataType: "json",
				data: {"agencia": agencia},
				url: "/servicios/getcliente",
				})
				.done(function (data, textStatus, jqXHR) {
					$("#nombres").val(data.NOMBRE);
					$("#telefono").val(data.TELEFONO);
					$("#email").val(data.EMAIL);
				})
				.fail(function (jqXHR, textStatus, errorThrown) {
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: 'No se pudo recuperar la información de la base de datos'
					});
				});	
			}else{
				$("#divvale").css("display", "none");
				$("#divvale :input").attr('disabled', true);
				$("#idvale").val("");
				@if($servicio->id == null)
					$("#nombres").val("");
					$("#telefono").val("");
					$("#email").val("");
				@endif
			}
		});

		$("#empresa").change(function () {
			var tercero = $(this).val();
			$("#codigo").val("");
			$("#valera").empty();
			$("#valera").attr("disabled", true);
			$("#idvale").val("");
			$("#usuarios").val("");
			$("#usuarios").removeAttr("readonly");

			if(tercero != 435){			
				$("#divasignado").css("display", "block");
				$("#divavianca").css("display", "none");
				$("#beneficiariovale").val("");
				$("#referenciadovale").val("");
				$("#centrocosto").val("");
				$("#destino").val("");
				$("#avianca").val(0);
				$("#cobro").attr("disabled", false);


				if(tercero == 13885){
					$("#divesga").css("display", "flex");
					$("#divesga").insertAfter("#divagevalera");
					$("#subestacion").trigger("change");
				}else if(tercero == 784){
					$("#divpetro").css("display", "block");
					$("#divasignado").css("display", "none");
					$("#cobro").val("Ruta");
					$("#cobro").attr("disabled", true);
					cargarRutas(tercero);
				}else{
					$("#divesga").css("display", "none");
				}
				
			}else{
				$("#divasignado").css("display", "none");
				$("#divavianca").css("display", "block");
				$("#cobro").attr("disabled", true);

				$.ajax({
					type: "GET",
					dataType: "json",
					data: {"tipovale": "Tierra"},
					url: "/servicios/avianca/rutas",
				})
				.done(function (data, textStatus, jqXHR) {
					if($.isEmptyObject(data)){
						Swal.fire({
							icon: 'error',
							title: 'Error',
							text: 'No se pudo recuperar la información de la base de datos'
						});
					}else{
						$("#codigo").val(data.vale.codigo);
						$("#idvale").val(data.vale.id);
						$("#avianca").val(1);
						let rutas = data.rutas;
						for (const key in rutas) {
							$("#rutas").append('<option value="' + rutas[key].CONTRATO_VALE + '_' + rutas[key].SECUENCIA + '">' + rutas[key].ORIGEN + ' --- ' + rutas[key].DESTINO + '    ($' + rutas[key].TARIFA_COBRO.toLocaleString() + ')</option>');
						}
						$("#valera").attr("disabled", false);
						for (const key in data.valeras) {
							$("#valera").append('<option value="' + data.valeras[key].id + '">' + data.valeras[key].nombre + '</option>');
						}	
					}
				})
				.fail(function (jqXHR, textStatus, errorThrown) {
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: 'No se pudo recuperar la información de la base de datos'
					});
				});	
			}

			if(tercero != 435){
				$.ajax({
				type: "GET",
				dataType: "json",
				url: "/servicios/getvaleras/" + tercero,
				})
				.done(function (data, textStatus, jqXHR) {
					if(data.length > 0 ){
						$("#valera").attr("disabled", false);
						for (const key in data) {
							$("#valera").append('<option value="' + data[key].id + '">' + data[key].nombre + '</option>');
						}
						if(idValera != 0){
							$("#valera").val(idValera);
							@if ($servicio->vale != null)
								$("#codigo").val('{{$servicio->vale->codigo}}');
								$("#usuarios").val('{{$servicio->usuarios}}');
								$("#usuarios").val($("#usuarios").val().replace("|", "\n"));
								$("#codigo").trigger("focusout");
							@endif
						}else{
							$("#valera").trigger("change");
						}					
					}else{
						Swal.fire({
							icon: 'error',
							title: 'Error',
							text: 'Esta empresa no posee valeras activas'
						});
					}
				})
				.fail(function (jqXHR, textStatus, errorThrown) {
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: 'No se pudo recuperar la información de la base de datos'
					});
				});
			}

			$.ajax({
				type: "GET",
				dataType: "json",
				data: {"empresa": tercero},
				url: "/servicios/getcliente",
				})
				.done(function (data, textStatus, jqXHR) {
					$("#nombres").val(data.RAZON_SOCIAL);
					$("#telefono").val(data.TELEFONO);
					$("#email").val(data.EMAIL);
				})
				.fail(function (jqXHR, textStatus, errorThrown) {
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: 'No se pudo recuperar la información de la base de datos'
					});
				});			
		});

		$("#empresafisico").change(function () {
			let agen = $(this).val();
			$.ajax({
				type: "GET",
				dataType: "json",
				data: {"agencia": agen},
				url: "/servicios/getcliente",
				})
				.done(function (data, textStatus, jqXHR) {
					$("#nombres").val(data.NOMBRE);
					$("#telefono").val(data.TELEFONO);
					$("#email").val(data.EMAIL);

					if(agen == 89020123001 || agen == 80021569401){
						$("#divesga").css("display", "flex");
						$("#divesga").insertAfter("#divfisico");
						$("#subestacion").attr("disabled", false).trigger("change");
					}else{
						$("#divesga").css("display", "none");
					}
				})
				.fail(function (jqXHR, textStatus, errorThrown) {
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: 'No se pudo recuperar la información de la base de datos'
					});
				});	
		});
		let valerasAutomaticas = [147, 302, 303];

		$("#valera").change(function () {
			$("#codigo").val("");
			$("#beneficiariovale").val("");
			$("#referenciadovale").val("");
			$("#centrocosto").val("");
			$("#destino").val("");
			$("#idvale").val("");
			$("#usuarios").val("");
			$("#usuarios").removeAttr("readonly");
			
			$("#nombres").val($("#empresa option:selected").text() + " " + $("#valera option:selected").text());
			
			let valeraSel = Number($(this).val());		
			if (valerasAutomaticas.includes(valeraSel)){
				$.ajax({
					type: "GET",
					url: "/servicios/vale_automatico",
					data: {"idValera": $(this).val()},
					dataType: "json"
				})
				.done(function (data, textStatus, jqXHR) {
					if(!$.isEmptyObject(data)){
						$("#codigo").val(data.codigo);
						$("#idvale").val(data.id);
					}
				})
				.fail(function (jqXHR, textStatus, errorThrown) {
					Swal.fire({
						icon: 'error',
						title: 'Error: Código de vale automático falló',
						text: textStatus
					});
				});	
			}
		});

		function ubicarSitio(){
			var sitio = $("#direccion").val();	
			var direct;
			sitio = sitio + " " + $("#municipio").val() + ", Santander";
		
			if(sitio !== ""){
				direct = sitio;
			}
			geocoder.geocode( { 'address': sitio}, function(results, status) {
				let encontrado = false;
				let muni = $("#municipio").val();
				if (status == 'OK') {
					for (const key in results[0].address_components) {
						if(results[0].address_components[key].types[0] == "locality"  || results[0].address_components[key].types[0] == "administrative_area_level_2"){
							if(results[0].address_components[key].long_name == muni){
								encontrado = true;
								break;
							}
						}
					}
					if(encontrado){
						let lnglat = [results[0].geometry.location.lng(), results[0].geometry.location.lat()];
						let coordinate = ol.proj.transform(lnglat, 'EPSG:4326', 'EPSG:3857');
						marker.getGeometry().setCoordinates(coordinate);
						map.getView().setCenter(coordinate);
						map.getView().setZoom(16);

						buscar = true;						
						lugar[0] = direct;
						$("#direccion").val(lugar[0]);
						$("#latitud").val(results[0].geometry.location.lat());
						$("#longitud").val(results[0].geometry.location.lng());
						
					}else{
						Swal.fire({
							icon: 'warning',
							title: 'Dirección desconocida',
							text: 'No se pudo localizar la dirección en el municipio seleccionado'
						});
					}	  
				} else {
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: 'No se pudo localizar el sitio suministrado: ' + sitio
					});
				}
			});
		}

		function buscarSitio(lanzar = 0){
			let municipios =['Bucaramanga','Floridablanca','Girón','Piedecuesta','Lebrija'];
			var sitio = $("#sitio").val();
			let ubicaciones = [];	
				if(sitio !== ""){
					direct = sitio;
				}
				for (const municipio of municipios) {
				let direccion = sitio + ', ' + municipio + ', Santander';
					geocoder.geocode({ 'address': direccion }, function(results, status) {
						let encontrado = false;
						if (status == 'OK') {
							for (const result of results) {
								if (result.address_components.some(component => municipios.includes(component.long_name))) {
									if (!ubicaciones.some(u => u.formatted_address === result.formatted_address)) {
										ubicaciones.push(result);
									}		
								}
							}
						} 
					});	
				}
				
				setTimeout(() => {
					if (ubicaciones.length === 0 ) {
						Swal.fire({
							icon: 'error',
							title: 'Error',
							text: 'No se encontraron ubicaciones para: ' + sitio
						});
					} else {
						mostrardirecciones(ubicaciones, lanzar, sitio);
					}
				}, 1000);																	
		}

		async function mostrardirecciones(ubicaciones,lanzar,sitio) { //funcion asincrona
			let opciones = new Object();
			for (const key in ubicaciones) {
				opciones[key]=ubicaciones[key].formatted_address;
			}

			const { value: Vubicacion } = await Swal.fire({
				title: 'Seleccione una Dirección',
				input: 'select',
				inputOptions: opciones,
				inputPlaceholder: 'Seleccione una dirección',
				showCancelButton: true,
				inputValidator: (value) => {
					return new Promise((resolve) => {
					if (value !== '') {
						resolve()
					} else {
						resolve('Necesitas seleccionar una opción :)')
						}
					})
				}
			});

			if (Vubicacion) {
				let lnglat = [ubicaciones[Vubicacion].geometry.location.lng(), ubicaciones[Vubicacion].geometry.location.lat()];
				let coordinate = ol.proj.transform(lnglat, 'EPSG:4326', 'EPSG:3857');
				marker.getGeometry().setCoordinates(coordinate);
				map.getView().setCenter(coordinate);
				map.getView().setZoom(16);
				buscar = true;						
				lugar[0] = sitio;

				for (const key in ubicaciones[Vubicacion].address_components) {
					if (ubicaciones[Vubicacion].address_components[key].types[0] == "locality"  || ubicaciones[Vubicacion].address_components[key].types[0] == "administrative_area_level_2"){
						lugar[1]=ubicaciones[Vubicacion].address_components[key].long_name;
					}
				}

				if (lanzar == 0) {
					fundireccion(lnglat);
				}
			}
		}

		$("#codigo").focusout(function () {
			var codigo = $("#codigo").val();

			if(codigo != ""){
				var empresa = $("#empresa").val();
				var valera = $("#valera").val();
				if(valera == 101){
					$(this).val("");
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: 'La valera seleccionada no puede ser usada para servicios manuales'
					});

					return;
				}
				$.ajax({
				type: "GET",
				data:{"valera":valera, "codigo":codigo},
				dataType: "json",
				url: "/servicios/getvale",
				})
				.done(function (data, textStatus, jqXHR) {
					var borrar = true;
					if(data.valeranull == 0){
						Swal.fire({
							icon: 'error',
							title: 'Error',
							text: 'La valera seleccionada no está vigente'
						});
					}else{
						if(data.valenull == 0){
							$("#codigo").val("");
							$("#codigo").attr('placeholder', 'Código');
							$("#codigo").addClass('vale-error');
							Swal.fire({
								icon: 'error',
								title: 'Error',
								text: 'El código del vale ingresado no es válido'
							});
						}else{
							if(data.usadonull == 0){
								$("#codigo").val("");
								$("#codigo").attr('placeholder', 'Código');
								$("#codigo").addClass('vale-error');
								Swal.fire({
									icon: 'error',
									title: 'Error',
									text: 'El código de vale ingresado ya fue usado'
								});							
							}else{
								$("#beneficiariovale").val(data.beneficiario);
								$("#referenciadovale").val(data.referenciado);
								$("#centrocosto").val(data.centrocosto);
								$("#destino").val(data.destino);
								$("#idvale").val(data.id);
								if(data.estado == "Asignado"){
									$("#usuarios").val(data.beneficiario + ".");
									if(data.beneficiario != "."){
										$("#usuarios").attr("readonly", true);
									}
								}								
								borrar = false;
							}
						}
					}

					if(borrar){
						$("#beneficiariovale").val("");
						$("#referenciadovale").val("");
						$("#centrocosto").val("");
						$("#destino").val("");
						$("#idvale").val("");
						$("#usuarios").val("");
						$("#usuarios").removeAttr("readonly");
					}
				})
				.fail(function (jqXHR, textStatus, errorThrown) {
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: 'No se pudo recuperar la información de la base de datos'
					});
				});

			}else{
				$("#beneficiariovale").val("");
				$("#referenciadovale").val("");
				$("#centrocosto").val("");
				$("#destino").val("");
				$("#idvale").val("");
				$("#usuarios").val("");
				$("#usuarios").removeAttr("readonly");
			}			
		});

		$(document).ready(function () {
			vectorLayer = new ol.layer.Vector({
				source: new ol.source.Vector()
			});
			map.addLayer(vectorLayer);
			map.addOverlay(overlay);

			marker = new ol.Feature({
				geometry: new ol.geom.Point(ol.proj.fromLonLat([-73.1156152, 7.1155167])),
				posicion: 0
			});
			marker.setStyle(new ol.style.Style(
				{
					image: marcador
				}
			));
			vectorLayer.getSource().addFeature(marker);

			@if($cliente != null && $servicio->id == null)
				let ubicli = [{{$cliente->longitud}}, {{$cliente->latitud}}];
				$("#latitud").val(ubicli[1]);
				$("#longitud").val(ubicli[0]);
				let coordinate = ol.proj.transform(ubicli, 'EPSG:4326', 'EPSG:3857');

				marker.getGeometry().setCoordinates(coordinate);
				map.getView().setCenter(coordinate);
				map.getView().setZoom(16);
			@endif

			$("#contacto, #Otrocontacto").on("input", function() {
				var currentValue = $(this).val();
				$("#contacto, #Otrocontacto").not(this).val(currentValue);
			});

			@if ($servicio->id != null)
				$("#asignacion").val('{{$servicio->asignacion}}');
				$("#asignacion").trigger("change");
				@if ($servicio->fechaprogramada != null) 
					$("#hora").val("programado");
					$("#hora").trigger("change");
					$("#fechaprogramada").val('{{$servicio->fechaprogramada}}');
				@else
					$("#hora").val('inmediato');
				@endif
				$("#pago").val('{{$servicio->pago}}');
				@if($servicio->pago == 'Vale físico')
					idValera = {{$servicio->nroIdentificacion}};
				@endif
				$("#pago").trigger("change");
				@if($servicio->vale != null)
					$("#empresa").val({{$servicio->vale->valera->cuentae->agencia_tercero_TERCERO}});
					idValera = {{$servicio->vale->valeras_id}};
					$("#empresa").trigger("change");
				@else
					$("#usuarios").val('{{$servicio->usuarios}}');
				@endif
				$("#cobro").val('{{$servicio->cobro}}');
				$("#contacto").val('{{$servicio->contacto}}');
				$("#complemento").val('{{$servicio->adicional}}');
				$("#observaciones").val('{{$servicio->observaciones}}');
				$("#sitio").val('{{$servicio->direccion}}');

				let ubiserv = [{{$servicio->longitud}}, {{$servicio->latitud}}];
				$("#latitud").val(ubiserv[1]);
				$("#longitud").val(ubiserv[0]);
				let coordinate = ol.proj.transform(ubiserv, 'EPSG:4326', 'EPSG:3857');

				marker.getGeometry().setCoordinates(coordinate);
				map.getView().setCenter(coordinate);
				map.getView().setZoom(16);
			@endif

			$("html,body").animate({scrollTop: $("#contservicio").offset().top}, 2000);			
		});

		$("#tipovale").change(function (event) {
			var tipo = $(this).val();
			if(tipo == "Equipaje"){
				//$("#rutas").attr("multiple", true);
				//$("#rutas").css("height", "250px");
				$("#divuelo").css("display", "none");
				$("#divoucher").css("display", "none");
				$("#divusuariosav").css("display", "none");
				$("#divcentrocostoav").css("display", "flex");	
			}else if(tipo == "Tierra"){
				$("#divuelo").css("display", "none");
				$("#divoucher").css("display", "none");
				//$("#rutas").attr("multiple", false);
				//$("#rutas").css("height", "auto");

				$("#divusuariosav").css("display", "block");
				$("#divcentrocostoav").css("display", "none");		
			}else if (tipo == "Tripulación") {
				$("#divuelo").css("display", "flex");
				$("#divoucher").css("display", "none");
				//$("#rutas").attr("multiple", false);
				//$("#rutas").css("height", "auto");

				$("#divusuariosav").css("display", "block");
				$("#divcentrocostoav").css("display", "none");
			}else{
				$("#divuelo").css("display", "flex");
				$("#divoucher").css("display", "flex");
				//$("#rutas").attr("multiple", false);
				//$("#rutas").css("height", "auto");

				$("#divusuariosav").css("display", "none");
				$("#divcentrocostoav").css("display", "flex");
			}
		
			$("#rutas").empty();
			$.ajax({
				type: "GET",
				dataType: "json",
				data: {"tipovale": tipo},
				url: "/servicios/avianca/rutas",
			})
			.done(function (data, textStatus, jqXHR) {
				if($.isEmptyObject(data)){
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: 'No se pudo recuperar la información de la base de datos'
					});
				}else{
					let rutas = data.rutas;
					for (const key in rutas) {
						$("#rutas").append('<option value="' + rutas[key].CONTRATO_VALE + '_' + rutas[key].SECUENCIA + '">' + rutas[key].ORIGEN + ' --- ' + rutas[key].DESTINO + '    ($' + rutas[key].TARIFA_COBRO.toLocaleString() + ')</option>');
					}
				}
			})
			.fail(function (jqXHR, textStatus, errorThrown) {
				Swal.fire({
					icon: 'error',
					title: 'Error',
					text: 'No se pudo recuperar la información de la base de datos'
				});
			});	
		});

		var inav = new Array();
		inav["usuav1"] = "";
		inav["usuav2"] = "";
		inav["usuav3"] = "";

		$(".usuav").autocomplete({
      		source: function( request, response ) {
				$.ajax({
					url: "/servicios/avianca/get_usuariosav",
					dataType: "json",
					data: {identificacion: request.term},
					success: function( data ) {
						response( $.map(data, function (item) {
							var objeto = new Object();
							objeto.label = item.identificacion + "---" + item.nombres + " " + item.apellidos;
							objeto.value = item.identificacion;
							objeto.zona = item.zona;
							objeto.direccion = item.direccion;
							objeto.complemento = item.complemento;
							objeto.latitud = item.latitud;
							objeto.longitud = item.longitud;
							return objeto;
						}) );
					}
				});
			},
			minLength: 3,
			select: function (event, ui) {
				let ind = $(this).attr("id");
				inav[ind] = ui.item.label.split("---")[1];

				$("#usuarios").val(inav["usuav1"] + '\n' + inav["usuav2"] + '\n' + inav["usuav3"]);

				if ($("#tiposer").val() == "Recogida" && $(this).attr("id") == "usuav1") {
					$("#sitio").val(ui.item.direccion);
					$("#direccion").val(ui.item.direccion);
					$("#municipio").val(ui.item.zona);
					$("#complemento").val(ui.item.complemento);

					if (ui.item.longitud != null) {
						let lnglat = [ui.item.longitud, ui.item.latitud];
						let coordinate = ol.proj.transform(lnglat, 'EPSG:4326', 'EPSG:3857');
						marker.getGeometry().setCoordinates(coordinate);
						map.getView().setCenter(coordinate);
						map.getView().setZoom(16);

						buscar = true;						
						lugar[0] = ui.item.direccion;
						$("#direccion").val(ui.item.direccion);
						$("#latitud").val(ui.item.latitud);
						$("#longitud").val(ui.item.longitud);
						
					}else{
						if (ui.item.direccion != null) {
							ubicarSitio();
						}
					}		
				}
			}
		});

		var limite;

		$("#formservicio").submit(function (event) {
			event.preventDefault();
			$("#btnsubmit").attr("disabled", true);
			$("#load").remove();
			if($("#pago").val() == "Vale electrónico" && $("#idvale").val() == ""){
				Swal.fire({
					icon: 'error',
					title: 'Error',
					text: 'La información del vale no ha sido confirmada'
				});			
			}else if($("#direccion").val() == ""){
				Swal.fire({
					icon: 'error',
					title: 'Error',
					text: 'La información de dirección no se ha completado'
				});
			}else{
				if($("#hora").val() == "inmediato"){
					$("#Modalserv").modal('show');
				}
				if($("#asignacion").val() == "Directo"){
					limite = 30;
				}else{
					limite = 25;
				}
				if($("#valera").val() == 66){
					$("#cobro").val("Ruta");
				}		
				$.ajax({
					type: "POST",
					data:  $(this).serialize(),
					dataType: "json",
					url: $(this).attr('action'),
				})
				.done(function (data, textStatus, jqXHR) {
					if(!$.isEmptyObject(data)){
						if(data.respuesta == "Correcto"){
							if($("#hora").val() == "inmediato"){
								$("#servicioid").val(data.id);
								$("#txtid").text(data.id);
								$("#imgbuscar").show('fast');
								$("#titlemodal").text("Buscando vehiculos cercanos");
								sincronizador = setInterval(sincroservicio, 2000, data.id);
								//time = setTimeout(pararsincro, 30000, data.id);
							}else{
								window.location.href = "/servicios/en_curso";
							}
						}else{
							$("#imgbuscar").hide('slow');
							$("#titlemodal").text("No se asignó vehiculo");
							$("#txtestado").text("No vehiculo");
							$("#Modalserv").modal('hide');
							Swal.fire({
								icon: 'error',
								title: 'Error',
								text: data.mensaje
							});
						}
					}else{
						$("#imgbuscar").hide('slow');
						$("#titlemodal").text("No se asignó vehiculo");
						$("#txtestado").text("No vehiculo");
						$("#Modalserv").modal('hide');
						Swal.fire({
							icon: 'error',
							title: 'Error',
							text: 'No se pudo crear el servicio con los datos enviados'
						});
					}										
				})
				.fail(function (jqXHR, textStatus, errorThrown) {
					$("#imgbuscar").hide('slow');
					$("#titlemodal").text("No se asignó vehiculo");
					$("#txtestado").text("No vehiculo");
					$("#Modalserv").modal('hide');
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: 'Algo falló al enviar los datos del servicio'
					});
				});
			}				
		});

		$("#subestacion").change(function(){
			let datos = $(this).val().split("_");
			$("#sitio").val(datos[0]);
			$("#direccion").val(datos[0]);
			$("#municipio").val(datos[1]);

			ubicarSitio();

			if(datos.length > 2){
				$("#complemento").val(datos[2]);
			}		
		});

		var ciclos = 0;
		var fin = false;
		function sincroservicio(servicio) {
			ciclos++;
			if(ciclos <= limite){
				$.ajax({
					type: "GET",
					data:  {'id': servicio},
					dataType: "json",
					url: "/servicios/sincronizar",
				})
				.done(function (data, textStatus, jqXHR) {
					if(!$.isEmptyObject(data)){
						$("#txtnombrec").text(data.nombrec);
						$("#placac").text(data.placa);
						$("#txtestado").text("Asignado");
						clearInterval(sincronizador);
						$("#imgbuscar").hide('slow');
						$("#titlemodal").text("Asignación de vehiculo correcta");
						$("#linkend").attr('href', '/servicios/en_curso');
						$("#linkend").removeClass('disabled');
						$("#linkend").removeAttr('aria-disabled');
						ciclos = 31;
					}
				})
				.fail(function (jqXHR, textStatus, errorThrown) {
					
				});
			}else{
				clearInterval(sincronizador);
				if(fin == false){					
					pararsincro(servicio);
				}else{
					detenersincro(servicio);
				}			
			}			
		}

		function pararsincro(servicio){
			if($("#asignacion").val() == "Normal"){
					$.ajax({
					type: "GET",
					dataType: "json",
					data:  {'id': servicio},
					url: "/servicios/liberar",
					})
					.done(function (data, textStatus, jqXHR) {
						if(data.estado == "Libre"){
							$("#titlemodal").text("Buscando en todos los vehiculos activos");
							ciclos = 0;
							fin = true;	
							sincronizador = setInterval(sincroservicio, 2000, servicio);						
							//time = setTimeout(detenersincro, 20000, servicio);		
						}else if(data.estado == "Asignado"){
							$("#txtnombrec").text(data.nombrec);
							$("#placac").text(data.placa);
							$("#txtestado").text("Asignado");
							$("#imgbuscar").hide('slow');
							$("#titlemodal").text("Asignación de vehiculo correcta");
							$("#linkend").attr('href', '/servicios/en_curso');
							$("#linkend").removeClass('disabled');
							$("#linkend").removeAttr('aria-disabled');
						}else{
							detenersincro(servicio);
						}
					})
					.fail(function (jqXHR, textStatus, errorThrown) {
						detenersincro(servicio);
					});			
			}else{
				detenersincro(servicio);
			}		
		}

		function detenersincro(servicio) {
			$.ajax({
					type: "GET",
					dataType: "json",
					data:  {'id': servicio},
					url: "/servicios/detener",
				})
				.done(function (data, textStatus, jqXHR) {
					if(data.estado == "No vehiculo"){
						$("#imgbuscar").hide('slow');
						$("#titlemodal").text("No se asignó vehiculo");
						$("#txtestado").text("No vehiculo");
						$("#linkend").attr('href', '/servicios/finalizados');
						$("#linkend").removeClass('disabled');
						$("#linkend").removeAttr('aria-disabled');
						$("#btnvolver").removeAttr('disabled');		
						$("#btnsubmit").attr("disabled", false);											
					}else if(data.estado == "Asignado"){
						$("#txtnombrec").text(data.nombrec);
						$("#placac").text(data.placa);
						$("#txtestado").text("Asignado");
						$("#imgbuscar").hide('slow');
						$("#titlemodal").text("Asignación de vehiculo correcta");
						$("#linkend").attr('href', '/servicios/en_curso');
						$("#linkend").removeClass('disabled');
						$("#linkend").removeAttr('aria-disabled');
					}
				})
				.fail(function (jqXHR, textStatus, errorThrown) {
					$("#imgbuscar").hide('slow');
					$("#titlemodal").text("No se asignó vehiculo");
					$("#txtestado").text("No vehiculo");
					$("#linkend").attr('href', '/servicios/finalizados');
					$("#linkend").removeClass('disabled');
					$("#linkend").removeAttr('aria-disabled');
					$("#btnvolver").removeAttr('disabled');
					$("#btnsubmit").attr("disabled", false);
				});			
		}

		function resetbuttons() {
			$("#linkend").removeAttr('href');
			$("#linkend").addClass('disabled');
			$("#linkend").attr('aria-disabled', 'true');
			$("#btnvolver").attr('disabled', 'true');
			$("#Modalserv").modal('hide');
			$("#titlemodal").text("Buscando vehiculos cercanos");
			$("#txtestado").text("Pendiente");
			$("#btnsubmit").attr("disabled", false);
			ciclos = 0;
			fin = false;
		}
		
		$("#placa").autocomplete({
      		source: function( request, response ) {
        	$.ajax({
          		url: "/getconductores_placa",
          		dataType: "json",
          		data: {placa: request.term},
          	success: function( data ) {
            		response( $.map(data, function (item) {
						return{
							label : item.placa + "_" + item.nombre,
							value : item.placa + "_" + item.nombre + "_" + item.cuenta			
							}
						}) );
          			}
        		});
			},
			minLength: 3
		});

		$("#tiposer").change(function(){
			if ($(this).val() == "Reparto") {
				$("#sitio").val("aeropuerto");
				$("#direccion").val("aeropuerto");
				$("#municipio").val("Lebrija");
				$("#divreportado").css("display", "none");
				ubicarSitio();
			}else{
				$("#divreportado").css("display", "block");
			}			
		});

		function cargarRutas(tercero) {
			$.ajax({
				type: "GET",
				dataType: "json",
				data: {
					"tercero": tercero
				},
				url: "/empresas/" + tercero + "/rutas/activas",
			})
			.done(function(data, textStatus, jqXHR) {
				if (data.length == 0) {
					Swal.fire({
						icon: 'warning',
						title: '¡Atención!',
						text: 'No se encontraron rutas activas',
						footer: 'Se requiere un contrato vigente'
					});
				} else {
					$("#rutaspetro").empty();
					for (const key in data) {
						$("#rutaspetro").append('<option value="' + data[key].CONTRATO_VALE + '_' + data[key].SECUENCIA + '">' + data[key].ORIGEN + ' --- ' + data[key].DESTINO + '    ($' + data[key].TARIFA_COBRO.toLocaleString() + ')</option>');
					}
				}
			})
			.fail(function(jqXHR, textStatus, errorThrown) {
				Swal.fire({
					icon: 'error',
					title: 'Error 500',
					text: 'No se pudo recuperar la información de la base de datos'
				});
			});
		}
		
		let inpetro = new Array();
		inpetro["usuariopetro1"] = "";
		inpetro["usuariopetro2"] = "";
		inpetro["usuariopetro3"] = "";
		inpetro["usuariopetro4"] = "";
		// Se aplica a todos los input de clase usuariopetro
		$(".usuariopetro").autocomplete({
			source: function(request, response) {
				$.ajax({
					url: "/servicios/get_usuarios",
					dataType: "json",
					data: {
						identificacion: request.term
					},
					success: function(data) {
						response($.map(data, function(item) {
							// todos los atributos añadidos a objetos se cargaran en la variable UI
							let objeto = new Object();
							objeto.id= item.id;
							if (item.identificacion === null || item.identificacion === undefined) {
								objeto.label = item.nombre;
								objeto.value = item.nombre;
							} else {
								objeto.label = item.identificacion + "---" + item.nombre;
								objeto.value = item.identificacion;
							}
							objeto.celulares = parseInt(item.celulares.replace(/[^0-9]/g, ''), 10);
							objeto.direccion = item.direccion;
							objeto.municipio = item.municipio;
							objeto.barrio = item.barrio;
							objeto.complemento = item.complemento;
							objeto.latitud = item.lat;
							objeto.longitud = item.lng;
							objeto.sub_cuenta=item.sub_cuenta;
							objeto.affe=item.affe;
							objeto.solicitado=item.solicitado;
							objeto.autorizado=item.autorizado;
							return objeto;
						}));
					}
				});
			},
			minLength: 3,
			select: function(event, ui) {
				let ind = $(this).attr("id");				

				let labelParts = ui.item.label.split("---");

				// Si el label no contiene '---', asignamos todo el label al array
				if (labelParts.length === 1) {
					inpetro[ind] = labelParts[0];
				} else {
					inpetro[ind] = labelParts[1];
				}

				// Al seleccionar cargamos un string juntado gracias al del split todos los label de usuarios añadidos
				$("#usuarios").val(inpetro["usuariopetro1"] + '\n' + inpetro["usuariopetro2"] + '\n' + inpetro[
					"usuariopetro3"] + '\n' + inpetro["usuariopetro4"]);

				// Encontrar el siguiente grupo de inputs
				let inputGroup = $(this).closest('.input-group');
				let allInputGroups = $('.input-group');
				let currentIndex = allInputGroups.index(inputGroup);
				let foundGroup = allInputGroups.eq(currentIndex);

				let allInputs = foundGroup.find('input');
				let ccostoInput = allInputs.eq(1);
				let idusuarioInput = allInputs.eq(2);

				let modalCCostoButton = foundGroup.find('button');

				
				if (idusuarioInput.length) {
					// Actualizar el valor del input idUsuario
					let id= ui.item.id;
					idusuarioInput.val(id); 
				}
				
				if (ccostoInput.length) {
					// Actualizar el valor del input ccosto
					let valueText="Sub_Cuenta: "+ui.item.sub_cuenta+" / AFFE: "+ ui.item.affe ;
					ccostoInput.val(valueText); 
					ccostoInput.attr('placeholder', valueText ); 
					ccostoInput.attr('title', valueText); 
				}

				if (modalCCostoButton.length) {
					modalCCostoButton.attr('data-form-action', `/pasajero/${ui.item.id}/actualizar/CCosto`); 
					modalCCostoButton.attr('data-modal-title', `Centro de Costo Pasajero: ${ui.item.label}`);
				}

				if ($(this).attr("id") == "usuariopetro1") {
					$("#contacto").val(ui.item.celulares);
					$("#sitio").val(ui.item.direccion);
					$("#direccion").val(ui.item.direccion);
					$("#municipio").val(ui.item.municipio);
					$("#complemento").val(ui.item.barrio);

					if (ui.item.longitud != null) {
						let lnglat = [ui.item.longitud, ui.item.latitud];
						let coordinate = ol.proj.transform(lnglat, 'EPSG:4326', 'EPSG:3857');
						marker.getGeometry().setCoordinates(coordinate);
						map.getView().setCenter(coordinate);
						map.getView().setZoom(16);
						buscar = true;
						lugar[0] = ui.item.direccion;
						$("#direccion").val(ui.item.direccion);
						$("#latitud").val(ui.item.latitud);
						$("#longitud").val(ui.item.longitud);
					} else {
						if (ui.item.direccion != null) {
							ubicarSitio();
						}
					}
				}
			}
		});
		
		$('button[data-toggle="modal"]').on('click', function() {
			// Obtener datos del botón
			let modalId = $(this).data('modal-id');
			let formAction = $(this).data('form-action');
			let modalTitle = $(this).data('modal-title');
			//Recuperamos y Guardamos el id del input group en el modal para actualizarlo luego
			let groupId = $(this).closest('.input-group').data('group-id');
			$('#modalCCosto').data('group-id', groupId);
			// Actualizar el contenido del modal
			$('#modalCCosto #modalCCostoTitulo').text(modalTitle);
			$('#modalCCosto #modalCCostoCambiar').attr('action', formAction);
		});

		$('.ccosto-submit').click(function(e) {
			e.preventDefault();
			
			// Recoge el formulario
			let form = $('#modalCCostoCambiar');
			let groupId = $('#modalCCosto').data('group-id');

			// Limpia los errores previos
			form.find('.is-invalid').removeClass('is-invalid');
			form.find('.invalid-feedback').remove();
			
			// Envía el formulario
			$.ajax({
				url: form.attr('action'), // URL de destino
				method: 'POST',
				data: form.serialize(),
				success: function(response) {
					if (response.message) {
						// Mostrar mensaje de éxito
						Swal.fire({
							icon: 'success',
							title: 'Éxito',
							text: response.message,
						});
						
						// Reflejar cambios: Actualiza el campo input del centro de costo en el input-group correspondiente
						let valueText="Sub_Cuenta: "+response.sub_cuenta+" / AFFE: "+ response.affe ;
						let inputField= $(`div[data-group-id="${groupId}"] .centro-costo`);
						inputField.val(valueText); 
						inputField.attr('placeholder', valueText); 
						inputField.attr('title',  valueText); 

						// Limpiamos y cierramos el modal IMPORTANTE LIMPIARLO PARA QUE NO QUEDE CON DATOS
						form[0].reset();
						$('#modalCCosto').modal('hide');
					} else {
						// Caso por defecto
						Swal.fire({
							icon: 'warning',
							title: 'Atención',
							text: 'La respuesta no fue como se esperaba. Por favor, verifique la acción nuevamente.',
						});
					}
				},
				error: function(xhr) {
						// Limpia los errores anteriores
						form.find('.invalid-feedback').remove();
						form.find('.is-invalid').removeClass('is-invalid');
						// mensaje por defecto
						let errorMessage = 'Algo salió mal, por favor intenta nuevamente.';

						//Todos los mensajes traen mensajes por defecto para evitar rupturas
						if (xhr.status === 422) {
							// Errores de validación / sintaxis
							let errors = xhr.responseJSON.errors;
							
							// Muestra los errores en el formulario
							$.each(errors, function(key, errorMessages) {
								let input = form.find(`[name="${key}"]`);
								input.addClass('is-invalid');
								input.after(`<div class="invalid-feedback">${errorMessages[0]}</div>`);
							});
						} else if (xhr.status === 404) {
							// Manejo de error 404 sin registros de pasajero
							if (xhr.responseJSON && xhr.responseJSON.message) {
								errorMessage = xhr.responseJSON.message;
							} else {
								errorMessage = 'El registro solicitado no fue encontrado.';
							}
						} else if (xhr.status === 500) {
							// Manejo de error 500 trae el error del catch
							if (xhr.responseJSON && xhr.responseJSON.message) {
								errorMessage = xhr.responseJSON.message;
							} else {
								errorMessage = 'Ocurrió un error interno del servidor. Por favor, intenta de nuevo más tarde.';
							}
						}
						// Muestra el mensaje de error
						Swal.fire({
							icon: 'error',
							title: 'Error',
							text: errorMessage,
						});
				}
			});
		});
		

	</script>
@endsection