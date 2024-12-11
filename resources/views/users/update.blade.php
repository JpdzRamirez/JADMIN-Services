@extends('layouts.logeado')

@section('sub_title', 'Actualizar datos')

@section('sub_content')
<div class="card">
	<div class="card-body">
		{{ Form::model($user, ['route' => $route, 'method' => $method, 'id' => 'formuser'] ) }}
        {{ Form::hidden('id', null) }}
        <div class="form-group row {{ $errors->has('password') ? 'form-error': '' }}">
            {{ Form::label('usuario', 'Usuario', ['class' => 'col-md-2']) }}
                <div class="col-md-10">
                    {{ Form::text('usuario', null, ['readonly', 'class' => 'form-control', 'style' => 'width:80%']) }}
                    {!! $errors->first('usuario', '<p class="help-block">:message</p>') !!}
                </div>
            </div>
		<div class="form-group row {{ $errors->has('password') ? 'form-error': '' }}">
			{{ Form::label('password', 'Contraseña', ['class' => 'col-md-2']) }}
			<div class="col-md-10 tip">
				<input type="password" name="password" id="password" class="form-control" style="width: 50%;display: inline">
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
			{{ Form::label('password2', 'Confirmar contraseña', ['class' => 'col-md-2']) }}
			<div class="col-md-10">
				<input type="password" name="password2" id="password2" class="form-control" style="width: 50%;display: inline">
				<button class="btn-info btn-sm" type="button" onclick="mostrarpassword2()"><span class="fa fa-eye-slash" id="eye2"></span>
				{!! $errors->first('password2', '<p class="help-block">:message</p>') !!}
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
	<script src="{{ mix('/js/formsuser.js') }}"></script>
	<script>
		var pasar;
		$(document).ready(function () {

			$('input[type=password]').keyup(function() {
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
@endsection
