@extends('layouts.logeado')

@section('sub_title', 'Editar agencia')

@section('sub_content')
	<div class="card">
		<div class="card-body">
			{{ Form::model($agencia, ['route' => $route, 'method' => $method] ) }}
				{{ Form::hidden('id', null) }}

				<div class="form-group row {{ $errors->has('NOMBRE') ? 'form-error': '' }}">
					{{ Form::label('NOMBRE', 'Razón social', ['class' => 'label-required col-md-2']) }}
					<div class="col-md-10">
						{{ Form::text('NOMBRE', null, ['readonly', 'class' => 'form-control']) }}
						{!! $errors->first('NOMBRE', '<p class="help-block">:message</p>') !!}
					</div>
				</div>

				<div class="form-group row {{ $errors->has('NRO_IDENTIFICACION') ? 'form-error': '' }}">
					{{ Form::label('NRO_IDENTIFICACION', 'NIT', ['class' => 'label-required col-md-2']) }}
					<div class="col-md-10">
						{{ Form::text('NRO_IDENTIFICACION', null, ['readonly', 'class' => 'form-control']) }}
						{!! $errors->first('NRO_IDENTIFICACION', '<p class="help-block">:message</p>') !!}
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
				<div class="form-group text-center">
					{!! Form::button('Enviar', ['type' => 'submit', 'class' => 'btn btn-dark']) !!}
				</div>
			{{ Form::close() }}
		</div>
	</div>
@endsection