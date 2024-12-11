@extends('layouts.logeado')

@section('sub_title', 'Actualizar conductor')

@section('sub_content')
<div class="card">
	<div class="card-body">
		{{ Form::model($conductor, ['route' => $route, 'method' => $method, 'enctype' => 'multipart/form-data'] ) }}
		{{ Form::hidden('id', null) }}

		<div class="row">
			<div class="col-md-5" style="text-align: center">
				<p><b>{{$conductor->NOMBRE}}</b></p>		
				@if ($conductor->cuentac->foto == null)
					<img alt="Foto conductor" id="imgfoto" src="/img/foto.png" width="30%" height="40%"><br>
				@else
					<img alt="Foto conductor" id="imgfoto" src="data:image/*;base64, {{$conductor->cuentac->foto}}" width="30%" height="40%"><br>
				@endif
				<label for="foto"><h4><span class="badge badge-info">Cambiar foto</span></h4></label>
				<input type="file" name="foto" id="foto" style="display: none" accept="image/x-png,image/gif,image/jpeg">
				<br>
				<p><i class="fa fa-envelope" aria-hidden="true"></i> {{$conductor->EMAIL}}</p>
				<p style="margin-bottom: 0"><i class="fa fa-mobile" aria-hidden="true"></i> {{$conductor->CELULAR}}</p>
				@for ($i = 0; $i < $amarillas; $i++)
					<span style="color: yellow; font-size: 30pt">★</span>
				@endfor
				@for ($i = 0; $i < $grises; $i++)
					<span style="color: gray; font-size: 30pt">★</span>
				@endfor
			</div>
			<div class="col-md-7">
				<h3>Datos básicos</h3>
				<hr>
				<div class="row">
					<div class="col-md-6">
							<label for="documento">Identificación</label><br>
							<p id="documento">{{$conductor->NUMERO_IDENTIFICACION}}</p>
					</div>
					<div class="col-md-6">
							<label for="genero">Género</label><br>
							<p id="genero">@if ($conductor->SEXO == 2)
								Masculino
							@else
								Femenino
							@endif</p>
					</div>
				</div>
				<div class="row">
					<div class="col-md-6">
						<label for="nombres">Nombres</label><br>
						<p id="nombres">{{$conductor->PRIMER_NOMBRE}} {{$conductor->SEGUNDO_NOMBRE}}</p> 
					</div>
					<div class="col-md-6">
						<label for="apellidos">Apellidos</label><br>
						<p id="apellidos">{{$conductor->PRIMER_APELLIDO}} {{$conductor->SEGUNDO_APELLIDO}}</p>
					</div>				
				</div>
				<div class="row">
					<div class="col-md-6">
						<label for="licencia">Licencia de conducir</label><br>
						<p id="licencia">{{$conductor->LICENCIA}}</p> 
					</div>
					<div class="col-md-6">
						<label for="fechalicencia">Vencimiento licencia</label><br>
						<p id="fechalicencia">{{$conductor->VENCIMIENTO_LICENCIA}}</p>
					</div>	
				</div>
			</div>
		</div>

			<h3>Datos de localización</h3>
			<hr>
			<div class="form-group row">
					{{ Form::label('DIRECCION', 'Dirección', ['class' => 'label-required col-md-2']) }}
					<div class="col-md-10">
						{{ Form::text('DIRECCION', null, ['required', 'class' => 'form-control', 'style' => 'width:50%']) }}
					</div>
				</div>
				<div class="form-group row">
					{{ Form::label('TELEFONO', 'Teléfono', ['class' => 'col-md-2']) }}
					<div class="col-md-10">
						{{ Form::text('TELEFONO', null, ['class' => 'form-control', 'style' => 'width:50%']) }}
					</div>
				</div>
				<div class="form-group row">
						{{ Form::label('CELULAR', 'Celular', ['class' => 'col-md-2']) }}
						<div class="col-md-10">
							{{ Form::text('CELULAR', null, ['class' => 'form-control', 'style' => 'width:50%']) }}
						</div>
					</div>
				<div class="form-group row">
						{{ Form::label('EMAIL', 'E-mail', ['class' => 'col-md-2']) }}
						<div class="col-md-10">
							{{ Form::email('EMAIL', null, ['class' => 'form-control', 'style' => 'width:50%']) }}
						</div>
				</div>
				<div class="form-group row">
					{{ Form::label('exento', 'Exento cobro al tomar servicio', ['class' => 'col-md-2']) }}
					<div class="col-md-4">
						@if ($conductor->cuentac->exento == 0)
							<input type="checkbox" name="exento" id="exento" class="form-control">
						@else
							<input type="checkbox" name="exento" id="exento" class="form-control" checked>
						@endif
					</div>
				</div>

				<div class="form-group row">
					{{ Form::label('istransacciones', 'Bloquear transacciones', ['class' => 'col-md-2']) }}
					<div class="col-md-4">
						@if ($conductor->cuentac->istransacciones == 0)
							<input type="checkbox" name="istransacciones" id="istransacciones" class="form-control">
						@else
							<input type="checkbox" name="istransacciones" id="istransacciones" class="form-control" checked>
						@endif
					</div>
				</div>
		
				<div class="form-group row" id="divmotivotransacciones" ">
					{{ Form::label('motivobloqueo', 'Motivo Bloqueo', ['class' => 'col-md-2 col-form-label']) }}
					<div class="col-md-10">
						<input type="text" name="motivobloqueo" id="motivobloqueo" class="form-control" value="{{ old('motivobloqueo', $conductor->cuentac->motivobloqueo) }}">
					</div>
				</div>
				
			@if ($usuario->roles_id == 1 || $usuario->modulos[1]->pivot->editar == 1)
			<h3>Perfil de acceso</h3>
			<hr>
			<div class="row">
				<div class="col-md-4">
						<label for="usuario">Usuario</label><br>
						<input type="text" id="usuario" class="form-control" value="{{$conductor->NUMERO_IDENTIFICACION}}" readonly>
				</div>
				<div class="col-md-4">
						<label for="password">Contraseña</label><br>
						<input type="password" name="password" id="password" class="form-control"><br>
				</div>
			</div>		

			<div class="row">
				<div class="col-md-4">
						<label for="estado" class="label label-required">Estado</label><br>
						<select name="ESTADO" id="ESTADO" class="form-control">
							<option value="Activo">Activo</option>
							@if ($conductor->cuentac->estado == "Bloqueado")
								<option value="Bloqueado" selected>Bloqueado</option>
							@else
								<option value="Bloqueado">Bloqueado</option>
							@endif
							@if ($conductor->cuentac->estado == "Inactivo")
								<option value="Inactivo" selected>Inactivo</option>
							@else
								<option value="Inactivo">Inactivo</option>
							@endif					
						</select>
				</div>
				<div class="col-md-4">
						@if ($conductor->cuentac->estado == "Bloqueado")
							<label for="desbloqueo" class="label">Motivo y Fecha de Desbloqueo</label><br>
							<p style="font-size: 14pt; color: indianred">{{ $conductor->cuentac->razon }},	{{ $conductor->cuentac->fechabloqueo }}</p>
						@endif
				</div>
			</div>
						
			<br>
			<div id="divbloqueado" style="display: none">
				<div class="form-group row">
					{{ Form::label('sanciones', 'Sanciones', ['class' => 'label-required col-md-2']) }}
					<div class="col-md-6">
						<select name="sanciones" id="sanciones" class="form-control">
							@foreach ($sanciones as $sancion)
								<option value="{{$sancion->id}}">{{$sancion->descripcion}} ({{$sancion->cantidad}} {{$sancion->unidad}})</option>
							@endforeach
						</select>
					</div>
					@if (Auth::user()->roles_id == 1)
						<div class="col-md-2">
							<button type="button" class="btn btn-sm btn-dark open-modal" data-toggle="modal" data-target="#Modal"><i class="fa fa-plus-circle" aria-hidden="true"></i> Nueva sanción</button>
						</div>
					@endif				
				</div>
			</div>

			<div id="divinactivo" style="display: none">
				<div class="form-group row">
					{{ Form::label('motivo', 'Motivo', ['class' => 'label-required col-md-2']) }}
					<div class="col-md-6">
						<input type="text" name="motivo" id="motivo" class="form-control">
					</div>
				</div>
			</div>
		@endif
		
		<div class="form-group text-center">
			{!! Form::button('Enviar', ['type' => 'submit', 'class' => 'btn btn-dark']) !!}
		</div>
		{{ Form::close() }}

		<div class="row">
			<div class="col-md-6">
				<h3>Historial de Suspensiones</h3>
				<hr>
				<table class="table table-bordered">
					<thead>
						<tr>
							<th>Sanción</th>
							<th>Fecha bloqueo</th>
							<th>Operador del bloqueo</th>
							<th>Feche desbloqueo</th>
							<th>Operador del desbloqueo</th>				
						</tr>
					</thead>				
					<tbody>
						@foreach ($conductor->cuentac->suspensiones as $suspension)
							<tr>
								<td>{{$suspension->sancion->descripcion}} ( {{$suspension->sancion->cantidad}} {{$suspension->sancion->unidad}} )</td>
								<td>{{$suspension->fechabloqueo}}</td>
								<td>{{$suspension->operador1->nombres}}</td>
								<td>{{$suspension->fechadesbloqueo}}</td>
								<td>
									@if ($conductor->cuentac->estado != "Bloqueado")
										@if ($suspension->operador2 == null)
											CRM
										@else
											{{$suspension->operador2->nombres}}
										@endif
									@endif
								</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>
			<div class="col-md-6">
				<h3>Historial de Inactivaciones</h3>
				<hr>
				<table class="table table-bordered">
					<thead>
						<tr>
							<th>Fecha Inactivación</th>
							<th>Motivo</th>
							<th>Usuario que inactivó</th>
							<th>Fecha de reactivación</th>
							<th>Usuario que reactivó</th>			
						</tr>
					</thead>				
					<tbody>
						@foreach ($conductor->cuentac->inactivaciones as $inactivacion)
							<tr>
								<td>{{$inactivacion->fecha}} </td>
								<td>{{$inactivacion->motivo}}</td>
								<td>{{$inactivacion->operador1->nombres}}</td>
								<td>{{$inactivacion->reactivacion}}</td>
								<td>@if ($inactivacion->operador2 != null)
										{{$inactivacion->operador2->nombres}}
									@endif
								</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
@endsection
@if (Auth::user()->roles_id == 1)
	@section('modal')
		<div id="Modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="Modal" aria-hidden="true">
				<div class="modal-dialog" style="min-width: 50%">
					<div class="modal-content">
						<div class="modal-header">
							<h4 class="modal-title">Nueva sanción</h4>
							<button type="button" class="close" data-dismiss="modal">&times;</button>
						</div>
						<form action="/sanciones/nueva" method="POST">
						<div class="modal-body">
							<div class="row">
								<div class="col-md-3">
									<label for="descripcion" class="label-required">Descripción</label>                         
								</div>
								<div class="col-md-9">
									<input type="text" name="descripcion" id="descripcion" class="form-control" required>	
								</div>
							</div>
							<br>
							<div class="row">
								<div class="col-md-3">
									<label for="unidad" class="label-required">Unidad de tiempo</label>                         
								</div>
								<div class="col-md-9">
									<select name="unidad" id="unidad" class="form-control">
										<option value="Horas">Horas</option>
										<option value="Dias">Dias</option>
										<option value="Semanas">Semanas</option>
									</select>	
								</div>
							</div>
							<br>		
							<div class="row">
									<div class="col-md-3">
										<label for="cantidad" class="label-required">Cantidad</label>                         
									</div>
									<div class="col-md-9">
										<input type="number" name="cantidad" id="cantidad" class="form-control" required>	
									</div>
								</div>
						</div>
						<div class="modal-footer">
							<input type="hidden" name="conductor" value="{{$conductor->cuentac->id}}">
							<input type="hidden" name="_token" value="{{csrf_token()}}">
							<button type="submit" class="btn btn-success">Guardar</button>
							<button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
						</div>
						</form>
				</div>
			</div>
		</div>
		</div>
	@endsection
@endif

@if ($conductor->cuentac->estado != "Bloqueado")
	@section('script')
	
	<script>
		document.addEventListener('DOMContentLoaded', function () {
			var checkbox = document.getElementById('istransacciones');
			var motivoDiv = document.getElementById('divmotivotransacciones');
	
			function toggleMotivo() {
				if (checkbox.checked) {
					motivoDiv.style.display = 'block';
				} else {
					motivoDiv.style.display = 'none';
				}
			}
	
			checkbox.addEventListener('change', toggleMotivo);
	
			// Initialize visibility on page load
			toggleMotivo();
		});

		function readURL(input) {
			if (input.files && input.files[0]) {
				var reader = new FileReader();
				reader.onload = function(e) {
					$('#imgfoto').attr('src', e.target.result);
				}
				reader.readAsDataURL(input.files[0]);
			}
		}

		$("#ESTADO").change(function () {
			var estado = $(this).val();

			if(estado == "Bloqueado"){
				$("#divbloqueado").css("display", "block");
				$("#divbloqueado :input").attr('disabled', false);
				$("#divinactivo").css("display", "none");
				$("#divinactivo :input").attr('disabled', true);
			}else if(estado == "Inactivo"){
				$("#divinactivo").css("display", "block");
				$("#divinactivo :input").attr('disabled', false);
				$("#divbloqueado").css("display", "none");
				$("#divbloqueado :input").attr('disabled', true);
			}else{
				$("#divbloqueado").css("display", "none");
				$("#divbloqueado :input").attr('disabled', true);
				$("#divinactivo").css("display", "none");
				$("#divinactivo :input").attr('disabled', true);
			}
		});

		$("#foto").change(function () {	
			readURL(this);
		});
	</script>
		
	@endsection
@endif

@section('sincro')
		<script src="{{ mix('/js/sincronizacion.js') }}"></script>
@endsection
