@extends('layouts.logeado')

@if ($method == "post")
    @section('sub_title', 'Nueva Sucursal')
@else
    @section('sub_title', 'Editar Sucursal')
@endif

@section('sub_content')
	<div class="card">
		<div class="card-body">
			{{ Form::model($sucursal, ['route' => $route, 'method' => $method] ) }}
				{{ Form::hidden('id', null) }}
				<div class="form-group row {{ $errors->has('tercero') ? 'form-error': '' }}">
					{{ Form::label('tercero', 'Empresa', ['class' => 'label-required col-md-2']) }}
					<div class="col-md-10">
						@if ($method == "post")
							<select name="tercero" id="tercero" class="form-control" style="width: 50%">
								@foreach ($estaciones as $estacion)
									<option value="{{$estacion->TERCERO}}">{{$estacion->RAZON_SOCIAL}}</option>
								@endforeach
							</select>
						@else
							<select name="tercero" id="tercero" class="form-control" style="width: 50%" disabled>
								<option value="{{$sucursal->tercero->TERCERO}}">{{$sucursal->tercero->RAZON_SOCIAL}}</option>
							</select>
						@endif
						
					</div>
				</div>
				<div class="form-group row {{ $errors->has('nombre') ? 'form-error': '' }}">
						{{ Form::label('nombre', 'Nombre sucursal', ['class' => 'label-required col-md-2']) }}
						<div class="col-md-10">
							@if ($method == "post")
								{{ Form::text('nombre', null, ['required', 'class' => 'form-control', 'style' => 'width: 50%']) }}
							@else
								{{ Form::text('nombre', $sucursal->user->nombres, ['required', 'class' => 'form-control', 'style' => 'width: 50%']) }}
							@endif
							{!! $errors->first('nombre', '<p class="help-block">:message</p>') !!}
						</div>
                </div>

                <div class="form-group row {{ $errors->has('usuario') ? 'form-error': '' }}">
						{{ Form::label('usuario', 'Usuario', ['class' => 'label-required col-md-2']) }}
						<div class="col-md-10">
							@if ($method == "post")
								{{ Form::text('usuario', null, ['required', 'class' => 'form-control', 'style' => 'width: 50%']) }}
							@else
								{{ Form::text('usuario', $sucursal->user->usuario, ['required', 'disabled', 'class' => 'form-control', 'style' => 'width: 50%']) }}
							@endif
							{!! $errors->first('usuario', '<p class="help-block">:message</p>') !!}
						</div>
				</div>
				
				<div class="form-group row {{ $errors->has('password') ? 'form-error': '' }}">
					{{ Form::label('password', 'Contraseña', ['class' => 'label-required col-md-2']) }}
					<div class="col-md-10">
						@if ($method == "post")
							{{ Form::password('password', ['required', 'style' => 'width: 30%;height: 33px']) }}
						@else
							{{ Form::password('password', ['style' => 'width: 30%;height: 33px']) }}
						@endif
						<button class="btn-info btn-sm" type="button" onclick="mostrarpassword()"><span class="fa fa-eye-slash" id="eye"></span>
						{!! $errors->first('password', '<p class="help-block">:message</p>') !!}
					</div>
				</div>
                
                <div class="form-group row {{ $errors->has('estado') ? 'form-error': '' }}">
						{{ Form::label('estado', 'Estado', ['class' => 'label-required col-md-2']) }}
						<div class="col-md-10">
							<select name="estado" id="estado" class="form-control" style="width: 50%">
								@if ($method == "put")
									@if ($sucursal->user->estado == 1)
										<option value="1" selected>Activa</option>
										<option value="0">Inactiva</option>
									@else
										<option value="1">Activa</option>
										<option value="0" selected>Inactiva</option>
									@endif
								@else
									<option value="1">Activa</option>
									<option value="0">Inactiva</option>
								@endif                     
                            </select>
							{!! $errors->first('estado', '<p class="help-block">:message</p>') !!}
						</div>
				</div>
				<div class="form-group text-center">
					{!! Form::button('Enviar', ['type' => 'submit', 'class' => 'btn btn-dark']) !!}
				</div>
			{{ Form::close() }}
		</div>
	</div>
@endsection
@section('script')
	<script>
		function mostrarpassword() {
    		var cambio = document.getElementById("password");
    		if (cambio.type == "password") {
      			cambio.type = "text";
      			$('#eye').removeClass('fa fa-eye-slash').addClass('fa fa-eye');
    		} else {
      			cambio.type = "password";
      			$('#eye').removeClass('fa fa-eye').addClass('fa fa-eye-slash');
    		}
  		}
	</script>
@endsection