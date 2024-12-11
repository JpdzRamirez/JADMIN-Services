@extends('layouts.logeado')

@if ($method == "post")
    @section('sub_title', 'Nuevo pasajero')
@else
    @section('sub_title', 'Actualizar pasajero: ' . $pasajero->identificacion)
@endif

@section('sub_content')
<div class="card">
	<div class="card-body" id="cardb">
			@if($errors->first('sql') != null)
			<div class="alert alert-danger" style="margin:5px 0">
				<h6>{{$errors->first('sql')}}</h6>
			</div>				
			@endif
		{{ Form::model($pasajero, ['route' => $route, 'method' => $method] ) }}
        {{ Form::hidden('id', null) }}

            <div class="form-group row">
                {{ Form::label('identificacion', 'Identificación', ['class' => 'label-required col-md-2']) }}
                <div class="col-md-10">
                    @if ($method == "post")
                        {{ Form::text('identificacion', null, ['required', 'class' => 'form-control', 'style' => 'width:50%']) }}
                    @else
                        {{ Form::text('identificacion', null, ['readonly', 'class' => 'form-control', 'style' => 'width:50%']) }}
                    @endif
                </div>
            </div>

            <div class="form-group row">
                {{ Form::label('nombres', 'Nombres', ['class' => 'label-required col-md-2']) }}
                <div class="col-md-10">
                    @if ($method == "post")
                        {{ Form::text('nombres', null, ['required', 'class' => 'form-control', 'style' => 'width:50%']) }}
                    @else
                        {{ Form::text('nombres', null, ['readonly', 'class' => 'form-control', 'style' => 'width:50%']) }}  
                    @endif
                </div>
			</div>
			
			<div class="form-group row">
                {{ Form::label('apellidos', 'Apellidos', ['class' => 'label-required col-md-2']) }}
                <div class="col-md-10">
                    @if ($method == "post")
                        {{ Form::text('apellidos', null, ['required', 'class' => 'form-control', 'style' => 'width:50%']) }}
                    @else
                        {{ Form::text('apellidos', null, ['readonly', 'class' => 'form-control', 'style' => 'width:50%']) }}  
                    @endif                
                </div>
			</div>

        <div class="form-group row">
			{{ Form::label('tipo', 'Tipo de Pasajero', ['class' => 'label-required col-md-2']) }}
			<div class="col-md-10">
				{!! Form::select("tipo", ["Tripulación" => "Tripulación", "Tierra"=>"Tierra"], $pasajero->tipo, ['class'=>'form-control', 'style' => 'width:50%']) !!}
			</div>
		</div>
		
		<div class="form-group row">
			{{ Form::label('centrocosto', 'Centro de costo', ['class' => 'label-required col-md-2']) }}
			<div class="col-md-10">
				{{ Form::text('centrocosto', null, ['required', 'class' => 'form-control', 'style' => 'width:50%']) }}
			</div>
		</div>

		<div class="form-group row">
			{{ Form::label('celular', 'Celular', ['class' => 'col-md-2']) }}
			<div class="col-md-10">
				{{ Form::text('celular', null, ['class' => 'form-control', 'style' => 'width:50%']) }}
			</div>
		</div>

		<div class="form-group row">
			{{ Form::label('zona', 'Zona', ['class' => 'col-md-2']) }}
			<div class="col-md-10">
				{!! Form::select("zona", [""=>"","Bucaramanga"=>"Bucaramanga", "Floridablanca"=>"Floridablanca", "Girón"=>"Girón", "Piedecuesta"=>"Piedecuesta", "Lebrija"=>"Lebrija"], $pasajero->zona, ['class'=>'form-control', 'style' => 'width:50%']) !!}
			</div>
		</div>

		<div class="form-group row">
			{{ Form::label('direccion', 'Dirección', ['class' => 'col-md-2']) }}
			<div class="col-md-10">
				{{ Form::text('direccion', null, ['class' => 'form-control', 'style' => 'width:50%']) }}
			</div>
		</div>

		<div class="form-group row">
			{{ Form::label('complemento', 'Complemento dirección', ['class' => 'col-md-2']) }}
			<div class="col-md-10">
				{{ Form::text('complemento', null, ['class' => 'form-control', 'style' => 'width:50%']) }}
			</div>
		</div>

        <div class="form-group row">
			{{ Form::label('base', 'Base', ['class' => 'col-md-2']) }}
			<div class="col-md-10">
				{{ Form::text('base', null, ['class' => 'form-control', 'style' => 'width:50%']) }}
			</div>
		</div>

        <div class="form-group row">
			{{ Form::label('vicepresidencia', 'Vicepresidencia', ['class' => 'col-md-2']) }}
			<div class="col-md-10">
				{{ Form::text('vicepresidencia', null, ['class' => 'form-control', 'style' => 'width:50%']) }}
			</div>
		</div>

        <div class="form-group row">
			{{ Form::label('division', 'División', ['class' => 'col-md-2']) }}
			<div class="col-md-10">
				{{ Form::text('division', null, ['class' => 'form-control', 'style' => 'width:50%']) }}
			</div>
		</div>

        <div class="form-group row">
			{{ Form::label('departamento', 'Departamento', ['class' => 'col-md-2']) }}
			<div class="col-md-10">
				{{ Form::text('departamento', null, ['class' => 'form-control', 'style' => 'width:50%']) }}
			</div>
		</div>

		<div class="form-group text-center">
			{!! Form::button('Enviar', ['type' => 'submit', 'class' => 'btn btn-dark']) !!}
		</div>
		{{ Form::close() }}

	</div>
</div>
@endsection
