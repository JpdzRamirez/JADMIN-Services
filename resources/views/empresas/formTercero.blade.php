@extends('layouts.logeado')

@if ($metodo == 'post')
    @section('sub_title', 'Nuevo usuario para ' . $tercero->RAZON_SOCIAL)
@else
    @section('sub_title', 'Editar usuario de '. $tercero->RAZON_SOCIAL)
@endif

@section('sub_content')
	<div class="card">
		<div class="card-body">
			{{ Form::model($tercero, ['route' => $route, 'method' => 'post', 'id' => 'formtercero'] ) }}
				{{ Form::hidden('id', null) }}

				<div class="form-group row {{ $errors->has('RAZON_SOCIAL') ? 'form-error': '' }}">
					{{ Form::label('RAZON_SOCIAL', 'Razon social', ['class' => 'label-required col-md-2']) }}
					<div class="col-md-10">
						{{ Form::text('RAZON_SOCIAL', null, ['readonly', 'class' => 'form-control']) }}
						{!! $errors->first('RAZON_SOCIAL', '<p class="help-block">:message</p>') !!}
					</div>
				</div>

				<div class="form-group row {{ $errors->has('DIRECCION') ? 'form-error': '' }}">
						{{ Form::label('DIRECCION', 'Dirección', ['class' => 'label-required col-md-2']) }}
						<div class="col-md-10">
							{{ Form::text('DIRECCION', null, ['required', 'class' => 'form-control']) }}
							{!! $errors->first('DIRECCION', '<p class="help-block">:message</p>') !!}
						</div>
				</div>
				<div class="form-group row {{ $errors->has('TELEFONO') ? 'form-error': '' }}">
						{{ Form::label('TELEFONO', 'Teléfono', ['class' => 'label col-md-2']) }}
						<div class="col-md-10">
							{{ Form::text('TELEFONO', null, ['class' => 'form-control']) }}
							{!! $errors->first('TELEFONO', '<p class="help-block">:message</p>') !!}
						</div>
				</div>
				<div class="form-group row {{ $errors->has('EMAIL') ? 'form-error': '' }}">
						{{ Form::label('EMAIL', 'Email', ['class' => 'label col-md-2']) }}
						<div class="col-md-10">
							{{ Form::email('EMAIL', null, ['class' => 'form-control']) }}
							{!! $errors->first('EMAIL', '<p class="help-block">:message</p>') !!}
						</div>
                </div>
                <div class="form-group row {{ $errors->has('NRO_IDENTIFICACION') ? 'form-error': '' }}">
					{{ Form::label('NRO_IDENTIFICACION', 'Usuario', ['class' => 'label-required col-md-2']) }}
					<div class="col-md-10">
						{{ Form::text('NRO_IDENTIFICACION', null, ['readonly', 'class' => 'form-control']) }}
						{!! $errors->first('NRO_IDENTIFICACION', '<p class="help-block">:message</p>') !!}
					</div>
				</div>
                <div class="form-group row {{ $errors->has('password') ? 'form-error': '' }}">
                    {{ Form::label('password', 'Contraseña', ['class' => 'label-required col-md-2']) }}
                    <div class="col-md-10 tip">
                        @if ($metodo == 'post')
                            {{ Form::password('password', ['class'=>'form-control', 'style'=>'display:inline; width:50%', 'required']) }}
                        @else
                            {{ Form::password('password', ['class'=>'form-control', 'style'=>'display:inline; width:50%']) }}
                        @endif
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
                        @if ($metodo == 'post')
                            {{ Form::password('password2', ['class'=>'form-control', 'style'=>'display:inline; width:50%', 'required']) }}
                        @else
                            {{ Form::password('password2', ['class'=>'form-control', 'style'=>'display:inline; width:50%']) }}
                        @endif
                        <button class="btn-info btn-sm" type="button" onclick="mostrarpassword2()"><span class="fa fa-eye-slash" id="eye2"></span>
                        
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

        $("#formtercero").submit(function (ev) {
            var pass1 = $("#password").val();
            var pass2 = $("#password2").val()

            if(pass1 != pass2){
                ev.preventDefault();
                $("#load").hide();
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Las contraseñas no coinciden'
                });
            }else{
                if (pasar != 4) {
                    ev.preventDefault();
                    $("#load").hide();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'La contraseña no cumple con los requisitos minimos'
                    });
                }                  
            }        
        });
    </script>
@endsection