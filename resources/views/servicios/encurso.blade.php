@extends('layouts.logeado')

@section('style')
	<style>
		ul.ui-autocomplete {
			z-index: 1100;
		}

		tbody tr td{
			max-height: 150px;
		}

		.resaltar-servicio {
			background-color: #d5f6f8a6; /* Puedes cambiar el color aquí */
			font-weight: normal;
		}
	</style>
@endsection

@if (isset($filtro))
	@section('sub_title', 'Servicios en Curso. Filtro: ' . $filtro[0] . '=' . $filtro[1])
@else
	@section('sub_title', 'Servicios en Curso')
@endif

@section('sub_content')
	<div class="card">
		<div class="card-body">
			@isset($filtro)
				<input type="hidden" id="filtro" value="{{$filtro[0]}}_{{$filtro[1]}}">
			@endisset
				<div class="align-center" style="display: inline">
					@if ($usuario->roles_id == 1 || $usuario->modulos[0]->pivot->editar == 1)
						<a href="{{route('servicios.nuevo')}}" class="btn btn-dark btn-sm">Nuevo servicio</a>
					@endif
					<button type="button" class="btn" style="background-color: #00965e; margin-left: 5px; float: right" onclick="toexcel();"><i class="fa fa-file-excel-o" aria-hidden="true"></i></button>                    
					<p style="font-weight:bold;margin-top:0.5rem; float: right">Exportar: </p>
				</div>

			@if($errors->first('sql') != null)
				<div class="alert alert-danger" style="margin:5px 0">
					<h6>{{$errors->first('sql')}}</h6>
				</div>				
			@endif

			<div class="table-responsive" id="listar" style="min-height: 500px">
				<table class="table table-bordered" id="tab_listar" style="table-layout: fixed">
					<thead>
						<tr>
                            <th>ID</th>
							<th>Fecha</th>
							<th>Cliente</th>
							<th>Teléfono</th>
							<th>Dir. Origen</th>
							<th>Despacho</th>
							<th>Modo pago</th>
							<th>Asignación</th>
							<th>Conductor</th>
							<th>Vehiculo</th>
							<th>Fuente</th>
							<th>Estado</th>
						</tr>
						<tr>

							<th><form method="GET" class="form-inline" action="/servicios/filtrar_en_curso"><input type="number" name="id" class="form-control" required></form></th>

							<th><form  method="GET" class="form-inline" action="/servicios/filtrar_en_curso"><input type="date" name="fecha" class="form-control" onchange="this.form.submit()" required></form></th>

							<th><form  method="GET" class="form-inline" action="/servicios/filtrar_en_curso"><input type="text" name="cliente" class="form-control" required></form></th>

							<th><form  method="GET" class="form-inline" action="/servicios/filtrar_en_curso"><input type="text" name="telefono" class="form-control" required></form></th>

							<th><form  method="GET" class="form-inline" action="/servicios/filtrar_en_curso"><input type="text" name="direccion" class="form-control" required></form></th>

							<th>

								<form  method="GET" class="form-inline" action="/servicios/filtrar_en_curso">

								<select name="fechaprogramada" class="form-control" onchange="this.form.submit()">

									<option value="sinfiltro"></option>

									<option value="inmediato">Inmediato</option>

									<option value="programado">Programado</option>

								</select>

								</form>

							</th>

							<th>

								<form  method="GET" class="form-inline" action="/servicios/filtrar_en_curso">						

								<select name="pago" class="form-control" onchange="this.form.submit()">

									<option value="sinfiltro"></option>

									<option value="Efectivo">Efectivo</option>

									<option value="Vale electrónico">Vale electrónico</option>

									<option value="Vale físico">Vale físico</option>

								</select>

								</form>

							</th>

							<th>

								<form  method="GET" class="form-inline" action="/servicios/filtrar_en_curso">

								<select name="asignacion" class="form-control" onchange="this.form.submit()">

									<option value="sinfiltro"></option>

									<option value="Normal">Normal</option>

									<option value="Directo">Directo</option>

								</select>

								</form>

							</th>

							<th><form  method="GET" class="form-inline" action="/servicios/filtrar_en_curso"><input type="text" name="conductor" class="form-control" required></form></th>

							<th><form  method="GET" class="form-inline" action="/servicios/filtrar_en_curso"><input type="text" name="vehiculo" maxlength="7" class="form-control" required></form></th>

							<th><form  method="GET" class="form-inline" action="/servicios/filtrar_en_curso">

									<select name="fuente" class="form-control" id="fuente" onchange="this.form.submit()">

										<option value="">Todos</option>

										<option value="CRM">CRM</option>

										<option value="IVR">IVR</option>

										<option value="APP Usuario">App Usuario</option>

									</select>

								</form>

							</th>

							<th>

								<form  method="GET" class="form-inline" action="/servicios/filtrar_en_curso">

								<select id="estado" name="estado" class="form-control" onchange="this.form.submit()">

									<option value="sinfiltro"></option>

									<option value="Pendiente">Pendiente</option>

									<option value="Asignado">Asignado</option>

									<option value="En curso">En curso</option>

								</select>

								</form>

							</th>

						</tr>

					</thead>

					<tbody>

						@forelse($servicios as $servicio)	
							<tr>					
                                <td>{{ $servicio->id }}</td>
								<td>{{ $servicio->fecha }}</td>
								
								@if ($servicio->vale != null)  <!-- servicio con vales-->
									<td @if ($servicio->vale->valera->cuentae->agencia_tercero_TERCERO == 3408) class="resaltar-servicio" @endif>														
										{{ $servicio->vale_servicio->vale->valera->cuentae->agencia->NOMBRE }}
									</td>
								@else 	<!-- servicio normal-->
									<td>									
										{{ $servicio->cliente->nombres}}
									</td>
								@endif
									
								<td>{{ $servicio->cliente->telefono }}</td>
								<td><div style="max-height: 100px; overflow: hidden;">{{ $servicio->direccion }}</div></td>
								<td>@if ($servicio->fechaprogramada == null)
										Inmediato
									@else
										{{$servicio->fechaprogramada}}
									@endif
								</td>
								<td>{{ $servicio->pago }}</td>
								<td>{{ $servicio->asignacion}}</td>
								<td>@if ($servicio->cuentac == Null)
										Sin asignar
									@else
										{{ $servicio->cuentac->conductor->NOMBRE}}
									@endif
								</td>
								<td>@if ($servicio->placa == null)
										Sin asignar
									@else
										{{$servicio->placa}}
									@endif
								</td>
								<td>@if ($servicio->users_id != null)

									@if ($servicio->users_id == 112)

										IVR

									@else

										CRM

									@endif

								@else

									App Usuario

								@endif
							

								</td>

								<td> {{$servicio->estado}} </td>

								<td>
									<div class="buttons-list-container">
										<div class="buttons-list-items"">
											<a href="{{ route('servicios.detalles', ['servicio' => $servicio->id]) }}" class="btn btn-info btn-sm">Detalles</a>

											@if ($servicio->pago == "Vale electrónico")
		
												@if ($servicio->estadocobro == 0)
		
													<a href="/servicios/gestion_cobro/{{$servicio->id}}" data-toggle="tooltip" title="Cobro por horas y ruta bloqueado. (Click para habilitar)" class="btn btn-danger btn-sm">Horas y Ruta</a>
		
												@else
		
													<a href="/servicios/gestion_cobro/{{$servicio->id}}" data-toggle="tooltip" title="Cobro por horas y ruta habilitado. (Click para bloquear)" class="btn btn-success btn-sm">Horas y Ruta</a>
		
												@endif
		
											@endif
		
											@if ($servicio->valeav != null && $servicio->fechaprogramada != null)
		
													<button class="btn btn-sm btn-warning" onclick="getServicio({{$servicio->id}})">Editar</button>
		
											@endif
		
											@if ($servicio->valeav == null && $servicio->fechaprogramada != null)
		
												@if ($servicio->clientes_id == 90 && $servicio->ruta != null)
		
													<button class="btn btn-sm btn-warning" onclick="servicioMajorel({{$servicio->id}})">Editar</button>
		
												@else	
		
													<button class="btn btn-sm btn-warning" onclick="getEditarServicio({{$servicio->id}})">Editar</button>
		
												@endif
		
											@endif		
										</div>
									</div>
	

								</td>

							</tr>

							@empty

							<tr class="align-center">

								<td colspan="4">No hay datos</td>

							</tr>

						@endforelse

					</tbody>

				</table>

				@if(method_exists($servicios,'links'))

					{{ $servicios->links() }}

				@endif			

			</div>

		</div>

	</div>

@endsection

@section('modal')

	<div id="Modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="Modal" aria-hidden="true">

		<div class="modal-dialog" style="min-width: 50%">

			<div class="modal-content">

				<div class="modal-header">

					<h4 class="modal-title">Editar Servicio Avianca</h4>

					<button type="button" class="close" data-dismiss="modal">&times;</button>

				</div>

				<form id="formtrans" action="/servicios/avianca/editar" method="POST">

				<div class="modal-body" style="min-height: 200px">

					<div class="row">

						<div class="col-md-4">

							<label for="idservicio" class="label-required">ID Servicio</label>                         

						</div>

						<div class="col-md-8">

							<input type="number" name="idservicio" id="idservicio" class="form-control" readonly required>	

						</div>

					</div>

					<br>

					<div class="row">

						<div class="col-md-4">

							<label for="fechades" class="label-required">Fecha despacho</label>                         

						</div>

						<div class="col-md-8">

							<input type="datetime-local" name="fechades" id="fechades" class="form-control">

						</div>

					</div>

					<br>

					<div class="row" id="divdirecto" style="display: none">

						<div class="col-md-4">

							<label for="placa" class="label-required">Placa y conductor</label>                         

						</div>

						<div class="col-md-8">

							<input type="text" name="placa" id="placa" class="form-control placaComplete">

						</div>

					</div>

					

					<div id="locales" style="display: none">

						<div class="row">

							<div class="col-md-4">

								<label for="usuariosl" class="label-required">Usuarios</label>                         

							</div>

							<div class="col-md-8">

								<textarea name="usuariosl" id="usuariosl" cols="30" rows="4" class="form-control"></textarea>

							</div>

						</div>

					</div>

					<div id="registrados" style="display: none">

						<div class="row">

							<div class="col-md-4">

								<label for="usuav1" class="label-required">Usuario 1</label>                         

							</div>

							<div class="col-md-8">

								<input type="text" name="usuav1" id="usuav1" class="form-control usuav"/>

							</div>

						</div>

						<br>

						<div class="row">

							<div class="col-md-4">

								<label for="usuav2">Usuario 2</label>                         

							</div>

							<div class="col-md-8">

								<input type="text" name="usuav2" id="usuav2" class="form-control usuav"/>

							</div>

						</div>

						<br>

						<div class="row">

							<div class="col-md-4">

								<label for="usuav3">Usuario 3</label>                         

							</div>

							<div class="col-md-8">

								<input type="text" name="usuav3" id="usuav3" class="form-control usuav"/>

							</div>

						</div>

					</div>

				</div>

				<div class="modal-footer">

					<input type="hidden" name="_token" value="{{csrf_token()}}">

					<button type="submit" class="btn btn-success">Guardar</button>

					<button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>

				</div>

				</form>

			</div>

		</div>

	</div>



	<div id="servicioMajorel" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="Modal" aria-hidden="true">
		<div class="modal-dialog" style="min-width: 70%">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title">Editar Servicio Majorel</h4>
					<button type="button" class="close" data-dismiss="modal">&times;</button>
				</div>

				<form id="formMajorel" action="/servicios/majorel/actualizar" method="POST">
					<div class="modal-body" style="min-height: 200px">
						<div class="row form-group">
							<div class="col-md-4">
								<label for="idMajorel" class="label-required">ID Servicio</label>                         
							</div>

							<div class="col-md-8">
								<input type="number" name="idMajorel" id="idMajorel" class="form-control" readonly required>	
							</div>
						</div>

						<div class="row form-group">
							<div class="col-md-4">
								<label for="fechaMajorel" class="label-required">Fecha programada</label>                         
							</div>

							<div class="col-md-8">
								<input type="datetime-local" name="fechaMajorel" id="fechaMajorel" class="form-control">
							</div>
						</div>

						<div class="row form-group" id="editarDirecto">
							<div class="col-md-4">
								<label for="placaMajorel">Placa y conductor</label>                         
							</div>

							<div class="col-md-8">
								<input type="text" name="placaMajorel" id="placaMajorel" class="form-control placaComplete">
							</div>
						</div>

						<div class="for-group">
							<table class="table">
								<thead>
									<tr style="text-align: center">
										<th>Pasajero</th>
										<th>Novedad</th>
										<th>Observaciones</th>
									</tr>
							</thead>

							<tbody id="bodypasajeros">
							</tbody>	

							</table>
						</div>
					</div>

					<div class="modal-footer">
						<input type="hidden" name="_token" value="{{csrf_token()}}">
						<button type="submit" class="btn btn-success">Guardar</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
					</div>
				</form>
			</div>
		</div>
	</div>



	<div id="EditServicio" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="Modal" aria-hidden="true">

		<div class="modal-dialog" style="min-width: 50%">

			<div class="modal-content">

				<div class="modal-header">

					<h4 class="modal-title">Editar Servicio</h4>

					<button type="button" class="close" data-dismiss="modal">&times;</button>

				</div>

				<form id="formedit" action="/servicios/editar" method="POST">

					<div class="modal-body" style="min-height: 200px">

						<div class="row form-group">

							<div class="col-md-4">

								<label for="editarIdServicio" class="label-required">ID Servicio</label>                         

							</div>

							<div class="col-md-8">

								<input type="number" name="editarIdServicio" id="editarIdServicio" class="form-control" readonly required>	

							</div>

						</div>

						<div class="row form-group">

							<div class="col-md-4">

								<label for="editarFecha" class="label-required">Fecha programada</label>                         

							</div>

							<div class="col-md-8">

								<input type="datetime-local" name="editarFecha" id="editarFecha" class="form-control">

							</div>

						</div>

						<div class="row form-group" id="editarDirecto">

							<div class="col-md-4">

								<label for="editarPlaca">Placa y conductor</label>                         

							</div>

							<div class="col-md-8">

								<input type="text" name="editarPlaca" id="editarPlaca" class="form-control placaComplete">

							</div>

						</div>

						<div class="row form-group">

							<label for="editarDireccion" class="col-md-4">Dirección</label>

							<div class="col-md-8">

								<textarea id="editarDireccion" name="editarDireccion" rows="4" class="form-control"></textarea>

							</div>

						</div>

						<div class="row form-group">

							<label for="editarUsuarios" class="col-md-4">Usuarios</label>

							<div class="col-md-8">

								<textarea name="editarUsuarios" id="editarUsuarios" class="form-control" rows="4"></textarea>

							</div>

						</div>

					</div>

					<div class="modal-footer">

						<input type="hidden" name="_token" value="{{csrf_token()}}">

						<button type="submit" class="btn btn-success">Guardar</button>

						<button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>

					</div>

				</form>

			</div>

		</div>

	</div>

@endsection

@section('script')

	<script>

	

	    var pen = {{$pen}};

		var asi = {{$asi}};

		var cur = {{$cur}};

		var sincro = 1;

	    

		$(document).ready(function () {

			@if(count($servicios) > 1)

				setInterval(cambios, 5000);

			@endif

		});



	    function cambios(){

			if(sincro == 1){

				$.ajax({

                type: "GET",

				dataType: "json",

                url: "/servicios/ajaxencurso",

				}).done(function (data, textStatus, jqXHR) {

					if(data.cancelados.length > 0){

						sincro = 0;

						alerta("cancelados");

						Swal.fire({

							title: "Servicio cancelado",

							text: "Se han cancelado " + data.cancelados.length + " servicio(s)",

							icon: "warning",

							confirmButtonText: 'Revisar',

						}).then((result) => {

							sincro = 1;

							for (const key in data.cancelados) {			

								window.open("/servicios/filtrar_en_curso?id=" + data.cancelados[key].id);

							}

						});

					}else if(data.perdidos.length > 0){

						sincro = 0;

						alerta("perdidos");

						Swal.fire({

							title: "Programado(s) no tomado(s)",

							text: "No se han tomado " + data.perdidos.length + " servicio(s) programados",

							icon: "warning",

							confirmButtonText: 'Revisar',

						}).then((result) => {

							sincro = 1;

							for (const key in data.perdidos) {			

								window.open("/servicios/filtrar_en_curso?id=" + data.perdidos[key].id);

							}

						});

					}else if(data.asi != asi){

						location.reload();

					}else if(data.cur != cur){

						location.reload();

					}else if(data.pen != pen){

						location.reload();

					}		

				}).fail(function (jqXHR, textStatus, errorThrown) {

					

				});

			}

	    }



		function alerta(tipo) {

			var aud = document.createElement('audio');

			if(tipo == "ivr"){

				aud.src = "/sounds/ivr.mp3";

			}else if(tipo == "perdidos"){

				aud.src = "/sounds/perdidos.mp3";

			}else{

				aud.src = "/sounds/electronicos.mp3";

			}

			aud.autoplay = true;

			aud.volume = 0.2;

			aud.muted = true;

			document.body.appendChild(aud);

			aud.muted = false;

			aud.play();

		}



		function getServicio(servicio) {

			$("#idservicio").val(servicio);

			$("#Modal").modal('show');

			sincro = 0;

			$.ajax({

                type: "GET",

				dataType: "json",

				data: {idservicio: servicio},

                url: "/servicios/avianca/get_edicion",

            })

            .done(function (data, textStatus, jqXHR) {            

				$("#fechades").val(data.fechaprogramada.replace(" ", "T"));

				if(!$.isEmptyObject(data.usuariosav)){
					for (let index = 0; index < data.usuariosav.length; index++) {
						$("#usuav" + (index+1)).val(data.usuariosav[index].identificacion);				
					}
					$("#usuav1").attr("required", true);
					$("#registrados").css("display", "block");
					$("#locales").css("display", "none");
					$("#usuariosl").attr("required", false);
					$("#usuariosl").val("");
				}else{
					$("#usuariosl").val(data.usuarios);
					$("#usuariosl").attr("required", true);
					$("#locales").css("display", "block");
					$("#usuav1").attr("required", false);
					$("#registrados").css("display", "none");
				}

				if(data.asignacion == "Directo"){
					$("#divdirecto").css("display", "flex").after("<br>");
					$("#placa").val(data.placa + "_" + data.cuentac.conductor.NOMBRE + "_" + data.cuentac.id);
				}else{
					$("#divdirecto").css("display", "none");
					$("#placa").val("");
				}		
            })
            .fail(function (jqXHR, textStatus, errorThrown) {
            });
		}

		$(".usuav").autocomplete({
			appendTo: "#Modal",
      		source: function( request, response ) {
        	$.ajax({
          		url: "/servicios/avianca/get_usuariosav",
          		dataType: "json",
          		data: {identificacion: request.term},
				success: function( data ) {
					response( $.map(data, function (item) {
						return{
							label : item.identificacion + "---" + item.nombres + " " + item.apellidos,
							value : item.identificacion			
							}
						}) );
					}
				});
			},
			minLength: 3
    	});

		$(".placaComplete").autocomplete({
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

		function toexcel(){
			Swal.fire({
				title: '<strong>Exportando...</strong>',
				html:'<img src="/img/carga.gif" height="60 px" class="img-responsive" alt="Buscando">',
				showConfirmButton: false,
			});

			$.ajax({
				method: "GET",
				url: "/servicioscurso/exportar",
				data: { 'filtro': $("#filtro").val()}
			})
			.done(function (data, textStatus, jqXHR) {
				const byteCharacters = atob(data);
				const byteNumbers = new Array(byteCharacters.length);
				for (let i = 0; i < byteCharacters.length; i++) {
					byteNumbers[i] = byteCharacters.charCodeAt(i);
				}
				const byteArray = new Uint8Array(byteNumbers);
				var csvFile;
				var downloadLink;
				filename = "Servicios en curso.xlsx";
				csvFile = new Blob([byteArray], {type:'application/vnd.ms-excel'});
				downloadLink = document.createElement("a");
				downloadLink.download = filename;
				downloadLink.href = window.URL.createObjectURL(csvFile);
				downloadLink.style.display = "none";
				document.body.appendChild(downloadLink);
				downloadLink.click();
				Swal.close()		
			})

			.fail(function (jqXHR, textStatus, errorThrown) {
				Swal.close();
				Swal.fire({
					icon: 'error',
					title: 'Error',
					text: 'No se pudo recuperar la información de la base de datos'
				});
			});
		}

		function getEditarServicio(servicio) { 
			$("#editarIdServicio").val(servicio);
			$("#EditServicio").modal('show');
			sincro = 0;
			$.ajax({
				type: "get",
				url: "/servicios/get_editar",
				data: {idservicio:servicio},
				dataType: "json"
			}).done(function (data) {  
				if(!$.isEmptyObject(data)){		
					$("#editarFecha").val(data.fechaprogramada.replace(" ", "T"));
					$("#editarDireccion").val(data.direccion);
					$("#editarUsuarios").val(data.usuarios);
					if(data.asignacion == "Directo"){
						$("#editarPlaca").val(data.placa + "_" + data.cuentac.conductor.NOMBRE + "_" + data.cuentac.id);
					}else{
						$("#editarPlaca").val("");
					}	
				}
			}).fail(function () {  
			});
		}

		function servicioMajorel(servicio) {
			$("#idMajorel").val(servicio);
			$("#servicioMajorel").modal('show');
			sincro = 0;
			$.ajax({
				type: "get",
				url: "/servicios/majorel/editar",
				data: {idservicio:servicio},
				dataType: "json"
			}).done(function (data) {  
				$("#bodypasajeros").empty();
				if(!$.isEmptyObject(data)){	
					let novedades = '';
					for (const i in data.novedadesmaj) {
						novedades = novedades + '<option value="' + data.novedadesmaj[i].id + '">' + data.novedadesmaj[i].descripcion + '</option>';
					}
					$("#fechaMajorel").val(data.fechaprogramada.replace(" ", "T"));
					$("#direccionMajorel").val(data.direccion);
					if(data.asignacion == "Directo"){
						$("#placaMajorel").val(data.placa + "_" + data.cuentac.conductor.NOMBRE + "_" + data.cuentac.id);
					}else{
						$("#placaMajorel").val("");
					}

					let pasajeros = '';
					let key = 0;
					for (key = 0; key < data.ruta.pasajeros.length; key++) {
						pasajeros += '<tr>';
						pasajeros += '<td><input class="form-control" type="text" value="' + data.ruta.pasajeros[key].nombre + '" readonly></td>';
						pasajeros += '<td><select class="form-control" form="formMajorel" name="' + data.ruta.pasajeros[key].id + '">';
						pasajeros += '<option value="0">Sin novedad</option>';
						for (const i in data.novedadesmaj) {
							const selected = data.novedadesmaj[i].id === data.ruta.pasajeros[key].pivot.novedadesmaj_id ? 'selected' : '';
							pasajeros += '<option value="' + data.novedadesmaj[i].id + '" ' + selected + '>' + data.novedadesmaj[i].descripcion + '</option>';
						}
						pasajeros += '</select></td>';
						pasajeros += '<td><textarea class="form-control" form="formMajorel" name="obs' + data.ruta.pasajeros[key].id + '">' + (data.ruta.pasajeros[key].pivot.observaciones != null ? data.ruta.pasajeros[key].pivot.observaciones : "") + '</textarea></td>';
						pasajeros += '</tr>';
					}
					if(key < 4){
						for (let j = 0; j < 4 - key; j++) {
							pasajeros += '<tr>';
							pasajeros += '<td> <input type="text" placeholder="Cedula nuevo pasajero" class="form-control" form="formMajorel" name="newpasajero' + j + '" id="newpasajero' + j + '"/> </td>';
							pasajeros = pasajeros + '<td><select class="form-control" form="formMajorel" name="newnovedad'+ j + '"> <option value="0">Sin novedad</option>';
							pasajeros = pasajeros + novedades + ' </select></td><td><textarea class="form-control" form="formMajorel" name="newobs' + j + '"></textarea></td></tr>';
						}
					}
					$("#bodypasajeros").append(pasajeros);
				}
			}).fail(function () {  
			});
		}
		
		$('.modal').on('hidden.bs.modal', function () {
			sincro = 1;
		});

	</script>

@endsection