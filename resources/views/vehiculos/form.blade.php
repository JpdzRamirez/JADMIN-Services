@extends('layouts.logeado')

@if ($method == "post")
@section('sub_title', 'Nuevo taxi')
@else
@section('sub_title', 'Editar taxi')
@endif

@section('sub_content')
	<div class="card">
		<div class="card-body">
			{{ Form::model($vehiculo, ['route' => $route, 'method' => $method] ) }}
				{{ Form::hidden('id', null) }}
				<div class="form-group row {{ $errors->has('placa') ? 'form-error': '' }}">
					{{ Form::label('placa', 'Placa', ['class' => 'label-required col-md-2']) }}
					<div class="col-md-10">
						{{ Form::text('placa', null, ['required', 'maxlength' => '7', 'class' => 'form-control']) }}
						{!! $errors->first('placa', '<p class="help-block">:message</p>') !!}
					</div>
				</div>
				<div class="form-group row {{ $errors->has('interno') ? 'form-error': '' }}">
						{{ Form::label('interno', 'Número interno', ['class' => 'label-required col-md-2']) }}
						<div class="col-md-10">
							{{ Form::number('interno', null, ['required', 'class' => 'form-control']) }}
							{!! $errors->first('interno', '<p class="help-block">:message</p>') !!}
						</div>
				</div>
				<div class="form-group row {{ $errors->has('propietarios_id') ? 'form-error': '' }}">
					{{ Form::label('propietarios_id', 'Propietario', ['class' => 'label-required col-md-2']) }}
					<div class="col-md-10">
						<select name="propietarios_id" id="propietarios_id" class="form-control" required>
							@foreach ($propietarios as $propietario)
								<option value="{{ $propietario->id }}">{{ $propietario->numeroid }}</option>
							@endforeach
						</select>
						{!! $errors->first('propietarios_id', '<p class="help-block">:message</p>') !!}
					</div>
				</div>
				<div class="form-group row {{ $errors->has('conductores_id') ? 'form-error': '' }}">
						{{ Form::label('conductores_id', 'Conductor', ['class' => 'label-required col-md-2']) }}
						<div class="col-md-10">
							<select name="conductores_id" id="conductores_id" class="form-control" required>
								<option value="0">Sin conductor</option>
								@if ($method == "put")
									<option value="{{ $vehiculo->conductor->id}}">{{ $vehiculo->conductor->numeroid}}</option>
								@endif
								@foreach ($conductores as $conductor)
									<option value="{{ $conductor->id }}">{{ $conductor->numeroid }}</option>
								@endforeach
							</select>
							{!! $errors->first('conductores_id', '<p class="help-block">:message</p>') !!}
						</div>
				</div>
				<div class="form-group row {{ $errors->has('flotas_id') ? 'form-error': '' }}">
						{{ Form::label('flotas_id', 'Flota', ['class' => 'label-required col-md-2']) }}
						<div class="col-md-10">
							<select name="flotas_id" id="flotas_id" class="form-control" required>
								<option value="0">Sin flota</option>
								@foreach ($flotas as $flota)
									<option value="{{ $flota->id }}">{{ $flota->nombre }}</option>
								@endforeach
							</select>
							{!! $errors->first('flotas_id', '<p class="help-block">:message</p>') !!}
						</div>
				</div>
				<div class="form-group text-center">
					{!! Form::button('Enviar', ['type' => 'submit', 'class' => 'btn btn-dark']) !!}
				</div>
			{{ Form::close() }}
		</div>
	</div>
@endsection
@if ($method == "put")
	@section('script')
		<script>
			
			$(document).ready(function () {
				
				$('#propietarios_id option[value="{{$vehiculo->propietarios_id}}"]').attr('selected', true);
				$('#conductores_id option[value="{{$vehiculo->conductores_id}}"]').attr('selected', true);
				$('#flotas_id option[value="{{$vehiculo->flotas_id}}"]').attr('selected', true);

			});
		</script>
	@endsection
@endif