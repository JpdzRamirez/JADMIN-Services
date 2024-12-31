@extends('layouts.logeado')
@section('style')
	<style>
		ul.ui-autocomplete {
			z-index: 1100;
		}
	</style>
@endsection
@if (isset($filtro))
	@section('sub_title', 'Servicios Finalizados. Filtro: ' . $filtro)
@else
	@section('sub_title', 'Servicios Finalizados')
@endif


@section('sub_content')
	<div class="card">
		<div class="card-body">
			@isset($filtra)
				<input type="hidden" id="filtro" value="{{implode(",", $idsfiltro)}}">
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
							<th>Unidades/Valor</th>
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
							<form method="GET" id="formfiltro" class="form-inline" action="/servicios/filtrar_finalizados"></form>
								<th>
									@if (!empty($c1))
										<input type="number" value="{{$c1}}" name="id" form="formfiltro" class="form-control filt">
									@else
										<input type="number" name="id" class="form-control filt" form="formfiltro">
									@endif
									
								</th>
								<th>	
									@if (!empty($c2))
										<input type="text" id="fecha" value="{{$c2}}" name="fecha" class="form-control" form="formfiltro" autocomplete="off" onchange="this.form.submit()">
									@else
										<input type="text" id="fecha" name="fecha" class="form-control" form="formfiltro" autocomplete="off" onchange="this.form.submit()">
									@endif					
								</th>
								<th>
									@if (!empty($c3))
										<input type="text" value="{{$c3}}" name="cliente" class="form-control filt" form="formfiltro">
									@else
										<input type="text" name="cliente" class="form-control filt" form="formfiltro">
									@endif										
								</th>
								<th>
									
								</th>
								<th>
									@if (!empty($c5))
										<input type="text" value="{{$c5}}" name="direccion" form="formfiltro" class="form-control filt">
									@else
										<input type="text" name="direccion" class="form-control filt" form="formfiltro">
									@endif
								</th>
								<th>	
									@if (!empty($c6))
										@if ($c6 == "inmediato")
											<select name="fechaprogramada" id="fechaprogramada" form="formfiltro" class="form-control" onchange="this.form.submit()">
												<option value="">Todos</option>
												<option value="inmediato" selected>Inmediato</option>
												<option value="programado">Programado</option>
											</select>
										@else
										<select name="fechaprogramada" id="fechaprogramada" form="formfiltro" class="form-control" onchange="this.form.submit()">
											<option value="">Todos</option>
											<option value="inmediato">Inmediato</option>
											<option value="programado" selected>Programado</option>
										</select>
										@endif
									@else
										<select name="fechaprogramada" id="fechaprogramada" form="formfiltro" class="form-control" onchange="this.form.submit()">
											<option value="">Todos</option>
											<option value="inmediato">Inmediato</option>
											<option value="programado">Programado</option>
										</select>
									@endif																
								</th>
								<th>
									@if (!empty($c7))
										<select name="pago" id="pago" class="form-control" form="formfiltro" onchange="this.form.submit()">
											<option value="">Todos</option>
										@if ($c7 == "Efectivo")
											<option value="Efectivo" selected>Efectivo</option>
											<option value="Vale electrónico">Vale electrónico</option>
											<option value="Vale físico">Vale físico</option>
										@elseif($c7 == "Vale electrónico")
											<option value="Efectivo">Efectivo</option>
											<option value="Vale electrónico" selected>Vale electrónico</option>
											<option value="Vale físico">Vale físico</option>
										@else
											<option value="Efectivo">Efectivo</option>
											<option value="Vale electrónico">Vale electrónico</option>
											<option value="Vale físico" selected>Vale físico</option>
										@endif
										</select>
									@else
										<select name="pago" id="pago" class="form-control" form="formfiltro" onchange="this.form.submit()">
											<option value="">Todos</option>
											<option value="Efectivo">Efectivo</option>
											<option value="Vale electrónico">Vale electrónico</option>
											<option value="Vale físico">Vale físico</option>
										</select>
									@endif					
								</th>
								<th>
									@if (!empty($c8))
										<select name="asignacion" id="asignacion" class="form-control" form="formfiltro" onchange="this.form.submit()">
											<option value="">Todos</option>
										@if ($c8 == "Normal")
											<option value="Normal" selected>Normal</option>
											<option value="Directo">Directo</option>											
										@else
											<option value="Normal">Normal</option>
											<option value="Directo" selected>Directo</option>
										@endif
										</select>
									@else
										<select name="asignacion" id="asignacion" class="form-control" form="formfiltro" onchange="this.form.submit()">
											<option value="">Todos</option>
											<option value="Normal">Normal</option>
											<option value="Directo">Directo</option>
										</select>
									@endif
								</th>
								<th>
									@if (!empty($c9))
										<input type="text" value="{{$c9}}" name="conductor" form="formfiltro" class="form-control filt">
									@else
										<input type="text" name="conductor" class="form-control filt" form="formfiltro">
									@endif
								</th>
								<th>
									@if (!empty($c10))
										<input type="text" name="vehiculo" value="{{$c10}}" maxlength="7" form="formfiltro" class="form-control filt">
									@else
										<input type="text" name="vehiculo" maxlength="7" class="form-control filt" form="formfiltro">
									@endif
								</th>
								<th>
									<select name="fuente" class="form-control" id="fuente" form="formfiltro" onchange="this.form.submit()">
										<option value="">Todos</option>
										<option value="CRM">CRM</option>
										<option value="IVR">IVR</option>
										<option value="APP Usuario">App Usuario</option>
									</select>
								</th>
								<th>
									<select id="estado" name="estado" class="form-control" form="formfiltro" onchange="this.form.submit()">
										<option value="">Todos</option>
										<option value="Finalizado">Finalizado</option>
										<option value="No vehiculo">No vehiculo</option>
										<option value="Cancelado">Cancelado</option>
										<option value="Cancelado devuelto">Cancelado devuelto</option>
									</select>
								</th>
						</tr>
					</thead>
					<tbody>
						@forelse($servicios as $servicio)
							<tr>
                                <td>{{ $servicio->id }}</td>
								<td>{{ $servicio->fecha }}</td>
								@if ($servicio->vale != null)  <!-- servicio con vales-->
									<td>														
										{{ $servicio->vale_servicio->vale->valera->cuentae->agencia->NOMBRE }}
									</td>
								@else 	<!-- servicio normal-->
									<td>									
										{{ $servicio->cliente->nombres}}
									</td>
								@endif
								<td>
									@if ($servicio->valor != null)
										{{ $servicio->unidades}} - ${{number_format($servicio->valor)}} 
									@endif
								</td>
								<td>{{ $servicio->direccion }}</td>
								<td>@if ($servicio->fechaprogramada == null)
										Inmediato
									@else
										Programado
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
											@if ($usuario->roles_id == 1 || $usuario->modulos[0]->pivot->editar == 1)
												<a href="{{ route('novedades.listar', ['servicio' => $servicio->id]) }}" class="btn btn-warning btn-sm">Novedades</a>
												@if ($servicio->estado == "Cancelado")
													@if ($usuario->roles_id == 1)
														<button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#Modal" value="{{$servicio->id}}">Devolución</button>
													@else
														@php
															$fecha = Carbon\Carbon::now();
														@endphp
														@if ($servicio->cancelacion != null)
															@if ($fecha->diffInMinutes($servicio->cancelacion->fecha) < 90)
																<button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#Modal" value="{{$servicio->id}}">Devolución</button>
															@endif
														@endif
													@endif
												@endif
												@if ($servicio->clientes_id == 90 && $servicio->estado == "Finalizado" && $servicio->ruta != null)
												<button class="btn btn-sm btn-dark" onclick="servicioFinalizadoMajorel({{$servicio->id}})">Reportar</button>
												@endif
		
												@if ($servicio->clientes_id == 90 && $servicio->estado <> "Finalizado" && $servicio->ruta != null)
												<button class="btn btn-sm btn-sm" onclick="servicioMajorel2({{$servicio->id}})">Editar</button>
												@endif
		
												@if ($servicio->estado == "No vehiculo" || $servicio->estado == "Cancelado" || $servicio->estado == "Cancelado devuelto")
													<a href="/servicios/duplicar/{{$servicio->id}}" class="btn btn-sm btn-primary">Duplicar</a>
												@endif
										</div>
									</div>

									@endif
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
						<h4 class="modal-title">Confirmar devolución</h4>
						<button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <form id="formdevolver" action="/servicios/devolver" method="POST">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4">
                                <label for="justificacion" class="label-required">Justificación</label>                         
                            </div>
                            <div class="col-md-8">
                                <input type="text" name="justificacion" id="justificacion" class="form-control" required>	
                            </div>
                        </div>
					</div>
					<div class="modal-footer">
						<input type="hidden" name="servicio" id="servicio">
                        <input type="hidden" name="_token" value="{{csrf_token()}}">
						<button type="submit" class="btn btn-success">Continuar</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    </div>
                    </form>
			</div>
		</div>
    </div>

	<div id="servicioFinalizadoMajorel" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="Modal" aria-hidden="true">
		<div class="modal-dialog" style="min-width: 70%">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title">Editar Servicio Finalizado Majorel</h4>
					<button type="button" class="close" data-dismiss="modal">&times;</button>
				</div>
				<form id="formMajorel" action="/servicios/majorel/finalizado/actualizar" method="POST">
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
								<input type="datetime-local" name="fechaMajorel" id="fechaMajorel" class="form-control" readonly required>
							</div>
						</div>

						<div class="row form-group" id="editarDirecto">
							<div class="col-md-4">
								<label for="placaMajorel">Placa y conductor</label>                         
							</div>
							<div class="col-md-8">
								<input type="text" name="placaMajorel" id="placaMajorel" class="form-control placaComplete" readonly required>
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


	<div id="servicioMajorel2" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="Modal" aria-hidden="true">
		<div class="modal-dialog" style="min-width: 70%">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title">Editar Servicio Majorel</h4>
					<button type="button" class="close" data-dismiss="modal">&times;</button>
				</div>

				<form id="formMajorel" action="/servicios/majorel/finalizado/actualizar" method="POST">
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

@endsection
@section('script')
	<script type="text/javascript" src="/js/moment.min.js"></script>
	<script type="text/javascript" src="/js/daterangepicker.js"></script>
	<script>
		$(document).on('click', '.btn-danger', function(e){
            $("#servicio").val(e.target.value);

		});

		$(document).ready(function () {
			$("#fecha").daterangepicker({
				autoUpdateInput: false,
    			timePicker: true,
				timePicker24Hour: true,			
    			locale: {
					format: 'YYYY/MM/DD HH:mm',
          			cancelLabel: 'Clear'
     			}				
  			});

			@if(!empty($c11))
			  $("#fuente").val('{{$c11}}');
			@endif

			@if(!empty($c12))
			  $("#estado").val('{{$c12}}');
			@endif
		});

		$(".filt").keydown(function (event) {
    		var keypressed = event.keyCode || event.which;
    		if (keypressed == 13) {
        		$("#formfiltro").submit();
    		}
		});

		$("#fecha").on('apply.daterangepicker', function(ev, picker) {
      		$(this).val(picker.startDate.format('YYYY/MM/DD HH:mm') + ' - ' + picker.endDate.format('YYYY/MM/DD HH:mm'));
			$("#formfiltro").submit();
  		});

  		$("#fecha").on('cancel.daterangepicker', function(ev, picker) {
      		$(this).val('');
  		});
	
		function toexcel(){

			Swal.fire({
				title: '<strong>Exportando...</strong>',
				html:'<img src="/img/carga.gif" height="60 px" class="img-responsive" alt="Buscando">',
				showConfirmButton: false,
			});

			$.ajax({
				method: "POST",
				url: "/serviciosfinalizados/exportar",
				data: $("#formfiltro").serialize()
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

				filename = "Servicios finalizados.xlsx";
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
					text: textStatus
				});
			});
		}

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

		function servicioFinalizadoMajorel(servicio) {
			$("#idMajorel").val(servicio);
			$("#servicioFinalizadoMajorel").modal('show');
			sincro = 0;
			$.ajax({
				type: "get",
				url: "/servicios/majorel/finalizado/editar",
				data: {idservicio:servicio},
				dataType: "json"
			}).done(function (data) {  
				$("#bodypasajeros").empty();
					$("#fechaMajorel").val(data.fechaprogramada.replace(" ", "T").trim());
					$("#direccionMajorel").val(data.direccion.trim());
					if(data.asignacion == "Directo"){
						$("#placaMajorel").val((data.placa + "_" + data.cuentac.conductor.NOMBRE + "_" + data.cuentac.id).trim());
					}else{
						$("#placaMajorel").val(servicio);
					}
					let pasajeros = '';
						for (let key = 0; key < data.ruta.pasajeros.length; key++) {
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
						$("#bodypasajeros").append(pasajeros);


			}).fail(function () {  
			});
		}


		function servicioMajorel2(servicio) {
			$("#idMajorel").val(servicio);
			$("#servicioMajorel2").modal('show');
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
						pasajeros += '<td>' + data.ruta.pasajeros[key].nombre + '</td>';
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