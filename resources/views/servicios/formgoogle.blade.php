@extends('layouts.logeado')

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
				<div class="form-group row {{ $errors->has('nombres') ? 'form-error': '' }}">
					{{ Form::label('nombres', 'Nombre', ['class' => 'label-required col-md-2']) }}
					<div class="col-md-10">
						@if ($cliente != null)
							{{ Form::text('nombres', $cliente->nombres, ['required', 'class' => 'form-control', 'style' => 'width:60%']) }}
						@else
							{{ Form::text('nombres', null, ['required', 'class' => 'form-control', 'style' => 'width:60%']) }}
						@endif						
						{!! $errors->first('nombres', '<p class="help-block">:message</p>') !!}
					</div>
				</div>
				<div class="form-group row {{ $errors->has('telefono') ? 'form-error': '' }}">
					{{ Form::label('telefono', 'Telefono', ['class' => 'label-required col-md-2']) }}
					<div class="col-md-4">
						@if ($cliente != null)
							{{ Form::text('telefono', $cliente->telefono, ['required', 'class' => 'form-control']) }}
						@else
							{{ Form::text('telefono', null, ['required', 'class' => 'form-control']) }}
						@endif			
						{!! $errors->first('telefono', '<p class="help-block">:message</p>') !!}
					</div>

					{{ Form::label('email', 'Correo electrónico', ['class' => 'label col-md-2']) }}
					<div class="col-md-4">
						@if ($cliente != null)
							{{ Form::text('email', $cliente->email, ['class' => 'form-control']) }}
						@else
							{{ Form::text('email', null, ['class' => 'form-control']) }}
						@endif					
						{!! $errors->first('email', '<p class="help-block">:message</p>') !!}
					</div>
				</div>
				<h4>Datos del servicio</h4>
				<hr>
				<div class="form-group row {{ $errors->has('asignacion') ? 'form-error': '' }}">
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
						{!! $errors->first('pago', '<p class="help-block">:message</p>') !!}
					</div>
				</div>
				<div id="divdirecto" style="display: none">					
					<div class="form-group row {{ $errors->has('placa') ? 'form-error': '' }}">
						{{ Form::label('placa', 'Placa', ['class' => 'label-required col-md-2']) }}
						<div class="col-md-10">
							<input type="text" minlength="7" maxlength="7" name="placa" id="placa" class="form-control" style="width: 60%" disabled required>
						</div>
					</div>
				</div>
				<div id="divprogramado" style="display: none">
					<div class="form-group row {{ $errors->has('horaprogramada') ? 'form-error': '' }}">
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
									{!! $errors->first('empresa', '<p class="help-block">:message</p>') !!}
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
						<div class="form-group row {{ $errors->has('codigo') ? 'form-error': '' }}">
							{{ Form::label('codigo', 'Código del vale', ['class' => 'label-required col-md-2']) }}
							<div class="col-md-2">
								<input type="number" name="codigo" id="codigo" class="form-control" disabled required>
							</div>
						</div>						
						<div id="divasignado">							
							<div class="form-group row {{ $errors->has('codigo') ? 'form-error': '' }}">
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
							<div class="form-group row {{ $errors->has('beneficiariovale') ? 'form-error': '' }}">
									{{ Form::label('beneficiariovale', 'Beneficiario del vale', ['class' => 'label col-md-2']) }}
									<div class="col-md-10">
										<input type="text" name="beneficiariovale" id="beneficiariovale" class="form-control" style="width: 50%" readonly>
									</div>
							</div>
							<div class="form-group row {{ $errors->has('centrocosto') ? 'form-error': '' }}">
								{{ Form::label('centrocosto', 'Centro de Costo', ['class' => 'label col-md-2']) }}
								<div class="col-md-10">
									<input type="text" name="centrocosto" id="centrocosto" class="form-control" style="width: 50%" readonly>
								</div>
							</div>
							<div class="form-group row {{ $errors->has('referenciadovale') ? 'form-error': '' }}">
								{{ Form::label('referenciadovale', 'Actividad a realizar', ['class' => 'label col-md-2']) }}
								<div class="col-md-10">
									<input type="text" name="referenciadovale" id="referenciadovale" class="form-control" style="width: 50%" readonly>
								</div>
							</div>
							<div class="form-group row {{ $errors->has('destino') ? 'form-error': '' }}">
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
					</div>

					<div class="form-group row {{ $errors->has('usuarios') ? 'form-error': '' }}">
						{{ Form::label('usuarios', 'Usuarios del servicio', ['class' => 'label-required col-md-2']) }}
						<div class="col-md-10">
							<textarea name="usuarios" class="form-control" id="usuarios" cols="30" rows="3" style="width: 50%" required></textarea>
						</div>
					</div>

					<div class="form-group row mt-4">
						<div class="col-12">
							<input form="formbuscar" type="text" name="sitio" class="form-control" placeholder="Direccion Completa" id="sitio"> 
						</div>
					</div>

					<div class="row mb-4">
						<div class="col-12">
							<div id="mapa" style="min-height: 300px">
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

					<div class="form-group row {{ $errors->has('flota') ? 'form-error': '' }}">
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

					<div class="form-group row {{ $errors->has('complemento') ? 'form-error': '' }}">
						{{ Form::label('complemento', 'Complemento', ['class' => 'col-md-2']) }}
						<div class="col-md-10">
							{{ Form::text('complemento', null, ['class' => 'form-control', 'style' => 'width:30%']) }}
						</div>
					</div>

					<div class="form-group row {{ $errors->has('observaciones') ? 'form-error': '' }}">
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
@endsection

@section('modal')
	<div id="Modalserv" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="Modal" aria-hidden="true" data-backdrop="static" data-keyboard="false">
		<div class="modal-dialog" style="min-width: 50%">
			<div class="modal-content">
				<div class="modal-header">
					<img src="/img/carga.gif" id="imgbuscar" height="60 px" class="img-responsive" alt="Buscando">
					<h4 class="modal-title" id="titlemodal">Buscando vehiculos cercanos</h4>
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
@endsection

@section('script')
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyByzEj0ALxdktnognv3gr6XCIeN3DtMw1U&callback=initMap" async defer></script>
	<script>
		var marker, map, geocoder, lugar, sincronizador, time;
		lugar = new Array();
		var buscar = false; 
		function initMap() {
			var soacha =  {lat: 4.569261, lng: -74.187225}; 
  			var bucaramanga = {lat: 7.1155167, lng: -73.1156152};
			geocoder = new google.maps.Geocoder();
  			map = new google.maps.Map(
      		document.getElementById('mapa'), {
				  	zoom: 13, 
				  	center: bucaramanga, 
        			gestureHandling: 'greedy',
        			zoomControl: true,
        			mapTypeControl: true,
        			scaleControl: true,
        			streetViewControl: false,
        			rotateControl: true,
        			fullscreenControl: true
			});
			marker = new google.maps.Marker({
				position: bucaramanga,
				map: map,
				title:"Ubicación del servicio"
			});
			@if($cliente != null)
				var ubicli = {lat: {{$cliente->latitud}}, lng: {{$cliente->longitud}}};
				$("#latitud").val(ubicli.lat);
				$("#longitud").val(ubicli.lng);

      			map.setCenter(ubicli);
				marker.setPosition(ubicli);
				map.setZoom(16);
			@endif

			map.addListener('click', function(event) {
			marker.setPosition(event.latLng);
			geocoder.geocode({ 'location': event.latLng }, function(results, status) {
				if (status === 'OK') {
				if (results[0]) {
					var direccion = results[0].formatted_address;
					var municipio = obtenerMunicipio(results[0]);
					
					// Lista de municipios permitidos
					var municipiosPermitidos = ['Piedecuesta', 'Bucaramanga', 'Floridablanca', 'Giron', 'Girón', 'Lebrija', 'Rionegro', 'Ruitoque' ];

					// Verificar si el municipio está en la lista de municipios permitidos
					if (municipiosPermitidos.includes(municipio)) {
					$("#latitud").val(event.latLng.lat());
					$("#longitud").val(event.latLng.lng());
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
		}		

		$("#btnmapa").click(async function (e) { 
			if($("#empresa").val() == 435 && $("#direccion").val() != "" && ($("#tipovale").val() == "Tierra" || $("#tipovale").val() == "Tripulación")){
				const { value: codigo } = await Swal.fire({
					title: 'Código de Autorización',
					input: 'text',
					inputPlaceholder: 'Ingrese autorización para actualizar dirección',
					inputValue: '',
					showCancelButton: true
				});

				if(codigo){
					$("#autorizacion").val(codigo);
					$("#Modal").modal('show');
				}
			}else{
				$("#Modal").modal('show');
			}		
		});
		
		$("#sitio").keypress(function (e) { 
			if (e.keyCode === 13) {   
				buscarSitio();
			}
		});

		function fundireccion() {
			$("#direccion").val(lugar[0]);
			$("#municipio").val(lugar[1]);
			$("#latitud").val(marker.getPosition().lat());
			$("#longitud").val(marker.getPosition().lng());	
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

			}else if(pago == "Vale físico"){
				$("#divvale").css("display", "none");
				$("#divvale :input").attr('disabled', true);
				$("#divfisico").css("display", "block");
				$("#divfisico :input").attr('disabled', false);
				$("#idvale").val("");

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
				$("#nombres").val("");
				$("#telefono").val("");
				$("#email").val("");
				$("#idvale").val("");
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

				if(tercero == 13885){
					$("#divesga").css("display", "flex");
					$("#divesga").insertAfter("#divagevalera");
					$("#subestacion").trigger("change");
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
						map.setCenter(results[0].geometry.location);
						marker.setPosition(results[0].geometry.location);
						map.setZoom(16);
						buscar = true;						
						lugar[0] = direct;
							$("#direccion").val(lugar[0]);
							$("#latitud").val(marker.getPosition().lat());
							$("#longitud").val(marker.getPosition().lng());
						
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
			})
				if (Vubicacion) {
					map.setCenter(ubicaciones[Vubicacion].geometry.location);
					marker.setPosition(ubicaciones[Vubicacion].geometry.location);
					map.setZoom(16);
					buscar = true;						
					lugar[0] = sitio;

				for (const key in ubicaciones[Vubicacion].address_components) {
					if (ubicaciones[Vubicacion].address_components[key].types[0] == "locality"  || ubicaciones[Vubicacion].address_components[key].types[0] == "administrative_area_level_2"){
						lugar[1]=ubicaciones[Vubicacion].address_components[key].long_name;
					}
				}

				if (lanzar == 0) {
					fundireccion()
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

				ubicarSitio();

				$("#complemento").val(ui.item.complemento);
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

	</script>
@endsection