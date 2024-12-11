@extends('layouts.logeado')

@if ($method == "post")
    @section('sub_title', 'Nuevo usuario')
@else
    @section('sub_title', 'Actualizar usuario')
@endif

@section('sub_content')
<div class="card">
	<div class="card-body" id="cardb">
			@if($errors->first('sql') != null)
			<div class="alert alert-danger" style="margin:5px 0">
				<h6>{{$errors->first('sql')}}</h6>
			</div>				
			@endif
		{{ Form::model($user, ['route' => $route, 'method' => $method, 'id' => 'formuser'] ) }}
        {{ Form::hidden('id', null) }}
        @if ($method == "post")
            <div class="form-group row {{ $errors->has('nombres') ? 'form-error': '' }}">
                {{ Form::label('nombres', 'Nombres', ['class' => 'label-required col-md-2']) }}
                <div class="col-md-10">
                    {{ Form::text('nombres', null, ['required', 'class' => 'form-control', 'style' => 'width:50%']) }}
                    {!! $errors->first('nombres', '<p class="help-block">:message</p>') !!}
                </div>
			</div>
			
			<div class="form-group row {{ $errors->has('identificacion') ? 'form-error': '' }}">
                {{ Form::label('identificacion', 'Identificación', ['class' => 'label-required col-md-2']) }}
                <div class="col-md-10">
                    {{ Form::text('identificacion', null, ['required', 'class' => 'form-control', 'style' => 'width:50%']) }}
                    {!! $errors->first('identificacion', '<p class="help-block">:message</p>') !!}
                </div>
            </div>
        @endif
		
		<div class="form-group row {{ $errors->has('usuario') ? 'form-error': '' }}">
			{{ Form::label('usuario', 'Usuario', ['class' => 'label-required col-md-2']) }}
			<div class="col-md-10">
				{{ Form::text('usuario', null, ['required', 'class' => 'form-control', 'style' => 'width:50%']) }}
				{!! $errors->first('usuario', '<p class="help-block">:message</p>') !!}
			</div>
		</div>

		<div class="form-group row {{ $errors->has('password') ? 'form-error': '' }}">
			{{ Form::label('password', 'Contraseña', ['class' => 'label-required col-md-2']) }}
			<div class="col-md-10 tip">
				{{ Form::password('password', ['class'=>'form-control', 'style'=>'display:inline; width:50%']) }}
				<button class="btn-info btn-sm" type="button" onclick="mostrarpassword()"><span class="fa fa-eye-slash" id="eye"></span></button>
				<div id="toolinfo" class="tiptext">
					<h6 style="color: black !important">La contraseña debe tener los siguientes requisitos:</h6>
					<ul style="text-align: left">
						<li id="letter">Al menos <strong>una letra minúscula</strong></li>
						<li id="capital">Al menos <strong>una letra mayúscula</strong></li>
						<li id="number">Al menos <strong>un número</strong></li>
						<li id="length">Por lo menos <strong>8 caracteres</strong></li>
					</ul>
				</div>				
			</div>
		</div>
		<div class="form-group row {{ $errors->has('password2') ? 'form-error': '' }}">
			{{ Form::label('password2', 'Confirmar contraseña', ['class' => 'label-required col-md-2']) }}
			<div class="col-md-10">
				{{ Form::password('password2', ['class'=>'form-control', 'style'=>'display:inline; width:50%']) }}
				<button class="btn-info btn-sm" type="button" onclick="mostrarpassword2()"><span class="fa fa-eye-slash" id="eye2"></span>
				
			</div>
		</div>

		<div class="form-group row {{ $errors->has('estado') ? 'form-error': '' }}">
			{{ Form::label('estado', 'Estado', ['class' => 'label-required col-md-2']) }}
			<div class="col-md-10">
				<select class="form-control" name="estado" id="estado" style="width: 50%" required>
					<option value="1">Activo</option>
					<option value="0">Inactivo</option>
				</select>
				{!! $errors->first('estado', '<p class="help-block">:message</p>') !!}
			</div>
		</div>

		@if ($method == "put")
			@if ($user->roles_id == 1 || $user->roles_id == 2)
				<div class="form-group row {{ $errors->has('roles_id') ? 'form-error': '' }}">
					{{ Form::label('roles_id', 'Rol', ['class' => 'label-required col-md-2']) }}
					<div class="col-md-10">
						<select class="form-control" name="roles_id" id="roles_id" style="width: 50%" required>
							<option value="1">Administrador</option>
							<option value="2">Usuario</option>
						</select>
						{!! $errors->first('roles_id', '<p class="help-block">:message</p>') !!}
					</div>
				</div>
			@endif
		@else
			<div class="form-group row {{ $errors->has('roles_id') ? 'form-error': '' }}">
				{{ Form::label('roles_id', 'Rol', ['class' => 'label-required col-md-2']) }}
				<div class="col-md-10">
					<select class="form-control" name="roles_id" id="roles_id" style="width: 50%" required>
						<option value="1">Administrador</option>
						<option value="2" selected>Usuario</option>
					</select>
					{!! $errors->first('roles_id', '<p class="help-block">:message</p>') !!}
				</div>
			</div>
		@endif

		<div class="form-group text-center">
			{!! Form::button('Enviar', ['type' => 'submit', 'class' => 'btn btn-dark']) !!}
		</div>
		{{ Form::close() }}

	</div>
</div>
@endsection

@section('script')
	<script src="{{ mix('/js/formsuser.js') }}"></script>
		@if ($method == 'put')
		<script>
			var pasar;
			$(document).ready(function(){			
				$('#estado option[value="{{$user->estado}}"]').attr("selected", "selected");
				$('#roles_id option[value="{{$user->roles_id}}"]').attr("selected", "selected");

				$('#password').keyup(function() {
					var pswd = $(this).val();
					pasar = 0;

					if ( pswd.length > 8 ) {
						$('#length').css('color', 'green');
						pasar++;
					} else {
						$('#length').css('color', 'red');
					}

					if ( pswd.match(/[a-z]/) ) {
						$('#letter').css('color', 'green');
						pasar++;
					} else {
						$('#letter').css('color', 'red');
					}

					if ( pswd.match(/[A-Z]/) ) {
						$('#capital').css('color', 'green');
						pasar++;
					} else {
						$('#capital').css('color', 'red');
					}

					if ( pswd.match(/\d/) ) {
						$('#number').css('color', 'green');
						pasar++;
					} else {
						$('#number').css('color', 'red');
					}
				});
			});
			
		</script>
		@endif
@endsection
