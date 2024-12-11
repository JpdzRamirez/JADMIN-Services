@extends('layouts.logeado')

@if ($method == "post")
@section('sub_title', 'Nueva flota')
@else
@section('sub_title', 'Editar flota')
@endif

@section('sub_content')
	<div class="card">
		<div class="card-body">
			{{ Form::model($flota, ['route' => $route, 'method' => $method] ) }}
				{{ Form::hidden('id', null) }}
				<div class="form-group row {{ $errors->has('descripcion') ? 'form-error': '' }}">
					{{ Form::label('descripcion', 'Descripción', ['class' => 'label-required col-md-2']) }}
					<div class="col-md-10">
						{{ Form::text('descripcion', null, ['required', 'class' => 'form-control', 'style' => 'width:50%']) }}
						{!! $errors->first('descripcion', '<p class="help-block">:message</p>') !!}
					</div>
				</div>
				<div class="form-group row {{ $errors->has('codigo') ? 'form-error': '' }}">
						{{ Form::label('codigo', 'Código interno', ['class' => 'label-required col-md-2']) }}
						<div class="col-md-10">
							{{ Form::number('codigo', null, ['required', 'class' => 'form-control']) }}
							{!! $errors->first('codigo', '<p class="help-block">:message</p>') !!}
						</div>
				</div>
				<div class="form-group text-center">
					{!! Form::button('Enviar', ['type' => 'submit', 'class' => 'btn btn-dark']) !!}
				</div>
			{{ Form::close() }}
		</div>
	</div>
@endsection
@section('sincro')
		<script src="{{ mix('/js/sincronizacion.js') }}"></script>
@endsection